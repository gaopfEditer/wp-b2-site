<?php
/**
 *  搜索页面
 */
get_header();

$post_type = b2_get_search_type();
unset($post_type['cpay']);
$opt_type = b2_get_option('template_top','search_menu');

$_post_type = [];

foreach ($opt_type as $k) {
    if(isset($post_type[$k])){
        $_post_type[$k] = $post_type[$k];
    }
    
}

$type = isset($_GET['type']) && $_GET['type'] ? $_GET['type'] : 'post';
$key = get_search_query();

global $wp;
$url = B2_HOME_URI.'/'.$wp->request;
$request = http_build_query($_REQUEST);
$request = $request ? '?'.$request : '';
$request = remove_query_arg('s',$request);
$url = preg_replace('#page/([^/]*)$#','', $url);

?>
<div class="tax-search box wrapper b2-radius mg-b">
    <div class="search-types">

        <?php 
            $type_url = http_build_query($_REQUEST);
            $type_url = $type_url ? '?'.$type_url : ''; 
            foreach ($_post_type as $k => $v) {
                echo '<a class="'.($k === $type ? 'picked' : '').'" href="'.add_query_arg('type',$k,$url.$type_url).'">'.$v.'</a>';
            }
        ?>
    </div>
    <form method="get" action="<?php echo $url.$request; ?>" autocomplete="off">
        <input type="text" name="s" class="b2-radius" placeholder="<?php echo sprintf(__('在「%s」中搜索','b2'),$_post_type[$type]);?>" value="<?php echo $key; ?>">
        <input type="hidden" name="type" value="<?php echo $type; ?>">
        <button class="text"><?php echo b2_get_icon('b2-search-line'); ?></button>
    </form>
</div>
<div class="b2-single-content wrapper single-sidebar-hidden">
    <div id="primary-home" class="wrapper">
        <div class="search-page-title mg-b box b2-radius">
            <h2><?php echo $post_type[$type]; ?></h2>
            <p><?php echo sprintf(__('关键词 [%s] 的搜索结果：','b2'),'<span class="red">'.$key.'</span>'); ?></p>
        </div>
        <?php get_template_part( 'Search/'.$type ); ?>
    </div>
</div>
<?php get_footer(); ?>