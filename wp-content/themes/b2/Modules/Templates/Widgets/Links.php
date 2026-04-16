<?php namespace B2\Modules\Templates\Widgets;

class Links extends \WP_Widget{

    //小工具slug
	protected $widget_slug = 'b2-widget-links';

    //短代码名
	protected static $shortcode = 'b2_widget_links';

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
			__( 'B2-连接组', 'b2' ),
			array(
				'classname' => $this->widget_slug,
				'customize_selective_refresh' => true,
				'description' => __( '“连接组”小工具', 'b2' ),
			)
		);

        $img = B2_THEME_URI.'/Assets/fontend/images/tools';
        $links_arg = '
优惠活动|#|查看本站的最新优惠|<img src="'.$img.'/youhui.png" />|0
配件商城|#|在线购买XX配件|<img src="'.$img.'/kuozhan.png" />|0
法律声明|#|本站的法律声明|<img src="'.$img.'/wendang.png" />|0
在线工单|#|提交在线工单|<img src="'.$img.'/gongdan.png" />|0
建议提交|#|查看本主题的文档|<img src="'.$img.'/jianyi.png" />|0
';

		//默认设置项
		self::$defaults = array(
			'links_title'=>__('联系与合作','b2'),
			'links_arg'  => trim($links_arg),
			'show_mobile'=>0
		);

		//设置项
		$this->cmb2_fields = array(
			array(
				'name'   => __('标题','b2'),
				'id_key' => 'links_title',
				'id'     => 'links_title',
				'type'   => 'text'
            ),
			array(
				'name'   => __('连接组','b2'),
				'id_key' => 'links_arg',
                'id'     => 'links_arg',
                'desc' => sprintf(__('请填写连接数据,格式为%s比如： %s（每组数据占一行）','b2'),'<code>参数1（连接名称）|参数2（连接地址）|参数3（连接描述）|参数4（连接图标）|参数5（是否新窗口打开）</code><br>','<br><code>优惠活动|https://xxx.com/about|查看本站的最新优惠|'.htmlspecialchars('<img src="xx.png"/>').'|0</code>'),
				'type'   => 'textarea'
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

        $list = explode(PHP_EOL,$instance['links_arg']);
        $arr = array();
        foreach($list as $k=>$v){
            $line = explode('|',$v);
            $arr[] = array(
                'title'=>isset($line[0]) ? $line[0] : '',
                'link'=>isset($line[1]) ? $line[1] : '',
                'desc'=>isset($line[2]) ? $line[2] : '',
                'icon'=>isset($line[3]) ? $line[3] : '',
                'open'=>isset($line[4]) ? $line[4] : '',
            );
        }

        $html = '<ul class="links-widget">';
        foreach($arr as $_k=>$v){
            $html .= '
                <li>
                    <a '.($v['open'] == 1 ? 'target="__blank"' : '').' class="link-block" href="'.$v['link'].'"></a>
                    '.($v['icon'] ? '<div class="links-icon">'.$v['icon'].'</div>' : '').'
                    <div>
                        <h2>'.$v['title'].'</h2>
                        <p>'.$v['desc'].'</p>
                    </div>
                </li>
            ';
        }
        $html .= '</ul>';

		// 如果 $widget 是空的， 重建缓存
		if ( empty( $widget )) {
			$widget = '';
	
			$widget .= !$instance['show_mobile'] ? str_replace('class="','class="mobile-hidden ',$atts['before_widget']) : $atts['before_widget'];
			$widget .= '<div class="b2-widget-title">';
			$widget .= $atts['before_title']. esc_attr( $instance['links_title'] ) .$atts['after_title'];
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