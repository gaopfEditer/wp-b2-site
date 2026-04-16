<div id="ask-archive" class="ask-list" ref="asklist">
    <div class="gujia" ref="gujia">
        <?php 
            for ($i=0; $i < 12; $i++) {
                ?>
                <div class="ask-item" >
                    <div class="ask-item-top b2flex">
                        <div class="ask-user b2flex">
                            <div class="ask-avatar">

                            </div>
                            <div class="ask-user-info">
                                <span class="ask-user-name"></span>
                                <span class="ask-aks-date"></span>
                            </div>
                        </div>
                        <div class="ask-pay b2flex">
                            <div class="ask-reward-pay-left">
                                <div class="ask-pay-number b2flex">
                                    <span></span>
                                </div>
                                <div class="ask-pay-type">
                                    <span></span>
                                </div>
                            </div>
                            <div class="ask-reward"></div>
                        </div>
                    </div>
                    <div class="ask-item-info">
                        <div>
                            <h2 class="ask-title"></h2>
                            <div class="ask-desc">
                                <div></div>
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
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                </div>
                <?php
            }
        ?>
    </div>
    <div class="ask-list-box" v-if="data != ''">
        <template v-if="empty">
            <div style="width:100%;height:100%"><?php echo B2_EMPTY; ?></div>
        </template>
        <div class="ask-item" v-for="(item,i) in data.data" v-else>
            <div class="ask-item-top b2flex">
                <div class="ask-user b2flex">
                    <div class="ask-avatar">
                        <a :href="item.author.link" target="_blank"><?php echo b2_get_img(array(
                            'src_data'=>':src="item.author.avatar"',
                            'class'=>array('avatar'),
                            'source_data'=>':srcset="item.author.avatar_webp"'
                        ));?></a>
                    </div>
                    <div class="ask-user-info">
                        <a :href="item.author.link" target="_blank"><span class="ask-user-name" v-text="item.author.name"></span></a>
                        <span class="ask-aks-date" v-text="item.metas._date"></span>
                    </div>
                </div>
                <div class="ask-pay b2flex" v-if="item.metas.reward">
                    <div class="ask-reward-pay-left">
                        <div class="ask-pay-number b2flex">
                            <span v-if="item.metas.reward.rewardType == 'credit'">
                                <?php echo b2_get_icon('b2-coin-line'); ?>
                                <b v-text="item.metas.reward.money"></b>
                            </span>
                            <span v-else>
                                <?php echo B2_MONEY_SYMBOL; ?>
                                <b v-text="item.metas.reward.money"></b>
                            </span>
                        </div>
                        <div class="ask-pay-type">
                            <span v-if="!item.metas.endtime" class="ask-passtime"><?php echo __('悬赏已过期','b2'); ?></span>
                            <span class="ask-passtime" v-else><?php echo sprintf(__('%s后悬赏过期'),'<b v-text="item.metas.endtime"></b>'); ?></span>
                        </div>
                    </div>
                    <div class="ask-reward"><?php echo __('悬 赏','b2'); ?></div>
                </div>
            </div>
            <div class="ask-item-info">
                <div class="b2flex">
                    <div class="ask-info-text">
                        <a :href="item.link" class="link-block" target="_blank"></a>
                        <h2 class="ask-title" v-text="item.title"></h2>
                        <div class="ask-desc" v-html="item.desc"></div>
                    </div>
                    <div class="ask-thumb" v-if="item.thumb">
                        <?php echo b2_get_img(array(
                            'src_data'=>':src="item.thumb"',
                            'class'=>array('ask-thumb-url'),
                            'source_data'=>':srcset="item.thumb_webp"'
                        ));?>
                    </div>
                </div>
                <div class="ask-inv-box b2-radius" v-if="item.last_answer.name || item.metas.inv.length > 0">
                    <span v-if="item.metas.inv.length > 0">
                        <?php echo sprintf(__('%s邀请了%s回答此问题','b2'),'<b v-text="item.author.name"></b>','<a :href="u.link" target="_blank" v-for="(u,ui) in item.metas.inv" :key="ui"><b v-text="u.name"></b></a>'); ?>
                    </span>
                    <span v-if="item.last_answer.name">
                        <?php echo sprintf(__(' 最后回答来自 %s'),'<a :href="item.last_answer.link" v-text="item.last_answer.name" target="_blank"></a>'); ?>
                    </span>
                </div>
            </div>
            <div class="ask-item-footer b2flex">
                <div class="ask-tags">
                    <a v-for="tag in item.tags" v-text="tag.name" :style="'background:'+tag.bgcolor+';color:'+tag.color" class="ask-tag-item" :href="tag.link" target="_blank"></a>
                </div>
                <div class="ask-metas">
                    <span><?php echo b2_get_icon('b2-message-3-fill'); ?><?php echo sprintf(__('%s 回答','b2'),'<b v-text="item.metas.answer_count"></b>'); ?></span>
                    <span><?php echo b2_get_icon('b2-star-fill'); ?><?php echo sprintf(__('%s 收藏','b2'),'<b v-text="item.metas.favorites"></b>'); ?></span>
                    <span><?php echo b2_get_icon('b2-eye-fill'); ?><?php echo sprintf(__('%s 阅读','b2'),'<b v-text="item.metas.views"></b>'); ?></span>
                    <span v-if="isAuthor && item.can_edit" v-cloak class="red" @click="deleteAsk(i,item.id)"><?php echo __('删除','b2'); ?></span>
                    <span v-if="isAuthor && item.can_edit" v-cloak class="red"><a :href="'<?php echo b2_get_custom_page_url('po-ask');?>?id='+item.id" target="_blank"><?php echo __('编辑','b2'); ?></a></span>
                </div>
            </div>
        </div>
    </div>
</div>