<?php namespace B2\Modules\Templates\Widgets;

class Download extends \WP_Widget{

    //小工具slug
	protected $widget_slug = 'b2-widget-download';

    //短代码名
	protected static $shortcode = 'b2_widget_download';

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
			__( 'B2-文章内页下载小工具', 'b2' ),
			array(
				'classname' => $this->widget_slug,
				'customize_selective_refresh' => true,
				'description' => __( '“文章内页下载”小工具', 'b2' ),
			)
		);

		//默认设置项
		self::$defaults = array(
			'show_mobile'=>0
		);

		//设置项
		$this->cmb2_fields = array(
			array(
                'before_row'=>'<p class="red">'.__('文章内页有下载时才显示','b2').'</p>',
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

        $post_id = get_the_ID();

		if(!get_post_meta($post_id,'b2_open_download',true)) return '';

        $data = get_post_meta($post_id,'b2_single_post_download_group',true);
        $data = is_array($data) ? $data : [];

        $count = count($data);

        $gujia = '<div ref="gujia">';

        for ($i=0; $i < $count; $i++) { 
            $gujia .= '<div class="w-d-list gujia">
            <div class="w-d-title">

            </div>
            <div class="w-d-meta">
                <p></p>
                <p></p>
                <p></p>
                <p></p>
            </div>
            <div class="w-d-role">
            </div>
            <div class="w-d-download">
                <button disabled></button>
            </div>
        </div>';
        }

        $gujia .= '</div>';

		$html = '
            '.$gujia.'
            <div class="wdlist" ref="wdlist">
                <div class="w-d-list" v-for="(item,index) in list" :key="index">
                    <div class="w-d-title">
                        <h2 v-text="item.name"></h2>
                        <a :href="item.view" target="_blank" class="download-view button empty text" v-if="item.view">'.__('查看演示','b2').'</a>
                    </div>
                    <div class="w-d-meta">
                        <p v-for="(m,_index) in item.attrs" :key="_index">
                            <span>{{m.name}}</span>：<span>{{m.value}}</span>
                        </p>
                    </div>
                    <div :class="\'w-d-role b2-radius \' + (item.current_user.can.allow ? \'d-allow\' : \'\')">
                        <div><span>'.__('您的下载权限','b2').'</span><span @click="show(index)">'.__('查看全部权限','b2').'</span></div>
                        <ul v-if="item.show">
                            <li v-for="right in item.rights" :class="right.lv == item.current_user.lv.lv.lv || right.lv == item.current_user.lv.vip.lv ? \'red\' : \'\'">
                                <div>
                                    <span v-text="right.lv_name"></span>
                                </div>
                                <div v-if="right.type == \'money\'">'.B2_MONEY_SYMBOL.'<span v-text="right.value"></span></div>
                                <div v-if="right.type == \'credit\'">'.b2_get_icon('b2-coin-line').'<span v-text="right.value"></span></div>
                                <div v-if="right.type == \'free\'">'.__('免费下载','b2').'</div>
                                <div v-if="right.type == \'full\'">'.__('今天剩余0次下载','b2').'</div>
                                <div v-if="right.type == \'comment\'">'.__('评论后下载','b2').'</div>
                                <div v-if="right.type == \'login\'">'.__('登录后下载','b2').'</div>
                            </li>
                        </ul>
                        <div class="w-d-current" v-if="!item.current_user.can.allow">
                            <div>
                                <span v-if="item.current_user.lv.lv" v-html="item.current_user.lv.lv.icon"></span>
                                <span v-if="item.current_user.lv.vip" v-html="item.current_user.lv.vip.icon"></span>
                            </div>
                            <span v-if="item.current_user.can.type == \'login\'">
                            '.__('登录后下载：','b2').'<a href="javascript:void(0)" onclick="login.show = true;login.loginType = 1">'.__('登录','b2').'</a>
                            </span>
                            <span v-else-if="item.current_user.lv.lv.lv == \'dark_room\'">
                            '.__('小黑屋反思中！','b2').'
                            </span>
                            <span v-else-if="item.current_user.can.type == \'comment\'">
                            '.__('评论后下载','b2').'<a href="#respond">'.__('评论','b2').'</a>
                            </span>
                            <span v-else-if="item.current_user.lv.lv.lv == \'guest\' && !item.current_user.guest">
                                <span v-show="list[index].rights[0].lv == \'all\'" v-cloak><b><template v-if="item.current_user.can.type == \'credit\'">'.b2_get_icon('b2-coin-line').'</template><template v-else>'.B2_MONEY_SYMBOL.'</template><i v-html="list[index].current_user.can.value"></i></b></span>
                                <a href="javascript:void(0)" onclick="login.show = true;login.loginType = 1">'.__('请先登录','b2').'</a>
                            </span>
                            <span v-else-if="item.current_user.can.type == \'full\'" class="green">
                                '.__('今天剩余0次下载','b2').'
                            </span>
                            <span v-else-if="item.current_user.can.type == \'credit\'">
                                '.b2_get_icon('b2-coin-line').'<b><i v-html="list[index].current_user.can.value"></i></b>
                            </span>
                            <span v-else-if="item.current_user.can.type == \'money\'">
                                '.B2_MONEY_SYMBOL.'<b v-text="list[index].current_user.can.value"></b>
                            </span>
                            <span v-else>
                                <a href="'.b2_get_custom_page_url('vips').'" target="_blank">'.__('升级会员','b2').'</a>
                            </span>
                        </div>
                        <div class="w-d-current can-down" v-else>
                            <span v-if="item.current_user.current_guest == 0 || item.current_user.can.free_down" class="green">
                                '.__('您已获得下载权限','b2').'
                            </span>
                            <span v-else>'.sprintf(__('今日还有%s次免费下载','b2'),'{{item.current_user.can.count}}').'</span>
                        </div>
                    </div>
                    <div class="w-d-download">
                        <span v-for="b in item.button"><button @click="go(b.link,item.current_user.can.allow,item,index)" class="button" v-text="b.name"></button></span>
                    </div>
                </div>
            </div>
		';
		
        $widget = '';

        $widget .= !$instance['show_mobile'] ? str_replace('class="','class="mobile-hidden ',$atts['before_widget']) : $atts['before_widget'];

        $widget .= '<div class="b2-widget-box">'.$html.'</div>';

        $widget .= $atts['after_widget'];
			
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