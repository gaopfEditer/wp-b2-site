<?php
use B2\Modules\Templates\Single;
use B2\Modules\Common\Ask;
use B2\Modules\Common\Post;
get_header();
$post_type = get_post_type();

$post_id = get_the_ID();

$tags = Ask::get_ask_tags($post_id);

$post_meta = Post::post_meta($post_id);

$post_meta['user_link'] = b2_get_custom_page_url('ask-people').'?id='.$post_meta['user_id'];
$smile = B2\Modules\Common\Comment::smilies_reset(true);
$html = '';
$answer_smile = '';
foreach ($smile as $k => $v) {
    $html .= '<button class="text smily-button" @click="addSmile(\''.$k.'\')">'.$v.'</button>';
    $answer_smile .= '<button class="text smily-button" @click="answerAddSmile(\''.$k.'\')">'.$v.'</button>';
}
$answer_name = b2_get_option('normal_custom','custom_answer_name');
?>
<?php do_action('b2_single_wrapper_before'); ?>

<div class="b2-single-content wrapper">

    <?php do_action('b2_single_before'); ?>

    <div id="primary-home" class="content-area">
        <?php do_action('b2_single_ask_content_before'); ?>
            <article class="single-article b2-radius box">
                <div class="b2flex ask-single-top" data-term="<?php echo isset($tags[0]['id']) ? $tags[0]['id'] : 0;?>">
                    <div class="ask-tags mg-b">
                        <?php 
                            foreach ($tags as $t) {
                                echo '<a href="'.$t['link'].'" target="_blank" style="background:'.$t['bgcolor'].';color:'.$t['color'].'" class="ask-tag-item">'.$t['name'].'</a>';
                            }
                        ?>
                    </div>
                    <div class="ask-pay b2flex" v-if="metas.reward" v-cloak>
                        <div class="ask-reward-pay-left">
                            <div class="ask-pay-number b2flex">
                                <span v-if="metas.reward.rewardType == 'credit'">
                                    <?php echo b2_get_icon('b2-coin-line'); ?>
                                    <b v-text="metas.reward.money"></b>
                                </span>
                                <span v-else>
                                    <?php echo B2_MONEY_SYMBOL; ?>
                                    <b v-text="metas.reward.money"></b>
                                </span>
                            </div>
                            <div class="ask-pay-type">
                                <span v-if="!metas.endtime" class="ask-passtime"><?php echo __('悬赏已过期','b2'); ?></span>
                                <span class="ask-passtime" v-else><?php echo sprintf(__('%s后悬赏过期'),'<b v-text="metas.endtime"></b>'); ?></span>
                            </div>
                        </div>
                        <div class="ask-reward"><?php echo __('悬赏','b2'); ?></div>
                    </div>
                </div>
                <header class="entry-header">
                    <h1>
                        <?php the_title(); ?>
                    </h1>
                    <div id="post-meta" class="post-user-info">
                        <div class="post-meta-left">
                            <a class="link-block" href="<?php echo $post_meta['user_link']; ?>" target="_blank"></a>
                            <div class="avatar-parent"><img class="avatar b2-radius" src="<?php echo $post_meta['user_avatar']; ?>" /><?php echo $post_meta['user_title'] ? $post_meta['verify_icon'] : ''; ?></div>
                            <div class="post-user-name"><b><?php echo $post_meta['user_name']; ?></b><span class="user-title"><?php echo $post_meta['user_title']; ?></span></div>
                        </div>
                        <div class="post-meta-right">
                            <div class="" v-if="self == false" v-cloak>
                                <button @click="followingAc" class="author-has-follow" v-if="following"><?php echo __('取消关注','b2'); ?></button>
                                <button @click="followingAc" v-else class="author-follow"><?php echo b2_get_icon('b2-add-line').__('关注','b2'); ?></button>
                                <button class="empty" @click="dmsg()"><?php echo __('私信','b2'); ?></button>
                            </div>
                        </div>
                    </div>
                </header>
                <div class="ask-single-gujia gujia">
                    <div class="ask-item-info">
                        <div>
                            <h2 class="ask-title"></h2>
                            <div class="ask-desc">
                                <div></div>
                                <div></div>
                            </div>
                        </div>
                    </div>
                    <div class="ask-item-footer b2flex">
                        <div class="ask-tags">
                            <span></span>
                            <span></span>
                        </div>
                        <div class="ask-metas">
                            <span></span>
                        </div>
                    </div>
                </div>
                <div class="ask-single-info">
                    <div class="ask-inv-box b2-radius" v-if="postData && postData.inv.length > 0" >
                        <?php echo b2_get_icon('b2--pangxie'); ?>
                        <span v-if="postData.inv.length > 0">
                            <?php echo sprintf(__('邀请：%s','b2'),'<a :href="u.link" target="_blank" v-for="(u,ui) in postData.inv" :key="ui"><b v-text="u.name"></b></a>'); ?>
                        </span>
                    </div>
                    <div class="entry-excerpt" @click.stop="showAc">
                        <span class="ask-excerpt" v-html="excerpt"></span>  
                        <!-- <span class="ask-single-more"><?php echo __('显示全部','b2').b2_get_icon('b2-arrow-down-s-line'); ?></span> -->
                    </div>
                    <div class="entry-content">
                        <?php the_content(); ?>
                    </div>
                    <div class="ask-footer">
                        <div class="ask-metas">
                            <span><?php echo b2_get_icon('b2-message-3-fill'); ?><?php echo $answer_name.' <b v-text="answer_count"></b>'; ?></span>
                            <span><?php echo b2_get_icon('b2-eye-fill'); ?><?php echo sprintf(__('阅读 %s','b2'),'<b v-text="views"></b>'); ?></span>
                            <span @click="postFavoriteAc" :class="['ask-favorite',{'b2-color':favorites_isset}]"><?php echo b2_get_icon('b2-star-fill'); ?><?php echo sprintf(__('收藏 %s','b2'),'<b v-text="favorites"></b>'); ?></span>
                            <span v-if="status == 'pending'" class="green"><?php echo __('待审','b2'); ?></span>
                            <span v-if="canEdit"><a :href="'<?php echo b2_get_custom_page_url('po-ask'); ?>?id='+b2_global.post_id" target="_blank" class="red"><?php echo __('编辑','b2'); ?></a></span>
                        </div>
                        <div class="ask-footer-right">
                            <span class="ask-single-more" @click.stop="showAc" v-show="!show"><?php echo __('显示全部','b2').b2_get_icon('b2-arrow-down-s-line'); ?></span>
                            <span @click.stop="showAc" class="single-ask-hiden" v-show="show"><?php echo __('收起','b2').b2_get_icon('b2-arrow-up-s-line');?></span>
                        </div>
                    </div>
                </div>
                <div class="ask-write-answer" data-id="<?php echo $post_id;?>" ref="writeanswer">
                    <button class="text" @click.stop="showAnswer(false)"><?php echo b2_get_icon('b2-quill-pen-line').sprintf(__('写%s','b2'),$answer_name); ?></button>
                </div>
            </article>
            <div class="ask-write-answer-box box b2-radius mg-b">
                <div class="po-answer-title b2flex">
                    <span><?php echo sprintf(__('您的%s','b2'),$answer_name); ?></span>
                    <span class="aclose" onclick="askwriteanswer.showAnswer()"><?php echo b2_get_icon('b2-close-line'); ?></span>
                </div>
                <div id="write-answer-box"></div>
            </div>
            <div class="ask-answer-box box b2-radius">
                <div class="ask-answer-title b2flex">
                    <h2><?php echo $answer_name; ?></h2>
                    <div>
                        <span :class="fliter == 'hot' ? 'b2-color' : ''" @click="fliter = 'hot'"><?php echo __('默认排序','b2'); ?></span>
                        <span :class="fliter == 'date' ? 'b2-color' : ''" @click="fliter = 'date'"><?php echo __('时间排序','b2'); ?></span>
                    </div>
                </div>
                <div class="gujia" ref="gujia">
                    <?php for ($i=0; $i < 8; $i++) {  ?>
                        <div class="answer-item">
                            <div class="answer-top b2flex">
                                <div class="b2flex answer-top-left">
                                    <span class="avatar"></span>
                                    <div class="author-info">
                                        <span></span>
                                        <span></span>
                                    </div>
                                </div>
                                <div class="answer-top-right">
                                    <span></span>
                                </div>
                            </div>
                            <div class="answer-content entry-content">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                            <div class="answer-footer b2flex">
                                <div class="answer-footer-left">
                                    <span></span>
                                    <span></span>
                                </div>
                                <div class="answer-footer-right">
                                    <span></span>
                                    <span></span>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <div class="answer-list" v-if="data !== ''">
                    <template v-if="data.data.length == 0">
                        <div class="answer-empty"><?php echo __('暂无讨论','b2'); ?></div>
                    </template>
                    <template v-else>
                        <div :class="['answer-item',{'new-comment':item.new}]" v-for="(item,i) in data.data" :key="i" :id="'answer-item-'+item.post_id">
                            <div class="answer-top b2flex">
                                <div class="b2flex answer-top-left">
                                    <a :href="item.author.link" target="_blank" class="link-block"></a>
                                    <span class="avatar">
                                        <?php echo b2_get_img(array(
                                            'src_data'=>':src="item.author.avatar"',
                                            'class'=>array('avatar'),
                                            'source_data'=>':srcset="item.author.avatar_webp"'
                                        ));?>
                                    </span>
                                    <div class="author-info">
                                        <div><span v-text="item.author.name" class="answer-name"></span></div>
                                        <div class="b2flex">
                                            <p v-html="item.author.lv.lv.lv ? item.author.lv.lv.icon : ''"></p>
                                            <p v-html="item.author.lv.vip.lv ? item.author.lv.vip.icon : ''"></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="answer-top-right">
                                    <span>
                                        <button :class="['answer-follow',{'picked':item.author.followed}]" v-if="!item.self" @click="followingAc(i,item.author.id)"><span v-if="item.author.followed"><?php echo b2_get_icon('b2-subtract-line').__('已关注','b2'); ?></span><span v-else><?php echo b2_get_icon('b2-add-line').__('关注','b2'); ?></span></button>
                                    </span>
                                </div>
                            </div>
                            <div class="b2flex answer-meta" v-if="isInv(item.author.id) || item.best">
                                <div class="answer-inv" v-if="isInv(item.author.id)"><?php echo b2_get_icon('b2--pangxie').__('蟹邀','b2'); ?></div>
                                <div class="green answer-best" v-if="item.best"><?php echo b2_get_icon('b2-check-double-line').__('最佳答案','b2'); ?></div>
                            </div>
                            <div :class="['answer-content entry-content',{'answer-show':item.showMore}]" v-html="item.content"></div>
                            <div class="answer-show-more" v-if="item.showMore" v-cloak @click="item.showMore = false">
                                <b></b><span><?php echo __('阅读全部','b2'); ?></span><b></b>
                            </div>
                            <div class="answer-date b2flex">
                                <span><?php echo sprintf(__('发布于：%s'),'<time v-text="item.date"></time>'); ?></span>
                                <div class="b2flex">
                                    <div class="red answer-pending answer-edit" v-if="item.can_edit" @click="deleteAnswer(i,item.post_id)">
                                        <?php echo __('删除','b2'); ?>
                                    </div>
                                    <div class="red answer-pending answer-edit" v-if="item.can_edit" @click="editAnswer(item.post_id)">
                                        <?php echo __('编辑','b2'); ?>
                                    </div>
                                    <div class="red answer-pending" v-if="item.post_status == 'pending'"><?php echo __('待审','b2'); ?></div>
                                </div>
                            </div>
                            <div class="answer-footer b2flex">
                                <div class="answer-footer-left b2flex">
                                    <div class="b2flex">
                                        <button :class="['answer-vote',{'picked':item.vote.isset_up}]" @click="answerVote(i,'up',item.post_id)"><?php echo b2_get_icon('b2-arrow-drop-up-fill').sprintf(__('赞同 %s','b2'),'<b v-text="item.vote.up" v-if="item.vote.up > 0"></b>'); ?></button>
                                        <button :class="['answer-vote answer-down',{'picked':item.vote.isset_down}]" @click="answerVote(i,'down',item.post_id)"><?php echo b2_get_icon('b2-arrow-drop-down-fill'); ?></button>
                                    </div>
                                    <div class="best-answer"><span @click="bestAnswer(i,item.post_id)" v-if="best == 0 && b2_global.author_id == userData.id"><?php echo __('采纳为最佳答案','b2'); ?></span></div>
                                    <div class="answer-link-button"><a :href="item.link" target="_blank"><?php echo b2_get_icon('b2-share-forward-fill').__('直达连接','b2'); ?></a></div>
                                    <!-- <span><?php echo b2_get_icon('b2-star-fill'); ?><?php echo sprintf(__('收藏 %s','b2'),'<b v-text="item.favorites"></b>'); ?></span>
                                    <span><?php echo b2_get_icon('b2-eye-fill'); ?><?php echo sprintf(__('阅读 %s','b2'),'<b v-text="item.views"></b>'); ?></span> -->
                                </div>
                                <div class="answer-footer-right">
                                    <button class="answer-comment-button" @click="showComment(i,true)">
                                        <span v-if="item.comment == 0" class="b2flex">
                                            <?php echo __('参与讨论','b2').b2_get_icon('b2-arrow-down-s-line'); ?>
                                        </span>
                                        <span v-else class="b2flex">
                                            <?php echo sprintf(__('%s条讨论','b2').b2_get_icon('b2-arrow-down-s-line'),'<b v-text="item.comment"></b>'); ?>
                                        </span>
                                    </button>
                                </div>
                            </div>
                            <?php get_template_part( 'TempParts/Ask/comments');?>
                        </div>
                    </template>
                </div>
                <pagenav-new class="ask-list-nav" ref="infonav" navtype="post" :pages="topt['pages']" type="p" box=".answer-item" :opt="topt" :api="api" :rote="false" url="<?php echo get_permalink($post_id); ?>" @return="get"></pagenav-new>
                <div id="comment-form-reset" v-cloak>
                    <div class="topic-comment-form show b2-radius" id="topic-comment-form" @click.stop="">
                        <div class="topic-answer-login" v-if="!login" v-cloak>
                            <p><?php echo __('请先登录！','b2'); ?></p>
                            <div><button class="empty" @click="loginAc(1)"><?php echo __('登录','b2'); ?></button>&nbsp;&nbsp;<button @click="loginAc(2)"><?php echo __('快速注册','b2'); ?></button></div>
                        </div>
                        <div class="topic-comment-left">
                            <textarea @focus="commentBox.focus = true" ref="topicForm" placeholder="<?php echo __('您的看法','b2'); ?>"></textarea>
                            <div class="topic-comment-tools">
                                <button :class="['text',{'hover':commentBox.showImgBox}]">
                                    <label>
                                        <?php echo b2_get_icon('b2-image-fill'); ?>
                                        <input type="file" accept="image/jpg,image/jpeg,image/png,image/gif" @change="getFile($event)" multiple="multiple" class="b2-hidden-always" ref="imageInput"/>
                                    </label>
                                </button>
                                <button :class="['text',{'hover':smileShow}]" @click="smileShow = !smileShow"><?php echo b2_get_icon('b2-emotion-laugh-line'); ?></button>
                                <div v-if="commentBox.showImgBox" v-cloak class="topic-comment-img-box">
                                    <span>
                                        <b v-if="commentBox.progress === 100"><?php echo __('图片审查中...','b2'); ?></b>
                                        <b v-else-if="commentBox.progress !== 'success'" v-text="commentBox.progress+'%'"></b>
                                        <b v-else class="topic-image-close">
                                            <i class="b2font b2-close-line" @click="removeImage()"></i>
                                        </b>
                                    </span>
                                    <img :src="commentBox.img" v-if="commentBox.img"/>
                                </div>
                                <div :class="['comment-smile-box',{'b2-show':smileShow}]" v-cloak @click.stop="">
                                    <?php echo $html; ?>
                                </div>
                            </div>
                        </div>
                        <div class="topic-comment-right">
                            <button class="text" @click="setCommentBox('#comment-box-'+opt.topicId)"><?php echo __('取消','b2'); ?></button>
                            <button :disabled="commentDisabled()" @click="submitComment" :class="commentBox.locked == true ? 'b2-loading' : ''"><span><?php echo __('发布','b2'); ?></span></button>
                        </div>
                    </div>
                </div>
            </div>
        <?php do_action('b2_single_ask_content_after'); ?>
    </div>

    <?php do_action('b2_single_after'); ?>

    <?php 
        get_sidebar(); 
    ?>

</div>
<?php do_action('b2_single_wrapper_after'); ?>
<?php
get_footer();