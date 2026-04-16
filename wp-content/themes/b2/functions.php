<?php
/**
* 定义常量
*/

// if(!current_user_can('administrator')){
// 	wp_die('升级中，过一会再来吧！购买请联系QQ：110613846。演示站：https://b2.7b2.com');
// }

define('B2_DS',DIRECTORY_SEPARATOR);
define('B2_BLOG_NAME',get_bloginfo('name'));
define('B2_BLOG_DESC',get_bloginfo('description'));
define('B2_HOME_URI',home_url());
define('B2_THEME_DIR', get_template_directory() );
define('B2_INCLUDES_PATH', get_template_directory() . '/includes/' );
define('B2_THEME_URI', get_template_directory_uri() );
define('B2_VERSION', '4.4.1' );
define('B2_DEFAULT_IMG',B2_THEME_URI.'/Assets/fontend/images/default-img.jpg');
define('B2_DEFAULT_AVATAR',B2_THEME_URI.'/Assets/fontend/images/default-avatar.png');
define('B2_GAP',16);
define('B2_SIDEBAR_GAP',16);
define('B2_IMG_RATIO',1.2);
define('B2_VERIFY_CODE','normal');
define('B2_ASIDE_SEARCH',1);
define('B2_EMPTY','<div class="empty-page"><img src="'.B2_THEME_URI.'/Assets/fontend/images/b2-page-empty.svg"><p>'.__('暂无相关结果','b2').'</p></div>');
define('B2_VERIFY_ICON','<i class="b2-vrenzhengguanli b2font b2-color"></i>');
define('B2_LOADING_IMG',B2_THEME_URI.'/Assets/fontend/images/default-img.jpg');
define('EXCERPT_LENGTH',260);
define('B2_CUSTOM_INDEX',false);
define('B2_OPEN_CIRCLE_IDS',[]);
define('B2_AUTH',isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : false);

define('B2_OPEN_WIDGET_CACHE',true);

define('B2_DEFAULT_OPTS',get_option('b2_default_opts'));

function b2_get_option($where,$key,$circle_id = 0){

	if($circle_id){

		$setting = apply_filters('b2_get_circle_setting_by_id', array('circle_id'=>$circle_id,'key'=>$key));
		
		if($setting !== false){
			return $setting;
		}
	}

	$settings = get_option('b2_'.$where);

	if(isset($settings[$key])){
		return $settings[$key];
	}else{

		$where = substr($where,0,strpos($where, '_'));

		if(!isset(B2_DEFAULT_OPTS[$where][$key])) return '';

		return B2_DEFAULT_OPTS[$where][$key];

	}

	return '';
}

/**
 * 设置是否开启debug模式的常量
 */
define('B2_OPEN_CACHE', b2_get_option('normal_main','open_cache') && wp_using_ext_object_cache());

//货币符号
define('B2_MONEY_SYMBOL',b2_get_option('normal_main','money_symbol'));
define('B2_MONEY_NAME',b2_get_option('normal_main','money_name'));

// wp_cache_flush();
/**
 * 初始化类
 */
require B2_THEME_DIR .B2_DS. 'loader.php';


function b2_custom_excerpt_length( $length ) {
    return EXCERPT_LENGTH;
}
add_filter( 'excerpt_length', 'b2_custom_excerpt_length', 999 );


/**
* 主题启用后进行的操作
 */
if ( ! function_exists( 'b2_setup' ) ) :

    function b2_setup() {
		$circle_name = b2_get_option('normal_custom','custom_circle_name');
		$shop_name = b2_get_option('normal_custom','custom_shop_name');

		$newsflashes_name = b2_get_option('normal_custom','custom_newsflashes_name');

		$links_name = b2_get_option('normal_custom','custom_links_name');

		$type = b2_get_option('template_top','top_type');

		if($type === 'social-top'){
			$arg = array(
				'ym-menu'=>__('顶部页眉菜单（不支持二级菜单）','b2')
			);
		}else{
			$arg = array(
				'ym-menu'=>__('页眉左侧菜单（不支持二级菜单）','b2')
			);
		}

		$arg['post'] = sprintf(__( '首页、文章、页面、分类、%s等页面菜单', 'b2' ),b2_get_option('normal_custom','custom_collection_name'));

		if(b2_get_option('newsflashes_main','newsflashes_open')){
			$arg['newsflashes'] = sprintf(__( '%s页面菜单', 'b2' ),$newsflashes_name);
		}

		if(b2_get_option('document_main','document_open')){
			$arg['document'] = __( '文档页面菜单', 'b2' );
		}

		if(b2_get_option('shop_main','shop_open')){
			$arg['shop'] = sprintf(__( '%s页面菜单', 'b2' ),$shop_name);
		}

		if(b2_get_option('circle_main','circle_open')){
			$arg['circle'] = sprintf(__( '%s页面菜单', 'b2' ),$circle_name);
		}

		if(b2_get_option('links_main','link_open')){
			$arg['links'] = sprintf(__( '%s页面菜单', 'b2' ),$links_name);
		}

		if(b2_get_option('ask_main','ask_open')){
			$ask_name = b2_get_option('normal_custom','custom_ask_name');
			$arg['ask'] = sprintf(__( '%s页面菜单', 'b2' ),$ask_name);
		}

		if(b2_get_option('infomation_main','infomation_open')){
			$arg['infomation'] = sprintf(__( '%s页面菜单', 'b2' ),b2_get_option('normal_custom','custom_infomation_name'));
		}

		$arg['top'] = __( '其他页面菜单（不能自定义菜单的地方会显示此菜单）', 'b2' );

        //注册菜单
        register_nav_menus($arg);

		load_theme_textdomain( 'b2', B2_THEME_DIR . '/languages' );

		add_theme_support( 'title-tag',$arg);

		//支持缩略图
		add_theme_support( 'post-thumbnails' );

    }

endif;
add_action( 'after_setup_theme', 'b2_setup' );//加载语言包

//开启友情连接
add_filter('pre_option_link_manager_enabled','__return_true');

add_filter( 'big_image_size_threshold', '__return_false' );

/**
* 注册侧边栏
 */
