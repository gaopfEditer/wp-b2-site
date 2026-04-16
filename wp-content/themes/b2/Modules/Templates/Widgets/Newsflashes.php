<?php namespace B2\Modules\Templates\Widgets;

use B2\Modules\Common\Newsflashes as News;

class Newsflashes extends \WP_Widget{

    //小工具slug
	protected $widget_slug = 'b2-widget-newsflashes';

    //短代码名
	protected static $shortcode = 'b2_widget_newsflashes';

    //CMB2
	protected $cmb2 = null;

    //默认设置
	protected static $defaults = array();
    
    //实例
	protected $_instance = array();
    
    //cmb2项目
	protected $cmb2_fields = array();

    
	public function __construct() {
		
		$newsflashes_name = b2_get_option('normal_custom','custom_newsflashes_name');
		parent::__construct(
			$this->widget_slug,
			sprintf(__( 'B2-%s小工具', 'b2' ),$newsflashes_name),
			array(
				'classname' => $this->widget_slug,
				'customize_selective_refresh' => true,
				'description' => sprintf(__( '一个页面最多只能有一个%s小工具！显示热门%s，最新%s，利好最多%s等', 'b2' ),$newsflashes_name,$newsflashes_name,$newsflashes_name,$newsflashes_name),
			)
		);

		//默认设置项
		self::$defaults = array(
			'new_title' => $newsflashes_name,
			'news_tag'  => '',
			'order'=>'new',
			'count' => 6,
			'show_mobile'=>0
		);

		//设置项
		$this->cmb2_fields = array(
			array(
				'name'   => __('标题','b2'),
				'id_key' => 'new_title',
				'id'     => 'new_title',
				'type'   => 'text',
			),

			array(
				'name'    => sprintf(__('%s标签ID','b2'),$newsflashes_name),
				'desc'    => sprintf(__('可以填写%s标签ID，多个请用竖线|隔开。ID之间是或者的关系，比如设置ID为1的标签和ID为12的分类，写作%s，这里将会显示标签是1，或者分类是12的文章。留空将显示全部。','b2'),$newsflashes_name,'<code>1|12</code>'),
				'id_key'  => 'news_tag',
				'id'      => 'news_tag',
				'type'    => 'textarea_small'
			),
			array(
				'name'   => __('排序方法','b2'),
				'id_key' => 'order',
				'id'     => 'order',
				'type'   => 'select',
				'options'=>array(
					'new'=>__('最新','b2'),
					'hot'=>__('热门','b2'),
					'rand'=>__('随机','b2'),
					'vote'=>b2_get_option('newsflashes_main','newsflashes_vote_up_text')
				)
			),
			array(
				'name'=>__('显示数量','b2'),
				'id_key'=>'count',
				'id'=>'count',
				'type'=>'text'
			),
			array(
				'name'=>__('移动端是否显示','b2'),
				'id_key'=>'show_mobile',
				'id'=>'show_mobile',
				'type'=>'radio_inline',
				'options'=>array(
					1=>__('显示','b2'),
					0=>__('隐藏','b2')
				)
			)
        );
        
        //有内容更新，刷新缓存
		add_action( 'save_post',    array( $this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
        add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );
        
		add_shortcode( self::$shortcode, array( __CLASS__, 'b2_widget_hot_post' ) );
	}
    
    /**
     * 刷新缓存
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
	public function flush_widget_cache() {
		wp_cache_delete( $this->id, 'widget' );
	}
    
    /**
     * 显示小工具
     *
     * @param [type] $args
     * @param [type] $instance
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
	public function widget( $args, $instance ) {
	
		echo self::get_widget( array(
			'args'     => $args,
			'instance' => $instance,
			'cache_id' => $this->id,
		) );
	}
    
    /**
     * 显示小工具短代码内容
     *
     * @param [type] $atts
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
	public static function get_widget( $atts ) {
        $atts['args']['cache_id'] = $atts['cache_id'];

		//获取设置项
		$instance = shortcode_atts(
			self::$defaults,
			! empty( $atts['instance'] ) ? (array) $atts['instance'] : array(),
			self::$shortcode
		);

		$atts = shortcode_atts(
			array(
				'instance'      => array(),
				'before_widget' => '',
				'after_widget'  => '',
				'before_title'  => '',
				'after_title'   => '',
				'cache_id'      => '',
				'flush_cache'   => isset( $_GET['delete-trans'] ), 
			),
			isset( $atts['args'] ) ? (array) $atts['args'] : array(),
			self::$shortcode
        );

		if ( empty( $atts['cache_id'] ) ) {
			$atts['cache_id'] = md5( serialize( $atts ) );
        }

		if(B2_OPEN_CACHE){
			$widget = ! $atts['flush_cache']
			? wp_cache_get( $atts['cache_id'], 'widget' )
            : '';
		}else{
			$widget = '';
		}

		if(!empty($widget) && $instance['order'] != 'rand') return $widget;

		$newsflashes_name = b2_get_option('normal_custom','custom_newsflashes_name');
        
        $instance['news_tag'] = trim($instance['news_tag'], " \t\n\r\0\x0B\xC2\xA0");

		if((int)$instance['count'] < 0 || (int)$instance['count'] > 50) return;

		$args = array(
			'posts_per_page'=>$instance['count'] ? (int)$instance['count'] : 6,
			'orderby'=>'desc',
			's_order'=>$instance['order']
        );

		if($instance['news_tag']){
			$tax_array = explode( '|', $instance['news_tag']);
			
			$arg = array();
			$args['tax_query'] = array(
				'relation' => 'OR',
			);
			foreach ($tax_array as $k => $v) {
				$term = get_term( $v );

				array_push($args['tax_query'], array(
					'taxonomy' => $term->taxonomy,
					'field' => 'term_id',
					'terms' => $v,
					'operator'         => 'IN',
					'include_children' => false,
				));
			}
			
		}

		if($args['s_order'] === 'hot'){
			$args['meta_key'] = 'views';
        }
        
        if($args['s_order'] === 'vote'){
			$args['meta_key'] = 'b2_vote_up_count';
        }

		if($args['s_order'] === 'rand'){
			$args['orderby'] = 'rand';
        }
        
        $gujia = '';

        for ($i=0; $i < $instance['count']; $i++) { 
            $gujia .= '<li class="widget-news-gujia">
                
                <div class="widget-new-content">
                    <h2 class="anhover"></h2>
					<div class="widget-new-header">
						<span class="widget-news-user"></span>
						<span></span>
					</div>
                </div>
            </li>';
        }

        $html = '
            <div class="widget-newsflashes-box" ref="newsWidget" data-json=\''.json_encode($args).'\'>
                <ul>
                    <div ref="gujia">
                    '.$gujia.'
                    </div>
                    <div v-show="list !== \'\'" v-cloak>
                        <li v-for="item in list">
                            <div class="widget-new-content">
                                <h2 class="anhover"><span class="ps"><a :href="item.link" target="_blank"><b v-html="item.title"></b></a></h2>
								<div class="widget-new-header">
									<span class="widget-news-user">
										<a :href="item.tag.link"><b v-text="item.tag.name"></b></a>
										<a :href="item.author.link" target="_blank">'.__('作者：','b2').'<b v-html="item.author.name"></b></a>
									</span>
									<span class="ps1" v-html="item.date.time"></span>
								</div>
                            </div>
                        </li>
                    </div>
                </ul>
				<div class="widget-mission-footer"><a href="'.get_post_type_archive_link('newsflashes').'" target="_blank">'.sprintf(__('全部%s','b2'),$newsflashes_name).'</a></div>
            </div>
        ';
        
		wp_reset_postdata();
		// 如果 $widget 是空的， 重建缓存
		if ( empty( $widget )) {
			$widget = '';
	
			$widget .= !$instance['show_mobile'] ? str_replace('class="','class="mobile-hidden ',$atts['before_widget']) : $atts['before_widget'];
			$widget .= '<div class="b2-widget-title">';
			$widget .= $atts['before_title']. esc_attr( $instance['new_title'] ) .$atts['after_title'];
			$widget .= '</div>';
			$widget .= '<div class="b2-widget-box">'.$html.'</div>';
			$widget .= $atts['after_widget'];
			
			if(B2_OPEN_CACHE && $instance['order'] != 'rand'){
				wp_cache_set( $atts['cache_id'], $widget, 'widget', MINUTE_IN_SECONDS*30 );
			}
			
		}

		return $widget;
	}
    
    /**
     * 更新小工具
     *
     * @param array $new_instance
     * @param array $old_instance
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
	public function update( $new_instance, $old_instance ) {
		$this->flush_widget_cache();
		$sanitized = $this->cmb2( true )->get_sanitized_values( $new_instance );
		return $sanitized;
	}
    
    /**
     * 小工具表单
     *
     * @param array $instance
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
	public function form( $instance ) {
		// 如果没有设置项使用默认
		$this->_instance = wp_parse_args( (array) $instance, self::$defaults );
		$cmb2 = $this->cmb2();
		$cmb2->object_id( $this->option_name );
		\CMB2_hookup::enqueue_cmb_css();
		\CMB2_hookup::enqueue_cmb_js();
		$cmb2->show_form();
	}
    
    /**
     * 创建实例
     *
     * @param bool $saving
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
	public function cmb2( $saving = false ) {

		$cmb2 = new \CMB2( array(
			'id'      => $this->option_name .'_box', 
			'hookup'  => false,
			'show_on' => array(
				'key'   => 'options-page',
				'value' => array( $this->option_name )
			),
		), $this->option_name );
		foreach ( $this->cmb2_fields as $field ) {
			if ( ! $saving ) {
				$field['id'] = $this->get_field_name( $field['id'] );
			}
			$field['default_cb'] = array( $this, 'default_cb' );
			$cmb2->add_field( $field );
		}
		return $cmb2;
	}
	/**
	 * 设置默认项
	 *
	 * @param  array      $field_args CMB2的设置项
	 * @param  CMB2_Field $field CMB2 选项对象
	 *
	 * @return mixed      Field value.
	 */
	public function default_cb( $field_args, $field ) {
		return isset( $this->_instance[ $field->args( 'id_key' ) ] )
			? $this->_instance[ $field->args( 'id_key' ) ]
			: null;
	}
}