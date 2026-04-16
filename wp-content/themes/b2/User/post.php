<?php

$user_id =  get_query_var('author');

$paged = get_query_var('b2_paged') ? get_query_var('b2_paged') : 1;

$pagename = get_query_var('b2_user_page');

$settings = array(
    'post_type'=>'post-1',
    'post_order'=>'',
    'post_row_count'=>3,
    'post_count'=>15,
    'post_thumb_ratio'=>'1/0.84',
    'post_open_type'=>1,
    'post_meta'=>array('date','views','like','cats','edit'),
    'author__in' => array($user_id),
    'post_paged'=>$paged,
    'post_status'=>array('publish','pending','draft'),
    'post_ignore_sticky_posts'=>1
);

$settings['post_paged'] = isset($settings['post_paged']) ? $settings['post_paged'] : $paged;

//取消隐藏加载更多按钮
$settings['post_load_more'] = 0;

//隐藏侧边栏
$settings['show_sidebar'] = true;
$settings['width'] = b2_get_page_width($settings['show_sidebar']);

$settings = apply_filters('b2_user_page_post',$settings);

//获取文章列表数据
// $modules =  new B2\Modules\Templates\Modules\Posts;
// $data = $modules->init($settings,1,true);

//排序css
$fr = 0;
for($_i=1; $_i<=$settings['post_row_count']; $_i++){
    if(($settings['post_type'] == 'post-4' && $_i == 1) || $settings['post_type'] == 'post-3'){
        $fr = 1;
    }else{
        $fr ++;
    }
}

$r = (round(1/$fr,4)*100).'%';
?>
<style>
    .post-item-1 ul.b2_gap > li{
        width:<?php echo $r; ?>
    }
</style>
<div id="author-post-list" data-settings='<?php echo json_encode($settings,true); ?>' ref="AuthorSettings">
    <div class="button empty b2-loading empty-page text box b2-radius" v-show="loading"></div>
    <div class="<?php echo $settings['post_type']; ?> post-list post-item-1 hidden-line" id="post-list" v-show="!empty" v-cloak>
        <ul class="b2_gap <?php if($settings['post_type'] == 'post-2') echo 'grid'; ?>"></ul>
    </div>
    <div v-show="empty && !loading" v-cloak class="box b2-radius"><?php echo B2_EMPTY; ?></div>

    <div class="author-page-nav b2-radius mg-t box b2-pd" v-if="pages > 0" v-cloak>
        <page-nav ref="commentPageNav" paged="<?php echo $paged; ?>" navtype="post" :pages="pages" type="p" :box="selecter" :opt="options" :api="api" url="<?php echo get_author_posts_url($user_id).'/'.$pagename; ?>" title=""></page-nav>
    </div>
    
</div>