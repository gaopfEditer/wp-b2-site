<?php
use B2\Modules\Common\Circle;
    $topic_id = get_the_ID();
    $circle_id = Circle::get_circle_id_by_topic_id($topic_id);
    $allow_read = Circle::allow_read($topic_id,0);
    $circle_name = b2_get_option('normal_custom','custom_circle_name');
    $circle_owner_name = b2_get_option('normal_custom','custom_circle_owner_name');
?>

<main class="site-main circle-single" id="circle-topic-list" ref="circleSingle" data-id="<?php echo $topic_id; ?>" data-circleId="<?php echo $circle_id; ?>">
    <div class="entry-header pianli">
        <h1><?php echo get_the_title(); ?></h1>
        <time datetime="<?php echo get_the_date('c',$topic_id); ?>"><?php echo get_the_date('Y-n-j G:i:s',$topic_id); ?></time>
    </div>
    <section class="circle-topic-item gujia box" v-if="data === ''" v-cloak>
        <article class="entry-content pianli">
            <?php 
            if(isset($allow_read['allow']) && $allow_read['allow']){
                the_content(); 
            }
            ?>
        </article>
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
    <section v-for="(item,ti) in data" :key="item.topic_id" v-else :class="'circle-topic-item box' + ' circle-topic-item-'+item.topic_id" v-cloak>
        <?php get_template_part('TempParts/circle/circle-topic-content'); ?>
        <?php get_template_part( 'TempParts/circle/circle-topic-footer');?>
        <?php get_template_part( 'TempParts/circle/circle-comments');?>
    </section>
    <?php get_template_part( 'TempParts/circle/circle-comment-and-answer');?>
    <div id="circle-join-box" :class="['modal join-circle-box',{'show-modal':showJoin}]" v-cloak v-if="b2CirclePostBox">
        <div v-if="!b2CirclePostBox.currentUser.inCircle" class="join-circle-pay-money modal-content">
            <div class="b2-color"><?php echo b2_get_icon('b2-shield-user-line'); ?></div>
            <h2><?php echo sprintf(__('您还未加入该%s','b2'),$circle_name); ?></h2>
            <span class="pay-close" @click="showJoin = false">×</span>
            <div class="pay-box-content">
                <div v-if="b2CirclePostBox.currentUser.currentCircleRole.type === 'free'">
                    <p v-if="b2CirclePostBox.currentUser.currentCircleRole.data === 'check'"><?php echo sprintf(__('提交申请，管理员审核之后方可加入%s','b2'),$circle_name); ?></p>
                    <p v-else><?php echo sprintf(__('免费入%s，可阅读更多%s内话题','b2'),$circle_name,$circle_name); ?></p>
                </div>
                <div class="po-topic-box-tips-button">
                    <button v-if="b2CirclePostBox.currentUser.currentCircleRole.data === 'check'" :class="b2CirclePostBox.currentUser.currentCircleRole.status === 'pending' ? 'pending' : ''" :disabled="b2CirclePostBox.currentUser.currentCircleRole.status === 'pending'" @click="b2CirclePostBox.joinCircle()">
                        <span v-if="b2CirclePostBox.currentUser.currentCircleRole.status === 'none'"><?php echo sprintf(__('提交加入%s申请','b2'),$circle_name); ?></span>
                        <span v-else><?php echo __('您已成功提交申请，等待审核中...','b2'); ?></span>
                    </button>
                    <button v-else-if="b2CirclePostBox.currentUser.currentCircleRole.data === 'free'" @click="b2CirclePostBox.joinCircle()" :class="b2CirclePostBox.joinLocked ? 'b2-loading' : ''" :disabled="b2CirclePostBox.joinLocked"><?php echo sprintf(__('加入%s','b2'),$circle_name); ?></button>
                    <div v-else-if="b2CirclePostBox.currentUser.currentCircleRole.type === 'lv'">
                        <p><?php echo sprintf(__('专属%s，您需要成为以下会员组的成员，方可加入%s','b2'),$circle_name,$circle_name); ?></p>
                        <div class="circle-lv-list">
                            <p v-for="item in b2CirclePostBox.currentUser.currentCircleRole.data.list" v-html="item"></p>
                        </div>
                        <div class="join-circle-button-pay">
                            <button @click="b2CirclePostBox.joinCircle()" :disabled="!b2CirclePostBox.currentUser.currentCircleRole.data.allow_join">
                                <span v-if="b2CirclePostBox.currentUser.currentCircleRole.data.allow_join"><?php echo sprintf(__('加入%s','b2'),$circle_name); ?></span>
                                <span v-else><?php echo __('无权加入','b2'); ?></span>
                            </button>
                        </div>
                    </div>
                    <div v-else>
                        <label v-for="(item,key) in b2CirclePostBox.currentUser.currentCircleRole.data" :key="key" :class="b2CirclePostBox.join.picked === item.type ? 'picked' : ''" v-if="item.money">
                            <button class="empty" @click="b2CirclePostBox.join.picked = item.type"><?php echo B2_MONEY_SYMBOL; ?><span v-text="item.money"></span>/<span v-text="item.name"></span></button>
                        </label>
                        <div class="join-circle-button-pay">
                            <button @click="b2CirclePostBox.joinPay()"><?php echo sprintf(__('支付加入%s','b2'),$circle_name); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
