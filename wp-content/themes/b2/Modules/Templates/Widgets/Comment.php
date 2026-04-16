<?php namespace B2\Modules\Templates\Widgets;

class Comment extends \WP_Widget{

    //小工具slug
	protected $widget_slug = 'b2-widget-comment';

    //短代码名
	protected static $shortcode = 'b2_widget_comment';

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
			__( 'B2-最新评论', 'b2' ),
			array(
				'classname' => $this->widget_slug,
				'customize_selective_refresh' => true,
				'description' => __( '“最新评论”小工具', 'b2' ),
			)
		);

		//默认设置项
		self::$defaults = array(
            'comment_title'=>__('最新评论','b2'),
            'comment_count'=>6,
            'comment_hidden'=>1,
			'show_mobile'=>0
		);

		//设置项
		$this->cmb2_fields = array(
			array(
				'name'   => __('标题','b2'),
				'id_key' => 'comment_title',
				'id'     => 'comment_title',
				'type'   => 'text'
            ),
            array(
				'name'   => __('一页显示几条评论','b2'),
				'id_key' => 'comment_count',
				'id'     => 'comment_count',
				'type'   => 'text'
			),
			array(
				'name'   => __('要隐藏的用户ID','b2'),
				'id_key' => 'comment_hidden',
				'id'     => 'comment_hidden',
                'type'   => 'text',
                'desc'=>__('请直接填写用户ID，一般为管理员','b2')
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

		for ($i=0; $i < $instance['comment_count']; $i++) { 
			$gujia .= '
			<li>
				<div class="widget-comment-user">
					<div class="widget-comment-user-left">
						<span></span>
					</div>
					<time></time>
				</div>
				<div class="widget-comment-contnet b2-radius jt">
					<p></p>
				</div>
				<div class="widget-comment-post">
					<span></span><a href="#"></a>
				</div>
			</li>
			';
		}

		$html = '
            <div class="comment-widget" id="comment-widget" ref="commentWidget" data-count="'.$instance['comment_count'].'" data-hidden="'.$instance['comment_hidden'].'">
				<ul class="comment-gujia" ref="gujia">
					'.$gujia.'
				</ul>
				<ul v-if="data != \'\'" v-cloak>
					<li v-for="item in data">
						<div class="widget-comment-user">
							<div class="widget-comment-user-left">
								<a :href="item.user.link" target="_blank" class="link-block"></a>
									<img :src="item.user.avatar" class="b2-radius avatar"/>
								<span v-text="item.user.name"></span>
							</div>
							<span v-html="item.date"></span>
						</div>
						<div class="widget-comment-contnet b2-radius jt">
							<p v-html="item.content"></p>
							<p v-if="item.comment_img" class="comment-img"><img :src="item.comment_img" class="b2-radius"></p>
						</div>
						<div class="widget-comment-post">
							<span>'.sprintf(__('%s来自：','b2'),'<b class="b2-color" v-text="item.post_type"></b>').'</span><a :href="item.post.link" v-html="item.post.title"></a>
						</div>
					</li>
				</ul>
			</div>
		';

		// 如果 $widget 是空的， 重建缓存
		if ( empty( $widget )) {
			$widget = '';
	
			$widget .= !$instance['show_mobile'] ? str_replace('class="','class="mobile-hidden ',$atts['before_widget']) : $atts['before_widget'];
			$widget .= '<div class="b2-widget-title">';
			$widget .= $atts['before_title']. esc_attr( $instance['comment_title'] ).$atts['after_title'];
			$widget .= '</div><div class="comment-widget-nav"><span :class="{\'locked\':prev || locked}" @click="prevAc()">'.b2_get_icon('b2-arrow-left-s-line').'PREV</span><span :class="{\'locked\':next || locked}" @click="nexAc()">NEXT'.b2_get_icon('b2-arrow-right-s-line').'</span></div>';
			$widget .= '<div class="b2-widget-box">'.$html.'</div>';
			$widget .= $atts['after_widget'];
			
			// if(B2_OPEN_CACHE){
			// 	wp_cache_set( $atts['cache_id'], $widget, 'widget', MINUTE_IN_SECONDS*30 );
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