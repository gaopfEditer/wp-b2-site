<?php
$user_id =  get_query_var('author');
$paged = get_query_var('b2_paged') ? get_query_var('b2_paged') : 1;
$pagename = get_query_var('b2_user_page');
?>

<div id="myorders" class="my-orders box b2-radius b2-pd" ref="myorders" data-paged="<?php echo $paged; ?>">
    <div class="button empty b2-loading empty-page text" v-if="list === ''"></div>
    <div class="" v-else-if="list.length == 0" v-cloak>
        <?php echo B2_EMPTY; ?>
    </div>
    <div class="order-list" v-else v-cloak>
        <ul>
            <li v-for="(item,i) in list" :key="i">
                <div class="order-id">
                    <span><?php echo __('订单号：','b2'); ?>{{item.order_id}}</span>
                </div>
                <div class="order-title">
                    <a :href="item.order_name.link" class="order-title-thumb"><img :src="item.thumb" class="b2-radius"/></a>
                    <div class="p-title-row">
                        <div>
                            <a :href="item.order_name.link" class="order-title-name">{{item.order_name.name}}</a>
                            <div v-if="item.desc.length > 0" class="shop-item-desc">
                                <p v-for="(t,index) in item.desc" :key="index">
                                    <span v-text="t.name"></span>：<span v-text="t.value"></span>
                                </p>
                            </div>
                        </div>
                        <div v-if="item._order_state == 'w'" class="order-back">
                            <button class="empty text" v-if="item._order_state != 'q'" @click="userChangeOrderState(item.order_id,i)"><?php echo __('继续支付','b2'); ?></button>
                        </div>
                        <div v-else-if="item.order_commodity == 1 && item._order_state != 'f'" class="order-back">
                            <button class="empty text" v-if="item._order_state != 'q'" @click="userChangeOrderState(item.order_id,i)"><?php echo __('确认收货','b2'); ?></button>
                            <button class="empty text" disabled="true" v-else><?php echo __('已签收','b2'); ?></button>
                            <p class="order_state_desc" v-if="item._order_state != 'q' && item.pass_days"><?php echo sprintf(__('%s后自动确认','b2'),'{{item.pass_days}}'); ?></p>
                            <p class="order_state_desc red" v-else @click="b2Dmsg.userid = b2_global.shop_after_sale;b2Dmsg.show = true"><?php echo __('申请售后','b2'); ?></p>
                        </div>
                    </div>
                </div>
                <div class="g-list" v-if="item.g_list.length > 0">
                    <div v-for="g,gi in item.g_list" :key="gi" class="g-list-item">
                        <div>
                            <img :src="g.thumb" class="g-thumb"/>
                        </div>
                        <div>
                            <a :href="g.link" target="_blank" v-text="g.title"></a>
                            <div  class="g-list-meta b2flex shop-item-desc">
                                <template v-if="g.desc.length > 0" class="shop-item-desc">
                                    <p v-for="(t,index) in g.desc" :key="index">
                                        <span v-text="t.name"></span>：<span v-text="t.value"></span><span class="dot">/</span>
                                    </p>
                                </template>
                                <p><span><?php echo __('数量：','b2'); ?></span><span v-text="g.count"></span></p>
                                <span class="dot">/</span>
                                <p><span><?php echo __('金额：','b2'); ?></span><span v-text="'<?php echo B2_MONEY_SYMBOL;?>'+ g.price.current_price"></span></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="order-status">
                    <div class="order-type">
                        <p><?php echo __('类型','b2'); ?></p>
                        <span v-text="item.order_type"></span>
                    </div>
                    <div class="order-date">
                        <p><?php echo __('日期','b2'); ?></p>
                        <span v-text="item.order_date"></span>    
                    </div>
                    <div class="order-state">
                        <p><?php echo __('状态','b2'); ?></p>
                        <span v-text="item.order_state"></span>    
                    </div>
                </div>
                <div class="order-money">
                    <div class="order-price">
                        <p><?php echo __('单价','b2'); ?></p>
                        <span v-if="item.money_type == 1"><?php echo b2_get_icon('b2-coin-line'); ?>{{item.order_price}}</span>
                        <span v-if="item.money_type == 0"><?php echo B2_MONEY_SYMBOL; ?>{{item.order_price}}</span>
                    </div>
                    <div class="order-count">
                        <p><?php echo __('数量','b2'); ?></p>
                        <span>{{item.order_count}}</span>
                    </div>
                    <div class="order-total green">
                        <p><?php echo __('总价','b2'); ?></p>
                        <span v-if="item.money_type == 1"><?php echo b2_get_icon('b2-coin-line'); ?>{{item.order_total}}</span>
                        <span v-if="item.money_type == 0"><?php echo B2_MONEY_SYMBOL; ?>{{item.order_total}}</span>
                    </div>
                </div>
                <div class="order-money tracking-number">
                    <div>
                        <p><?php echo __('收件地址','b2'); ?></p>
                        <span>{{item.address ? item.address : '<?php echo __('无','b2'); ?>'}}</span>
                    </div>
                    <div class="order-count">
                        <p><?php echo __('快递信息','b2'); ?></p>
                        <span v-if="item.tracking_number.type">{{item.tracking_number.type}}: {{item.tracking_number.number}}</span>
                        <span v-else><?php echo __('暂无','b2'); ?></span>
                    </div>
                    <div>
                        <p><?php echo __('物流追踪','b2'); ?></p>
                        <button class="text" @click="getExpressInfo(item.id,item.tracking_number.number,item.address,item.tracking_number.com)" v-if="item.tracking_number.type"><?php echo __('查看','b2'); ?></button>
                        <span v-else><?php echo __('没有数据','b2'); ?></span>
                    </div>
                </div>
                <div class="order-content order-title" v-if="item.order_content">
                    <div v-html="item.order_content"></div>
                </div>
            </li>
        <ul>
    </div>
    <div class="b2-pagenav" style="margin:0;padding:0">
        <page-nav ref="commentPageNav" paged="<?php echo $paged; ?>" navtype="json" :pages="pages" type="p" box=".order-list ul" :opt="options" :api="api" url="<?php echo get_author_posts_url($user_id).'/'.$pagename; ?>" title="" @return="get"></page-nav>
    </div>
    <div :class="['modal','order-box',{'show-modal':show}]" v-cloak>
        <div class="modal-content search-box-content b2-radius">
            <span class="close-button" @click="show = false">×</span> 
            <div class="express-error" v-if="!express[id]">
                <?php echo __('快递查询中，请稍后...','b2'); ?>
            </div>
            <div class="order-express-list" v-else>
                <div class="express-error" v-if="express[id].ret_code === -1">
                    {{express[id].msg}}
                </div>
                <div class="express-list" v-else>
                    <div class="express-list-top">
                        <p>{{express[id].expTextName}}</p>
                        <p>{{express[id].mailNo}}</p>
                    </div>
                    <ul>
                        <li v-for="item in express[id].data">
                            <p class="express-time" v-text="item.time"><p>
                            <p v-text="item.context"></p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>