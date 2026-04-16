<?php
    $key = get_search_query();
    $settings = array(
        'post_type'=>'post-1',
        'post_order'=>'new',
        'post_row_count'=>4,
        'post_count'=>20,
        'post_thumb_ratio'=>'1/0.618',
        'post_open_type'=>1,
        'post_meta'=>array('user','date','views','like','cats','des'),
        'search'=>$key,
        'search_type'=>isset($_GET['type']) ? $_GET['type'] : 'all'
    );

    global $wp;
    $url = B2_HOME_URI.'/'.$wp->request;
    $request = http_build_query($_REQUEST);
    $request = $request ? '?'.$request : '';

    if(isset($wp_query->query['year']) && $wp_query->query['year']){
        $settings['year'] = $wp_query->query['year'];
    }

    if(isset($wp_query->query['monthnum']) && $wp_query->query['monthnum']){
        $settings['month'] = $wp_query->query['monthnum'];
    }

    $paged = get_query_var('paged') ? get_query_var('paged') : 1;

    $settings['post_paged'] = isset($settings['post_paged']) ? $settings['post_paged'] : $paged;

    //取消隐藏加载更多按钮
    $settings['post_load_more'] = 0;

    //隐藏侧边栏
    $settings['show_sidebar'] = 0;
    $settings['width'] = b2_get_page_width($settings['show_sidebar']);
    $settings['no_rows'] = false;
    //获取文章列表数据
    $modules =  new B2\Modules\Templates\Modules\Posts;
    $data = $modules->init($settings,1,true);

    //排序css
    $fr = 0;
    for($_i=1; $_i<=$settings['post_row_count']; $_i++){
        if(($settings['post_type'] == 'post-4' && $_i == 1) || $settings['post_type'] == 'post-3'){
            $fr = 1;
        }else{
            $fr ++;
        }
    }

    $r = ((floor((1/$fr)*10000)/10000)*100).'%';

    $thumb = '';

    $pagenav_type = 'ajax_pagenav';

    $settings = apply_filters('b2_search_page_settings', $settings);

    //设置项传入JS
    wp_localize_script( 'vue', 'b2_cat',array(
        'opt'=>$settings
    ));


?>
<style>
    .post-item-1 ul.b2_gap > li{
        width:<?php echo $r; ?>
    }
</style>

<?php do_action('b2_normal_archive_before'); ?>
<div class="archive-row">
    <div class="<?php echo $settings['post_type']; ?> post-list post-item-1" id="post-list">
        <?php if($data['data']){ ?>
            <div class="hidden-line">
                <ul class="b2_gap <?php if($settings['post_type'] == 'post-2') echo 'grid'; ?>"><?php echo $data['data']; ?></ul>
            </div>
        <?php }else{
            echo '<div class="box">'.B2_EMPTY.'</div>';
        } ?>
    </div>
</div>
<?php do_action('b2_normal_archive_after'); ?>
<?php if($data['pages'] > 1){ ?>
<div class="b2-pagenav post-nav box b2-radius mg-t" data-max="<?php echo $data['pages']; ?>">
    <?php echo b2_pagenav(array('pages'=>$data['pages'],'paged'=>$paged)); ?>
</div>
<?php } ?>