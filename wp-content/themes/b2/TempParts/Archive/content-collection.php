<?php
use B2\Modules\Templates\Archive;

/**
 * 分类存档页面
 */
    
    $paged = get_query_var('paged') ? get_query_var('paged') : 1;

    //获取分类数据
    $term = get_queried_object();

    if(!isset($term->term_id)){
        wp_safe_redirect(B2_HOME_URI.'/404');
        exit;
    }

    //获取设置项
    $settings = get_term_meta($term->term_id,'b2_group',true);
    $settings = is_array($settings) ? $settings[0] : array();

    //如果设置项为空，则使用默认设置
    if(!isset($settings['post_row_count'])){
        $settings = array(
            'post_order'=>'new',
            'post_open_type'=>0,
        );
    }

    $settings['post_type'] = 'post-3';
    $settings['post_meta'] = array('user','date','views','like','cats','des');
    $settings['post_row_count'] = 1;
    $settings['post_count'] = 12;
    $settings['post_thumb_ratio'] = '1/0.84';
    $settings['post_thumb_ratio_mobile'] = '1/0.84';
    $settings['post_thumb_ratio_pc'] = '1/0.84';

    $settings['post_paged'] = isset($settings['post_paged']) ? $settings['post_paged'] : $paged;

    //隐藏加载更多按钮
    $settings['post_load_more'] = 0;

    //当前专题slug加入设置项中
    $settings['collection_slug'] = array($term->term_id);

    //分类筛选
    if(isset($_GET['post_cat']) && !empty($_GET['post_cat'])){
        $settings['post_cat'] = array($_GET['post_cat']);
    }

    //标签筛选
    if(isset($_GET['post_tag']) && !empty($_GET['post_tag'])){
        $settings['post_tag'] = array($_GET['post_tag']);
    }

     //搜索
     if(isset($_POST['archiveSearch']) && !empty($_POST['archiveSearch']) ){

        $settings['search'] = $_POST['archiveSearch'];
    }

    if(!empty($_GET)){
        $tags = array();
        foreach ($_GET as $k_k => $v_k) {
            if(strpos($k_k,'tags') !== false){
                $tags[] = $v_k;
            }
        }

        if(!empty($tags)){
            $settings['tags'] = $tags;
        }
    }

    //排序筛选
    if(isset($_GET['post_order']) && !empty($_GET['post_order'])){
        $settings['post_order'] = $_GET['post_order'];
    }

    //自定义字段筛选
    $filters = get_term_meta($term->term_id,'b2_filter',true);
    if(isset($filters[0]['meta'])){

        $metas = array();
        $filters_arg = Archive::meta_str_to_array($filters[0]['meta']);

        if($filters_arg){

            foreach($filters_arg as $k=>$v){

                if(isset($_GET[$v['meta_key']]) && !empty($_GET[$v['meta_key']])){
                    $metas[$v['meta_key']] = $_GET[$v['meta_key']];
                }

            }

        }

        $settings['metas'] = $metas;
    }

    //隐藏侧边栏
    $settings['show_sidebar'] = get_term_meta($term->term_id,'b2_show_sidebar',true);
    $settings['width'] = b2_get_page_width($settings['show_sidebar']);
    $settings['no_rows'] = false;
    //获取文章列表数据
    $modules =  new B2\Modules\Templates\Modules\Posts;
    $data = $modules->init($settings,1,true);
    $size = $modules::get_thumb_size($settings,$settings['post_row_count']);
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

    $thumb = get_term_meta($term->term_id,'b2_tax_img',true);

    $pagenav_type = get_term_meta($term->term_id,'b2_tax_pagenav_type',true);
    $pagenav_type = $pagenav_type ? $pagenav_type : 'ajax_pagenav';

    //设置项传入JS
    wp_localize_script( 'vue', 'b2_cat',array(
        'opt'=>$settings
    ));

    $index = get_term_meta($term->term_id, 'b2_tax_index', true);
