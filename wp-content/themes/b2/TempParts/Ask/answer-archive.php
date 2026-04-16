<div class="gujia" ref="gujiaanswer">
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
<div class="answer-list people-answer" v-if="answerData !== ''">
    <template v-if="answerData.data.length == 0">
        <div class="answer-empty"><?php echo __('暂无讨论','b2'); ?></div>
    </template>
    <template v-else>
        <div :class="['answer-item',{'new-comment':item.new}]" v-for="(item,i) in answerData.data" :key="i" :id="'answer-item-'+item.post_id">
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
            <div class="people-parent-data">
                <a :href="item.parent_data.link" target="_blank"><h2 v-html="item.parent_data.title"></h2></a>
            </div>
            <div class="people-answer-type green"><?php echo __('回答：','b2'); ?></div>
            <a :href="item.link" target="_blank"><div :class="['answer-content entry-content',{'answer-show':item.showMore}]" v-html="item.content"></div></a>
            <div class="answer-show-more" v-if="item.showMore" v-cloak @click="item.showMore = false">
                <b></b><span><?php echo __('阅读全部','b2'); ?></span><b></b>
            </div>
            <div class="answer-date b2flex">
                <div><?php echo sprintf(__('发布于：%s'),'<time v-text="item.date"></time>'); ?><span class="red answer-pending" v-if="item.post_status == 'pending'"><?php echo __('待审','b2'); ?></span></div>
                <div class="b2flex">
                    <div class="red answer-pending answer-edit" v-if="item.can_edit" @click="deleteAnswer(i,item.post_id)">
                        <?php echo __('删除','b2'); ?>
                    </div>
                    <div class="red answer-pending answer-edit" v-if="item.can_edit">
                        <a :href="item.parent_data.link+'?answer_id='+item.post_id" target="_blank"><?php echo __('编辑','b2'); ?></a>
                    </div>
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
            </div>
        </div>
    </template>
</div>