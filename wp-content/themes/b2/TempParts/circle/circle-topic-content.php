<?php 
$circle_name = b2_get_option('normal_custom','custom_circle_name');
$circle_owner_name = b2_get_option('normal_custom','custom_circle_owner_name');
?>
<div class="topic-header">
    <div class="topic-header-left">
        <div class="topic-avatar">
            <a :href="item.author.link" target="_blank">
            <?php echo b2_get_img(array(
                'source_data'=>':srcset="item.author.avatar_webp"',
                'src_data'=>':src="item.author.avatar"',
                'class'=>array('b2-radius')
            ));?>
            <b v-html="item.author.verify_icon" v-if="item.author.user_title"></b></a>
        </div>
        <div class="topic-name">
            <div>
                <div class="topic-name-data">
                    <a :href="item.author.link" target="_blank"><b v-text="item.author.name"></b></a>
                    <span v-if="item.author.is_circle_admin" class="circle-is-admin"><?php echo $circle_owner_name; ?></span>
                    <span v-if="item.author.is_admin" class="circle-is-circle-admin"><?php echo __('管理员','b2'); ?></span>
                </div>
                <div class="topic-user-lv">
                    <p v-html="item.author.lv.vip.lv ? item.author.lv.vip.icon : ''"></p>
                    <p v-html="item.author.lv.lv.lv ? item.author.lv.lv.icon : ''"></p>
                </div>
            </div>
        </div>
    </div>
    <div class="topic-header-right">
        <span class="topic-date topic-circle">
            <a :href="item.circle.link" target="_blank">
                <b class="circle-hash">
                    <svg width="16" height="16" viewBox="0 0 36 36"><g fill-rule="evenodd"><path d="M18 0c9.941 0 18 8.059 18 18 0 2.723-.604 5.304-1.687 7.617v6.445a2.25 2.25 0 0 1-2.096 2.245l-.154.005-6.446.001A17.932 17.932 0 0 1 18 36C8.059 36 0 27.941 0 18S8.059 0 18 0z" fill-opacity=".1"></path><path d="M23.32 7.875c.517 0 .948.18 1.293.54.296.294.444.632.444 1.015a.589.589 0 0 1-.037.202l-.258 2.17c0 .18.087.27.259.27h.96c.592 0 1.097.185 1.516.557.419.372.628.828.628 1.369 0 .54-.21 1.003-.628 1.386a2.166 2.166 0 0 1-1.515.574h-1.478c-.197 0-.308.09-.333.27l-.517 3.684c-.025.158.049.237.221.237h1.22c.591 0 1.096.191 1.515.574.419.384.628.845.628 1.386 0 .54-.21 1.003-.628 1.386a2.166 2.166 0 0 1-1.515.574h-1.7c-.172 0-.27.08-.296.237l-.273 2.062c-.05.495-.283.912-.702 1.25a2.282 2.282 0 0 1-1.478.507c-.518 0-.949-.18-1.294-.54-.295-.294-.443-.632-.443-1.015 0-.067.012-.135.037-.202l.236-2.062c.025-.158-.049-.237-.221-.237h-3.732c-.198 0-.296.08-.296.237l-.31 2.062a1.96 1.96 0 0 1-.721 1.25c-.407.338-.88.507-1.423.507-.517 0-.948-.18-1.293-.54-.296-.294-.444-.632-.444-1.015v-.202l.274-2.062c.025-.158-.062-.237-.259-.237h-.739a2.166 2.166 0 0 1-1.515-.574c-.419-.383-.628-.845-.628-1.386 0-.54.21-1.002.628-1.386a2.166 2.166 0 0 1 1.515-.574h1.257c.172 0 .27-.079.295-.237l.48-3.684c.025-.18-.06-.27-.258-.27h-.924a2.166 2.166 0 0 1-1.515-.574c-.419-.383-.628-.84-.628-1.37 0-.529.21-.985.628-1.368a2.166 2.166 0 0 1 1.515-.575h1.515c.197 0 .308-.09.333-.27L13.01 9.6c.074-.474.314-.88.72-1.217.407-.338.881-.507 1.423-.507.518 0 .949.18 1.294.54.27.294.406.62.406.98v.237l-.294 2.17c-.025.18.061.27.259.27h3.769c.172 0 .27-.09.295-.27l.295-2.203c.074-.474.314-.88.72-1.217.407-.338.881-.507 1.423-.507zm-3.316 7.875h-3.49c-.157 0-.256.071-.296.213l-.014.077-.45 3.956c-.02.145.029.228.144.249l.064.005h3.524c.134 0 .22-.059.26-.176l.016-.078.484-3.956c.02-.166-.037-.26-.17-.284l-.072-.006z" fill-rule="nonzero"></path></g></svg>
                </b>
                <b v-text="item.circle.name"></b>
            </a>
        </span>
        <!-- <div>
            <a :href="item.link" target="_blank">
                <span v-if="item.data.type === 'say'" v-cloak><?php echo b2_get_icon('b2-tubiao108'); ?></span>
                <span v-if="item.data.type === 'ask'" v-cloak><?php echo b2_get_icon('b2-icon_tiwen'); ?></span>
                <span  v-if="item.data.type === 'vote'" v-cloak><?php echo b2_get_icon('b2-toupiao'); ?></span>
                <span v-if="item.data.type === 'guess'" v-cloak><?php echo b2_get_icon('b2-fensi'); ?></span>
            </a>
        </div> -->
    </div>
