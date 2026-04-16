<?php namespace B2\Modules\Templates\Widgets;

class User extends \WP_Widget{

    //小工具slug
	protected $widget_slug = 'b2-widget-user';

    //短代码名
	protected static $shortcode = 'b2_widget_user';

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
			__( 'B2-用户面板', 'b2' ),
			array(
				'classname' => $this->widget_slug,
				'customize_selective_refresh' => true,
				'description' => __( '“用户面板”小工具', 'b2' ),
			)
		);

		//默认设置项
		self::$defaults = array(
			'user_no_login_text'=>__('所有的伟大，都源于一个勇敢的开始','b2'),
			'show_mobile'=>0
		);

		//设置项
		$this->cmb2_fields = array(
			array(
				'name'   => __('未登录状态时的提示语','b2'),
				'id_key' => 'user_no_login_text',
				'id'     => 'user_no_login_text',
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

		$g_name = b2_get_option('normal_custom','custom_announcement_name');

		$html = '
			<div class="user-widget" id="user-widget" ref="userWidget">
				<div class="user-widget-content">
					<div class="widget-gujia-user" ref="gujia">
						<div class="user-widget-info">
							<div class="user-w-avatar">
							
							</div>
							<div class="user-w-name">
							</div>
						</div>
						<div class="user-w-tj">
							<div>
							</div>
							<div>
							</div>
							<div>
							</div>
							<div>
							</div>
						</div>
						<div class="user-w-rw"></div>
						<div class="user-w-announcement">
							<div></div>
							<div></div>
							<div></div>
						</div>
					</div>
					<div v-show="announcement != \'\'" v-cloak>
						<div v-if="userData != \'\'" v-cloak>
							<div class="user-widget-info">
								<div class="user-w-avatar">
									<a :href="userData.link" class="avatar-parent">
										'.b2_get_img(array(
											'src_data'=>':src="userData.avatar"',
											'class'=>array('avatar','b2-radius'),
											'source_data'=>':srcset="userData.avatar_webp"'
										)).'
									</a>
									<span v-html="userData.verify_icon" v-if="userData.verify"></span>
								</div>
								<div class="user-w-name">
									<a :href="userData.link"><h2 v-text="userData.name"></h2></a>
									<div class="user-w-lv">
										<div v-html="userData.lv.vip.icon" v-if="userData.lv.vip.icon"></div><div v-html="userData.lv.lv.icon" v-if="userData.lv.lv.icon"></div>
									</div>
								</div>
							</div>
							<div class="user-w-tj">
								<div>
									<p>'.__('文章','b2').'</p>
									<span v-text="userData.post_count"></span>
								</div>
								<div>
									<p>'.__('评论','b2').'</p>
									<span v-text="userData.comment_count"></span>
								</div>
								<div>
									<p>'.__('关注','b2').'</p>
									<span v-text="userData.following"></span>
								</div>
								<div>
									<p>'.__('粉丝','b2').'</p>
									<span v-text="userData.followers"></span>
								</div>
							</div>
							<div class="user-w-rw user-w-tips" :data-title="\''.__('您已完成今天任务的','b2').'\'+userData.task+\'%\'">
								<div class="user-w-rw-bg">{{userData.task+\'%\'}}</div>
								<a class="link-block" href="'.b2_get_custom_page_url('task').'" target="_blank"></a>
							</div>
						</div>
						<div v-if="!b2token" v-cloak>
							<div class="user-w-logon">
								<p class="user-w-logon-title b2-color">'.__('嗨！朋友','b2').'</p>
								<p>'.$instance['user_no_login_text'].'</p>
							</div>
							<div v-if="openOauth">
								<div class="oauth-login-button">
									<a :href="open.url" :class="\'login-\'+key" v-for="(open,key,index) in oauth" @click="markHistory(key)" v-if="open.open">{{open.name}}'.__('登录','b2').'</a>
								</div>
							</div>
							<div class="no-social" v-else v-cloak>
								<button @click="login.show = true">'.__('登录','b2').'</button>
							</div>
						</div>
						<div class="user-w-announcement" v-cloak>
							<div v-if="announcement != \'\' && announcement != \'none\'">
								<ul>
									<li v-for="(item,index) in announcement" :key="index" v-if="index < 20">
										<a :href="item.href" target="_blank"><b>'.$g_name.'：</b><span v-text="item.title"></span></a>
									</li>
								</ul>
							</div>
							<div v-else style="font-size:12px;padding:16px;color:#999">
								'.sprintf(__('没有%s','b2'),$g_name).'
							</div>
						</div>
					</div>
					<div class="widget-mission-footer"><a href="'.get_post_type_archive_link('announcement').'" target="_blank">'.sprintf(__('全部%s','b2'),$g_name).'</a></div>
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