<div class="topic-hot-comment" v-if="Object.keys(item.hot_comment).length > 0 && item.sticky != 1 && item.allow_read['type'] !== 'current_circle_read'">
    <span class="hot-comment-title"><?php echo __('热门评论','b2'); ?></span>
    <div class="hot-commment-content">
        <span v-text="item.hot_comment.author"></span> : <span v-text="item.hot_comment.content"></span>
    </div>
    <span class="hot-comment-up"><?php echo b2_get_icon('b2-thumb-up-line'); ?><b v-text="item.hot_comment.comment_up"></b></span>
</div>
<div class="topic-footer">
    <div class="topic-footer-left">
        <button :class="['text',{'picked b2-color':item.meta.vote.isset_up}]" @click="postVote(ti,'up',item.topic_id)"><?php echo b2_get_icon('b2-arrow-drop-up-fill'); ?><span><?php echo __('赞','b2'); ?></span><b v-text="item.meta.vote.up"></b></button>
        <button :class="['text',{'picked b2-color':item.meta.vote.isset_down}]" @click="postVote(ti,'down',item.topic_id)"><?php echo b2_get_icon('b2-arrow-drop-down-fill'); ?></button>
        <span class="topic-date"><b v-html="item.meta.date"></b></span>
        <span v-if="item.status === 'pending' && !admin.is" class="topic-pending"><?php echo __('待审','b2'); ?></span>
        <div class="topic-meta-more-box">
            <button class="topic-date topic-meta-more"><?php echo b2_get_icon('b2-more-line'); ?></button>
            <div class="topic-more-menu">
                <ul>
                    <li v-if="isAdmin() || item.role.can_delete"><a :href="'<?php echo b2_get_custom_page_url('circle-topic-edit').'?topic_id='; ?>'+item.topic_id" target="_blank"><?php echo __('编辑话题','b2'); ?></a></li>
                    <li v-if="isAdmin() || item.role.can_delete"><button @click="deleteTopic(ti,item.topic_id)"><?php echo __('删除话题','b2'); ?></button></li>
                    <li><button class="fuzhi" :data-clipboard-text="item.link"><?php echo __('复制链接','b2'); ?></button></li>
                    <!-- <li v-if="!single.is"><a class="fuzhi" :href="item.link" target="_blank"><?php echo __('前往查看','b2'); ?></a></li> -->
                    <!-- <li><button><?php echo __('举报','b2'); ?></button></li> -->
                    <li v-if="isAdmin()">
                        <button @click="setSticky(item.topic_id,ti)"><b v-if="item.sticky === 1"><?php echo __('取消置顶','b2'); ?></b><b v-else><?php echo __('置顶','b2'); ?></b></button>
                    </li>
                    <li v-if="isAdmin()">
                        <button @click="setBest(item.topic_id,ti)"><b v-if="item.best"><?php echo __('取消加精','b2'); ?></b><b v-else><?php echo __('加精','b2'); ?></b></button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="topic-footer-right">
        <div v-if="admin.is">
            <button class="red text" @click="deleteTopic(ti,item.topic_id)"><?php echo __('删除','b2'); ?></button>
            <button class="green text" @click="topicChangeStatus(ti,item.topic_id)"><?php echo __('审核通过','b2'); ?></button>
        </div>
        <button :class="['topic-comment-button',{'b2-color':commentBox.index === ti}]" @click="showComment(ti,true)" v-else>
            <span v-if="commentBox.index === ti"><?php echo __('收起讨论','b2'); ?></span>
            <span v-else-if="item.meta.comment != 0"><?php echo '<b v-text="item.meta.comment"></b>'.__('条讨论','b2'); ?></span>
            <span v-else><?php echo __('参与讨论','b2'); ?></span>
        </button>
    </div>
</div>