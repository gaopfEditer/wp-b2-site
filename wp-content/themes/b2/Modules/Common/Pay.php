<?php namespace B2\Modules\Common;

class Pay{

    //检查目前使用的是什么支付平台的
    public static function pay_type($type){

        $arg = array('alipay_normal','xunhu','alipay_hupijiao','mapay','pay020','xorpay','yipay','wecatpay_normal','wecatpay_hupijiao','payjs','paypal','balance','credit','card','coupon','alipay','wecatpay','suibian','baidu','toutiao');

        if(!in_array($type,$arg)) return array('error'=>__('支付类型错误！','b2'));

        if(strpos($type,'alipay') !== false){
            
            $alipay_type = b2_get_option('normal_pay','alipay');
            if(!$alipay_type) return array('error'=>__('未启用支付宝','b2'));
            return array(
                'pick'=>'alipay',
                'type'=>$alipay_type
            );

        }elseif(strpos($type,'wecatpay') !== false){

            $wecatpay_type = b2_get_option('normal_pay','wecatpay');
            if(!$wecatpay_type) return array('error'=>__('未启用微信支付','b2'));
            return array(
                'pick'=>'wecatpay',
                'type'=>$wecatpay_type
            );

        }elseif($type === 'balance'){
            return array(
                'pick'=>'balance',
                'type'=>'balance'
            );
        }elseif($type === 'credit'){
            return array(
                'pick'=>'credit',
                'type'=>'credit'
            );
        }elseif($type === 'card'){
            return array(
                'pick'=>'card',
                'type'=>'card'
            );
        }elseif($type === 'coupon'){
            return array(
                'pick'=>'coupon',
                'type'=>'coupon'
            );
        }elseif($type === 'paypal'){
            return array(
                'pick'=>'paypal',
                'type'=>'paypal'
            );
        }elseif($type === 'baidu'){
            
            return array(
                'pick'=>'baidu',
                'type'=>'baidu'
            );
        }elseif($type === 'toutiao'){
            
            return array(
                'pick'=>'toutiao',
                'type'=>'toutiao'
            );
        }

        return array('error'=>__('未知的支付类型','b2'));
    }

    //通过平台获取支付方式
    public static function check_pay_type($type){

        $pay_type = self::pay_type($type);

        if(isset($pay_type['error'])) return $pay_type;

        return self::chekc_jump($pay_type);

    }

    //通过支付方式判断是扫码还是跳转支付
    public static function chekc_jump($type){
        $pay_type = 'scan';

        $is_mobile = wp_is_mobile();
        $is_weixin = b2_is_weixin();
        $is_pcweixin = isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false && strpos($_SERVER['HTTP_USER_AGENT'], 'WindowsWechat') !== false;

        switch ($type['type']) {
            case 'alipay_normal':
                $alipay_type = b2_get_option('normal_pay','alipay_type');
                if($alipay_type === 'normal'){
                    $pay_type = 'jump';
                }elseif(!$is_mobile){
                    $pay_type = 'scan';
                }else{
                    $pay_type = 'jump';
                }
                break;
            case 'xorpay':
                if($is_mobile && $type['pick'] == 'alipay'){
                    $pay_type = 'jump';
                }elseif($is_weixin){
                    $pay_type = 'mweb';
                }elseif(!$is_mobile){
                    $pay_type = 'scan';
                }
                break;
            case 'wecatpay_normal':
                if($is_weixin && !$is_pcweixin){
                    $pay_type = 'jsapi';
                }elseif($is_mobile){
                    $pay_type = 'mweb';
                }else{
                    $pay_type = 'scan';
                }
                break;
            case 'balance':
                $pay_type = 'balance';
                break;
            case 'xunhu':
                if($is_weixin){
                    $pay_type = 'mweb';
                }elseif($is_mobile && $type['pick'] == 'alipay'){
                    $pay_type = 'mweb';
                }elseif($is_mobile && $type['pick'] == 'wecatpay'){
                    $pay_type = 'jump';
                }else{
                    $pay_type = 'scan';
                }
                break;
            case 'alipay_hupijiao':
            case 'wecatpay_hupijiao':
                $pay_type = 'jump';
                break;
            case 'payjs':
                if($is_weixin){
                    $pay_type = 'jsapi';
                }else{
                    $pay_type = 'scan';
                }
                break;
            case 'pay020':
                $type = trim(b2_get_option('normal_pay','020pay_type'), " \t\n\r\0\x0B\xC2\xA0");
                if($type == 1){
                    $pay_type = 'jump';
                }else{
                    $pay_type = 'scan';
                }
                break;
            case 'mapay':
                $type = trim(b2_get_option('normal_pay','mapay_type'), " \t\n\r\0\x0B\xC2\xA0");
                if($type == 1){
                    $pay_type = 'jump';
                }else{
                    $pay_type = 'scan';
                }
                break;
            case 'yipay':
            case 'suibian':
                $pay_type = 'jump';
                break;
            case 'credit':
                $pay_type = 'scan';
                break;
            case 'card':
                $pay_type = 'card';
                break;
            case 'paypal':
                $pay_type = 'jump';
                break;
            case 'baidu':
            case 'toutiao':
                $pay_type = 'jsapi';
                break;
        }

        $paytype = array(
            'pay_type'=>$pay_type,
            'is_mobile'=>$is_mobile,
            'is_weixin'=>$is_weixin
        );
        
        if($pay_type === 'card'){
            $paytype['card_text'] = b2_get_option('normal_gold','card_text');
        }

        return apply_filters('b2_pay_type',$paytype,$type);
    }

    //获取当前平台，允许使用的支付方式
    public static function allow_pay_type($show_type){

        $user_id = b2_get_current_user_id();

        $is_mobile = wp_is_mobile();
        $is_weixin = b2_is_weixin();
        $array = array(
            'wecatpay'=>true,
            'alipay'=>true,
            'balance'=>$user_id ? true : false,
            'paypal'=>true,
            'card'=>false
        );

        //获取当前的支付方式
        $alipay_type = b2_get_option('normal_pay','alipay');
        $wecatpay_type = b2_get_option('normal_pay','wecatpay');
      
        switch ((string)$alipay_type) {
            case '0':
                $array['alipay'] = false;
                break;
        }
        
        switch ((string)$wecatpay_type) {
            case '0':
                $array['wecatpay'] = false;
                break;
        }

        if($show_type == 'cz'){
            $array['card'] = (int)b2_get_option('normal_gold','card_allow');
            $array['balance'] = false;
        }
        
        if($show_type == 'cg'){
            $array['min'] = (int)b2_get_option('normal_gold','credit_qc');
            $array['dh'] = (int)b2_get_option('normal_gold','credit_dh');
        }

        $array['paypal'] = (int)b2_get_option('normal_pay','paypal_open') === 1;

        $money = get_user_meta($user_id,'zrz_rmb',true);
        $money = $money ? $money : 0;

        $array['money'] =  $money;
        $array['mp'] =  get_user_meta($user_id,'zrz_mpweixin_pay_uid',true) ? true : false;
        $array['h5_weixin'] =  get_user_meta($user_id,'zrz_weixin_uid',true) ? true : false;

        return $array;
        
    }

    public static function xml_parser($str){ 
        $xml_parser = xml_parser_create(); 
        if(!xml_parse($xml_parser,$str,true)){ 
            xml_parser_free($xml_parser); 
            return false; 
        }else {
            xml_parser_free($xml_parser); 
            return true; 
        } 
    }

