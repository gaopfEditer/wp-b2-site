<?php namespace B2\Modules\Templates\Widgets;

class Ask extends \WP_Widget{

    //小工具slug
	protected $widget_slug = 'b2-widget-ask';

    //短代码名
	protected static $shortcode = 'b2_widget_ask_post';

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
			__( 'B2-问答小工具', 'b2' ),
			array(
				'classname' => $this->widget_slug,
				'customize_selective_refresh' => true,
				'description' => __( '问答首页会显示全部问答，问答板块中会显示当前问答的内容，问答内页会显示相关问答', 'b2' ),
			)
		);

		//默认设置项
		self::$defaults = array(
			'ask_title' => __('问答','b2'),
			'type'  => '',
			'post_cat'=>'',
			'order'=>'meta_value_num',
			'days'=>0,
			'count' => 6,
			'show_thumb'=>1,
			'show_mobile'=>0
		);

		$collection_name = b2_get_option('normal_custom','custom_collection_name');

		//设置项
		$this->cmb2_fields = array(
			array(
				'name'   => __('标题','b2'),
				'id_key' => 'ask_title',
				'id'     => 'ask_title',
				'type'   => 'text',
				'before_row'=>'<p>'.__( '问答首页会显示全部问答，问答板块中会显示当前问答的内容，问答内页会显示相关问答', 'b2' ).'</p>'
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
		
		add_shortcode( self::$shortcode, array( __CLASS__, 'b2_widget_ask_post' ) );
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
		
		// if(B2_OPEN_CACHE){
		// 	$widget = ! $atts['flush_cache']
		// 	? wp_cache_get( $atts['cache_id'], 'widget' )
        //     : '';
		// }else{
			$widget = '';
		// }

		if(!empty($widget)) return $widget;

		$gujia = '<ul class="ask-widget-list gujia">';

		for ($i=0; $i <= $instance['count']; $i++) { 
			$gujia .= '<li><span></span></li>';
		}

		$gujia .= '</ul>';

		$html = '<div id="ask-widget" ref="askwidget" data-count="'.$instance['count'].'" data-time="'.$instance['days'].'">
			<div class="ask-widget-fliter">
				<span :class="fliter == \'hot\' ? \'b2-color\' : \'\'" @click="fliter = \'hot\'">'.__('热门','b2').'</span>
				<span :class="fliter == \'last\' ? \'b2-color\' : \'\'" @click="fliter = \'last\'">'.__('最新','b2').'</span>
				<span :class="fliter == \'waiting\' ? \'b2-color\' : \'\'" @click="fliter = \'waiting\'">'.__('等待回答','b2').'</span>
			</div>
			<div class="ask-widget-list">
				'.$gujia.'
				<div v-if="data !== \'\'" v-cloak>
					<div v-if="empty">
						'.__('没有内容','b2').'
					</div>
					<ul class="ask-widget-ul" v-else>
						<li v-for="(item,i) in data.data">
							<a :href="item.link" target="_blank" class="link-block"></a>
							<div class="ask-widget-title"><span v-text="item.title"></span></div>
							<div class="b2flex ask-widget-meta">
								<div><span v-text="item.metas.answer_count"></span>'.__('个回答','b2').'</div>
								<div>
									<div v-if="item.metas.reward" class="green">
										<span v-if="item.metas.reward.rewardType == \'credit\'">
											'.b2_get_icon('b2-coin-line').'{{item.metas.reward.money}}
										</span>
										<span v-else>
											'.B2_MONEY_SYMBOL.'{{item.metas.reward.money}}
										</span>
									</div>
								</div>
							</div>
						</li>
					</ul>
				</div>
			</div>
		</div>';		
		wp_reset_postdata();
		// 如果 $widget 是空的， 重建缓存
		if ( empty( $widget )) {
			$widget = '';
	
			$widget .= !$instance['show_mobile'] ? str_replace('class="','class="mobile-hidden ',$atts['before_widget']) : $atts['before_widget'];
			$widget .= '<div class="b2-widget-title">';
			$widget .= $atts['before_title']. esc_attr( $instance['ask_title'] ) .$atts['after_title'];
			$widget .= '</div><div class="comment-widget-nav"><span :class="{\'locked\':prev}" @click="prevAc()">'.b2_get_icon('b2-arrow-left-s-line').'PREV</span><span :class="{\'locked\':next}" @click="nexAc()">NEXT'.b2_get_icon('b2-arrow-right-s-line').'</span></div>';
			$widget .= '<div class="b2-widget-box">'.$html.'</div>';
			$widget .= $atts['after_widget'];
			
			if(B2_OPEN_CACHE && $instance['order'] != 'rand'){
				wp_cache_set( $atts['cache_id'], $widget, 'widget', 1800 );
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