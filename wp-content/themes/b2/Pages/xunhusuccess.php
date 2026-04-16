<?php
get_header();
?>
<div id="pay-return" class="empty-page pay-return" ref="payReturn">
    <div class="pay-return-icon">
        <?php echo b2_get_icon('b2-check-double-line'); ?>
    </div>
    <h2 class="green"><?php echo __('支付成功','b2'); ?></h2>
    <p class="payres-button"><a class="empty" href="javascript:window.location.href='about:blank';window.close();"><?php echo __('关闭当前页面','b2'); ?></a><a href="javascript:void(0)" onclick="userTools.goUserPage('orders')" v-if="login" v-cloak><?php echo __('查看我的订单列表','b2'); ?></a><a href="javascript:void(0)" onclick="userTools.goUserPage('back')"><?php echo __('返回商品页','b2'); ?></a></p>
</div>
<?php
get_footer();
