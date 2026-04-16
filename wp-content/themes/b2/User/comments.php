<?php
use B2\Modules\Common\Comment;

$user_id =  get_query_var('author');

$paged = get_query_var('b2_paged');
$paged = $paged ? $paged : 1;
$number = 10;

$pagename = get_query_var('b2_user_page');

$data = Comment::get_user_comment_list(array('user_id'=>$user_id,'paged'=>$paged,'number'=>$number));

?>
<div class="author-comment-list">
    <div class="box b2-radius b2-pd mg-b">
        <ul>
            <?php 
                if(empty($data['data'])){
                    echo B2_EMPTY;
                }else{

                    foreach ($data['data'] as $k => $v) {
                        ?>
                            <li>
                                <div class="author-comment-date"><?php echo $v['comment_date']; ?></div>
                                <div class="author-comment-content b2-radius">
                                    <?php if($v['comment_img']){ ?><div class="comment-img-box"><img class="comment-img b2-radius" src="<?php echo $v['comment_img']; ?>" /></div><?php } ?>
                                    <div class="author-comment-content-text"><?php echo $v['comment_content']; ?></div>
                                </div>
                                <div class="author-comment-post"><a href="<?php echo $v['post_link'];?>"><?php echo b2_get_icon('b2-external-link-line').$v['post_title']; ?></a></div>
                            </li>
                        <?php
                    }

                }
            ?>
        </ul>
    </div>
    <?php if(ceil($data['count']/$number) > 0){ ?>
    <div class="b2-pagenav author-comment box b2-radius b2-pd" ref="authorCommentSettings" data-settings='<?php echo json_encode(array('user_id'=>$user_id,'paged'=>$paged,'number'=>$number),true); ?>'>
		<page-nav ref="commentPageNav" paged="<?php echo $paged; ?>" navtype="authorComments" pages="<?php echo ceil($data['count']/$number); ?>" type="p" :box="selecter" :opt="options" :api="api" url="<?php echo get_author_posts_url($user_id).'/'.$pagename; ?>" title=""></page-nav>
	</div>
    <?php } ?>
</div>