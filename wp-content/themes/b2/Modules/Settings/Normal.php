<?php
namespace B2\Modules\Settings;

use B2\Modules\Common\User;

class Normal{

    //默认设置项
    public static $default_settings = array(
        'audit_mode'=>0,
        'restapi_mode'=>0,
        'open_cache'=>1,
        'allow_register'=>1,
        'allow_cookie'=>0,
        'img_logo'=>'',
        'img_logo_white'=>'',
        'text_logo'=>'',
        'logo_width'=>'120',
        'default_video_poster'=>'',
        'separator'=>'-',
        'money_symbol'=>'￥',
        'money_name'=>'余额',
        'home_keywords'=>'',
        'home_description'=>'',
        'header_code'=>'',
        'footer_code'=>'',
        'check_type'=>'text',
        'build_phone_email'=>0,
        'phome_select'=>'aliyun',
        'accesskey_id'=>'',
        'access_key_secret'=>'',
        'sign_name'=>'',
        'template_code'=>'',
        'apikey'=>'',
        'yunpian_text'=>'',
        'tpl_id'=>'',
        'juhe_key'=>'',
        'site_key'=>'',
        'api_key'=>'',
        'zz_id'=>'',
        'zz_password'=>'',
        'tencent_id'=>'',
        'tencent_SmsSdkAppid'=>'',
        'tencent_appkey'=>'',
        'tencent_Sign'=>'',
        'others_url'=>'',
        'others_back'=>'',
        'login_keep'=>7,
        //赛邮相关
        'saiyou_app_id'=>'',
        'saiyou_app_key'=>'',
        'saiyou_project'=>'',
        'saiyou_sign_type'=>'',
        //用户相关设置
        'user_avatar_open'=>1,
        'user_avatar_letter'=>1,
        'user_id_decode'=>0,
        'user_slug'=>'users',
        //创作设置
        'write_webp'=>1,
        'write_allow'=>1,
        'write_image_size'=>5,
        'write_video_size'=>50,
        'write_file_size'=>30,
        'write_image_webp'=>1,
        'write_image_crop'=>1,
        'write_image_lazyload'=>1,
        /*安全*/
        'allow_baidu_safe'=>0,
        'baidu_appId'=>'',
        'baidu_apiKey'=>'',
        'baidu_secretKey'=>'',
        'repo_count'=>10,
        'repo_time'=>3,
        'post_count'=>20,
        'upload_count'=>20,
        'register_count'=>5,
        'back_room'=>0.042,
        'clean_message'=>0,
        'aliyun_cdn_jianquan'=>0,
        'aliyun_cdn_jianquan_key'=>'',
        /*自定义连接和名称*/
        'custom_collection_name'=>'专题',
        'custom_collection_link'=>'collection',
        'custom_circle_name'=>'圈子',
        'custom_circle_owner_name'=>'圈主',
        'custom_circle_member_name'=>'圈友',
        'custom_circle_link'=>'circle',
        'custom_shop_name'=>'商铺',
        'custom_shop_link'=>'shop',
        'custom_newsflashes_name'=>'快讯',
        'custom_newsflashes_link'=>'newsflashes',
        'custom_document_name'=>'文档',
        'custom_document_link'=>'document',
        'custom_announcement_name'=>'公告',
        'custom_announcement_link'=>'announcement',
        'custom_links_name'=>'网址导航',
        'custom_links_link'=>'links',
        'custom_infomation_name'=>'供求信息',
        'custom_infomation_link'=>'infomation',
        'custom_infomation_for'=>'供',
        'custom_infomation_get'=>'求',
        'custom_ask_name'=>'问答',
        'custom_ask_link'=>'ask',
        'custom_ask_cat_name'=>'板块',
        'custom_answer_name'=>'回答',
        //社交登录
        'site_privacy'=>'',
        'site_terms'=>'',
        'wx_pc_secret'=>'',
        'wx_pc_key'=>'',
        'wx_gz_key'=>'',
        'wx_gz_secret'=>'',
        'qq_id'=>'',
        'qq_secret'=>'',
        'weibo_key'=>'',
        'weibo_secret'=>'',
        'juhe_open'=>0,
        'juhe_url'=>'',
        'juhe_appid'=>'',
        'juhe_appkey'=>'',
        'juhe_types'=>'',
        'wx_pc_open'=>0,
        'wx_gz_open'=>0,
        'qq_open'=>0,
        'weibo_open'=>0,
        'remove_category_tag'=>0,
        'wx_mp_login'=>0,
        'wx_mp_login_text'=>'',
        //积分奖励
        'credit_login'=>160,
        'credit_post'=>100,
        'credit_topic'=>50,
        'credit_infomation'=>50,
        'credit_comment'=>20,
        'credit_comment_up'=>5,
        'credit_post_comment'=>5,
        'credit_follow'=>5,
        'credit_post_up'=>10,
        'credit_qd'=>'50-200',
        'card_allow'=>1,
        'card_text'=>'',
        'credit_dh'=>38,
        'credit_qc'=>10,
        'credit_newsflashes'=>20,
        'tk_bs'=>3,
        //任务
        'task_post'=>2,
        'task_infomation'=>2,
        'task_circle'=>2,
        'task_post_vote'=>4,
        'task_newsflashes'=>5,
        'task_comment'=>4,
        'task_post_comment'=>4,
        'task_comment_vote'=>6,
        'task_follow'=>2,
        'task_fans'=>2,
        'task_user_qq'=>100,
        'task_user_weixin'=>100,
        'task_user_weibo'=>100,
        'task_user_verify'=>300,
        'task_mission_task'=>'3|100',
        'wx_mp_in_login'=>0,
        //PayPal支付
        'paypal_username'=>'',
        'paypal_password'=>'',
        'paypal_signature'=>'',
        'paypal_sandbox'=>0,
        'paypal_rate'=>1,
        'paypal_open'=>0,
        'paypal_currency_code'=>'USD',
        'default_imgs'=>[],
    );

    public function init(){
        add_action('cmb2_admin_init',array($this,'normal_options_page'));
    }

