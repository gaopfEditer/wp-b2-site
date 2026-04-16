<?php namespace B2\Modules\Templates\Widgets;
use B2\Modules\Common\Links;
use B2\Modules\Templates\Modules\Links as LinkHtml;

class BookMark extends \WP_Widget{

    //小工具slug
	protected $widget_slug = 'b2-widget-bookmark';

    //短代码名
	protected static $shortcode = 'b2_widget_bookmark';

    //CMB2
	protected $cmb2 = null;

    //默认设置
	protected static $defaults = array();
    
    //实例
	protected $_instance = array();
    
    //cmb2项目
	protected $cmb2_fields = array();

    
	public function __construct() {
		parent::__construct(
			$this->widget_slug,
			__( 'B2-导航连接', 'b2' ),
			array(
				'classname' => $this->widget_slug,
				'customize_selective_refresh' => true,
				'description' => __( '“导航连接”小工具', 'b2' ),
			)
		);

		//默认设置项
		self::$defaults = array(
            'link_title'=>__('热门推荐','b2'),
			'link_cat'=>'',
			'link_show_children'=>1,
            'link_junp'=>'self',
            'link_count_total'=>6,
            'link_order'=>'link_rating',
            'show_mobile'=>0
		);

		//设置项
		$this->cmb2_fields = array(
            array(
				'name'   => __('标题','b2'),
				'id_key' => 'link_title',
				'id'     => 'link_title',
				'type'   => 'text'
            ),
			array(
                'name'=>__('要显示的链接分类','b2'),
                'id_key' => 'link_cat',
                'id'=>'link_cat',
                'type'    => 'text',
                'desc'=>sprintf(__('请直接填写导航链接的分类ID，ID可以前往查看：%s链接分类%s。如果要显示当前链接对应的分类中的链接，请留空','b2'),'<a target="_blank" href="'.admin_url('/edit-tags.php?taxonomy=link_cat&post_type=links').'">','</a>')
            ),
            array(
                'name'=>__('是否包含子链接分类中的链接','b2'),
                'id'=>'link_show_children',
                'id_key'=>'link_show_children',
                'type'    => 'select',
                'options'=>array(
                    1=>__('显示','b2'),
                    0=>__('不显示','b2')
                ),
                'desc'=>__('如果此链接分类中有子链接分类，您可以选择是否显示子链接分类中的链接，注意，如果子链接分类特别多（大于20个）可能会有性能问题。','b2')
            ),
            array(
                'name'=>__('点击跳转方式','b2'),
                'id'=>'link_junp',
                'id_key'=>'link_junp',
                'type'    => 'select',
                'options'=>array(
                    'self'=>__('跳转到本站内页','b2'),
                    'target'=>__('跳转到目标站点','b2')
                )
            ),
            array(
                'name'=>__('一共显示几个','b2'),
                'id'=>'link_count_total',
                'id_key'=>'link_count_total',
                'type'    => 'text'
            ),
            array(
                'name'=>__('排序方法','b2'),
                'id'=>'link_order',
                'id_key'=>'link_order',
                'type'    => 'select',
                'options'=>array(
                    'DESC'=>__('最新添加的排在前面','b2'),
                    'ASC'=>__('最后添加的排在前面','b2'),
                    'link_rating'=>__('点赞最高的排在前面','b2')
                )
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
        
        //关于我们短代码
		//add_shortcode( self::$shortcode, array( __CLASS__, 'b2_widget_about_us' ) );
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

		if(B2_OPEN_CACHE && $instance['link_cat']){
			$widget = ! $atts['flush_cache']
			? wp_cache_get( $atts['cache_id'], 'widget' )
            : '';
		}else{
			$widget = '';
		}

		if(!empty($widget)) return $widget;

        if(!$instance['link_cat']){
            global $post;
            if(isset($post->ID)){
                $terms = wp_get_object_terms($post->ID, 'link_cat', array('fields' => 'ids'));
                if($terms){
                    $instance['link_cat'] = $terms[0];
                }else{
					$terms = get_terms( array( 
						'taxonomy' => 'link_cat',
						'count'   => 1,
						'cache_domain'=>'b2_link_cat'
					));
					if($terms){
						$instance['link_cat'] = $terms[0]->term_id;
					}
				}
            }
        }
		
        $instance['link_count'] = 1;
        $instance['link_meta'] = ['icon','desc'];

        $html = new LinkHtml();

        $html = $html->init($instance,'w');


		// 如果 $widget 是空的， 重建缓存
		if ( empty( $widget )) {
			$widget = '';
	
			$widget .= !$instance['show_mobile'] ? str_replace('class="','class="mobile-hidden ',$atts['before_widget']) : $atts['before_widget'];
			if($instance['link_title']){
				$widget .= '<div class="b2-widget-title">';
				$widget .= $atts['before_title']. esc_attr( $instance['link_title'] ) .$atts['after_title'];
				$widget .= '</div>';
			}
			$widget .= '<div class="b2-widget-box">'.$html.'</div>';
			$widget .= $atts['after_widget'];
			
			
			if(B2_OPEN_CACHE){
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