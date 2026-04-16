<?php namespace B2\Modules\Templates\Widgets;
use B2\Modules\Common\Shop;
use B2\Modules\Common\Post as Cpost;
class Products extends \WP_Widget{

    //小工具slug
	protected $widget_slug = 'b2-widget-products';

    //短代码名
	protected static $shortcode = 'b2_widget_products';

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
			__( 'B2-商品聚合', 'b2' ),
			array(
				'classname' => $this->widget_slug,
				'customize_selective_refresh' => true,
				'description' => __( '显示热门商品，最新商品，购买动态等', 'b2' ),
			)
		);

		//默认设置项
		self::$defaults = array(
			'hot_title' => __('商品聚合','b2'),
			'post_type'  => 'news',
            'post_cat'=>'',
            'product_type'=>'all',
			'order'=>'meta_value_num',
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
				'name'    => __('商品类型','b2'),
				'desc'    => __('将会显示在前端，不设置请留空','b2'),
				'id_key'  => 'product_type',
				'id'      => 'product_type',
				'type'    => 'select',
				'options'=>array(
                    'all'=>__('全部','b2'),
					'normal'=>__('购买','b2'),
					'exchange'=>__('兑换','b2'),
					'lottery'=>__('抽奖','b2')
				)
			),
			array(
				'name'    => __('显示类型','b2'),
				'desc'    => __('将会显示在前端，不设置请留空','b2'),
				'id_key'  => 'post_type',
				'id'      => 'post_type',
				'type'    => 'select',
				'options'=>array(
					'hot'=>__('热门商品','b2'),
					'news'=>__('最新商品','b2'),
					'rand'=>__('随机商品','b2'),
					'buy'=>__('购买动态','b2')
                ),
                'desc'=>__('购买动态不受类型和分类的限制','b2')
			),
			array(
				'name'    => __('分类ID','b2'),
				'desc'    => sprintf(__('请填写商品分类ID，多个请用竖线|隔开。ID之间是或者的关系，比如设置ID为1的商品分类和ID为12的商品分类，写作%s，这里将会显示商品分类是1，或者商品分类是12的商品。留空将显示全部。','b2'),'<code>1|12</code>'),
				'id_key'  => 'post_cat',
				'id'      => 'post_cat',
				'type'    => 'textarea_small'
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
        
		add_shortcode( self::$shortcode, array( __CLASS__, 'b2_widget_hot_products' ) );
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

		if(!empty($widget)) return $widget;

		$instance['post_cat'] = trim($instance['post_cat'], " \t\n\r\0\x0B\xC2\xA0");

        if((int)$instance['count'] < 0 || (int)$instance['count'] > 50) return;
        
        if($instance['post_type'] === 'buy'){
            global $wpdb;
            $table_name = $wpdb->prefix . 'zrz_order';
            $data = $wpdb->get_results($wpdb->prepare( "SELECT * FROM $table_name WHERE (order_type = %s OR order_type = %s OR order_type = %s) AND order_state != %s ORDER BY order_date DESC LIMIT %d", 'c','d','gx','w',$instance['count']),ARRAY_A);
            
			if(!empty($data)){
                $html = '<ul class="b2-widget-buy-ul ">';
                    foreach ($data as $k => $v) {

						$title = get_the_title($v['post_id']);
						$thumb = Cpost::get_post_thumb($v['post_id']);
						$thumb = b2_get_thumb(array('thumb'=>$thumb,'width'=>50,'height'=>50));
						$text = $v['order_type'] === 'gx' ? __('购买','b2') : ($v['order_type'] === 'd' ? __('兑换','b2') : __('抽中','b2'));
						$html .= '<li>
							<div class="buy-news-img">
								'.b2_get_img(array(
									'src'=>$thumb,
									'alt'=>$title,
									'class'=>array('b2-radius')
								)).'
							</div>
							<div class="buy-news-info">
								<p>'.b2_str_encryption(get_the_author_meta('display_name',$v['user_id'])).sprintf(__(' 刚刚 %s 了','b2'),$text).'</p>
								<a href="'.get_permalink($v['post_id']).'" target="_blank">'.$title.'</a>
							</div>
						</li>';
                    }
                $html .= '</ul>';
            }else{
                $html = '<div class="b2-widget-empty">'.__('没有购买动态','b2').'</div>';
            }
        }else{
            $args = array(
                'post_type'=>'shop',
                'posts_per_page'=>$instance['count'] ? (int)$instance['count'] : 6,
				'orderby'=>'date',
				'no_found_rows'=>true
            );

            if($instance['post_cat']){
                $tax_array = explode( '|', $instance['post_cat']);
                
                $arg = array();
                $args['tax_query'] = array(
                    'relation' => 'OR',
                );
                foreach ($tax_array as $k => $v) {
                    $term = get_term( $v );

                    array_push($args['tax_query'], array(
                        'taxonomy' => 'shoptype',
                        'field' => 'term_id',
                        'terms' => $v,
                        'operator'         => 'IN',
                        'include_children' => false,
                    ));
                }
            }
            
            if($instance['product_type'] !== 'all'){
                $args['meta_query'] = array(
                    'relation' => '=',
                );
                array_push($args['meta_query'], array(
                    'key'     => 'zrz_shop_type',
                    'value'   => $instance['product_type'],
                    'compare' => '=',
                ));

            }

            //热门商品
            if($instance['post_type'] === 'hot'){
                $args['orderby'] = 'meta_value_num';
                $args['meta_key'] = 'views';
            }

            //随机商品
            if($instance['post_type'] === 'rand'){
                $args['orderby'] = 'rand';
            }          

            //购买动态
            $the_query = new \WP_Query( $args );

            if ( $the_query->have_posts() ) {

                //获取文章数据
                $html = '<div class="hidden-line"><ul class="b2-widget-list-ul">';
                $_pages = $the_query->max_num_pages;
                $i = 0;

                while ( $the_query->have_posts() ) {

                    $the_query->the_post();
                    
                    $i++;
                    $post_id = $the_query->post->ID;
                    
                    $link = get_permalink();
                    
                    $thumb = b2_get_thumb(array(
                        'thumb'=>Cpost::get_post_thumb($post_id),
                        'width'=>300,
                        'height'=>300
                    ));

                    $data = Shop::get_shop_item_data($post_id,0);

					$title = get_the_title();
                    $html .='<li class="b2-widget-box widget-post">
                        <div class="b2-widget-post-thumb box-in b2-radius">
							<div class="b2-widget-post-thumb-product-img">
							'.b2_get_img(array(
								'src'=>$thumb,
								'alt'=>$title
							)).'
                            '.($data['type'] === 'lottery' ? '<span class="shop-normal-tips">'.__('抽奖','b2').'</span>' : ($data['type'] === 'exchange' ? '<span class="shop-normal-tips">'.__('兑换','b2').'</span>' : '')).'
                            </div>
                            <div class="b2-widget-post-title">
                                <h2>'.$title.'</h2>
                                <div class="products-price '.($data['type'] === 'normal' ? 'red' : 'green').'">
                                '.($data['type'] === 'normal' ? B2_MONEY_SYMBOL : b2_get_icon('b2-coin-line')).'
                                '.$data['price']['current_price'].'
                                </div>
                            </div>
                            <a ref="nofollow" class="link-overlay" href="'.$link.'"></a>
                        </div>
                    </li>';
                
                }
                
				$html .= '</ul></div>';
				
            }else{
                $html = '<div class="b2-widget-empty">'.__('没有任何内容','b2').'</div>';
            }
            wp_reset_postdata();
            
        }
		// 如果 $widget 是空的， 重建缓存
		if ( empty( $widget )) {
			$widget = '';
	
			$widget .= !$instance['show_mobile'] ? str_replace('class="','class="mobile-hidden ',$atts['before_widget']) : $atts['before_widget'];
			$widget .= '<div class="b2-widget-title">';
			$widget .= $atts['before_title']. esc_attr( $instance['hot_title'] ) .$atts['after_title'];
			$widget .= '</div>';
			$widget .= '<div class="b2-widget-box">'.$html.'</div>';
			$widget .= $atts['after_widget'];
			
			if(B2_OPEN_CACHE){
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