<?php
use B2\Modules\Templates\Modules\Links;
use B2\Modules\Common\Links as LinkAction;

$term = get_queried_object();
$open = b2_get_option('links_main','link_open');

if(!isset($term->term_id) || !$open){
    wp_redirect(B2_HOME_URI.'/404');
    exit;
}
$term_id = $term->term_id;

$paged = get_query_var('paged');
$paged = $paged ? $paged : 1;

get_header();

$opt = LinkAction::get_default_settings($term_id);
$opt['post_paged'] = $paged;
$html = new Links();
$data = $html->init($opt,0);

?>

<div class="b2-single-content wrapper">
    <div id="links" class="content-area links wrapper">
		<main id="main" class="site-main">
            <div id="primary-home" class="content-area">
                <?php if(!$data && !$opt['link_show_children']){ ?>
                    <div class="box" style="width:100%"><?php echo B2_EMPTY; ?></div>
                <?php }else{ 
                    echo '<div class="link-breadcrumb b2-hover mg-b">'.LinkAction::link_breadcrumb().'</div>'.$data;

                    $childrens = LinkAction::get_children_cat($term_id,-1);

                    if(!empty($childrens) && $opt['link_show_children']){
                        foreach ($childrens as $k => $v) {
                            $html = new Links();

                            $arg = LinkAction::get_default_settings($v->term_id);

                            echo $html->init($arg,$k+1);
                        }
                    }

                    } ?>
            </div>
        </main>
    </div>
</div>
<?php
get_footer();