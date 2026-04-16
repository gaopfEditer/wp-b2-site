<?php
use B2\Modules\Common\Pay;
get_header();
$data = isset($_GET) ? $_GET : array();
$token = isset($_GET['token']) ? $_GET['token'] : '';

?>

<div class="social-box empty-page" id="pay-page" ref="payPage" data-pay='<?php echo json_encode($data,true); ?>' data-token="<?php echo $token; ?>">
    <div>
        <?php if(!$token){ ?>
            <div id="order-loading" v-if="!token && !error" v-cloak>
                <h2><?php echo __('....订单创建中....','b2'); ?></h2>
                <div class="b2-loading button text empty social-loading"></div>
            </div>
            <div v-if="error">
                <h2 v-text="error"></h2>
            </div>
            <div v-else-if="token" v-cloak>
                <h2><?php echo __('....订单创建成功，数据交换中....','b2'); ?></h2>
                <!-- <div class="b2-loading button text empty social-loading"></div> -->
                <div class="order-paybutton"><a :href="payUrl" class="button"><?php echo __('前往支付','b2'); ?></a></div>
            </div>
        <?php } 
            if($token){
        ?>
            <div id="order-pay">
                <h2><?php echo __('....支付跳转中....','b2'); ?></h2>
                <div class="b2-loading button text empty social-loading"></div>
            </div>
        <?php 
            $pay = Pay::pay($token);
            if(isset($pay['error'])){
                echo '
                    <h2>'.$pay['error'].'<h2>
                    <script>
                        document.querySelector("#order-pay").remove();
                    </script>
                ';
            }else{
                echo $pay;
            }
        } ?>
    </div>
</div>

<?php
get_footer();