    /**
     * 获取默认设置项
     *
     * @param string $key 数组键值
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function get_default_settings($key){
        $arr = array(
            'text_logo'=>B2_BLOG_NAME,
            'default_imgs'=>array(B2_DEFAULT_IMG),
            'img_logo_white'=>'',
            'default_video_poster'=>B2_THEME_URI.'/Assets/fontend/images/xg-poster-default.jpg',
            'card_text'=>sprintf(__('请前往%s购买卡密，然后回到此处进行充值','b2'),'<a href="#" target="_balnk">购买卡密</a>'),
            'zz_temp'=>'',
            'register_msg'=>__('欢迎您来到本站！','b2'),
            'default_user_cover'=>B2_THEME_URI.'/Assets/fontend/images/task_bg.jpg',
            'default_user_avatar'=>B2_DEFAULT_AVATAR,
            'default_box_bg'=>B2_THEME_URI.'/Assets/fontend/images/model-bg.png'
        );

        if($key == 'all'){
            return $arr;
        }

        if(isset($arr[$key])){
            return $arr[$key];
        }
    }

    /**
     * 常规设置
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public function normal_options_page(){

        $normal = new_cmb2_box( array(
            'id'           => 'b2_normal_main_options_page',
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_normal_main',
            'tab_group'    => 'b2_normal_options',
            'parent_slug'     => 'b2_main_options',
            'tab_title'    => __('综合设置','b2'),
            'menu_title'   => __('常规设置','b2'),
        ) );

        $normal->add_field(array(
            'name'    => __( '是否开启简洁模式', 'b2' ),
            'desc'    => __( '如果您不需要站点有类似，登陆、注册、评论等交互功能，可以开启简洁模式，将只显示文章，没有交互。', 'b2' ),
            'id'=>'audit_mode',
            'type'             => 'select',
            'default'          => self::$default_settings['audit_mode'],
            'options'          => array(
                1 => __( '开启简洁模式', 'b2' ),
                0   => __( '关闭简洁模式', 'b2' ),
            ),
        ));

        // $normal->add_field(array(
        //     'name'    => __( 'Rest api 兼容模式（一般情况下请保持关闭即可）', 'b2' ),
        //     'desc'    => __( '如果您的某些插件在B2主题的 rest api 请求中无效（一般很少能遇到），可以开启此兼容模式尝试是否正常，如果依然无效，请保持此开关的关闭状态再找其他原因。此选项关闭，可以让B2主题的 rest api 请求速度提高30%。', 'b2' ),
        //     'id'=>'restapi_mode',
        //     'type'             => 'select',
        //     'default'          => self::$default_settings['restapi_mode'],
        //     'options'          => array(
        //         1 => __( '开启Rest api 兼容模式', 'b2' ),
        //         0   => __( '关闭Rest api 兼容模式', 'b2' ),
        //     ),
        // ));

        if(wp_using_ext_object_cache()){
            //开启自带缓存开关
            $normal->add_field(array(
                'name'    => __( '开启主题自带缓存', 'b2' ),
                'desc'    => sprintf(__( '当您使用了redis 或者 memcached 这类数据缓存时，主题将自动对查询和计算量比较大的代码进行结果的缓存%s实际使用过程中建议开启。%s调试主题，修改代码等情况下建议关闭,修改完成即可开启（修改代码以后请在缓存插件中清空一下缓存）', 'b2' ),'<br/>','<br/>'),
                'id'=>'open_cache',
                'type'             => 'select',
                'default'          => self::$default_settings['open_cache'],
                'options'          => array(
                    1 => __( '开启', 'b2' ),
                    0   => __( '关闭', 'b2' ),
                ),
            ));
        }

        $normal->add_field(array(
            'name'    => __( '开启主题自带SEO', 'b2' ),
            'desc'    => __( '如果您需要使用某些SEO插件，请关闭主题自带的SEO功能，以免产生冲突', 'b2' ),
            'id'=>'open_seo',
            'type'             => 'select',
            'default'          => 0,
            'options'          => array(
                0 => __( '开启', 'b2' ),
                1   => __( '关闭', 'b2' ),
            ),
        ));

        //logo设置
        $normal->add_field( array(
            'name'    => __( '图片LOGO（深色）', 'b2' ),
            'desc'    => sprintf(__( '某些顶部背景为白色的情况下使用，建议使用%s格式的图片，以适应高分辨率屏幕。%s如果不设置图片LOGO，网站将显示下面设置的文字LOGO。', 'b2' ),'<code>.svg</code>','<br>'),
            'id'      => 'img_logo',
            'type'    => 'file',
            'options' => array(
                'url' => true, 
            ),
            'text'    => array(
                'add_upload_file_text' => __( '选择LOGO', 'b2' ),
            ),
            'query_args' => array(
                'type' => array(
                    'image/svg+xml',
                    'image/gif',
                    'image/jpeg',
                    'image/png',
                ),
            ),
            'default'=>self::$default_settings['img_logo']
        ));

        //logo设置
        $normal->add_field( array(
            'name'    => __( '图片LOGO（浅色）', 'b2' ),
            'desc'    => sprintf(__( '某些顶部透明的风格下使用，也会视频播放器右上角显示，建议使用%s格式的图片，以适应高分辨率屏幕。%s如果不设置图片LOGO，网站将显示下面设置的文字LOGO。', 'b2' ),'<code>.svg</code>','<br>'),
            'id'      => 'img_logo_white',
            'type'    => 'file',
            'options' => array(
                'url' => true, 
            ),
            'text'    => array(
                'add_upload_file_text' => __( '选择LOGO', 'b2' ),
            ),
            'query_args' => array(
                'type' => array(
                    'image/svg+xml',
                    'image/gif',
                    'image/jpeg',
                    'image/png',
                ),
            ),
            'default'=>self::$default_settings['img_logo_white']
        ));

        //文字LOGO
        $normal->add_field(array(
            'name'    => __( '文字LOGO', 'b2' ),
            'desc'    => __( '默认为您的站点名，您也可以自定义该名称，只在页面顶部显示。', 'b2' ),
            'id'=>'text_logo',
            'type'=>'text',
            'default'=>B2_BLOG_NAME
        ));

        //随机缩略图
        $normal->add_field( array(
            'name'    => __( '默认缩略图', 'b2' ),
            'desc'    => __( '可以设置多个默认缩略图，当您的文章没有指定缩略图，并且文章内部没有图片的时候，随机显示这些缩略图。', 'b2' ),
            'id'      => 'default_imgs',
            'type'    => 'file_list',
            'text'    => array(
                'add_upload_file_text' => __( '选择图片', 'b2' ),
            ),
            'preview_size' => array( 100, 100 ), // Default: array( 50, 50 )
            'query_args' => array(
                'type' => 'image',
            ),
            'default'=>self::$default_settings['default_imgs']
        ));

        //播放器默认缩略图
        $normal->add_field( array(
            'name'    => __( '视频播放器默认缩略图', 'b2' ),
            'desc'    => __( '当您未设置视频封面的时候默认显示此封面。', 'b2' ),
            'id'      => 'default_video_poster',
            'type'    => 'file',
            'options' => array(
                'url' => true, 
            ),
            'text'    => array(
                'add_upload_file_text' => __( '选择图片', 'b2' ),
            ),
            'query_args' => array(
                'type' => array(
                    'image/svg+xml',
                    'image/gif',
                    'image/jpeg',
                    'image/png',
                ),
            ),
            'default'=>self::$default_settings['default_video_poster']
        ));

        //播放器默认缩略图
        $normal->add_field( array(
            'name'    => __( '弹窗背景图', 'b2' ),
            'desc'    => __( '登陆、注册、支付等弹窗顶部有一个背景图片，您可以在此更换此图片。', 'b2' ),
            'id'      => 'default_box_bg',
            'type'    => 'file',
            'options' => array(
                'url' => true, 
            ),
            'text'    => array(
                'add_upload_file_text' => __( '选择图片', 'b2' ),
            ),
            'query_args' => array(
                'type' => array(
                    'image/svg+xml',
                    'image/gif',
                    'image/jpeg',
                    'image/png',
                ),
            ),
            'default'=>self::get_default_settings('default_box_bg')
        ));

        //货币符号
        $normal->add_field(array(
            'name'    => __( '网站代币符号', 'b2' ),
            'desc'    => sprintf(__( '网站的代币符号，默认%s。', 'b2' ),'<code>K</code>'),
            'id'=>'money_symbol',
            'type'=>'text',
            'default'=>self::$default_settings['money_symbol']
        ));

        //货币名称
        $normal->add_field(array(
            'name'    => __( '网站货币名称', 'b2' ),
            'desc'    => sprintf(__( '比如%s等，或者其他您觉得符合您站点气质的名称。', 'b2' ),'<code>'.__('代币','b2').'</code>'.'<code>'.__('余额','b2').'</code>'),
            'id'=>'money_name',
            'type'=>'text',
            'default'=>self::$default_settings['money_name']
        ));

        //网站连接符
        $normal->add_field(array(
            'name'    => __( '网站连接符', 'b2' ),
            'desc'    => sprintf(__( '标题与描述之间的分隔符，默认%s。', 'b2' ),'<code>-</code>'),
            'id'=>'separator',
            'type'=>'text',
            'default'=>self::$default_settings['separator']
        ));

        //开启自带缓存开关
        $normal->add_field(array(
            'name'    => __( '分类目录是否去掉category标签', 'b2' ),
            'desc'    => sprintf(__('如果您之前的分类链接里面有%s标签，此时去掉可能影响之前的收录。%s设置完成以后，请重新保存一下固定链接'),'<code>category</code>','<br>'),
            'id'=>'remove_category_tag',
            'type'             => 'select',
            'default'          => 1,
            'options'          => array(
                1 => __( '去掉category', 'b2' ),
                0   => __( '保留category', 'b2' ),
            ),
        ));

        //首页SEO关键词
        $normal->add_field(array(
            'name'=>__('首页SEO关键词','b2'),
            'desc'=>sprintf(__( '建议使用英文的%s隔开，一般3-5个关键词即可，多了会有堆砌嫌疑。', 'b2' ),'<code>,</code>'),
            'id'=>'home_keywords',
            'type'=>'text',
            'default'=>self::$default_settings['home_keywords']
        ));

        //首页SEO描述
        $normal->add_field(array(
            'name'=>__('首页SEO描述','b2'),
            'desc'=>__( '描述你站点的主营业务，一般不超过200个字。', 'b2' ),
            'id'=>'home_description',
            'type'=>'textarea_small',
            'default'=>self::$default_settings['home_description']
        ));

        //头部HTML标签
        $normal->add_field(array(
            'name'=>__('头部HTML标签','b2'),
            'desc'=>sprintf(__( '你可以添加站点的%s等标签，通常情况下，这里是用来放置第三方台验证站点所有权时使用的。', 'b2' ),'<code>'.htmlspecialchars('<meta>、<link>、<style>、<script>').'</code>'),
            'id'=>'header_code',
            'type'=>'textarea_code',
            'options' => array( 'disable_codemirror' => true ),
            'default'=>self::$default_settings['header_code']
        ));

        //底部HTML标签
        $normal->add_field(array(
            'name'=>__('底部HTML标签','b2'),
            'desc'=>sprintf(__( '你可以添加站点的%s等标签，通常情况下，这里是用来加载额外的JS、css文件，或者放置统计代码。', 'b2' ),'<code>'.htmlspecialchars('<style>、<script>').'</code>'),
            'id'=>'footer_code',
            'type'=>'textarea_code',
            'options' => array( 'disable_codemirror' => true ),
            'default'=>self::$default_settings['footer_code']
        ));

        $custom = new_cmb2_box(array(
            'id'           => 'b2_normal_custom_options_page',
            'tab_title'    => __('名称及连接','b2'), 
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_normal_custom',
            'parent_slug'     => 'admin.php?page=b2_normal_main',
            'tab_group'    => 'b2_normal_options',
        ));

        $custom->add_field(array(
            'before_row'=>'<h2>特别注意：</h2><p>1、更改此处设置，页面的网址和标题会跟着变化，对于老站来说可能会短时间内影响您之前的收录，新站可以根据自己的需求更改</p><p>3、更改完成务必重新保存一下固定连接以刷新缓存：<a href="'.admin_url('/options-permalink.php').'" target="_blank">固定连接</a></p><p>3、如果变更未生效，请刷新各种缓存，包括CDN、数据库缓存和各种缓存插件</p><p>4、小工具、菜单处的名称和连接请手动修改</p><h2>自定义专题</h2>',
            'name'=>__('【专题】名称','b2'),
            'id'=>'custom_collection_name',
            'type'=>'text',
            'default'=>self::$default_settings['custom_collection_name']
        ));

        $custom->add_field(array(
            'name'=>__('【专题】连接别名','b2'),
            'id'=>'custom_collection_link',
            'type'=>'text',
            'default'=>self::$default_settings['custom_collection_link']
        ));

        $custom->add_field(array(
            'before_row'=>'<h2>自定义圈子</h2>',
            'name'=>__('【圈子】名称','b2'),
            'id'=>'custom_circle_name',
            'type'=>'text',
            'default'=>self::$default_settings['custom_circle_name']
        ));

        $custom->add_field(array(
            'name'=>__('【圈主】名称','b2'),
            'id'=>'custom_circle_owner_name',
            'type'=>'text',
            'default'=>self::$default_settings['custom_circle_owner_name']
        ));

        $custom->add_field(array(
            'name'=>__('【圈友】名称','b2'),
            'id'=>'custom_circle_member_name',
            'type'=>'text',
            'default'=>self::$default_settings['custom_circle_member_name']
        ));

        $custom->add_field(array(
            'name'=>__('【圈子】连接别名','b2'),
            'id'=>'custom_circle_link',
            'type'=>'text',
            'default'=>self::$default_settings['custom_circle_link']
        ));

        $custom->add_field(array(
            'before_row'=>'<h2>自定义商铺</h2>',
            'name'=>__('【商铺】名称','b2'),
            'id'=>'custom_shop_name',
            'type'=>'text',
            'default'=>self::$default_settings['custom_shop_name']
        ));

        $custom->add_field(array(
            'name'=>__('【商铺】连接别名','b2'),
            'id'=>'custom_shop_link',
            'type'=>'text',
            'default'=>self::$default_settings['custom_shop_link']
        ));

        $custom->add_field(array(
            'before_row'=>'<h2>自定义快讯</h2>',
            'name'=>__('【快讯】名称','b2'),
            'id'=>'custom_newsflashes_name',
            'type'=>'text',
            'default'=>self::$default_settings['custom_newsflashes_name']
        ));

        $custom->add_field(array(
            'name'=>__('【快讯】连接别名','b2'),
            'id'=>'custom_newsflashes_link',
            'type'=>'text',
            'default'=>self::$default_settings['custom_newsflashes_link']
        ));

        $custom->add_field(array(
            'before_row'=>'<h2>自定义文档</h2>',
            'name'=>__('【文档】名称','b2'),
            'id'=>'custom_document_name',
            'type'=>'text',
            'default'=>self::$default_settings['custom_document_name']
        ));

        $custom->add_field(array(
            'name'=>__('【文档】连接别名','b2'),
            'id'=>'custom_document_link',
            'type'=>'text',
            'default'=>self::$default_settings['custom_document_link']
        ));

        $custom->add_field(array(
            'before_row'=>'<h2>自定义公告</h2>',
            'name'=>__('【公告】名称','b2'),
            'id'=>'custom_announcement_name',
            'type'=>'text',
            'default'=>self::$default_settings['custom_announcement_name']
        ));

        $custom->add_field(array(
            'name'=>__('【公告】连接别名','b2'),
            'id'=>'custom_announcement_link',
            'type'=>'text',
            'default'=>self::$default_settings['custom_announcement_link']
        ));

        $custom->add_field(array(
            'before_row'=>'<h2>自定义网址导航</h2>',
            'name'=>__('【网址导航】名称','b2'),
            'id'=>'custom_links_name',
            'type'=>'text',
            'default'=>self::$default_settings['custom_links_name']
        ));

        $custom->add_field(array(
            'name'=>__('【网址导航】连接别名','b2'),
            'id'=>'custom_links_link',
            'type'=>'text',
            'default'=>self::$default_settings['custom_links_link']
        ));

        $custom->add_field(array(
            'before_row'=>'<h2>供求信息</h2>',
            'name'=>__('【供求信息】名称','b2'),
            'id'=>'custom_infomation_name',
            'type'=>'text',
            'default'=>self::$default_settings['custom_infomation_name']
        ));

        $custom->add_field(array(
            'name'=>__('【供求信息】连接别名','b2'),
            'id'=>'custom_infomation_link',
            'type'=>'text',
            'default'=>self::$default_settings['custom_infomation_link']
        ));

        $custom->add_field(array(
            'name'=>__('【供求信息】中的<供>自定义名称','b2'),
            'id'=>'custom_infomation_for',
            'type'=>'text',
            'default'=>self::$default_settings['custom_infomation_for']
        ));

        $custom->add_field(array(
            'name'=>__('【供求信息】中的<求>自定义名称','b2'),
            'id'=>'custom_infomation_get',
            'type'=>'text',
            'default'=>self::$default_settings['custom_infomation_get']
        ));

        $custom->add_field(array(
            'before_row'=>'<h2>问答</h2>',
            'name'=>__('【问答】名称','b2'),
            'id'=>'custom_ask_name',
            'type'=>'text',
            'default'=>self::$default_settings['custom_ask_name']
        ));

        $custom->add_field(array(
            'name'=>__('【问答板块】名称','b2'),
            'id'=>'custom_ask_cat_name',
            'type'=>'text',
            'default'=>self::$default_settings['custom_ask_cat_name']
        ));

        $custom->add_field(array(
            'name'=>__('【问题回答】名称','b2'),
            'id'=>'custom_answer_name',
            'type'=>'text',
            'default'=>self::$default_settings['custom_answer_name']
        ));

        $custom->add_field(array(
            'name'=>__('【问答】连接别名','b2'),
            'id'=>'custom_ask_link',
            'type'=>'text',
            'default'=>self::$default_settings['custom_ask_link']
        ));

        $safe = new_cmb2_box(array(
            'id'           => 'b2_normal_safe_options_page',
            'tab_title'    => __('安全设置','b2'), 
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_normal_safe',
            'parent_slug'     => 'admin.php?page=b2_normal_main',
            'tab_group'    => 'b2_normal_options',
        ));

        $safe->add_field(array(
            'name'    => __( '是否开启百度文本审查功能', 'b2' ),
            'desc'    => sprintf(__( '站点中所有用户需要输入的文本都将经过AI文本审查功能进行过滤，避免涉黄、涉敏感、涉广告等违规内容的发布。%s1、请前往%s百度AI开放平台%s开通账户。%s2、控制面板的【内容审核】中创建应用，只勾选内容审核相关的。%s3、将生成的appid，API Key，Secret Key填入下方。%s免费用户拥有5万次的免费API调用，每秒并发2QPS', 'b2' )
            ,'<br>','<a href="https://ai.baidu.com/" target="_blank">','</a>','<br>','<br>','<br>'),
            'id'=>'allow_baidu_safe',
            'type'             => 'select',
            'default'          => self::$default_settings['allow_baidu_safe'],
            'options'          => array(
                1 => __( '开启', 'b2' ),
                0   => __( '关闭', 'b2' ),
            ),
        ));

        $safe->add_field(array(
            'name'    => __( 'AppId', 'b2' ),
            'desc'    => __( '百度智能云控制面板->内容审核->你创建的应用->AppId', 'b2' ),
            'id'=>'baidu_appId',
            'type'             => 'text',
            'default'          => self::$default_settings['baidu_appId'],
        ));

        $safe->add_field(array(
            'name'    => __( 'API Key', 'b2' ),
            'desc'    => __( '百度智能云控制面板->内容审核->你创建的应用->API Key', 'b2' ),
            'id'=>'baidu_apiKey',
            'type'             => 'text',
            'default'          => self::$default_settings['baidu_apiKey'],
        ));

        $safe->add_field(array(
            'name'    => __( 'Secret Key', 'b2' ),
            'desc'    => __( '百度智能云控制面板->内容审核->你创建的应用->Secret Key', 'b2' ),
            'id'=>'baidu_secretKey',
            'type'             => 'text',
            'default'          => self::$default_settings['baidu_secretKey'],
        ));

        //限制操作频次
        $safe->add_field(array(
            'before_row'=>sprintf(__('%s限制操作频次（需要开启了redis 或者 memcached）%s%s此功能用来防止某些人通过接口批量对数据进行操作，比如批量发送垃圾信息，批量下载，批量硬解密码等操作%s%s如果您不知道下面的设置项是什么意思，请保持默认即可%s','b2'),'<h2>','</h2>','<p>','</p>','<p>','</p>'),
            'name'    => __( '记录时间段', 'b2' ),
            'desc'    => __( '记录这个时间段的用户操作，如果这个时间段用户操作次数超过某个数值，将会触发限制。请直接填写数字，比如记录10秒内的操作，直接填写数字10', 'b2' ),
            'id'=>'repo_count',
            'type'             => 'text',
            'default'          => self::$default_settings['repo_count'],
        ));

        $safe->add_field(array(
            'name'    => __( '操作频次', 'b2' ),
            'desc'    => __( '上面时间段内用户操作的频次超过多少次以后触发限制。比如：上面时间段填的是10秒，这里操作频次填的是5次，则代表，用户10秒内操作某个数据超过5次以后触发限制。', 'b2' ),
            'id'=>'repo_time',
            'type'             => 'text',
            'default'          => self::$default_settings['repo_time'],
        ));

        $safe->add_field(array(
            'name'    => __( '加入小黑屋的时间', 'b2' ),
            'desc'    => __( '通过上面的机制触发限制，这个限制是将此用户加入小黑屋。请在这里填写加入小黑屋的时间，单位是天。如果只想将他加入小黑屋1个小时，那么就是1/24=0.042，直接填写0.042即可。', 'b2' ),
            'id'=>'back_room',
            'type'             => 'text',
            'default'          => self::$default_settings['back_room'],
        ));

        //限制提交数据总量
        $safe->add_field(array(
            'before_row'=>sprintf(__('%s限制3小时内提交数据总量（需要开启了redis 或者 memcached）%s%s此功能用来防止某些人通过接口批量对数据进行操作，比如批量发送垃圾信息，批量上传，批量硬解密码等操作%s%s如果您不知道下面的设置项是什么意思，请保持默认即可。（管理员不受此限制）%s','b2'),'<h2>','</h2>','<p>','</p>','<p>','</p>'),
            'name'    => __( '3小时内发布文章、评论、快讯、帖子等总量', 'b2' ),
            'desc'    => __( '直接填写数字，比如20', 'b2' ),
            'id'=>'post_count',
            'type'             => 'text',
            'default'          => self::$default_settings['post_count'],
        ));

        $safe->add_field(array(
            'name'    => __( '3小时内上传文件的总量', 'b2' ),
            'desc'    => __( '文件包含图片、视频、或者其他wp允许上传的文件类型，3小时内超过此数量，将不再允许上传。请直接填写数字，比如20', 'b2' ),
            'id'=>'upload_count',
            'type'             => 'text',
            'default'          => self::$default_settings['upload_count'],
        ));

        $safe->add_field(array(
            'name'    => __( '同一个IP，1小时内限制注册多少次', 'b2' ),
            'desc'    => __( '超过次数将不再允许注册，此功能可防止有人使用临时邮箱批量注册的现象', 'b2' ),
            'id'=>'register_count',
            'type'             => 'text',
            'default'          => self::$default_settings['register_count'],
        ));


        $safe->add_field(array(
            'before_row'=>sprintf(__('%s消息表自动清理%s%s消息表用来记录用户的消息数据，包括积分和%s记录，用户比较多的时候数据量可能会很大，如果消息表（zrz_message）数据比较庞大，可根据自身情况设置清理多少天前的数据。%s','b2'),'<h2>','</h2>','<p>',B2_MONEY_NAME,'</p>'),
            'name'    => __( '清理多少天之前的数据', 'b2' ),
            'desc'    => __( '请直接填写天数数字。设置好之后，您需要点击主题激活按钮，才会执行。之后每次激活都会检查过期数据，并清理。如果不需要清理，请填0。', 'b2' ),
            'id'=>'clean_message',
            'type'             => 'text',
            'default'          => self::$default_settings['clean_message'],
        ));

        //OSS_CDN鉴权
        // $safe->add_field(array(
        //     'before_row'=>sprintf(__('%s阿里云CDN的URL鉴权设置%s%s此功能用来防止某些人盗用您的资源，或者恶意刷您的CDN流量，造成资金的大量损失，如果您使用了oss upload 插件来储存您的媒体文件到OSS，并且开启了阿里云的 CDN 功能，建议开启此项。%s','b2'),'<h2>','</h2>','<p>','</p>'),
        //     'name'    => __( '是否开启阿里云CDN的URL鉴权功能', 'b2' ),
        //     'id'=>'aliyun_cdn_jianquan',
        //     'type'             => 'select',
        //     'options'=>array(
        //         1=>__('开启URL鉴权功能','b2'),
        //         0=>__('关闭URL鉴权功能','b2')
        //     ),
        //     'default'          => self::$default_settings['aliyun_cdn_jianquan'],
        // ));

        // $safe->add_field(array(
        //     'name'    => __( '主key', 'b2' ),
        //     'desc'    => __( '请前往阿里云CDN控制台->域名管理->访问控制->URL中设置主KEY，设置完成后复制到此处', 'b2' ),
        //     'id'=>'aliyun_cdn_jianquan_key',
        //     'type'             => 'text',
        //     'default'          => self::$default_settings['aliyun_cdn_jianquan_key'],
        // ));

        $login = new_cmb2_box(array(
            'id'           => 'b2_normal_login_options_page',
            'tab_title'    => __('登录与注册','b2'), 
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_normal_login',
            'parent_slug'     => 'admin.php?page=b2_normal_main',
            'tab_group'    => 'b2_normal_options',
        ));

        //允许注册
        $login->add_field(array(
            'name'    => __( '是否开启注册', 'b2' ),
            'desc'    => __( '建议将wp设置->常规中的<任何人都可以注册>的勾选项去掉，防止机器人注册。', 'b2' ),
            'id'=>'allow_register',
            'type'             => 'select',
            'default'          => self::$default_settings['allow_register'],
            'options'          => array(
                1 => __( '开启', 'b2' ),
                0   => __( '关闭', 'b2' ),
            ),
        ));

        $login->add_field(array(
            'name'    => __( '登陆时效', 'b2' ),
            'desc'    => __( '用户登陆之后将会有一段时间保持登陆状态，这里可以设置登陆状态的时效，出于安全考虑，一般不超过7天。', 'b2' ),
            'id'=>'login_keep',
            'type'             => 'text',
            'default'          => self::$default_settings['login_keep'],
        ));

        $login->add_field(array(
            'name'    => __( '是否开启cookie兼容模式', 'b2' ),
            'desc'    => __( '不知道干啥用的，请关闭，不用开启。如果您使用了一些插件，涉及到用户登录的情况，请开启此项。', 'b2' ),
            'id'=>'allow_cookie',
            'type'             => 'select',
            'default'          => self::$default_settings['allow_cookie'],
            'options'          => array(
                1 => __( '开启', 'b2' ),
                0   => __( '关闭', 'b2' ),
            ),
        ));

        $login->add_field(array(
            'name'    => __( '隐私政策网址', 'b2' ),
            'desc'    => __( '如果设置将会显示在登录框底部，请直接填写网址', 'b2' ),
            'id'=>'site_privacy',
            'type'             => 'text',
            'default'          => self::$default_settings['site_privacy'],
        ));

        $login->add_field(array(
            'name'    => __( '用户协议网址', 'b2' ),
            'desc'    => __( '如果设置将会显示在登录框底部，请直接填写网址', 'b2' ),
            'id'=>'site_terms',
            'type'             => 'text',
            'default'          => self::$default_settings['site_terms'],
        ));

        //注册形式
        $login->add_field(array(
            'name'    => __( '请选择身份验证形式', 'b2' ),
            'id'=>'check_type',
            'type'             => 'select',
            'default'          => self::$default_settings['check_type'],
            'options'          => array(
                'tel' => __( '手机验证', 'b2' ),
                'email'   => __( '邮箱验证', 'b2' ),
                'telandemail'   => __( '手机和邮箱均可验证', 'b2' ),
                'text'=>__( '主题自带图形验证', 'b2' ),
                'luo'=>__( 'Luosimao人机验证', 'b2' )
            ),
            'desc'    => sprintf(__( '用于注册，找回密码等操作，四种身份验证形式只能选择其中一种。
            %s如果选择包含邮箱验证，请确认服务器支持邮件发送功能，如果服务器不支持邮件发送，推荐安装%s插件去支持。
            %s如果选择了包含手机验证，请按照下面的提示进行短信设置。
            %s如果选择Luosimao验证，请前往%s申请，然后将%s和%s填入下面设置项中。
            ', 'b2' ),'<br>','<a href="'.admin_url('/plugin-install.php?s=WP+Email+SMTP&tab=search&type=term').'" target="__blank">Easy WP SMTP</a>','<br>','<br>','<a target="_blank" href="https://luosimao.com/service/captcha">Luosimao</a>','<code>site key</code>','<code>secret key</code>'),
        ));

        $login->add_field(array(
            'name'    => __( '社交登录强制绑定手机、邮箱或用户名', 'b2' ),
            'desc'    => __( '第三方社交注册并未要求用户填写手机号码或邮箱，如果需要强制绑定手机或邮箱，请开启，绑定的是手机、邮箱还是用户名，与上面选择的身份验证形式一致。', 'b2' ),
            'id'=>'build_phone_email',
            'type'             => 'select',
            'options'          => array(
                0 => __( '不要求绑定', 'b2' ),
                1   => __( '强制绑定', 'b2' )
            ),
            'default'          => self::$default_settings['build_phone_email'],
        ));

        $login->add_field(array(
            'name'    => __( '新注册用户消息提示', 'b2' ),
            'desc'    => __( '新用户注册成功以后，会在消息中心有一条新的消息，请在此设置新消息内容', 'b2' ),
            'id'=>'register_msg',
            'type'             => 'textarea_small',
            'default'          => self::get_default_settings('register_msg')
        ));

        //短信服务选择
        $login->add_field(array(
            'before_row'=>'<div id="sms-select" class="cmb-row">',
            'name'    => __( '请选择手机短信服务商', 'b2' ),
            'id'=>'phome_select',
            'type'             => 'select',
            'default'          => self::$default_settings['phome_select'],
            'options'          => array(
                'aliyun' => __( '阿里云', 'b2' ),
                'yunpian'   => __( '云片', 'b2' ),
                'juhe'   => __( '聚合', 'b2' ),
                'zhongzheng'   => __( '中正云', 'b2' ),
                'tencent'   => __( '腾讯云', 'b2' ),
                'submail'   => __( '赛邮云', 'b2' ),
                'others'   => __( '其他', 'b2' )
            ),
        ));

        //阿里云短信设置
        $login->add_field(array(
            'before_row'=>'<div id="aliyun-sms" class="cmb-row"><h5>'.__('阿里云短信设置','b2').'</h5>',
            'name'    => __( 'AccessKey Id', 'b2' ),
            'desc'    => __( '阿里云控制台->鼠标放到右上角头像上->accessKeys->AccessKey ID。', 'b2' ),
            'id'=>'accesskey_id',
            'type'             => 'text',
            'default'          => self::$default_settings['accesskey_id'],
        ));

        $login->add_field(array(
            'name'    => __( 'Access Key Secret', 'b2' ),
            'desc'    => __( '阿里云控制台->鼠标放到右上角头像上->accessKeys->Access Key Secret。', 'b2' ),
            'id'=>'access_key_secret',
            'attributes' => array(
                'type' => 'password',
            ),
            'type'             => 'text',
            'default'          => self::$default_settings['access_key_secret'],
        ));

        $login->add_field(array(
            'name'    => __( '签名名称', 'b2' ),
            'desc'    => __( '阿里云控制台->短信服务控制台->国内消息->签名管理->签名名称。', 'b2' ),
            'id'=>'sign_name',
            'type'             => 'text',
            'default'          => self::$default_settings['sign_name'],
        ));

        $login->add_field(array(
            'name'    => __( '模板CODE', 'b2' ),
            'desc'    => __( '阿里云控制台->短信服务控制台->国内消息->模板管理->模板CODE。', 'b2' ),
            'id'=>'template_code',
            'type'             => 'text',
            'default'          => self::$default_settings['template_code'],
            'after_row'=>'<p class="red">申请地址：<a href="https://cn.aliyun.com/product/sms" target="__blank">阿里云短信服务</a></p></div>',
        ));

        //云片设置
        $login->add_field(array(
            'before_row'=>'<div id="yunpian-sms" class="cmb-row"><h5>'.__('云片短信设置','b2').'</h5>',
            'name'    => __( 'Apikey', 'b2' ),
            'desc'    => __( '云片管理控制台->账户设置->子账户管理。', 'b2' ),
            'id'=>'apikey',
            'attributes' => array(
                'type' => 'password',
            ),
            'type'             => 'text',
            'default'          => self::$default_settings['apikey'],
        ));

        $login->add_field(array(
            'name'    => __( '模板内容', 'b2' ),
            'desc'    => sprintf(__( '云片管理控制台->国内短信->签名模板报备->已审核的【模板内容】%s
            比如：%s', 'b2' ),'<br>','<code>【'.B2_BLOG_NAME.'】您的验证码是#code#。如非本人操作，请忽略本短信</code>'),
            'id'=>'yunpian_text',
            'type'             => 'text',
            'default'          => self::$default_settings['yunpian_text'],
            'after_row'=>'<p class="red">申请地址：<a href="https://www.yunpian.com" target="__blank">云片网</a></p></div>',
        ));

        //腾讯云
        $login->add_field(array(
            'before_row'=>'<div id="tencent-sms" class="cmb-row"><h5>'.__('腾讯云短信设置','b2').'</h5><p class="red">正文短信模板内容务必使用类似：验证码为：{1}，若非本人操作，请勿泄露。这种一个参数的，不要使用有2个及2个以上的参数的内容</p>',
            'name'    => __( '短信模板ID', 'b2' ),
            'id'=>'tencent_id',
            'type'             => 'text',
            'default'          => self::$default_settings['tencent_id'],
            'desc'=>__('模板 ID，必须填写已审核通过的模板 ID。模板ID可登录 腾讯短信控制台->国内短信->正文模板->ID 中查看','b2')
        ));

        $login->add_field(array(
            'name'    => __( '短信签名内容', 'b2' ),
            'id'=>'tencent_Sign',
            'type'             => 'text',
            'desc'=>__('短信签名内容，签名信息可登录 腾讯短信控制台->国内短信->签名管理->内容 中查看。','b2'),
            'default'          => self::$default_settings['tencent_Sign'],
            
        ));

        $login->add_field(array(
            'name'    => __( '短信SDK AppID', 'b2' ),
            'id'=>'tencent_SmsSdkAppid',
            'type'             => 'text',
            'default'          => self::$default_settings['tencent_SmsSdkAppid'],
            'desc'=>__('短信SdkAppid在 短信控制台 应用管理->应用列表，示例如1400006666。','b2'),
        ));

        $login->add_field(array(
            'name'    => __( '短信App Key', 'b2' ),
            'id'=>'tencent_appkey',
            'type'             => 'text',
            'attributes' => array(
                'type' => 'password',
            ),
            'default'          => self::$default_settings['tencent_appkey'],
            'desc'=>__('短信SdkAppid在 短信控制台 应用管理->应用列表，示例如e0410cfd6568dc827c2c5ac7b7c38851。','b2'),
            'after_row'=>'<p class="red">申请地址：<a href="https://console.cloud.tencent.com/sms/smslist" target="__blank">腾讯云短信</a></p></div>'
        ));

        //其他短信商
        $login->add_field(array(
            'before_row'=>'<div id="others-sms" class="cmb-row"><h5>'.__('通用短信接口','b2').'</h5>',
            'name'    => __( '接口网址', 'b2' ),
            'id'=>'others_url',
            'type'             => 'text',
            'default'          => self::$default_settings['others_url'],
            'desc'=>sprintf(__('比如：http://xxx.com/sms?username=%s&password=%s&phone=%s&message=【柒比贰】您的验证码是%s。如非本人操作，请忽略本短信','b2'),'<span class="red">smsphp</span>','<span class="red">123456</span>','<span class="green">#phone#</span>','<span class="green">#code#</span>').'<br><br>'.
            __('其中红色部分是你要根据不同平台自己设置的，可能参数不止这两个。绿色部分是程序会自动添加的，请保持不要修改。','b2')
        ));

        $login->add_field([
            'name'=>__('短信内容是否需要urldecode,','b2'),
            'id'=>'others_urlencode',
            'type'=>'select',
            'options'=>[
                1=>__('使用','b2'),
                0=>__('不使用','b2')
            ],
            'default'=>0,
            'desc'=>__('不同平台要求不同,请查看您使用平台的接口文档中是否要求短信内容urlencode','b2')
        ]);

        $login->add_field(array(
            'name'    => __( '成功发送短信，返回代码', 'b2' ),
            'id'=>'others_back',
            'type'             => 'text',
            'default'          => self::$default_settings['others_back'],
            'desc'=>__('短信发送成功或失败对方服务器通常都会返回一些信息，请直接填写发送成功之后返回有别于失败的特殊字符串。比如发送成功后返回的信息中包括success这个字符，发送失败不会有这个字符，那么请在这里填写success','b2'),
            'after_row'=>'
            <div>
                <p>通用接口配置注意事项：</p>
                <p>1、短信接口并不是免费的，要使用短信发送功能，必须先付费申请一个短信接口，一般每条短信一毛钱左右。你可以自由选择一家比较稳定的短信接口服务商，有些可能不太稳定，比如延时很久都收不到短信。</p>
                <p>2、当你选择一家短信接口提供商之后，请向他索取http短信发送接口网址，比如“http://xxxx.com/?id=帐号&密码=xxx&mob=这是手机号&content=这是短信内容”填入到上面的第一项“接口网址：”那里。然后自己按接口网址正确的输入各项参数后，在浏览器上打开它，若能发送短信成功，就查看一下当前网页源代码，看看返回的是什么代码，然后复制一部分特定的代码在上面的第二项那里“成功发送短信返回代码：”即可。</p>
                <p>3、如果你不知道找哪个短信接口服务商的话，你可以分别咨询一下这三家：“短信通平台”、“移动商务应用中心”、“单元科技”，看看哪个更适合你.</p>
                <p>4、部分服务商的接口示例如下，短信服务商的接口时不时会有更改，购买接口时，请联系服务商给最新接口：</p>
                <p>短信通：http://sms.106jiekou.com/gbk/sms.aspx?account=账号&password=密码&mobile=$mob&content=您的订单编码：$content。如需帮助请联系客服。</p>
                <p>移动商务应用中心：http://service.winic.org/sys_port/gateway/?id=帐号&pwd=密码&to=$mob&content=$content&time=</p>
                <p>名传无线(单元科技)：http://server4.chineseserver.net:9801/CASServer/SmsAPI/SendMessage.jsp?userid=帐号&password=密码&destnumbers=$mob&msg=$content&sendtime=</p>
                <p>注意，以上帐号密码要对应换成你自己的，再输入到上面的方框里。有部分服务商要求固定的$content内容格式模板，比如“您的验证码是：$content。请不要把验证码泄露给其他人。如非本人操作，可不用理会！【短信通】 ”</p>
                <p>4、部分服务商的接口成功发送短信之后返回的代码，示例如下:</p>
                <p>短信通：100</p>
                <p>移动商务应用中心：000</p>
                <p>名传无线(单元科技)：messages="1"</p>
                <p>务必注意，不能有空格</p>
            </div>
            </div>',
        ));

        //赛邮云设置
		$login->add_field(array(
            'before_row'=>'<div id="submail-sms" class="cmb-row"><h5>'.__('赛邮短信设置','b2').'</h5>',
            'name'    => __( 'APPID应用ID', 'b2' ),
            'desc'    => __( '登录后创建网址：https://www.mysubmail.com/chs/sms/apps', 'b2' ),
            'id'=>'saiyou_app_id',
            'type'             => 'text',
            'default'          => self::$default_settings['saiyou_app_id'],
        ));

        $login->add_field(array(
            'name'    => __( 'APPKEY应用密匙', 'b2' ),
            'desc'    => __( '登录后创建网址：https://www.mysubmail.com/chs/sms/apps', 'b2' ),
            'id'=>'saiyou_app_key',
            'type'             => 'text',
            'default'          => self::$default_settings['saiyou_app_key'],
        ));
		
		$login->add_field(array(
            'name'    => __( '模板标记', 'b2' ),
            'desc'    => __( '登录后创建网址：https://www.mysubmail.com/chs/sms/templates', 'b2' ),
            'id'=>'saiyou_project',
            'type'             => 'text',
            'default'          => self::$default_settings['saiyou_project'],
        ));

        $login->add_field(array(
            'name'    => __( 'Sign Type验证模式', 'b2' ),
			    'desc'    => __( '1.md5=md5 签名验证模式[推荐]<br>2.sha1=sha1 签名验证模式[推荐]<br>3.normal=密匙明文验证', 'b2' ),
			    'id'=>'saiyou_sign_type',
            'type'             => 'text',
            'default'          => self::$default_settings['saiyou_sign_type'],
            'after_row'=>'<p class="red">申请地址：<a href="https://www.mysubmail.com/" target="__blank">赛邮·云通讯</a></p></div>',
        ));

        //中正云
        $login->add_field(array(
            'before_row'=>'<div id="zhongzheng-sms" class="cmb-row"><h5>'.__('中正云短信设置','b2').'</h5>',
            'name'    => __( '中正云账户', 'b2' ),
            'id'=>'zz_id',
            'type'             => 'text',
            'default'          => self::$default_settings['zz_id'],
        ));

        $login->add_field(array(
            'name'    => __( '中正云密码', 'b2' ),
            'id'=>'zz_password',
            'type'             => 'text',
            'attributes' => array(
                'type' => 'password',
            ),
            'default'          => self::$default_settings['zz_password']
        ));

        $login->add_field(array(
            'name'    => __( '中正云模板', 'b2' ),
            'id'=>'zz_temp',
            'type'             => 'text',
            'attributes' => array(
                'type' => 'text',
            ),
            'default'          => self::get_default_settings('zz_temp'),
            'desc'    => sprintf(__( '比如：%s', 'b2' ),'<code>【'.B2_BLOG_NAME.'】您的验证码是#code#。如非本人操作，请忽略本短信</code>'),
            'after_row'=>'<p class="red">申请地址：<a href="http://www.winic.org/product/dxyz.asp" target="__blank">中正云</a></p></div>',
        ));

        //聚合设置
        $login->add_field(array(
            'before_row'=>'<div id="juhe-sms" class="cmb-row"><h5>'.__('聚合短信设置','b2').'</h5>',
            'name'    => __( '模板ID', 'b2' ),
            'desc'    => __( '我的聚合->短信API服务->短信模板->模板ID。', 'b2' ),
            'id'=>'tpl_id',
            'type'             => 'text',
            'default'          => self::$default_settings['tpl_id'],
        ));

        $login->add_field(array(
            'name'    => __( 'Appkey', 'b2' ),
            'desc'    => __( '数据中心->我的数据->AppKey。', 'b2' ),
            'id'=>'juhe_key',
            'type'             => 'text',
            'attributes' => array(
                'type' => 'password',
            ),
            'default'          => self::$default_settings['juhe_key'],
            'after_row'=>'<p class="red">申请地址：<a href="https://www.juhe.cn/docs/index/cid/13" target="__blank">聚合网</a></p></div></div>',
        ));
        
        //luosimao验证
        $login->add_field(array(
            'before_row'=>'<div id="luosimao" class="cmb-row"><h5>'.__('luosimao人机验证设置','b2').'</h5>',
            'name'    => __( 'Site key', 'b2' ),
            'desc'    => __( 'luosimao后台->人机验证->操作->设置。', 'b2' ),
            'id'=>'site_key',
            'type'             => 'text',
            'default'          => self::$default_settings['site_key'],
        ));

        $login->add_field(array(
            'name'    => __( 'Api key', 'b2' ),
            'desc'    => __( 'luosimao后台->人机验证->操作->设置。', 'b2' ),
            'id'=>'api_key',
            'type'             => 'text',
            'default'          => self::$default_settings['api_key'],
            'after_row'=>'<p class="red">申请地址：<a href="https://luosimao.com/service/captcha" target="__blank">Luosimao</a></p></div>',
        ));

        //微信扫码登录
        $login->add_field(array(
            'name'    => __( '是否启用微信PC扫码登录（微信开放平台PC扫码登陆）', 'b2' ),
            'before_row'=>'<h2>'.__('微信登录设置','b2').'</h2><div class="cmb-row"><p><b>'.__('微信pc端登录设置','b2').'</b></p>',
            'id'=>'wx_pc_open',
            'type'             => 'select',
            'options'          => array(
                1 => __( '开启', 'b2' ),
                0   => __( '关闭', 'b2' )
            ),
            'desc'=>__('请注意，如果您开通了微信开放平台，并且添加了网站应用，此处开启才有效，否则请保持关闭','b2'),
            'default'          => self::$default_settings['wx_pc_open'],
        ));

        $login->add_field(array(
            'name'    => __( 'AppID', 'b2' ),
            'id'=>'wx_pc_key',
            'desc'=>__('微信开放平台网站应用的AppID','b2'),
            'type'             => 'text',
            'default'          => self::$default_settings['wx_pc_key'],
        ));

        $login->add_field(array(
            'name'    => __( 'AppSecret', 'b2' ),
            'id'=>'wx_pc_secret',
            'desc'=>__('微信开放平台网站应用的AppSecret','b2'),str_replace(array('http','https'),'',B2_HOME_URI),
            'type'             => 'text',
            'attributes' => array(
                'type' => 'password',
            ),
            'default'          => self::$default_settings['wx_pc_secret'],
            'after_row'=>'<p class="red">'.sprintf(__('微信扫码登录申请地址：%s；回调地址请填写：%s','b2'),'<a target="_blank" href="https://open.weixin.qq.com/">https://open.weixin.qq.com/</a>',
            str_replace(array('http://','https://'),'',B2_HOME_URI)).'</p></div>'
        ));

        //微信授权登录
        $login->add_field(array(
            'name'    => __( '是否启用微信公众号内授权登录', 'b2' ),
            'before_row'=>'<div class="cmb-row"><p><b>'.__('微信公众号登录设置','b2').'</b></p>',
            'id'=>'wx_gz_open',
            'type'             => 'select',
            'options'          => array(
                1 => __( '开启', 'b2' ),
                0   => __( '关闭', 'b2' )
            ),
            'default'          => self::$default_settings['wx_gz_open'],
        ));

        $login->add_field(array(
            'name'    => __( 'AppID', 'b2' ),
            'id'=>'wx_gz_key',
            'desc'=>__('微信公众平台->基本配置->开发者ID(AppID)','b2'),
            'type'             => 'text',
            'default'          => self::$default_settings['wx_gz_key'],
        ));

        $login->add_field(array(
            'name'    => __( 'AppSecret', 'b2' ),
            'id'=>'wx_gz_secret',
            'attributes' => array(
                'type' => 'password',
            ),
            'desc'=>__('微信公众平台->基本配置->开发者密码','b2'),
            'type'             => 'text',
            'default'          => self::$default_settings['wx_gz_secret'],
            'after_row'=>'<p class="red">'.__('公众号授权登录，请开通微信服务号（必须认证了的服务号），微信订阅号无法使用','b2').'<p></div>'
        ));

        //微信授权登录
        $login->add_field(array(
            'name'    => __( '是否开启PC端扫码关注公众号后自动登录？', 'b2' ),
            'before_row'=>'<div class="cmb-row"><p><b>'.__('扫码关注自动登录','b2').'</b></p>',
            'id'=>'wx_mp_login',
            'type'             => 'select',
            'options'          => array(
                1 => __( '开启', 'b2' ),
                0   => __( '关闭', 'b2' )
            ),
            'desc'=>'<p>'.sprintf(__('开启此项，用户点击微信登录会直接显示公众号二维码，用户扫码关注以后自动登录。开启条件：1、关闭上面“微信PC扫码登录”;2、开通了微信服务号并在主题%s常规设置->微信设置%s里面填写了相关设置项。'),'<a target="_blank" href="'.admin_url('/admin.php?page=b2_normal_weixin').'">','</a>').'</p>',
            'default'=> self::$default_settings['wx_gz_open'],
        ));

        $login->add_field(array(
            'name'    => __( '关注公众号登录后微信提示文字', 'b2' ),
            'id'=>'wx_mp_login_text',
            'type'             => 'textarea',
            'desc'=>__('用户通过关注公众号登录网站后，微信内可以返回一段文字，此处为文字内容','b2'),
            'default'=> self::$default_settings['wx_mp_login_text'],
            'after_row'=>'</div>'
        ));

        //微信授权登录
        $login->add_field(array(
            'name'    => __( '是否开启微信端用户进入网站提示授权（已授权自动登录）？', 'b2' ),
            'before_row'=>'<div class="cmb-row"><p><b>'.__('微信端自动登录','b2').'</b></p>',
            'desc'=>__('务必设置了上面<微信公众号登录设置>，才会生效！否则请关闭','b2'),
            'id'=>'wx_mp_in_login',
            'type'             => 'select',
            'options'          => array(
                1 => __( '开启', 'b2' ),
                0   => __( '关闭', 'b2' )
            ),
            'default'=> self::$default_settings['wx_mp_in_login'],
            'after_row'=>'</div>'
        ));

        //QQ登录
        $login->add_field(array(
            'name'    => __( '是否启用QQ授权登录', 'b2' ),
            'before_row'=>'<h2>'.__('QQ登录设置','b2').'</h2><div class="cmb-row">',
            'id'=>'qq_open',
            'type'             => 'select',
            'options'          => array(
                1 => __( '开启', 'b2' ),
                0   => __( '关闭', 'b2' )
            ),
            'default'          => self::$default_settings['qq_open'],
        ));

        $login->add_field(array(
            'name'    => __( 'APP ID', 'b2' ),
            'id'=>'qq_id',
            'type'             => 'text',
            'default'          => self::$default_settings['qq_id'],
        ));

        $login->add_field(array(
            'name'    => __( 'APP Key', 'b2' ),
            'id'=>'qq_secret',
            'attributes' => array(
                'type' => 'password',
            ),
            'type'             => 'text',
            'default'          => self::$default_settings['qq_secret'],
            'after_row'=>'<p class="red">'.sprintf(__('QQ登录申请地址：%s；回调地址请填写：%s','b2'),'<a target="_blank" href="https://connect.qq.com/">https://connect.qq.com/</a>',B2_HOME_URI.'/open?type=qq').'<p></div>'
        ));

        //微博登录
        $login->add_field(array(
            'name'    => __( '是否启用微博授权登录', 'b2' ),
            'before_row'=>'<h2>'.__('微博设置','b2').'</h2><div class="cmb-row">',
            'id'=>'weibo_open',
            'type'             => 'select',
            'options'          => array(
                1 => __( '开启', 'b2' ),
                0   => __( '关闭', 'b2' )
            ),
            'default'          => self::$default_settings['weibo_open'],
        ));

        $login->add_field(array(
            'name'    => __( 'App Key', 'b2' ),
            'id'=>'weibo_id',
            'type'             => 'text',
            'default'          => self::$default_settings['weibo_key'],
        ));

        $login->add_field(array(
            'name'    => __( 'App Secret', 'b2' ),
            'id'=>'weibo_secret',
            'type'             => 'text',
            'attributes' => array(
                'type' => 'password',
            ),
            'default'          => self::$default_settings['weibo_secret'],
            'after_row'=>'<p class="red">'.sprintf(__('微博登录申请地址：%s；回调地址和取消回调地址都填写：%s','b2'),'<a target="_blank" href="http://open.weibo.com/development">http://open.weibo.com/development</a>',B2_HOME_URI.'/open?type=weibo').'<p></div>'
        ));

        //彩虹聚合登录
        $login->add_field(array(
            'name'    => __( '是否启用彩虹聚合登录', 'b2' ),
            'before_row'=>'<h2>'.__('彩虹聚合登录','b2').'</h2><div class="cmb-row juhe-login">',
            'id'=>'juhe_open',
            'type'             => 'select',
            'options'          => array(
                1 => __( '开启', 'b2' ),
                0   => __( '关闭', 'b2' )
            ),
            'default'          => self::$default_settings['juhe_open'],
        ));

        $login->add_field(array(
            'name'    => __( '接口网址', 'b2' ),
            'id'=>'juhe_url',
            'type'             => 'text',
            'default'          => self::$default_settings['juhe_url'],
        ));

        $login->add_field(array(
            'name'    => __( 'AppID', 'b2' ),
            'id'=>'juhe_appid',
            'type'             => 'text',
            'attributes' => array(
                'type' => 'password',
            ),
            'default'          => self::$default_settings['juhe_appid']
        ));

        $login->add_field(array(
            'name'    => __( 'AppKey', 'b2' ),
            'id'=>'juhe_appkey',
            'type'             => 'text',
            'default'          => self::$default_settings['juhe_appkey']
        ));

        $login->add_field(array(
            'name'    => __( '启用的登录方式', 'b2' ),
            'id'=>'juhe_types',
            'type'             => 'pw_multiselect',
            'default'          => self::$default_settings['juhe_types'],
            'options'=>[
                'qq'=>__('QQ','b2'),
                'wx'=>__('微信','b2'),
                'alipay'=>__('支付宝','b2'),
                'sina'=>__('微博','b2'),
                'baidu'=>__('百度','b2'),
                'huawei'=>__('华为','b2'),
                'xiaomi'=>__('小米','b2'),
                'google'=>__('Google','b2'),
                'facebook'=>__('Facebook','b2'),
                'twitter'=>__('Twitter','b2'),
                'microsoft'=>__('microsoft','b2'),
                'dingtalk'=>__('钉钉','b2'),
                'github'=>__('Github','b2'),
                'gitee'=>__('Gitee','b2')
            ],
            'after_row'=>'<p class="red">'.__('彩虹聚合登录为第三方登录平台，一般是由个人自己搭建。或者使用现成的第三方，不过第三方一旦停止服务，您的用户将无法再次登录您的网站，所以请根据自己的需求选择。具体搭建和使用方法，请自行百度。','b2').'<p></div>'
        ));

        //用户设置项
        $user = new_cmb2_box(array(
            'id'           => 'b2_normal_user_options_page',
            'tab_title'    => __('用户设置','b2'), 
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_normal_user',
            'parent_slug'     => 'admin.php?page=b2_normal_main',
            'tab_group'    => 'b2_normal_options',
        ));

        $user->add_field(array(
            'name'    => __( '用户默认头像', 'b2' ),
            'id'=>'default_user_avatar',
            'type'    => 'file',
            'options' => array(
                'url' => true, 
            ),
            'text'    => array(
                'add_upload_file_text' => __( '选择图片', 'b2' ),
            ),
            'query_args' => array(
                'type' => array(
                    'image/svg+xml',
                    'image/gif',
                    'image/jpeg',
                    'image/png',
                ),
            ),
            'default'=>self::get_default_settings('default_user_avatar')
        ));

        $user->add_field(array(
            'name'    => __( '用户默认封面', 'b2' ),
            'id'=>'default_user_cover',
            'type'    => 'file',
            'options' => array(
                'url' => true, 
            ),
            'text'    => array(
                'add_upload_file_text' => __( '选择图片', 'b2' ),
            ),
            'query_args' => array(
                'type' => array(
                    'image/svg+xml',
                    'image/gif',
                    'image/jpeg',
                    'image/png',
                ),
            ),
            'default'=>self::get_default_settings('default_user_cover')
        ));

        $user->add_field(array(
            'name'    => __( '加密用户页面网址', 'b2' ),
            'id'=>'user_id_decode',
            'type'             => 'select',
            'default'          => self::$default_settings['user_id_decode'],
            'desc'    => sprintf(__( '加密前：%s%s加密后：%s%s设置完成以后请保存一下固定链接，前端用户需要重新登录', 'b2' ),'<code>'.B2_HOME_URI.'/users/<span class="red">123</span></code>','<br>','<code>'.B2_HOME_URI.'/users/<span class="red">pXyQNJVVV</span></code>','<br><span class="red">','</span>'),
            'options'          => array(
                1 => __( '加密', 'b2' ),
                0 => __( '不加密', 'b2' ),
            ),
        ));

        $user->add_field(array(
            'name'    => __( '重写用户页面规则', 'b2' ),
            'desc'    => sprintf(__( '如果需要用户页面是%s，则在此处填写%s%s设置完成以后请保存一下固定链接，前端用户需要重新登录', 'b2' ),'<code>'.B2_HOME_URI.'/<span class="red">users</span>/123</code>','<code>users</code>','<br><span class="red">','</span>'),
            'id'=>'user_slug',
            'type'             => 'text',
            'default'          => self::$default_settings['user_slug'],
        ));

        // $user->add_field(array(
        //     'name'    => __( '是否使用默认字母头像', 'b2' ),
        //     'id'=>'user_avatar_open',
        //     'type'             => 'select',
        //     'default'          => self::$default_settings['user_avatar_open'],
        //     'options'          => array(
        //         1 => __( '使用', 'b2' ),
        //         0 => __( '关闭', 'b2' ),
        //     ),
        // ));

        // $user->add_field(array(
        //     'name'    => __( '默认字母头像使用第一个字符还是最后一个字符', 'b2' ),
        //     'id'=>'user_avatar_letter',
        //     'type'             => 'select',
        //     'default'          => self::$default_settings['user_avatar_letter'],
        //     'options'          => array(
        //         1 => __( '第一个字符', 'b2' ),
        //         0 => __( '最后一个字符', 'b2' ),
        //     ),
        // ));

        //用户等级设置
        $user_lv_group = $user->add_field( array(
            'id'          => 'user_lv_group',
            'type'        => 'group',
            'description' => sprintf(__( '普通用户等级设置%s（LV0等级必须设置积分为0，否则新用户没有等级提示，请不要删除或随意改变下面等级的排序，也不要随意在中间插入新的等级，否则可能造成老用户的等级失效。）%s', 'b2' ),'<span class="red">','<span>'),
            'options'     => array(
                'group_title'       => '{#}', 
                'add_button'        => __( '添加新等级', 'b2' ),
                'remove_button'     => __( '删除等级', 'b2' ),
                'sortable'          => false,
                'closed'         => true, 
                'remove_confirm' => __( '确定要删除这个等级吗？', 'b2' ), 
            )
        ));

        $user->add_group_field( $user_lv_group, array(
            'name' => __('该等级的对外名称','b2'),
            'id'   => 'name',
            'type' => 'text',
            'desc'=>sprintf(__('比如 %s 等等','b2'),'<code>学前班</code><code>周会员</code>')
        ) );

        $user->add_group_field( $user_lv_group, array(
            'name' => __('到达此等级需要的积分','b2'),
            'id'   => 'credit',
            'type' => 'text',
            'desc'=>sprintf(__('比如 %s 积分，用户会达到此积分便会升级为此等级','b2'),'<code>1000</code>')
        ) );

        $user->add_group_field( $user_lv_group, array(
            'name' => __('允许每天下载的次数','b2'),
            'id'   => 'allow_download_count',
            'type'             => 'text',
            'default'          => 20,
            'desc'=>__('为防止有人恶意采集下载资源，可在此处设置允许每天下载的次数。如果当天下载次数达到最大，将不允许再次下载。（如果下载次数大于或等于9999，系统将识别为不限制下载次数）','b2')
        ));

        do_action('b2_setting_normal_user_lv_group',$user,$user_lv_group);

        $user->add_group_field( $user_lv_group, array(
            'name' => __('该等级的权限','b2'),
            'id'      => 'user_role',
            'type'    => 'multicheck_inline',
            'options' => b2_roles_arg()
        ) );

        //vip用户等级设置
        $user_vip_group = $user->add_field( array(
            'id'          => 'user_vip_group',
            'type'        => 'group',
            'description' => sprintf(__( '付费用户等级设置%s（请不要随意删除或改变下面等级的排序，也不要随意在中间插入新的等级，否则可能造成老用户的等级失效。）%s', 'b2' ),'<span class="red">','</span>'),
            'options'     => array(
                'group_title'       => '<span class="lv-name"></span><b>（vip{#}）</b>', 
                'add_button'        => __( '添加新等级', 'b2' ),
                'remove_button'     => __( '删除等级', 'b2' ),
                'sortable'          => false,
                'closed'         => true, 
                'remove_confirm' => __( '确定要删除这个等级吗？', 'b2' ), 
            ),
        ));

        $user->add_group_field( $user_vip_group, array(
            'name' => __('该等级的对外名称','b2'),
            'id'   => 'name',
            'type' => 'text',
            'desc'=>sprintf(__('比如 %s 等等','b2'),'<code>超级会员</code><code>白金会员</code>')
        ) );

        $user->add_group_field( $user_vip_group, array(
            'name' => __('该等级的对外显示的颜色','b2'),
            'id'   => 'color',
            'type' => 'colorpicker',
        ) );

        $user->add_group_field( $user_vip_group, array(
            'name' => __('购买此等级需要支付的费用','b2'),
            'id'   => 'price',
            'type' => 'text_money',
            'sanitization_cb' => 'b2_sanitize_number',
            'before_field' => B2_MONEY_SYMBOL,
            'desc'=>sprintf(__('比如 %s 元，用户支付了这些费用，便会成为此等级用户，如果设置为0将不允许用户购买','b2'),'<code>100</code>')
        ) );

        $user->add_group_field( $user_vip_group, array(
            'name' => __('该等级的有效期','b2'),
            'id'   => 'time',
            'type' => 'text',
            'desc'=>sprintf(__('此处为用户从购买会员到会员过期的时间，比如 %s 天，请直接填写天数的数字，永久有效请填0。','b2'),'<code>30</code>')
        ) );

        $user->add_group_field( $user_vip_group, array(
            'name' => __('是否允许查看所有隐藏内容','b2'),
            'id'   => 'allow_read',
            'type'             => 'select',
            'default'          => 0,
            'options'          => array(
                1 => __( '允许', 'b2' ),
                0 => __( '禁止', 'b2' ),
            ),
            'desc'=>__('对文章中隐藏代码包裹起来的内容有效','b2')
        ) );

        $user->add_group_field( $user_vip_group, array(
            'name' => __('是否允许下载所有资源','b2'),
            'id'   => 'allow_download',
            'type'             => 'select',
            'default'          => 0,
            'options'          => array(
                1 => __( '允许', 'b2' ),
                0 => __( '禁止', 'b2' ),
            ),
            'desc'=>__('将会允许此等级用户免费下载所有文章中设置的下载资源','b2')
        ));

        $user->add_group_field( $user_vip_group, array(
            'name' => __('允许每天下载的次数','b2'),
            'id'   => 'allow_download_count',
            'type'             => 'text',
            'default'          => 20,
            'desc'=>__('为防止有人恶意采集下载资源，可在此处设置允许每天下载的次数。如果当天下载次数达到最大，将不允许再次下载。（如果下载次数大于或等于9999，系统将识别为不限制下载次数）','b2')
        ));

        do_action('b2_setting_normal_user_vip_group',$user,$user_vip_group);

        $user->add_group_field( $user_vip_group, array(
            'name' => __('是否允许查看所有付费视频','b2'),
            'id'   => 'allow_videos',
            'type'             => 'select',
            'default'          => 0,
            'options'          => array(
                1 => __( '允许', 'b2' ),
                0 => __( '禁止', 'b2' ),
            ),
            'desc'=>__('将会允许此等级用户免费查看所有付费视频内容','b2')
        ));

        $user->add_group_field( $user_vip_group, array(
            'name' => __('初始人数','b2'),
            'id'   => 'count',
            'type'             => 'text',
            'default'          => 300,
            'desc'=>__('新站vip购买页面显示VIP数量太少，这里可以设置一个初始数量，让数据看上去漂亮一些','b2')
        ));

        $user->add_group_field( $user_vip_group, array(
            'name' => __('该等级的权限','b2'),
            'id'      => 'user_role',
            'type'    => 'multicheck_inline',
            'options' => b2_roles_arg()
        ) );

        $user->add_group_field( $user_vip_group, array(
            'name' => __('自定义权限','b2'),
            'id'   => 'more',
            'type'             => 'textarea',
            'desc'=>sprintf(__('%s在vip购买页面每个等级都会有相应的权限，如果您自己还给了这个等级其他权限，可以在这里自定义，每个权限占一行，比如：%s参与本站线下活动|0%s100天售后服务|0%s其中竖线和后面的0（|0 或|1）代表该等级是否有此权限，0代表无，1代表有%s','b2'),'<p>','</p><p>','</p><p>','</p><p>','</p>')
        ));

        self::write_settings();
        self::pay_settings();
        self::gold_settings();
        self::task_settings();
        self::weixin_settings();
        self::weixin_menu();
        self::weixin_message();
    }

    public static function write_settings(){

        $lvs = User::get_user_roles();

        $setting_lvs = array();
        foreach($lvs as $k => $v){
            $setting_lvs[$k] = $v['name'];
        }

        if(b2_get_option('verify_main','verify_allow')){
            $setting_lvs['verify'] = __('认证用户','b2');
        }

        $write = new_cmb2_box(array(
            'id'           => 'b2_normal_write_options_page',
            'tab_title'    => __('投稿及媒体','b2'), 
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_normal_write',
            'parent_slug'     => 'admin.php?page=b2_normal_main',
            'tab_group'    => 'b2_normal_options',
        ));

        //是否允许用户投稿
        // $write->add_field(array(
        //     'name'    => __( '是否开启webp格式', 'b2' ),
        //     'id'      => 'write_webp',
        //     'type'    => 'radio_inline',
        //     'options' => array(
        //         1 => __( '启用', 'b2' ),
        //         0   => __( '禁用', 'b2' ),
        //     ),
        //     'desc'=>__('如果您的图片存在本地，此项设置才会生效，如果图片存在远程服务器，比如OSS，COS等，需要去相关插件里面开启','b2'),
        //     'default' => self::$default_settings['write_webp'],
        // ));

        //是否允许用户投稿
        $write->add_field(array(
            'name'    => __( '是否允许用户投稿', 'b2' ),
            'id'      => 'write_allow',
            'type'    => 'radio_inline',
            'options' => array(
                1 => __( '允许投稿', 'b2' ),
                0   => __( '禁止投稿', 'b2' ),
            ),
            'default' => self::$default_settings['write_allow'],
        ));

        //默认投稿的分类ID
        $write->add_field(array(
            'name'    => __( '限制投稿', 'b2' ),
            'id'      => 'write_can_post',
            'type'    => 'text',
            'default'=>3,
            'desc'=>__('普通用户多少篇投稿未审核时不允许再次投稿，管理员，编辑不受此数量限制.（此项设置可以防止垃圾投稿）','b2')
        ));

        //允许投稿的分类
        $write->add_field(array(
            'name' => __('允许投稿的分类','b2'),
            'id'   => 'write_cats',
            'type' => 'taxonomy_multicheck_hierarchical',
            'taxonomy'=>'category',
            // Optional :
            'text'           => array(
                'no_terms_text' => sprintf(__('没有分类，请前往%s添加','b2'),'<a target="_blank" href="'.admin_url('/edit-tags.php?taxonomy=category').'">分类设置</a>')
            ),
            'remove_default' => 'true', // Removes the default metabox provided by WP core.
            // Optionally override the args sent to the WordPress get_terms function.
            'query_args' => array(
                'orderby' => 'count',
                'hide_empty' => false,
            ),
            'select_all_button' => true,
            'desc'=>__('请确保您的分类别名不是中文，否则无法选中，不选择将不显示分类设置的选项','b2'),
        ));

        //默认投稿的分类ID
        $write->add_field(array(
            'name'    => __( '默认投稿的分类ID', 'b2' ),
            'id'      => 'write_cats_default',
            'type'    => 'text',
            'desc'=>__('请直接填写分类ID，不设置则默认投入ID为1的分类中','b2')
        ));

        $collection_name = b2_get_option('normal_custom','custom_collection_name');

        //允许投稿的专题
        $write->add_field(array(
            'name' => sprintf(__('允许投稿的%s','b2'),$collection_name),
            'id'   => 'write_callections',
            'type' => 'taxonomy_multicheck_hierarchical',
            'taxonomy'=>'collection',
            // Optional :
            'text'           => array(
                'no_terms_text' => sprintf(__('没有%s，请前往%s添加','b2'),$collection_name,'<a target="_blank" href="'.admin_url('/edit-tags.php?taxonomy=collection').'">'.$collection_name.'设置</a>')
            ),
            'remove_default' => 'true', // Removes the default metabox provided by WP core.
            // Optionally override the args sent to the WordPress get_terms function.
            'query_args' => array(
                'orderby' => 'count',
                'hide_empty' => false,
            ),
            'select_all_button' => true,
            'desc'=>sprintf(__('请确保您的%s别名不是中文，否则无法选中，不选择将不显示%s设置的选项','b2'),$collection_name,$collection_name),
        ));

        //默认投稿的专题ID
        $write->add_field(array(
            'name'    => sprintf(__( '默认投稿的%sID', 'b2' ),$collection_name),
            'id'      => 'write_callections_default',
            'type'    => 'text',
            'desc'=>sprintf(__('请直接填写%sID，不设置则默认不设置%s','b2'),$collection_name,$collection_name)
        ));

        $write->add_field(array(
            'name' => __('哪些等级投稿时直接发布，不用审核','b2'),
            'id'   => 'write_post_role',
            'type' => 'multicheck_inline',
            'options'=>$setting_lvs,
            'desc'=> __('选择之后，这些等级的用户发布文章之后不用审核','b2')
        ));

        $write->add_field(array(
            'name'=>__('投稿自定义字段设置','b2'),
            'id'=>'write_custom_code',
            'type'=>'select',
            'options'=>array(
                0=>__('不使用','b2'),
                1=>__('使用','b2')
            ),
            'default'=>0,
            'desc'=>__('投稿中的自定义字段，前端不会显示，需要站长自己写代码去使用自定义字段','b2'),
        ));

        $custom_code = $write->add_field( array(
            'id'          => 'write_custom_group',
            'type'        => 'group',
            'options'     => array(
                'group_title'       => __( '自定义字段{#}', 'b2' ), 
                'add_button'        => __( '添加新的自定义字段', 'b2' ),
                'remove_button'     => __( '删除自定义字段', 'b2' ),
                'sortable'          => true,
                'closed'         => true, 
                'remove_confirm' => __( '确定要删除这个自定义字段吗？', 'b2' ), 
            )
        ));

        $write->add_group_field( $custom_code, array(
            'name' => __('自定义字段名称','b2'),
            'id'   => 'name',
            'type' => 'text',
            'desc'=>sprintf(__('提示用户要设置的是什么，比如：%s水果%s','b2'),'<code>','</code>')
        ) );

        $write->add_group_field( $custom_code, array(
            'name' => __('自定义字段key','b2'),
            'id'   => 'key',
            'type' => 'text',
            'desc'=>sprintf(__('请使用英文，比如%sfruit_name%s（水果名称）','b2'),'<code>','</code>')
        ) );

        $write->add_group_field( $custom_code, array(
            'name' => __('描述','b2'),
            'id'   => 'desc',
            'type' => 'textarea_small',
            'desc'=>sprintf(__('向用户说明这个选项如何使用，比如：%s请选择您喜欢的水果%s','b2'),'<code>','</code>')
        ) );

        $write->add_group_field( $custom_code, array(
            'name' => __('表单形式','b2'),
            'id'   => 'type',
            'type' => 'select',
            'options'=>array(
                'text'=>__('单行文本','b2'),
                'textarea'=>__('多行文本','b2'),
                'radio'=>__('单选','b2'),
                'checkbox'=>__('多选','b2')
            )
        ) );

        $write->add_group_field( $custom_code, array(
            'name' => __('待选值','b2'),
            'id'   => 'value',
            'type' => 'textarea',
            'desc'=>sprintf(__('如果表单形式选择单选，或者多选，请填写待选值，否则请留空。每组值占一行，推荐使用%sapple=苹果%s这种形式，%sapple%s是存入数据库里的值，%s苹果%s是显示出来给用户看的（英文的值方便查找与管理）。比如：%sapple=苹果%sorange=橘子%s'),'<code>','</code>','<code>','</code>','<code>','</code>','<br><code>','</code><br><code>','</code>')
        ) );

        $write->add_field(array(
            'before_row'=>'<h2>'.__('媒体设置','b2').'</h2><p>'.__('媒体设置全站生效','b2').'</p>',
            'name'    => __( '是否启用webp格式', 'b2' ),
            'id'      => 'write_image_webp',
            'type'    => 'select',
            'options'=>array(
                1=>__('启用webp格式图片','b2'),
                0=>__('禁用webp格式图片','b2')
            ),
            'default'=>self::$default_settings['write_image_webp'],
            'desc'=>__('webp格式的图片可以大量节省您的宽带，优化网站打开速度。建议开启。如果您使用的远程图片，则不能支持。','b2')
        ));

        $write->add_field(array(
            'name'    => __( '图片网址批量替换', 'b2' ),
            'id'      => 'write_image_replace',
            'type'    => 'textarea',
            'options'=>array(
                1=>__('启用裁剪','b2'),
                0=>__('禁止裁剪','b2')
            ),
            'desc'=>'如果您的某些图片地址变了，可以使用此功能批量替换要显示的图片网址。如果不使用，请留空。按照 <code>旧网址<span class="red">|</span>新网址</code> 的格式填写要替换的网址，每组网址占一行。比如 <br><code>https://a.com<span class="red">|</span>https://b.com</code><br><code>https://c.com<span class="red">|</span>https://d.com</code>',
        ));

        $write->add_field(array(
            'name'    => __( '是否允许自动裁剪缩略图', 'b2' ),
            'id'      => 'write_image_crop',
            'type'    => 'select',
            'options'=>array(
                1=>__('启用裁剪','b2'),
                0=>__('禁止裁剪','b2')
            ),
            'default'=>self::$default_settings['write_image_crop'],
            'desc'=>__('裁剪缩略图可以大量节省您的宽带，优化网站打开速度。建议开启。如果您使用的远程图片，则不能支持。','b2')
        ));

        $write->add_field(array(
            'name'    => __( '是否开启懒加载功能', 'b2' ),
            'desc'    => __( '此设置全站生效', 'b2' ),
            'id'=>'write_image_lazyload',
            'type'=>'select',
            'options'=>array(
                1=>__('开启','b2'),
                0=>__('关闭','b2')
            ),
            'default'=>self::$default_settings['write_image_lazyload']
        ));

        $write->add_field(array(
            'name' => __('哪些等级允许上传图片？','b2'),
            'id'   => 'write_image_role',
            'type' => 'multicheck_inline',
            'options'=>$setting_lvs,
            'desc'=> __('这里设置以后，全站生效，选择之后，只有这些等级允许上传图片','b2')
        ));

        $write->add_field(array(
            'name'    => __( '允许上传图片的最大体积', 'b2' ),
            'id'      => 'write_image_size',
            'type'    => 'text',
            'default'=>self::$default_settings['write_image_size'],
            'desc'=>__('这里设置以后，全站生效，全站的图片体积都不允许超过此范围，单位是M，请直接填写数字','b2')
        ));

        $write->add_field(array(
            'name' => __('哪些等级允许上传视频？','b2'),
            'id'   => 'write_video_role',
            'type' => 'multicheck_inline',
            'options'=>$setting_lvs,
            'desc'=> __('这里设置以后，全站生效，选择之后，只有这些等级允许上传视频','b2')
        ));

        $write->add_field(array(
            'name'    => __( '允许上传视频的最大体积', 'b2' ),
            'id'      => 'write_video_size',
            'type'    => 'text',
            'default'=>self::$default_settings['write_video_size'],
            'desc'=>__('这里设置以后，全站生效，全站上传的视频体积都不允许超过此范围，单位是M，请直接填写数字','b2')
        ));

        $write->add_field(array(
            'name' => __('哪些等级允许上传文件？','b2'),
            'id'   => 'write_file_role',
            'type' => 'multicheck_inline',
            'options'=>$setting_lvs,
            'desc'=> __('这里设置以后，全站生效，选择之后，只有这些等级允许上传文件','b2')
        ));

        $write->add_field(array(
            'name'    => __( '允许上传文件的最大体积', 'b2' ),
            'id'      => 'write_file_size',
            'type'    => 'text',
            'default'=>self::$default_settings['write_file_size'],
            'desc'=>__('这里设置以后，全站生效，全站上传的文件体积都不允许超过此范围，单位是M，请直接填写数字','b2')
        ));
    } 

    public static function pay_settings(){
        $pay = new_cmb2_box(array(
            'id'           => 'b2_normal_pay_options_page',
            'tab_title'    => __('支付设置','b2'), 
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_normal_pay',
            'parent_slug'     => 'admin.php?page=b2_normal_main',
            'tab_group'    => 'b2_normal_options',
        ));

        //选择支付宝支付方式
        $pay->add_field(array(
            'name'    => __( '是否开启paypal', 'b2' ),
            'id'      => 'paypal_open',
            'type'    => 'select',
            'options' => array(
                '0' => __( '关闭', 'b2' ),
                '1'=>__('开启','b2')
            ),
            'default' => '0',
            'before_row'=>'<p class="red">'.__('第三方支付不能保证100%安全，我们只对接口做兼容，其他请自行负责。请不要使用个人支付接口从事违法活动，否则删除授权，并向公安机关举报','b2').'</p>'
        ));

        //选择支付宝支付方式
        $pay->add_field(array(
            'name'    => __( '支付宝', 'b2' ),
            'id'      => 'alipay',
            'type'    => 'select',
            'options' => array(
                '0' => __( '关闭', 'b2' ),
                'alipay_normal'  => __( '支付宝官方（推荐使用）', 'b2' ),
                'xunhu'  => __( '迅虎支付（支持H5支付）（推荐使用）', 'b2' ),
                'alipay_hupijiao'  => __( '虎皮椒支付', 'b2' ),
                'mapay'=>__( '码支付', 'b2' ),
                'payjs'=>__('payjs支付'),
                'xorpay'=>__('xorpay支付'),
                'yipay'=>__('易支付','b2'),
                // 'pay020'=>__('202支付'),
                // 'suibian'=>__('随便付','b2')
            ),
            'default' => '0',
        ));

        //微信支付方式
        $pay->add_field(array(
            'name'    => __( '微信', 'b2' ),
            'id'      => 'wecatpay',
            'type'    => 'select',
            'options' => array(
                '0' => __( '关闭', 'b2' ),
                'wecatpay_normal'  => __( '微信官方（推荐使用）', 'b2' ),
                'xunhu'  => __( '迅虎支付（支持H5支付）（推荐使用）', 'b2' ),
                'wecatpay_hupijiao'  => __( '虎皮椒支付', 'b2' ),
                'mapay'=>__( '码支付', 'b2' ),
                'xorpay'=>__('xorpay支付'),
                'payjs'=>__('payjs支付'),
                'yipay'=>__('易支付','b2'),
                // 'pay020'=>__('202支付（不推荐）'),
                // 'suibian'=>__('随便付（不推荐）','b2')
            ),
            'default' => '0'
        ));

        //支付宝官方
        $pay->add_field(array(
            'name'    => __( '支付方式', 'b2' ),
            'id'      => 'alipay_type',
            'type'    => 'select',
            'options' => array(
                'normal' => __( '常规方式', 'b2' ),
                'scan'  => __( '当面付', 'b2' ),
            ),
            'desc'=>__('如果你是支付宝企业账户，推荐使用常规方式，兼容性最好。如果您是个人用户只能选择当面付，当面付支持手机和移动端支付','b2'),
            'default' => 'normal',
            'before_row'=>'<div id="alipay_normal" class="cmb-row"><h2>'.__('支付宝官方支付设置','b2').'</h2>',
        ));

        $pay->add_field(array(
            'name'    => __( 'APPID', 'b2' ),
            'id'      => 'alipay_appid',
            'type'    => 'text',
            'desc'=>__('打开链接： https://open.alipay.com 账户中心->密钥管理->开放平台密钥，填写您支付的应用的APPID','b2')
        ));

        $pay->add_field(array(
            'name'    => __( '应用私钥', 'b2' ),
            'id'      => 'alipay_private_key',
            'type'    => 'textarea',
            'desc'=>__('本主题使用的是 RSA2 算法生成的私钥。请使用 RSA2 算法来生成。','b2')
        ));

        $pay->add_field(array(
            'name'    => __( '支付宝公钥', 'b2' ),
            'id'      => 'alipay_public_key',
            'type'    => 'textarea',
            'after_row'=>'</div>',
            'desc'=>__('请在 账户中心->密钥管理->开放平台密钥，找到添加了支付功能的应用，根据你的加密类型，查看支付宝公钥。','b2')
        ));

        //选择支付宝支付方式
        $pay->add_field(array(
            'name'    => __( 'API用户名', 'b2' ),
            'id'      => 'paypal_username',
            'type'    => 'input',
            'before_row'=>'<div id="paypal_normal" class="cmb-row"><h2>'.__('Paypal官方支付设置','b2').'</h2><p>如果您的收款账户为中国大陆地区，则账户类型必须是企业。并且只能收取非中国大陆区的PayPal付款</p><p>查看你的paypal秘钥信息：https://www.paypal.com/businessprofile/mytools/apiaccess/firstparty/signature</p>',
        ));

        $pay->add_field(array(
            'name'    => __( 'API密码', 'b2' ),
            'id'      => 'paypal_password',
            'type'    => 'input',
            'attributes' => array(
                'type' => 'password',
            )
        ));

        $pay->add_field(array(
            'name'    => __( '签名', 'b2' ),
            'id'      => 'paypal_signature',
            'type'    => 'input',
            'attributes' => array(
                'type' => 'password',
            )
        ));

        $pay->add_field(array(
            'name'    => __( '是否开启沙箱模式', 'b2' ),
            'id'      => 'paypal_sandbox',
            'type'    => 'select',
            'options'=>array(
                '0'=>__('关闭','b2'),
                '1'=>__('开启','b2')
            ),
            'default' => self::$default_settings['paypal_sandbox'],
            'desc'=>sprintf(__('沙箱模式是PayPal支付的测试环境。如果开启沙箱模式，请使用沙箱环境生成的收款账户和付款账户进行测试。%s测试通过后生产环境使用时请关闭沙箱模式，上面PayPal收款Email请设置成真实的收款账户','b2'),'<br>'),
        ));

        $pay->add_field(array(
            'name'    => __( '结算货币', 'b2' ),
            'id'      => 'paypal_currency_code',
            'type'    => 'select',
            'options'=>array(
                'AUD'=>'Australian dollar (AUD)',
                'BRL'=>'Brazilian real (BRL)',
                'CAD'=>'Canadian dollar (CAD)',
                'CNY'=>'Chinese Renmenbi (CNY)',
                'CZK'=>'Czech koruna (CZK)',
                'DKK'=>'Danish krone (DKK)',
                'EUR'=>'Euro (EUR)',
                'HKD'=>'Hong Kong dollar (HKD)',
                'HUF'=>'Hungarian forint (HUF)',
                'INR'=>'Indian rupee (INR)',
                'ILS'=>'Israeli new shekel (ILS)',
                'JPY'=>'Japanese yen (JPY)',
                'MYR'=>'Malaysian ringgit (MYR)',
                'MXN'=>'Mexican peso (MXN)',
                'TWD'=>'New Taiwan dollar (TWD)',
                'NZD'=>'New Zealand dollar (NZD)',
                'NOK'=>'Norwegian krone (NOK)',
                'PHP'=>'Philippine peso (PHP)',
                'PLN'=>'Polish złoty (PLN)',
                'GBP'=>'Pound sterling (GBP)',
                'RUB'=>'Russian ruble (RUB)',
                'SGD'=>'Singapore dollar (SGD)',
                'SEK'=>'Swedish krona (SEK)',
                'CHF'=>'Swiss franc (CHF)',
                'THB'=>'Thai baht (THB)',
                'USD'=>'United States dollar (USD)'
            ),
            'default' => self::$default_settings['paypal_currency_code'],
            'desc'=>__('请选择结算货币','b2')
        ));

        $pay->add_field(array(
            'name'    => __( '汇率', 'b2' ),
            'id'      => 'paypal_rate',
            'type'    => 'text',
            'default' => self::$default_settings['paypal_rate'],
            'desc'=>__('转换成paypal支付货币的汇率，比如1人民币兑换0.1430美元，这里就填0.1430','b2'),
            'after'=>'</div>'
        ));

        //202支付
        $pay->add_field(array(
            'name'    => __( '020pay付款方式', 'b2' ),
            'id'      => '020pay_type',
            'type'    => 'select',
            'options' => array(
                1 => __( '跳转页面支付', 'b2' ),
                4  => __( '二维码支付', 'b2' )
            ),
            'default'=>1,
            'desc'=>__('二维码支付需要您在020pay支付后台上传您对应的金额二维码，如果没有上传可能会无法回调，如果您之不知道怎么使用，建议直接用跳转模式','b2'),
            'before_row'=>'<div id="pay020" class="cmb-row" style="display:none"><h2>'.__('020pay支付设置（支持支付宝和微信）','b2').'</h2><p>202支付官方网址：https://020zf.com/</p>',
        ));

        $pay->add_field(array(
            'name'    => __( 'identification', 'b2' ),
            'id'      => 'pay020_identification',
            'type'    => 'text',
            'desc'=>__('您的商户唯一标识，注册后在账号设置->API接口信息里获得','b2'),
        ));

        $pay->add_field(array(
            'name'    => __( 'token', 'b2' ),
            'id'      => 'pay020_token',
            'type'    => 'text',
            'desc'=>__('您的商户唯一标识，注册后在账号设置->API接口信息里获得','b2'),
            'after_row'=>'</div>',
        ));

        //随便付
        $pay->add_field(array(
            'name'    => __( '商户ID', 'b2' ),
            'id'      => 'suibian_id',
            'type'    => 'text',
            'desc'=>__('请前往后台查看','b2'),
            'before_row'=>'<div id="suibian" class="cmb-row" style="display:none"><h2>'.__('随便付设置','b2').'</h2><p>随便付官方网址：https://www.sbpay.cn</p>',
        ));

        $pay->add_field(array(
            'name'    => __( '商户Key', 'b2' ),
            'id'      => 'suibian_key',
            'type'    => 'text',
            'desc'=>__('请前往后台查看','b2'),
            'after_row'=>'</div>',
        ));

        //迅虎支付
        $pay->add_field(array(
            'name'    => __( 'MCHID', 'b2' ),
            'id'      => 'xunhu_appid',
            'type'    => 'text',
            'desc'=>__('进入迅虎支付平台，查看商户ID','b2'),
            'before_row'=>'<div id="xunhu" class="cmb-row"><h2>'.__('迅虎支付设置（支持支付宝和微信）','b2').'</h2>',
        ));

        $pay->add_field(array(
            'name'    => __( 'PRIVATE KEY', 'b2' ),
            'id'      => 'xunhu_appsecret',
            'type'    => 'text',
            'desc'=>__('进入迅虎支付平台，查看应用PRIVATE KEY','b2'),
        ));

        $pay->add_field(array(
            'name'    => __( '支付网关', 'b2' ),
            'id'      => 'xunhu_gateway',
            'type'    => 'text',
            'default'=>'https://admin.xunhuweb.com',
            'desc'=>__('不知道这个做什么用的请保持默认即可，默认网关：https://admin.xunhuweb.com。迅虎支付申请地址：<a href="https://pay.xunhuweb.com" target="_blank">https://pay.xunhuweb.com</a>','b2'),
            'after_row'=>'</div>'
        ));

        //虎皮椒支付宝
        $pay->add_field(array(
            'name'    => __( 'appid', 'b2' ),
            'id'      => 'alipay_hupijiao_appid',
            'type'    => 'text',
            'before_row'=>'<div id="alipay_hupijiao" class="cmb-row"><h2>'.__('虎皮椒支付宝设置（支持支付宝支付）','b2').'</h2>',
        ));

        $pay->add_field(array(
            'name'    => __( 'appsecret', 'b2' ),
            'id'      => 'alipay_hupijiao_appsecret',
            'type'    => 'text',
        ));

        $pay->add_field(array(
            'name'    => __( '支付网关', 'b2' ),
            'id'      => 'alipay_hupijiao_gateway',
            'type'    => 'text',
            'default'=>'https://api.xunhupay.com/payment/do.html',
            'desc'=>__('留空则使用默认网关，如无特别升级，请留空','b2'),
            'after_row'=>'</div>'
        ));

        //虎皮椒微信
        $pay->add_field(array(
            'name'    => __( 'appid', 'b2' ),
            'id'      => 'wecatpay_hupijiao_appid',
            'type'    => 'text',
            'before_row'=>'<div id="wecatpay_hupijiao" class="cmb-row"><h2>'.__('虎皮椒微信支付设置（支持微信支付）','b2').'</h2>',
        ));

        $pay->add_field(array(
            'name'    => __( 'appsecret', 'b2' ),
            'id'      => 'wecatpay_hupijiao_appsecret',
            'type'    => 'text',
        ));

        $pay->add_field(array(
            'name'    => __( '支付网关', 'b2' ),
            'id'      => 'wecatpay_hupijiao_gateway',
            'type'    => 'text',
            'default'=>'https://api.xunhupay.com/payment/do.html',
            'desc'=>__('留空则使用默认网关，如无特别升级，请留空','b2'),
            'after_row'=>'</div>'
        ));

        //payjs
        $pay->add_field(array(
            'name'    => __( '商户号', 'b2' ),
            'id'      => 'payjs_mchid',
            'type'    => 'text',
            'desc'=>__('请登录payjs.cn会员中心查看','b2'),
            'before_row'=>'<div id="payjs" class="cmb-row"><h2>'.__('payjs支付设置（支持支付宝和微信）','b2').'</h2>',
        ));

        $pay->add_field(array(
            'name'    => __( '密钥', 'b2' ),
            'id'      => 'payjs_key',
            'type'    => 'text',
            'desc'=>__('请登录payjs.cn会员中心查看。','b2'),
            'after_row'=>'</div>'
        ));

        //易支付
        $pay->add_field(array(
            'name'    => __( '接口网址', 'b2' ),
            'id'      => 'yipay_gateway',
            'type'    => 'text',
            'desc'=>__('您的易支付接口网址','b2'),
            'before_row'=>'<div id="yipay" class="cmb-row"><h2>'.__('易支付（支持支付宝和微信）','b2').'</h2>',
        ));

        $pay->add_field(array(
            'name'    => __( '商户ID', 'b2' ),
            'id'      => 'yipay_id',
            'type'    => 'text',
            'desc'=>__('商户ID','b2'),
        ));

        $pay->add_field(array(
            'name'    => __( '商户KEY', 'b2' ),
            'id'      => 'yipay_key',
            'type'    => 'text',
            'desc'=>__('商户KEY','b2'),
            'after_row'=>'</div>'
        ));

        //码支付
        $pay->add_field(array(
            'name'    => __( '码支付付款方式', 'b2' ),
            'id'      => 'mapay_type',
            'type'    => 'select',
            'options' => array(
                1 => __( '跳转页面支付', 'b2' ),
                4  => __( '二维码支付', 'b2' )
            ),
            'default'=>1,
            'desc'=>__('二维码支付需要您在码支付后台上传您对应的金额二维码，如果没有上传可能会无法回调，如果您之不知道怎么使用，建议直接用跳转模式','b2'),
            'before_row'=>'<div id="mapay" class="cmb-row"><h2>'.__('码支付设置（支持支付宝和微信）','b2').'</h2>',
        ));

        $pay->add_field(array(
            'name'    => __( '码支付ID', 'b2' ),
            'id'      => 'mapay_id',
            'type'    => 'text',
            'desc'=>__('码支付后台->系统设置->码支付ID','b2'),
        ));

        $pay->add_field(array(
            'name'    => __( '通讯密钥', 'b2' ),
            'id'      => 'mapay_key',
            'type'    => 'text',
            'desc'=>__('码支付后台->系统设置->通讯密钥','b2'),
            'after_row'=>'</div>'
        ));

        //xorpay支付
        $pay->add_field(array(
            'name'    => __( 'aid', 'b2' ),
            'id'      => 'xorpay_aid',
            'type'    => 'text',
            'desc'=>__('xorpay 后台，应用配置中查看','b2'),
            'before_row'=>'<div id="xorpay" class="cmb-row"><h2>'.__('xorpay支付设置（支持支付宝和微信）','b2').'</h2><p>申请地址:<a href="https://xorpay.com?r=lemolee" target="_blank">xorpay</a></p>'
        ));

        $pay->add_field(array(
            'name'    => __( 'app appsecret', 'b2' ),
            'id'      => 'xorpay_secret',
            'type'    => 'text',
            'desc'=>__('xorpay 后台，应用配置中查看','b2'),
            'after_row'=>'</div>'
        ));

        //微信官方支付
        $pay->add_field(array(
            'name'    => __( 'APPID', 'b2' ),
            'id'      => 'wecatpay_appid',
            'type'    => 'text',
            'desc'=>__('进入微信公众平台（服务号）->左侧菜单最下面->基本配置->开发者ID(AppID)','b2'),
            'before_row'=>'<div id="wecatpay_normal" class="cmb-row"><h2>'.__('微信官方支付设置','b2').'</h2>
            <p>使用条件：</p><p>1、需要开通微信公众号和微信商户；</p><p>2、并且微信商户->产品中心 开通了 Native 支付，H5支付（手机浏览器使用）</p><p>3、并且微信商户->产品中心->开发配置->填写JSAPI支付目录和H5支付域名</p><p>4、并且微信服务号->基本配置里面设置一下IP白名单</p><p>5、您的公众号和商户已经绑定到了一起</p>',
        ));

        $pay->add_field(array(
            'name'    => __( '微信支付商户号', 'b2' ),
            'id'      => 'wecatpay_mch_id',
            'type'    => 'text',
            'desc'=>__('进入微信商户平台->商户信息->基本信息->商户号，','b2'),
        ));

        $pay->add_field(array(
            'name'    => __( '商户支付密钥', 'b2' ),
            'id'      => 'wecatpay_secret',
            'type'    => 'text',
            'desc'=>__('进入微信商户平台->API安全->API密钥','b2'),
            'after_row'=>'</div>'
        ));
    }

    public static function gold_settings(){
        $gold = new_cmb2_box(array(
            'id'           => 'b2_gold_options_page',
            'tab_title'    => __('财富设置','b2'), 
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_normal_gold',
            'parent_slug'     => 'admin.php?page=b2_normal_main',
            'tab_group'    => 'b2_normal_options',
        ));

        $gold->add_field(array(
            'name' => __('是否允许提现','b2'),
            'id'      => 'gold_tx',
            'type'    => 'select',
            'options' => array(
                0=>__( '禁止提现', 'b2' ),
                1=>__( '允许提现', 'b2' )
            ),
            'before_row'=>'<h2>'.__('提现设置','b2').'</h2>'
        ) );

        $gold->add_field(array(
            'name' => sprintf(__('%s超过多少允许提现','b2'),B2_MONEY_NAME),
            'id'   => 'gold_money',
            'default'=> 50,
            'type' => 'text_money',
            'sanitization_cb' => 'b2_sanitize_number',
            'before_field' => B2_MONEY_SYMBOL,
            'desc'=>sprintf(__('用户%s大于或者等于这个金额方可允许提现','b2'),B2_MONEY_NAME)
        ));

        $gold->add_field(array(
            'name' => __('网站提成比例','b2'),
            'id'   => 'gold_tc',
            'default'=> '0.05',
            'type' => 'text',
            'desc'=>__('默认 0.05 （5%），如果网站不抽成，请设置为0。','b2')
        ));
        
        $gold->add_field(array(
            'name' => __('是否允许卡密充值？','b2'),
            'id'      => 'card_allow',
            'type'    => 'select',
            'default'=>self::$default_settings['card_allow'],
            'options' => array(
                0=>__( '禁止使用', 'b2' ),
                1=>__( '允许使用', 'b2' )
            ),
            'before_row'=>'<h2>'.__('卡密充值设置','b2').'</h2>'
        ) );

        $gold->add_field(array(
            'name' => __('卡密充值弹窗提示信息','b2'),
            'id'      => 'card_text',
            'type'    => 'textarea',
            'desc'=>__('通常用来提示用户去哪购买的文字','b2'),
            'default'=>self::get_default_settings('card_text')
        ) );

        $gold->add_field(array(
            'name' => __('1元人民币兑换多少积分','b2'),
            'id'      => 'credit_dh',
            'type'    => 'text',
            'default'=>self::$default_settings['credit_dh'],
            'desc'=>__('默认1元兑换38积分','b2'),
            'before_row'=>'<h2>'.__('积分兑换','b2').'</h2><p>为了避免用户故意刷积分的问题存在，暂时只限制允许购买积分，不允许积分兑换'.B2_MONEY_NAME.'</p>'
        ) );

        $gold->add_field(array(
            'name' => __('多少元起兑','b2'),
            'id'      => 'credit_qc',
            'type'    => 'text',
            'default'=>self::$default_settings['credit_qc'],
            'desc'=>__('默认10元起兑','b2'),
        ) );

        $gold->add_field(array(
            'name' => __('新用户注册','b2'),
            'id'      => 'credit_login',
            'type'    => 'text',
            'default'=>self::$default_settings['credit_login'],
            'desc'=>__('奖励对象：注册者。默认260分。首次注册时奖励。','b2'),
            'before_row'=>'<h2>'.__('积分奖励','b2').'</h2><p>'.__('如果不允许奖励积分，请设置为0').'</p>'
        ) );

        $gold->add_field(array(
            'name' => __('发表文章','b2'),
            'id'      => 'credit_post',
            'type'    => 'text',
            'default'=>self::$default_settings['credit_post'],
            'desc'=>__('奖励对象：文章作者。默认200分。草稿状态不算，只有审核通过才算','b2'),
        ) );

        $circle_name = b2_get_option('normal_custom','custom_circle_name');
        $newsflashes_name = b2_get_option('normal_custom','custom_newsflashes_name');

        $gold->add_field(array(
            'name' => __('评论奖励','b2'),
            'id'      => 'credit_comment',
            'type'    => 'text',
            'default'=>self::$default_settings['credit_comment'],
            'desc'=>__('奖励对象：发表评论者。默认20分。（此评论积分设置包含站内所有的评论）','b2'),
        ) );

        $gold->add_field(array(
            'name' => __('文章（帖子）被评论奖励','b2'),
            'id'      => 'credit_post_comment',
            'type'    => 'text',
            'default'=>self::$default_settings['credit_post_comment'],
            'desc'=>__('奖励对象：文章（帖子）作者。默认5分。','b2'),
        ) );

        $gold->add_field(array(
            'name' => __('评论被点赞','b2'),
            'id'      => 'credit_comment_up',
            'type'    => 'text',
            'default'=>self::$default_settings['credit_comment_up'],
            'desc'=>__('奖励对象：评论作者。取消点赞评论作者会扣除相应的积分，默认5分。（此评论积分设置包含站内所有的评论）','b2'),
        ) );

        $gold->add_field(array(
            'name' => __('关注或被关注','b2'),
            'id'      => 'credit_follow',
            'type'    => 'text',
            'default'=>self::$default_settings['credit_follow'],
            'desc'=>__('奖励对象：点击关注的人或被关注的人，取消关注会扣除相应的积分。默认5分。','b2'),
        ) );

        $gold->add_field(array(
            'name' => __('被点赞','b2'),
            'id'      => 'credit_post_up',
            'type'    => 'text',
            'default'=>self::$default_settings['credit_post_up'],
            'desc'=>sprintf(__('文章、%s话题、等被点赞。奖励对象：文章（或帖子）作者，如果取消点赞会扣除相应的积分。默认10分。','b2'),$circle_name),
        ) );

        $infomation_name = b2_get_option('normal_custom','custom_infomation_name');

        $gold->add_field(array(
            'name' => sprintf(__('发布%s帖子','b2'),$circle_name),
            'id'      => 'credit_topic',
            'type'    => 'text',
            'default'=>self::$default_settings['credit_topic'],
            'desc'=>__('奖励对象：帖子作者。默认50分。','b2'),
        ) );

        $gold->add_field(array(
            'name' => sprintf(__('发布%s帖子','b2'),$infomation_name),
            'id'      => 'credit_infomation',
            'type'    => 'text',
            'default'=>self::$default_settings['credit_infomation'],
            'desc'=>__('奖励对象：帖子作者。默认50分。','b2'),
        ) );

        $gold->add_field(array(
            'name' => sprintf(__('发布%s','b2'),$newsflashes_name),
            'id'      => 'credit_newsflashes',
            'type'    => 'text',
            'default'=>self::$default_settings['credit_newsflashes'],
            'desc'=>sprintf(__('发布%s，并且已经审核通过，奖励对象：发布%s的人。默认20分。','b2'),$newsflashes_name,$newsflashes_name),
        ) );

        $gold->add_field(array(
            'name' => __('签到','b2'),
            'id'      => 'credit_qd',
            'type'    => 'text',
            'default'=>self::$default_settings['credit_qd'],
            'desc'=>__('奖励对象：当前登录用户，登录用户每日签到将会随机获得积分，如果是固定值请使用 xx-xx 例如 100-100（中间横杠不可缺失）。','b2'),
        ) );

        $gold->add_field(array(
            'name' => __('签到填坑倍数','b2'),
            'id'      => 'tk_bs',
            'type'    => 'text',
            'default'=>self::$default_settings['tk_bs'],
            'desc'=>sprintf(__('请参考%s填坑说明%s','b2'),'<a href="'.b2_get_custom_page_url('mission').'/today" target="_blank">','</a>'),
        ) );
    }

    public static function task_settings(){
        $task = new_cmb2_box(array(
            'id'           => 'b2_task_options_page',
            'tab_title'    => __('任务设置','b2'), 
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_normal_task',
            'parent_slug'     => 'admin.php?page=b2_normal_main',
            'tab_group'    => 'b2_normal_options',
        ));

        foreach(b2_task_arg() as $k=>$v){
            if($k === 'task_post'){
                $task->add_field(array(
                    'name' => $v['name'].__('任务次数'),
                    'id'      => $k,
                    'type'    => 'text',
                    'default'=>$v['times'],
                    'before_row'=>'<h2>每日任务次数</h2><p>1、如果不启用此任务，请将任务数量设置为0，此时用户奖励积分的次数将没有限制</p><p>2、奖励的金额是财富设置中的积分金额</p><p>3、超过这些次数以后将不再增加积分</p><p>4、每天重置次数</p>'
                ));
            }else{
                $task->add_field(array(
                    'name' => $v['name'].__('任务次数'),
                    'id'      => $k,
                    'type'    => 'text',
                    'default'=>$v['times'],
                ));
            }
        }

        $task->add_field(array(
            'name' => __('连续签到N天，奖励N积分','b2'),
            'id'      => 'task_mission_task',
            'type'    => 'text',
            'default'=>self::$default_settings['task_mission_task'],
            'desc'=>__('想要用户连续签到3天，并且奖励100积分，则直接填3|100，如果不奖励，请之直接填0','b2'),
        ) );

        foreach (b2_task_user_arg() as $key => $value) {
            if($key === 'task_user_qq'){
                $task->add_field(array(
                    'name' => $value['name'].__('奖励积分'),
                    'id'      => $key,
                    'type'    => 'text',
                    'default'=>$value['credit'],
                    'before_row'=>'<h2>新手任务积分奖励</h2><p>1、如果不启用此任务，请将积分奖励设置为0</p>'
                ));
            }else{
                $task->add_field(array(
                    'name' => $value['name'].__('奖励积分'),
                    'id'      => $key,
                    'type'    => 'text',
                    'default'=>$value['credit'],
                ));
            }
        }

        
    }

    public static function weixin_settings(){
        $weixin = new_cmb2_box(array(
            'id'           => 'b2_weixin_options_page',
            'tab_title'    => __('微信设置','b2'), 
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_normal_weixin',
            'parent_slug'     => 'admin.php?page=b2_normal_main',
            'tab_group'    => 'b2_normal_options',
        ));

        $weixin->add_field(array(
            'name' => __('微信公众号appid','b2'),
            'id'      => 'weixin_appid',
            'type'    => 'text',
            'desc'=>__('进入微信公众平台（服务号）->左侧菜单最下面->基本配置->开发者ID(AppID)','b2'),
            'before_row'=>'<h2>'.__('微信设置','b2').'</h2><p>'.sprintf(__('填写此信息，您的网站将和微信公众号深度整合（%s必须为微信认证了的服务号，订阅号没有此功能）%s'),'<b class="red">','</b>').'</p>'
        ) );

        $weixin->add_field(array(
            'name' => __('微信公众号appsecret','b2'),
            'id'      => 'weixin_appsecret',
            'type'    => 'text',
            'desc'=>__('进入微信公众平台（服务号）->左侧菜单最下面->基本配置->开发者密码(AppSecret)','b2'),
            'after_row'=>'
            <h2>以上两个获取完成以后，请按照下面的步骤配置公众号：</h2>
            <p>1、微信公众号后台左侧菜单最下面->基本配置->IP白名单中将自己服务器的IP加入白名单</p>
            <p>2、微信公众号后台左侧菜单->公众号设置->功能设置中，请将业务域名、JS接口安全域名、网页授权域名三项按照要求填写进去，网址为您的站点域名</p>
            <p>3、自己网站的<a href="'.admin_url('/options-permalink.php').'" target="_blank">固定链接设置</a>中，最后不是斜杠结尾，如果是斜杠，请去掉</p>
            <p>4、自己网站中至少有一篇已经发布的文章</p>
            '
        ) );

        $weixin->add_field(array(
            'name' => __('Token','b2'),
            'id'      => 'weixin_token',
            'type'    => 'text',
            'before_row'=>'
            <hr><h2>微信公众号服务器配置</h2>
            <p>请进入微信公众号后台->左侧菜单->基本配置->服务器配置，然后按照下图所示进行填写</p>
            '
        ) );

        $weixin->add_field(array(
            'name' => __('EncodingAESKey','b2'),
            'id'      => 'weixin_encodingaeskey',
            'type'    => 'text',
            'after_row'=>'<p>设置方法参考如下：</p><p><img src="'.B2_THEME_URI.'/Assets/admin/images/weixservers.png'.'"></p><p>如果提交验证token失败，请检查url是否输入错误</p><p class="red">公众号后台提交此项设置以后一定记得点击启用按钮</p>'
        ) );
    }

    public static function weixin_menu(){
        $weixin = new_cmb2_box(array(
            'id'           => 'b2_weixin_menu_options_page',
            'tab_title'    => __('公众号菜单设置','b2'), 
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_normal_weixin_menu',
            'parent_slug'     => 'admin.php?page=b2_normal_main',
            'tab_group'    => 'b2_normal_options',
            'display_cb'      => array(__CLASS__,'list_option_page_cb'),
        ));

        $weixin->add_field(array(
            'name' => __('微信菜单Json数据','b2'),
            'id'      => 'weixin_menu',
            'type'    => 'textarea_code',
            'before_row'=>'<h2>微信菜单设置</h2>
            <p>1、请注意，要使用此功能，必须开通并认证了微信服务号，并且 <a href="'.admin_url().'/admin.php?page=b2_normal_weixin" target="_blank">微信设置</a> 中的各项设置填写完毕，并且微信后台启用了服务器配置。</p>
            <p>2、进入 <a href="https://wei.jiept.com/" target="_blank">微信菜单设置页面</a> 根据您的需求将菜单设置好，然后将生成的 json 数据复制到下面的设置项中，保存即可。</p>
            <p>3、修改菜单可能不会立刻生效，大概有5分钟的缓存期。</p>',
            'after_row'=>'<h2>没经验的朋友请参考下图：<h2><p><a href="https://wei.jiept.com/" target="_blank">https://wei.jiept.com/</a></p><p><img src="'.B2_THEME_URI.'/Assets/admin/images/weixinmenu.png'.'"></p>'
        ) ); 
    }

    public static function weixin_message(){
        $weixin = new_cmb2_box(array(
            'id'           => 'b2_weixin_message_options_page',
            'tab_title'    => __('模板消息设置','b2'), 
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_normal_weixin_message',
            'parent_slug'     => 'admin.php?page=b2_normal_main',
            'tab_group'    => 'b2_normal_options',
            'display_cb'      => array(__CLASS__,'list_option_page_cb'),
        ));

        $weixin->add_field(array(
            'before_row'=>'<h2>注意事项：</h2>
            <p>1、请确保已经开通并认证了微信服务号</p>
            <p>2、请确保已经配置并开启了公众号服务器：<a href="'.admin_url('admin.php?page=b2_normal_weixin').'" target="_blank">B2主题公众号服务器配置</a></p>
            <p>3、请确保已经开通了公众号<模板消息>功能。如果未开通，请前往微信公众号后台<a href="https://mp.weixin.qq.com/" target="_blank">公众号后台</a>，左侧菜单最下面，点击 新功能，找到模板消息，申请开通。申请的时候请确保选择两个行业（IT科技/互联网|电子商务 和 IT科技/IT软件与服务）</p>
            <p>4、以上准备完毕之后，请在这里开启模板消息功能并点击保存。</p>
            <p>5、因为需要用户的openid参数，所以只有绑定了微信登陆和关注了公众号的用户才会获得消息通知。</p>',
            'name' => __('是否开启微信模板消息','b2'),
            'id'      => 'weixin_message_open',
            'type'    => 'select',
            'options'=>[
                1=>__('开启','b2'),
                0=>__('关闭','b2')
            ],
            'default'=>0
        ));

        $weixin->add_field(array(
            'name' => __('接收通知的管理员ID','b2'),
            'id'      => 'admin_id',
            'type'    => 'text',
            'desc'=>__('如果站点内有新的交易，管理员会收到通知，请填写收取通知的管理员ID，前提是这个管理员必须绑定了微信登陆！','b2')
        ) ); 

        
    }
}