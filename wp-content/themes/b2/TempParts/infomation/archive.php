<?php
    $name = b2_get_option('normal_custom','custom_infomation_name');
    $for = b2_get_option('normal_custom','custom_infomation_for');
    $get = b2_get_option('normal_custom','custom_infomation_get');
    $paged = get_query_var('paged');
    $paged = $paged ? $paged : 1;
    
    $count = (int)b2_get_option('infomation_main','infomation_per_count');
    $offset = ($paged -1)*$count;
    
?>
<div class="infomation-list-top">
    <div class="infomation-list-top-l">
        <span :class="type == 'all' ? 'picked' : ''" @click="type = 'all'"><?php echo __('全部','b2'); ?></span>
        <span :class="type == 'get' ? 'picked' : ''"  @click="type = 'get'"><?php echo $get; ?></span>
        <span :class="type == 'for' ? 'picked' : ''"  @click="type = 'for'"><?php echo $for; ?></span>
    </div>
    <div class="infomation-list-top-r">
        <div class="infomation-fliter b2-color" @click.stop="showFliter = true">
            <?php echo b2_get_icon('b2-filter-2-line'); ?>
            <span v-if="fliter == 'default'"><?php echo __('全部','b2'); ?></span>
            <span v-else-if="fliter == 'hot'"><?php echo __('热门的','b2'); ?></span>
            <span v-else-if="fliter == 'my'"><?php echo __('有兴趣的','b2'); ?></span>
        </div>
        <div class="infomation-fliter-box jt b2-radius" v-cloak v-show="showFliter">
            <div :class="fliter == 'default' ? 'picked' : ''" @click="fliterAc('default')"><?php echo __('全部','b2'); ?></div>
            <div :class="fliter == 'hot' ? 'picked' : ''" @click="fliterAc('hot')"><?php echo __('热门的','b2'); ?></div>
            <div :class="fliter == 'my' ? 'picked' : ''" @click="fliterAc('my')"><?php echo __('有兴趣的','b2'); ?></div>
        </div>
    </div>
</div>
<div class="gujia" ref="gujia">
    <?php 
        for ($i=0; $i < 12; $i++) {
            ?>
            <div class="info-list" >
                <div class="avatar-box"></div>
                <div class="info-right">
                    <div class="info-r-top">
                        <span class="i-type"></span>
                        <span class="i-author"></span>
                        <span class="i-date"></span>
                        <span class="i-status" ></span>
                    </div>
                    <div class="info-center">
                        <div class="info-row-1">
                            <div class="info-title">
                                <h2></h2>
                            </div>
                            <div class="info-price">
                                <p class="i-price"></p>
                            </div>
                        </div>
                    </div>
                    <div class="info-footer">
                        <div class="info-footer-l">
                            <span class="i-cat"></span>
                            <span class="b2-dot">•</span>
                            <span class="i-like"></span>
                            <span class="b2-dot">•</span>    
                            <span class="i-comment"></span>
                            <span class="b2-dot">•</span>
                            <span class="i-views"></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    ?>
</div>
<div v-cloak v-if="data">
    <div v-if="data.data.length > 0">
        <div class="info-list" v-for="(item,index) in data.data">
            <div class="avatar-box">
                <a :href="item.author.link" target="_blank">
                    <?php echo b2_get_img(array(
                        'src_data'=>':src="item.author.avatar"',
                        'class'=>array('avatar','b2-radius'),
                        'source_data'=>':srcset="item.author.avatar_webp"'
                    ));?>
                </a>
            </div>
            <div class="info-right">
                <div class="info-r-top">
                    <span v-text="item.type.text" :class="['i-type',item.type.type]"></span>
                    <span class="i-author"><a :href="item.author.link" target="_blank" v-text="item.author.name"></a></span>
                    <span class="i-date">
                        <time class="b2timeago" :datetime="item.date" itemprop="datePublished" v-text="item.date"></time>
                    </span>
                    <span class="i-status" >
                        <b v-if="item.post_status == 'pending'" class="red"><?php echo __('待审','b2'); ?></b>
                        <b v-else-if="item.status.status == 0" class="green"><?php echo __('进行中','b2'); ?></b>
                        <b v-else><?php echo __('已完成','b2'); ?></b>
                    </span>
                </div>
                <div class="info-center">
                    <div class="info-row-1">
                        <div class="info-title">
                            <h2>
                                <span v-if="item.sticky == 1"><?php echo __('置顶','b2'); ?></span><a :href="item.link" v-text="item.title" target="_blank"></a>
                            </h2>
                            <div class="info-row-img" v-if="item.images.length > 0">
                                <div class="info-imgs" v-for="(img,i) in item.images">
                                    <a :href="item.link" class="link-block" target="_blank"></a>
                                    <?php echo b2_get_img(array(
                                        'src_data'=>':src="img.thumb"',
                                        'class'=>array('info-img','b2-radius'),
                                        'pic_data'=>' v-if="img.thumb"',
                                        'source_data'=>':srcset="img.thumb_webp"'
                                    ));?>
                                </div>
                            </div>
                        </div>
                        <div :class="['info-price',item.type.type]" v-if="item.price">
                            <a :href="item.link" target="_blank">
                                <p class="i-price" v-text="'<?php echo B2_MONEY_SYMBOL; ?>'+item.price"></p>
                                <p class="i-price i-none-price" v-else><?php echo __('暂无报价','b2'); ?></p>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="info-footer">
                    <div class="info-footer-l">
                        <span class="i-cat" v-if="item.cat.name">
                            <a :href="item.cat.link" target="_blank" v-text="item.cat.name"></a>
                        </span>
                        <span class="b2-dot" v-if="item.cat.name && item.vote.count > 0">•</span>
                        <span class="i-like" v-if="item.vote.count > 0">
                            {{item.vote.count}}<?php echo __('人有兴趣','b2'); ?>
                        </span>
                        <span class="b2-dot"  v-if="(item.cat.name || item.vote.count > 0) && item.comment_count > 0">•</span>    
                        <span class="i-comment" v-if="item.comment_count > 0">{{item.comment_count}}<?php echo __('条评论','b2'); ?></span>
                        <span class="b2-dot" v-if="(item.cat.name || item.vote.count > 0 || item.comment_count > 0) && item.views > 0">•</span>
                        <span class="i-views" v-if="item.views > 0">
                            <?php echo b2_get_icon('b2-fire-line'); ?>{{item.views}}<?php echo __('热度','b2'); ?>
                        </span>
                    </div>
                    <div class="info-footer-r" v-if="item.can_edit && isAuthor" v-cloak>
                        <button class="text" @click="deleteAc(item.id,index)"><?php echo __('删除','b2'); ?></button>
                        <a class="text button" target="_blank" :href="'<?php echo b2_get_custom_page_url('po-infomation'); ?>?id='+item.id"><?php echo __('编辑','b2'); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div v-else>
        <?php echo B2_EMPTY; ?>
    </div>
</div>
<pagenav-new ref="infonav" navtype="post" :pages="opt['pages']" :type="navType" :box="selecter" :opt="opt" :api="api" :rote="true" url="<?php echo get_post_type_archive_link('infomation'); ?>" title="<?php echo $name; ?>" @return="get"></pagenav-new>