    public static function is_json($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    //选择支付平台
    public static function pay($data){
        
        $data = apply_filters('b2_pay_before', $data);
        
        if(isset($data['error'])) return $data;

        if(isset($data['pay_type'])){
            $type = $data['pay_type'];
            $data['title'] = str_replace(array('&','=',' '),'',$data['title']);

            return self::$type($data);
        }
    }

    //积分支付
    public static function credit($data){
        
        if(!$data['user_id']) return array('error'=>__('请先登录','b2'));
        
        if($data['order_type'] === 'c'){
            return self::credit_pay($data['order_id']);
        }
        
        return $data['order_id'];
    }

    public static function credit_pay($order_id){
        global $wpdb;
        $table_name = $wpdb->prefix . 'zrz_order';

        $data = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_name WHERE `order_id`=%s",$order_id),
            ARRAY_A
        );

        if(empty($data)) return array('error'=>__('支付信息错误','b2'));

        if($data['order_state'] !== 'w') return array('error'=>__('支付信息错误','b2'));

        if($data['pay_type'] !== 'credit') return array('error'=>__('支付类型错误','b2'));

        if(!$data['user_id']) return array('error'=>__('请先登录','b2'));
        
        $credit = Credit::credit_change($data['user_id'],-$data['order_total']);

        if($credit === false){
            return array('error'=>__('积分余额不足','b2'));
        }

        $data = apply_filters('b2_credit_pay_after', $data,$credit);

        return Orders::order_confirm($data['order_id'],$data['order_total']);
    }

    //订单回调
    public static function pay_notify($method,$post){

        $post = apply_filters('b2_pay_notify_action', $post);

        if(isset($post['token']) && isset($post['PayerID'])){
            return self::paypal_notify($method,$post);
        }

        $hupijiao = isset($post['hash']) && isset($post['trade_order_id']);
        $xunhupay = isset($post['mchid']) && isset($post['out_trade_no']) && isset($post['order_id']);

        $order_id = '';

        if(isset($post['out_trade_no'])){
            $order_id = $post['out_trade_no'];
        }

        if($hupijiao){
            $order_id = $post['trade_order_id'];
        }

        if(isset($post['aoid']) && isset($post['pay_price']) && isset($post['order_id'])){
            $order_id = $post['order_id'];
        }

        if(isset($post['userID']) && isset($post['trueID'])){
            $order_id = $post['pay_id'];
        }

        if(isset($post['item_number']) && isset($post['residence_country'])){
            $order_id = $post['item_number'];
        }

        if(isset($post['orderid']) && isset($post['account_name'])){
            $order_id = $post['orderid'];
        }

        if(isset($post['dealId']) && isset($post['hbBalanceMoney'])){
            $order_id = $post['tpOrderId'];
        }

        if(isset($post['msg'])){
            
            $tt_notify_data = json_decode($post['msg'], true);

            if($post){
                $order_id = $tt_notify_data['cp_orderno'];
            }
            
        }

        if(!$order_id) return array('error'=>__('订单获取失败','b2'));

        global $wpdb;
        $table_name = $wpdb->prefix . 'zrz_order';
        $order = $wpdb->get_row(
            $wpdb->prepare("
                SELECT * FROM $table_name
                WHERE order_id LIKE %s 
                ",
                '%'.$order_id.'%'
            )
        ,ARRAY_A);

        if(!$order) return '';

        $type = apply_filters('b2_order_check_action', array('order'=>$order,'hupijiao'=>$hupijiao,'xunhupay'=>$xunhupay));

        if(!$type) return '';

        if(isset($type['error'])) return $type;

        if($type === 'xunhu') return self::xunhu_notify($post,$order);
        
        $type = $type.'_notify';
        
        $_POST = $post;
        $_GET = $post;
        //update_user_meta(1,'paypal_data2',$post);

        if(!method_exists(__CLASS__,$type)) return '';

        return self::$type($method,$post);
    }

    //余额支付
    public static function balance($data){

        if(!$data['user_id']) return array('error'=>__('请先登录','b2'));

        return $data['order_id'];
        
    }

    public static function balance_pay($order_id){

        global $wpdb;
        $table_name = $wpdb->prefix . 'zrz_order';

        $data = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_name WHERE `order_id`=%s",$order_id),
            ARRAY_A
        );

        if(empty($data)) return array('error'=>__('支付信息错误','b2'));

        if($data['order_state'] !== 'w') return array('error'=>__('支付信息错误','b2'));

        if($data['pay_type'] !== 'balance') return array('error'=>__('支付类型错误','b2'));

        if(!$data['user_id']) return array('error'=>__('请先登录','b2'));

        $money = User::money_change($data['user_id'],-$data['order_total']);

        if($money === false){
            return array('error'=>sprintf(__('%s不足','b2'),B2_MONEY_NAME));
        }

        $data = apply_filters('b2_balance_pay_after', $data,$money);

        if(isset($data['error'])) return $data;
 