</div>
<div class="topic-content">
    <h2 v-if="item.title || item.best" v-cloak>
        <template v-if="single.is"><span v-html="item.title" v-if="item.title"></span></template>
        <a :href="item.link" target="_blank" v-else><span v-html="item.title" v-if="item.title"></span></a>
        <span class="topic-best" v-if="item.best"><img src="<?php echo B2_THEME_URI.'/Assets/fontend/images/topic-best.png'; ?>" /></span>
    </h2>
    <template v-if="!item.allow_read['allow']">
        <div :class="'topic-read-role '+item.allow_read['type']" v-if="item.allow_read['type'] === 'credit'">
            <div class="topic-guess-box guess-type b2-radius">
                <div class="topic-vote-desc"><?php echo b2_get_icon('b2-lock-2-line').__('隐藏内容，支付积分阅读','b2'); ?></div>
                <div class="not-allow-role">
                    <div class="topic-read-nub"><?php echo b2_get_icon('b2-coin-line'); ?><span v-text="item.allow_read.data"></span></div>
                    <div class="topic-read-number"><?php echo sprintf(__('已有%s人购买此隐藏内容','b2'),'<span v-text="item.allow_read.count">90</span>'); ?></div>
                    <div class="topic-read-pay-button"><button class="empty" @click="hiddenContentPay(ti)"><?php echo __('支付','b2'); ?></button></div>
                </div>
            </div>
        </div>
        <div :class="'topic-read-role '+item.allow_read['type']" v-if="item.allow_read['type'] === 'money'">
            <div class="topic-guess-box guess-type b2-radius">
                <div class="topic-vote-desc"><?php echo b2_get_icon('b2-lock-2-line').__('隐藏内容，支付费用阅读','b2'); ?></div>
                <div class="not-allow-role">
                    <div class="topic-read-nub"><?php echo B2_MONEY_SYMBOL; ?><span v-text="item.allow_read.data"></span></div>
                    <div class="topic-read-number"><?php echo sprintf(__('已有%s人购买此隐藏内容','b2'),'<span v-text="item.allow_read.count">86</span>'); ?></div>
                    <div class="topic-read-pay-button"><button class="empty" @click="hiddenContentPay(ti)"><?php echo __('支付','b2'); ?></button></div>
                </div>
            </div>
        </div>
        <div :class="'topic-read-role '+item.allow_read['type']" v-if="item.allow_read['type'] === 'lv'">
            <div class="topic-guess-box allow-read-type b2-radius">
                <div class="topic-vote-desc"><?php echo b2_get_icon('b2-lock-2-line').__('隐藏内容，仅限以下用户组阅读','b2'); ?></div>
                <div class="not-allow-role">
                    <div class="topic-read-nub">
                        <ul>
                            <li v-for="(lv,index) in item.allow_read.data" v-html="lv" :key="index"></li>
                        </ul>
                    </div>
                    <div class="topic-read-pay-button">
                        <a class="button empty" href="<?php echo b2_get_custom_page_url('gold');?>" target="_blank"><?php echo __('积分升级','b2'); ?></a>
                        <a class="button empty" href="<?php echo b2_get_custom_page_url('vips');?>" target="_blank"><?php echo __('变更会员','b2'); ?></a>
                    </div>
                </div>
            </div>
        </div>
        <div :class="'topic-read-role '+item.allow_read['type']" v-if="item.allow_read['type'] === 'login'">
            <div class="topic-guess-box allow-read-type login b2-radius">
                <div class="topic-vote-desc"><?php echo b2_get_icon('b2-lock-2-line').__('隐藏内容，登录后阅读','b2'); ?></div>
                <div class="not-allow-role">
                    <div class="topic-read-nub">
                        <div class="topic-read-number"><?php echo __('登录之后方可阅读隐藏内容','b2'); ?></div>
                    </div>
                    <div class="topic-read-pay-button">
                        <button class="empty" @click="loginAc(1)"><?php echo __('登录','b2'); ?></button>
                        <button class="empty" @click="loginAc(2)"><?php echo __('快速注册','b2'); ?></button>
                    </div>
                </div>
            </div>
        </div>
        <div :class="'topic-read-role '+item.allow_read['type']" v-if="item.allow_read['type'] === 'comment'">
            <div class="topic-guess-box allow-read-type login b2-radius">
                <div class="topic-vote-desc"><?php echo b2_get_icon('b2-lock-2-line').__('隐藏内容，评论后阅读','b2'); ?></div>
                <div class="not-allow-role">
                    <div class="topic-read-nub">
                        <div class="topic-read-number"><?php echo __('请在下面参与讨论之后，方可阅读隐藏内容','b2'); ?></div>
                    </div>
                    <div class="topic-read-pay-button">
                        <button class="empty" @click="showComment(ti,true)"><?php echo __('参与讨论','b2'); ?></button>
                    </div>
                </div>
            </div>
        </div>
        <div :class="'topic-read-role '+item.allow_read['type']" v-if="item.allow_read['type'] === 'current_circle_read'">
            <div class="topic-guess-box allow-read-type login b2-radius">
                <div class="topic-vote-desc"><?php echo b2_get_icon('b2-lock-2-line').sprintf(__('隐藏内容，加入%s后阅读','b2'),$circle_name); ?></div>
                <div class="not-allow-role">
                    <div class="topic-read-nub">
                        <div class="topic-read-number"><?php echo sprintf(__('您需要加入%s之后才能查看帖子内容','b2'),$circle_name); ?></div>
                    </div>
                    <div class="topic-read-pay-button">
                        <button class="empty" @click="jionCircleAction(item)"><?php echo sprintf(__('加入%s','b2'),$circle_name); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </template>
    <template v-else>
        <div v-if="item.data.type === 'guess' && item.data.data.answer === item.data.data.picked" class="guess-right-tips">
            <p><?php echo sprintf(__('%s您猜对了答案，下面是向您展示的隐藏信息：%s','b2'),'<span class="b2-color">','</span>'); ?></p>
        </div>
        <div v-if="item.data.type === 'ask'" class="topic-content-text topic-content-text-ask">
            <div>
                <div class="topic-ask-to">
                    <span v-if="item.data.data.reward === 'credit'" class="ask-money">[<?php echo b2_get_icon('b2-coin-line'); ?><b v-text="item.data.data.pay"></b>]</span>
                    <span v-else class="b2-color">[<?php echo B2_MONEY_SYMBOL; ?><b v-text="item.data.data.pay"></b>]</span>
                    <?php echo __('向','b2'); ?>
                    <span v-html="askContent(ti)"></span>
                    <?php echo __('提问：','b2'); ?>
                </div>
                <div v-html="fliterContent(item.content,ti,item)"></div>
            </div>
        </div>
        <div class="topic-guess-box guess-type b2-radius" v-else-if="item.data.type === 'guess' && item.data.data.answer !== item.data.data.picked">
            <div class="topic-vote-desc">
                <?php echo b2_get_icon('b2-lock-2-line').__('隐藏内容，猜对答案后阅读','b2'); ?>
            </div>
            <ul>
                <li v-for="(g,gi) in item.data.data.list" :class="gi === data[ti].data.data.picked ? 'picked color-button' : 'color-button'" @click="guessPicked(ti,gi)" :key="gi">
                    <span v-text="g.title"></span>
                </li>
            </ul>
            <div class="vote-resout guess-resout">
                <button class="empty" @click="guessAction(ti)" :disabled="!data[ti].data.data.picked && data[ti].data.data.picked !== 0" v-if="!data[ti].data.data.answer && data[ti].data.data.answer !== 0">
                    <?php echo __('提交答案','b2'); ?>
                </button>
                <p v-else>
                    <?php echo sprintf(__('%s猜错啦：%s您选中的是「%s」，正确答案是：「%s」','b2'),'<b>','</b>','<span v-text="item.data.data.list[item.data.data.picked].title"></span>','<span v-text="item.data.data.list[item.data.data.answer].title"></span>'); ?>
                </p>
            </div>
        </div>
        <div v-else class="topic-content-text" v-html="fliterContent(item.content,ti,item)"></div>
        <div v-if="item.attachment.video.length > 0" v-cloak class="topic-video-box" v-cloak>
            <ul>
                <li v-for="(v,index) in item.attachment.video" :key="v.id" :style="'max-width:'+(v.show ? v.width_normal : v.width)+'px;'">
                    <div :style="'padding-top:'+((v.show ? v.ratio_normal : v.ratio)*100)+'%'" class="b2-radius">
                        <video class="lazy" :poster="v.poster ? v.poster : ''" :data-src="v.link" :id="'video'+item.topic_id+'i'+index" preload="none" objectfit="cover" x5-video-player-type='h5' :controls="video.index == index && video.id == item.topic_id && video.action == true ? true : false"></video>
                        <div :class="['topic-video-play',{'hidden':video.index == index && video.id == item.topic_id && video.action == true}]" @click="play(ti,item.topic_id,index)">
                            <span :class="['play-button',{'hidden':video.index == index && video.id == item.topic_id && video.action == true}]">
                                <?php echo b2_get_icon('b2-play-circle-line'); ?>
                            </span>
                        </div>
                        <div class="video-bg">
                            <img :src="v.poster" class="video-bg" />
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        <div v-if="item.attachment.image.length > 0" v-cloak class="topic-image-box" v-cloak>
            <ul :class="'img-list-'+(item.attachment.image.length > 1 ? 'more' : '1')" v-show="!item.attachment.Showimage">
                <li v-for="(img,index) in item.attachment.image" v-if="index <= 2" :key="img.id" @click="showImageLight(ti,index)" :style="item.attachment.image.length < 2 ? 'width:'+item.attachment.image[0].thumb_w+'px' : ''">
                    <div :style="item.attachment.image.length < 2 ? 'padding-top:'+(img.big_ratio*100)+'%' : ''" class="b2-radius">
                        <div v-if="img.gif">
                            <?php echo b2_get_img(array(
                                'source_data'=>':srcset="img.current_webp"',
                                'src_data'=>':src="img.play === \'\' ? \''.B2_LOADING_IMG.'\' : img.current"',
                                'class'=>array('b2-radius','circle-gif')
                            ));?>
                        </div>
                        <div v-else>
                            <?php echo b2_get_img(array(
                                'source_data'=>':srcset="img.current_webp"',
                                'src_data'=>':src="img.current"',
                                'class'=>array('b2-radius')
                            ));?>
                        </div>
                        <div class="topic-gif-play" @click="playGif(ti,index)" v-if="img.gif_first">
                            <p v-if="!img.play">PLAY</p>
                            <p v-else-if="img.play == 'loading'">Loading...</p>
                        </div>
                        <span v-if="index === 2 && item.attachment.image.length > 3" class="image-number">
                            +<b v-text="item.attachment.image.length - 3"></b>
                        </span>
                    </div>
                </li>
            </ul>
            <div class="topic-image-light" v-show="item.attachment.Showimage">
                <div class="topic-image-tools">
                    <a href="javascript:void(0)" @click="closeImageBox(ti)"><?php echo b2_get_icon('b2-arrow-up-line').'<b>'.__('收起','b2').'</b>'; ?></a>
                    <a :href="item.attachment.imageIndex || item.attachment.imageIndex == 0 ? item.attachment.image[item.attachment.imageIndex].link : ''" target="_blank"><?php echo b2_get_icon('b2-zoom-in-line').'<b>'.__('查看原图','b2').'</b>'; ?></a>
                    <a href="javascript:void(0)" @click="rotate('left',ti)"><?php echo b2_get_icon('b2-arrow-go-back-line').'<b>'.__('向左旋转','b2').'</b>'; ?></a>
                    <a href="javascript:void(0)" @click="rotate('right',ti)"><?php echo b2_get_icon('b2-arrow-go-forward-line').'<b>'.__('向右旋转','b2').'</b>'; ?></a>
                </div>
                <ul class="topic-image-box-big">
                    <li v-for="(big,index) in item.attachment.image" :key="big.id" :class="item.attachment.imageIndex === index ? 'image-show' : 'b2-hidden-always'">
                        <div :style="'padding-top:'+(big.big_ratio*100)+'%'" class="box-in b2-radius" @click="showImageBox($event,ti)">
                            <span v-if="item.attachment.imageIndex === index && item.attachment.image.length > 1" class="prev" @click.stop="imageNav('prev',ti)"></span>
                            <?php echo b2_get_img(array(
                                'source_data'=>':srcset="big.big_thumb_webp"',
                                'src_data'=>':src="big.big_thumb"'
                            ));?>
                            <span v-if="item.attachment.imageIndex === index && item.attachment.image.length > 1" class="next" @click.stop="imageNav('next',ti)"></span>
                        </div>
                        <img :src="big.big_thumb" class="zoom-img" :data-original="big.link"/>
                    </li>
                </ul>
                <ul class="topic-image-box-small">
                    <li v-for="(small,index) in item.attachment.image" :key="small.id" @click="showImageLight(ti,index)" :class="item.attachment.imageIndex === index ? 'picked b2-radius' : 'b2-radius'">
                        <?php echo b2_get_img(array(
                            'source_data'=>':srcset="small.small_thumb_webp"',
                            'src_data'=>':src="small.small_thumb"'
                        ));?>
                    </li>
                </ul>
            </div>
        </div>
        <div v-if="item.attachment.file.length > 0" v-cloak class="topic-file-box" v-cloak>
            <ul>
                <li v-for="(f,index) in item.attachment.file" :key="f.id">
                    <div>
                        <div :class="['file-list-item b2-radius',f.ext]">
                            <a class="link-block" :href="f.link" :download="f.name" target="_blank"></a>
                            <span class="file-mime" v-text="f.ext"></span> 
                            <span class="file-name" v-text="f.name"></span> 
                            <span class="file-size" v-text="f.size"></span>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        <div v-if="item.attachment.card.length > 0" v-cloak class="topic-card-box" v-cloak>
            <ul>
                <li v-for="(c,index) in item.attachment.card" :key="c.id" class="circle-media-list b2-radius">
                    <div class="circle-card-box-list">
                        <div class="topic-card-document">
                            <a :href="c.link" class="link-block" target="_blank"></a>
                            <div class="circle-card-thumb b2-radius">
                                <?php echo b2_get_img(array(
                                    'source_data'=>':srcset="c.thumb_webp"',
                                    'src_data'=>':src="c.thumb"'
                                ));?>
                            </div>
                            <div class="topic-card-info">
                                <h2><span v-text="c.type_name" class="b2-color"></span><span v-html="c.title"></span></h2>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        <div class="topic-vote" v-if="item.data.type === 'vote'" v-cloak>
            <div class="topic-vote-multiple topic-vote-box b2-radius" v-if="item.data.data.type == 'multiple'">
                <div class="topic-vote-desc">
                    <b class="b2-color"><i><?php echo __('多选','b2'); ?></i></b><span><?php echo sprintf(__('%s人参与投票','b2'),'<b v-text="item.data.data.total"></b>'); ?></span>
                </div>
                <ul>
                    <template v-if="!item.data.data.voted">
                        <li v-for="(vote,mdi) in item.data.data.list" :key="mdi">
                            <label>
                                <input type="checkbox" v-model="item.data.data.picked" :value="mdi"/><span class="vote-bar" v-text="vote.title"></span>
                            </label>
                        </li>
                    </template>
                    <template v-else>
                        <li v-for="(vote,vdi) in item.data.data.list" class="topic-vote-radio-picked" :key="vdi">
                            <div :class="voteCurrent(ti,vdi) ? 'picked' : ''">
                                <span v-text="vote.title"></span>
                                <span><b v-text="vote.vote+'<?php echo __('人','b2'); ?>'"></b><b v-text="'('+voteText(ti,vdi)+')'"></b></span>
                            </div>
                            <div>
                                <span :style="'width:'+voteText(ti,vdi)" :class="voteCurrent(ti,vdi) ? 'picked' : ''"></span>
                            </div>
                        </li>
                    </template>
                </ul>
                <div class="vote-resout">
                    <button class="empty" @click="voteRadioPicked(ti)" :disabled="item.data.data.picked == '' && item.data.data.picked !== 0" v-if="!item.data.data.voted">
                        <?php echo __('投票','b2'); ?>
                    </button>
                    <button class="empty" v-else disabled>
                        <?php echo __('已投票','b2'); ?>
                    </button>
                </div>
            </div>
            <div class="topic-vote-multiple topic-vote-box b2-radius" v-if="item.data.data.type == 'radio'">
                <div class="topic-vote-desc">
                    <b class="b2-color"><i><?php echo __('单选','b2'); ?></i></b><span><?php echo sprintf(__('%s人参与投票','b2'),'<b v-text="item.data.data.total"></b>'); ?></span>
                </div>
                <ul>
                    <template v-if="!item.data.data.voted">
                        <li v-for="(vote,vdi) in item.data.data.list" :key="vdi">
                            <label>
                                <input type="radio" v-model="item.data.data.picked" :value="vdi"/><span class="vote-bar" v-text="vote.title"></span>
                            </label>
                        </li>
                    </template>
                    <template v-else>
                        <li v-for="(vote,vdi) in item.data.data.list" class="topic-vote-radio-picked" :key="vdi">
                            <div :class="voteCurrent(ti,vdi) ? 'picked' : ''">
                                <span v-text="vote.title"></span>
                                <span><b v-text="vote.vote+'<?php echo __('人','b2'); ?>'"></b><b v-text="'('+voteText(ti,vdi)+')'"></b></span>
                            </div>
                            <div>
                                <span :style="'width:'+voteText(ti,vdi)" :class="voteCurrent(ti,vdi) ? 'picked' : ''"></span>
                            </div>
                        </li>
                    </template>
                </ul>
                <div class="vote-resout">
                    <button class="empty" @click="voteRadioPicked(ti)" :disabled="item.data.data.picked == '' && item.data.data.picked !== 0" v-if="!item.data.data.voted">
                        <?php echo __('投票','b2'); ?>
                    </button>
                    <button class="empty" v-else disabled>
                        <?php echo __('已投票','b2'); ?>
                    </button>
                </div>
            </div>
            <div class="topic-vote-pk b2-radius" v-if="item.data.data.type == 'pk'">
                <div class="topic-vote-desc"><b class="b2-color">PK</b><span><?php echo sprintf(__('%s人参与PK','b2'),'<b v-text="item.data.data.total"></b>'); ?></span></div>
                <div class="vote-pk-box" v-if="!item.data.data.voted">
                    <div class="vote-pk-left vote-pk">
                        <p v-text="item.data.data.list[0].title" @click="topicVote(ti,0)"></p>
                    </div>
                    <div class="vote-pk-right vote-pk">
                        <p v-text="item.data.data.list[1].title" @click="topicVote(ti,1)"></p>
                    </div>
                </div>
                <div class="vote-pk-res-box" v-else>
                    <div class="topic-pk-resout-head">
                        <div><p><b v-text="voteText(ti,0)"></b><span v-if="voteCurrent(ti,0)"><b class="dot">·</b><?php echo __('已选','b2'); ?></span></p><p v-text="item.data.data.list[0].title"></p></div>
                        <div><p><span v-if="voteCurrent(ti,1)"><?php echo __('已选','b2'); ?><b class="dot">·</b></span><b v-text="voteText(ti,1)"></b></p><p v-text="item.data.data.list[1].title"></p></div>
                    </div>
                    <div class="topic-pk-resout-footer">
                        <div class="vote-pk-left vote-pk" :style="'width:'+voteText(ti,0)"><p></p></div>
                        <div class="vote-pk-right vote-pk" :style="'width:'+voteText(ti,1)"><p></p></div>
                    </div>
                </div>
                <div class="vote-pk-desc" v-if="!item.data.data.voted"><?php echo __('投票后查看结果，您的选择是？','b2'); ?></div>
                <div class="vote-pk-desc" v-if="item.data.data.voted"><?php echo __('思想因碰撞产生火花，真理因辩论获得升华','b2'); ?></div>
            </div>
        </div>
        <div v-if="item.data.type === 'ask'" class="topic-content-text topic-content-text-ask topic-ask-box b2-radius">
            <div class="ask-pay">
                <div>
                    <span class="ask-users-count">
                        <?php echo sprintf(__('已有%s个回答，%s','b2'),'<b v-text="item.data.data.answer_count"></b>','<b v-text="item.data.data.end_time" v-if="item.data.data.end_time != -1"></b><b v-else>'.__('问题已过期','b2').'</b>'); ?>
                    </span>
                    <span class="ask-write b2-color" v-if="canAnswer(ti)" @click="resetAnswerList(ti)"><?php echo __('我来回答','b2'); ?></span>
                </div>
                <div :class="['ask-toumiao',{'picked':answer.listParent === ti}]" v-if="item.data.data.answer_count !== 0">
                    <span class="ask-toumiao-b" @click="resetAnswerList(ti)" v-if="item.data.data.type === 'everyone' || item.data.data.can_read"><?php echo __('查看答案','b2').b2_get_icon('b2-arrow-right-s-fill'); ?></span>
                    <span class="ask-toumiao-b" @click="resetAnswerList(ti)" v-else-if="item.data.data.answer_count !== 0"><?php echo __('偷瞄答案','b2').b2_get_icon('b2-arrow-right-s-fill'); ?></span>
                </div>
                <div class="ask-toumiao" v-else><b><?php echo __('没有回答','b2'); ?></b></div>
            </div>
            <div :id="'answer-box-'+ti">
                <template v-if="answer.listParent === ti" v-cloak>
                    <div class="ask-answer-box" :id="'ask-box-'+ti"></div>
                    <div class="ask-answer-list" :id="'ask-list-'+ti" v-if="item.data.data.answer_count > 0">
                        <template v-if="item.data.data.can_read === false">
                            <div class="ask-read-answer">
                                <h2><?php echo b2_get_icon('b2-lock-2-line').__('偷瞄答案','b2'); ?></h2>
                                <p><?php echo __('不能确保答案质量，偷瞄之前务必想好哦！','b2'); ?></p>
                                <div class="ask-read-pay">
                                    <span v-if="item.data.data.reward === 'credit'"><?php echo b2_get_icon('b2-jifen'); ?><b v-text="item.data.data.pay_read"></b></span>
                                    <span v-else><?php echo B2_MONEY_SYMBOL; ?><b v-text="item.data.data.pay_read"></b></span>
                                </div>
                                <div class="topic-read-pay-button" @click="readAnswerPay()"><button class="empty"><?php echo __('支付','b2'); ?></button></div>
                            </div>
                        </template>
                        <template v-else>
                            <div v-if="answer.list === ''" class="answer-loading">
                                <button class="text b2-loading empty"></button>
                            </div>
                            <div v-else-if="answer.list.length > 0" class="answer-list b2-radius">
                                <ul>
                                    <li v-for="(an,ai) in answer.list" :key="ai">
                                        <div class="answer-header">
                                            <div>
                                                <?php echo b2_get_img(array(
                                                    'source_data'=>':srcset="an.user.avatar_webp"',
                                                    'src_data'=>':src="an.user.avatar"'
                                                ));?>
                                                <span><a :href="an.user.link" v-text="an.user.name"></a></span><span v-html="an.verify_icon"></span><span v-html="an.date"></span>
                                            </div>
                                            <div v-if="item.data.data.best === ''"><button class="text" v-if="an.is_author" @click="answerRight(ti,an.id)" :disabled="answer.answerRightLocked"><?php echo __('采纳','b2'); ?></button></div>
                                            <div v-else-if="item.data.data.best == an.id" class="answer-right"><?php echo __('已采纳','b2'); ?></div>
                                        </div>
                                        <div class="answer-content" v-html="fliterAnswer(an.content,ai)"></div>
                                        <div class="topic-commentlist-img-box b2-radius" v-if="an.image">
                                            <?php echo b2_get_img(array(
                                                'source_data'=>':srcset="an.image.thumb_webp"',
                                                'src_data'=>':data-zooming-width="an.image.width" :data-zooming-height="an.image.height" :data-src="an.image.thumb" :data-original="an.image.full" :src="an.image.thumb"'
                                            ));?>
                                        </div>
                                        <div class="answer-file-box file-list-item b2-radius" v-if="an.file">
                                            <span class="file-mime"><b v-text="an.file.ext"></b></span>
                                            <span class="file-name" v-text="an.file.name"></span>
                                            <span class="file-size" v-text="readablizeBytes(an.file.size)"></span>
                                            <a class="link-block" :href="an.file.link" :download="an.file.name"></a>
                                        </div>
                                        <div class="answer-list-tools">
                                            <div class="answer-list-tools-left">
                                                <button :class="['text',{'picked b2-color':an.vote.isset_up}]" @click="answerVote(ai,'up',an.id)"><?php echo b2_get_icon('b2-thumb-up-line'); ?><b v-text="an.vote.up"></b></button>
                                            </div>
                                            <div class="topic-author-info-right" v-if="an.can_edit || isAdmin()">
                                                <button class="text" @click="editAnswer(ai,an.id)" v-if="canAnswer(ti)"><?php echo __('编辑','b2'); ?></button>
                                                <button class="text" @click="deleteAnswer(ai,an.id)"><?php echo __('删除','b2'); ?></button>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <pagenav-new ref="topicCommentNav" type="p" :paged="answer.opt.paged" :pages="answer.opt.pages" :opt="answer.opt" :api="answer.opt.api" @return="getMoreAnswers" v-if="answer.opt.pages > 1"></pagenav-new>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </template>
</div>