<?php
use B2\Modules\Common\Pay;
get_header();

$success = true;
$res = pay::pay_notify('get',$_GET);

if(isset($res['error'])){
    $success = false;
}
?>
<div id="pay-return" class="empty-page pay-return" ref="payReturn">

        <?php if($success){
        ?>  <div class="pay-return-icon">
                <?php echo b2_get_icon('b2-check-double-line'); ?>
            </div>
            <h2 class="green"><?php echo __('支付成功','b2'); ?></h2>
        <?php
        }else{
        ?>
            <div class="pay-return-icon">
                <?php echo b2_get_icon('b2-close-line'); ?>
            </div>
            <h2 class="red"><?php echo __('支付失败','b2'); ?></h2>
        <?php
        }
        ?>
        <p class="payres-button"><a class="empty" href="javascript:window.location.href='about:blank';window.close();"><?php echo __('关闭当前页面','b2'); ?></a><a href="javascript:void(0)" onclick="userTools.goUserPage('orders')" v-if="login" v-cloak><?php echo __('查看我的订单列表','b2'); ?></a><a href="javascript:void(0)" onclick="userTools.goUserPage('back')"><?php echo __('返回商品页','b2'); ?></a></p>
</div>
<?php
get_footer();
