<?php
/**
 * 文章内容页
 */
$post_id = get_the_id();
?>
<?php do_action('b2_stream_post_before'); ?>

<div class="s-a-c">
    <div class="s-a-c-t">
        <a :href="item.data.meta.user_link" target="_blank"><?php echo b2_get_img(array(
            'src_data'=>':src="item.data.meta.user_avatar"',
            'class'=>array('b2-radius'),
            'source_data'=>':srcset="item.data.meta.user_avatar_webp"'
        ));?></a>
        <div class="s-a-info">
            <div>
                <div><span><a :href="item.data.meta.user_link" target="_blank" v-text="item.data.meta.user_name"></a></span><span v-if="item.data.meta.lv.lv.lv"  v-html="item.data.meta.lv.lv.icon"></span><span v-if="item.data.meta.lv.vip.lv"  v-html="item.data.meta.lv.vip.icon"></span></div>
                <div class="sah-r">
                    <div class="sah-type"><span v-text="item.data.terms.post_type.name"></span></div>
                    <div class="sah-catlist">
                        <span v-for="cat in item.data.terms.terms">
                            <a :href="cat.link" v-text="cat.name" target="_blank"></a>
                        </span>
                    </div>
                </div>
            </div>
            <div class="sah-date" v-html="item.data.meta.date"></div>
        </div>
    </div>
    <h2><a :href="item.title.link" target="_blank" class="b2-out" v-html="item.title.name"><?php echo get_the_title(); ?></a></h2>
    <div class="s-a-f">
        <div class="saf-z">
            <button :class="item.data.data.up_isset == 1 ? 'picked' : ''" @click="vote('up',item.id,index)"><?php echo b2_get_icon('b2-arrow-drop-up-fill').' '.__('赞','b2'); ?><b v-text="item.data.data.up"></b></button>
            <button :class="item.data.data.down_isset == 1 ? 'picked' : ''" @click="vote('down',item.id,index)"><?php echo b2_get_icon('b2-arrow-drop-down-fill'); ?></button>
        </div>
        <div class="saf-c">
            <span class="saf-comment"><a :href="item.title.link" target="_blank"><?php echo b2_get_icon('b2-chat-smile-2-line'); ?><b v-if="item.data.meta.comment == 0"><?php echo __('参与讨论','b2'); ?></b><b v-else>{{item.data.meta.comment}}<?php echo __('条讨论','b2'); ?></b></a></span>
        </div>
    </div>
</div>
<?php do_action('b2_stream_post_after'); ?>
