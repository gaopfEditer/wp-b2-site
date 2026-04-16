<?php
$paged = get_query_var('paged');
$paged = $paged ? $paged : 1;
$count = b2_get_option('circle_main','topic_per_count');
$offset = ($paged -1)*$count;

$gc = (int)get_option('b2_circle_default');
$term = get_queried_object();
$circle_id = isset($term->term_id) ? (int)$term->term_id : $gc;
$circle_name = b2_get_option('normal_custom','custom_circle_name');
$circle_owner_name = b2_get_option('normal_custom','custom_circle_owner_name');

$page_name = get_query_var('b2_page');
$circle_link = b2_get_option('normal_custom','custom_circle_link');

?>
<div class="circle-content box b2-radius topic-mg-t" id="circle-topic-list" ref="paged" data-paged="<?php echo $paged; ?>">
    <div class="circle-zz mobile-show" v-show="b2CirclePostBox && b2CirclePostBox.showPoBox && !people.is" @click="b2CirclePostBox.showPoBox = false" v-cloak></div>
    <div id="public">
        <div class="my-circle-list">
            <?php if($page_name != $circle_link.'-people') {?>
                <div>
                    <button :class="['text',{'picked b2-color':circle.current === 'default'}]" @click.stop="pickedCircle('default',<?php echo $gc; ?>)"><?php echo __('广场','b2'); ?></button>
                </div>
                <div>
                    <button :class="['text',{'picked b2-color':circle.current === 'join'}]" @click.stop="showCircleListBox('join')">
                        <span v-if="circle.picked.type === 'join' && circle.picked.id !== ''" v-cloak>
                            <img :src="circle.join[circle.picked.id].icon" /><b v-text="circle.join[circle.picked.id].name"></b>
                        </span>
                        <span v-else><?php echo sprintf(__('加入的%s','b2'),$circle_name).'</span>'; ?>
                    </button>
                    <div class="circle-my-create" v-cloak v-show="circle.current === 'join' && circle.showBox === 'join'">
                        <div v-if="Object.keys(circle.join).length == 0" class="my-circle-empty">
                            <?php echo sprintf(__('没有加入%s','b2'),$circle_name); ?>
                        </div>
                        <div v-else>
                            <ul>
                                <li v-for="item in circle.join" @click.stop="pickedCircle('join',item.id)" :key="item.id">
                                    <div>
                                        <img :src="item.icon" />
                                        <span v-text="item.name"></span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div>
                    <button :class="['text',{'picked b2-color':circle.current === 'created'}]" @click.stop="showCircleListBox('created')">
                        <span v-if="circle.picked.type === 'created' && circle.picked.id !== ''" v-cloak>
                            <img :src="circle.created[circle.picked.id].icon" /><b v-text="circle.created[circle.picked.id].name"></b>
                        </span>
                        <span v-else><?php echo sprintf(__('创建的%s','b2'),$circle_name); ?></span>
                    </button>
                    <div class="circle-my-create" v-cloak v-if="circle.current === 'created' && circle.showBox === 'created'" @click.stop="">
                        <div v-if="Object.keys(circle.created).length == 0" class="my-circle-empty">
                            <?php echo sprintf(__('没有创建%s','b2'),$circle_name); ?>
                        </div>    
                        <div v-else>
                            <ul>
                                <li v-for="item in circle.created" @click.stop="pickedCircle('created',item.id)" :key="item.id">
                                    <div>
                                        <img :src="item.icon" />
                                        <span v-text="item.name"></span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div>
                    <button :class="['text',{'picked b2-color':circle.current === 'my'}]" @click.stop="showMyTopic();circle.current = 'my'"><?php echo __('我的话题','b2'); ?></button>
                </div>
            <?php } ?>
        </div>
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
                <button class="text" @click.stop="topicFliter.show = !topicFliter.show"><?php echo b2_get_icon('b2-filter-2-line'); ?><span><?php echo __('筛选','b2'); ?></span></button>
                <div class="topic-drop-box" v-cloak v-if="topicFliter.show" @click.stop="">
                    <ul>
                        <li><button :class="{'picked':topicFliter.orderBy === 'date'}" @click="pickedOrder('date')"><?php echo __('最新话题','b2'); ?></button></li>
                        <li><button :class="{'picked':topicFliter.orderBy === 'best'}" @click="pickedOrder('best')"><?php echo __('精华','b2'); ?></button></li>
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
                for ($i=0; $i < 5; $i++) { 
                    ?>
                    <section class="circle-topic-item gujia">
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
        <div v-if="(data !== '' && !locked) || reload" class="circle-list-box">
            <div v-if="data.length == 0" class="topic-list-empty mg-t" v-cloak>
                <?php echo b2_get_icon('b2-notification-badge-line'); ?>
                <p><?php echo __('没有话题','b2'); ?></p>
            </div>
            <section v-for="(item,ti) in data" :key="item.topic_id" v-else :class="'circle-topic-item ' + ' circle-topic-item-'+item.topic_id">
                <template v-if="item.sticky === 1">
                    <div class="topic-sticky">
                        <div class="topic-sticky-info">
                            <div :class="item.attachment.image.length > 0 ? 'has-image' : ''">
                                <div class="topic-sticky-title">
                                    <h2>
                                        <span class="topic-sticky-icon"><?php echo b2_get_icon('b2-upload-fill').__('置顶','b2'); ?></span>
                                        <a :href="item.link" target="_blank">
                                            <span v-if="item.title" v-text="item.title"></span>
                                            <span v-else-if="item.content" v-text="item.content"></span>
                                            <span v-else>
                                                <b><?php echo sprintf(__('专属内容，加入%s后方可查看！','b2'),$circle_name); ?></b>
                                            </span>
                                        </a>
                                    </h2>
                                </div>
                                <div v-if="item.attachment.image.length > 0" class="topic-sticky-img-box b2-radius">
                                    <?php echo b2_get_img(array(
                                        'source_data'=>':srcset="item.attachment.image[0].thumb_webp"',
                                        'src_data'=>':src="item.attachment.image[0].thumb"'
                                    ));?>
                                </div>
                            </div>
                            <?php get_template_part( 'TempParts/circle/circle-topic-footer');?>
                            <?php get_template_part( 'TempParts/circle/circle-comments');?>
                        </div>
                    </div>
                </template>
                <template v-else>
                    <?php get_template_part('TempParts/circle/circle-topic-content'); ?>
                    <?php get_template_part( 'TempParts/circle/circle-topic-footer');?>
                    <?php get_template_part( 'TempParts/circle/circle-comments');?>
                </template>
            </section>
            <div class="topic-loading-more-button" v-if="reload && data.length != 0">
                <?php echo __('加载中...','b2'); ?>
            </div>
            <div class="topic-loading-more-button" v-else-if="paged >= pages && data.length != 0">
                <?php echo __('我是有底线的！','b2'); ?>
            </div>
        </div>
        <div id="circle-join-box" :class="['modal join-circle-box',{'show-modal':showJoin}]" v-cloak>
            <div v-if="b2CirclePostBox && !b2CirclePostBox.currentUser.inCircle" class="join-circle-pay-money modal-content">
                <div class="b2-color"><?php echo b2_get_icon('b2-shield-user-line'); ?></div>
                <h2><?php echo sprintf(__('您还未加入该%s','b2'),$circle_name); ?></h2>
                <span class="pay-close" @click="showJoin = false">×</span>
                <div class="pay-box-content">
                    <div v-if="b2CirclePostBox.currentUser.currentCircleRole.type === 'free'">
                        <p v-if="b2CirclePostBox.currentUser.currentCircleRole.data === 'check'"><?php echo sprintf(__('提交申请，管理员审核之后方可加入%s','b2'),$circle_name); ?></p>
                        <p v-else><?php echo sprintf(__('免费加入%s，可阅读更多%s内话题','b2'),$circle_name,$circle_name); ?></p>
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
                                <p v-for="(item,index) in b2CirclePostBox.currentUser.currentCircleRole.data.list" v-html="item" :key="index"></p>
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
    </div>
    <div class="circle-list">
        <ul>
            <?php 
                $_pages = 0;
                $args = array(
                    'post_type' => 'circle',
                    'orderby'  => 'date',
                    'order'=>'DESC',
                    'post_status'=>'publish',
                    'posts_per_page'=>$count,
                    'offset'=>$offset,
                    'paged'=>$paged,
                    'suppress_filters' => true
                );

                $topic_query = new \WP_Query( $args );

                if ( $topic_query->have_posts()) {
                    $_pages = $topic_query->max_num_pages;
                    while ( $topic_query->have_posts() ) {
                        $topic_query->the_post();

                        get_template_part( 'TempParts/circle/circle','item');

                    }
                    
                }
                wp_reset_postdata();
            ?>
        </ul>
        <?php
            $pagenav = b2_pagenav(array('pages'=>$_pages,'paged'=>$paged)); 
            if($pagenav){
                echo '<div class="b2-pagenav collection-nav post-nav box">'.$pagenav.'</div>';
            }
        ?>
    </div>
    <?php get_template_part( 'TempParts/circle/circle-comment-and-answer');?>
</div>