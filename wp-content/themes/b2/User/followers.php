<?php
    $user_id = get_query_var('author');
    $paged = get_query_var('b2_paged');
    $paged = $paged ? $paged : 1;
    $pagename = get_query_var('b2_user_page');
    $follow = get_user_meta($user_id,'zrz_followed',true);
    $follow = is_array($follow) ? count($follow) : 0;
    $_pages = ceil($follow/15);
?>
<div class="author-follow  box b2-radius b2-pd" id="author-followers" ref="authorFollow" data-paged="<?php echo $paged; ?>" data-pages="<?php echo $_pages; ?>">
    <ul>
        <div class="button empty b2-loading empty-page text"></div>
    </ul>
    <div class="b2-pagenav mg-t" v-show="pages > 0" v-cloak style="padding:0">
        <page-nav ref="commentPageNav" paged="<?php echo $paged; ?>" navtype="authorComments" pages="<?php echo $_pages; ?>" type="p" box=".author-follow ul" :opt="options" :api="api" url="<?php echo get_author_posts_url($user_id).'/'.$pagename; ?>" title=""></page-nav>
    </div>
</div>
