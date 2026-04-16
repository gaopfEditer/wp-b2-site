<?php
    use B2\Modules\Common\User;

    $user_id = get_query_var('author');
    $paged = get_query_var('b2_paged');
    $pagename = get_query_var('b2_user_page');
    $sub = get_query_var('b2_user_page_sub');
    $sub = $sub ? $sub : 'post';

    $paged = $paged ? $paged : 1;

    $collections = User::get_user_favorites_list($user_id,$paged,15,$sub);

    $post_type = b2_post_types();
    if(!b2_get_option('newsflashes_main','newsflashes_open')){
        unset($post_type['newsflashes']);
    }

?>
<div class="collections-menu box b2-radius mg-b">
    <ul>
        <?php 
            foreach ($post_type as $k => $v) {
        ?>
            <li><a class="<?php if($sub == $k) echo 'current'; ?> b2-radius" href="<?php echo get_author_posts_url($user_id).'/'.$pagename.'/'.$k; ?>"><?php echo $v; ?></a></li>
        <?php
            }
        ?>
    </ul>
</div>
<div class="author-follow" id="author-collections" ref="authorFollow" data-paged="<?php echo $paged; ?>" data-sub="<?php echo $sub; ?>" data-pages="<?php echo $collections['pages']; ?>">
    <ul class="collections-post-list box b2-radius b2-pd">
        <div class="button empty b2-loading empty-page text"></div>
    </ul>
    <div class="b2-pagenav" v-show="pages > 0">
		<page-nav ref="commentPageNav" paged="<?php echo $paged; ?>" navtype="authorComments" pages="<?php echo $collections['pages']; ?>" type="p" box=".author-follow ul" :opt="options" :api="api" url="<?php echo get_author_posts_url($user_id).'/'.$pagename.'/'.$sub; ?>" title=""></page-nav>
	</div>
</div>
