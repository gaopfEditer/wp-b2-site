<?php namespace B2\Modules\Templates\Widgets;

class Mission extends \WP_Widget{

    //小工具slug
	protected $widget_slug = 'b2-widget-mission';

    //短代码名
	protected static $shortcode = 'b2_widget_mission';

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
			__( 'B2-签到', 'b2' ),
			array(
				'classname' => $this->widget_slug,
				'customize_selective_refresh' => true,
				'description' => __( '“签到小工具”小工具', 'b2' ),
			)
		);

		//默认设置项
		self::$defaults = array(
			'mission_count'=>6,
			'show_mobile'=>0
		);

		//设置项
		$this->cmb2_fields = array(
			array(
				'name'   => __('签到列表用户数量','b2'),
				'id_key' => 'mission_count',
				'id'     => 'mission_count',
				'type'   => 'text'
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

		$gujia = '';

		for ($i=0; $i < $instance['mission_count']; $i++) { 
			$gujia .= '
			<li>
				<a href="#" class="user-link-block avatar-parent"><span></span></a>
				<div class="user-mission-info">
					<div class="user-mission-info-left">
						<a href="#"><p></p></a>
						<p><time></time></p>
					</div>
					<div class="user-mission-info-right">
						<span class="user-credit"></span>
					</div>
				</div>
			</li>
			';
		}

		$html = '
			<div class="mission-widget" ref="missionWidget" data-count="'.$instance['mission_count'].'">
				<div class="mission-gujia" ref="missiongujia">
					<div class="user-w-qd">
						<div class=""></div>
					</div>
					<div class="user-w-qd-list">
						<div class="user-w-qd-list-title b2-radius">
							<p><span></span></p>
							<p><span></span></p>
						</div>
						<div class="mission-today-list">
							<ul>
								'.$gujia.'
							</ul>
							<div class="widget-mission-footer"><a href="'.b2_get_custom_page_url('mission').'" target="_blank">'.__('签到排行','b2').'</a></div>
						</div>
					</div>
				</div>
				<div v-cloak v-if="data !== \'\'">
					<div :class="[\'user-w-qd\',{\'cur\':!data.mission.credit}]" @click="mission()" v-if="data !== \'\'">
						<div class="" v-if="locked">'.b2_get_icon('b2-gift-2-line').__('幸运之星正在降临...','b2').'</div>
						<div class="" v-else-if="data.mission.credit == \'\'">'.b2_get_icon('b2-gift-2-line').__('点击领取今天的签到奖励！','b2').'</div>
						<div class="" v-else>'.b2_get_icon('b2-gift-2-line').sprintf(__('恭喜！您今天获得了%s积分','b2'),'<b>{{data.mission.credit}}</b>').'</div>
					</div>
					<div class="user-w-qd-list">
						<div class="user-w-qd-list-title b2-radius">
							<p :class="type == \'today\' ? \'picked\' : \'\'" @click="type = \'today\'"><span>'.__('今日签到','b2').'</span></p>
							<p :class="type == \'always\' ? \'picked\' : \'\'" @click="type = \'always\'"><span>'.__('连续签到','b2').'</span></p>
						</div>
						<div class="mission-today-list" v-cloak v-if="data.mission">
							<template v-if="data.mission_today_list.data.length > 0">
								<ul v-if="type === \'today\'">
									<li v-for="item in data.mission_today_list.data">
										<a :href="item.user.link" class="user-link-block avatar-parent"><img :src="item.user.avatar" class="b2-radius avatar"><span v-if="item.user.user_title" v-html="item.user.verify_icon"></span></a>
										<div class="user-mission-info">
											<div class="user-mission-info-left">
												<a :href="item.user.link"><p v-text="item.user.name"></p></a>
												<p v-html="item.date"></p>
											</div>
											<div class="user-mission-info-right">
												<span class="user-credit">'.b2_get_icon('b2-coin-line').'{{item.credit}}</span>
											</div>
										</div>
									</li>
								</ul>
								<ul v-else>
									<li v-for="_item in data.mission_always_list.data">
										<a :href="_item.user.link" class="user-link-block avatar-parent"><img :src="_item.user.avatar" class="b2-radius avatar"><span v-if="_item.user.user_title" v-html="_item.user.verify_icon"></span></a>
										<div class="user-mission-info">
											<div class="user-mission-info-left">
												<a :href="_item.user.link"><p v-text="_item.user.name"></p></a>
												<p v-html="_item.date"></p>
											</div>
											<div class="user-mission-info-right">
												'.__('连续','b2').'{{_item.count}}'.__('天','b2').'
											</div>
										</div>
									</li>
								</ul>
							</template>
							<template v-else>
								<div style="padding: 20px;font-size: 12px;text-align: center;">'.__('没有签到数据','b2').'</div>
							</template>
							<div class="widget-mission-footer"><a href="'.b2_get_custom_page_url('mission').'" target="_blank">'.__('签到排行','b2').'</a></div>
						</div>
					</div>
				</div>
			</div>
		';

		// 如果 $widget 是空的， 重建缓存
		if ( empty( $widget )) {
			$widget = '';
	
			$widget .= !$instance['show_mobile'] ? str_replace('class="','class="mobile-hidden ',$atts['before_widget']) : $atts['before_widget'];
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