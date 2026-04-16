<?php
/**
 * 分类存档页面
 */
    $settings = array(
        'post_type'=>'post-1',
        'post_order'=>'new',
        'post_row_count'=>4,
        'post_count'=>24,
        'post_thumb_ratio'=>'1/0.8',
        'post_open_type'=>1,
        'post_meta'=>array('user','date','views','like','cats','des'),
    );

    global $wp;
    $current_url = B2_HOME_URI.'/'.add_query_arg(array(),$wp->request);
    $current_url = preg_replace('/\/page\/(\d+)/i','',$current_url);

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

    $size = $modules::get_thumb_size($settings,$settings['post_row_count']);

     //搜索
     if(isset($_POST['archiveSearch']) && !empty($_POST['archiveSearch']) ){

        $settings['search'] = $_POST['archiveSearch'];
    }

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
} ?>

<div class="archive-row">
<div class="<?php echo ($settings['post_row_count'] > 1 && $settings['post_type'] == 'post-3' ? 'post-3-li-dubble' : ($settings['post_type'] == 'post-3' ? 'box b2-radius' : '')).' '.$settings['post_type'].' post-item-1 post-list hidden-line '.(($settings['post_row_count'] == 1 && $settings['post_type'] == 'post-3' > 1) || $settings['post_type'] == 'post-5' || $settings['post_type'] == 'post-6' ? 'box b2-radius' : ''); ?>" id="post-list">
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
        <page-nav paged="<?php echo $paged; ?>" navtype="post" pages="<?php echo $data['pages']; ?>" type="<?php echo $type; ?>" :box="selecter" :opt="opt" :api="api" url="<?php echo $current_url; ?>" title=""></page-nav>
    
    <?php } 

    ?>
</div>