<?php namespace B2\Modules\Settings;

use B2\Modules\Common\User;

class Post{

    public static $default_settings = array(
        'post_show_widget'=>1,
        'post_style'=>'post-1',
        'b2_single_show_radio'=>0,
        'b2_single_show_tags'=>1
    );

    public function init(){
        add_action('cmb2_admin_init',array($this,'post_settings'));
        add_filter( 'is_protected_meta',array($this,'hidden_post_custom_field') , 10, 2 );

        add_action( 'restrict_manage_posts', [$this,'b2child_collection_taxonomy_filter'] );
    }

    /*
    * 自定义文章列表添加分类筛选
    * https://www.wpdaxue.com/taxonomy-filter-for-custom-post-type.html
    */
    public function b2child_collection_taxonomy_filter() {
        global $typenow;
        $post_type = 'post'; // 这是文章类型的slug，需要根据实际情况修改
        $taxonomy  = 'collection'; // 这是自定义分类法 taxonomy，需要根据实际修改
        if ($typenow == $post_type) {
            $selected      = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
            $info_taxonomy = get_taxonomy($taxonomy);
            wp_dropdown_categories(array(
                'show_option_all' => sprintf( __( '全部%s', 'b2' ), $info_taxonomy->label ),
                'taxonomy'        => $taxonomy,
                'name'            => $taxonomy,
                'orderby'         => 'id',
                'order'           => 'DESC',
                'selected'        => $selected,
                'hierarchical'    => true,
                'show_count'      => true,
                'hide_empty'      => true,
                'value_field'     => 'slug'
            ));
        };
    }

    public function hidden_post_custom_field( $protected, $meta_key ){
        
        if(strpos($meta_key,'b2_') !== false || strpos($meta_key,'zrz_') !== false) return true;

        return $protected;
        
    }

    public static function get_default_settings($key){
        $arr = array();

        if(isset($arr[$key])){
            return $arr[$key];
        }
    }

