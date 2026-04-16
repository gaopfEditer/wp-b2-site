<?php
/**侧边栏
 */

// if(!B2\Modules\Templates\Main::show_sidebar()) return;
$sidebars_widgets = wp_get_sidebars_widgets();
// echo '<pre>';
// var_dump($sidebars_widgets['sidebar-3']);
// $arg = array(
//     'post_single'=>'sidebar-3',
//     'shop_single'=>'sidebar-6',
//     'shop_archive'=>'sidebar-7',
//     'shop_home'=>'sidebar-5',
//     'circle_single'=>'sidebar-12',
//     'circle_archive'=>'sidebar-10',
//     'newsflashes_single'=>'sidebar-8',
//     'newsflashes_archive'=>'sidebar-11',
//     'page_single'=>'sidebar-4',
//     'default'=>'sidebar-1',
// );



// ob_start();
// dynamic_sidebar( 'sidebar-8' );
// $output = ob_get_contents();

// ob_end_clean();

$page = is_page();

$is_circle = apply_filters('b2_is_page', 'circle');
$is_stream = apply_filters('b2_is_page', 'stream');
$is_links = apply_filters('b2_is_page', 'links');
$is_newsflashes = is_singular('newsflashes');
$is_infomation = apply_filters('b2_is_page', 'infomation');
$is_single_infomation = is_singular('infomation');

$is_ask = apply_filters('b2_is_page', 'ask');

$is_cpay = apply_filters('b2_is_page', 'cpay');

$is_archive_newsflashes = is_post_type_archive('newsflashes') || is_tax('newsflashes_tags');

$is_archive_ask = is_post_type_archive('ask') || is_tax('ask_cat');

$is_shop = is_singular('shop');
$is_post = is_singular('post');
$is_circle_single = is_singular('circle');
if($is_circle_single && empty($sidebars_widgets['sidebar-12'])) return;
if($is_circle && empty($sidebars_widgets['sidebar-10']) && !$is_circle_single){
    return;
}

if($is_post){
    $style = B2\Modules\Templates\Single::get_single_post_settings(get_the_id(),'single_post_style');
    $style = $style ? $style : 'post-style-1';

    if($style === 'post-style-2') return;

    $show_widget = B2\Modules\Templates\Single::get_single_post_settings(get_the_id(),'single_post_sidebar_show');
    if((int)$show_widget == 0) return;
}

if($is_post && empty($sidebars_widgets['sidebar-3'])) return;

if($is_shop && empty($sidebars_widgets['sidebar-6'])) return;

if($is_newsflashes && empty($sidebars_widgets['sidebar-8'])) return;

if($is_archive_newsflashes && empty($sidebars_widgets['sidebar-11'])) return;

if($page && empty($sidebars_widgets['sidebar-4']) && !is_front_page() && !$is_stream) return;



if($is_infomation && $is_single_infomation && empty($sidebars_widgets['sidebar-15'])) return;
if($is_infomation && !$is_single_infomation && empty($sidebars_widgets['sidebar-16'])) return;
if($is_cpay && empty($sidebars_widgets['sidebar-17'])) return;

//分类
$tax = get_queried_object();
$taxonomy = isset($tax->taxonomy) ? $tax->taxonomy : '';

if($taxonomy === 'shoptype' && empty($sidebars_widgets['sidebar-7'])) return;

if(is_post_type_archive('shop') && empty($sidebars_widgets['sidebar-5'])) return;

?>
<aside id="secondary" class="widget-area">
    <div class="sidebar">
        <div class="sidebar-innter widget-ffixed">
            <?php
                if($is_cpay){
                    dynamic_sidebar( 'sidebar-17' );
                }elseif($is_infomation && $is_single_infomation){
                    dynamic_sidebar( 'sidebar-15' );
                }elseif($is_infomation && !$is_single_infomation){
                    dynamic_sidebar( 'sidebar-16' );
                }elseif($is_links){
                    dynamic_sidebar( 'sidebar-14' );
                }elseif($is_newsflashes){
                    dynamic_sidebar( 'sidebar-8' );
                }elseif($is_circle_single){
                    dynamic_sidebar( 'sidebar-12' );
                }elseif($is_circle){
                    dynamic_sidebar( 'sidebar-10' );
                }elseif($is_archive_ask){
                    dynamic_sidebar( 'sidebar-18' );
                }elseif($is_ask){
                    dynamic_sidebar( 'sidebar-19' );
                }elseif($is_shop){
                    dynamic_sidebar( 'sidebar-6' );
                }elseif($is_post){
                    dynamic_sidebar( 'sidebar-3' );
                }elseif($taxonomy === 'shoptype'){
                    dynamic_sidebar( 'sidebar-7' );
                }elseif(is_post_type_archive('shop') || apply_filters('b2_is_page', 'shop')){
                    dynamic_sidebar( 'sidebar-5' );
                }elseif($is_stream){
                    dynamic_sidebar( 'sidebar-13' );
                }elseif($is_archive_newsflashes){
                    dynamic_sidebar( 'sidebar-11' );
                }elseif($page){
                    dynamic_sidebar( 'sidebar-4' );
                }else{
                    dynamic_sidebar( 'sidebar-1' );
                }
            ?>
        </div>
    </div>
</aside>