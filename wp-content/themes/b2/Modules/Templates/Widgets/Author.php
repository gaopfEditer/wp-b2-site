<?php namespace B2\Modules\Templates\Widgets;

use B2\Modules\Common\User;
use B2\Modules\Common\Comment;

class Author extends \WP_Widget{

    //小工具slug
	protected $widget_slug = 'b2-widget-author';

    //短代码名
	protected static $shortcode = 'b2_widget_author';

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
			__( 'B2-作者面板', 'b2' ),
			array(
				'classname' => $this->widget_slug,
				'customize_selective_refresh' => true,
				'description' => __( '“作者面板”小工具（只在内页生效）', 'b2' ),
			)
		);

        self::$defaults = array(
			'post_count'=>4,
			'title'=>__('关于作者','b2'),
			'show_mobile'=>0
		);

		//设置项
		$this->cmb2_fields = array(
			array(
				'name'   => __('标题','b2'),
				'id_key' => 'title',
				'id'     => 'title',
				'type'   => 'text'
			),
			array(
				'name'   => __('显示TA的多少篇最新文章','b2'),
				'id_key' => 'post_count',
				'id'     => 'post_count',
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

        if(!is_single()) return;
        $post_id = get_the_id();

		$author = get_post_field('post_author', $post_id );
		if(!$author) return;

		$author_data = User::get_user_public_data($author);

		$count_data = User::get_user_custom_data($author);

		$args = array(
			'post_type'=>array('post','circle','document','newsflashes','shop'),
			'posts_per_page'=>$instance['post_count'] ? (int)$instance['post_count'] : 6,
			'orderby'=>'date',
			'post_status'=>'publish',
			'author'=>$author,
			'post__not_in'=>get_option("sticky_posts"),
			'no_found_rows'=>true
		);

		$posts = '';

		$the_query = new \WP_Query( $args );

		if ( $the_query->have_posts() ) {

			//获取文章数据
            while ( $the_query->have_posts() ) {
				$the_query->the_post();

				$psot_type = Comment::get_post_type(get_the_id());
				$posts .= '<div><a href="'.get_permalink().'" target="_blank"><span class="b2-color">'.$psot_type.'</span> '.get_the_title().'</a></div>';

			}

			wp_reset_postdata();
		}

		$html = '
			<div class="author-widget">
				<div class="author-widget-content">
					<div class="w-a-info">
						<img src="'.$author_data['avatar'].'" class="avatar b2-radius"/>
						<div class="w-a-name">
							<a href="'.$author_data['link'].'" class="link-block"></a>
							<p>'.$author_data['name'].''.($author_data['verify'] ? B2_VERIFY_ICON : '').'</p>
							<div class="w-a-lv">
								'.($author_data['lv']['lv']['icon'] ? $author_data['lv']['lv']['icon'] : '').'
								'.($author_data['lv']['vip']['icon'] ? $author_data['lv']['vip']['icon'] : '').'
							</div>
						</div>
					</div>
					<div class="w-a-count">
						<div>
							<p>'.__('文章','b2').'</p>
							<span>'.$count_data['post_count'].'</span>
						</div>
						<div>
							<p>'.__('评论','b2').'</p>
							<span>'.$count_data['comment_count'].'</span>
						</div>
						<div>
							<p>'.__('关注','b2').'</p>
							<span>'.$count_data['following'].'</span>
						</div>
						<div>
							<p>'.__('粉丝','b2').'</p>
							<span>'.$count_data['followers'].'</span>
						</div>
					</div>
					<div class="w-a-post-list">
						'.$posts.'
					</div>
				</div>
				<div class="widget-mission-footer b2-color"><a href="'.b2_get_custom_page_url('tastream').'/'.$author.'" target="_blank">'.__('Ta的全部动态','b2').'</a></div>
			</div>';

		// 如果 $widget 是空的， 重建缓存
		if ( empty( $widget )) {
			$widget = '';
	
			$widget .= !$instance['show_mobile'] ? str_replace('class="','class="mobile-hidden ',$atts['before_widget']) : $atts['before_widget'];
			$widget .= '<div class="b2-widget-title">';
			$widget .= $atts['before_title'].'<span>'.esc_attr( $instance['title'] ) .'</span><span class="">
			<button @click="followingAc" class="text" v-if="following == 1" v-cloak>'.__('取消关注','b2').'</button>
			<button @click="followingAc" v-else class="text">'.b2_get_icon('b2-add-line').__('关注','b2').'</button>
			<button class="text" @click="dmsg()">'.__('私信','b2').'</button>
			</span>'.$atts['after_title'];
			$widget .= '</div>';
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