    public function post_settings(){

        $post_meta = new_cmb2_box(array( 
            'id'            => 'single_post_metabox',
            'title'         => __( '风格设置', 'b2' ),
            'object_types'  => array( 'post'),
            'context'       => 'normal',
            'priority'      => 'high',
            'show_names'    => true,
        ));

        $default_settings = get_option('b2_template_single');
        $default_settings = isset($default_settings['single_style_group']) ? $default_settings['single_style_group'][0] : array();

        $post_meta_svg = apply_filters('b2_admin_post_meta', array(
            'options'=>array(                
                'post-style-1' => __('纯文字','b2'), 
                'post-style-2' => __('简洁','b2'), 
                'post-style-3' => __('大图片','b2'), 
                'post-style-4' => __('小图片','b2'),
                'post-style-5' => __('视频','b2'), 
                ),
            'images' => array(                
                'post-style-1' => '/Assets/admin/images/post-style-1.svg',
                'post-style-2' => '/Assets/admin/images/post-style-2.svg',
                'post-style-3' => '/Assets/admin/images/post-style-3.svg',
                'post-style-4' => '/Assets/admin/images/post-style-4.svg',
                'post-style-5' => '/Assets/admin/images/post-style-5.svg',
                )
            ));
        $post_meta->add_field(array(
            'name'    => __( '文章样式', 'b2' ),
            'id'=>'b2_single_post_style',
            'type' => 'radio_image',
            'options' => $post_meta_svg['options'],
            'classes'=>array('cmb-type-radio-image'),
            'images_path'  => B2_THEME_URI,
            'images'       => $post_meta_svg['images'],
            'default'=>isset($default_settings['single_post_style']) ? $default_settings['single_post_style'] : 'post-style-1',
        ));

        $post_meta->add_field(array(
            'name' => __('视频查看权限','b2'),
            'id'   => 'b2_single_post_video_role',
            'type' => 'select',
            'options'=>array(
                'none'=>__('无限制','b2'),
                'money'=>__('支付费用可见','b2'),
                'credit'=>__('支付积分可见','b2'),
                'roles'=>__('限制等级可见','b2'),
                'login'=>__('登录可见','b2'),
                'comment'=>__('评论可见','b2'),
            ),
            'default'=>'none',
            'before_row'=>'<div id="post-style-5-box">'
        ));

        $post_meta->add_field(array(
            'name' => __('是否允许游客购买','b2'),
            'id'   => 'b2_video_guest_buy',
            'type' => 'select',
            'options'=>array(
                1=>__('允许','b2'),
                0=>__('禁止','b2')
            ),
            'desc'=>__('如果是支付费用查看视频，您可以开启游客支付功能。只支持支付费用可见。','b2'),
            'default'=>0,
        ));

        $post_meta->add_field(array(
            'name' => __('需要支付的金额','b2'),
            'id'   => 'b2_single_post_video_money',
            'type' => 'text_money',
            'sanitization_cb' => 'b2_sanitize_number',
            'before_field' => B2_MONEY_SYMBOL,
            'desc'=> sprintf(__('请直接填写数字，比如%s元','b2'),'<code>100</code>'),
        ));

        $post_meta->add_field(array(
            'name' => __('需要支付的积分','b2'),
            'id'   => 'b2_single_post_video_credit',
            'type' => 'text',
            'desc'=> sprintf(__('请直接填写数字，比如%s积分','b2'),'<code>100</code>'),
        ));

        $lvs = User::get_user_roles();

        $setting_lvs = array();
        foreach($lvs as $k => $v){
            $setting_lvs[$k] = $v['name'];
        }

        $post_meta->add_field(array(
            'name' => __('允许查看的用户组','b2'),
            'id'   => 'b2_single_post_video_roles',
            'type' => 'multicheck_inline',
            'options'=>$setting_lvs,
            'desc'=> __('请选择允许查看视频的用户组','b2'),
        ));

        $post_meta->add_field(array(
            'name' => __('是否允许自动播放','b2'),
            'id'   => 'b2_single_post_video_auto_pay',
            'type' => 'select',
            'options'=>array(
                1=>__('自动播放','b2'),
                0=>__('禁止自动播放','b2')
            ),
            'default'=>1,
            'desc'=> __('如果设置自动播放，打开页面以后视频会自动播放（因为浏览器的特殊配置，不能保证所有环境都能自动播放）','b2'),
        ));

        $video_group = $post_meta->add_field( array(
            'id'          => 'b2_single_post_video_group',
            'type'        => 'group',
            'description' => __( '视频设置', 'b2' ),
            'options'     => array(
                'group_title'       => __( '视频{#}', 'b2' ),
                'add_button'        => __( '添加新视频', 'b2' ),
                'remove_button'     => __( '删除视频', 'b2' ),
                'sortable'          => true,
                'closed'         => true,
                'remove_confirm' => __( '确定要删除这个视频吗？', 'b2' ),
            )
        ));

        $post_meta->add_group_field( $video_group, array(
            'name' => __('Dogecloud 视频ID','b2'),
            'id'   => 'dogecloud_id',
            'type' => 'text',
            'desc'=> '<p>'.sprintf(__('如果您使用了 <a href="https://www.dogecloud.com/" target="_blank">Dogecloud</a>，可以通过视频ID直接获取视频信息，前提是已经设置了两个key：<a target="_blank" href="'.admin_url('/admin.php?page=b2_template_single').'">dogecloud 设置</a>，没有使用 Dogecloud 此处留空即可','b2')).'</p><p>填写完视频ID请点 <a href="javascript:void(0)" @click="getVideo" class="get-video-button">获取</a></p>',
        ) );

        $post_meta->add_group_field( $video_group, array(
            'name' => __('视频标题','b2'),
            'id'   => 'title',
            'type' => 'text',
            'desc'=> sprintf(__('视频的标题，比如%s，如果此标题上面有总标题，请使用英文竖号分割一下，比如%s','b2'),'<code>Python简介</code>','<code>Python入门课程<span class="red">|</span>Python简介</code>'),
        ) );

        $post_meta->add_group_field( $video_group, array(
            'name' => __('视频封面','b2'),
            'id'   => 'poster',
            'type' => 'file',
            'options' => array(
                'url' => true, // Hide the text input for the url
            ),
            'query_args' => array(
                'type' => array(
                    'image/gif',
                    'image/jpeg',
                    'image/png',
                ),
            ),
            'desc'=> __('视频的封面，可以直接上传到媒体库，或者复制连接到此处','b2'),
        ) );

        $post_meta->add_group_field( $video_group, array(
            'name' => __('视频预览地址','b2'),
            'id'   => 'view_url',
            'type' => 'file',
            'options' => array(
                'url' => true, // Hide the text input for the url
            ),
            'text'    => array(
                'add_upload_file_text' => __('添加视频','b2') // Change upload button text. Default: "Add or Upload File"
            ),
            'query_args' => array(
                'type' => array(
                    'video/mp4',
                    'video/mpeg',
                    'application/ogg',
                ),
            ),
            'desc'=>__('用户未支付或者权限不足的情况下可以进行视频预览，如果没有支付限制，请留空（只支持类似 .mp4这种文件形式，不支持优酷等其他第三方）','b2'),
        ) );

        $post_meta->add_group_field( $video_group, array(
            'name' => __('视频地址','b2'),
            'id'   => 'url',
            'type' => 'file',
            'options' => array(
                'url' => true, // Hide the text input for the url
            ),
            'text'    => array(
                'add_upload_file_text' => __('添加视频','b2') // Change upload button text. Default: "Add or Upload File"
            ),
            'query_args' => array(
                'type' => array(
                    'video/mp4',
                    'video/mpeg',
                    'application/ogg',
                ),
            ),
            'desc'=>__('请上传视频，或者直接输入视频地址，或直接粘贴B站视频页面网址到此处。此为必填，否则前台无法播放视频。（只支持类似 .mp4这种文件，或者B站视频，不支持优酷等第三方）','b2'),
        ) );

        //是否显示标签
        $post_meta->add_field(array(
            'name' => __('是否显示标签','b2'),
            'id'   => 'b2_single_show_tags',
            'type'             => 'select',
            'options'          => array(
                1 => __( '显示', 'b2' ),
                0   => __( '隐藏', 'b2' ),
                'global'=>__( '使用全局设置', 'b2' )
            ),
            'default'=>'global',
            'desc'=>__('如果开启，将会显示在文章页面底部。使用全局设置，将会调用模块设置->文章内页中的相关设置','b2'),
            'before_row'=>'</div>'
        ) );

        //是否显示语音朗读
        $post_meta->add_field(array(
            'name' => __('是否显示语音朗读','b2'),
            'id'   => 'b2_single_show_radio',
            'type'             => 'select',
            'options'          => array(
                1 => __( '显示', 'b2' ),
                0   => __( '隐藏', 'b2' ),
                'global'=>__( '使用全局设置', 'b2' )
            ),
            'default'=>'global',
            'desc'=>__('如果开启，将会显示在文章页面内容顶部。使用全局设置，将会调用模块设置->文章内页中的相关设置','b2'),
        ) );

        //是否显示侧边栏
        $post_meta->add_field(array(
            'name' => __('是否显示侧边栏小工具','b2'),
            'id'   => 'b2_single_post_sidebar_show',
            'type'             => 'select',
            'options'          => array(
                1 => __( '显示', 'b2' ),
                0   => __( '隐藏', 'b2' ),
                'global'=>__( '使用全局设置', 'b2' )
            ),
            'default'=>'global',
            'desc'=>__('使用全局设置，将会调用模块设置->文章内页中的相关设置','b2'),
        ) );

        //是否使用自带幻灯
        $post_meta->add_field(array(
            'name' => __('是否使用主题自带文章内图片点击放大功能','b2'),
            'id'   => 'b2_single_post_slider',
            'type'             => 'select',
            'default'          => 'global',
            'desc'=>__( '如果开启，将会显示在文章页面内容顶部。使用全局设置，将会调用模块设置->文章内页中的相关设置', 'b2' ),
            'options'          => array(
                1 => __( '开启', 'b2' ),
                0   => __( '关闭', 'b2' ),
                'global'=>__( '使用全局设置', 'b2' )
            ),
        ) );

        //文章顶部广告位代码
        $post_meta->add_field( array(
            'name' => __('文章顶部广告位代码','b2'),
            'id'   => 'b2_single_post_top_ads',
            'desc'=>__('如果不设置广告位请留空','b2'),
            'type' => 'textarea_code',
            'options' => array( 'disable_codemirror' => true ),
        ));

        //文章顶部广告位代码
        $post_meta->add_field(array(
            'name' => __('文章低部广告位代码','b2'),
            'id'   => 'b2_single_post_bottom_ads',
            'desc'=>__('如果不设置广告位请留空','b2'),
            'type' => 'textarea_code',
            'options' => array( 'disable_codemirror' => true ),
        ));
        
        $this->post_side_settings();
        $this->post_download_settings();
        $this->document_order();
    }

