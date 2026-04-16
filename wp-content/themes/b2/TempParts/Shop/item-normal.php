<?php
$post_id = get_the_id();
$title = get_the_title();
?>
<div class="shop-normal-item" data-id="<?php echo $post_id; ?>">
    <div class="shop-normal-item-in box b2-radius">
        <div class="shop-normal-item-img">
        <template v-if="data == ''">
                <img src="<?php echo B2_DEFAULT_IMG; ?>" />
            </template>
            <template v-else v-cloak>
                <?php echo b2_get_img(array(
                'src_data'=>':src="data['.$post_id.'].thumb"',
                'source_data'=>':srcset="data['.$post_id.'].thumb_webp"',
                'alt'=>$title
                )); ?>
            </template>
            <span class="shop-normal-tips" v-if="data !== '' && data[<?php echo $post_id; ?>].price.price_text" v-text="data[<?php echo $post_id; ?>].price.price_text"></span>
            <a class="link-block" href="<?php echo get_permalink($post_id); ?>"></a>
        </div>
        <div class="shop-normal-item-info">
            <h2 class="shop-title">
                <a href="<?php echo get_permalink($post_id); ?>"><?php echo $title; ?></a>
            </h2>
            <div class="shop-normal-item-price">
                <div class="shop-normal-item-left">
                    <span :class="[data !== '' && data[<?php echo $post_id; ?>].price.current_price === data[<?php echo $post_id; ?>].price.price ? 'shop-item-picked' : 'shop-item-delete',data !== '' && data[<?php echo $post_id; ?>].price.price === '' ? 'shop-item-hidden' : '','shop-item-normal-price']" v-cloak>
                        <i><?php echo B2_MONEY_SYMBOL; ?></i>    
                        <b v-if="data != ''" v-text="data[<?php echo $post_id; ?>].price.price"></b>
                        <b v-else>--</b>
                    </span>
                    <span :class="['shop-item-d-price',data !== '' && data[<?php echo $post_id; ?>].price.d_price === data[<?php echo $post_id; ?>].price.current_price ? 'shop-item-picked' : 'shop-item-delete',data !== '' && data[<?php echo $post_id; ?>].price.d_price === '' ? 'shop-item-hidden' : '']" v-cloak>
                        <i><?php echo B2_MONEY_SYMBOL; ?></i>
                        <b v-if="data != ''" v-text="data[<?php echo $post_id; ?>].price.d_price"></b>
                        <b v-else>--</b>
                    </span>
                    <span :class="['shop-item-u-price',data !== '' && data[<?php echo $post_id; ?>].price.u_price === data[<?php echo $post_id; ?>].price.current_price && data[<?php echo $post_id; ?>].is_vip ? 'shop-item-picked' : 'shop-item-delete',data !== '' && data[<?php echo $post_id; ?>].price.u_price === '' ? 'shop-item-hidden' : '']" v-cloak>
                        <i><?php echo B2_MONEY_SYMBOL; ?></i>
                        <b v-if="data != ''" v-text="data[<?php echo $post_id; ?>].price.u_price"></b>
                        <b v-else>--</b>
                    </span>
                </div>
                <div class="shop-normal-item-right">
                    <button :disabled="data !== '' && !data[<?php echo $post_id; ?>].can_buy.allow" v-text="data !== '' ? data[<?php echo $post_id; ?>].can_buy.text : '<?php echo __('购买','b2'); ?>'" @click="go('<?php echo get_permalink($post_id); ?>')"></button>
                </div>
            </div>
            <div class="shop-normal-item-count">
                <span><?php echo __('库存：','b2'); ?><b v-if="data === ''">--</b><b v-else v-text="data[<?php echo $post_id; ?>].stock.total"></b></span>
                <span v-show="data !== '' && data[<?php echo $post_id; ?>].stock.sell !== ''" v-cloak><?php echo __('已售：','b2'); ?><b v-if="data === ''">--</b><b v-else v-text="data[<?php echo $post_id; ?>].stock.sell"></b></span>
                <span><?php echo __('人气：','b2'); ?><b v-if="data === ''">--</b><b v-else v-text="data[<?php echo $post_id; ?>].views"></b></span>
            </div>
        </div>
    </div>
</div>