<?php
get_header();
$circle_name = b2_get_option('normal_custom','custom_circle_name');
?>
<div class="b2-single-content wrapper circle-archive circle-admin-page" id="circle-topic-list" ref="circleAdmin">
    <div id="primary-home" class="wrapper content-area">
        <main class="site-main">
            <div class="circle-content" id="circle-topic-list" v-show="admin.type === 'topic'">
                <div class="topic-mg-t public box b2-radius">
                    <div class="topic-type-menu">
                        <ul>
                            <li>
                                <button @click="pickedType('all')" :class="topicFliter.type === 'all' ? 'picked' : ''"><?php echo __('全部','b2'); ?></button>
                            </li>
                            <li>
                                <button @click="pickedType('say')" :class="topicFliter.type === 'say' ? 'picked' : ''"><?php echo __('我说','b2'); ?></button>
                            </li>
                            <li>
                                <button @click="pickedType('ask')" :class="topicFliter.type === 'ask' ? 'picked' : ''"><?php echo __('提问','b2'); ?></button>
                            </li>
                            <li>
                                <button @click="pickedType('vote')" :class="topicFliter.type === 'vote' ? 'picked' : ''"><?php echo __('投票','b2'); ?></button>
                            </li>
                            <li>
                                <button @click="pickedType('guess')" :class="topicFliter.type === 'guess' ? 'picked' : ''"><?php echo __('你猜','b2'); ?></button>
                            </li>
                        </ul>
                        <div class="topic-drop">
                            <button class="text" @click.stop="topicFliter.show = !topicFliter.show"><?php echo b2_get_icon('b2-filter-2-line'); ?><span><?php echo __('话题筛选','b2'); ?></span></button>
                            <div class="topic-drop-box" v-cloak v-if="topicFliter.show" @click.stop="">
                                <ul>
                                    <li><button :class="{'picked':topicFliter.orderBy === 'date'}" @click="pickedOrder('date')"><?php echo __('最新话题','b2'); ?></button></li>
                                    <li><button :class="{'picked':topicFliter.orderBy === 'up'}" @click="pickedOrder('up')"><?php echo __('最多点赞','b2'); ?></button></li>
                                    <li><button :class="{'picked':topicFliter.orderBy === 'comment'}" @click="pickedOrder('comment')"><?php echo __('最多讨论','b2'); ?></button></li>
                                </ul>
                                <ul>
                                    <li><button :class="{'picked':topicFliter.file === 'all'}" @click="pickedFile('all')"><?php echo __('全部','b2'); ?></button></li>
                                    <li><button :class="{'picked':topicFliter.file === 'image'}" @click="pickedFile('image')"><?php echo __('图片','b2'); ?></button></li>
                                    <li><button :class="{'picked':topicFliter.file === 'video'}" @click="pickedFile('video')"><?php echo __('视频','b2'); ?></button></li>
                                    <li><button :class="{'picked':topicFliter.file === 'file'}" @click="pickedFile('file')"><?php echo __('文件','b2'); ?></button></li>
                                    <li><button :class="{'picked':topicFliter.file === 'card'}" @click="pickedFile('card')"><?php echo __('卡片','b2'); ?></button></li>
                                </ul>
                                <ul>
                                    <li><button :class="{'picked':topicFliter.role === 'all'}" @click="pickedRole('all')"><?php echo __('全部','b2'); ?></button></li>
                                    <li><button :class="{'picked':topicFliter.role === 'public'}" @click="pickedRole('public')"><?php echo __('公开话题','b2'); ?></button></li>
                                    <li><button :class="{'picked':topicFliter.role === 'login'}" @click="pickedRole('login')"><?php echo __('登录可见','b2'); ?></button></li>
                                    <li><button :class="{'picked':topicFliter.role === 'comment'}" @click="pickedRole('comment')"><?php echo __('评论可见','b2'); ?></button></li>
                                    <li><button :class="{'picked':topicFliter.role === 'money'}" @click="pickedRole('money')"><?php echo __('付费阅读','b2'); ?></button></li>
                                    <li><button :class="{'picked':topicFliter.role === 'credit'}" @click="pickedRole('credit')"><?php echo __('积分阅读','b2'); ?></button></li>
                                    <li><button :class="{'picked':topicFliter.role === 'lv'}" @click="pickedRole('lv')"><?php echo __('限制等级','b2'); ?></button></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="circle-topic-list">
                    <div id="circle-list-gujia" :class="(data !== '' && !locked) || reload ? '' :'show'" ref="listGujia">
                        <?php 
                            for ($i=0; $i < 6; $i++) { 
                                ?>
                                <section class="circle-topic-item gujia box b2-radius mg-t">
                                    <div class="topic-header">
                                        <div class="topic-header-left">
                                            <div class="topic-avatar bg"></div>
                                            <div class="topic-name">
                                                <span class="bg"></span>
                                                <p class="bg"></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="topic-content">
                                        <p class="bg"></p>
                                        <p class="bg"></p>
                                        <p class="bg"></p>
                                        <p class="bg"></p>
                                        <p class="bg"></p>
                                    </div>
                                    <div class="topic-footer">
                                        <div class="topic-footer-left">
                                            <span class="bg"></span>
                                            <span class="bg"></span>
                                        </div>
                                        <div class="topic-footer-right">
                                            <span class="bg"></span>
                                        </div>
                                    </div>
                                </section>
                                <?php
                            }
                        ?>
                    </div>
                    <div v-if="(data !== '' && !locked) || reload" v-cloak>
                        <div v-if="data.length == 0" class="topic-list-empty box b2-radius mg-t">
                            <?php echo b2_get_icon('b2-notification-badge-line'); ?>
                            <p><?php echo sprintf(__('该%s下没有待审话题','b2'),$circle_name); ?></p>
                        </div>
                        <section v-for="(item,ti) in data" :key="item.topic_id" v-else :class="'circle-topic-item box b2-radius mg-t' + ' circle-topic-item-'+item.topic_id">
                            <?php get_template_part('TempParts/circle/circle-topic-content'); ?>
                            <?php get_template_part( 'TempParts/circle/circle-topic-footer');?>
                            <?php get_template_part( 'TempParts/circle/circle-comments');?>
                        </section>
                        <div class="topic-loading-more-button" v-if="reload">
                            <?php echo __('加载中...','b2'); ?>
                        </div>
                        <div class="topic-loading-more-button" v-else-if="paged >= pages">
                        <?php echo __('我是有底线的！','b2'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<?php
get_footer();