    public function post_side_settings(){

        //是否允许分利
        $distribution_meta = new_cmb2_box(array( 
            'id'            => 'distribution_post_metabox',
            'title'         => __( '推广设置', 'b2' ),
            'object_types'  => array( 'post','shop','page','document'),
            'context'       => 'side',
            'priority'      => 'high',
            'show_names'    => true,
        ));

        $distribution_meta->add_field(array(
            'name' => __('是否允许分红','b2'),
            'id'   => 'b2_allow_distribution',
            'type' => 'select',
            'options'=>array(
                -1=>__('使用默认设置','b2'),
                1=>__('允许分红','b2'),
                0=>__('不允许分红','b2')
            ),
            'default'=>-1,
            'desc'=> __('推广设置中有一个总开关，使用默认设置则总开关生效，如果在此设置则优先生效','b2'),
        ));

        $post_meta = new_cmb2_box(array( 
            'id'            => 'single_post_side_metabox',
            'title'         => __( '隐藏内容阅读权限', 'b2' ),
            'object_types'  => array( 'post','document','page','shop'), // Post type
            'context'       => 'side',
            'priority'      => 'high',
            'show_names'    => true,
        ));

        $post_meta->add_field(array(
            'name' => __('阅读权限','b2'),
            'id'   => 'b2_post_reading_role',
            'type' => 'select',
            'options'=>array(
                'none'=>__('无限制','b2'),
                'money'=>__('支付费用可见','b2'),
                'credit'=>__('支付积分可见','b2'),
                'roles'=>__('限制等级可见','b2'),
                'login'=>__('登录可见','b2'),
                'comment'=>__('评论可见','b2'),
            ),
            'desc'=> __('需要在文章中使用隐藏内容短代码工具将需要隐藏的内容包裹起来，否则不生效','b2'),
            'default'=>'none',
        ));

        $post_meta->add_field(array(
            'name' => __('是否允许游客购买','b2'),
            'id'   => 'b2_hidden_guest_buy',
            'type' => 'select',
            'options'=>array(
                1=>__('允许','b2'),
                0=>__('禁止','b2')
            ),
            'desc'=>__('如果是支付费用查看隐藏内容，您可以开启游客支付功能。只支持支付费用查看。','b2'),
            'default'=>0,
        ));

        $post_meta->add_field(array(
            'name' => __('需要支付的金额','b2'),
            'id'   => 'b2_post_money',
            'type' => 'text_money',
            'sanitization_cb' => 'b2_sanitize_number',
            'before_field' => B2_MONEY_SYMBOL,
            'desc'=> sprintf(__('请直接填写数字，比如%s元','b2'),'<code>100</code>'),
        ));

        $post_meta->add_field(array(
            'name' => __('需要支付的积分','b2'),
            'id'   => 'b2_post_credit',
            'type' => 'text',
            'desc'=> sprintf(__('请直接填写数字，比如%s积分','b2'),'<code>100</code>'),
        ));

        $lvs = User::get_user_roles();

        $setting_lvs = array();
        foreach($lvs as $k => $v){
            $setting_lvs[$k] = $v['name'];
        }

        $post_meta->add_field(array(
            'name' => __('允许查看的用户组','b2'),
            'id'   => 'b2_post_roles',
            'type' => 'multicheck_inline',
            'options'=>$setting_lvs,
            'desc'=> __('请选择允许查看隐藏内容的用户组','b2'),
        ));

        $post_meta->add_field(array(
            'name' => __('默认查看人数','b2'),
            'id'   => 'b2_post_hidden_count',
            'type' => 'text',
            'desc'=> __('如果隐藏内容为付费或者支付积分，没有人购买时会显示0人购买，不太好，此数据相当于伪造了付费用户的数量','b2'),
        ));

        $post_meta->add_field(array(
            'name' => __('时效设置','b2'),
            'id'   => 'b2_post_hidden_times',
            'type' => 'text',
            'desc'=> sprintf(__('用户购买以后，多少小时后过期（过期后不能再次查看付费内容，需要再次购买），请直接填写小时数字，支持小数。比如%s，%s。如果此处留空不设置，用户购买以后，永久生效。此项设置只对积分阅读或者付费阅读生效。','b2'),'<code class="red">2</code>','<code class="red">0.5</code>'),
        ));

        $post_poster = new_cmb2_box(array( 
            'id'            => 'single_post_side_poster_metabox',
            'title'         => __( '自定义海报图片', 'b2' ),
            'object_types'  => array( 'post','shop','document','page'), // Post type
            'context'       => 'side',
            'show_names'    => false,
        ));

        $post_poster->add_field(array(
            'name' => __('自定义海报图片','b2'),
            'id'   => 'b2_post_poster',
            'type' => 'file',
            'options' => array(
                'url' => true, // Hide the text input for the url
            ),
            'query_args' => array(
                'type' => array(
                    'image/gif',
                    'image/jpeg',
                    'image/png',
                ),
            ),
            'desc'=> __('如果不设置，将自动使用本文特色图，如果没有设置特色图，自动使用文章中第一张图片，都没有则使用主题自带默认图片','b2')
        ));

        $mp = new_cmb2_box(array( 
            'id'            => 'single_post_mp_back_key_metabox',
            'title'         => __( '微信关键词', 'b2' ),
            'object_types'  => array( 'post','shop','document','page'), // Post type
            'context'       => 'side',
            'show_names'    => false,
        ));

        $mp->add_field(array(
            'name' => __('微信关键词','b2'),
            'id'   => 'single_post_mp_back_key',
            'type' => 'text',
            'default'=>'',
            'desc'=> sprintf(__('如果您开通并认证了微信服务号，并且主题中设置了相关功能（%s微信设置%s），用户在微信中回复此关键词，将会自动返回此文章。','b2'),'<a href="'.admin_url('/admin.php?page=b2_normal_weixin').'" target="_blank">','</a>')
        ));

        $g_name = b2_get_option('normal_custom','custom_announcement_name');

        $post_gg = new_cmb2_box(array( 
            'id'            => 'single_post_gg_poster_metabox',
            'title'         => sprintf(__( '该文章的弹出%s', 'b2' ),$g_name),
            'object_types'  => array( 'post','shop','document','page'), // Post type
            'context'       => 'side',
            'show_names'    => true,
        ));

        $post_gg->add_field(array(
            'name' => sprintf(__('%s标题','b2'),$g_name),
            'id'   => 'b2_post_gg_title',
            'type' => 'textarea_small',
            'desc'=> __('支持 HTML，尽量短小才好看。','b2')
        ));

        $post_gg->add_field(array(
            'name' => sprintf(__('%s内容','b2'),$g_name),
            'id'   => 'b2_post_gg',
            'type' => 'textarea',
            'desc'=> sprintf(__('支持 HTML，用户每次打开该文章都会显示此%s，如果不需要显示，请留空。','b2'),$g_name)
        ));

        $gg_meta = new_cmb2_box(array( 
            'id'            => 'single_gg_metabox',
            'title'         => sprintf(__( '%s设置', 'b2' ),$g_name),
            'object_types'  => array( 'announcement'),
            'context'       => 'side',
            'priority'      => 'high',
            'show_names'    => true,
        ));

        $gg_meta->add_field(array(
            'name' => sprintf(__('弹窗%s显示条件','b2'),$g_name),
            'id'   => 'b2_gg_show',
            'type' => 'select',
            'options'=>array(
                0=>__('所有人可见','b2'),
                1=>__('登录用户可见','b2'),
                2=>__('未登录用户可见','b2'),
                3=>__('不显示弹窗','b2')
            ),
            'default'=>0,
        ));

        $gg_meta->add_field(array(
            'name' => __('弹窗间隔时间','b2'),
            'id'   => 'b2_gg_days',
            'type' => 'input',
            'desc' => sprintf(__('如果没有新的%s，弹出一次用户关闭后再隔多少天再次弹出','b2'),$g_name),
            'default'=>1,
        ));

        $gg_meta->add_field(array(
            'name' => sprintf(__('旧%s多少天后自动关闭弹出','b2'),$g_name),
            'id'   => 'b2_gg_over',
            'type' => 'input',
            'desc' => sprintf(__('如果没有新的%s发布，旧的%s多少天以后会自动失效，不再弹出。','b2'),$g_name,$g_name),
            'default'=>7,
        ));

        $news_meta = new_cmb2_box(array( 
            'id'            => 'single_news_metabox',
            'title'         => __( '来源地址', 'b2' ),
            'object_types'  => array( 'newsflashes'),
            'context'       => 'side',
            'priority'      => 'high',
            'show_names'    => false,
        ));

        $newsflashes_name = b2_get_option('normal_custom','custom_newsflashes_name');

        $news_meta->add_field(array(
            'name' => sprintf(__('%s来源地址','b2'),$newsflashes_name),
            'id'   => 'b2_newsflashes_from',
            'type' => 'text',
            'desc'=> sprintf(__('%s来源地址','b2'),$newsflashes_name)
        ));
    }