        return Orders::order_confirm($data['order_id'],$data['order_total']);
    }

    /*-----------------------------------易支付----------------------------------------*/

    public static function yipay($data){
        $settings = array(
            'pid'=>trim(b2_get_option('normal_pay','yipay_id'), " \t\n\r\0\x0B\xC2\xA0"),
            'type'=>$data['_pay_type'] == 'alipay' ? 'alipay' : 'wxpay',
            'sitename'=>B2_BLOG_NAME,
            'out_trade_no'=>$data['order_id'],
            'notify_url'=>b2_get_custom_page_url('notify'),
            'return_url'=>b2_get_custom_page_url('xunhusuccess'),
            'name'=>$data['title'],
            'money'=>$data['order_total'],
            'sign_type'=>'MD5'
        );

        ksort($settings);
        reset($settings);

        $sign = '';
        $urls = '';

        foreach ($settings AS $key => $val) {
            if ($val == '' || $key == 'sign' || $key == 'sign_type') continue;
            if ($sign != '') {
                $sign .= "&";
                $urls .= "&";
            }
            $sign .= "$key=$val";
            $urls .= "$key=" . urlencode($val);
        }

        $query = $urls . '&sign=' . md5($sign.trim(b2_get_option('normal_pay','yipay_key'), " \t\n\r\0\x0B\xC2\xA0")).'&sign_type=MD5';
        $url = rtrim(b2_get_option('normal_pay','yipay_gateway'), '/');
        $url = $url.'/submit.php?'.$query;

        return array(
            'id'=>$data['order_id'],
            'url'=>$url
        );

    }

    //回调
    public static function yipay_notify($method,$data){
        if(isset($data['trade_status']) && $data['trade_status'] === 'TRADE_SUCCESS'){
            ksort($data);
            reset($data);
    
            $sign = '';
    
            foreach ($data AS $key => $val) {
                if ($val == '' || $key == 'sign' || $key == 'sign_type') continue;
                if ($sign != '') {
                    $sign .= "&";
                    $urls .= "&";
                }
                $sign .= "$key=$val";
            }

            $sign = md5($sign .trim(b2_get_option('normal_pay','yipay_key'), " \t\n\r\0\x0B\xC2\xA0"));

            if(!$sign) return array('error'=>__('支付回调错误','b2'));

            if($sign === $data['sign']){
                if($method == 'get'){
                    return 'success';
                }else{
                    $res = Orders::order_confirm($data['out_trade_no'],false);
                    if(isset($res['error'])){
                        //file_put_contents('notify.txt', 'mapay error:'.$res['error'], FILE_APPEND);
                        return $res;
                    }else{
                        return 'success';
                    }
                }
            }
        }

        return array('error'=>__('支付回调错误','b2'));
    }

    /*-----------------------------------xorpay支付----------------------------------------*/

    public static function xorpay($data){

        $is_weixin = b2_is_weixin();
        $is_mobile = wp_is_mobile();

        $pay_type = 'alipay';

        if($is_weixin || ($is_mobile && $data['_pay_type'] === 'wecatpay')){
            $pay_type = 'jsapi';
        }elseif($data['_pay_type'] === 'wecatpay'){
            $pay_type = 'native';
        }else{
            $pay_type = 'alipay';
        }

        $settings = array(
            'name'=>$data['title'],
            'pay_type'=>$pay_type,
            'price'=>$data['order_total'],
            'order_id'=>$data['order_id'],
            'notify_url'=>b2_get_custom_page_url('notify')
        );
        $aid = trim(b2_get_option('normal_pay','xorpay_aid'), " \t\n\r\0\x0B\xC2\xA0");
        
        $secret = trim(b2_get_option('normal_pay','xorpay_secret'), " \t\n\r\0\x0B\xC2\xA0");

        $sign = $settings['name'].$settings['pay_type'].$settings['price'].$settings['order_id'].$settings['notify_url'].$secret;
        $settings['sign'] = md5($sign);

        if($is_weixin){
            $settings['return_url'] = b2_get_custom_page_url('xunhusuccess');
            $settings['cancel_url'] = $data['redirect_url'];
            // $res = wp_remote_post('https://xorpay.com/api/cashier/'.$aid,array(
            //     'body'=>$settings
            // ));

            // $url = wp_remote_retrieve_body( $res );

            // preg_match('/location.href = "(.+)"/', $url, $match);
            return array(
                'url'=>"https://xorpay.com/api/cashier/".$aid."?name=".urlencode($settings['name'])."&pay_type=".$settings['pay_type']."&price=".$settings['price']."&order_id=".$settings['order_id']."&order_uid=".$settings['order_uid']."&notify_url=".$settings['notify_url']."&sign=".$settings['sign'],
                'order_id'=>$data['order_id'],
            );
        }

        if (($is_mobile && $data['_pay_type'] === 'wecatpay')){
            $api_url = "https://xorpay.com/api/cashier/".$aid."?name=".urlencode($settings['name'])."&pay_type=".$settings['pay_type']."&price=".$settings['price']."&order_id=".$settings['order_id']."&order_uid=".$settings['order_uid']."&notify_url=".$settings['notify_url']."&sign=".$settings['sign'];
            return array(
                'is_weixin'=>$is_weixin,
                'is_mobile'=>$is_mobile,
                'order_id'=>$data['order_id'],
                'qrcode'=>$api_url
            );
        }

        $res = wp_remote_post('https://xorpay.com/api/pay/'.$aid,array(
            'body'=> $settings
        ));

        if ( is_wp_error($res) ) {
            return array('error'=> $res->get_error_message());
        } else {
            $resout = json_decode(wp_remote_retrieve_body( $res ), true );
            if($resout['status'] === 'ok'){
                $resout = $resout['info'];
                
                if($data['jump'] === 'jump'){

                    return array(
                        'id'=>$data['order_id'],
                        'url'=>$resout['qr']
                    );

                }

                return array(
                    'is_weixin'=>$is_weixin,
                    'is_mobile'=>$is_mobile,
                    'order_id'=>$data['order_id'],
                    'qrcode'=>$resout['qr']
                );
            }else{
                return array('error'=>$resout['status']);
            }
        }

        return array('error'=>__('支付失败','b2'));
    }

    public static function xorpay_notify($method,$post){

        if(!isset($post['aoid']) || !isset($post['sign']) || !isset($post['order_id']) || !isset($post['pay_price']) || !isset($post['pay_time'])) return array('error'=>__('参数不全','b2'));

        $secret = trim(b2_get_option('normal_pay','xorpay_secret'), " \t\n\r\0\x0B\xC2\xA0");

        if(!$secret) return array('error'=>__('支付回调错误','b2'));

        $sign = md5($post['aoid'].$post['order_id'].$post['pay_price'].$post['pay_time'].$secret);

        if($post['sign'] === $sign){
            //更新订单数据
            $res = Orders::order_confirm($post['order_id'],false);
            if(isset($res['error'])){
                //file_put_contents('notify.txt', 'xorpay error:'.$res['error'], FILE_APPEND);
                return $res;
            }else{
                return 'success';
            }
        }

        return array('error'=>__('参数校验错误','b2'));
    }

    /*-----------------------------------202支付----------------------------------------*/
    public static function pay020($data){
        $identification = trim(b2_get_option('normal_pay','pay020_identification'), " \t\n\r\0\x0B\xC2\xA0");
        $token = trim(b2_get_option('normal_pay','pay020_token'), " \t\n\r\0\x0B\xC2\xA0"); 

        $_data = array(
            "goodsname"=>$data['title'],
            "identification" => $identification,
            "notify_url"=>b2_get_custom_page_url('notify'),
            "orderid"=>$data['order_id'],
            "orderuid"=>(int)$data['user_id'] ? $data['user_id'] : 'guest',
            "price"=>$data['order_total']*100,
            "return_url"=>b2_get_custom_page_url('xunhusuccess'),
            "token"=>$token,
            'type'=>$data['_pay_type'] === 'alipay' ? 2 : 1
        );

        ksort($_data);
        reset($_data);

        $sign = '';
        $url = '';

        foreach ($_data AS $key => $val) {
            if ($sign != '') {
                $sign .= "&";
                if($key !== 'token'){
                    $url .= "&";
                }
            }
            $sign .= "$key=$val";
            if($key !== 'token'){
                $url .= "$key=$val";
            }
        }

        $k = md5($_data['goodsname']. $_data['identification']. $_data['notify_url']. $_data['orderid']. $_data['orderuid']. $_data['price']. $_data['return_url']. $_data['token']. $_data['type']);

        unset($_data['token']);
        
        if($data['jump'] == 'jump'){

            return array(
                'id'=>$data['order_id'],
                'url'=>'https://pay.020zf.com/?'.$url.'&key='.$k
            );
            
        }

        $query = $url.'&key='.$k;

        $url = "https://data.020zf.com/index.php?s=/api/pp/index_show.html&{$query}";
        $res = wp_remote_get($url);

        $res = json_decode(wp_remote_retrieve_body( $res ), true );
        if(isset($res['code']) && (int)$res['code'] !== 200) return array('error'=>$res['data']);

        $is_weixin = b2_is_weixin();
        $is_mobile = wp_is_mobile();

        return array(
            'type'=>'pay020',
            'is_weixin'=>$is_weixin,
            'is_mobile'=>$is_mobile,
            'order_id'=>$data['order_id'],
            'qrcode'=>'https://data.020zf.com/api.php/pp/scerweima2?url='.$res['data']['qrcode']
        );
    }

    public static function pay020_notify($method,$post){
        $token = trim(b2_get_option('normal_pay','pay020_token'), " \t\n\r\0\x0B\xC2\xA0");
        if(!$token) return array('error'=>__('支付回调错误','b2'));
        //回调过来的post值
        $bill_no = $post["bill_no"];                  //一个24位字符串，是此订单在020ZF服务器上的唯一编号
        $orderid = $post["orderid"];                  //是您在发起付款接口传入的您的自定义订单号
        $price = $post["price"];                      //单位：分。是您在发起付款接口传入的订单价格
        $actual_price = $post["actual_price"];        //单位：分。一定存在。表示用户实际支付的金额。
        $orderuid = $post["orderuid"];              //如果您在发起付款接口带入此参数，我们会原封不动传回。
        $key = $post["key"];                     
        
        $notify_key = md5($actual_price.$bill_no.$orderid.$orderuid.$price.$token);
      
        if($key == $notify_key){
            $res = Orders::order_confirm($orderid,false);
            if(isset($res['error'])){
                //file_put_contents('notify.txt', 'mapay error:'.$res['error'], FILE_APPEND);
                return $res;
            }else{
                return 'success';
            }
        }
    }

    /*-----------------------------------随便付----------------------------------------*/
    public static function suibian($data){
        $id = trim(b2_get_option('normal_pay','suibian_id'), " \t\n\r\0\x0B\xC2\xA0");
        $key = trim(b2_get_option('normal_pay','suibian_key'), " \t\n\r\0\x0B\xC2\xA0"); 

        $_data = array(
            "mch_uid" => $id,
            "out_trade_no"=>$data['order_id'],
            'pay_type_id'=>$data['_pay_type'] === 'alipay' ? 2 : 1,
            'total_fee' => $data['order_total'],
            'mepay_type'=>2,
            'return_type'=>1,
            "notify_url"=>b2_get_custom_page_url('notify'),
            "return_url"=>b2_get_custom_page_url('xunhusuccess')
        );

        ksort($_data);
        reset($_data);

        $sign = '';
        $urls = '';

        foreach ($_data AS $k => $val) {
            if ($val == ''||$k == 'sign') continue;
            if ($sign != '') {
                $sign .= "&";
                $urls .= "&";
            }
            $sign .= "$k=$val";
            $urls .= "$k=" . urlencode($val);
        }

        $query = $urls . '&sign=' . md5($sign .$key);
        $url = "https://www.sbpay.cn/pay.html?{$query}";

        if($data['jump'] == 'jump'){

            return array(
                'id'=>$data['order_id'],
                'url'=>$url
            );

        }

        $res = wp_remote_get($url);
        $res = json_decode(wp_remote_retrieve_body( $res ), true );
        if(isset($res['msg']) && $res['msg'] !== 'ok') return array('error'=>$res['msg']);

        $is_weixin = b2_is_weixin();
        $is_mobile = wp_is_mobile();

        return array(
            'type'=>'suibian',
            'is_weixin'=>$is_weixin,
            'is_mobile'=>$is_mobile,
            'order_id'=>$data['order_id'],
            'qrcode'=>$res['qrcode']
        );
    }

    public static function suibian_notify($method,$post){
        ksort($post);
        reset($post); 
        $mch_key = trim(b2_get_option('normal_pay','suibian_key'), " \t\n\r\0\x0B\xC2\xA0"); 
        if(!$mch_key) return array('error'=>__('支付回调错误','b2'));
        $sign = '';
        foreach ($post AS $key => $val) {
            if ($val == '' || $key == 'sign') continue;
            if ($sign) $sign .= '&'; 
            $sign .= "$key=$val"; 
        }
        if (!$post['transaction_id'] || md5($sign . $mch_key) != $post['sign'] || $post['status']!=1) {
            return array('error'=>__('支付失败','b2'));
        } else { 

            //业务处理
            $out_trade_no = $post['out_trade_no']; //需要充值的ID 或订单号 或用户名
            $mepay_total = (float)$post['mepay_total']; //提交金额
            $total_fee = (float)$post['total_fee']; //用户实际付款
            $param = $post['param']; //自定义参数
            $transaction_id = $post['transaction_id']; //流水号
            
            if($method === 'get'){
                return 'success';
            }else{
                $res = Orders::order_confirm($out_trade_no,false);
                if(isset($res['error'])){
                    //file_put_contents('notify.txt', 'mapay error:'.$res['error'], FILE_APPEND);
                    return $res;
                }else{
                    return 'success';
                }
            }

            return 'success';

        }
    }

    /*-----------------------------------码支付----------------------------------------*/

    public static function mapay($data){
        $codepay_id = trim(b2_get_option('normal_pay','mapay_id'), " \t\n\r\0\x0B\xC2\xA0");
        $codepay_key = trim(b2_get_option('normal_pay','mapay_key'), " \t\n\r\0\x0B\xC2\xA0"); 

        $_data = array(
            "id" => $codepay_id,
            "pay_id" => $data['order_id'], 
            "type" => $data['_pay_type'] == 'alipay' ? 1 : 3,
            "price" => $data['order_total'],
            "notify_url"=>b2_get_custom_page_url('notify'),
            "return_url"=>b2_get_custom_page_url('return'),
            'page'=>$data['jump'] == 'jump' ? 1 : 4
        );

        ksort($_data);
        reset($_data);

        $sign = '';
        $urls = '';

        foreach ($_data AS $key => $val) {
            if ($val == ''||$key == 'sign') continue;
            if ($sign != '') {
                $sign .= "&";
                $urls .= "&";
            }
            $sign .= "$key=$val";
            $urls .= "$key=" . urlencode($val);

        }
        $query = $urls . '&sign=' . md5($sign .$codepay_key);
        $url = "";
        //"https://api.xiuxiu888.com/creat_order/?{$query}";

        if($data['jump'] == 'jump'){

            return array(
                'id'=>$data['order_id'],
                'url'=>$url
            );
        }

        $res = wp_remote_get($url);
        $res = json_decode(wp_remote_retrieve_body( $res ), true );
        if(isset($res['msg']) && $res['msg'] !== 'ok') return array('error'=>$res['msg']);

        $is_weixin = b2_is_weixin();
        $is_mobile = wp_is_mobile();

        return array(
            'type'=>'mapay',
            'is_weixin'=>$is_weixin,
            'is_mobile'=>$is_mobile,
            'order_id'=>$data['order_id'],
            'qrcode'=>$res['qrcode']
        );
    }

    public static function mapay_notify($method,$post){
        ksort($post);
        reset($post); 
        $codepay_key = trim(b2_get_option('normal_pay','mapay_key'), " \t\n\r\0\x0B\xC2\xA0");
        if(!$codepay_key) return array('error'=>__('支付回调错误','b2'));
        $sign = '';
        foreach ($post AS $key => $val) {
            if ($val == '' || $key == 'sign') continue;
            if ($sign) $sign .= '&'; 
            $sign .= "$key=$val"; 
        }
        if (!$post['pay_no'] || md5($sign . $codepay_key) != $post['sign']) {
            return array('error'=>__('支付失败','b2'));
        } else { 
           
            $pay_id = $post['pay_id']; 
            $money = (float)$post['money']; 
            $price = (float)$post['price']; 
            $param = $post['param']; 
            $pay_no = $post['pay_no']; 

            if($money >= $price){
                if($method === 'get'){
                    return 'success';
                }else{
                    $res = Orders::order_confirm($pay_id,false);
                    if(isset($res['error'])){
                        //file_put_contents('notify.txt', 'mapay error:'.$res['error'], FILE_APPEND);
                        return $res;
                    }else{
                        return 'success';
                    }
                }
            }
        }
    }
    /*-----------------------------------payjs支付----------------------------------------*/

    //payjs 支付
    public static function payjs($data){
        require B2_THEME_DIR.'/Library/payjs/payjs.php';

        $settings =array(
           'body' =>$data['title'],
           'out_trade_no' =>$data['order_id'],
           'total_fee' => $data['order_total']*100,
           'notify_url'=> b2_get_custom_page_url('notify'),
           'callback_url'=>$data['redirect_url'],
        );

        if($data['_pay_type'] === 'alipay'){
            $settings['type'] = 'alipay';
        }

        // if($is_weixin){
        //     //获取openid
            
        // }

        $is_weixin = b2_is_weixin();
        $is_mobile = wp_is_mobile();

        $payjs = new \Payjs($settings,trim(b2_get_option('normal_pay','payjs_key'), " \t\n\r\0\x0B\xC2\xA0"),trim(b2_get_option('normal_pay','payjs_mchid'), " \t\n\r\0\x0B\xC2\xA0"),$is_weixin,b2_get_custom_page_url('xunhusuccess'));
        
        $rst = $payjs->pay();

        if($is_weixin){
            return array(
                'url'=>$rst,
                'order_id'=>$data['order_id']
            );
        }else{
            
            $resout = json_decode($rst,true);
    
            if(isset($resout['return_code']) && (int)$resout['return_code'] === 1){
    
                return array(
                    'is_weixin'=>$is_weixin,
                    'is_mobile'=>$is_mobile,
                    'order_id'=>$data['order_id'],
                    'qrcode'=>$resout['code_url']
                );
              
            }
        }
        
        return array('error'=>$resout->return_msg);
    }

    //payjs支付回调
    public static function payjs_notify($method,$post){

        $return_code = isset($post['return_code']) ? $post['return_code'] : false;

        if($return_code == 1){

            $post = array_map('stripslashes_deep', $post);
            $sign = isset($post['sign']) ? $post['sign'] : '';
            $total_fee = isset($post['total_fee']) ? $post['total_fee'] : '';
            $out_trade_no = isset($post['out_trade_no']) ? $post['out_trade_no'] : '';

            //验证签名
            unset($post['sign']);
            ksort($post);

            $codepay_key = trim(b2_get_option('normal_pay','payjs_key'), " \t\n\r\0\x0B\xC2\xA0");
            if(!$codepay_key) return array('error'=>__('支付回调错误','b2'));

            $sign_d = strtoupper(md5(urldecode(http_build_query($post)).'&key='.$codepay_key));
 
            if($sign_d === $sign){
                //更新订单数据
                $res = Orders::order_confirm($out_trade_no,false);
                if(isset($res['error'])){
                    //file_put_contents('notify.txt', 'payjs error:'.$res['error'], FILE_APPEND);
                    return $res;
                }else{
                    return 'success';
                }
            }
        }

        return array('error'=>__('回调错误','b2'));
    }

    /*-----------------------------------paypal支付----------------------------------------*/

    public static function paypal_opts(){

        $debug = b2_get_option('normal_pay','paypal_sandbox');

        $opt = [
            'username'=>b2_get_option('normal_pay','paypal_username'),
            'password'=>b2_get_option('normal_pay','paypal_password'),
            'signature'=>b2_get_option('normal_pay','paypal_signature')
        ];
        
        $config = array_merge( array( 
            'username' => 'name',
            'password' => 'pass',
            'signature' => 'sign',
            'return' => '',
            'cancel' => '',
            'debug' => (int)$debug == 1 ? true : false,
        ), $opt);

        return $config;
    }

    public static function paypal($data){

        $total = bcmul($data['order_total'],b2_get_option('normal_pay','paypal_rate'),2);

        require_once B2_THEME_DIR .'/Library/paypal/paypal.php';
        require_once B2_THEME_DIR .'/Library/paypal/httprequest.php';

        $config = self::paypal_opts();

        $config['return'] = b2_get_custom_page_url('return');
        $config['cancel'] =  $data['redirect_url'];

        $PayPal = new \PayPal($config);

        $res = $PayPal->doExpressCheckout($total,$data['title'], $data['order_id'], b2_get_option('normal_pay','paypal_currency_code'));

        // $url = $url.'&'.http_build_query($arg);

        if(!is_string($res)) return ['error'=>isset($res['L_LONGMESSAGE0']) ? $res['L_LONGMESSAGE0'] :  __('paypal配置错误，请检查','b2')];

        unset($config['username'],$config['password'],$config['signature']);

        return array(
            'id'=>$data['order_id'],
            'url'=>array(
                'url'=>$res,
                'data'=>$config
            )
        );

    }

    public static function paypal_notify($post,$data){

        require_once B2_THEME_DIR .'/Library/paypal/paypal.php';
        require_once B2_THEME_DIR .'/Library/paypal/httprequest.php';

        $config = self::paypal_opts();

        $pp = new \PayPal($config);
        $res = $pp->doPayment();

        if ($res['ACK'] == 'Success' && isset($res['TOKEN'])) {
            $order = $pp->getCheckoutDetails($res['TOKEN']);

            $order_id = $order['INVNUM'];

            return Orders::order_confirm($order_id,0);
            if($res){
                return 'success';
            }
        }

        return false;

    }

    /*-----------------------------------迅虎，虎皮椒支付----------------------------------------*/

   //虎皮椒微信
   public static function wecatpay_hupijiao($data){
        $settings = array(
            'appid'     => trim(b2_get_option('normal_pay','wecatpay_hupijiao_appid'), " \t\n\r\0\x0B\xC2\xA0"),
            'appsecret' =>trim(b2_get_option('normal_pay','wecatpay_hupijiao_appsecret'), " \t\n\r\0\x0B\xC2\xA0"),
            'geteway'=>trim(b2_get_option('normal_pay','wecatpay_hupijiao_gateway'), " \t\n\r\0\x0B\xC2\xA0"),
            'payment'   => 'wechat'
        );

        return self::xunhu_action($data,$settings);
    }

    //虎皮椒支付宝
    public static function alipay_hupijiao($data){
        $settings = array(
            'appid'     => trim(b2_get_option('normal_pay','alipay_hupijiao_appid'), " \t\n\r\0\x0B\xC2\xA0"),
            'appsecret' =>trim(b2_get_option('normal_pay','alipay_hupijiao_appsecret'), " \t\n\r\0\x0B\xC2\xA0"),
            'geteway'=>trim(b2_get_option('normal_pay','alipay_hupijiao_gateway'), " \t\n\r\0\x0B\xC2\xA0"),
            'payment'   => 'alipay'
        );

        return self::xunhu_action($data,$settings);
    }

    //迅虎支付
    public static function xunhu($data){
        $is_weixin = b2_is_weixin();
        $is_mobile = wp_is_mobile();
        $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
        $recent_url=dirname($http_type.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"]);
        $settings = array(
            'appid'     => trim(b2_get_option('normal_pay','xunhu_appid'), " \t\n\r\0\x0B\xC2\xA0"),
            'appsecret' =>trim(b2_get_option('normal_pay','xunhu_appsecret'), " \t\n\r\0\x0B\xC2\xA0"),
            'geteway'=>trim(b2_get_option('normal_pay','xunhu_gateway'), " \t\n\r\0\x0B\xC2\xA0"),
            'payment'   => $data['_pay_type'] == 'alipay' ? 'alipay' : 'wechat'
        );
        $param=array(
            'mchid'     	=> $settings['appid'],
            'out_trade_no'	=> $data['order_id'],
            'type'  		=> $settings['payment'],
            'total_fee' 	=> $data['order_total']*100,
            'body'  		=> $data['title'],
            'notify_url'	=> b2_get_custom_page_url('notify'),
            'nonce_str' 	=> str_shuffle(time())
        );
        require B2_THEME_DIR.'/Library/xunhupay/api.php';
        $t=isset($settings['geteway'])?$settings['geteway']:'https://admin.xunhuweb.com';
        $private_key=$settings['appsecret'];
        
        if($is_weixin){
            $param['redirect_url']=$data['redirect_url'];
            $param['sign']     = \XunHu_Payment_Api::generate_xh_hash($param,$private_key);
            $pay_url     = \XunHu_Payment_Api::data_link($t.'/pay/cashier', $param);
            return array(
                    'url'=>htmlspecialchars_decode($pay_url,ENT_NOQUOTES),
                    'order_id'=>$data['order_id'],
                );
        }
        if($is_mobile){
            if($settings['payment']=='alipay'){
                $param['redirect_url']=$data['redirect_url'];
                $param['sign']	  = \XunHu_Payment_Api::generate_xh_hash($param,$private_key);
                $pay_url          = \XunHu_Payment_Api::data_link($t.'/alipaycashier', $param);
                return array(
                    'url'=>htmlspecialchars_decode($pay_url,ENT_NOQUOTES),
                    'order_id'=>$data['order_id'],
                );
            }else{
                $param['redirect_url']= urldecode($data['redirect_url']);
                $param['trade_type'] = 'WAP';
                $param['wap_url']    = $http_type.$_SERVER['SERVER_NAME'];//h5支付域名必须备案，然后找服务商绑定
                $param['wap_name']   = B2_BLOG_NAME;
                $param['sign']       = \XunHu_Payment_Api::generate_xh_hash($param,$private_key);
                $pay_data=json_encode($param);
                $response   		 = \XunHu_Payment_Api::http_post_json($t.'/pay/payment', json_encode($param));
                $result     		 = $response?json_decode($response,true):null;
                if(!$result){
                    return array('error'=>'Internal server error');
                }
                $sign       	  = \XunHu_Payment_Api::generate_xh_hash($result,$private_key);
                if(!isset( $result['sign'])|| $sign!=$result['sign']){
                    return array('error'=>'Invalid sign!');
                }
                if($result['return_code']!='SUCCESS'){
                    return array('error'=>sprintf(__('错误代码：%s。错误信息：%s','b2'),$result['err_code']),$result['err_msg']);
                }
                $pay_url =$result['mweb_url'].'&redirect_url='.$param['redirect_url'];

                return array(
                    'id'=>$data['order_id'],
                    'url'=>array(
                        'url'=>$result['mweb_url'],
                        'data'=>$param
                    )
                );
            }
        }
        $param['sign']     = \XunHu_Payment_Api::generate_xh_hash($param,$private_key);
        try {
            $response   	  = \XunHu_Payment_Api::http_post_json($t.'/pay/payment', json_encode($param));
            $result     	  = $response?json_decode($response,true):null;
            if(!$result){
                return array('error'=>'Internal server error');
            }
            $sign       	  = \XunHu_Payment_Api::generate_xh_hash($result,$private_key);

            if(!isset( $result['sign']) || $sign != $result['sign']){
                return array('error'=>'Invalid sign!');
            }
            if($result['return_code']!='SUCCESS'){
                return array('error'=>sprintf(__('错误代码：%s。错误信息：%s'),$result['err_code']),$result['err_msg']);
            }
            return array(
                'is_weixin'=>$is_weixin,
                'is_mobile'=>$is_mobile,
                'order_id'=>$data['order_id'],
                'qrcode'=>$result['code_url']
            );
        } catch (\Exception $e) {
            return array('error'=>$e->getMessage());
        }
    }

    //迅虎支付
    public static function xunhu_action($data,$settings){
        $settings['trade_order_id'] = $data['order_id'];
        $settings['total_fee'] = $data['order_total'];
        $settings['title']     = $data['title'];
        $settings['version']   = '1.1';
        $settings['lang']       = 'zh-cn'; 
        $settings['time']      = time();
        $settings['notify_url']=  b2_get_custom_page_url('notify');
        $settings['return_url']= b2_get_custom_page_url('xunhusuccess');
        $settings['callback_url']= b2_get_custom_page_url('xunhufail');
        $settings['nonce_str'] = str_shuffle(time());

        $is_weixin = b2_is_weixin();
        $is_mobile = wp_is_mobile();

        if($is_mobile && !$is_weixin && $settings['payment'] == 'wechat'){
            $settings['type'] = 'WAP';
            $settings['wap_url'] = B2_HOME_URI;
            $settings['wap_name'] = B2_BLOG_NAME;
        }

        require B2_THEME_DIR.'/Library/xunhu/api.php';

        $settings['hash']     = \XH_Payment_Api::generate_xh_hash($settings,$settings['appsecret']);
        try {
            $response     = \XH_Payment_Api::http_post($settings['geteway'], json_encode($settings));

            $result       = $response ? json_decode($response,true) : null;
            if(!$result){
                return array('error'=>'Internal server error');
            }

            $hash         = \XH_Payment_Api::generate_xh_hash($result,$settings['appsecret']);
            if(!isset( $result['hash'])|| $hash!=$result['hash']){
                return array('error'=>__('Invalid sign!','b2'));
            }

            if($result['errcode']!=0){
                return array('error'=>$result['errmsg']);
            }

            $pay_url =$result['url'];
            return array(
                'id'=>$data['order_id'],
                'url'=>$pay_url
            );

        } catch (\Exception $e) {
            return array('error'=>$e->getMessage());
        }
    }

    //迅虎回调
    public static function xunhu_notify($post,$order){
        require B2_THEME_DIR.'/Library/xunhu/api.php';
        require B2_THEME_DIR.'/Library/xunhupay/api.php';
        $type = $order['pay_type'];
        if($type === 'xunhu'){
            $private_key = trim(b2_get_option('normal_pay','xunhu_appsecret'), " \t\n\r\0\x0B\xC2\xA0");

            if(!$private_key) return array('error'=>__('支付回调错误','b2'));

            $hash =\XunHu_Payment_Api::generate_xh_hash($post,$private_key);

            if($post['sign'] != $hash){
                //签名验证失败
                return array('error'=>__('签名错误','b2'));
            }

            if($post['status']=='complete'){
                $res = Orders::order_confirm($post['out_trade_no'],$post['total_fee']/100);
                return 'success';
            }

        }else{
            $appsecret = trim(b2_get_option('normal_pay',$type.'_appsecret'), " \t\n\r\0\x0B\xC2\xA0");

            if(!$appsecret) return array('error'=>__('支付回调错误','b2'));

            $hash = \XH_Payment_Api::generate_xh_hash($post,$appsecret);

            if($post['hash'] !== $hash){
                return array('error'=>__('签名验证失败','b2'));
            }

            if($post['status'] == 'OD'){
                $res = Orders::order_confirm($post['trade_order_id'],$post['total_fee']);
                return 'success';
            }

        }
        
        return array('error'=>__('回调失败','b2'));
    }

    /*-----------------------------------微信，支付宝官方支付设置----------------------------------------*/

    //支付宝官方设置项
    public static function alipay_normal_settings(){
        return [
            // 沙箱模式
            'debug'       => false,
            'sign_type'   => "RSA2",
            'appid'       => trim(b2_get_option('normal_pay','alipay_appid'), " \t\n\r\0\x0B\xC2\xA0"),
            'public_key'  => trim(b2_get_option('normal_pay','alipay_public_key'), " \t\n\r\0\x0B\xC2\xA0"),
            'private_key' => trim(b2_get_option('normal_pay','alipay_private_key'), " \t\n\r\0\x0B\xC2\xA0"),
            'notify_url'  => b2_get_custom_page_url('notify'),
            'return_url'  =>b2_get_custom_page_url('return')
        ];
    }

    //微信官方设置项
    public static function wecatpay_normal_settings($data){

        $settings = [
            'appid'          => trim(b2_get_option('normal_pay','wecatpay_appid'), " \t\n\r\0\x0B\xC2\xA0"),
            'mch_id'         => trim(b2_get_option('normal_pay','wecatpay_mch_id'), " \t\n\r\0\x0B\xC2\xA0"),
            'mch_key'        => trim(b2_get_option('normal_pay','wecatpay_secret'), " \t\n\r\0\x0B\xC2\xA0")
        ];

        if(isset($data['order_mobile']) && $data['order_mobile'] == 'mp'){
            $mpappid = get_option('b2_app_login_normal');

            if(isset($mpappid['weixin_appid']) && $mpappid['weixin_appid']){
                $settings['appid'] = $mpappid['weixin_appid'];
            }else{
                return array('error'=>__('请在小程序插件中设置小程序登陆相关参数','b2'));
            }
            
        }elseif(isset($data['order_mobile']) && $data['order_mobile'] == 'app'){
            $mpappid = get_option('b2_app_pay_normal');
            $settings['appid'] = $mpappid['pay_appid_mpopen'];
        }

        return $settings;
    }

    /*-------------------------------------微信官方支付--------------------------------------*/

    // public static function wecat_jsapi(){
    //     $key = zrz_get_social_settings('open_weixin_gz_key');
    //     $secret = zrz_get_social_settings('open_weixin_gz_secret');
    //     if(!$key || !$secret) return;

    //     require_once B2_THEME_DIR .'/Library/jssdk.php';
    //     $jssdk = new JSSDK($key, $secret);
    //     $signPackage = $jssdk->GetSignPackage();

    //     if($signPackage){
    //         global $post;
    //         if(isset($post->ID)){
    //             $thumb = get_the_post_thumbnail_url($post->ID);
    //             $img = $thumb ? $thumb : zrz_get_first_img(get_post_field('post_content', $post->ID));
    //         }else{
    //             $img = zrz_get_theme_settings('logo');
    //         }

    //         $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    //         $url = $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

    //         return array('msg'=>$signPackage,'post_data'=>array(
    //             'imgUrl'=>$img,
    //             'link'=>$url,
    //             'desc'=>zrz_seo_head_meta_description(true),
    //             'title'=>wp_get_document_title()
    //         ));

    //     }
    // }

    //微信支付官方（扫码支付）
    public static function wecatpay_normal($data){

        try {
            $is_weixin = b2_is_weixin();
            $is_mobile = wp_is_mobile();

            $is_pcweixin = isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false && strpos($_SERVER['HTTP_USER_AGENT'], 'WindowsWechat') !== false;

            $type = 'NATIVE';
            if(isset($data['order_mobile']) && $data['order_mobile'] == 'app'){
                $type = 'APP';
            }elseif($is_weixin && !$is_pcweixin){
                $type = 'JSAPI';
            }elseif($is_mobile && !$is_weixin){
                $type = 'MWEB';
            }

            $config = self::wecatpay_normal_settings($data);

            $wechat = new \WeChat\Pay($config);
           
            $options = [
                'body'             => $data['title'],
                'out_trade_no'     => $data['order_id'],
                'total_fee'        => $data['order_total']*100,
                'trade_type'       => $type,
                'notify_url'       => b2_get_custom_page_url('notify'),
                'spbill_create_ip' =>  b2_get_user_ip(),
            ];

            if($type === 'JSAPI'){
                $open_id = get_user_meta($data['user_id'],'zrz_weixin_uid',true);

                if(isset($data['order_mobile']) && $data['order_mobile'] == 'mp'){
                    $open_id = get_user_meta($data['user_id'],'zrz_mpweixin_pay_uid',true);
                }else{
                    $oauth = b2_oauth_types();
                    if(isset($oauth['weixin']['url']) && $oauth['weixin']['url']){
                        if(!$open_id) return array('error'=>array(
                            'msg'=>'bind_weixin',
                            'oauth'=>$oauth
                        ));
                    }else{
                        return array('error'=>array(
                            'msg'=>'bind_weixin',
                            'oauth'=>__('网站未开通服务号，无法完成支付，请在浏览器中打开当前网页完成支付。','b2')
                        ));
                    }
                }

                $options['openid'] = $open_id;
            }

            $result = $wechat->createOrder($options);

            if($type === 'MWEB'){
                $data['redirect_url'] = isset($data['redirect_url']) && $data['redirect_url'] ? urlencode($data['redirect_url']) : '';
                return array(
                    'url'=>$data['redirect_url'] ? add_query_arg( array('redirect_url' => $data['redirect_url']), $result['mweb_url']) : $result['mweb_url'],
                    'id'=>$data['order_id'],
                );
            }elseif($type === 'NATIVE'){
                return array(
                    'is_weixin'=>$is_weixin ? $result['code_url'] : false,
                    'is_mobile'=>wp_is_mobile(),
                    'order_id'=>$data['order_id'],
                    'qrcode'=>$result['code_url']
                );
            }else if($type === 'JSAPI'){
                if(isset($result['err_code_des'])){
                    return array('error'=>$result['err_code_des']);
                }
                return array(
                    'url'=>$wechat->createParamsForJsApi($result['prepay_id']),
                    'id'=>$data['order_id']
                );
            }else if($type === 'APP'){
                return array(
                    'url'=>$wechat->createParamsForApp($result['prepay_id']),
                    'id'=>$data['order_id']
                );
            }

        } catch (\Exception $e) {
            return array('error'=>$e->getMessage());
        }
    }

    //微信官方回调
    public static function wecatpay_normal_notify($method,$post){

        //获取订单信息
        global $wpdb;
        $table_name = $wpdb->prefix . 'zrz_order';

        //获取订单数据
        $order = $wpdb->get_row(
            $wpdb->prepare("
                SELECT * FROM $table_name
                WHERE order_id = %s
                ",
                $post['out_trade_no']
            )
        ,ARRAY_A);
        
        $config = self::wecatpay_normal_settings($order);
        try{
            $wechat = new \WeChat\Pay($config);
        
            $data = $wechat->getNotify();
            //update_user_meta(1,'wepay',$data);
            if ($data['return_code'] === 'SUCCESS' && $data['result_code'] === 'SUCCESS') {
                
                if($method == 'get'){
                        return true;
                }else{
                    $res = Orders::order_confirm($data['out_trade_no'],false);
                    if(isset($res['error'])){
                        //file_put_contents('notify.txt', 'wecatpay_normal error:'.$res['error'], FILE_APPEND);
                        return $res;
                    }else{
                        return 'success';
                    }
                }
            }
        } catch (\Exception $e) {
            //file_put_contents('notify.txt', 'wecatpay_normal:'.$e->getMessage(), FILE_APPEND);
            return array('error'=>$e->getMessage());
        }
    }

    /*-------------------------------------支付宝官方支付--------------------------------------*/

    //支付宝常规（跳转支付）
    public static function alipay_normal($data){

        $config = self::alipay_normal_settings($data);

        try {
            $is_weixin = b2_is_weixin();
            $is_mobile = wp_is_mobile();

            $alipay_type = b2_get_option('normal_pay','alipay_type');

            if(isset($data['order_mobile']) && $data['order_mobile'] == 'app'){
                $pay = \We::AliPayApp($config);
            }elseif($data['jump'] === 'jump' && $alipay_type =='normal'){
                if($is_mobile){
                    if(isset($data['order_mobile']) && $data['order_mobile'] == 'alipayH5'){
                        $config['return_url'] = $data['redirect_url'];
                        $config['passback_params'] = urlencode($data['redirect_url']);
                    }
                    $pay = \We::AliPayWap($config);
                }else{
                    $pay = \We::AliPayWeb($config);
                }
            }else{
                $pay = \We::AliPayScan($config);
            }
            
            $result = $pay->apply([
                'out_trade_no' => $data['order_id'], // 商户订单号
                'total_amount' => $data['order_total'], // 支付金额
                'subject'      => $data['title']
            ]);

            if(isset($data['order_mobile']) && $data['order_mobile'] == 'app'){
                return array(
                    'id'=>$data['order_id'],
                    'url'=>$result
                );
            }elseif($data['jump'] === 'jump' && $alipay_type =='normal'){
                return array(
                    'id'=>$data['order_id'],
                    'url'=>$result
                );

            }elseif($data['jump'] === 'jump' && $alipay_type =='scan'){
                $pay_url =$result['qr_code'];
                return array(
                    'id'=>$data['order_id'],
                    'url'=>$result['qr_code']
                );
  
            }

            return array(
                'is_weixin'=>$is_weixin,
                'is_mobile'=>$is_mobile,
                'order_id'=>$data['order_id'],
                'qrcode'=>$result['qr_code']
            );
           
        } catch (\Exception $e) {
            return array('error'=>$e->getMessage());
        }
    }

    //支付宝官方回调
    public static function alipay_normal_notify($method,$post){

        $config = self::alipay_normal_settings();
        if(isset($post['passback_params'])){
            $config['return_url'] =  $post['passback_params'];
        }
        
        try {
            $pay = \AliPay\App::instance($config);
            $data = $pay->notify();
            
            if($method == 'get'){
                if($post['sign'] === $data['sign']){
                    return true;
                }else{
                    return false;
                }
            }else{
                if($post['sign'] !== $data['sign']) return array('error'=>__('嗯？','b2'));
                //update_user_meta(1,'test',$data);
                if (in_array($data['trade_status'], ['TRADE_SUCCESS', 'TRADE_FINISHED'])) {
                    $res = Orders::order_confirm($data['out_trade_no'],$data['total_amount']);
                    if(isset($res['error'])){
                        //file_put_contents('notify.txt', 'alipay_normal error:'.$res['error'], FILE_APPEND);
                        return $res;
                    }else{
                        return 'success';
                    }
                } else {
                    //file_put_contents('notify.txt', __('回调成功，支付失败','b2'), FILE_APPEND);
                }
            }
        } catch (\Exception $e) {
            //file_put_contents('notify.txt', 'alipay_normal::'.$e->getMessage(), FILE_APPEND);
            return array('error'=>$e->getMessage());
        }
    }

    public static function toutiao($data){

        $opt = get_option('b2_app_pay_normal');

        $url = 'https://developer.toutiao.com/api/apps/ecpay/v1/create_order';
        $params['app_id'] = $opt['pay_toutiao_appid'];
        $params['out_order_no'] = $data['order_id'];
        $params['total_amount'] = (int)$data['order_total'] * 100;
        $params['subject'] = $data['title'];
        $params['body'] = $data['title'];
        $params['valid_time'] = 12800;
        $secret = $opt['pay_toutiao_salt'];
        $params["notify_url"] = b2_get_custom_page_url('notify');
        $params['sign'] = self::ttsign($params,$secret);

        $http = self::httpPost($url, $params);
        $res = json_decode($http,true);

        if($res['err_no'] !== 0){
            return array('error'=>$res['err_tips']);
        }

        set_transient($params['out_order_no'],$params,36000);
        $res['out_order_no'] =  $data['order_id'];
        return $res;
    }

    public static function toutiao_notify($method,$post){

        $opt = get_option('b2_app_pay_normal');

        if ($post['msg_signature'] !== self::getNotifySign($post, $opt['pay_toutiao_token'])) {
            return array('error'=>'回调验证错误');
        } else {
            $tt_notify_data = json_decode($post['msg'], true);

            $res = Orders::order_confirm($tt_notify_data['cp_orderno'],null);
      
            if(isset($res['error'])){
                //file_put_contents('notify.txt', 'alipay_normal error:'.$res['error'], FILE_APPEND);
                return $res;
            }else{
                return 'success';
            }
        }
        
        return 'success';

    }

    public static function getNotifySign(array $body, string $secret)
    {
 
        $filtered = [];
        foreach ($body as $key => $value) {
            if (in_array($key, ['msg_signature', 'type'])) {
                continue;
            }
            $filtered[] =
                is_string($value)
                    ? trim($value)
                    : $value;
        }
        $filtered[] = trim($secret);
        sort($filtered, SORT_STRING);
        $filtered = trim(implode('', $filtered));
 
        return sha1($filtered);
    }

    public static function httpPost($url, $data = null)
    {
    
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        if (!$data) return false;
        if (is_array($data)) {
    
            $data = json_encode($data);
        }
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length:' . strlen($data),
            'Cache-Control: no-cache',
            'Pragma: no-cache'
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
    }

    public static function ttsign($params,$secret)
    {

        $rList = array();
        foreach ($params as $k => $v) {
    
            if ($k == "other_settle_params" || $k == "app_id" || $k == "sign" || $k == "thirdparty_id")
                continue;
            $value = trim(strval($v));
            $len = strlen($value);
            if ($len > 1 && substr($value, 0, 1) == "\"" && substr($value, $len, $len - 1) == "\"")
                $value = substr($value, 1, $len - 1);
            $value = trim($value);
            if ($value == "" || $value == "null")
                continue;
            array_push($rList, $value);
        }
        array_push($rList, $secret);
        sort($rList, 2);
        return md5(implode('&', $rList));
    }


    public static function baidu($data){
        require B2_THEME_DIR.'/Library/baidupay.php';

        $opt = get_option('b2_app_pay_normal');

        if(!isset($opt['pay_baidu_dealId'])) return array('error'=>__('请先前往移动端插件中配置百度支付','b2'));

        $config = array(
            'deal_id'       => $opt['pay_baidu_dealId'], // 百度收银台的财务结算凭证
            'app_key'       => $opt['pay_baidu_appkey'], // 表示应用身份的唯一ID
            'private_key'   => $opt['pay_baidu_priKey'], // 私钥原始字符串
            'public_key'    => $opt['pay_baidu_publicKey'], // 平台公钥
            'notify_url'    => '', // 支付回调地址
        );

        $order = array(
            'body'          => $data['title'], // 产品描述
            'total_amount'  => $data['order_total']*100, // 订单金额（分）
            'order_sn'      => $data['order_id'], // 订单编号
        );

        try {
            $baidupay = new \feng\BaiduPay($config);
            return array(
                'payData'=>$baidupay->xcxPay($order),
                'id'=>$data['order_id']
            );
        } catch (\Exception $e) {
            return array('error'=>$e->getMessage());
        }

    }

    public static function baidu_notify($method,$post){

        require B2_THEME_DIR.'/Library/baidupay.php';

        $opt = get_option('b2_app_pay_normal');

        $config = array(
            'deal_id'       => $opt['pay_baidu_dealId'], // 百度收银台的财务结算凭证
            'app_key'       => $opt['pay_baidu_appkey'], // 表示应用身份的唯一ID
            'private_key'   => $opt['pay_baidu_priKey'], // 私钥原始字符串
            'public_key'    => $opt['pay_baidu_publicKey'], // 平台公钥
            'notify_url'    => '', // 支付回调地址
        );

        $baidupay = new \feng\BaiduPay($config);

        $re = $baidupay->notify();
        if ($re) {
            // 这里回调处理订单操作
            // 以验证返回支付成功后的信息，可直接对订单进行操作，已通知微信支付成功
            $res = Orders::order_confirm($post['tpOrderId'],false);
            return $baidupay->success(); // 支付返还成功，通知结果
        } else {
            // 支付失败
            return $baidupay->error(); // 支付失败，返回状态（无论支付成功与否都需要通知百度）
        }

    }

    //ajax检查支付结果
    public static function pay_check($order_id){
        $res = apply_filters('b2_pay_check',$order_id);
        return $res;
    }

    public static function xml2arr($xml){
        $entity = libxml_disable_entity_loader(true);
        $data = (array)simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        libxml_disable_entity_loader($entity);
        return json_decode(json_encode($data), true);
    }
}