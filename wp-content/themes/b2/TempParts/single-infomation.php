<?php
use B2\Modules\Common\Post;
use B2\Modules\Common\Infomation;
/**
 * 文章内容页
 */
$post_id = get_the_id();
$post_meta = Post::post_meta($post_id);

$info = new Infomation();
$data = $info->infomation_data($post_id);

// var_dump($data);

$name = b2_get_option('normal_custom','custom_infomation_name');
$for = b2_get_option('normal_custom','custom_infomation_for');
$get = b2_get_option('normal_custom','custom_infomation_get');

$metas = '';

if(!empty($data['meta'])){
    foreach ($data['meta'] as $key => $value) {
        $metas .= '<li class="info-meta-'.$key.'">
        <span class="info-meta-key">'.$value['k'].'：</span><span class="info-meta-v">'.$value['v'].'</span>
        </li>';
    }
}

?>

<article class="single-article b2-radius box">
    <div class="infomation-breadcrumb b2-hover mg-b">
        <?php echo Infomation::link_breadcrumb($post_id); ?>
    </div>
    <header class="entry-header">
        <h1>
            <?php echo $data['title']; ?>
        </h1>
        <div id="infomation-meta-header">
            <ul class="post-meta">
                <li class="single-date">
                    <span><?php echo sprintf(__('发布于：%s'),$data['_date']); ?></span>
                </li>
                <li>
                    <span id="b2-post-status" class="red" style="display:none"><?php echo __('待审中','b2'); ?></span>
                </li>
            </ul>
        </div>
    </header>
    <div class="entry-content">
        <?php unset($post_meta); do_action('b2_single_post_content_before'); ?>
        <div class="infomation-meta b2-radius" ref="infomationmeta" data-id="<?php echo $post_id; ?>">
            <div class="info-contact">
            <div class="info-contact-title">
                <span><?php echo __('名片','b2'); ?></span>
                <span>
                    <button @click="b2SingleMeta.followingAc" class="author-has-follow" v-if="b2SingleMeta.following" v-cloak><?php echo __('取消关注','b2'); ?></button>
                    <button @click="b2SingleMeta.followingAc" v-else v-cloak><?php echo b2_get_icon('b2-add-line').__('关注','b2'); ?></button>
                    <button class="empty" @click="b2SingleMeta.dmsg()"><?php echo __('私信','b2'); ?></button>
                </span>
            </div>
                <div class="w-a-info">
                    <img src="<?php echo $data['author']['avatar']; ?>" class="avatar b2-radius"/>
                    <div class="w-a-name">
                        <a href="<?php echo $data['author']['link'];?>" class="link-block"></a> 
                        <p>
                            <span><?php echo $data['author']['name']; ?></span>
                            <?php echo $data['author']['user_title'] ? '<span class="uverify">已认证</span>' : ''; ?>
                        </p> 
                        <div class="w-a-lv">
                            <?php echo $data['author']['lv']['lv']['icon']; ?>
                            <?php echo $data['author']['lv']['vip']['lv'] ? $data['author']['lv']['vip']['icon'] : ''; ?>                       
                        </div>
                    </div>
                </div>
                <div class="info-author-count" v-if="data != ''">
                    <p><span><?php echo $get; ?>：</span><b v-text="data.author_count.get"></b></p>
                    <p><span><?php echo $for; ?>：</span><b v-text="data.author_count.for"></b></p>
                    <p><span><?php echo __('已完成：','b2'); ?></span><b v-text="data.author_count.finish"></b></p>
                    <p><span><?php echo __('进行中：','b2'); ?></span><b v-text="data.author_count.doing"></b></p>
                </div>
                <div class="info-counnect-number">
                    <?php 
                        if(isset($data['contact']['type'])){
                            echo '<p>'.b2_get_icon('b2-bear-smile-line').$data['contact']['type'].'</p><p>'.$data['contact']['number'].'</p>';
                        }else{
                            echo '<p>'.b2_get_icon('b2-bear-smile-line').__('联系方式','b2').'</p><p>'.__('私信或下方留言','b2').'</p>';
                        }
                    ?>
                </div>
            </div>
            <div class="info-box">
                <h2>
                    <?php echo $data['type']['text']; ?>
                    <span><?php echo b2_get_icon('b2-fire-line'); ?><b v-text="b2ContentFooter.postData.views"></b><?php echo __('热度','b2'); ?></span>
                </h2>
                <ul class="infometas">
                    <li v-if="data.sticky == 1 && data.sticky_expired_date" v-cloak>
                        <span class="info-meta-key red"><?php echo __('置顶到期：','b2'); ?></span><span class="info-meta-v" v-text="data.sticky_expired_date"></span>
                    </li>
                    <li v-if="data.status == 0">
                        <span class="info-meta-key"><?php echo __('帖子有效期：','b2'); ?></span><span class="info-meta-v" v-text="data.finish.text"></span>
                    </li>
                    <li>
                        <span class="info-meta-key"><?php echo __('预计价格：','b2'); ?></span><span class="info-meta-v red"><?php echo $data['price'] ? B2_MONEY_SYMBOL.$data['price'] : __('暂无','b2'); ?></span>
                    </li>
                    <li>
                        <span class="info-meta-key"><?php echo __('状态：','b2'); ?></span>
                        <span class="info-meta-v green" v-if="data.status == 0"><?php echo __('进行中','b2'); ?></span>
                        <span class="info-meta-v" v-else v-cloak><?php echo __('已完成','b2'); ?></span>
                    </li>
                    <?php echo $metas ? '<li class="li-line"></li>'.$metas : ''; ?>
                </ul>
                <div class="infomation-vote" v-if="data != ''" v-cloak>
                    <div class="vote-list">
                        <span v-for="(item,i) in data.vote.list" class="b2tips" :data-title="item.name">
                            <a :href="item.link" target="_blank">
                            <?php echo b2_get_img(array(
                                'src_data'=>':src="item[\'avatar\']"',
                                'class'=>array('avatar'),
                                'pic_data'=>' v-if="item[\'avatar\']"',
                                'source_data'=>':srcset="item[\'avatar_webp\']"'
                            ));?>
                            </a>
                        </span>
                        <b v-text="'+'+data.vote.count+'<?php echo __(' 有兴趣','b2'); ?>'"></b>
                    </div>
                    <button @click="b2ContentFooter.vote('up')" :class="b2ContentFooter.postData.up_isset ? 'picked up empty' : 'empty'">
                        <span v-if="b2ContentFooter.postData.up_isset" class="infomation-novote" v-cloak><?php echo b2_get_icon('b2-subtract-line').__('没兴趣了','b2'); ?></span>
                        <span v-else><?php echo b2_get_icon('b2-add-line').__('有兴趣','b2'); ?></span>
                    </button>
                </div>
                
            </div>
        </div>
        <?php the_content(); ?>

        <div class="content-footer">
            <div class="content-footer-poster">
                <button class="poster-span" @click="openPoster()"><?php echo b2_get_icon('b2-share-forward-fill');?><b><?php echo __('海报分享','b2'); ?></b></button>
                <button :class="['text favorite-button',{'sc':postData.favorites_isset}]" @click="postFavoriteAc" v-cloak><?php echo b2_get_icon('b2-star-fill'); ?>{{postData.favorites_isset ? '<?php echo __('已收藏','b2'); ?>' : '<?php echo __('收藏','b2'); ?>'}}</button>
            </div>
        </div>
        <?php do_action('b2_single_post_content_after'); ?>
    </div>

    <?php do_action('b2_single_article_after'); ?>
</article>