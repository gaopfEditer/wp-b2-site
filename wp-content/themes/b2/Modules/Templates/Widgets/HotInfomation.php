<?php namespace B2\Modules\Templates\Widgets;

class HotInfomation extends \WP_Widget{

    //小工具slug
	protected $widget_slug = 'b2-widget-hot-infomation';

    //短代码名
	protected static $shortcode = 'b2_widget_hot_infomation';

    //CMB2
	protected $cmb2 = null;

    //默认设置
	protected static $defaults = array();
    
    //实例
	protected $_instance = array();
    
    //cmb2项目
	protected $cmb2_fields = array();

    
	public function __construct() {
        $infomation_name = b2_get_option('normal_custom','custom_infomation_name');
		parent::__construct(
			$this->widget_slug,
			sprintf(__( 'B2-%s聚合', 'b2' ),$infomation_name),
			array(
				'classname' => $this->widget_slug,
				'customize_selective_refresh' => true,
				'description' => sprintf(__( '显示热门%s，最新%s，评论最多%s等', 'b2' ), $infomation_name, $infomation_name, $infomation_name),
			)
		);

		//默认设置项
		self::$defaults = array(
			'hot_title' => sprintf(__('%s聚合','b2'),$infomation_name),
			'post_type'  => 'infomation',
			'post_cat'=>'',
			'order'=>'meta_value_num',
			'days'=>0,
			'count' => 6,
			'show_mobile'=>0
		);

		//设置项
		$this->cmb2_fields = array(
			array(
				'name'   => __('标题','b2'),
				'id_key' => 'hot_title',
				'id'     => 'hot_title',
				'type'   => 'text',
			),
			array(
				'name'    => __('分类ID','b2'),
				'desc'    => sprintf(__('可以填写分类ID，%sID，标签ID等，多个请用竖线|隔开。ID之间是或者的关系，比如设置ID为1的标签和ID为12的分类，写作%s，这里将会显示标签是1，或者分类是12的文章。留空将显示全部。','b2'),$infomation_name,'<code>1|12</code>'),
				'id_key'  => 'post_cat',
				'id'      => 'post_cat',
				'type'    => 'textarea_small'
			),
			array(
				'name'   => __('排序方法','b2'),
				'id_key' => 'order',
				'id'     => 'order',
				'type'   => 'select',
				'options'=>array(
					'rand'=>__('随机','b2'),
					'date'=>__('时间','b2'),
					'meta_value_num'=>__('浏览量','b2'),
					'comment_count'=>__('评论数量','b2')
				)
			),
			array(
				'name'   => __('时间范围','b2'),
				'id_key' => 'days',
				'id'     => 'days',
				'type'   => 'text',
				'desc'=>__('限制显示多少天内的文章，如果没有时间限制，请填写0','b2')
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
		//add_filter( 'widget_update_callback', array( $this, 'cache_bump' ),10, 3 );
		add_action( 'save_post',    array( $this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
        add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );
		
		add_shortcode( self::$shortcode, array( __CLASS__, 'b2_widget_hot_infomation' ) );
	}

	public function cache_bump( $instance ) {

		$this->flush_widget_cache();

		return $instance;

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
		wp_cache_delete($this->id, 'widget' );
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
				'cache_id'=>'',
				'instance'      => array(),
				'before_widget' => '',
				'after_widget'  => '',
				'before_title'  => '',
				'after_title'   => '',
				'flush_cache'   => isset( $_GET['delete-trans'] ), 
			),
			isset( $atts['args'] ) ? (array) $atts['args'] : array(),
			self::$shortcode
		);

		if ( empty( $atts['cache_id'] ) ) {
			$atts['cache_id'] = md5( serialize( $atts ) );
        }
		
		if(B2_OPEN_CACHE && $instance['order'] != 'rand'){
			$widget = ! $atts['flush_cache']
			? wp_cache_get( $atts['cache_id'], 'widget' )
            : '';
		}else{
			$widget = '';
		}

		if(!empty($widget)) return $widget;

		$instance['post_cat'] = trim($instance['post_cat'], " \t\n\r\0\x0B\xC2\xA0");

		if((int)$instance['count'] < 0 || (int)$instance['count'] > 50) return;

		$args = array(
			'post_type'=>'infomation',
			'posts_per_page'=>$instance['count'] ? (int)$instance['count'] : 6,
			'orderby'=>$instance['order'],
			'no_found_rows'=>true,
			'post_status'=>'publish',
		);

		if($instance[ 'days' ]){
            $args['date_query'] = array(
                array(
                    'after'     => wp_date('Y-m-d',wp_strtotime("-".$instance[ 'days' ]." days")),//7天的时间
                    'inclusive' => true,
                )
            );
        }

		if($instance['post_cat']){
			$tax_array = explode( '|', $instance['post_cat']);
			
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

		if($args['orderby'] === 'meta_value_num'){
			$args['meta_key'] = 'views';
		}

		$the_query = new \WP_Query( $args );

		if ( $the_query->have_posts() ) {

			//获取文章数据
			$html = '<ul class="b2-widget-list-ul b2-widget-infomation">';
            $_pages = $the_query->max_num_pages;
			$i = 0;

            $name_for = b2_get_option('normal_custom','custom_infomation_for');
            $name_get = b2_get_option('normal_custom','custom_infomation_get');

			$sidebar_width = b2_get_option('template_index','sidebar_width');
            while ( $the_query->have_posts() ) {

				$the_query->the_post();
				
				$i++;
                $post_id = $the_query->post->ID;
				
				$link = get_permalink();

                $title = get_the_title();

                $type = get_post_meta($post_id,'b2_infomation_type',true);

                $html .= '<li class="b2-widget-box widget-post widget-post-none">
                
                <div class="b2-widget-post-title">
                    <h2>'.$title.'</h2>
                    <div class="widget-info-type"><span class="i-type '.$type.'">'.($type == 'get' ? $name_get : $name_for).'</span><span>'.b2_timeago(get_the_date('Y-m-d G:i:s')).'</span></div>
                </div>
                <a class="link-overlay" href="'.$link.'"></a>
            </li>';
					
            }
			
			$html .= '</ul>';
			
		}else{
			$html = '<div class="b2-widget-empty b2-pd b2-b-t fs12">'.__('没有任何内容','b2').'</div>';
		}
		
		wp_reset_postdata();
		// 如果 $widget 是空的， 重建缓存
		if ( empty( $widget )) {
			$widget = '';
	
			$widget .= !$instance['show_mobile'] ? str_replace('class="','class="mobile-hidden ',$atts['before_widget']) : $atts['before_widget'];
			$widget .= '<div class="b2-widget-title">';
			$widget .= $atts['before_title']. esc_attr( $instance['hot_title'] ) .$atts['after_title'];
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