?>
    <style>
        .post-item-1 ul.b2_gap > li{
            width:<?php echo $r; ?>
        }
    </style>
    <?php if($settings['post_type'] == 'post-3'){ 
    $r = round(1/$settings['post_row_count'],6)*100;
    echo '
    <style>
            .post-module-thumb{
                width:'.$size['w'].'px;min-width:'.$size['w'].'px;
            }
            @media screen and (max-width:720px){
                .post-module-thumb{
                    width:'.$size['m_w'].'px;min-width:'.$size['m_w'].'px;
                }
            }
            '.($settings['post_row_count'] > 1 ? 
            '
            @media screen and (min-width:720px){
                ul.b2_gap > li + li{margin:0}
                ul.b2_gap > li{width:'.$r.'%}
                ul.b2_gap > li:nth-last-child(2),ul.b2_gap > li:nth-last-child(1){
                    margin-bottom:0!important
                }
                .post-info h2{
                    font-size: 16px;
                    font-weight: 400;
                }
                .post-excerpt{
                    -webkit-line-clamp: 1;
                }
            }
            ' : ''
            ).'
        </style>';
} 

if($term && isset($term->term_id)){
    $thumb = get_term_meta($term->term_id,'b2_tax_img',true);
}

$thumb = b2_get_thumb(array('thumb'=>$thumb,'height'=>240,'width'=>860));

$desc = get_the_archive_description();
$title = get_the_archive_title();
$color = get_term_meta($term->term_id, 'b2_tax_color', true);
$collection_name = b2_get_option('normal_custom','custom_collection_name');
?>

<div class="box b2-radius">
    <div class="collection-list-top">
        <div class="collection-list-top-bg" style="background: linear-gradient(0.12turn,<?php echo $color; ?>,transparent);"></div>
        <img src="<?php echo $thumb; ?>">
        <div class="collection-list-top-info">
            <h1><?php echo $title; ?></h1>
            <?php echo $desc; ?>
            <div class="read-more mg-t">
                <a href="<?php echo b2_get_custom_page_url('collection'); ?>" target="_blank"><?php echo sprintf(__('往期%s','b2'),$collection_name).b2_get_icon('b2-arrow-right-s-line'); ?></a>
            </div>
        </div>
    </div>

    <div class="archive-row">

        <div class="collection-header b2-padding">
            <div class="">
                <?php echo sprintf(__('一共%s篇文章','b2'),'<b class="b2-color">'.$data['count'].'</b>');?>
            </div>
            <div class="collection-number b2-radius">
                <span><?php echo sprintf(__('%s：第%s%s%s期','b2'),$collection_name,'<b>',$index,'</b>'); ?></span>
            </div>
        </div>
        <div class="<?php echo $settings['post_type']; ?> post-list post-item-1 hidden-line" id="post-list">
            <?php if($data['data']){ ?>
                <ul class="b2_gap <?php if($settings['post_type'] == 'post-2') echo 'grid'; ?>"><?php echo $data['data']; ?></ul>
            <?php }else{
                echo str_replace('empty-page','empty-page box',B2_EMPTY);
            } ?>
        </div>
        
    </div>

    <div class="b2-pagenav post-nav <?php echo $data['pages'] <= 1 ? 'b2-hidden-always' : ''; ?>" data-max="<?php echo $data['pages']; ?>">
        <?php if($pagenav_type === 'normal'){ ?>

            <?php echo b2_pagenav(array('pages'=>$data['pages'],'paged'=>$paged)); ?>

        <?php }else{ 

            $type = $pagenav_type === 'ajax_loader' ? 'm' : 'p';

        ?>
            <page-nav paged="<?php echo $paged; ?>" navtype="post" pages="<?php echo $data['pages']; ?>" type="<?php echo $type; ?>" :box="selecter" :opt="opt" :api="api" url="<?php echo get_category_link($term->term_id); ?>" title="<?php echo $term->name; ?>"></page-nav>
        
        <?php } 
  
        ?>
    </div>
</div>