    public function post_download_settings(){
        $from_meta = new_cmb2_box(array( 
            'id'            => 'single_post_from',
            'title'         => __( '文章来源', 'b2' ),
            'object_types'  => array( 'post','document','page'), // Post type
            'context'       => 'normal',
            'priority'      => 'high',
            'show_names'    => true,
        ));

        $from_meta->add_field(array(
            'name' => __('文章来源网址','b2'),
            'id'   => 'b2_post_from_url',
            'type' => 'text',
            'default'=>'',
        ));

        $from_meta->add_field(array(
            'name' => __('文章来源站点名称','b2'),
            'id'   => 'b2_post_from_name',
            'type' => 'text',
            'default'=>'',
        ));

        $down_meta = new_cmb2_box(array( 
            'id'            => 'single_post_download',
            'title'         => __( '下载设置', 'b2' ),
            'object_types'  => array( 'post','document','shop','page'), // Post type
            'context'       => 'normal',
            'priority'      => 'high',
            'show_names'    => true,
        ));

        $down_meta->add_field(array(
            'name' => __('是否开启下载功能？','b2'),
            'id'   => 'b2_open_download',
            'type' => 'select',
            'options'=>array(
                1=>__('开启','b2'),
                0=>__('关闭','b2')
            ),
            'default'=>0,
        ));

        $down_meta->add_field(array(
            'name' => __('是否允许游客购买','b2'),
            'id'   => 'b2_down_guest_buy',
            'type' => 'select',
            'options'=>array(
                1=>__('允许','b2'),
                0=>__('禁止','b2')
            ),
            'desc'=>__('如果是支付费用下载，您可以开启游客支付功能。只支持支付费用下载。','b2'),
            'default'=>0,
        ));

        $opt = b2_get_option('template_downloads','downloads_group');
        if(!empty($opt)){
            $options = [
                0=>__('不选择','b2')
            ];
            foreach ($opt as $k => $v) {
                $options[$k+1] = $v['title'];
            }
        }else{
            $options = [
                0=>__('不选择','b2')
            ];
        }

        $download_group = $down_meta->add_field( array(
            'before_row'=>'<div id="download-data" data-download=\''.json_encode($opt).'\'></div>',
            'id'          => 'b2_single_post_download_group',
            'type'        => 'group',
            'options'     => array(
                'group_title'       => __( '资源{#}', 'b2' ),
                'add_button'        => __( '添加一个资源项', 'b2' ),
                'remove_button'     => __( '删除资源', 'b2' ),
                'sortable'          => true,
                'closed'         => true,
                'remove_confirm' => __( '确定要删除这个资源吗？', 'b2' ),
            )
        ));

        
        $down_meta->add_group_field( $download_group, array(
            'name' => __('选择模版','b2'),
            'id'   => 'template',
            'type' => 'select',
            'options'=>$options,
            'desc'=> sprintf(__('您可以前往%s页面创建下载模版，在此处可以选择设置好的模版，然后直接编辑，方便快速设置','b2'),'<a href="'.admin_url('/admin.php?page=b2_template_downloads').'" target="_blank">'.__('下载模版设置','b2').'</a>'),
        ) );

        $down_meta->add_group_field( $download_group, array(
            'name' => __('资源链接','b2'),
            'id'   => 'url',
            'type' => 'textarea_code',
            'desc'=> sprintf(__('格式为%s，每组占一行。%s比如：%s%s提取码标识为：tq，解压码标识为：jy，如果中需要其中一个，只设置一个即可，都没有可以不用设置','b2'),'<code>资源名称|下载地址|提取码,解压码</code>','<br>','<br><code>百度网盘|https://baidu.com/xxxx.html|tq=123,jy=345</code>','<br>'),
            'options' => array( 'disable_codemirror' => true )
        ) );

        $down_meta->add_group_field( $download_group, array(
            'name' => __('资源缩略图','b2'),
            'id'   => 'thumb',
            'type' => 'file',
            'options' => array(
                'url' => true, // Hide the text input for the url
            ),
            'query_args' => array(
                'type' => array(
                    'image/gif',
                    'image/jpeg',
                    'image/png',
                ),
            ),
            'desc'=> __('可以不设置，将自动显示封面图片','b2')
        ) );

        $down_meta->add_group_field( $download_group, array(
            'name' => __('资源名称','b2'),
            'id'   => 'name',
            'type' => 'text',
            'desc'=> __('如果不设置，将获取文章标题当作资源名称','b2'),
        ) );

        $down_meta->add_group_field( $download_group, array(
            'name' => __('演示地址','b2'),
            'id'   => 'view',
            'type' => 'text',
            'desc'=> __('请填写网址形式，如果不设置，前台将不显示演示地址','b2'),
        ) );

        $down_meta->add_group_field( $download_group, array(
            'name' => __('资源属性','b2'),
            'id'   => 'attr',
            'type' => 'textarea',
            'desc'=> sprintf(__('格式为%s，每组占一行。%s比如：%s','b2'),'<code>属性名|属性值</code>','<br>','<br><code>大小|50kb</code><br><code>格式|zip</code>'),
        ) );

        $down_meta->add_group_field( $download_group, array(
            'name' => __('下载权限','b2'),
            'id'   => 'rights',
            'type' => 'textarea',
            'desc'=> sprintf(__('格式为%s，比如%s权限参数：%s评论可见：comment%s登录可见：login%s无限制：free%s付费下载：money=10%s积分下载：credit=30%s特殊权限：%s所有人免费：all|free（或者credit=10这种格式）%s普通用户组免费：lv|free（或者credit=10这种格式）%sVIP用户免费：vip|free（或者credit=10这种格式）','b2'),'<code>等级|权限</code>','<br><code>vip1|free</code><br><code>vip2|money=1</code><br><code>lv2|comment</code><br><code>lv3|login</code><br><code>lv4|money=10</code><br><code>lv4|credit=30</code><br><code>guest|money=30</code>(游客付费价格，游客无法支付积分，如果上面关闭了游客购买功能，此种设置不会生效)<br>','<br>','<br>','<br>','<br>','<br>','<br>','<br>','<br>','<br>'),
            ) );


        // $down_meta->add_group_field( $download_group, array(
        //     'name' => __('下载时效','b2'),
        //     'id'   => 'times',
        //     'type' => 'text',
        //     'desc'=> sprintf(__('用户购买以后，多少小时后过期（过期后不能再次下载资源，需要再次购买），请直接填写小时数字，支持小数。比如%s，%s。如果此处留空不设置，用户购买以后，永久生效。此项设置只对积分下载或者付费下载生效。','b2'),'<code class="red">2</code>','<code class="red">0.5</code>'),
        // ) );
    }

    public function document_order(){
        $document_name = b2_get_option('normal_custom','custom_document_name');

        $document = new_cmb2_box(array( 
            'id'            => 'single_document_order',
            'title'         => __( '文章在该组别的排序', 'b2' ),
            'object_types'  => array( 'document'), // Post type
            'context'       => 'side',
            'priority'      => 'high',
            'show_names'    => false,
        ));

        $document->add_field(array(
            'name' => __('文章在该组别的排序','b2'),
            'id'   => 'b2_document_order',
            'type' => 'text',
            'desc' => sprintf(__('排序必填，否则%s分类中不显示，数字越小，排名越靠前','b2'), $document_name),
        ));
    }
}