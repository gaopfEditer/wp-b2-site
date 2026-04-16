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
            <a class="link-block" href="<?php echo get_permalink($post_id); ?>"></a>
        </div>
        <div class="shop-normal-item-info shop-exchange-item-info">
            <h2 class="shop-title">
                <a href="<?php echo get_permalink($post_id); ?>"><?php echo $title; ?></a>
            </h2>
            <div class="shop-normal-item-price">
                <div class="shop-normal-item-left">
                    <div class="shop-item-price-credit">
                        <span :class="data != '' && data[<?php echo $post_id; ?>].price.price ? 'shop-item-delete' : ''" v-cloak>
                            <?php echo __('原价：','b2'); ?><i><?php echo B2_MONEY_SYMBOL; ?></i>
                            <b v-if="data != ''" v-text="data[<?php echo $post_id; ?>].price.price"></b>
                        </span>
                        <span class="shop-item-credit" v-cloak>
                            <?php echo b2_get_icon('b2-coin-line'); ?>
                            <b v-if="data != ''" v-text="data[<?php echo $post_id; ?>].price.current_price"></b>
                        </span>
                    </div>
                </div>
                <div class="shop-normal-item-right">
                    <button :disabled="data !== '' && !data[<?php echo $post_id; ?>].can_buy.allow" v-text="data !== '' ? data[<?php echo $post_id; ?>].can_buy.text : '<?php echo __('兑换','b2'); ?>'" @click="go('<?php echo get_permalink($post_id); ?>')"></button>
                </div>
            </div>
            <div class="total-rodia">
                <span :style="'width:'+data[<?php echo $post_id; ?>].stock.scale+'%'" v-if="data !== ''"></span>
            </div>
            <div class="shop-normal-item-count">
                <span><?php echo __('库存：','b2'); ?><b v-if="data === ''">--</b><b v-else v-text="data[<?php echo $post_id; ?>].stock.total"></b></span>
                <span v-show="data !== '' && data[<?php echo $post_id; ?>].stock.sell !== ''" v-cloak><?php echo __('已兑：','b2'); ?><b v-if="data === ''">--</b><b v-else v-text="data[<?php echo $post_id; ?>].stock.sell"></b></span>
                <span><?php echo __('人气：','b2'); ?><b v-if="data === ''">--</b><b v-else v-text="data[<?php echo $post_id; ?>].views"></b></span>
            </div>
        </div>
    </div>
</div>