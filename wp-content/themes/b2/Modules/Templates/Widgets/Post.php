<?php namespace B2\Modules\Templates\Widgets;

class Post extends \WP_Widget{

    //小工具slug
	protected $widget_slug = 'b2-widget-hot';

    //短代码名
	protected static $shortcode = 'b2_widget_hot_post';

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
			__( 'B2-文章聚合', 'b2' ),
			array(
				'classname' => $this->widget_slug,
				'customize_selective_refresh' => true,
				'description' => __( '显示热门文章，最新文章，评论最多文章等', 'b2' ),
			)
		);

		//默认设置项
		self::$defaults = array(
			'hot_title' => __('文章聚合','b2'),
			'post_type'  => 'post',
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
				'id_key' => 'hot_title',
				'id'     => 'hot_title',
				'type'   => 'text',
			),
			array(
				'name'    => __('文章类型','b2'),
				'desc'    => __('将会显示在前端，不设置请留空','b2'),
				'id_key'  => 'post_type',
				'id'      => 'post_type',
				'type'    => 'select',
				'options'=>array(
					'post'=>__('文章','b2'),
					// 'shoptype'=>__('商品','b2'),
					// 'topic'=>__('帖子','b2'),
					// 'bubble'=>__('冒泡','b2'),
					// 'arcitive'=>__('活动','b2'),
					// 'lab'=>__('研究所','b2')
				)
			),
			array(
				'name'    => __('分类ID','b2'),
				'desc'    => sprintf(__('可以填写分类ID，%sID，标签ID等，多个请用竖线|隔开。ID之间是或者的关系，比如设置ID为1的标签和ID为12的分类，写作%s，这里将会显示标签是1，或者分类是12的文章。留空将显示全部。','b2'),$collection_name,'<code>1|12</code>'),
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
				'name'=>__('缩略图模式','b2'),
				'id_key'=>'show_thumb',
				'id'=>'show_thumb',
				'type'=>'radio_inline',
				'options'=>array(
					1=>__('无图模式','b2'),
					2=>__('小图模式','b2'),
					3=>__('大图模式','b2')
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
        
		//有内容更新，刷新缓存
		//add_filter( 'widget_update_callback', array( $this, 'cache_bump' ),10, 3 );
		add_action( 'save_post',    array( $this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
        add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );
		
		add_shortcode( self::$shortcode, array( __CLASS__, 'b2_widget_hot_post' ) );
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
			'post_type'=>$instance['post_type'],
			'posts_per_page'=>$instance['count'] ? (int)$instance['count'] : 6,
			'orderby'=>$instance['order'],
			'post__not_in'=>get_option("sticky_posts"),
			'no_found_rows'=>true,
			'post_status'=>'publish'
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
			$html = '<ul class="b2-widget-list-ul">';
            $_pages = $the_query->max_num_pages;
			$i = 0;

			$sidebar_width = b2_get_option('template_main','sidebar_width');
            while ( $the_query->have_posts() ) {

				$the_query->the_post();
				
				$i++;
                $post_id = $the_query->post->ID;
				
				$link = get_permalink();

				if($instance['show_thumb'] && $instance['post_type'] === 'post'){

					$title = get_the_title();

					if($instance['show_thumb'] == 2){
						$w = intval($sidebar_width/3.6);
						$h = intval(($sidebar_width/3.6)*0.618);
	
						$thumb = b2_get_thumb(array(
							'thumb'=>\B2\Modules\Common\Post::get_post_thumb($post_id),
							'width'=>$w,
							'height'=>$h
						));

						$html .= '<li class="b2-widget-box widget-post widget-post-small">
							'.($i < 4 ? '<div class="b2-widget-post-order widget-order-'.$i.'"><span class="b2-radius">TOP'.$i.'</span></div>' : '').'
							<div class="b2-widget-post-thumb b2-radius">
								<div class="b2-widget-post-thumb-img">
									'.b2_get_img(array(
										'class'=>array('b2-radius'),
										'src'=>$thumb,
										'alt'=>$title
									)).'
								</div>
								<div class="b2-widget-post-title">
									<h2>'.$title.'</h2>
									'.b2_timeago(get_the_date('Y-m-d G:i:s')).'
								</div>
							</div>
							<a ref="nofollow" class="link-overlay" href="'.$link.'"></a>
						</li>';
					}elseif($instance['show_thumb'] == 1){
						$html .= '<li class="b2-widget-box widget-post widget-post-none">
							<div class="b2-widget-post-order widget-order-'.$i.'"><span class="b2-radius">'.$i.'</span></div>
							<div class="b2-widget-post-title">
								<h2>'.$title.'</h2>
								'.b2_timeago(get_the_date('Y-m-d G:i:s')).'
							</div>
							<a class="link-overlay" href="'.$link.'"></a>
						</li>';
					}elseif($instance['show_thumb'] == 3){

						$w = $sidebar_width;
						$h = intval($sidebar_width*0.618);

						$thumb = b2_get_thumb(array(
							'thumb'=>\B2\Modules\Common\Post::get_post_thumb($post_id),
							'width'=>$w,
							'height'=>$h
						));

						$html .='<li class="b2-widget-box widget-post widget-post-big">
							<div class="b2-widget-post-thumb b2-radius">
								'.b2_get_img(array(
									'class'=>array('b2-radius'),
									'src'=>$thumb,
									'alt'=>$title,
									'attr'=>array(
										'height'=>$h,
										'width'=>$w
									)
								)).'
								<div class="b2-widget-post-title">
									<h2>'.$title.'</h2>
									'.b2_timeago(get_the_date('Y-m-d G:i:s')).'
								</div>
							</div>
							<a ref="nofollow" class="link-overlay" href="'.$link.'"></a>
						</li>';
					}
					
				}
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