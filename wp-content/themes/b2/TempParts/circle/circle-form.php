<?php
use B2\Modules\Common\User;
use B2\Modules\Common\Circle;

$gc = (int)get_option('b2_circle_default');
$circle_id = isset($_GET['circle_id']) ? (int)($_GET['circle_id']) : 0;
if($circle_id){
    $term = get_term_by('id',$circle_id , 'circle_tags');
}else{
    $term = get_queried_object();
    $circle_id = isset($term->term_id) ? (int)$term->term_id : $gc;
}
$circle_name = b2_get_option('normal_custom','custom_circle_name');
$circle_owner_name = b2_get_option('normal_custom','custom_circle_owner_name');
$circle_member = b2_get_option('normal_custom','custom_circle_member_name');
$newsflashes_name = b2_get_option('normal_custom','custom_newsflashes_name');

$cats = b2_get_option('circle_main','circle_home_cats');
$cats = !empty($cats) ? count($cats) : 0;

?>
<div class="circle-top">
    <div id="po-topic-box" class="box b2-radius" >
        <div ref="poFormBox" class="po-topic-box-in">
        <div v-show="topCats.circle_square == 0 && parseInt(circle.picked) == <?php echo $gc; ?>" v-cloak>
            <h2 class="b2flex"><span><?php echo __('快速进入','b2'); ?></span><span><a href="<?php echo b2_get_custom_page_url('all-circles');?>" target="_blank"><b><?php echo __('全部圈子','b2').'</b>'.b2_get_icon('b2-arrow-right-s-line'); ?></a></span></h2>
            <div class="circle-home-cats b2flex">
                <div :class="['c-h-item b2-radius b2flex',item.type]" v-for="item,i in (topCats.data.length > 0 ? topCats.data : <?php echo $cats; ?>)" :key="i" v-cloak @click="go(i)">
                    <img :src="item.icon" class="c-t-icon"/>
                    <span v-if="item.type == 'lv'" class="c-t-mark lv"><?php echo __('专属','b2'); ?></span>
                    <span v-if="item.type == 'money'" class="c-t-mark"><?php echo __('付费','b2'); ?></span>
                    <div>
                        <div class="c-t-title" v-text="topCats.data.length > 0 ? item.name : ''"></div>
                        <div class="c-t-meta">
                            <span v-if="topCats.data.length"><?php echo $circle_member; ?>&nbsp;{{item.user_count}}</span>
                            <span v-if="topCats.data.length"><?php echo __('话题','b2'); ?>&nbsp;{{item.topic_count}}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-show="!(topCats.circle_square == 0 && circle.picked == <?php echo $gc; ?>)">
            <div class="po-topic">
                <!-- <div class="cirlce-head-bg">
                    <div :style="'background-image:url('+(getCircleData(circle.picked,'icon') ? getCircleData(circle.picked,'icon') : '<?php echo B2_THEME_URI; ?>/Assets/fontend/images/default-img.jpg')+')'"></div>
                </div> -->
                <div class="circle-info">
                    <div class="circle-info-in">
                        <div class="circle-info-left">
                            <img :src="getCircleData(circle.picked,'icon') ? getCircleData(circle.picked,'icon') : '<?php echo B2_DEFAULT_IMG; ?>'" class="b2-radius"/>
                            <h1>
                                <p @click="showAdmin"><b v-text="getCircleData(circle.picked,'name')"><?php echo $term ? $term->name : __('广场','b2'); ?></b><span class="mobile-show"><i :class="['b2font b2-arrow-right-s-line',{'picked':showAdminBox}] "></i></span></p>
                                <a :href="getCircleData(circle.picked,'admin','link')" target="_blank">
                                    <span v-text="getCircleData(circle.picked,'admin','name')"></span>
                                    <span><?php echo sprintf(__('(%s)','b2'),$circle_owner_name); ?></span>
                                </a>
                            </h1>
                            <div class="mobile-show po-top-submit">
                                <button class="empty" @click="showPoBox = !showPoBox"><?php echo __('发布话题','b2'); ?></button>
                            </div>
                        </div>
                        <div class="circle-admin-info" v-show="showAdminBox" v-cloak>
                            <div>
                                <a :href="'<?php echo b2_get_custom_page_url('circle-users'); ?>?circle_id='+circle.picked" target="_blank" class="link-block"></a>
                                <b v-text="getCircleData(circle.picked,'user_count')"></b>
                                <span><?php echo $circle_member; ?></span>
                                <?php echo b2_get_icon('b2-parent-line'); ?>
                                
                            </div>
                            <div>
                                <b v-text="getCircleData(circle.picked,'topic_count')"></b>
                                <span><?php echo __('话题','b2'); ?></span>
                                <?php echo b2_get_icon('b2-chat-smile-3-line'); ?>
                            </div>
                            <div>
                                <a class="link-block" href="<?php echo b2_get_custom_page_url('all-circles');?>" target="_blank"></a>
                                <b v-text="getCircleData(circle.picked,'circle_count')"></b>
                                <span class="b2-color"><?php echo sprintf(__('所有%s','b2'),$circle_name); ?></span>
                                <?php echo b2_get_icon('b2-donut-chart-fill'); ?>    
                            </div>
                            <div v-if="currentUser.isCircleAdmin || currentUser.isAdmin" v-cloak>
                                <a class="link-block" :href="'<?php echo b2_get_custom_page_url('circle-topics'); ?>?circle_id='+circle.picked" target="_blank"></a>
                                <b style="font-style:normal;font-weight:400;font-size:12px"><?php echo __('待审','b2'); ?></b>
                                <span><?php echo __('管理','b2'); ?></span>
                                <?php echo b2_get_icon('b2-settings-3-line'); ?>    
                            </div>
                            <span class="mobile-show close-admin-box"><i class="b2font b2-close-line" @click="showAdmin"></i></span>
                        </div>
                    </div>
                    <p v-text="getCircleData(circle.picked,'desc')" class="circle-desc b2-radius" ></p>
                </div>
                <div class="po-form-box " :id="showPoBox ? 'show-form' : 'show-form-hidden'">
                    <div class="po-form-box-in" >
                        <div class="mobile-show b-b mobile-po-top">
                            <span><?php echo __('发布话题','b2'); ?></span>
                            <span><i class="b2font b2-close-line" @click="showPoBox = !showPoBox"></i></span>
                        </div>
                        <div class="po-topic-box-tips b2-radius" v-if="!login || !currentUser.allowCreateTopic || !currentUser.inCircle || currentUser.darkRoom || !currentUser.canPost" v-show="showPoBox" v-cloak>
                            <div class="b2-color"><?php echo b2_get_icon('b2-shield-user-line'); ?></div>
                            <div v-if="!login">
                                <h2>
                                    <?php echo __('您还未登录','b2'); ?>
                                </h2>
                                <p><?php echo __('登录后可阅读更多话题','b2'); ?></p>
                                <div class="po-topic-box-tips-button">
                                    <button class="empty" @click="loginAc(1)"><?php echo __('登录','b2'); ?></button>
                                    <button @click="loginAc(2)"><?php echo __('快速注册','b2'); ?></button>
                                </div>
                            </div>
                            <div v-else-if="currentUser.darkRoom">
                                <h2>
                                    <?php echo __('您被关小黑屋了','b2'); ?>
                                </h2>
                                <div>
                                    <?php echo __('被关小黑屋的用户无法发布话题，请汲取教训，出狱后再进行互动','b2'); ?>
                                </div>
                            </div>
                            <div v-else-if="!currentUser.allowCreateTopic">
                                <h2>
                                    <?php echo sprintf(__('您没有权限在%s中发布话题','b2'),$circle_name); ?>
                                </h2>
                                <div>
                                    <p><?php echo __('您需要通过积分提升您的等级，或者成为我们的会员方可获得话题发布权限','b2'); ?></p>
                                </div>
                                <div class="po-topic-box-tips-button">
                                    <a class="button empty" href="<?php echo b2_get_custom_page_url('gold');?>" target="_blank"><?php echo __('积分升级','b2'); ?></a>
                                    <a class="button empty" href="<?php echo b2_get_custom_page_url('vips');?>" target="_blank"><?php echo __('变更会员','b2'); ?></a>
                                </div>
                            </div>
                            <div v-else-if="!currentUser.inCircle" class="join-circle-pay-money">
                                <h2>
                                    <?php echo sprintf(__('您还未加入该%s','b2'),$circle_name); ?>
                                </h2>
                                <div>
                                    <div v-if="currentUser.currentCircleRole.type === 'free'">
                                        <p v-if="currentUser.currentCircleRole.data === 'check'"><?php echo sprintf(__('提交申请，管理员审核之后方可加入%s','b2'),$circle_name); ?></p>
                                        <p v-else><?php echo sprintf(__('免费入加入%s，可阅读更多%s内话题','b2'),$circle_name,$circle_name); ?></p>
                                    </div>
                                    <div class="po-topic-box-tips-button">
                                        <button v-if="currentUser.currentCircleRole.data === 'check'" :class="currentUser.currentCircleRole.status === 'pending' ? 'pending' : ''" :disabled="currentUser.currentCircleRole.status === 'pending'" @click="joinCircle()">
                                            <span v-if="currentUser.currentCircleRole.status === 'none'"><?php echo sprintf(__('提交加入%s申请','b2'),$circle_name); ?></span>
                                            <span v-else><?php echo __('您已成功提交申请，等待审核中...','b2'); ?></span>
                                        </button>
                                        <button v-else-if="currentUser.currentCircleRole.data === 'free'" @click="joinCircle()" :class="joinLocked ? 'b2-loading' : ''" :disabled="joinLocked"><?php echo sprintf(__('加入%s','b2'),$circle_name); ?></button>
                                        <div v-else-if="currentUser.currentCircleRole.type === 'lv'">
                                            <p><?php echo sprintf(__('专属%s，您需要成为以下会员组的成员，方可加入%s','b2'),$circle_name,$circle_name); ?></p>
                                            <div class="circle-lv-list">
                                                <p v-for="(item,_lv) in currentUser.currentCircleRole.data.list" v-html="item" :key="_lv"></p>
                                            </div>
                                            <div class="join-circle-button-pay">
                                                <button @click="joinCircle()" :disabled="!currentUser.currentCircleRole.data.allow_join">
                                                    <span v-if="currentUser.currentCircleRole.data.allow_join"><?php echo sprintf(__('加入%s','b2'),$circle_name); ?></span>
                                                    <span v-else><?php echo __('无权加入','b2'); ?></span>
                                                </button>
                                            </div>
                                        </div>
                                        <div v-else>
                                            <label v-for="(item,key) in currentUser.currentCircleRole.data" :key="key" :class="join.picked === item.type ? 'picked' : ''" v-if="item.money">
                                                <button class="empty" @click="join.picked = item.type"><?php echo B2_MONEY_SYMBOL; ?><span v-text="item.money"></span>/<span v-text="item.name"></span></button>
                                            </label>
                                            <div class="join-circle-button-pay">
                                                <button @click="joinPay()"><?php echo sprintf(__('支付加入%s','b2'),$circle_name); ?></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else-if="!currentUser.canPost">
                                <h2>
                                    <?php echo __('您有过多待审话题','b2'); ?>
                                </h2>
                                <div>
                                    <?php echo sprintf(__('您有%s个待审话题未审核，请稍后再发表话题','b2'),'<span v-text="currentUser.allowPendings"></span>'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="po-topic-top" @click="showPoBox = true">
                            <div class="po-topic-top-left">
                                <span class="po-topic-avatar">
                                    <img :src="userData.avatar" v-if="userData.avatar" class="avatar"/>
                                </span>
                                <div class="po-ask">
                                    <span><?php echo __('在','b2'); ?></span>
                                    <button @click="circle.show = !circle.show">
                                        <span>
                                            <?php echo b2_get_icon('b2-hashtag'); ?><b v-text="getCircleData(circle.picked,'name')"></b>
                                        </span>
                                        <?php echo b2_get_icon('b2-arrow-right-s-line'); ?>
                                    </button>
                                    <span v-if="topicType === 'ask'" v-cloak><?php echo __('提问：','b2'); ?></span>
                                    <span v-else-if="topicType === 'vote'" v-cloak><?php echo __('投票：','b2'); ?></span>
                                    <span v-else-if="topicType === 'guess'" v-cloak><?php echo __('你猜：','b2'); ?></span>
                                    <span v-else><?php echo __('说：','b2'); ?></span>
                                </div>
                            </div>
                            <div class="po-topic-top-right" v-cloak>
                                <div class="mobile-show" v-cloak>
                                    <button class="picked" @click="topicTypeBox = !topicTypeBox" v-if="topicType === 'say'"><?php echo b2_get_icon('b2-double-quotes-l').__('我说','b2'); ?></button>
                                    <button class="picked" @click="topicTypeBox = !topicTypeBox" v-else-if="topicType === 'ask'" v-cloak><?php echo b2_get_icon('b2-question-line').__('提问','b2'); ?></button>
                                    <button class="picked" @click="topicTypeBox = !topicTypeBox" v-else-if="topicType === 'vote'" v-cloak><?php echo b2_get_icon('b2-bar-chart-fill').__('投票','b2'); ?></button>
                                    <button class="picked" @click="topicTypeBox = !topicTypeBox" v-else-if="topicType === 'guess'" v-cloak><?php echo b2_get_icon('b2-heart-pulse-line').__('你猜','b2'); ?></button>
                                </div>
                                <div :class="topicTypeBox ? 'show-topic-box' : 'hidden-topic-box'">
                                    <button :class="topicType === 'say' ? 'picked' : ''" @click="topicType = 'say';topicTypeBox = false"><?php echo b2_get_icon('b2-double-quotes-l').__('我说','b2'); ?></button>
                                    <button :class="topicType === 'ask' ? 'picked' : ''" @click="topicType = 'ask';topicTypeBox = false" v-if="currentUser.topicTypeRole.ask" v-cloak><?php echo b2_get_icon('b2-question-line').__('提问','b2'); ?></button>
                                    <button :class="topicType === 'vote' ? 'picked' : ''" @click="topicType = 'vote';topicTypeBox = false" v-if="currentUser.topicTypeRole.vote" v-cloak><?php echo b2_get_icon('b2-bar-chart-fill').__('投票','b2'); ?></button>
                                    <button :class="topicType === 'guess' ? 'picked' : ''" @click="topicType = 'guess';topicTypeBox = false" v-if="currentUser.topicTypeRole.guess" v-cloak><?php echo b2_get_icon('b2-heart-pulse-line').__('你猜','b2'); ?></button>
                                </div>
                            </div>
                        </div>
                        <div class="po-topic-textarea b2-radius" ref="textareaTopic" data-circle="<?php echo $circle_id; ?>" data-gc="<?php echo $gc; ?>">
                            <textarea type="text" :placeholder="topicType === 'guess' || role.see !== 'public' ? '<?php echo __('标题（必填）','b2'); ?>' : '<?php echo __('标题（选填）','b2'); ?>'" :class="['topic-title',{'required':topicType === 'guess' || role.see !== 'public'}]" ref="textarea_title"></textarea>
                            <textarea @input="changeText()" ref="textarea_box" :maxlength="character.max" :minlength="character.min" :placeholder="placeholder()" class="topic-content"></textarea>
                            <div class="po-topic-textarea-footer">
                                <?php 
                                    $smile = B2\Modules\Common\Comment::smilies_reset(true);
                                    $html = '';
                                    foreach ($smile as $k => $v) {
                                        $html .= '<button class="text smily-button" @click="addSmile(\''.$k.'\')">'.$v.'</button>';
                                    }
                                ?>
                                <div class="smile-box">
                                    <button class="button-sm" @click.stop="smileShow = !smileShow">
                                        <span v-if="smileShow"><?php echo b2_get_icon('b2-emotion-laugh-line'); ?></span>
                                        <span v-else v-cloak><?php echo b2_get_icon('b2-emotion-line'); ?></span>
                                    </button>
                                    <div :class="['comment-smile-box',{'b2-show':smileShow}]" v-cloak @click.stop="">
                                        <?php echo $html; ?>
                                    </div>
                                </div>
                                <div class="po-topic-textarea-character"><div :class="['editor-character', {'character-over':character.over}]" v-html="makesvg(percentage(character.length),nFormatter(character.length),character.length)"></div></div>
                            </div>
                            <div class="po-topic-circle" v-show="circle.show" ref="circleList" v-cloak>
                                <p class="po-topic-circle-desc">
                                    <span><?php echo sprintf(__('可以选择您加入或创建的%s','b2'),$circle_name,$circle_name); ?></span>
                                    <span class="circle-more-right b2-color"><a href="<?php echo b2_get_custom_page_url('all-circles');?>"><?php echo __('更多','b2'); ?><?php echo b2_get_icon('b2-arrow-right-s-line'); ?></a></span>
                                </p>
                                <ul v-if="circle.list !== ''">
                                    <li v-for="item in circle.list" :class="item.id === circle.picked ? 'picked' : ''" :key="item.id">
                                        <div class="circle-list-in" @click="circlePicked(item.id)">
                                            <div class="circle-list-image">
                                                <span>
                                                    <img :src="item.icon" v-if="item.icon"/>
                                                </span>
                                            </div>
                                            <div class="circle-list-name">
                                                <span v-text="item.name"></span>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="topic-po-tools">
                            <div class="circle-vote b2-radius" v-show="topicType === 'ask'" v-cloak>
                                <div class="vote-type">
                                    <button :class="ask.type === 'someone' ? 'picked' : ''" @click="ask.type = 'someone'"><?php echo __('指定','b2'); ?></button>
                                    <button :class="ask.type === 'everyone' ? 'picked' : ''" @click="ask.type = 'everyone'"><?php echo __('所有人','b2'); ?></button>
                                </div>
                                <div class="ask-box-search-user" v-if="ask.type === 'someone'" v-cloak>
                                    <label class="ask-search-user-input">
                                        <ul>
                                            <li v-for="(item,index) in ask.pickedList" @click.stop.prevent="" class="search-user-li" :key="index">
                                                <div>
                                                <?php echo b2_get_img(array(
                                                    'source_data'=>':srcset="item.avatar_webp"',
                                                    'src_data'=>':src="item.avatar"',
                                                    'class'=>array('avatar')
                                                ));?>
                                                <span v-text="item.name"></span><b @click.stop.prevent="removePickedUser(index)"><?php echo b2_get_icon('b2-close-line'); ?></b></div>
                                            </li>
                                            <li v-if="!ask.hiddenInput"><input type="text" autocomplete="off" name="user" placeholder="<?php echo __('搜索用户','b2'); ?>" v-model="ask.userInput" @focus="ask.focus = true" @input="searchUser" @click.stop=""></li>
                                        </ul>
                                    </label>
                                    <div class="search-users" v-cloak v-show="ask.focus">
                                        <div v-if="ask.userList.length > 0" class="search-users-list">
                                            <ul>
                                                <li v-for="item in ask.userList" @click.stop.prevent="pickedUser(item.id,item.name,item.avatar)" :key="item.id">
                                                    <?php echo b2_get_img(array(
                                                        'source_data'=>':srcset="item.avatar_webp"',
                                                        'src_data'=>':src="item.avatar"',
                                                        'class'=>array('avatar')
                                                    ));?>
                                                    <span v-text="item.name"></span>（<b v-text="'ID:'+item.id"></b>）
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="search-users-none search-users-list" v-else>
                                            <?php echo __('请输入您要添加的用户昵称','b2'); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="ask-reward-input" v-else>
                                    <div class="zhanwei"><span><?php echo __('@所有人提问','b2'); ?></span></div>
                                </div>
                                <p><?php echo __('您可以指定向某些人或者所有人提问','b2'); ?></p>
                                <div class="ask-credit">
                                    <div class="vote-type">
                                        <button :class="ask.reward === 'credit' ? 'picked' : ''" @click="ask.reward = 'credit'"><?php echo __('奖励积分','b2'); ?></button>
                                        <button :class="ask.reward === 'money' ? 'picked' : ''" @click="ask.reward = 'money'"><?php echo sprintf(__('奖励%s','b2'),B2_MONEY_NAME); ?></button>
                                    </div>
                                </div>
                                <div class="ask-reward-input"><input type="text" v-model="ask.pay" autocomplete="off" name="reward" :placeholder="ask.reward === 'credit' ? '<?php echo __('请输入积分数额（整数）','b2'); ?>' : '<?php echo __('请输入金额（直接填写金额数字最少1元）','b2'); ?>'"></div>
                                <p class="ask-desc-text">
                                    <span><?php echo sprintf(__('问题提交之后会立刻扣除相应的%s。','b2'),'<b v-if="ask.reward === \'credit\'">'.__('积分','b2').'</b><b v-else>'.B2_MONEY_NAME.'</b>'); ?></span>
                                    <span>
                                        <?php echo sprintf(__('您的当前%s为：','b2'),'<b v-if="ask.reward === \'credit\'">'.__('积分','b2').'</b><b v-else>'.B2_MONEY_NAME.'</b>'); ?>
                                        <b v-if="ask.reward === 'credit'"><?php echo b2_get_icon('b2-coin-line'); ?>{{currentUser.credit ? currentUser.credit : 0}}</b>
                                        <b v-else><?php echo B2_MONEY_SYMBOL; ?>{{currentUser.money ? currentUser.money : 0}}</b>
                                    </span>
                                </p>
                                <div class="ask-credit ask-time">
                                    <div class="vote-type b2-color">
                                        <?php echo __('提问过期天数','b2'); ?>
                                    </div>
                                </div>
                                <div class="ask-reward-input">
                                    <input type="text" autocomplete="off" v-model="ask.time" placeholder="<?php echo __('请输入过期时间（天）','b2'); ?>">
                                </div>
                                <p><?php echo sprintf(__('请直接填写数字，并且大于1小于30。%s如果没有回答被采纳，提问过期后会自动关闭，然后对奖励进行结算。','b2'),'<br>'); ?></p>
                            </div>
                            <div class="circle-vote b2-radius" v-if="topicType === 'vote'" v-cloak>
                                <div class="vote-type">
                                    <button @click="vote.type = 'radio'" :class="vote.type === 'radio' ? 'picked b2-color' : ''"><?php echo __('单选','b2'); ?></button>
                                    <button @click="vote.type = 'multiple'" :class="vote.type === 'multiple' ? 'picked b2-color' : ''"><?php echo __('多选','b2'); ?></button>
                                    <button @click="vote.type = 'pk'" :class="vote.type === 'pk' ? 'picked b2-color' : ''"><?php echo __('PK','b2'); ?></button>
                                </div>
                                <div class="vote-list">
                                    <ul>
                                        <li v-for="(item,index) in vote.list" :key="index">
                                            <input type="text" v-model="vote.list[index]" :disabled="index >= 2 && vote.type === 'pk' ? true : false" />
                                            <button @click="index === (vote.list.length - 1) ? (index >= 2 && vote.type === 'pk' ? subVoteList(index) : addVoteList()) : subVoteList(index)">
                                                <span v-if="index >= 2 && vote.type === 'pk'"><?php echo b2_get_icon('b2-indeterminate-circle-line'); ?></span>
                                                <span v-else-if="index === (vote.list.length - 1)"><?php echo b2_get_icon('b2-add-circle-line b2-color'); ?></span>
                                                <span v-else><?php echo b2_get_icon('b2-indeterminate-circle-line'); ?></span>
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                                <p v-if="vote.type === 'radio'"><?php echo __('可以设置多个项目，用户只能选择其中一个项目进行投票','b2'); ?></p>
                                <p v-else-if="vote.type === 'multiple'" v-cloak><?php echo __('可以设置多个项目，用户可以选择多个项目进行投票','b2'); ?></p>
                                <p v-else v-cloak><?php echo __('只能设置两个项目，用户只能选择其中之一','b2'); ?></p>
                            </div>
                            <div class="circle-vote b2-radius" v-if="topicType === 'guess'" v-cloak>
                                <div class="vote-list">
                                    <ul>
                                        <li v-for="(item,index) in guess.list" :key="index">
                                            <input type="radio" :value="index" v-model="guess.right" class="topic-guess-right">
                                            <input type="text" v-model="guess.list[index]" :disabled="index >= 2 && guess.type == 'pk' ? true : false" />
                                            <button @click="index === (guess.list.length - 1) ? addGuessList() : subGuessList(index)">
                                                <span v-if="index === (guess.list.length - 1)"><?php echo b2_get_icon('b2-add-circle-line b2-color'); ?></span>
                                                <span v-else><?php echo b2_get_icon('b2-indeterminate-circle-line'); ?></span>
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                                <p><?php echo sprintf(__('请选择一个正确答案。当前正确答案是：%s','b2'),'<span v-text="\'#\'+(guess.right+1)" class="b2-color right-desc"></span>'); ?></p>
                            </div>
                            <div class="circle-media-box">
                                <div class="circle-image-box" v-if="image.list.length > 0" v-cloak>
                                    <p><?php echo sprintf(__('最多上传%s张图片，可拖动排序','b2'),'<b v-text="image.count"></b>'); ?></p>
                                    <div>
                                        <div v-for="(img,index) in image.list" :class="['circle-media-list',{'no-drags':uploadType !== ''}]" :draggable="uploadType === '' ? true : false" @dragstart="dragstart(img,'image')" @dragenter="dragenter(img,'image')" @dragend="dragend(img,'image')" :key="index">
                                            <div>
                                                <img :src="img.url" />
                                                <span v-text="img.progress+'%'" v-if="img.progress != '0' && img.progress != '100'" class="upload-progress"></span>
                                                <span v-else-if="img.success === false" class="upload-progress"><?php echo __('校验中..','b2'); ?></span>
                                                <span v-else-if="img.success === 'fail'" class="upload-progress"><i v-if="uploadType == ''" @click="removeFile(index,'image')"><?php echo __('请删除','b2'); ?></i><i v-else><?php echo __('上传错误','b2'); ?></i></span>
                                                <b v-else-if="uploadType == ''" class="circle-img-close" @click="removeFile(index,'image')"><?php echo b2_get_icon('b2-close-line'); ?></b>
                                            </div>
                                            <span class="circle-image-progress" :style="'left:'+img.progress+'%'"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="circle-image-box circle-video-box" v-if="video.list.length > 0" v-cloak>
                                    <p><?php echo sprintf(__('最多上传%s个视频，可拖动排序','b2'),'<b v-text="video.count"></b>'); ?></p>
                                    <div>
                                        <div v-for="(video,index) in video.list" class="circle-media-list" :draggable="video.success === true ? true : false" @dragstart="dragstart(video,'video')" @dragenter="dragenter(video,'video')" @dragend="dragend(video,'video')" :key="index">
                                            <div>
                                                <video :src="video.url" :poster="video.poster" preload="none" x5-video-player-type='h5'></video>
                                                <span v-text="video.progress+'%'" v-if="video.progress != '0' && video.progress != '100'" class="upload-progress"></span>
                                                <span v-else-if="video.success === false" class="upload-progress"><?php echo __('校验中..','b2'); ?></span>
                                                <span v-else-if="video.success === 'fail'" class="upload-progress"><i v-if="uploadType == ''" @click="removeFile(index,'video')"><?php echo __('请删除','b2'); ?></i><i v-else><?php echo __('上传错误','b2'); ?></i></span>
                                                <b v-else-if="uploadType == ''" class="circle-img-close" @click="removeFile(index,'video')"><?php echo b2_get_icon('b2-close-line'); ?></b>
                                            </div>
                                            <span class="circle-image-progress" :style="'left:'+video.progress+'%'"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="circle-file-box circle-image-box" v-if="file.list.length > 0" v-cloak>
                                    <p><?php echo sprintf(__('最多上传%s个文件，可拖动排序','b2'),'<b v-text="file.count"></b>'); ?></p>
                                    <div>
                                        <div v-for="(file,index) in file.list" class="circle-media-list" :draggable="file.success === true ? true : false" @dragstart="dragstart(file,'file')" @dragenter="dragenter(file,'file')" @dragend="dragend(file,'file')" :key="index">
                                            <div>
                                                <div :class="['file-list-item',file.ext]">
                                                    <span v-text="file.ext.toUpperCase()" class="file-mime"></span>
                                                    <span v-text="file.name" class="file-name"></span>
                                                    <span v-text="file.size" class="file-size"></span>
                                                </div>
                                                <span v-text="file.progress+'%'" v-if="file.progress != '0' && file.progress != '100'" class="upload-progress"></span>
                                                <span v-else-if="file.success === false" class="upload-progress"><?php echo __('校验中..','b2'); ?></span>
                                                <span v-else-if="file.success === 'fail'" class="upload-progress"><i v-if="uploadType == ''" @click="removeFile(index,'file')"><?php echo __('请删除','b2'); ?></i><i v-else><?php echo __('上传错误','b2'); ?></i></span>
                                                <b v-else-if="uploadType == ''" class="circle-img-close" @click="removeFile(index,'file')"><?php echo b2_get_icon('b2-close-line'); ?></b>
                                            </div>
                                            <span class="circle-image-progress" :style="'left:'+file.progress+'%'"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="circle-card-box circle-image-box" v-if="card.list.length > 0" v-cloak>
                                    <p><?php echo sprintf(__('最多添加%s个卡片，可拖动排序','b2'),'<b v-text="card.count"></b>'); ?></p>
                                    <div>
                                        <div v-for="(card,index) in card.list" class="circle-media-list" draggable="true" @dragstart="dragstart(card,'card')" @dragenter="dragenter(card,'card')" @dragend="dragend(card,'card')" :key="index">
                                            <div class="circle-card-box-list">
                                                <div :class="'circle-card-'+card.data.type">
                                                    <div class="circle-card-thumb">
                                                    <?php echo b2_get_img(array(
                                                        'source_data'=>':srcset="card.data.thumb_webp"',
                                                        'src_data'=>':src="card.data.thumb"',
                                                    ));?>
                                                    </div>
                                                    <div class="circle-card-info">
                                                        <div class="b2-color"><span v-text="card.data.type_name"></span></div>
                                                        <h2><a :href="card.data.link" v-html="card.data.title"></a></h2>
                                                    </div>
                                                </div>
                                                <span v-text="card.progress+'%'" v-if="card.progress != '0' && card.progress != '100'" class="upload-progress"></span>
                                                <b v-else class="circle-img-close" @click="removeFile(index,'card')"><?php echo b2_get_icon('b2-close-line'); ?></b>
                                            </div>
                                            <span class="circle-image-progress" :style="'left:'+card.progress+'%'"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="po-topic-tools">
                                <div class="po-topic-tools-left">
                                    <div class="po-topic-tools-item" ref="imgbutton" v-show="currentUser.mediaRole.image" v-cloak>
                                        <label class="button text">
                                            <?php echo b2_get_icon('b2-image-fill'); ?><b><?php echo __('图片','b2'); ?></b>
                                            <input type="file" accept="image/gif,image/jpeg,image/jpg,image/png" @change="getFile($event,'image')" multiple="multiple" name="file" class="b2-hidden-always" ref="imageInput" :disabled="uploadType != ''"/>
                                        </label>
                                    </div>
                                    <div class="po-topic-tools-item" ref="videobutton" v-show="currentUser.mediaRole.video" v-cloak>
                                        <label class="button text">
                                            <?php echo b2_get_icon('b2-movie-fill'); ?><b><?php echo __('视频','b2'); ?></b>
                                            <input type="file" ref="videoInput" :disabled="uploadType != ''" class="b2-hidden-always" name="file" accept="video/mp4,video/x-ms-asf,video/x-ms-wmv,video/x-ms-wmx,video/x-ms-wm,video/avi,video/divx,video/x-flv,video/quicktime,video/mpeg,video/ogg,video/webm,video/x-matroska,video/3gpp,video/3gpp2" @change="getFile($event,'video')" multiple="multiple">
                                        </label>
                                    </div>
                                    <div class="po-topic-tools-item" ref="filebutton" v-show="currentUser.mediaRole.file" v-cloak>
                                        <label class="button text">
                                            <?php echo b2_get_icon('b2-file-3-fill'); ?><b><?php echo __('文件','b2'); ?></b>
                                            <input type="file" :disabled="uploadType != ''" accept=".txt,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip" name="file" @change="getFile($event,'file')" multiple="multiple" class="b2-hidden-always" ref="fileInput"/>
                                        </label>
                                    </div>
                                    <div class="po-topic-tools-item" ref="cardbutton" v-show="currentUser.mediaRole.card" v-cloak>
                                        <label class="button text" @click="card.show = true">
                                            <?php echo b2_get_icon('b2-profile-fill'); ?><b><?php echo __('卡片','b2'); ?></b>
                                        </label>
                                    </div>
                                </div>
                                <?php 
                                    $roles = array(
                                        'public'=>array(
                                            'text'=>__('公开','b2'),
                                            'icon'=>'b2-shixindiqiu'
                                        ),
                                        'login'=>array(
                                            'text'=>__('登录','b2'),
                                            'icon'=>'b2-account-circle-line'
                                        ),
                                        'comment'=>array(
                                            'text'=>__('评论','b2'),
                                            'icon'=>'b2-chat-2-line'
                                        ),
                                        'money'=>array(
                                            'text'=>__('付费','b2'),
                                            'icon'=>'b2-money-cny-circle-line'
                                        ),
                                        'credit'=>array(
                                            'text'=>__('积分','b2'),
                                            'icon'=>'b2-coin-line'
                                        ),
                                        'lv'=>array(
                                            'text'=>__('等级','b2'),
                                            'icon'=>'b2-vip-crown-2-line'
                                        )
                                    );

                                    $lvs = User::get_user_roles();

                                    $setting_lvs = array();
                                    foreach($lvs as $k => $v){
                                        $setting_lvs[$k] = $v['name'];
                                    }

                                    if(b2_get_option('verify_main','verify_allow')){
                                        $setting_lvs['verify'] = __('认证用户','b2');
                                    }
                                ?>
                                <div class="po-topic-tools-right" ref="role" data-roledata='<?php echo json_encode($roles,true); ?>' data-lvs='<?php echo json_encode($setting_lvs,true); ?>'>
                                    <button class="po-topic-role" @click.stop="role.show = !role.show" v-if="role.list != ''"><span class="b2-color"><i :class="'b2font '+role.list[role.see].icon"></i><b v-text="role.list[role.see].text"></b></span></button>
                                    <button :class="['po-topic-button',{'b2-loading':locked}]" :disabled="subLocked()" @click="submitTopic()">
                                        <span v-text="subText()"><?php echo __('立刻提交','b2'); ?></span>
                                    </button>
                                </div>
                                <div :class="['modal','po-card-box',{'show-modal':card.show}]" v-cloak>
                                    <div class="modal-content b2-radius">
                                        <span class="close-button" @click="card.show = false">×</span>
                                        <p><?php echo __('请输入本站的网址，将自动生成网址卡片','b2'); ?></p>
                                        <div>
                                            <input type="text" v-model="card.input"/>
                                            <p><?php echo sprintf(__('支持文章、%s、商品、%s、页面等网址','b2'),$newsflashes_name,$circle_name); ?></p>
                                        </div>
                                        <div class="po-card-box-button" @click="insertCard"><button :disabled="card.input === '' ? true : false"><?php echo __('生成','b2'); ?></button></div>
                                    </div>
                                </div>
                                <div class="circle-topic-role b2-radius" v-show="role.show" v-cloak @click.stop="">
                                    <div class="topic-role-type">
                                        <button v-for="(item,key) in role.list" :key="key" @click="role.see = key" :class="role.see === key ? 'picked b2-color' : ''" v-if="currentUser.readRole[key]">
                                            <i :class="'b2font '+item.icon"></i><span v-text="item.text"></span>
                                        </button>
                                        <div v-if="circle.picked != circle.gc && currentUser.currentCircleRole.read !== 'private'">
                                            <label><input type="checkbox" v-model="role.currentCircle"><span><?php echo sprintf(__('本%s专属','b2'),$circle_name); ?></span></label>
                                        </div>
                                    </div>
                                    <div class="topic-role-content">
                                        <div v-if="role.see === 'public'">
                                            <p><?php echo __('无限制，任意查看话题内容','b2'); ?><span v-if="role.currentCircle" class="b2-color"><?php echo sprintf(__('（不在广场显示，加入%s后方可查看）','b2'),$circle_name); ?></span></p>
                                        </div>
                                        <div v-if="role.see === 'money' && currentUser.readRole.money" class="role-content-input" v-cloak>
                                            <input type="text" v-model="role.money" placeholder="<?php echo __('金额（纯数字）','b2'); ?>"/>
                                            <p><?php echo __('用户支付费用之后方可查看话题内容','b2'); ?><span v-if="role.currentCircle" class="b2-color"><?php echo sprintf(__('（不在广场显示，加入%s后方可查看）','b2'),$circle_name); ?></span></p>
                                        </div>
                                        <div v-if="role.see === 'credit' && currentUser.readRole.credit" class="role-content-input">
                                            <input type="text" v-model="role.credit" placeholder="<?php echo __('积分（纯数字）','b2'); ?>"/>
                                            <p><?php echo __('用户支付积分后方可查看话题内容','b2'); ?><span v-if="role.currentCircle" class="b2-color"><?php echo sprintf(__('（不在广场显示，加入%s后方可查看）','b2'),$circle_name); ?></span></p>
                                        </div>
                                        <div v-if="role.see === 'lv' && currentUser.readRole.lv" class="role-content-lv">
                                            <ul>
                                                <li v-for="(lv,key) in role.lv" :key="key"><label><input type="checkbox" v-model="role.lvPicked" :value="key"/><span v-text="lv"></span></label></li>
                                            </ul>
                                            <p><?php echo __('请选择允许查看本话题的等级组','b2'); ?><span v-if="role.currentCircle" class="b2-color"><?php echo sprintf(__('（不在广场显示，加入%s后方可查看）','b2'),$circle_name); ?></span></p>
                                        </div>
                                        <div v-if="role.see === 'comment' && currentUser.readRole.comment">
                                            <p><?php echo __('用户评论此话题之后方可查看话题内容','b2'); ?><span v-if="role.currentCircle" class="b2-color"><?php echo sprintf(__('（不在广场显示，加入%s后方可查看）','b2'),$circle_name); ?></span></p>
                                        </div>
                                        <div v-if="role.see === 'login' && currentUser.readRole.login">
                                            <p><?php echo __('用户登录之后方可查看话题内容','b2'); ?><span v-if="role.currentCircle" class="b2-color"><?php echo sprintf(__('（不在广场显示，%s后方可查看）','b2'),$circle_name); ?></span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>