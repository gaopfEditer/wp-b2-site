<div class="topic-comments" v-cloak v-show="opt.topicId == item.topic_id">
    <div v-if="!item.can_comment" class="topic-comment-list-none">
            <span><?php echo __('该帖子评论已关闭','b2'); ?></span>
    </div>
    <template v-else>
        <div :id="'comment-box-'+item.topic_id"></div>
        <div v-if="commentList.load && !commentList.reload" class="comment-list-load"><button class="text empty b2-loading"></button></div>
        <div class="topic-comment-list b2-radius" :id="'comment-list-'+item.topic_id" v-else-if="(!commentList.load || commentList.reload) && opt.topicId == item.topic_id">
            <div class="topic-comment-list-header" v-if="commentList.list.length > 0">
                <span><?php echo __('讨论','b2'); ?></span>
                <button class="text" @click="changeOrderBy">
                    <span v-if="opt.orderBy === 'ASC'"><?php echo b2_get_icon('b2-arrow-left-right-fill').__('切换为时间排序','b2'); ?></span>
                    <span v-else><?php echo b2_get_icon('b2-arrow-left-right-fill').__('切换为默认顺序','b2'); ?></span>
                </button>
            </div>
            <ul v-if="commentList.list.length > 0">
                <li v-for="(list,ci) in commentList.list" :id="'topic-comment-'+list.comment_ID" class="topic-lv1" :key="list.comment_ID">
                    <div class="topic-comment-header">
                        <div class="topic-comment-avatar">
                            <a :href="list.comment_author.link" target="_blank">
                                <?php echo b2_get_img(array(
                                    'source_data'=>':srcset="list.comment_author.avatar_webp"',
                                    'src_data'=>':src="list.comment_author.avatar"',
                                    'class'=>array('avatar','b2-radius')
                                ));?>
                            </a>
                        </div>
                        <div class="topic-comment-content">
                            <div class="topic-author-info">
                                <div class="topic-author-info-left">
                                    <div>
                                        <a :href="list.comment_author.link" target="_blank"><span v-text="list.comment_author.name" class="author"></span></a>
                                        <span v-text="list.comment_author.vip" :class="'author-vip b2-'+list.comment_author.vip" v-if="list.comment_author.vip"></span>
                                        <span v-text="list.comment_author.lv" :class="'author-lv b2-'+list.comment_author.lv" v-if="list.comment_author.lv"></span>
                                    </div>
                                    <div class="comment-floor"><?php echo sprintf(__('第 %s 层','b2'),'<span>{{list.floor}}</span>'); ?></div>
                                </div>
                               
                                <div class="topic-author-info-right" v-if="list.can_edit">
                                    <button @click="deleteComment(list.comment_ID,ci)"><?php echo __('删除','b2'); ?></button>
                                </div>
                            </div>
                            <div class="topic-comment-text">
                                <p v-text="list.comment_content"></p>
                                <div class="topic-commentlist-img-box" v-if="list.img.full">
                                    <?php echo b2_get_img(array(
                                        'src_data'=>':src="list.img.thumb"',
                                        'source_data'=>':srcset="list.img.thumb_webp"',
                                        'data'=>array(
                                            ':data-zooming-width'=>'list.img.width',
                                            ':data-zooming-height'=>'list.img.height',
                                            ':data-src'=>'list.img.thumb',
                                            ':data-original'=>'list.img.full'
                                        )
                                    ));?>
                                </div>
                            </div>
                            <div class="topic-author-meta">
                                <span v-html="list.date" class="date"></span>
                                <div class="topic-author-meta-right">
                                    <button :class="['text',{'b2-color':commentBox.parent == list.comment_ID}]" @click="showChildComment(ci,list.comment_ID)"><?php echo b2_get_icon('b2-chat-smile-2-line'); ?><b v-text="list.child_comments.count"></b></button>
                                    <button :class="['text',{'picked b2-color':list.vote.picked}]" @click="vote(ci,'',list.comment_ID)"><?php echo b2_get_icon('b2-thumb-up-line'); ?><b v-text="list.vote.up"></b></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div :id="'comment-box-at-'+list.comment_ID"></div>
                    <div :id="'topic-comment-child-list-'+list.comment_ID" v-if="list.child_comments.list.length > 0" class="topic-child-list">
                        <ul>
                            <li v-for="(child,cd) in list.child_comments.list" :id="'topic-comment-'+child.comment_ID" :class="['topic-lv2',{'new-comment-child':cd > 2}]" :key="child.comment_ID">
                                <div class="topic-comment-header">
                                    <div class="topic-comment-content">
                                        <div class="topic-author-info">
                                            <div class="topic-author-info-left">
                                                <a :href="child.comment_author.link" target="_blank">
                                                    <?php echo b2_get_img(array(
                                                        'source_data'=>':srcset="child.comment_author.avatar_webp"',
                                                        'src_data'=>':src="child.comment_author.avatar"',
                                                        'class'=>array('avatar','b2-radius'),
                                                    ));?>
                                                </a>
                                                <span class="author"><a :href="child.comment_author.link" target="_blank" v-text="child.comment_author.name" ></a></span>
                                                <?php echo b2_get_icon('b2-arrow-right-s-fill'); ?>
                                                <span class="author"><a :href="child.at.link" target="_blank" v-text="child.at.name" ></a></span>
                                            </div>
                                            <div class="topic-author-info-right" v-if="list.can_edit">
                                                <button @click="deleteComment(child.comment_ID,ci,cd)"><?php echo __('删除','b2'); ?></button>
                                            </div>
                                        </div>
                                        <div class="topic-comment-text">
                                            <p v-text="child.comment_content"></p>
                                            <div class="topic-commentlist-img-box" v-if="child.img.full">
                                            <?php echo b2_get_img(array(
                                                'src_data'=>':src="child.img.thumb"',
                                                'source_data'=>':srcset="child.img.thumb_webp"',
                                                'data'=>array(
                                                    ':data-zooming-width'=>'child.img.width',
                                                    ':data-zooming-height'=>'child.img.height',
                                                    ':data-src'=>'child.img.thumb',
                                                    ':data-original'=>'child.img.full'
                                                )
                                            ));?>
                                            </div>
                                        </div>
                                        <div class="topic-author-meta">
                                            <span v-html="child.date" class="date"></span>
                                            <div class="topic-author-meta-right">
                                                <button :class="['text huifu-button',{'b2-color':commentBox.parent == child.comment_ID}]" @click="showChildComment(ci,child.comment_ID)"><?php echo b2_get_icon('b2-chat-smile-2-line'); ?></button>
                                                <button :class="['text',{'picked b2-color':child.vote.picked}]" @click="vote(ci,cd,child.comment_ID)"><?php echo b2_get_icon('b2-thumb-up-line'); ?><b v-text="child.vote.up"></b></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div :id="'comment-box-at-'+child.comment_ID"></div>
                            </li>
                        </ul>
                        <div class="topic-cilid-comment-more" v-if="list.child_comments.count > 3 && list.child_comments.list.length < list.child_comments.count">
                            <span class="b2-color" @click="getChildComments(ci,list.comment_ID)" v-if="list.child_comments.locked == false"><?php echo sprintf(__('阅读剩余%s条回复','b2'),'<b v-text="list.child_comments.count - ((list.child_comments.paged-1)*6 +3)"></b>'); ?></span>
                            <span class="b2-color" v-else><?php echo __('加载中...','b2'); ?></span>
                        </div>
                    </div>

                </li>
            </ul>
            <div class="topic-comment-list-footer" v-if="commentList.list.length > 0">
                <pagenav-new ref="topicCommentNav" type="p" :paged="opt.paged" :pages="opt.pages" :opt="opt" api="getTopicCommentList" @return="getMoreCommentListData" v-if="opt.pages > 1"></pagenav-new>
                <span v-else><?php echo __('没有更多讨论了','b2'); ?></span>
            </div>
            <div class="topic-comment-list-none" v-if="Object.keys(commentList.list).length == 0">
                <span><?php echo __('没有讨论，您有什么看法？','b2'); ?></span>
            </div>
        </div>
    </template>
</div>