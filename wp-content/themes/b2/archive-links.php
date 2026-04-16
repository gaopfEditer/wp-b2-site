<?php

use B2\Modules\Templates\Modules\Links;
use B2\Modules\Common\Links as LinksFn;

$open = b2_get_option('links_main','link_open');
if(!$open){
    wp_safe_redirect(B2_HOME_URI.'/404');
    exit;
}
/**
 * 网址导航
 */
get_header();

$opts = get_option('b2_links_main');

$data = [];

if(!empty($opts['link_cats'])){

    $data = [];

    foreach ($opts['link_cats'] as $k => $v) {
        $_opts = $opts;
        $_opts['link_cat'] = $v;
        $_opts['title'] = '';
        unset($_opts['link_cats']);
        $data[] = $_opts;
    }

    // var_dump($data);

    $title = b2_get_option('links_main','link_title');
    $total = LinksFn::link_total();
}

?>

<div class="b2-single-content wrapper">
    <div id="links" class="content-area links wrapper links-home">
		<main id="main" class="site-main">
            <div id="primary-home" class="content-area">
    
                <?php if((isset($total['link_count']) && $total['link_count'] == 0) || count($data) == 0){ ?>
                    <div class="box" style="width:100%"><?php echo B2_EMPTY; ?></div>
                <?php }else{ 
                    echo '<div class="home-links-content"><div class="b2-tab-links">
                    '.($title ? '<h1>'.$title.'</h1>' : '').'
                    <div class="link-total">
                        '.sprintf(__('共收录 %s 个分类，%s 个网址'),$total['term_count'],$total['link_count']).'
                    </div>
                    <div class="b2-tab-link-in"></div><div class="link-join"><a href="'.b2_get_custom_page_url('link-register').'" class="button empty" target="_blank">'.b2_get_icon('b2-user-location-line').'申请入驻</a></div>
                    </div><div class="home-links-right">';
                    foreach ($data as $key => $value) {
                        $html = new Links();
                        echo $html->init($value,$key);
                    }
                    echo '</div></div>';
                    } ?>
            
            </div>
        </main>
    </div>
</div>
<?php
get_footer();