function b2_widgets_init() {

	$circle_name = b2_get_option('normal_custom','custom_circle_name');

	register_sidebar( array(
		'name'          => __( '侧边栏', 'b2' ),
		'id'            => 'sidebar-1',
		'description'   => __( '请选择你的小工具，拖到此处。（显示在未自定义侧边栏的页面）', 'b2' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s mg-b box b2-radius">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => __( '文章内页小工具', 'b2' ),
		'id'            => 'sidebar-3',
		'description'   => __( '请选择你的小工具，拖到此处。（显示在文章内页）', 'b2' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s mg-b box b2-radius">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => __( '底部小工具', 'b2' ),
		'id'            => 'sidebar-2',
		'description'   => __( '请选择你的小工具，拖到此处。', 'b2' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s mg-b b2-radius">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	));

	register_sidebar( array(
		'name'          => __( '页面小工具', 'b2' ),
		'id'            => 'sidebar-4',
		'description'   => __( '请选择你的小工具，拖到此处。', 'b2' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s mg-b box b2-radius">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	));

	if(b2_get_option('shop_main','shop_open')){
		register_sidebar( array(
			'name'          => __( '商城首页', 'b2' ),
			'id'            => 'sidebar-5',
			'description'   => __( '请选择你的小工具，拖到此处。', 'b2' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s mg-b box b2-radius">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		));

		register_sidebar( array(
			'name'          => __( '商城内页', 'b2' ),
			'id'            => 'sidebar-6',
			'description'   => __( '请选择你的小工具，拖到此处。', 'b2' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s mg-b box b2-radius">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		));

		register_sidebar( array(
			'name'          => __( '商城分类页', 'b2' ),
			'id'            => 'sidebar-7',
			'description'   => __( '请选择你的小工具，拖到此处。', 'b2' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s mg-b box b2-radius">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		));
	}

	//快讯小工具
	if(b2_get_option('newsflashes_main','newsflashes_open')){
		$newsflashes_name = b2_get_option('normal_custom','custom_newsflashes_name');
		register_sidebar( array(
			'name'          => sprintf(__( '%s首页小工具', 'b2' ),$newsflashes_name),
			'id'            => 'sidebar-11',
			'description'   => __( '请选择你的小工具，拖到此处。', 'b2' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s mg-b box b2-radius">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		));

		register_sidebar( array(
			'name'          => sprintf(__( '%s内页小工具', 'b2' ),$newsflashes_name),
			'id'            => 'sidebar-8',
			'description'   => __( '请选择你的小工具，拖到此处。', 'b2' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s mg-b box b2-radius">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		));
	}

	if(b2_get_option('circle_main','circle_open')){
		register_sidebar( array(
			'name'          => sprintf(__( '%s左侧小工具', 'b2' ),$circle_name),
			'id'            => 'sidebar-9',
			'description'   => __( '请选择你的小工具，拖到此处。', 'b2' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s mg-b box b2-radius">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		));
		register_sidebar( array(
			'name'          => sprintf(__( '%s右侧小工具', 'b2' ),$circle_name),
			'id'            => 'sidebar-10',
			'description'   => __( '请选择你的小工具，拖到此处。', 'b2' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s mg-b box b2-radius">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		));
		register_sidebar( array(
			'name'          => sprintf(__( '%s内页小工具', 'b2' ),$circle_name),
			'id'            => 'sidebar-12',
			'description'   => __( '请选择你的小工具，拖到此处。', 'b2' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s mg-b box b2-radius">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		));
	}

	register_sidebar( array(
		'name'          => __( '信息流模式小工具', 'b2' ),
		'id'            => 'sidebar-13',
		'description'   => __( '请选择你的小工具，拖到此处。', 'b2' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s mg-b box b2-radius">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	));

	if(b2_get_option('links_main','link_open')){

		register_sidebar( array(
			'name'          => __( '网址导航内页小工具', 'b2' ),
			'id'            => 'sidebar-14',
			'description'   => __( '请选择你的小工具，拖到此处。', 'b2' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s mg-b box b2-radius">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		));
	}

	register_sidebar( array(
		'name'          => __( '自定义支付内页小工具', 'b2' ),
		'id'            => 'sidebar-17',
		'description'   => __( '请选择你的小工具，拖到此处。', 'b2' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s mg-b box b2-radius">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	));

	if(b2_get_option('infomation_main','infomation_open')){

		$infomation_name = b2_get_option('normal_custom','custom_infomation_name');

		register_sidebar( array(
			'name'          => sprintf(__( '%s内页小工具', 'b2' ),$infomation_name),
			'id'            => 'sidebar-15',
			'description'   => __( '请选择你的小工具，拖到此处。', 'b2' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s mg-b box b2-radius">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		));

		register_sidebar( array(
			'name'          => sprintf(__( '%s首页小工具', 'b2' ),$infomation_name),
			'id'            => 'sidebar-16',
			'description'   => __( '请选择你的小工具，拖到此处。', 'b2' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s mg-b box b2-radius">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		));
	}

	if(b2_get_option('ask_main','ask_open')){

		$ask_name = b2_get_option('normal_custom','custom_ask_name');
		register_sidebar( array(
			'name'          => sprintf(__( '%s存档小工具', 'b2' ),$ask_name),
			'id'            => 'sidebar-18',
			'description'   => __( '请选择你的小工具，拖到此处。', 'b2' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s mg-b box b2-radius">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		));

		register_sidebar( array(
			'name'          => sprintf(__( '%s内页小工具', 'b2' ),$ask_name),
			'id'            => 'sidebar-19',
			'description'   => __( '请选择你的小工具，拖到此处。', 'b2' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s mg-b box b2-radius">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		));
	}

	$index = get_option('b2_index_widget');
	if(!empty($index)){
		foreach ($index as $k => $v) {
			register_sidebar( array(
				'name'          => sprintf(__( '首页模块（%s）', 'b2' ),isset($v['name']) ? $v['name'] : '#'.($k+1)),
				'id'            => isset($v['key']) ? $v['key'] : $v,
				'description'   => __( '请选择你的小工具，拖到此处。', 'b2' ),
				'before_widget' => '<section id="%1$s" class="widget %2$s box b2-radius">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			));
		}
	}
}
add_action( 'widgets_init', 'b2_widgets_init' );

/**
 * 禁用工具条
 */
show_admin_bar( false );

/**
 * 禁用emoji
 *
 * @return void
 * @author Li Ruchun <lemolee@163.com>
 * @version 1.0.0
 * @since 2018
 */
function b2_theme_init() {

	//添加公告文章形式
	$g_name = b2_get_option('normal_custom','custom_announcement_name');
	$g_slug = b2_get_option('normal_custom','custom_announcement_link');

	$announcement = array(
		'name' => $g_name,
		'singular_name' => $g_name,
		'add_new' => sprintf(__('添加一个%s','b2'),$g_name),
		'add_new_item' => sprintf(__('添加一个%s','b2'),$g_name),
		'edit_item' => sprintf(__('编辑%s','b2'),$g_name),
		'new_item' => sprintf(__('新的%s','b2'),$g_name),
		'all_items' => sprintf(__('所有%s','b2'),$g_name),
		'view_item' => sprintf(__('查看%s','b2'),$g_name),
		'search_items' => sprintf(__('搜索%s','b2'),$g_name),
		'not_found' =>  sprintf(__('没有%s','b2'),$g_name),
		'not_found_in_trash' =>__('回收站为空','b2'),
		'menu_name' => $g_name
	);
	register_post_type( 'announcement',
		array(
			'labels' => $announcement,
			'has_archive' => true,
			'public' => true,
			'rewrite' => array(
				'slug' => $g_slug,
				'with_front' => true
			),
			'menu_icon'=>'dashicons-controls-volumeon',
			'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt','comments' ),
			'capability_type' => 'page',
		)
	);

	//专题文章形式
	$name = b2_get_option('normal_custom','custom_collection_name');
	$slug = b2_get_option('normal_custom','custom_collection_link');

	$arr = array(
		'name'              => $name,
		'singular_name'     => $name,
		'search_items'      => sprintf(__( '搜索%s', 'b2' ),$name),
		'all_items'         => sprintf(__( '所有%s', 'b2' ),$name),
		'parent_item'       => sprintf(__( '父级%s', 'b2' ),$name),
		'parent_item_colon' => sprintf(__( '父级%s', 'b2' ),$name),
		'edit_item'         => sprintf(__( '编辑%s', 'b2' ),$name),
		'update_item'       => sprintf(__( '更新%s', 'b2' ),$name),
		'add_new_item'      => sprintf(__( '添加%s', 'b2' ),$name),
		'new_item_name'     => sprintf(__( '%s名称', 'b2' ),$name),
		'menu_name'         => $name,
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $arr,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'show_in_rest' => true,
		'rewrite'           => array( 'slug' => $slug,'with_front' => false ),
	);

	register_taxonomy( 'collection', array( 'post' ), $args );

	//圈子文章形式
	$link = b2_get_option('normal_custom','custom_circle_link');
	$name = b2_get_option('normal_custom','custom_circle_name');

	$labels = array(
		'name'              => $name,
		'singular_name'     => $name,
		'search_items'      => sprintf(__( '搜索%s', 'b2' ),$name),
		'all_items'         => sprintf(__( '所有%s', 'b2' ),$name),
		'parent_item'       => sprintf(__( '父级%s', 'b2' ),$name),
		'parent_item_colon' => sprintf(__( '父级%s：', 'b2'),$name),
		'edit_item'         => sprintf(__( '编辑%s', 'b2' ),$name),
		'update_item'       => sprintf(__( '更新%s', 'b2' ),$name),
		'add_new_item'      => sprintf(__( '添加%s', 'b2' ),$name),
		'new_item_name'     => sprintf(__( '%s名称', 'b2' ),$name),
		'menu_name'         => $name,
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'public'=>true,
		'update_count_callback'=>'_update_post_term_count',
		'show_admin_column' => true,
		'query_var'         => true,
		'show_in_rest' => true,
		'rewrite'           => array( 'slug' => $link,'with_front' => false ),
	);

	register_taxonomy( 'circle_tags', array( 'circle_tags' ), $args );

	$labels = array(
		'name' => sprintf(__('%s话题','b2'),$name),
		'singular_name' =>sprintf(__('%s话题','b2'),$name),
		'add_new' => __('添加一个话题','b2'),
		'add_new_item' => __('添加一个话题','b2'),
		'edit_item' => __('编辑话题','b2'),
		'new_item' => __('新的话题','b2'),
		'all_items' => __('所有话题','b2'),
		'view_item' => __('查看话题','b2'),
		'search_items' => __('搜索话题','b2'),
		'not_found' =>  __('没有话题','b2'),
		'not_found_in_trash' =>__('回收站为空','b2'),
		'menu_name' => sprintf(__('%s话题','b2'),$name),
	);

	register_post_type( 'circle', array(
		'labels' => $labels,
		'has_archive' => true,
		'public'=>true,
		'menu_position'=>30,
		'menu_icon'=>'dashicons-universal-access-alt',
		'taxonomies' => array('circle_tags'),
		'exclude_from_search' => false,
		'capability_type' => 'page',
		'capabilities' => array(
			'create_posts' => false,
		),
		'supports' => array(
			'title',
			'comments',
			'editor'
		),
		'map_meta_cap' => true,
		'yarpp_support' => true,
		'rewrite' => array( 'slug' => $link ,'with_front' => false),
		)
	);

	$document_name = b2_get_option('normal_custom','custom_document_name');
	$document_slug = b2_get_option('normal_custom','custom_document_link');

	$arr = array(
		'name'              => sprintf(__( '%s分类', 'b2' ),$document_name),
		'singular_name'     => sprintf(__( '%s分类', 'b2' ),$document_name),
		'search_items'      => sprintf(__( '搜索%s分类', 'b2' ),$document_name),
		'all_items'         => sprintf(__( '所有%s分类', 'b2' ),$document_name),
		'parent_item'       => sprintf(__( '父级%s分类', 'b2' ),$document_name),
		'parent_item_colon' => sprintf(__( '父级%s分类', 'b2' ),$document_name),
		'edit_item'         => sprintf(__( '编辑%s分类', 'b2' ),$document_name),
		'update_item'       => sprintf(__( '更新%s分类', 'b2' ),$document_name),
		'add_new_item'      => sprintf(__( '添加%s分类', 'b2' ),$document_name),
		'new_item_name'     => sprintf(__( '%s分类名称', 'b2' ),$document_name),
		'menu_name'         => sprintf(__( '%s分类', 'b2' ),$document_name),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $arr,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'public'=>true,
		'show_in_rest' => true,
		'update_count_callback'=>'_update_post_term_count',
		'rewrite'           => array( 'slug' => $document_slug, 'with_front' => false ),
	);

	register_taxonomy( 'document_cat', array( 'document_cat' ), $args );

	$document = array(
		'name' => sprintf(__('%s中心','b2'),$document_name),
		'singular_name' => $document_name,
		'add_new' => sprintf(__('添加一个%s','b2'),$document_name),
		'add_new_item' => sprintf(__('添加一个%s','b2'),$document_name),
		'edit_item' => sprintf(__('编辑%s','b2'),$document_name),
		'new_item' => sprintf(__('新的%s','b2'),$document_name),
		'all_items' => sprintf(__('所有%s','b2'),$document_name),
		'view_item' => sprintf(__('查看%s','b2'),$document_name),
		'search_items' => sprintf(__('搜索%s','b2'),$document_name),
		'not_found' =>  sprintf(__('没有%s','b2'),$document_name),
		'not_found_in_trash' =>__('回收站为空','b2'),
		'menu_name' => $document_name,
	);

	register_post_type( 'document',
		array(
			'labels' => $document,
			'has_archive' => true,
			'public' => true,
			'show_in_rest' => true,
			'menu_icon'=>'dashicons-editor-paste-word',
			'rewrite' => array('slug' => $document_slug, 'with_front' => false),
			'taxonomies' => array('document_cat','post_tag'),
			'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt','comments' ),
			'capability_type' => 'page',
			'yarpp_support' => true
		)
	);

	$newsflashes_slug = b2_get_option('normal_custom','custom_newsflashes_link');
	$newsflashes_name = b2_get_option('normal_custom','custom_newsflashes_name');
	$arr = array(
		'name'              => sprintf(__( '%s标签', 'b2' ),$newsflashes_name),
		'singular_name'     => sprintf(__( '%s标签', 'b2' ),$newsflashes_name),
		'search_items'      => sprintf(__( '搜索%s标签', 'b2' ),$newsflashes_name),
		'all_items'         => sprintf(__( '所有%s标签', 'b2' ),$newsflashes_name),
		'parent_item'       => sprintf(__( '父级%s标签', 'b2' ),$newsflashes_name),
		'parent_item_colon' => sprintf(__( '父级%s标签', 'b2' ),$newsflashes_name),
		'edit_item'         => sprintf(__( '编辑%s标签', 'b2' ),$newsflashes_name),
		'update_item'       => sprintf(__( '更新%s标签', 'b2' ),$newsflashes_name),
		'add_new_item'      => sprintf(__( '添加%s标签', 'b2' ),$newsflashes_name),
		'new_item_name'     => sprintf(__( '%s标签名称', 'b2' ),$newsflashes_name),
		'menu_name'         => sprintf(__( '%s标签', 'b2' ),$newsflashes_name),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $arr,
		'public'=>true,
		'update_count_callback'=>'_update_post_term_count',
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'show_in_rest' => true,
		'rewrite'           => array( 'slug' => $newsflashes_slug, 'with_front' => false ),
	);

	register_taxonomy( 'newsflashes_tags', array( 'newsflashes_tags' ), $args );

	$newsflashes = array(
		'name' => $newsflashes_name,
		'singular_name' => $newsflashes_name,
		'add_new' => sprintf(__('添加一个%s','b2'),$newsflashes_name),
		'add_new_item' => sprintf(__('添加一个%s','b2'),$newsflashes_name),
		'edit_item' => sprintf(__('编辑%s','b2'),$newsflashes_name),
		'new_item' => sprintf(__('新的%s','b2'),$newsflashes_name),
		'all_items' => sprintf(__('所有%s','b2'),$newsflashes_name),
		'view_item' => sprintf(__('查看%s','b2'),$newsflashes_name),
		'search_items' => sprintf(__('搜索%s','b2'),$newsflashes_name),
		'not_found' =>  sprintf(__('没有%s','b2'),$newsflashes_name),
		'not_found_in_trash' =>__('回收站为空','b2'),
		'menu_name' => $newsflashes_name,
	);

	register_post_type( 'newsflashes',
		array(
			'labels' => $newsflashes,
			'has_archive' => true,
			'public'=>true,
			'menu_position'=>28,
			'menu_icon'=>'dashicons-media-spreadsheet',
			'taxonomies' => array('newsflashes_tags'),
			'exclude_from_search' => false,
			'capability_type' => 'page',
			// 'capabilities' => array(
			// 	'create_posts' => false,
			// ),
			'supports' => array(
				'title',
				'comments',
				'editor',
				'thumbnail'
			),
			'map_meta_cap' => true,
			'yarpp_support' => true,
			'rewrite' => array( 'slug' => $newsflashes_slug ,'with_front' => false),
		)
	);

	$shop_slug = b2_get_option('normal_custom','custom_shop_link');
	$shop_name = b2_get_option('normal_custom','custom_shop_name');
	$_shop_name = __('商品','b2');

	$labels = array(
		'name'              => sprintf(__( '%s分类', 'b2' ),$_shop_name),
		'singular_name'     => sprintf(__( '%s分类', 'b2' ),$_shop_name),
		'search_items'      => sprintf(__( '搜索%s分类', 'b2' ),$_shop_name),
		'all_items'         => sprintf(__( '所有%s分类', 'b2' ),$_shop_name),
		'parent_item'       => sprintf(__( '父级%s分类', 'b2' ),$_shop_name),
		'parent_item_colon' => sprintf(__( '父级%s分类：', 'b2' ),$_shop_name),
		'edit_item'         => sprintf(__( '编辑%s分类', 'b2' ),$_shop_name),
		'update_item'       => sprintf(__( '更新%s分类', 'b2' ),$_shop_name),
		'add_new_item'      => sprintf(__( '添加%s分类', 'b2' ),$_shop_name),
		'new_item_name'     => sprintf(__( '%s分类名称', 'b2' ),$_shop_name),
		'menu_name'         => sprintf(__( '%s分类', 'b2' ),$_shop_name)
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'public'=>true,
		'update_count_callback'=>'_update_post_term_count',
		'show_admin_column' => true,
		'query_var'         => true,
		'show_in_rest' => true,
		'rewrite'           => array( 'slug' => $shop_slug, 'with_front' => false ),
	);

	register_taxonomy( 'shoptype', array( 'shoptype' ), $args );

	$labels = array(
		'name' => $shop_name,
		'singular_name' => $shop_name,
		'add_new' => sprintf(__('添加一个%s','b2'),$_shop_name),
		'add_new_item' => sprintf(__('添加一个%s','b2'),$_shop_name),
		'edit_item' => sprintf(__('编辑%s','b2'),$_shop_name),
		'new_item' => sprintf(__('新的%s','b2'),$_shop_name),
		'all_items' => sprintf(__('所有%s','b2'),$_shop_name),
		'view_item' => sprintf(__('查看%s','b2'),$_shop_name),
		'search_items' => sprintf(__('搜索%s','b2'),$_shop_name),
		'not_found' =>  sprintf(__('没有%s','b2'),$_shop_name),
		'not_found_in_trash' =>sprintf(__('回收站为空','b2'),$_shop_name),
		'parent_item_colon' => sprintf(__( '父级%s', 'b2' ),$_shop_name),
		'menu_name' => $shop_name,
	);
	register_post_type( 'shop', array(
		'labels' => $labels,
		'has_archive' => true,
		'public' => true,
		'menu_icon'=>'dashicons-products',
		'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail','comments'),
		'taxonomies' => array('shoptype','post_tag'),
		'exclude_from_search' => false,
		'capability_type' => 'page',
		'show_in_rest'       => true,
		'rewrite' => array( 'slug' => $shop_slug, 'with_front' => false ),
		'yarpp_support' => true
		)
	);

	$link_name = b2_get_option('normal_custom','custom_links_name');
	$link_slug = b2_get_option('normal_custom','custom_links_link');

	$arr = array(
		'name'              => sprintf(__( '%s分类', 'b2' ),$link_name),
		'singular_name'     => sprintf(__( '%s分类', 'b2' ),$link_name),
		'search_items'      => sprintf(__( '搜索%s分类', 'b2' ),$link_name),
		'all_items'         => sprintf(__( '所有%s分类', 'b2' ),$link_name),
		'parent_item'       => sprintf(__( '父级%s分类', 'b2' ),$link_name),
		'parent_item_colon' => sprintf(__( '父级%s分类', 'b2' ),$link_name),
		'edit_item'         => sprintf(__( '编辑%s分类', 'b2' ),$link_name),
		'update_item'       => sprintf(__( '更新%s分类', 'b2' ),$link_name),
		'add_new_item'      => sprintf(__( '添加%s分类', 'b2' ),$link_name),
		'new_item_name'     => sprintf(__( '%s分类名称', 'b2' ),$link_name),
		'menu_name'         => sprintf(__( '%s分类', 'b2' ),$link_name),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $arr,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'public'=>true,
		'show_in_rest' => true,
		'update_count_callback'=>'_update_post_term_count',
		'rewrite'           => array( 'slug' => $link_slug, 'with_front' => false ),
	);

	register_taxonomy( 'link_cat', array( 'link_cat' ), $args );

	$links = array(
		'name' => sprintf(__('%s中心','b2'),$link_name),
		'singular_name' => $link_name,
		'add_new' => sprintf(__('添加一个%s','b2'),$link_name),
		'add_new_item' => sprintf(__('添加一个%s','b2'),$link_name),
		'edit_item' => sprintf(__('编辑%s','b2'),$link_name),
		'new_item' => sprintf(__('新的%s','b2'),$link_name),
		'all_items' => sprintf(__('所有%s','b2'),$link_name),
		'view_item' => sprintf(__('查看%s','b2'),$link_name),
		'search_items' => sprintf(__('搜索%s','b2'),$link_name),
		'not_found' =>  sprintf(__('没有%s','b2'),$link_name),
		'not_found_in_trash' =>__('回收站为空','b2'),
		'menu_name' => $link_name,
	);

	register_post_type( 'links',
		array(
			'labels' => $links,
			'has_archive' => true,
			'public' => true,
			'show_in_rest' => true,
			'menu_icon'=>'dashicons-buddicons-replies',
			'rewrite' => array('slug' => $link_slug, 'with_front' => false),
			'taxonomies' => array('link_cat'),
			'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt','comments' ),
			'capability_type' => 'page',
			'yarpp_support' => true
		)
	);

	$info_name = b2_get_option('normal_custom','custom_infomation_name');
	$info_slug = b2_get_option('normal_custom','custom_infomation_link');

	$arr = array(
		'name'              => sprintf(__( '%s分类', 'b2' ),$info_name),
		'singular_name'     => sprintf(__( '%s分类', 'b2' ),$info_name),
		'search_items'      => sprintf(__( '搜索%s分类', 'b2' ),$info_name),
		'all_items'         => sprintf(__( '所有%s分类', 'b2' ),$info_name),
		'parent_item'       => sprintf(__( '父级%s分类', 'b2' ),$info_name),
		'parent_item_colon' => sprintf(__( '父级%s分类', 'b2' ),$info_name),
		'edit_item'         => sprintf(__( '编辑%s分类', 'b2' ),$info_name),
		'update_item'       => sprintf(__( '更新%s分类', 'b2' ),$info_name),
		'add_new_item'      => sprintf(__( '添加%s分类', 'b2' ),$info_name),
		'new_item_name'     => sprintf(__( '%s分类名称', 'b2' ),$info_name),
		'menu_name'         => sprintf(__( '%s分类', 'b2' ),$info_name),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $arr,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'public'=>true,
		'show_in_rest' => true,
		'update_count_callback'=>'_update_post_term_count',
		'rewrite'           => array( 'slug' => $info_slug, 'with_front' => false ),
	);

	register_taxonomy( 'infomation_cat', array( 'infomation_cat' ), $args );

	$info = array(
		'name' => $info_name,
		'singular_name' => $info_name,
		'add_new' => sprintf(__('添加一个%s','b2'),$info_name),
		'add_new_item' => sprintf(__('添加一个%s','b2'),$info_name),
		'edit_item' => sprintf(__('编辑%s','b2'),$info_name),
		'new_item' => sprintf(__('新的%s','b2'),$info_name),
		'all_items' => sprintf(__('所有%s','b2'),$info_name),
		'view_item' => sprintf(__('查看%s','b2'),$info_name),
		'search_items' => sprintf(__('搜索%s','b2'),$info_name),
		'not_found' =>  sprintf(__('没有%s','b2'),$info_name),
		'not_found_in_trash' =>__('回收站为空','b2'),
		'menu_name' => $info_name,
	);

	register_post_type( 'infomation',
		array(
			'labels' => $info,
			'has_archive' => true,
			'public' => true,
			'show_in_rest' => true,
			'menu_icon'=>'dashicons-carrot',
			'rewrite' => array('slug' => $info_slug, 'with_front' => false),
			'taxonomies' => array('infomation_cat'),
			'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt','comments' ),
			'capability_type' => 'page',
			'yarpp_support' => true
		)
	);

	$info_name = __('自定义支付','b2');

    $labels = [
        'name'              => sprintf(__( '%s分类', 'b2' ),$info_name),
        'singular_name'     => sprintf(__( '%s分类', 'b2' ),$info_name),
        'search_items'      => sprintf(__( '搜索%s分类', 'b2' ),$info_name),
        'all_items'         => sprintf(__( '所有%s分类', 'b2' ),$info_name),
        'parent_item'       => sprintf(__( '父级%s分类', 'b2' ),$info_name),
        'parent_item_colon' => sprintf(__( '父级%s分类', 'b2' ),$info_name),
        'edit_item'         => sprintf(__( '编辑%s分类', 'b2' ),$info_name),
        'update_item'       => sprintf(__( '更新%s分类', 'b2' ),$info_name),
        'add_new_item'      => sprintf(__( '添加%s分类', 'b2' ),$info_name),
        'new_item_name'     => sprintf(__( '%s分类名称', 'b2' ),$info_name),
        'menu_name'         => sprintf(__( '%s分类', 'b2' ),$info_name),
    ];
    register_taxonomy('cpay_cat', ['cpay'], [
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_in_rest' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'public' => false,
        'rewrite' => false,
    ]);

	$custom_pay = array(
		'name' => $info_name,
		'singular_name' => $info_name,
		'add_new' => sprintf(__('添加一个%s','b2'),$info_name),
		'add_new_item' => sprintf(__('添加一个%s','b2'),$info_name),
		'edit_item' => sprintf(__('编辑%s','b2'),$info_name),
		'new_item' => sprintf(__('新的%s','b2'),$info_name),
		'all_items' => sprintf(__('所有%s','b2'),$info_name),
		'view_item' => sprintf(__('查看%s','b2'),$info_name),
		'search_items' => sprintf(__('搜索%s','b2'),$info_name),
		'not_found' =>  sprintf(__('没有%s','b2'),$info_name),
		'not_found_in_trash' =>__('回收站为空','b2'),
		'menu_name' => $info_name,
	);

	register_post_type( 'cpay',
		array(
			'labels' => $custom_pay,
			'has_archive' => true,
			'public' => true,
			'show_in_rest' => true,
			'menu_icon'=>'dashicons-money-alt',
			'rewrite' => array('slug' => 'cpay', 'with_front' => false),
			'supports' => array( 'title'),
			'taxonomies'=> ['cpay_cat'],
			'capability_type' => 'page',
			'yarpp_support' => false
		)
	);

	$ask_name = b2_get_option('normal_custom','custom_ask_name');
	$ask_slug = b2_get_option('normal_custom','custom_ask_link');
	$cat_name = b2_get_option('normal_custom','custom_ask_cat_name');
	$answer_name = b2_get_option('normal_custom','custom_answer_name');

	$arr = array(
		'name'              => $ask_name.$cat_name,
		'singular_name'     => $ask_name.$cat_name,
		'search_items'      => sprintf(__( '搜索%s', 'b2' ),$ask_name.$cat_name),
		'all_items'         => sprintf(__( '所有%s', 'b2' ),$ask_name.$cat_name),
		'parent_item'       => sprintf(__( '父级%s', 'b2' ),$ask_name.$cat_name),
		'parent_item_colon' => sprintf(__( '父级%s', 'b2' ),$ask_name.$cat_name),
		'edit_item'         => sprintf(__( '编辑%s', 'b2' ),$ask_name.$cat_name),
		'update_item'       => sprintf(__( '更新%s', 'b2' ),$ask_name.$cat_name),
		'add_new_item'      => sprintf(__( '添加%s', 'b2' ),$ask_name.$cat_name),
		'new_item_name'     => sprintf(__( '%s名称', 'b2' ),$ask_name.$cat_name),
		'menu_name'         => $ask_name.$cat_name,
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $arr,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'public'=>true,
		'show_in_rest' => true,
		'update_count_callback'=>'_update_post_term_count',
		'rewrite'           => array( 'slug' => $ask_slug, 'with_front' => false ),
	);

	register_taxonomy( 'ask_cat', array( 'ask_cat' ), $args );

	$ask = array(
		'name' => $ask_name,
		'singular_name' => $ask_name,
		'add_new' => sprintf(__('添加一个%s','b2'),$ask_name),
		'add_new_item' => sprintf(__('添加一个%s','b2'),$ask_name),
		'edit_item' => sprintf(__('编辑%s','b2'),$ask_name),
		'new_item' => sprintf(__('新的%s','b2'),$ask_name),
		'all_items' => sprintf(__('所有%s','b2'),$ask_name),
		'view_item' => sprintf(__('查看%s','b2'),$ask_name),
		'search_items' => sprintf(__('搜索%s','b2'),$ask_name),
		'not_found' =>  sprintf(__('没有%s','b2'),$ask_name),
		'not_found_in_trash' =>__('回收站为空','b2'),
		'menu_name' => $ask_name,
	);

	register_post_type( 'ask',
		array(
			'labels' => $ask,
			'has_archive' => true,
			'public' => true,
			'show_in_rest' => true,
			'menu_icon'=>'dashicons-format-chat',
			'rewrite' => array('slug' => $ask_slug, 'with_front' => false),
			'taxonomies' => array('ask_cat'),
			'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt','comments' ),
			'capability_type' => 'page',
			'yarpp_support' => true
		)
	);

	$ask_name = $answer_name;

	$ask = array(
		'name' => $ask_name,
		'singular_name' => $ask_name,
		'add_new' => sprintf(__('添加一个%s','b2'),$ask_name),
		'add_new_item' => sprintf(__('添加一个%s','b2'),$ask_name),
		'edit_item' => sprintf(__('编辑%s','b2'),$ask_name),
		'new_item' => sprintf(__('新的%s','b2'),$ask_name),
		'all_items' => sprintf(__('所有%s','b2'),$ask_name),
		'view_item' => sprintf(__('查看%s','b2'),$ask_name),
		'search_items' => sprintf(__('搜索%s','b2'),$ask_name),
		'not_found' =>  sprintf(__('没有%s','b2'),$ask_name),
		'not_found_in_trash' =>__('回收站为空','b2'),
		'menu_name' => $ask_name,
	);

	register_post_type( 'answer',
		array(
			'labels' => $ask,
			'has_archive' => true,
			'public' => true,
			'show_in_rest' => true,
			'show_in_menu' => 'edit.php?post_type=ask',
			'rewrite' => array('slug' => 'answer', 'with_front' => false),
			'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt','comments' ),
			'capability_type' => 'page',
			'yarpp_support' => true
		)
	);

	/* @var WP $wp */
	global $wp;
	// Remove the embed query var.
	$wp->public_query_vars = array_diff( $wp->public_query_vars, array(
	'embed',
	) );
	// Remove the REST API endpoint.
	remove_action( 'rest_api_init', 'wp_oembed_register_route' );
	// Turn off
	add_filter( 'embed_oembed_discover', '__return_false' );
	// Don't filter oEmbed results.
	remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );
	// Remove oEmbed discovery links.
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	// Remove oEmbed-specific JavaScript from the front-end and back-end.
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
	add_filter( 'tiny_mce_plugins', 'disable_embeds_tiny_mce_plugin' );
	// Remove all embeds rewrite rules.
	add_filter( 'rewrite_rules_array', 'disable_embeds_rewrites' );
	remove_action( 'template_redirect', 'wp_old_slug_redirect' );

	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	add_filter( 'tiny_mce_plugins', 'b2_disable_emojis_tinymce' );
	add_filter( 'wp_resource_hints', 'b2_disable_emojis_remove_dns_prefetch', 10, 2 );

	remove_action('wp_head', 'wp_shortlink_wp_head', 10);

    remove_action( 'template_redirect', 'wp_shortlink_header', 11);

	remove_image_size('post-thumbnail'); // disable images added via set_post_thumbnail_size()
    remove_image_size('another-size');   // disable any other added image sizes

}
add_action( 'init', 'b2_theme_init' );

// disable srcset on frontend
add_filter('max_srcset_image_width', 'b2_disable_srcset');
function b2_disable_srcset(){
    return 1;
}

/**
* Filter function used to remove the tinymce emoji plugin.
*
* @param array $plugins
* @return array Difference betwen the two arrays
*/
function b2_disable_emojis_tinymce( $plugins ) {
	if ( is_array( $plugins ) ) {
		return array_diff( $plugins, array( 'wpemoji' ) );
	} else {
		return array();
	}
}

/**
 * 判断是不是微信
 *
 * @return boolean
 * @author Li Ruchun <lemolee@163.com>
 * @version 1.0.0
 * @since 2018
 */
function b2_is_weixin(){
    if ( isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
        return true;
    }
    return false;
}

/**
* Remove emoji CDN hostname from DNS prefetching hints.
*
* @param array $urls URLs to print for resource hints.
* @param string $relation_type The relation type the URLs are printed for.
* @return array Difference betwen the two arrays.
*/
function b2_disable_emojis_remove_dns_prefetch( $urls, $relation_type ) {
	if ( 'dns-prefetch' == $relation_type ) {
		$emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' );
		$urls = array_diff( $urls, array( $emoji_svg_url ) );
	}

	return $urls;
}

//菜单设置项
function b2_get_menu_option($menu_item_id, $key = '',$default = null){
	$opts = get_option('cmb2_nav_menus', null);

	if ($opts) {
		$key .= '-'.$menu_item_id;
		return (isset($opts[$key])) ? $opts[$key] : $default;
	}

	return $default;
}

/**
 * 获取缩略图
 *
 * @param array $arg 缩略图参数：thumb->图片地址,type->裁剪方式,width->裁剪宽度,height->裁剪高度,gif->是否显示动图
 *
 * @return string 裁剪后的图片地址
 * @author Li Ruchun <lemolee@163.com>
 * @version 1.0.0
 * @since 2018
 */
function b2_get_thumb($arg){
	return \B2\Modules\Common\FileUpload::thumb($arg);
}

/**
 * 获取随机默认缩略图
 *
 * @return string 随机缩略图地址
 * @author Li Ruchun <lemolee@163.com>
 * @version 1.0.0
 * @since 2018
 */
function b2_get_default_img(){
	$default_img = b2_get_option('normal_main','default_imgs');

	if(empty($default_img)){
		$default_img = array(
			B2_DEFAULT_IMG
		);
	}
	return $default_img[array_rand($default_img,1)];
}

add_filter( 'update_attached_file', 'b2_update_attached_file', 10, 2 );
function b2_update_attached_file($file, $attachment_id){
	$upload_dir = apply_filters('b2_upload_path_arg',wp_upload_dir());
	if(strpos($file,$upload_dir['baseurl']) !== false){
		$file = str_replace($upload_dir['baseurl'].'/','',$file);
	}

	return $file;
}

/**
 * 获取当前用户的IP地址
 *
 * @return void
 * @author Li Ruchun <lemolee@163.com>
 * @version 1.0.0
 * @since 2018
 */
function b2_get_user_ip() {
	if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))  
		$ip = getenv("HTTP_CLIENT_IP");  
	else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"),  
	"unknown"))  
		$ip = getenv("HTTP_X_FORWARDED_FOR");  
	else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))  
		$ip = getenv("REMOTE_ADDR");  
	else if (isset ($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR']  
	&& strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))  
		$ip = $_SERVER['REMOTE_ADDR'];  
	else  
		$ip = "unknown";  
	return ($ip);  
}

/**
 * 返回字符串中的第一张图片
 *
 * @param string $content
 * @param int $i
 *
 * @return void
 * @author Li Ruchun <lemolee@163.com>
 * @version 1.0.0
 * @since 2018
 */
function b2_get_first_img($content,$i = 0) {
	preg_match_all('~<img[^>]*src\s?=\s?([\'"])((?:(?!\1).)*)[^>]*>~i', $content, $match,PREG_PATTERN_ORDER);

	if(is_numeric($i)){
		return isset($match[2][$i]) ? esc_url($match[2][$i]) : false;
	}elseif($i == 'all'){
		return $match[2];
	}else{
		return isset($match[2][0]) ? esc_url($match[2][0]) : false;
	}
}

function b2_timeago($ptime,$return = false){
	return B2\Modules\Common\Post::time_ago($ptime,$return);
}

/**
 * 数字缩写
 *
 * @param string $num
 * @param int $num
 *
 * @return void
 * @author Li Ruchun <lemolee@163.com>
 * @version 1.0.0
 * @since 2018
 */
function b2_number_format($num) {
	$num = $num === '' ? 0 : $num;
	if($num>1000) {
		$x = round($num);
		$x_number_format = number_format($x);
		$x_array = explode(',', $x_number_format);
		$x_parts = array('k', 'm', 'b', 't');
		$x_count_parts = count($x_array) - 1;
		$x_display = $x;
		$x_display = $x_array[0] . ((int) $x_array[1][0] !== 0 ? '.' . $x_array[1][0] : '');
		$x_display .= $x_parts[$x_count_parts - 1];

		return $x_display;
	}

	return $num;
}

//禁用响应式图片属性
//add_filter( 'wp_calculate_image_srcset', '__return_false' );
add_filter( 'use_default_gallery_style', '__return_false' );
/**
 * 获取描述
 *
 * @param int $post_id 文章ID
 * @param int $size 截取长度
 * @param string $content 需要截取的内容
 *
 * @return string 截取以后的字符串
 * @author Li Ruchun <lemolee@163.com>
 * @version 1.0.0
 * @since 2018
 */
function b2_get_des($post_id,$size,$content = ''){
	return B2\Modules\Templates\Modules\Sliders::get_des($post_id,$size,$content);
}

function b2_emoji_reject($text){
	$len = mb_strlen($text);
	$new_text = '';
	for ($i = 0; $i < $len; $i++) {
		$word = mb_substr($text, $i, 1);
		if (strlen($word) <= 3) {
			$new_text .= $word;
		}
	}
	return $new_text;
}

function get_post_qrcode($post_id = 0){

	if(!$post_id){
		global $post;
		$post_id = $post->ID;
	}

	return B2\Modules\Common\Post::save_post_qrcode($post_id,true);

}

//对象转数组
function b2_object_to_array($obj) {
    $obj = (array)$obj;
    foreach ($obj as $k => $v) {
        if (gettype($v) == 'resource') {
            return;
        }
        if (gettype($v) == 'object' || gettype($v) == 'array') {
            $obj[$k] = (array)b2_object_to_array($v);
        }
    }

    return $obj;
}

/**
 * 原生分页
 *
 * @param int $$range 偏移量
 * @param array pages 总页数；paged 当前页面
 * @param string $content 需要截取的内容
 *
 * @return string 导航字符串
 * @author Li Ruchun <lemolee@163.com>
 * @version 1.0.0
 * @since 2018
 */
function b2_pagenav($page_data = array('pages'=>0,'paged'=>0),$no_paged = false){

	$range = 4;
	$paged = 1;

	if($page_data['pages']){
		$max_page = $page_data['pages'];
	}else{
		global $wp_query;
		$max_page = $wp_query->max_num_pages;
	}
	if($page_data['paged']){
		$paged = (int)$page_data['paged'];
	}

	$current_url = '';
	if($no_paged){
		global $wp;
		$current_url = B2_HOME_URI.'/'.$wp->request;
		

		if($paged > 1){
			$current_url = rtrim($current_url,strrchr($current_url,'/'));
		}
	}

	$html = '';
	if($max_page > 1){
		$html .= '<div class="btn-group nav-normal post-nav">';

		if($max_page > $range){

			if($paged < $range){

				for($i = 1; $i <= ($range + 1); $i++){

					$link = rtrim(get_pagenum_link($i), "/");
					if($no_paged){
						$link = $current_url.'/'.$i;
					}

					$html .= '<a class="button empty '.($i==$paged ? 'selected disabled' : '').'" href="'. $link .'">'.$i.'</a>';
				}
			}elseif($paged >= ($max_page - ceil(($range/2)))){

				for($i = $max_page - $range; $i <= $max_page; $i++){
					$link = rtrim(get_pagenum_link($i), "/");
					if($no_paged){
						$link = $current_url.'/'.$i;
					}

					$html .= '<a class="button empty '.($i==$paged ? 'selected disabled' : '').'" href="'. $link .'">'.$i.'</a>';
				}
			}elseif($paged >= $range && $paged < ($max_page - ceil(($range/2)))){

				$link = rtrim(get_pagenum_link(1), "/");
				if($no_paged){
					$link = $current_url;
				}

				$html .= '<a href="'.$link.'" class="button empty">1</a>';
				$html .= '<a class="empty button bordernone" href="javascript:void(0)">...</a>';
				for($i = ($paged - ceil($range/2)); $i <= ($paged + ceil(($range/2))); $i++){
					$link = rtrim(get_pagenum_link($i), "/");
					if($no_paged){
						$link = $current_url.'/'.$i;
					}

					$html .= '<a class="button empty '.($i==$paged ? 'selected disabled' : '').'" href="'. $link .'">'.$i.'</a>';
				}
			}

		}else{

			for($i = 1; $i <= $max_page; $i++){
				$link = rtrim(get_pagenum_link($i), "/");
				if($no_paged){
					$link = $current_url.'/'.$i;
				}

				$html .= '<a class="button empty '.($i==$paged ? 'selected disabled' : '').'" href="'. $link .'">'.$i.'</a>';
			}

		}

		if($max_page > $range){
			$html .= '<a class="empty button bordernone" href="javascript:void(0)">...</a>';
			$link =  rtrim(get_pagenum_link($max_page), "/");
			if($no_paged){
				$link = $current_url.'/'.$max_page;
			}
			$html .= '<a class="button empty" href="' . $link . '">'.$max_page.'</a>';
		}

		$html .= '<label class="pager-center">
			<input type="text" value="'.$paged.'" ref="pagenavnumber" @keyup.enter="jumpAc($event)" @focus="focus" @blur="blur" autocomplete="off">
			/<span v-show="!showGoN">'.$max_page.__(' 页','b2').'</span>
			<button class="b2-color text" @click.prevent.stop="jumpAc($event)" v-show="showGoN" v-cloak>'.__('前往','b2').'</button>
		</label>';

		$html .= '</div>';

		$html .= '<div class="btn-pager">';

		$pre = rtrim(get_pagenum_link($paged-1), "/");

		if($no_paged){
			$pre = $current_url.'/'.($paged-1);
		}

		$html .= $paged-1 > 0 ? '<a class="button empty" href="'.$pre.'">❮</a>' : '<a class="button selected empty" herf="javascript:void(0)">❮</a>';

		$next = rtrim(get_pagenum_link($paged+1), "/");

		if($no_paged){
			$next = $current_url.'/'.($paged+1);
		}

		$html .= $paged+1 <= $max_page ? '<a href="'.$next.'" class="empty button">❯</a>' : '<a class="empty selected button" herf="javascript:void(0)">❯</a>';
		$html .= '</div>';

	}

	if($max_page > 1){
		return '<div class="ajax-pager"><div class="ajax-pagenav">'.$html.'</div></div>';
	}else {
		return '';
	}
}

function b2_increment($num){
	return $num+1;
}

//搜索形式
function b2_get_search_type(){
	$arg = apply_filters('b2_custom_post_type', array(
		// 'all'=>__('全部', 'b2'),
		'post'=>__('文章', 'b2'),
		'user'=>__('用户', 'b2'),
		'shop'=>b2_get_option('normal_custom','custom_shop_name'),
		'document'=>b2_get_option('normal_custom','custom_document_name'),
		'newsflashes'=>b2_get_option('normal_custom','custom_newsflashes_name'),
		'circle'=>b2_get_option('normal_custom','custom_circle_name'),
		'links'=>b2_get_option('normal_custom','custom_links_name'),
		'ask'=>b2_get_option('normal_custom','custom_ask_name'),
		'answer'=>b2_get_option('normal_custom','custom_answer_name'),
		'infomation'=>b2_get_option('normal_custom','custom_infomation_name'),
		'cpay'=>__('自定义支付','b2'),
		// 'bubble'=>__('冒泡', 'b2'),
		// 'labs'=>__('研究所', 'b2'),
	));

	if(is_audit_mode()){
		unset($arg['user']);
		unset($arg['shop']);
		unset($arg['newsflashes']);
		unset($arg['circle']);
		unset($arg['infomation']);
		unset($arg['ask']);
		unset($arg['answer']);
	}

	return $arg;
}


function b2_get_post_type_name($key){

	if(!$key) return;

	$arg = apply_filters('b2_get_post_type_name', array(
		'post'=>__('文章', 'b2'),
		'page'=>__('页面','b2'),
		'shop'=>__('商品','b2'),
		'document'=>b2_get_option('normal_custom','custom_document_name'),
		'newsflashes'=>b2_get_option('normal_custom','custom_newsflashes_name'),
		'circle'=>b2_get_option('normal_custom','custom_circle_name').__('帖子','b2'),
		'links'=>b2_get_option('normal_custom','custom_links_name').__('帖子','b2'),
		'ask'=>b2_get_option('normal_custom','custom_ask_name').__('帖子','b2'),
		'answer'=>b2_get_option('normal_custom','custom_answer_name'),
		'infomation'=>b2_get_option('normal_custom','custom_infomation_name').__('帖子','b2'),
		'cpay'=>__('自定义支付','b2')
	));

	if(isset($arg[$key])) return $arg[$key];

	return '';
}

//获取邀请码设置
function b2_get_inv_settings(){
	$invitation = b2_get_option('invitation_main','required');
	if($invitation){
		$invitation_text = b2_get_option('invitation_main','invitation_text');
		$invitation_text = explode('|',$invitation_text);
	}else{
		$invitation_text = array(__('获取邀请码','b2'),'#');
	}

	return array(
		'type'=>$invitation,
		'text'=>isset($invitation_text[0]) ? $invitation_text[0] : '',
		'link'=>isset($invitation_text[1]) ? $invitation_text[1] : ''
	);
}

//通过图片url获取图片ID
function b2_get_image_id($image_url) {
	global $wpdb;
	$attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $image_url ));
    return $attachment[0];
}

//自定义页面的名称数组
function b2_custom_page_arg(){

	$circle_link = b2_get_option('normal_custom','custom_circle_link');
	$circle_name = b2_get_option('normal_custom','custom_circle_name');

	$links_name = b2_get_option('normal_custom','custom_links_name');

	$infomation_link = b2_get_option('normal_custom','custom_infomation_link');
	$infomation_name = b2_get_option('normal_custom','custom_infomation_name');

	$ask_link = b2_get_option('normal_custom','custom_ask_link');
	$ask_name = b2_get_option('normal_custom','custom_ask_name');


	return apply_filters('b2_custom_page_arg',array(
		// 'announcements'=>array(
		// 	'key'=>b2_get_option('normal_custom','custom_announcement_link'),
		// 	'name'=>__('公告列表','b2')
		// ),
		'directmessage'=>array(
			'key'=>'directmessage',
			'name'=>__('私信','b2')
		),
		'message'=>array(
			'key'=>'message',
			'name'=>__('通知','b2')
		),
		'update'=>array(
			'key'=>'update',
			'name'=>__('更新','b2')
		),
		'collection'=>array(
			'key'=>b2_get_option('normal_custom','custom_collection_link'),
			'name'=>sprintf(__('%s中心','b2'),b2_get_option('normal_custom','custom_collection_name'))
		),
		'test'=>array(
			'key'=>'test',
			'name'=>__('测试','b2'),
		),
		'open'=>array(
			'key'=>'open',
			'name'=>__('社交登录','b2')
		),
		'invitation'=>array(
			'key'=>'invitation',
			'name'=>__('输入邀请码','b2')
		),
		'download'=>array(
			'key'=>'download',
			'name'=>__('下载','b2')
		),
		'notify'=>array(
			'key'=>'notify',
			'name'=>__('支付异步回调','b2')
		),
		'return'=>array(
			'key'=>'return',
			'name'=>__('支付结果','b2')
		),
		'pay'=>array(
			'key'=>'pay',
			'name'=>__('支付中','b2')
		),
		'xunhusuccess'=>array(
			'key'=>'xunhusuccess',
			'name'=>__('支付成功','b2')
		),
		'xunhufail'=>array(
			'key'=>'xunhufail',
			'name'=>__('支付失败','b2')
		),
		'gold'=>array(
			'key'=>'gold',
			'name'=>__('财富管理','b2')
		),
		'vips'=>array(
			'key'=>'vips',
			'name'=>__('成为会员','b2')
		),
		'verify'=>array(
			'key'=>'verify',
			'name'=>__('认证服务','b2')
		),
		'wecatmpnotify'=>array(
			'key'=>'wecatmpnotify',
			'name'=>__('微信公众号消息回调','b2')
		),
		'redirect'=>array(
			'key'=>'redirect',
			'name'=>__('跳转中...','b2')
		),
		'write'=>array(
			'key'=>'write',
			'name'=>__('写文章','b2')
		),
		'task'=>array(
			'key'=>'task',
			'name'=>__('我的任务','b2')
		),
		'mission'=>array(
			'key'=>'mission',
			'name'=>__('签到管理','b2')
		),
		'carts'=>array(
			'key'=>'carts',
			'name'=>__('购物车','b2')
		),
		'gold-top'=>array(
			'key'=>'gold-top',
			'name'=>__('财富排行','b2')
		),
		'requests'=>array(
			'key'=>'requests',
			'name'=>__('工单中心','b2')
		),
		'tags'=>array(
			'key'=>'tags',
			'name'=>__('标签','b2')
		),
		'distribution'=>array(
			'key'=>'distribution',
			'name'=>__('推广中心','b2')
		),
		'create-circle'=>array(
			'key'=>'create-'.$circle_link,
			'name'=>sprintf(__('创建%s','b2'),$circle_name)
		),
		'all-circles'=>array(
			'key'=>'all-'.$circle_link.'s',
			'name'=>sprintf(__('所有%s','b2'),$circle_name)
		),
		'circle-people'=>array(
			'key'=>$circle_link.'-people',
			'name'=>sprintf(__('他的%s','b2'),$circle_name)
		),
		'ajaxupdate'=>array(
			'key'=>'ajaxupdate',
			'name'=>__('异步更新数据','b2')
		),
		'circle-topics'=>array(
			'key'=>$circle_link.'-topics',
			'name'=>sprintf(__('%s管理','b2'),$circle_name)
		),
		'circle-users'=>array(
			'key'=>$circle_link.'-users',
			'name'=>sprintf(__('%s用户管理','b2'),$circle_name)
		),
		'circle-topic-edit'=>array(
			'key'=>$circle_link.'-topic-edit',
			'name'=>__('编辑话题','b2')
		),
		'dark-room'=>array(
			'key'=>'dark-room',
			'name'=>__('小黑屋','b2')
		),
		'cat-group'=>array(
			'key'=>'cat-group',
			'name'=>__('全部','b2')
		),
		'tastream'=>array(
			'key'=>'tastream',
			'name'=>__('Ta的动态','b2')
		),
		'loadercheck'=>array(
			'key'=>'loadercheck',
			'name'=>__('环境检查','b2')
		),
		// 'kuayu'=>array(
		// 	'key'=>'kuayu',
		// 	'name'=>__('跨域','b2')
		// ),
		'link-register'=>array(
			'key'=>'link-register',
			'name'=>__('申请入驻','b2')
		),
		'po-infomation'=>array(
			'key'=>'po-'.$infomation_link,
			'name'=>sprintf(__('发布%s','b2'),$infomation_name)
		),
		'infomation-people'=>array(
			'key'=>$infomation_link.'-people',
			'name'=>sprintf(__('%s用户中心','b2'),$infomation_name)
		),
		'get-image'=>array(
			'key'=>'get-image',
			'name'=>__('获取图片','b2')
		),
		'po-ask'=>array(
			'key'=>'po-'.$ask_link,
			'name'=>__('提问','b2')
		),
		'ask-people'=>array(
			'key'=>$ask_link.'-people',
			'name'=>sprintf(__('%s用户中心','b2'),$ask_name)
		),
		'social-login'=>array(
			'key'=>'social-login',
			'name'=>__('社交登录','b2')
		),
	));
}

if(!function_exists('bbq_core')){

	function bbq_core() {

		$request_uri_array  = apply_filters('request_uri_items',  array('@eval', 'eval\(', 'UNION(.*)SELECT', '\(null\)', 'base64_', '\/localhost', '\%2Flocalhost', '\/pingserver', 'wp-config\.php', '\/config\.', '\/wwwroot', '\/makefile', 'crossdomain\.', 'proc\/self\/environ', 'usr\/bin\/perl', 'var\/lib\/php', 'etc\/passwd', '\/https\:', '\/http\:', '\/ftp\:', '\/file\:', '\/php\:', '\/cgi\/', '\.cgi', '\.cmd', '\.bat', '\.exe', '\.sql', '\.ini', '\.dll', '\.htacc', '\.htpas', '\.pass', '\.asp', '\.jsp', '\.bash', '\/\.git', '\/\.svn', ' ', '\<', '\>', '\/\=', '\.\.\.', '\+\+\+', '@@', '\/&&', '\/Nt\.', '\;Nt\.', '\=Nt\.', '\,Nt\.', '\.exec\(', '\)\.html\(', '\{x\.html\(', '\(function\(', '\.php\([0-9]+\)', '(benchmark|sleep)(\s|%20)*\(', 'indoxploi', 'xrumer'));
		$query_string_array = apply_filters('query_string_items', array('@@', '\(0x', '0x3c62723e', '\;\!--\=', '\(\)\}', '\:\;\}\;', '\.\.\/', '127\.0\.0\.1', 'UNION(.*)SELECT', '@eval', 'eval\(', 'base64_', 'localhost', 'loopback', '\%0A', '\%0D', '\%00', '\%2e\%2e', 'allow_url_include', 'auto_prepend_file', 'disable_functions', 'input_file', 'execute', 'file_get_contents', 'mosconfig', 'open_basedir', '(benchmark|sleep)(\s|%20)*\(', 'phpinfo\(', 'shell_exec\(', '\/wwwroot', '\/makefile', 'path\=\.', 'mod\=\.', 'wp-config\.php', '\/config\.', '\$_session', '\$_request', '\$_env', '\$_server', '\$_post', '\$_get', 'indoxploi', 'xrumer'));
		$user_agent_array   = apply_filters('user_agent_items',   array('acapbot', '\/bin\/bash', 'binlar', 'casper', 'cmswor', 'diavol', 'dotbot', 'finder', 'flicky', 'md5sum', 'morfeus', 'nutch', 'planet', 'purebot', 'pycurl', 'semalt', 'shellshock', 'skygrid', 'snoopy', 'sucker', 'turnit', 'vikspi', 'zmeu'));

		$request_uri_string  = false;
		$query_string_string = false;
		$user_agent_string   = false;

		if (isset($_SERVER['REQUEST_URI'])     && !empty($_SERVER['REQUEST_URI']))     $request_uri_string  = $_SERVER['REQUEST_URI'];
		if (isset($_SERVER['QUERY_STRING'])    && !empty($_SERVER['QUERY_STRING']))    $query_string_string = $_SERVER['QUERY_STRING'];
		if (isset($_SERVER['HTTP_USER_AGENT']) && !empty($_SERVER['HTTP_USER_AGENT'])) $user_agent_string   = $_SERVER['HTTP_USER_AGENT'];

		if ($request_uri_string || $query_string_string || $user_agent_string) {

			if (

				// strlen( $_SERVER['REQUEST_URI'] ) > 255 || // optional

				preg_match('/'. implode('|', $request_uri_array)  .'/i', $request_uri_string)  ||
				preg_match('/'. implode('|', $query_string_array) .'/i', $query_string_string) ||
				preg_match('/'. implode('|', $user_agent_array)   .'/i', $user_agent_string)

			) {

				bbq_response();

			}

		}

	}

	function bbq_response() {

		header('HTTP/1.1 403 Forbidden');
		header('Status: 403 Forbidden');
		header('Connection: Close');

		exit();

	}

	bbq_core();

}

//自定义用户页面数组
function b2_custom_user_arg(){
	$shop_slug = b2_get_option('normal_custom','custom_newsflashes_link');

	return apply_filters('b2_custom_user_arg',array(
		'post'=>__('文章','b2'),
		$shop_slug=>b2_get_option('normal_custom','custom_newsflashes_name'),
		'comments'=>__('回复','b2'),
		'following'=>__('关注','b2'),
		'followers'=>__('粉丝','b2'),
		'collections'=>__('收藏','b2'),
		'myinv'=>__('邀请码','b2'),
		'orders'=>__('我的订单','b2'),
		'settings'=>__('我的设置','b2'),
		'index'=>__('基本信息','b2')
	));
}

//用户自定页面
function b2_post_types(){
	$shop_slug = b2_get_option('normal_custom','custom_newsflashes_link');
	return apply_filters('b2_post_types_arg',array(
		'post'=>__('文章','b2'),
		'shop'=>b2_get_option('normal_custom','custom_shop_name'),
		$shop_slug=>b2_get_option('normal_custom','custom_newsflashes_name'),
		//'topic'=>__('帖子','b2')
	));
}

//快递公司
function b2_express_types(){
	return apply_filters('b2_express',array(
		'shunfeng'=>__('顺丰快递','b2'),
		'youzheng'=>__('邮政EMS','b2'),
		'shentong'=>__('申通快递','b2'),
		'zhongtong'=>__('中通快递','b2'),
		'yunda'=>__('韵达快递','b2'),
		'tiantian'=>__('天天快递','b2'),
		'debang'=>__('德邦快递','b2'),
		'baishi'=>__('百世快递','b2'),
		'zhaijisong'=>__('宅急送','b2'),
		'jingdong'=>__('京东快递','b2')
	));
}

//订单形式
function b2_order_type($id = 0){
	$circle_name = b2_get_option('normal_custom','custom_circle_name');
	return apply_filters('b2_order_type',array(
		'g'=>__('合并付款','b2'),
		'gx'=>__('商城订单','b2'),
		'c'=>__('积分抽奖','b2'),
		'd'=>__('积分兑换','b2'),
		'w'=>__('文章内购','b2'),
		'x'=>__('资源下载','b2'),
		'ds'=>__('文章打赏','b2'),
		'cz'=>sprintf(__('%s充值','b2'),B2_MONEY_NAME),
		'vip'=>__('vip购买','b2'),
		'cg'=>__('积分购买','b2'),
		'v'=>__('视频购买','b2'),
		'verify'=>__('认证付费','b2'),
		'circle_join'=>sprintf(__('付费加入%s','b2'),$circle_name),
		'circle_read_answer_pay'=>sprintf(__('付费查看%s问答','b2'),$circle_name),
		'circle_hidden_content_pay'=>__('付费查看隐藏帖子','b2'),
		'mission'=>__('签到填坑','b2'),
		'coupon'=>__('优惠劵使用','b2'),
		'custom'=>$id ? get_the_id($id) : __('自定义支付','b2'),
		'infomation_sticky'=>sprintf(__('%s置顶','b2'),b2_get_option('normal_custom','custom_infomation_name'))
	),$id);
}


function b2_text($data = array()){
	$circle_name = b2_get_option('normal_custom','custom_circle_name');
	$newsflashes_name = b2_get_option('normal_custom','custom_newsflashes_name');
	$money_name = b2_get_option('normal_main','money_name');
	return array(
		'circle'=>array(
			'ask'=>__('对您的问题进行说明','b2'),
			'guess'=>__('猜对以后要显示的内容','b2'),
			'vote'=>__('对您的投票项目进行说明','b2'),
			'say'=>__('有什么新鲜事？','b2'),
			'repeat_id'=>__('请不要重复添加同一个用户','b2'),
			'remove_image'=>__('确定要删除这张图片吗？','b2'),
			'remove_video'=>__('确定要删除这个视频吗？','b2'),
			'remove_file'=>__('确定要删除这个文件吗？','b2'),
			'remove_card'=>__('确定要删除这个卡片吗？','b2'),
			'create_circle_pay_error'=>__('请选择付费机制','b2'),
			'create_circle_role_money_error'=>sprintf(__('请至少设置一个%s金额','b2'),$money_name),
			'create_circle_role_join_error'=>__('请选择入圈规则','b2'),
			'create_circle_info_error'=>sprintf(__('请完善%s资料','b2'),$circle_name),
			'create_circle_info_name_error'=>sprintf(__('%s名称必须大于2小于20个字符','b2'),$circle_name),
			'create_circle_info_desc_error'=>sprintf(__('%s描述必须大于20小于100个字符','b2'),$circle_name),
			'create_circle_role_lv_error'=>sprintf(__('请选择允许加入%s的用户组','b2'),$circle_name),
			'img_locked'=>__('请上传完毕后再添加新的上传','b2'),
			'file_count'=>__('超过上传数量限制','b2'),
			'all_users'=>__('所有人','b2'),
			'change_topic_form'=>__('切换讨论框会清除讨论框里已有的内容，确定要切换吗？','b2'),
			'set_sticky_success'=>__('设置置顶成功','b2'),
			'set_sticky_fail'=>__('取消置顶成功','b2'),
			'delete_success'=>__('删除成功','b2'),
			'delete_fail'=>__('删除失败，请联系管理员','b2'),
			'status_success'=>__('审核成功','b2'),
			'status_fail'=>__('审核失败','b2'),
			'delete_confirm'=>__('确定要删除这个话题吗？','b2'),
			'delete_user'=>__('确定要将这个用户踢出圈子吗？','b2'),
			'join_success'=>sprintf(__('恭喜，您已成功加入本%s','b2'),$circle_name),
			'join_title'=>sprintf(__('加入%s','b2'),$circle_name),
			'circle_date'=>array(
				'permanent'=>__('永久有效','b2'),
				'year'=>__('按年付费','b2'),
				'halfYear'=>__('半年付费','b2'),
				'season'=>__('按季付费','b2'),
				'month'=>__('按月付费','b2')
			),
			'text_less'=>__('文字太少','b2'),
			'text_more'=>__('文字太多','b2'),
			'file_error'=>__('请删除错误文件','b2'),
			'subing'=>__('发布中...','b2'),
			'sub_success'=>__('发布成功','b2'),
			'waiting_uploads'=>__('等待文件上传','b2'),
			'submit'=>__('立刻提交','b2'),
			'answer_image'=>__('您已上传过一张图片，确定要删除重新上传吗？','b2'),
			'answer_file'=>__('您已上传过一个附件，确定要删除重新上传吗？','b2'),
			'read_answer'=>__('偷瞄答案','b2'),
			'hidden_pay'=>__('查看隐藏内容：','b2'),
			'join_comment'=>__('暂无权查看评论内容','b2'),
			'delete_answer'=>__('确定要删除这个回答吗？','b2'),
			'delete_comment'=>__('确定要删除这条评论吗？','b2'),
		),
		'global'=>array(
			'xieyi'=>__('请勾选注册协议','b2'),
			'not_allow'=>__('权限不足','b2'),
			'dark_room_down'=>__('小黑屋反思中，不准下载！','b2'),
			'comment_down'=>__('评论并刷新后下载！','b2'),
			'role_down'=>__('没有权限下载！','b2'),
			'mission_tk'=>__('签到填坑','b2'),
			'copy_success'=>__('复制成功','b2'),
			'copy_select'=>__('请选中复制','b2'),
			'put_link'=>__('请输入连接','b2'),
			'video_file_error'=>__('视频文件有损坏，无法上传：','b2'),
			'my'=>__('我的','b2'),
			'login'=>__('登录','b2'),
			'has_mission'=>__('您已经签到了','b2'),
			'delete_coupon'=>__('确定要删除这个优惠劵吗？','b2'),
			'delete_post'=>__('确定要删除这篇文章吗？','b2'),
			'more_people'=>__('等人','b2'),
			'pay_money'=>__('充值','b2'),
			'pay_money_success'=>__('充值成功','b2'),
			'editor_hidden_content'=>__('请在这里编辑您的隐藏内容','b2'),
			'pay_credit'=>__('积分购买','b2'),
			'buy_count_error'=>__('购买数量错误','b2'),
			'delete_address'=>__('确定要删除这个地址吗？','b2'),
			'add_address'=>__('请添加您的收货地址','b2'),
			'add_email'=>__('请添加您的邮箱地址！','b2'),
			'buy'=>__('购买','b2'),
			'get_success'=>__('领取成功','b2'),
			'min'=>__('分','b2'),
			'sec'=>__('秒','b2'),
			'and'=>__('和','b2'),
			'edit_success'=>__('修改成功','b2'),
			'all'=>__('全部','b2'),
			'newsflashe_r'=>sprintf(__('%s标题和内容必填','b2'),$newsflashes_name),
			'newsflashe_insert_success'=>sprintf(__('%s发布成功','b2'),$newsflashes_name),
			'credit_pay'=>__('确定要消费 ${credit} 积分进行抽奖吗？','b2'),
			'missioning'=>__('签到中,请稍后...','b2'),
			'mission_success'=>__('签到成功','b2'),
			'save_r_success'=>__('保存草稿成功','b2'),
			'read_more'=>__('阅读更多','b2'),
			'sketchpad_empty'=>__('涂鸦不可为空！','b2'),
			'tuya'=>__('涂鸦','b2'),
			'check_message'=>__('请前往手机或邮箱查看验证码','b2'),
			'check_type'=>array(
				'tel'=>__('手机','b2'),
				'mail'=>__('邮箱','b2')
			),
			'tx_alert'=>sprintf(__('申请之后%s会扣除提现部分，稍后由我们人工为您提现，确认要提现吗？','b2'),$money_name),
			'cpay_file_count'=>__('最多只能上传 ${count} 个文件','b2'),
			'cpay_file_count_less'=>__('还能再上传 ${count} 个文件','b2'),
			'cpay_required_fields'=>__('请完成所有红星的必填项目','b2'),
			'infomation_sticky_pay'=>__('您选择了置顶，请先支付','b2'),
			'infomation_sticky_pay_title'=>sprintf(__('%s置顶','b2'),b2_get_option('normal_custom','custom_infomation_name')),
			'infomation_po_cat'=>__('请选择分类','b2'),
			'my_gold'=>__('我的财富','b2'),
			'my_requests'=>__('我的工单','b2'),
			'submit_success'=>__('提交成功','b2'),
			'max_ask_cat'=>__('最多只能选择3个标签','b2'),
			'best_answer'=>__('选择最佳答案后无法变更，确定要指定此回答为最佳答案吗？','b2'),
			'cannotanswer'=>__('您暂时无权发表帖子','b2')
		),
		'b2_text'=>array(
			'default_circle'=>__('广场','b2'),
			'default_circle_desc'=>__('公共区域，请文明发言!','b2'),
			'jihuo'=>__('主题文件损坏，无法正常激活，请重新下载主题','b2'),
			'jihuo_error'=>__('Wordpress没有正确安装，请从 cn.wordpress.org 站点下载最新版，重新安装','b2'),
			'jihuo_error_not_isset'=>__('主题文件损坏，无法正常激活，请重新下载主题。','b2'),
			'jihuo_error_no_role'=>__('主题文件夹权限错误：通常情况下 %s 文件夹内所有文件的用户组应当是 www 并且权限为 755 请修改之后再激活。','b2'),
			'jihuo_shouquan_error'=>__('授权错误','b2'),
			'buy'=>__('购买','b2'),
			'exchange'=>__('积分兑换','b2'),
			'lottery'=>__('积分抽奖','b2'),
			'info'=>__('的基本信息','b2'),
			'order_error'=>__('订单信息错误','b2'),
			'money_error'=>__('金额错误','b2'),
			'order_no_allow'=>__('非法操作','b2'),
			'order_cg_min'=>__('充值金额应当大于','b2'),
			'order_no_lv'=>__('没有这个等级','b2'),
			'order_pay_error'=>__('支付信息错误','b2'),
			'order_mission_error'=>__('签到积分设置错误','b2'),
			'order_mission_no_need'=>__('您没有必要填坑','b2'),
			'order_credit_empty'=>sprintf(__('您的%s不足','b2'),$money_name),
			'order_login'=>__('请先登录','b2'),
			'order_money_too_max'=>__('支付金额过大','b2'),
			'order_time_pass'=>__('支付过期，请重新发起','b2'),
			'order_check_error'=>__('订单合法性检查失败','b2'),
			'check_repo_error'=>__('发布频次过高！您已被关小黑屋，一个小时后解封。多次被关小黑屋会永久封禁IP和账户','b2'),
			'check_dark_room'=>__('在小黑屋中，无法操作','b2'),
			'check_dark_room_why'=>__('请求本站接口过于频繁','b2'),
			'all_peoples'=>__('所有人','b2'),
			'guest'=>__('游客','b2'),
			'normal_group'=>__('普通用户组','b2'),
			'vip_group'=>__('VIP用户组','b2'),
			'video'=>__('视频','b2'),
			'download'=>__('下载','b2'),
			'hidden'=>__('隐藏内容','b2'),
			'tuya'=>__('涂鸦','b2'),
			'under_stock'=>__('库存不足','b2'),
			'link_register'=>__('申请入驻','b2'),
			'order_infomation_price_error'=>__('置顶天数必须大于等于1天','b2')
		)
	);
}

//用户权限
function b2_roles_arg(){

	$circle_name = b2_get_option('normal_custom','custom_circle_name');
	$newsflashes_name = b2_get_option('normal_custom','custom_newsflashes_name');
	$ask_name = b2_get_option('normal_custom','custom_ask_name');

	$arg = array(
		'message'=>__( '发送私信', 'b2' ),
		'post'=>__( '发布文章', 'b2' ),
		'comment'=>__( '发布评论', 'b2' ),
		'newsflashes'=>sprintf(__( '发布%s', 'b2' ),$newsflashes_name),
		'ask'=>sprintf(__('发布%s','b2'),$ask_name),
		'answer'=>sprintf(__('%s的回答','b2'),$ask_name),
		'infomation'=>sprintf(__( '发布%s', 'b2' ),b2_get_option('normal_custom','custom_infomation_name'))
	);

	if(b2_get_option('circle_main','circle_open')){
		$arg['circle_topic'] = sprintf(__( '%s发帖', 'b2' ),$circle_name);
		$arg['circle_create'] = sprintf(__( '创建%s', 'b2' ),$circle_name);
	}

	return apply_filters('b2_roles_arg',$arg);
}

//每日任务
function b2_task_arg(){
	$newsflashes_name = b2_get_option('normal_custom','custom_newsflashes_name');
	$infomation_name = b2_get_option('normal_custom','custom_infomation_name');
	$circle_name = b2_get_option('normal_custom','custom_circle_name');

	return apply_filters('b2_task_arg',array(
		'task_post'=>array(
			'name'=>__( '发布文章', 'b2' ),
			'times'=>b2_get_option('normal_task','task_post'),
			'icon'=>'b2-write',
			'credit'=>b2_get_option('normal_gold','credit_post'),
			'url'=>b2_get_custom_page_url('write')
		),
		'task_infomation'=>array(
			'name'=>sprintf(__( '发布%s帖子', 'b2' ),$infomation_name),
			'times'=>b2_get_option('normal_task','task_infomation'),
			'icon'=>'b2-write',
			'credit'=>b2_get_option('normal_gold','credit_infomation'),
			'url'=>B2_HOME_URI.'/po-'.b2_get_option('normal_custom','custom_infomation_link')
		),
		'task_circle'=>array(
			'name'=>sprintf(__( '发布%s帖子', 'b2' ),$circle_name),
			'times'=>b2_get_option('normal_task','task_circle'),
			'icon'=>'b2-write',
			'credit'=>b2_get_option('normal_gold','credit_topic'),
			'url'=>B2_HOME_URI.'/'.b2_get_option('normal_custom','custom_circle_link')
		),
		'task_newsflashes'=>array(
			'name'=>sprintf(__( '发布%s', 'b2' ),$newsflashes_name),
			'times'=>b2_get_option('normal_task','task_newsflashes'),
			'icon'=>'b2-kuaixun',
			'credit'=>b2_get_option('normal_gold','credit_newsflashes'),
			'url'=>get_post_type_archive_link('newsflashes')
		),
		'task_post_vote'=>array(
			'name'=>__( '文章被点赞', 'b2' ),
			'times'=>b2_get_option('normal_task','task_post_vote'),
			'icon'=>'b2-zan',
			'credit'=>b2_get_option('normal_gold','credit_post_up'),
			'url'=>''
		),
		'task_post_comment'=>array(
			'name'=>__( '文章（帖子）被评论', 'b2' ),
			'times'=>b2_get_option('normal_task','task_post_comment'),
			'icon'=>'b2-zan',
			'credit'=>b2_get_option('normal_gold','credit_post_comment'),
			'url'=>''
		),
		'task_comment'=>array(
			'name'=>__( '评论', 'b2' ),
			'times'=>b2_get_option('normal_task','task_comment'),
			'icon'=>'b2-pinglun',
			'credit'=>b2_get_option('normal_gold','credit_comment'),
			'url'=>B2\Modules\Common\Task::get_random_post_url()
		),
		'task_comment_vote'=>array(
			'name'=>__( '评论被点赞', 'b2' ),
			'times'=>b2_get_option('normal_task','task_comment_vote'),
			'icon'=>'b2-love',
			'credit'=>b2_get_option('normal_gold','credit_comment_up'),
			'url'=>''
		),
		'task_follow'=>array(
			'name'=>__( '关注某人', 'b2' ),
			'times'=>b2_get_option('normal_task','task_follow'),
			'icon'=>'b2-guanzhu',
			'credit'=>b2_get_option('normal_gold','credit_follow'),
			'url'=>B2_HOME_URI.'?s=&type=user'
		),
		'task_fans'=>array(
			'name'=>__( '被某人关注', 'b2' ),
			'times'=>b2_get_option('normal_task','task_fans'),
			'icon'=>'b2-love',
			'credit'=>b2_get_option('normal_gold','credit_follow'),
			'url'=>''
		)
	));
}

//新手任务
function b2_task_user_arg(){
	return apply_filters('b2_task_user_arg',array(
		'task_user_qq'=>array(
			'name'=>__( '绑定QQ', 'b2' ),
			'times'=>1,
			'credit'=>b2_get_option('normal_task','task_user_qq'),
			'icon'=>'b2-qq',
			'url'=>''
		),
		'task_user_weixin'=>array(
			'name'=>__( '绑定微信', 'b2' ),
			'times'=>1,
			'credit'=>b2_get_option('normal_task','task_user_weixin'),
			'icon'=>'b2-weixin',
			'url'=>''
		),
		'task_user_weibo'=>array(
			'name'=>__( '绑定微博', 'b2' ),
			'times'=>1,
			'credit'=>b2_get_option('normal_task','task_user_weibo'),
			'icon'=>'b2-weibo',
			'url'=>''
		),
		'task_user_verify'=>array(
			'name'=>__( '获得认证', 'b2' ),
			'times'=>1,
			'credit'=>b2_get_option('normal_task','task_user_verify'),
			'icon'=>'b2-auth',
			'url'=>b2_get_custom_page_url('verify')
		),
	));
}

function b2_file_type(){
	return apply_filters('b2_file_type',array(
		'comment',
		'post',
		'avatar',
		'cover',
		'newsflashes',
		'request',
		'verify',
		'qrcode',
		'circle',
		'comment_drawing',
		'links',
		'cpay',
		'infomation'
	));
}

function b2_weixin_message_templates(){
	return apply_filters('b2_weixin_message_templates', [
		'pay_success'=>[
			'name'=>'订单支付成功提醒',
			'id'=>'OPENTM416836000',
			'temp_id'=>''
		],
		'new_order'=>[
			'name'=>'新订单通知',
			'id'=>'OPENTM418001517',
			'temp_id'=>''
		],
		'order_ship'=>[
			'name'=>'订单发货通知',
			'id'=>'OPENTM418033200',
			'temp_id'=>''
		],
		'vip_success'=>[
			'name'=>'开通成功通知',
			'id'=>'OPENTM411450851',
			'temp_id'=>''
		],
		'check_success'=>[
			'name'=>'审核通过通知',
			'id'=>'OPENTM411211455',
			'temp_id'=>''
		],
	]);
}

//是否为审核模式
function is_audit_mode(){
	return b2_get_option('normal_main','audit_mode') ? true : false;
}

function b2_settings_error($type='updated',$message=''){
	$type = $type=='updated' ? 'updated' : 'error';
	if(empty($message)) $message = $type=='updated' ?  __('设置已保存。','b2') : __('保存失败，请重试。','b2');
	add_settings_error(
		'b2_settings_message',
		esc_attr( 'b2_settings_updated' ),
		$message,
		$type
	);
	settings_errors( 'b2_settings_message' );
}


function b2_get_page_width($show_widget){
	$page_width = b2_get_option('template_main','wrapper_width');
	$page_width = preg_replace('/\D/s','',$page_width);

	if($show_widget){
		$width = b2_get_option('template_main','sidebar_width');

		return $page_width - $width - B2_GAP;

	}else{
		return $page_width;
	}
}

function b2_check_price($price) {
    // 不能小于0
    if (preg_match('/^[1-9]+\d*(.\d{1,2})?$/',$price)) {  // ? 0次或1次, + 1次或多次, * 0次或多次
        return true;
    } else {
        return false;
    }
}

//获取自定义页面链接
function b2_get_custom_page_url($type){
	return B2\Modules\Common\Rewrite::get_custom_page_link($type);
}

function b2_oauth_types($no_mp = false,$change = false){
	$wx_pc = 'https://open.weixin.qq.com/connect/qrconnect?appid='. b2_get_option('normal_login','wx_pc_key') .'&response_type=code&scope=snsapi_login&redirect_uri='. urlencode (B2_HOME_URI.'/open?type=wx_pc');
	$wx_gz = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='. b2_get_option('normal_login','wx_gz_key') .'&&redirect_uri='. urlencode (B2_HOME_URI.'/open?type=wx_gz').'&response_type=code&state='.md5 ( uniqid ( rand (), true ) ).'&scope=snsapi_userinfo#wechat_redirect';

	$wx_pc_open = !b2_is_weixin() && b2_get_option('normal_login','wx_pc_open');
	$wx_gz_open = b2_is_weixin() && b2_get_option('normal_login','wx_gz_open');
	$wx_mp = !b2_is_weixin() && b2_get_option('normal_login','wx_mp_login');

	$wx = array(
		'name'=>__('微信','b2'),
		'url'=>$wx_pc_open ? $wx_pc : ($wx_gz_open ? $wx_gz : ''),
		'open'=>$wx_pc_open || $wx_gz_open ? true : false
	);

	if((($wx_mp && $no_mp) || $wx_pc_open) && !$change){
		$wx['url'] = 'javascript:void(0)';
		$wx['open'] = true;
		$wx['mp'] = true;
		$wx['pc_open'] = $wx_pc_open;
		$wx['icon'] = 'b2-weixin';
		$wx['color'] = '#7BB32E';
	}

	$normal = array(
		'weixin'=>$wx,
		'weibo'=>array(
			'name'=>__('微博','b2'),
			'icon'=>'b2-weibo',
			'color'=>'#FF3E3E',
			'url'=>'https://api.weibo.com/oauth2/authorize?client_id=' .b2_get_option('normal_login','weibo_id'). '&response_type=code&redirect_uri=' . urlencode (B2_HOME_URI.'/open?type=weibo'),
			'open'=>b2_get_option('normal_login','weibo_open') ? true : false
		),
		'qq'=>array(
			'name'=>__('QQ','b2'),
			'icon'=>'b2-qq',
			'color'=>'#2D89EF',
			'url'=>"https://graph.qq.com/oauth2.0/authorize?client_id=" .b2_get_option('normal_login','qq_id'). "&state=".md5 ( uniqid ( rand (), true ) )."&response_type=code&redirect_uri=" . urlencode (B2_HOME_URI.'/open?type=qq'),
			'open'=>b2_get_option('normal_login','qq_open') ? true : false
		)
	);

	$juhe = b2_get_option('normal_login','juhe_open');
	if($juhe){
		$types = b2_get_option('normal_login','juhe_types');
		if($types && !empty($types)){
			foreach ($types as $key => $value) {
				$name = $value;
				$icon = '';
				$color='';
				switch ($value) {
					case 'qq':
						$name = __('QQ','b2');
						$icon = 'b2-qq';
						$color = '#2D89EF';
						break;
					case 'wx':
						$name = __('微信','b2');
						$icon = 'b2-weixin';
						$color = '#7BB32E';
						break;
					case 'sina':
						$name = __('微博','b2');
						$icon = 'b2-weibo';
						$color = '#FF3E3E';
						break;
					case 'baidu':
						$name = __('百度','b2');
						$icon = 'b2-baidu-fill';
						$color = '#3385FF';
						break;
					case 'alipay':
						$name = __('支付宝','b2');
						$icon = 'b2-alipay-fill1';
						$color = '#00A0E9';
						break;
					case 'huawei':
						$name = __('华为','b2');
						$icon = 'b2-huawei';
						$color = '#FF0000';
						break;
					case 'xiaomi':
						$name = __('小米','b2');
						$color = '#FF6900';
						$icon = 'b2-xiaomi';
						break;
					case 'dingtalk':
						$name = __('钉钉','b2');
						$color = '#FF4081';
						$icon = 'b2-dingding';
						break;
					case 'facebook':
						$icon = 'b2-facebook-circle-fill';
						$color = '#1877F2';
						break;
					case 'twitter':
						$icon = 'b2-twitter-fill';
						$color = '#1DA1F2';
						break;
					case 'google':
						$icon = 'b2-google-fill';
						$color = '#4285F4';
						break;
					case 'github':
						$icon = 'b2-github-fill';
						$color = '#181717';
						break;
					case 'gitee':
						$icon = 'b2-gitee';
						$color = '#C71D23';
						break;
					case 'microsoft':
						$icon = 'b2-microsoft-fill';
						$color = '#F25022';
						break;
				}
				$normal[($value == 'qq' ? 'juheqq' : $value)] = array(
					'name'=>ucfirst($name),
					'icon'=>$icon,
					'color'=>$color,
					'url'=>b2_get_custom_page_url('social-login').'?type='.$value,
					'open'=>true
				);
			}
		}
	}


	return apply_filters('b2_oauth_types_arg',$normal);
}

function b2_count_widgets_in_area($area) {
    $widgets = get_option('sidebars_widgets');
    if(isset($widgets[$area])) {
        return count($widgets[$area]);
    }
    return null;
}

//距离现在几天
function b2_time_days($startdate){
    if(!$startdate) return;

    $enddate = current_time( 'mysql' );

    return floor((wp_strtotime($enddate)-wp_strtotime($startdate))/86400);
}

//获取用户数据
function b2_get_userdata($user_id,$type,$post_id = 0){
	$user_data = get_userdata($user_id);

	switch($type){
		case 'link';
			if($post_id && $user_data){
				if(get_post_type($post_id) == "infomation"){
					return '<a target="_blank" href="'.b2_get_custom_page_url('infomation-people').'?id='.$user_data->ID.'">'.trim(esc_attr($user_data->display_name)).'</a>';
				}
			}
			if($user_data){
				return '<a target="_blank" href="'.get_author_posts_url($user_data->ID).'">'.trim(esc_attr($user_data->display_name)).'</a>';
			}else{
				return '<span class="user-none">'.__('已删除','b2').'</span>';
			}
			break;
		default :
		break;
	}
}

function b2_change_excerpt( $text)
{
	if(is_string($text)){
		$pos = strpos( $text, '[');
		if ($pos === false)
		{
			return $text;
		}

		return rtrim (substr($text, 0, $pos) );
	}
    return $text;
}
add_filter('get_the_excerpt', 'b2_change_excerpt');

if(!is_admin()){
	add_filter( 'ajax_query_attachments_args', 'b2_show_current_user_attachments' );

	function b2_show_current_user_attachments( $query ) {
		$user_id = b2_get_current_user_id();
		if ( $user_id ) {
			$query['author'] = $user_id;
		}
		return $query;
	}
}

function b2_redirect_non_admin_users() {
	if ( ! current_user_can( 'manage_options' ) && '/wp-admin/admin-ajax.php' != $_SERVER['PHP_SELF'] ) {
		wp_redirect( B2_HOME_URI);
		exit;
	}
}
//add_action( 'admin_init', 'b2_redirect_non_admin_users' );

//禁止用户编辑个人资料

if(!function_exists('b2child_child_stop_access_admin')){

	function b2child_child_stop_access_admin() {
    
		//预留钩子配置可访问后台的用户能力 https://wordpress.org/support/article/roles-and-capabilities/
		$capability = apply_filters( 'b2_admin_capability', 'manage_options' );  //默认管理员
		
		if ( is_admin() && ! current_user_can( $capability ) && ! current_user_can('editor') && ! current_user_can( 'author' ) && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			
			$user_id = get_current_user_id();
			
			if( $user_id ) {
				
				$redirect = get_author_posts_url($user_id); // 获取前端个人中心页面
				
				global $pagenow;
				if( $pagenow =='profile.php' || $pagenow =='user-edit.php' ) {
					$redirect = esc_url( $redirect.'/settings' ); //获取前端个人资料设置页面
				}
				
				wp_logout(); //自动注销当前用户在WP后台的登录状态
				wp_safe_redirect( $redirect ); // 将用户重定向到正确的页面
				exit;
			}
			
		}
	}
	add_action( 'admin_menu', 'b2child_child_stop_access_admin' );
}

//删除钩子
function b2_remove_filters_with_method_name( $hook_name = '', $method_name = '', $priority = 0 ) {
	global $wp_filter;

    // Take only filters on right hook name and priority
    if ( ! isset( $wp_filter[ $hook_name ][ $priority ] ) || ! is_array( $wp_filter[ $hook_name ][ $priority ] ) ) {
        return false;
    }
    // Loop on filters registered
    foreach ( (array) $wp_filter[ $hook_name ][ $priority ] as $unique_id => $filter_array ) {
        // Test if filter is an array ! (always for class/method)
        if ( isset( $filter_array['function'] ) && is_array( $filter_array['function'] ) ) {
            // Test if object is a class and method is equal to param !
            if ( is_object( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) && $filter_array['function'][1] == $method_name ) {
                // Test for WordPress >= 4.7 WP_Hook class (https://make.wordpress.org/core/2016/09/08/wp_hook-next-generation-actions-and-filters/)
                if ( is_a( $wp_filter[ $hook_name ], 'WP_Hook' ) ) {
                    unset( $wp_filter[ $hook_name ]->callbacks[ $priority ][ $unique_id ] );
                } else {
                    unset( $wp_filter[ $hook_name ][ $priority ][ $unique_id ] );
                }
            }
        }
    }
    return false;
}

function b2_get_custom_date($date){
	$current_date = current_time('Y');
	$post_date = date_create($date);

	if($post_date >= $current_date){
		return date_format($post_date,'m'.__(' 月 ','b2').'d'.__(' 日 ','b2'));
	}else{
		return date_format($post_date,'Y'.__(' 年 ','b2').'m'.__(' 月 ','b2').'d'.__(' 日 ','b2'));
	}
}

function b2_newsflashes_date($post_date){

    $date = date_create($post_date);

    $y = date_format($date,'Y');
    $m = date_format($date,'n');
    $d = date_format($date,'d');
	$time = date_format($date,'H:i');

	return array(
		'_date'=>sprintf(__('%s月%s','b2'),$m,$d),
		'date'=>sprintf(__('%s月%s','b2'),'<span>'.$m,'</span><span>'.$d,'</span>'),
		'time'=>$time,
		'key'=>$y.$m.$d
	);

}


function b2_newsflashes_date_day($post_date){
	$current_year = current_time('Y');
    $current_month = current_time('n');
    $current_day = current_time('j');

    $date = date_create($post_date);

    $y = date_format($date,'Y');
    $m = date_format($date,'n');
    $d = date_format($date,'j');
	$time = date_format($date,'H:i');

    if($current_year - $y === 0){

		if($current_month - $m === 0){
			if($current_day - $d === 0){
				return array(
					'date'=>__('今天，','b2').b2_get_week($post_date),
					'time'=>$time,
					'key'=>$y.$m.$d
				);
			}

			if($current_day - $d == 1){
				return array(
					'date'=>__('昨天，','b2').b2_get_week($post_date),
					'time'=>$time,
					'key'=>$y.$m.$d
				);
			}

			if($current_day - $d == 2){
				return array(
					'date'=>__('前天，','b2').b2_get_week($post_date),
					'time'=>$time,
					'key'=>$y.$m.$d
				);
			}
		}

        return array(
			'date'=>sprintf(__('%s月%s日，%s','b2'),$m,$d,b2_get_week($post_date)),
			'time'=>$time,
			'key'=>$y.$m.$d
		);
	}

	return array(
		'date'=>sprintf(__('%s年%s月%s日','b2'),$y,$m,$d),
		'time'=>$time,
		'key'=>$y.$m.$d
	);
}

//根据日期获取星期
function  b2_get_week($date){
	//强制转换日期格式
	$date_str=wp_date('Y-m-d',wp_strtotime($date));

	//封装成数组
	$arr=explode("-", $date_str);

	//参数赋值
	//年
	$year=$arr[0];

	//月，输出2位整型，不够2位右对齐
	$month=sprintf('%02d',$arr[1]);

	//日，输出2位整型，不够2位右对齐
	$day=sprintf('%02d',$arr[2]);

	//时分秒默认赋值为0；
	$hour = $minute = $second = 0;

	//转换成时间戳
	$strap = mktime($hour,$minute,$second,$month,$day,$year);

	//获取数字型星期几
	$number_wk=wp_date("w",$strap);

	//自定义星期数组
	$weekArr=array(
		__('星期日','b2'),
		__('星期一','b2'),
		__('星期二','b2'),
		__('星期三','b2'),
		__('星期四','b2'),
		__('星期五','b2'),
		__('星期六','b2')
	);

	//获取数字对应的星期
	return $weekArr[$number_wk];
}

/**
* Removes the 'wpembed' TinyMCE plugin.
*
* @since 1.0.0
*
* @param array $plugins List of TinyMCE plugins.
* @return array The modified list.
*/
function disable_embeds_tiny_mce_plugin( $plugins ) {
	return array_diff( $plugins, array( 'wpembed' ) );
}

/**
* Remove all rewrite rules related to embeds.
*
* @since 1.2.0
*
* @param array $rules WordPress rewrite rules.
* @return array Rewrite rules without embeds rules.
*/
function disable_embeds_rewrites( $rules ) {
foreach ( $rules as $rule => $rewrite ) {
	if ( false !== strpos( $rewrite, 'embed=true' ) ) {
		unset( $rules[ $rule ] );
	}
}
	return $rules;
}

/**
* Remove embeds rewrite rules on plugin activation.
*
* @since 1.2.0
*/
function disable_embeds_remove_rewrite_rules() {
add_filter( 'rewrite_rules_array', 'disable_embeds_rewrites' );
	flush_rewrite_rules();
}

register_activation_hook( __FILE__, 'disable_embeds_remove_rewrite_rules' );

/**
* Flush rewrite rules on plugin deactivation.
*
* @since 1.2.0
*/
function disable_embeds_flush_rewrite_rules() {
remove_filter( 'rewrite_rules_array', 'disable_embeds_rewrites' );
	flush_rewrite_rules();
}

register_deactivation_hook( __FILE__, 'disable_embeds_flush_rewrite_rules' );

//获取图标
function b2_get_icon($name,$key = 'b2font',$class = ""){

	$arg = apply_filters('b2_icon',array(
		'name'=>$name,
		'key'=>$key,
		'class'=>$class
	));

	return '<i class="'.$arg['key'].' '.$arg['name'].' '.$arg['class'].'"></i>';
}

function b2_strReplace($search,$replace,&$array) {
    
    if (is_array($array)) {
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                b2_strReplace($search,$replace,$array[$key]);
            }
        }
    }else{
		$array = str_replace($search, $replace, $array);
	}
    return $array;
}

//用户名星号处理
function b2_str_encryption($str){
    if(preg_match("/[\x{4e00}-\x{9fa5}]+/u", $str)) {
        //按照中文字符计算长度
        $len = mb_strlen($str, 'UTF-8');
        //echo '中文';
        if($len >= 3){
            //三个字符或三个字符以上掐头取尾，中间用*代替
            $str = mb_substr($str, 0, 1, 'UTF-8') . '***' . mb_substr($str, -1, 1, 'UTF-8');
        } elseif($len == 2) {
            //两个字符
            $str = mb_substr($str, 0, 1, 'UTF-8') . '*';
        }
    } else {
        //按照英文字串计算长度
        $len = strlen($str);
        //echo 'English';
        if($len >= 3) {
            //三个字符或三个字符以上掐头取尾，中间用*代替
            $str = substr($str, 0, 1) . '***' . substr($str, -1);
        } elseif($len == 2) {
            //两个字符
            $str = substr($str, 0, 1) . '*';
        }
    }
    return $str;
}

function b2_get_img($arg){
	$arg = apply_filters('b2_get_thumb_action', $arg);
	if(is_array($arg)) return '';
	return $arg;
}

function b2_lazy($src){
	$open = b2_get_option('template_main','lazy_load');
	if((int)$open === 1){
		return 'lazy" data-src="'.$src.'" src="'.B2_LOADING_IMG.'"';
	}

	return '" src="'.$src.'"';
}

function b2_check_repo($key = 0){

	if(!$key){
		$key = b2_get_current_user_id();
	}
	$res = wp_cache_get('b2_rp_'.$key);

	if($res) return false;
	wp_cache_set('b2_rp_'.$key,1,'',2);

	return true;
}

function smartwp_remove_wp_block_library_css(){
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'wc-block-style' ); // Remove WooCommerce block CSS
}
//add_action( 'wp_enqueue_scripts', 'smartwp_remove_wp_block_library_css', 100 );

function b2_is_page_type($type){

	return apply_filters('b2_is_page', $type);
}

function b2_after_days($end){
	$end = wp_strtotime($end);
	$now = wp_strtotime('now');
	$second = $end - $now;

	return b2time2string($second);
}

function b2time2string($second){
	$day = floor($second/(3600*24));
	$second = $second%(3600*24);//除去整天之后剩余的时间
	$hour = floor($second/3600);
	$second = $second%3600;//除去整小时之后剩余的时间
	$minute = floor($second/60);
	$second = $second%60;

	return array(
		'day'=>$day,
		'hour'=>$hour,
		'minute'=>$minute,
		'second'=>$second
	);
}

if(!function_exists('wp_strtotime')){
	function wp_strtotime($str) {

		if(!$str) return 0;

		$tz_string = get_option('timezone_string');
		$tz_offset = get_option('gmt_offset', 0);

		if (!empty($tz_string)) {
			$timezone = $tz_string;

		} elseif ($tz_offset == 0) {
			$timezone = 'UTC';

		} else {
			$timezone = $tz_offset;

			if(substr($tz_offset, 0, 1) != "-" && substr($tz_offset, 0, 1) != "+" && substr($tz_offset, 0, 1) != "U") {
				$timezone = "+" . $tz_offset;
			}
		}

		$datetime = new DateTime($str, new DateTimeZone($timezone));
		return $datetime->format('U');
	}
}


//获取字符串长度
function b2getStrLen(string $str){
    $mbLen = mb_strlen($str);
    $len = strlen($str);
    $subLen = $len - $mbLen;
    if ($subLen > 0) {
        $zhCharsLen = $subLen / 2;
        $len = $zhCharsLen + ($mbLen - $zhCharsLen);
    }
    return (int)$len;
}

function b2_get_yun_video_poster($link){
	if(defined('OSS_ACCESS_ID') || defined('WPOSS_ACCESS_ID')) return $link.'?x-oss-process=video/snapshot,t_1,m_fast';
	if(defined('COS_BASENAME') || defined('WPCOS_BASENAME')){
		$ext = substr(strrchr($link, '.'), 1);

		return str_replace('.'.$ext,'_1.jpg',$link);
	}
	if(defined('WPUpYun_VERSION')) return '';
	return '';
}

function b2_get_gif_first($link){
	if(strpos($link,'.gif') !== false){
		if(defined('OSS_ACCESS_ID')){
			if(strpos($link,'?x-oss-process=image/resize,') !== false){
				return $link.'/format,jpg';
			}
			return $link;
		}
		if(defined('COS_BASENAME')) return '';
		if(defined('WPUpYun_VERSION')) return '';
	}

	return '';
}

function b2_sanitize_number($value, $field_args, $field){
	return str_replace(',','',$value);
}

function array_insert(&$array, $position, $insert_array) {
	$first_array = array_splice ($array, 0, $position);
	$array = array_merge ($first_array, $insert_array, $array);
}

//操作cookie
function b2_setcookie($key,$val,$time = 86400) {
	$secure = ( 'https' === parse_url( wp_login_url(), PHP_URL_SCHEME ) );
    return setcookie( $key, maybe_serialize($val), time() + $time, COOKIEPATH, COOKIE_DOMAIN ,$secure);
}

//获取
function b2_getcookie($key) {
	$resout = isset( $_COOKIE[$key] ) ? $_COOKIE[$key] : '';
	return maybe_unserialize(wp_unslash($resout));
}

//销毁
function b2_deletecookie($key) {
    return setcookie( $key, '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN );
}

function b2_adjustBrightness($hexcolor, $percent) {
	if ( strlen( $hexcolor ) < 6 ) {
		$hexcolor = $hexcolor[0] . $hexcolor[0] . $hexcolor[1] . $hexcolor[1] . $hexcolor[2] . $hexcolor[2];
	  }
	  $hexcolor = array_map('hexdec', str_split( str_pad( str_replace('#', '', $hexcolor), 6, '0' ), 2 ) );

	  foreach ($hexcolor as $i => $color) {
		$from = $percent < 0 ? 0 : $color;
		$to = $percent < 0 ? $color : 255;
		$pvalue = ceil( ($to - $from) * $percent );
		$hexcolor[$i] = str_pad( dechex($color + $pvalue), 2, '0', STR_PAD_LEFT);
	  }

	  return '#' . implode($hexcolor);
}

// function b2_jwt_auth_token($data, $user){
// 	if (is_array($user)){
// 		wp_die(__('密码错误','b2'));
// 	}else{
// 		return apply_filters('b2_jwt_auth_token', $data, $user);
// 	}
// }
// add_filter( 'jwt_auth_token_before_dispatch', 'b2_jwt_auth_token', 10, 2);

add_filter('jwt_auth_expire', 'b2_jwt_auth_expire');
function b2_jwt_auth_expire($issuedAt){
	return $issuedAt + (int)b2_get_option('normal_login','login_keep')*DAY_IN_SECONDS;
}

add_filter('auth_cookie_expiration', 'b2_cookie_expiration', 9999, 3);
function b2_cookie_expiration($expiration, $user_id = 0, $remember = true) {
	$allow_cookie = b2_get_option('normal_login','allow_cookie');
	if((string)$allow_cookie === '1'){
		$login_keep = (int)b2_get_option('normal_login','login_keep');
		if ($login_keep) {
			return ($login_keep * DAY_IN_SECONDS) - (12 * HOUR_IN_SECONDS);
		} else {
			return $expiration;
		}

	}

    return $expiration;
}

function b2_get_images_from_content($content,$i = 0) {
	preg_match_all('~<img[^>]*src\s?=\s?([\'"])((?:(?!\1).)*)[^>]*>~i', $content, $match,PREG_PATTERN_ORDER);

	if(is_numeric($i)){
		return isset($match[2][$i]) ? esc_url($match[2][$i]) : '';
	}elseif($i == 'all'){
		return $match[2];
	}else{
		return isset($match[2][0]) ? esc_url($match[2][0]) : '';
	}
}

// 禁用自动生成的图片尺寸
function b2_disable_image_sizes($sizes) {

    unset($sizes['thumbnail']);    // disable thumbnail size
    unset($sizes['medium']);       // disable medium size
    unset($sizes['large']);        // disable large size
    unset($sizes['medium_large']); // disable medium-large size
    unset($sizes['1536x1536']);    // disable 2x medium-large size
    unset($sizes['2048x2048']);    // disable 2x large size

    return $sizes;

}
add_action('intermediate_image_sizes_advanced', 'b2_disable_image_sizes');

// 禁用缩放尺寸
add_filter('big_image_size_threshold', '__return_false');


// if(!function_exists('cwp_set_current_timezone')){
//     function cwp_set_current_timezone($timezone){
//         if( false===stripos( $timezone,':' ) ){
//             $timezone_name = $timezone;
//         }else{
//             $timezone = strtolower($timezone);
//             $timezone = str_replace(array('utc','UTC'), '', $timezone) ;
//             $timezone = preg_replace('/[^0-9]/', '', $timezone) * 36;
//             $timezone_name = timezone_name_from_abbr(null, $timezone, true);
//             if( false===$timezone_name ){
//                 $timezone_name = timezone_name_from_abbr(null, -10* 3600, false);
//             }
//         }
//         if(function_exists('date_default_timezone_set')){
//             date_default_timezone_set($timezone_name);
//             return $timezone_name;
//         }
//         return false;
//     }
// }

// if(function_exists('date_default_timezone_set')){
//     $timezone = '+0:00';
//     cwp_set_current_timezone( $timezone );
// }

//禁用小工具区块
add_filter( 'gutenberg_use_widgets_block_editor', '__return_false' );
add_filter( 'use_widgets_block_editor', '__return_false' );

function b2p($var){
	if(current_user_can('administrator')){
		echo '<pre>';
		var_dump($var);
		echo '</pre>';
	}
}

function b2Sec2Time($time){
    if(is_numeric($time) && $time > 0){
		$value = array(
		"years" => 0, "days" => 0, "hours" => 0,
		"minutes" => 0, "seconds" => 0,
		);
		if($time >= 31556926){
		$value["years"] = floor($time/31556926);
		$time = ($time%31556926);
		}
		if($time >= 86400){
		$value["days"] = floor($time/86400);
		$time = ($time%86400);
		}
		if($time >= 3600){
		$value["hours"] = floor($time/3600);
		$time = ($time%3600);
		}
		if($time >= 60){
		$value["minutes"] = floor($time/60);
		$time = ($time%60);
		}
		$value["seconds"] = floor($time);
		//return (array) $value;

		$t = '';

		if($value["years"] >= 1){
			$t = $value["years"].__('年','b2');
			$d = $value["days"];
			if($d > 0){
				$t = $value["years"].__('年','b2').$d.__('天','b2');
			}

		}elseif($value["years"] < 1 && $value["days"] >= 1){
			$t = $value["days"].__('天','b2');
			$h = $value["hours"];
			if($h > 0){
				$t = $value["days"].__('天','b2').$h.__('小时','b2');
			}
		}elseif($value["days"] < 1 && $value["hours"] >= 1){
			$t = $value["hours"].__('小时','b2');
		}elseif($value["hours"] < 1 && $value["minutes"] >= 1){
			$t = $value["minutes"].__('分','b2');
		}else{
			$t = $value["minutes"].__('秒','b2');
		}

		if($t){
			return $t;
		}else{
			return false;
		}

     }else{
    	return false;
    }
 }

/**
 * 返回两个时间的相距时间，*年*月*日*时*分*秒
 * @param int $one_time 时间一
 * @param int $two_time 时间二
 * @param int $return_type 默认值为0，0/不为0则拼接返回，1/*秒，2/*分*秒，3/*时*分*秒/，4/*日*时*分*秒，5/*月*日*时*分*秒，6/*年*月*日*时*分*秒
 * @param array $format_array 格式化字符，例，array('年', '月', '日', '时', '分', '秒')
 * @param array $custom_return 自定义返回 默认空，直接以数组的形式规定返回哪些数据 1/秒，2/分，3/时，4/天，5/月，6/年，比如array(4,3) 返回结果为 3天5小时
 * @return String or false
 */
function b2_get_remainder_time($one_time, $two_time, $return_type=0,$format_array = [],$custom_return = []){

	if(empty($format_array)){
		$format_array = array(__('年','b2'), __('月','b2'), __('天','b2'), __('小时','b2'), __('分','b2'), __('秒','b2'));
	}

    if ($return_type < 0 || $return_type > 6) {
        return false;
    }
    if (!(is_int($one_time) && is_int($two_time))) {
        return false;
    }
    $remainder_seconds = abs($one_time - $two_time);
    //年
    $years = 0;
    if (($return_type == 0 || $return_type == 6) && $remainder_seconds - 31536000 > 0) {
        $years = floor($remainder_seconds / (31536000));
    }
    //月
    $monthes = 0;
    if (($return_type == 0 || $return_type >= 5) && $remainder_seconds - $years * 31536000 - 2592000 > 0) {
        $monthes = floor(($remainder_seconds - $years * 31536000) / (2592000));
    }
    //日
    $days = 0;
    if (($return_type == 0 || $return_type >= 4) && $remainder_seconds - $years * 31536000 - $monthes * 2592000 - 86400 > 0) {
        $days = floor(($remainder_seconds - $years * 31536000 - $monthes * 2592000) / (86400));
    }
    //时
    $hours = 0;
    if (($return_type == 0 || $return_type >= 3) && $remainder_seconds - $years * 31536000 - $monthes * 2592000 - $days * 86400 - 3600 > 0) {
        $hours = floor(($remainder_seconds - $years * 31536000 - $monthes * 2592000 - $days * 86400) / 3600);
    }
    //分
    $minutes = 0;
    if (($return_type == 0 || $return_type >= 2) && $remainder_seconds - $years * 31536000 - $monthes * 2592000 - $days * 86400 - $hours * 3600 - 60 > 0) {
        $minutes = floor(($remainder_seconds - $years * 31536000 - $monthes * 2592000 - $days * 86400 - $hours * 3600) / 60);
    }
    //秒
    $seconds = $remainder_seconds - $years * 31536000 - $monthes * 2592000 - $days * 86400 - $hours * 3600 - $minutes * 60;

	if(!empty($custom_return)){
		$str = '';
		foreach ($custom_return as $v) {
			if($v == 6){
				if($years != 0){
					$str .= $years . $format_array[0];
				}
			}

			if($v == 5){
				if($monthes != 0){
					$str .= $monthes . $format_array[1];
				}
			}

			if($v == 4){
				if($days != 0){
					$str .= $days . $format_array[2];
				}
			}

			if($v == 3){
				if($hours != 0){
					$str .= $hours . $format_array[3];
				}
			}

			if($v == 2){
				if($minutes != 0){
					$str .= $minutes . $format_array[4];
				}
			}

			if($v == 1){
				if($seconds != 0){
					$str .= $seconds . $format_array[5];
				}
			}
		}

		return $str;
	}

    $return = false;
    switch ($return_type) {
        case 0:
            if ($years > 0) {
                $return = $years . $format_array[0] . $monthes . $format_array[1] . $days . $format_array[2] . $hours . $format_array[3] . $minutes . $format_array[4] . $seconds . $format_array[5];
            } else if ($monthes > 0) {
                $return = $monthes . $format_array[1] . $days . $format_array[2] . $hours . $format_array[3] . $minutes . $format_array[4] . $seconds . $format_array[5];
            } else if ($days > 0) {
                $return = $days . $format_array[2] . $hours . $format_array[3] . $minutes . $format_array[4] . $seconds . $format_array[5];
            } else if ($hours > 0) {
                $return = $hours . $format_array[3] . $minutes . $format_array[4] . $seconds . $format_array[5];
            } else if ($minutes > 0) {
                $return = $minutes . $format_array[4] . $seconds . $format_array[5];
            } else {
                $return = $seconds . $format_array[5];
            }
            break;
        case 1:
            $return = $seconds . $format_array[5];
            break;
        case 2:
            $return = $minutes . $format_array[4] . $seconds . $format_array[5];
            break;
        case 3:
            $return = $hours . $format_array[3] . $minutes . $format_array[4] . $seconds . $format_array[5];
            break;
        case 4:
            $return = $days . $format_array[2] . $hours . $format_array[3] . $minutes . $format_array[4] . $seconds . $format_array[5];
            break;
        case 5:
            $return = $monthes . $format_array[1] . $days . $format_array[2] . $hours . $format_array[3] . $minutes . $format_array[4] . $seconds . $format_array[5];
            break;
        case 6:
            $return = $years . $format_array[0] . $monthes . $format_array[1] . $days . $format_array[2] . $hours . $format_array[3] . $minutes . $format_array[4] . $seconds . $format_array[5];
            break;
        default:
            $return = false;
    }
    return $return;
}

/**
 * 某个日期后的几天，返回时间戳
 *
 * @param string $start 类似 2012-03-15 格式的时间
 * @param int $end 天数
 *
 * @return void
 * @author Li Ruchun <lemolee@163.com>
 * @version 3.5.6
 * @since 2021
 */
function b2_date_after($start,$end){
	$date=date_create($start);
	date_add($date,date_interval_create_from_date_string("$end days"));

	// return date_format($date,"Y-m-d H:i:s");
	return wp_strtotime(date_format($date,"Y-m-d H:i:s"));
}

function  b2isImg($fileName){
    $file     = fopen($fileName, "rb");
    $bin      = fread($file, 2);  // 只读2字节

    fclose($file);
    $strInfo  = @unpack("C2chars", $bin);
    $typeCode = intval($strInfo['chars1'].$strInfo['chars2']);
    $fileType = '';

    if($typeCode == 255216 /*jpg*/ || $typeCode == 7173 /*gif*/ || $typeCode == 13780 /*png*/)
    {
        return $typeCode;
    }
    else
    {
        // echo '"仅允许上传jpg/jpeg/gif/png格式的图片！';
        return false;
    }
}

function b2_get_excerpt($post_id,$length = 200){

	if(isset($GLOBALS['b2_get_excerpt_'.$post_id.'_'.$length])) return $GLOBALS['b2_get_excerpt_'.$post_id.'_'.$length];
	//b2_remove_filters_with_method_name('the_content','post_download',10,1);

	$exp = get_post_field('post_excerpt',$post_id);

	if(!$exp){
		$exp = get_post_field('post_content',$post_id);
	}

	$exp = \B2\Modules\Templates\Modules\Sliders::get_des(0,$length,$exp);

	// update_post_meta($post_id,'b2_post_exp',$exp);

	$GLOBALS['b2_get_excerpt_'.$post_id.'_'.$length] = $exp;
	
	return $exp;
}

function b2_remove_kh($str,$sanitize = false){
	$str = wp_unslash($str);
	if($sanitize){
		$str = sanitize_text_field($str);
	}else{
		$str = wp_strip_all_tags($str);
	}

	$str = apply_filters('b2_remove_kh', str_replace(array('{{','}}'),'',$str));
	return $str;
}

function b2_is_agent($type){
	if((strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')) && $type == 'ios'){
		 return true;
	}else if(strpos($_SERVER['HTTP_USER_AGENT'], 'Android') && $type == 'android'){
		return true;
	}

	return false;
}

function b2_is_mp(){
	if (strpos($_SERVER['HTTP_USER_AGENT'], 'miniprogram') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'baiduboxapp') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'toutiaomicroapp') !== false) {
		return true;
	} else {
		return false;
	}
}

// edited by fuzqing
function b2_is_enable_related_pay_money($id)
{
    $enable_related_pay_money = get_post_meta($id,'b2_enable_related_pay_money',true);
    $info = [
        'related'=>false,
    ];
    if ($enable_related_pay_money === 'off') {
        return $info;
    }
    $b2_related_field = get_post_meta($id,'b2_related_field',true);
    if (!$b2_related_field) {
        return $info;
    }
    $pay_group = get_post_meta($id,'b2_pay_custom_group',true);
    if (empty($pay_group) || count($pay_group) === 0) {
        return $info;
    }
    $found_key = array_search($b2_related_field, array_column($pay_group, 'key'));
    if ($found_key === false) {
        return $info;
    }
    if (!in_array($pay_group[$found_key]['type'],['radio','select'])) {
        return $info;
    }
    $price = get_post_meta($id,'b2_single_pay_money',true);
    $price = trim($price, " \t\n\r\0\x0B\xC2\xA0");
    if (!$price) {
        return $info;
    }
    $price = explode(PHP_EOL, $price );
    $price = array_map(function ($p){
        return trim($p);
    },$price);
    $related_field = $pay_group[$found_key];
    $str = trim($related_field['value'], " \t\n\r\0\x0B\xC2\xA0");
    $arr = [];
    $b2_related_field_value = '';
    if(!empty($str)){
        $str = explode(PHP_EOL, $str );
        if (count($str) !== count($price)) {
            return $info;
        }
        foreach($str as $_k=>$_v){
            $__k = explode('=',$_v);
            if (!isset($__k[0],$__k[1])) {
                return $info;
            }
            if ($_k ===0){
                $b2_related_field_value = $__k[0];
            }
            $arr[$__k[0]] = $price[$_k];
        }
    }else{
        return $info;
    }
    return [
        'related'=>true,
        'related_field'=>$b2_related_field,
        'related_field_value'=>$b2_related_field_value,
        'related_prices'=>$arr,
    ];
}
// edited by fuzqing
function b2_get_cpay_active_time($id)
{
    $active_date_type = get_post_meta($id,'b2_active_date_type',true);
    if ($active_date_type === 'date_area') {
        $start_t = get_post_meta($id,'b2_start_t',true);
        $end_t = get_post_meta($id,'b2_end_t',true);
        if ($end_t <= time()) {
            $info = [
                'tips'=>get_post_meta($id,'b2_end_text',true),
                'active'=>false,
            ];
        } else if($start_t > time()) {
            $text = get_post_meta($id,'b2_not_start_text',true);
            $text = str_replace('{{start_time}}',date("Y-m-d H:i:s",$start_t),$text);
            $info = [
                'tips'=>$text,
                'active'=>false,
            ];
        } else {
            $info = [
                'active'=>true,
            ];
        }
    } else {
        $info = [
            'active'=>true,
        ];
    }
    return $info;
}

function b2_delete_index_cache(){
	$cache_key = md5(B2_HOME_URI);
	$index = get_option('b2_template_index');

	if(isset($index['index_group'])){
		for ($i=0; $i < count($index['index_group']); $i++) { 
			delete_transient($cache_key.'_b2_index_module_'.$i);
		}
	}

}

function b2_get_current_user_id(){

	// return get_current_user_id();

	global $b2_current_user_id;

	if($b2_current_user_id !== NULL) return $b2_current_user_id;

	$b2_current_user_id = (int)apply_filters('b2_get_current_user_id',false);

	return $b2_current_user_id;
}


function b2_hex2rgba( $color, $opacity = false ) {

    $defaultColor = 'rgb(0,0,0)';

    // Return default color if no color provided
    if ( empty( $color ) ) {
        return $defaultColor;
    }

    // Ignore "#" if provided
    if ( $color[0] == '#' ) {
        $color = substr( $color, 1 );
    }

    // Check if color has 6 or 3 characters, get values
    if ( strlen($color) == 6 ) {
        $hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
    } elseif ( strlen( $color ) == 3 ) {
        $hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
    } else {
        return $defaultColor;
    }

    // Convert hex values to rgb values
    $rgb =  array_map( 'hexdec', $hex );

    // Check if opacity is set(rgba or rgb)
    if ( $opacity ) {
        if( abs( $opacity ) > 1 ) {
            $opacity = 1.0;
        }
        $output = 'rgba(' . implode( ",", $rgb ) . ',' . $opacity . ')';
    } else {
        $output = 'rgb(' . implode( ",", $rgb ) . ')';
    }

    // Return rgb(a) color string
    return $output;

}


function b2_recursive_sanitize_text_field($array) {
    foreach ( $array as $key => &$value ) {
        if ( is_array( $value ) ) {
            $value = b2_recursive_sanitize_text_field($value);
        }
        else {
            $value = sanitize_text_field( str_replace(array('{{','}}'),'',$value) );
        }
    }

    return $array;
}

/*https://blog.wpjam.com/m/limit-login-attempts/
限制IP登录错误次数6次*/
// add_action('wp_login_failed', function($username){
// 	$key	= $_SERVER['REMOTE_ADDR'];
// 	$times	= wp_cache_get($key, 'wpjam_login_limit');
// 	$times	= $times ?: 0;
// 	wp_cache_set($key, $times+1, 'wpjam_login_limit', MINUTE_IN_SECONDS*15);
// });

// add_filter('authenticate', function($user, $username, $password){
// 	$key	= $_SERVER['REMOTE_ADDR'];
// 	$times	= wp_cache_get($key, 'wpjam_login_limit');
// 	$times	= $times ?: 0;
// 	if($times > 5){
// 		remove_filter('authenticate', 'wp_authenticate_username_password', 20, 3);
// 		remove_filter('authenticate', 'wp_authenticate_email_password', 20, 3);
// 		return new WP_Error('too_many_retries', '你多次登录失败，请15分钟后重试！'); 
// 	}
// 	return $user;
// }, 1, 3 );

//临时设置全站的下载权限
// add_filter( 'b2_get_download_rights','set_new_download_role', 10,2);
// function set_new_download_role($role,$post_id){
// 	return 'all|free';
// }

//后台禁用作者选择
// function b2_remove_page_fields() {
// 	remove_meta_box('authordiv', 'post', 'normal');
// }
// add_action( 'admin_menu' , 'b2_remove_page_fields' );

// add_action(  'transition_post_status','publish_circle_gift', 998, 3 );
// function publish_circle_gift($new_status, $old_status, $post){
// 	$user_id = $post->post_author;
//     $post_type = $post->post_type;
// 	$post_id = $post->ID;

// 	$has_gift = get_post_meta($post_id,'b2_circle_has_gift',true);

// 	if(!$has_gift && $new_status === 'publish'){
// 		$key = get_post_meta($post_id,'single_circle_gift_key',true);
// 		$value = get_post_meta($post_id,'single_circle_gift_value',true);
// 		$notice = get_post_meta($post_id,'single_circle_gift_notice',true);

// 		if($value && $key == 'money'){
// 			$money = B2\Modules\Common\User::money_change($user_id,(float)$value);
// 			B2\Modules\Common\Message::add_message(array(
// 				'user_id'=>$user_id,
// 				'msg_type'=>92,
// 				'msg_read'=>1,
// 				'msg_date'=>current_time('mysql'),
// 				'msg_users'=>$user_id,
// 				'msg_credit'=>(float)$value,
// 				'msg_credit_total'=>get_user_meta($user_id,'zrz_rmb',true),
// 				'msg_key'=>$post_id,
// 				'msg_value'=>''
// 			));

// 			update_post_meta($post_id,'b2_circle_has_gift',1);
// 			return;
// 		}

// 		if($notice && $key == 'gift'){
// 			B2\Modules\Common\Message::add_message(array(
// 				'user_id'=>$user_id,
// 				'msg_type'=>93,
// 				'msg_read'=>0,
// 				'msg_date'=>current_time('mysql'),
// 				'msg_users'=>$user_id,
// 				'msg_credit'=>0,
// 				'msg_credit_total'=>0,
// 				'msg_key'=>$post_id,
// 				'msg_value'=>$notice
// 			));
// 			update_post_meta($post_id,'b2_circle_has_gift',1);
// 		}
// 	}
// }


// add_filter( 'b2_is_user_in_circle_return', 'my_user_in_circle_1998',2,3); 
// function my_user_in_circle_1998($status,$user_id,$circle_id){ 

// 	if($circle_id == 123 || $circle_id == 2){ return true; } 

// 	return $status;
// }

// add_action('b2_post_views','b2_view_credit');
// function b2_view_credit($data){

// 	if(!$data['user_id']) return;

// 	if(!wp_using_ext_object_cache()) return;

// 	$count = wp_cache_get('view_'.$data['user_id'], 'my_post_view');

// 	if($count > 3) return;

// 	\B2\Modules\Common\Gold::update_data([
// 		'date'=>current_time('mysql'),
// 		'to'=>$data['user_id'],
// 		'gold_type'=>0,
// 		'no'=>1,
// 		'post_id'=>$data['post_id'],
// 		'msg'=>__('阅读文章积分奖励：${post_id}','b2'),
// 		'type'=>'my_read_credit',
// 		'type_text'=>__('积分奖励','b2'),
// 		'read'=>0
// 	]);

//     wp_cache_set('view_'.$data['user_id'],$count+1,'my_post_view',DAY_IN_SECONDS);
// }