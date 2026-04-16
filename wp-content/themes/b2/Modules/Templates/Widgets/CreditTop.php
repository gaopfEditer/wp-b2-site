<?php namespace B2\Modules\Templates\Widgets;

class CreditTop extends \WP_Widget{

    //小工具slug
	protected $widget_slug = 'b2-widget-credit-top';

    //短代码名
	protected static $shortcode = 'b2_widget_credit-top';

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
			__( 'B2-积分排行小工具', 'b2' ),
			array(
				'classname' => $this->widget_slug,
				'customize_selective_refresh' => true,
				'description' => __( '“积分排行小工具”小工具', 'b2' ),
			)
		);

		//默认设置项
		self::$defaults = array(
            'ads_title'=>__('积分排行','b2'),
			'user_count'=>6,
			'exclude_users'=>0,
			'show_mobile'=>0
		);

		//设置项
		$this->cmb2_fields = array(
            array(
				'name'   => __('标题','b2'),
				'id_key' => 'ads_title',
				'id'     => 'ads_title',
				'type'   => 'text'
            ),
			array(
				'name'   => __('显示多少人','b2'),
				'id_key' => 'user_count',
				'id'     => 'user_count',
				'type'=>'text'
            ),
			array(
				'name'=>__('排除哪些人','b2'),
				'id_key'=>'exclude_users',
				'id'=>'exclude_users',
                'type'=>'text',
                'desc'=>__('填写要排除的用户ID，用英文的逗号 , 隔开','b2')
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

		// if ( empty( $atts['cache_id'] ) ) {
		// 	$atts['cache_id'] = md5( serialize( $atts ) );
        // }

		// if(B2_OPEN_CACHE){
		// 	$widget = ! $atts['flush_cache']
		// 	? wp_cache_get( $atts['cache_id'], 'widget' )
        //     : '';
		// }else{
		// 	$widget = '';
		// }

		// if(!empty($widget)) return $widget;

        $exclude = $instance[ 'exclude_users' ];
		$exclude = explode(',',$exclude);

		$args = array(
            'meta_key' => 'zrz_credit_total',
            'orderby' => 'meta_value_num',
            'order'	 => 'DESC',
			'number' => $instance[ 'user_count' ],
			'exclude'=>$exclude
		);
		
        $html = '<div class="credit-top" data-settings=\''.json_encode($args).'\' ref="creditTop">';

		$html .= '<ul class="gujia credit-top-list" ref="creditTopGujia">';
		for ($i=0; $i < $args['number']; $i++) { 
			$html .= '<li>
				<div class="credit-top-avatar"></div>
				<div class="credit-top-info">
					<div class="credit-top-info-left">
						<div class=""></div>
						<p><span></span><span></span></p>
					</div>
					<div class="credit-top-info-right"></div>
				</div>
			</li>';
		}
		$html .= '</ul>';

		$html .= '<ul class="credit-top-list" v-if="data != \'\'" v-cloak>';

		$html .= '<li v-for="item in data">
			<a :href="item.link" target="_blank" class="link-block"></a>
			<div class="credit-top-avatar">
				<img :src="item.avatar" class="avatar b2-radius"/>
			</div>
			<div class="credit-top-info">
				<div class="credit-top-info-left">
				<div class="credit-top-name"><span v-text="item.name"></span></div>
					<p><span v-html="item.lv.lv.icon"></span><span v-html="item.lv.vip.icon"></span></p>
				</div>
				<div class="credit-top-info-right"><span>'.b2_get_icon('b2-coin-line').'<b v-text="item.credit"></b></span></div>
			</div>
		</li>';
		
		$html .= '</ul>';
		$html .= '<div class="widget-mission-footer"><a href="'.b2_get_custom_page_url('gold-top').'" target="_blank">'.__('积分排行','b2').'</a></div>';
        $html .='</div>';


		// 如果 $widget 是空的， 重建缓存
		if ( empty( $widget )) {
			$widget = '';
	
			$widget .= !$instance['show_mobile'] ? str_replace('class="','class="mobile-hidden ',$atts['before_widget']) : $atts['before_widget'];
			$widget .= '<div class="b2-widget-title">';
			$widget .= $atts['before_title']. esc_attr( $instance['ads_title'] ) .$atts['after_title'];
			$widget .= '</div>';
			$widget .= '<div class="b2-widget-box">'.$html.'</div>';
			$widget .= $atts['after_widget'];
			
			
			// if(B2_OPEN_CACHE){
			// 	wp_cache_set( $atts['cache_id'], $widget, 'widget', WEEK_IN_SECONDS );
			// }
			
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