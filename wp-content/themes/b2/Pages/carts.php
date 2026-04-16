<?php
use B2\Modules\Common\Shop;
/**
 * 购物车页面
 */
get_header();
?>
<div id="carts">
    <div class="vip-page wrapper">
        <main id="main" class="site-main b2-radius">
            <div class="box b2-radius" id="carts-list">
                <div class="carts-top">
                    <div :class="step == 1 || step >= 2 ? 'picked' : ''"><?php echo b2_get_icon('b2-wodegouwuche'); ?><?php echo __('我的购物车','b2'); ?></div>
                    <span><?php echo b2_get_icon('b2-arrow-right-s-line'); ?></span>
                    <div :class="step >= 3 || step == 2 ? 'picked' : ''"><?php echo b2_get_icon('b2-jiesuan'); ?><?php echo __('结算','b2'); ?></div>
                    <span><?php echo b2_get_icon('b2-arrow-right-s-line'); ?></span>
                    <div :class="step == 3 ? 'picked' : ''"><?php echo b2_get_icon('b2-fukuanchenggongzhangdan'); ?><?php echo __('支付结果','b2'); ?></div>
                </div>
                <div class="carts-pay-resout" v-if="step == 3" v-cloak>
                    <h2><?php echo b2_get_icon('b2-check-double-line').'<br>'.__('购买成功','b2'); ?></h2>
                    <p v-if="b2token"><?php echo sprintf(__('%s请前往订单中心查看已购买订单%s','b2'),'<a :href="orderPage()">','</a>'); ?></p>
                    <p v-else><?php echo sprintf(__('请前往您的邮箱 %s 查看购买信息，如果找不到，请检查一下是否进入了邮箱的垃圾箱中。','b2'),'<b v-text="pickedEmail"></b>'); ?></p>
                </div>
                <div :class="'step'+step">
                    <ul>
                        <li class="table-bar">
                            <label class="carts-action" v-if="step == 1"><?php echo __('操作','b2'); ?></label>
                            <div class="carts-name"><?php echo __('名称','b2'); ?></div>
                            <div class="carts-price"><?php echo __('价格','b2'); ?></div>
                            <div class="carts-count"><?php echo __('数量','b2'); ?></div>
                            <div class="carts-total"><?php echo __('总价','b2'); ?></div>
                            <div class="carts-action" v-if="step == 1"><?php echo __('操作','b2'); ?></div>
                            <div class="carts-action" v-else-if="step == 3"><?php echo __('支付结果','b2'); ?></div>
                        </li>
                        <li v-if="getLocked">
                            <div class="button empty b2-loading empty-page text"></div>
                        </li>
                        <li v-else-if="Object.keys(data).length == 0" v-cloak>
                            <?php echo B2_EMPTY; ?>
                        </li>
                        <li class="table-info table-bar" v-else v-for="(item,key,index) in data" v-cloak v-if="(pickedProducts(key) && step == 2) || step == 1 || (step == 3 && (picked.indexOf(item.id+'_'+item.index)  !== -1 || picked.indexOf(item.id) !== -1))">
                            <label class="carts-action" v-if="step == 1"><input type="checkbox" v-model="picked" :value="item.id+'_'+item.index" :disabled="id"></label>
                            <div class="carts-name"><img :src="item.thumb" />
                                <div class="cart-name-flex"><a :href="item.link" target="_blank"><span v-text="item.title"></span></a>
                                    <template v-if="item.desc && item.desc.length > 0">
                                        <span v-for="(_item,i) in item.desc" class="shop-item-desc">
                                            <b v-text="_item.name"></b>:
                                            <b v-text="_item.value"></b>
                                        </span>
                                    </template>
                                </div>
                            </div>
                            <div class="carts-price"><?php echo B2_MONEY_SYMBOL; ?><span v-text="item.price.current_price"></span></div>
                            <div class="carts-count"><button @click="countSub(key)" v-if="step == 1" v-show="item.commodity == 1">-</button><span v-text="data[key].count"></span><button @click="countAdd(key)" v-if="step == 1" v-show="item.commodity == 1">+</button></div>
                            <div class="carts-total"><?php echo B2_MONEY_SYMBOL; ?><span v-text="mul(item.price.current_price,item.count)"></span></div>
                            <div class="carts-action" v-if="step == 1"><button class="text" @click="deleteCartsItem(key)" v-if="!id"><?php echo __('删除','b2'); ?></button></div>
                            <div class="carts-action green" v-else-if="step == 3"><?php echo __('购买成功','b2'); ?></div>
                        </li>
                    </ul>
                    <div class="carts-bottom" v-if="Object.keys(data).length != 0 && step != 3" v-cloak>
                        <div class="carts-bottom-left">
                            <label v-if="step == 1">
                                <input type="checkbox" v-model="pickedAll" :disabled="id"><?php echo __('全选','b2'); ?>
                            </label>
                            <span v-if="step == 1 && !id"><button class="text" @click="deleteAll"><?php echo __('删除选中商品','b2'); ?></button></span>
                        </div>
                        <div class="carts-bottom-right">
                            <?php echo __('总价：','b2'); ?><?php echo B2_MONEY_SYMBOL; ?><span v-text="totalMoney()"></span>
                        </div>
                    </div>
                    <div class="carts-comments" v-if="Object.keys(data).length != 0 && step != 3" v-cloak>
                        <textarea placeholder="<?php echo __('给卖家留言','b2'); ?>" v-model="comment"></textarea>
                    </div>
                    <div class="carts-address" v-if="showAddress && Object.keys(data).length != 0 && step != 3" v-cloak>
                        <div class="">
                            <p class="carts-desc"><?php echo __('实物收货地址（必填）：','b2'); ?></p>
                            <p v-if="pickedAddress !== ''"><span v-text="address.addresses[pickedAddress].province ? (address.addresses[pickedAddress].province+' '+address.addresses[pickedAddress].city+' '+address.addresses[pickedAddress].county+' '+address.addresses[pickedAddress].address) : address.addresses[pickedAddress].address"></span><span v-text="address.addresses[pickedAddress].name"></span><span v-text="address.addresses[pickedAddress].phone"></span></p>
                            <p v-else style="color:#999"><?php echo __('收货地址为空，请添加一个收货地址！','b2'); ?></p>
                        </div>
                        <div class="cart-edit-address"><button @click="showAddressBoxAction()" class="text"><?php echo b2_get_icon('b2-edit-2-line').__('编辑收货地址','b2'); ?></button></div>
                    </div>
                    <div class="carts-address" v-if="showEmail && Object.keys(data).length != 0 && step != 3" v-cloak>
                        <div class="">
                            <p class="carts-desc"><?php echo __('虚拟物品接收邮箱（必填）：','b2'); ?></p>
                            <p v-if="pickedEmail !== ''" v-text="pickedEmail"></p>
                            <p v-else style="color:#999"><?php echo __('请设置一个邮箱，用以接收购买后的虚拟物！','b2'); ?></p>
                        </div>
                        <div class="cart-edit-address"><button @click="showEmeilBoxAction()" class="text"><?php echo b2_get_icon('b2-edit-2-line').__('添加收货邮箱','b2'); ?></button></div>
                    </div>
                    <div class="my-coupons" v-if="coupons !== '' && Object.keys(coupons).length > 0 && Object.keys(data).length != 0 && step != 3" v-cloak>
                        <ul>
                            <li v-for="item in coupons" v-if="item.show == true">
                                <div class="shop-coupon-item">
                                    <div :class="'stamp b2-radius ' + couponClass(item) + (pickedCoupon.indexOf(item.id) !== -1 ? ' picked' : '')" @click="pickedCouponArg(item.id)">
                                        <div class="par">
                                            <span class="coupon-id">#{{item.id}}</span>
                                            <sub class="sign"><?php echo B2_MONEY_SYMBOL; ?></sub><span v-text="item.money"></span><sub><?php echo __('优惠劵','b2'); ?></sub>
                                            <div class="coupon-date">
                                                <div>
                                                    <div class="" v-if="item.expiration_date.expired"><?php echo __('使用时效：','b2'); ?><span><?php echo __('无法使用','b2'); ?></span></div>
                                                    <div class="coupon-desc" v-else-if="item.expiration_date.date != 0"><?php echo __('使用时效：','b2'); ?><p><span v-text="item.expiration_date.expired_date"></span><?php echo __('之前','b2'); ?></p></div>
                                                    <div class="coupon-desc" v-else><?php echo __('使用时效：','b2'); ?><?php echo __('永久有效','b2'); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="coupon-use"><?php echo __('使用','b2'); ?></div>
                                        <i class="coupon-bg"></i>
                                    </div>
                                </div>
                            </li>
                        <ul>
                    </div>
                    <?php echo B2\Modules\Templates\VueTemplates::address_box(); ?>
                    <?php echo B2\Modules\Templates\VueTemplates::email_box(); ?>
                    <div class="carts-address-button" v-if="Object.keys(data).length != 0 && step != 3" v-cloak>
                        <button class="text" @click="step = 1"><span v-if="step == 2"><?php echo __('返回修改','b2'); ?></span></button>
                        <div class="pay-total">
                            <?php echo __('合计：','b2').B2_MONEY_SYMBOL; ?>
                            <span v-text="totalPay()"></span>
                            <button @click="pay()"><?php echo __('支付','b2'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
            
        </main>
    </div>
</div>
<?php
get_footer();