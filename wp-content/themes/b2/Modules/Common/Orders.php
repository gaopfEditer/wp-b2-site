<?php namespace B2\Modules\Common;

use B2\Modules\Common\Pay;
use B2\Modules\Common\Cpay;
use B2\Modules\Common\Post;
use B2\Modules\Common\Shortcode;
use B2\Modules\Common\Shop;
use B2\Modules\Common\Message;
use B2\Modules\Common\User;
use B2\Modules\Common\Circle;
use B2\Modules\Common\PostRelationships;
use B2\Modules\Common\CircleRelate;

/*
* 商城订单项
* $order_type //订单类型
* c : 抽奖 ，d : 兑换 ，g : 购买 ，w : 文章内购 ，ds : 打赏 ，x : 资源下载 ，cz : 充值 ，vip : VIP购买 ,cg : 积分购买,
* v : 视频购买,verify : 认证付费,mission : 签到填坑 , coupon : 优惠劵订单,circle_join : 支付入圈 , circle_read_answer_pay : 付费查看提问答案,
* circle_hidden_content_pay : 付费查看隐藏内容，custom ：自定义支付，infomation_sticky ：信息置顶
*
* $order_commodity //商品类型
* 0 : 虚拟物品 ，1 : 实物
*
* $order_state //订单状态
* w : 等待付款 ，f : 已付款未发货 ，c : 已发货 ，s : 已删除 ，q : 已签收 ，t : 已退款
*/
class Orders{

    public function init(){
        add_filter( 'b2_order_notify_return', array(__CLASS__,'order_notify_return'),5,1);
    }

    //生成订单号
    public static function build_order_no() {
        $year_code = array('A','B','C','D','E','F','G','H','I','J');
        $order_sn = $year_code[intval(wp_date('Y'))-2020].
        strtoupper(dechex(wp_date('m'))).wp_date('d').
        substr(time(),-5).substr(microtime(),2,5).sprintf('%02d',rand(0,99));
        return $order_sn;
    }

    //创建临时订单
    public static function build_order($data){

        $user_id = b2_get_current_user_id();

        $can_guset_pay = apply_filters('b2_can_guest_buy', $data);

        if(!$user_id && !(int)$can_guset_pay) return array('error'=>__('请先登录','b2'));

        do_action('b2_before_build_order',$data, $user_id);

        if(!isset($data['order_type']) || $data['order_type'] === 'coupon' || $data['order_type'] === 'gx') return array('error'=>__('订单类型错误','b2'));

        $data['order_type'] = trim($data['order_type'], " \t\n\r\0\x0B\xC2\xA0");
        $data['pay_type'] = trim($data['pay_type'], " \t\n\r\0\x0B\xC2\xA0");
        // edited by fuzqing
        if ($data['order_type'] === 'custom') {
            $active_time = b2_get_cpay_active_time($data['post_id']);
            if (!$active_time['active']) {
                return array('error'=>$active_time['tips']);
            }
            $info = b2_is_enable_related_pay_money($data['post_id']);
            if ($info['related']) {
                $order_value = json_decode($data['order_value'],true);
                if ((float)$data['order_price'] !== (float)$order_value['price']
                    || (float)$data['order_price'] !== (float)$info['related_prices'][$order_value[$info['related_field']]]) {
                    return array('error'=>__('订单金额错误','b2'));
                }
            }
        }

        if($data['pay_type'] === 'coupon') return array('error'=>__('嗯？','b2'));
        if(isset($data['_pay_type'])) return array('error'=>__('嗯？','b2'));

        $data['order_count'] = isset($data['order_count']) ? (int)$data['order_count'] : 1;

        if($data['order_count'] < 1) return array('error'=>__('嗯？','b2'));

        if(isset($data['order_price']) && $data['order_price'] < 0) return array('error'=>__('嗯？','b2'));

        $data = self::build_order_action($data);

        if(is_array($data)){
            return Pay::pay($data);
        }else{
            return $data;
        }
    }

    public static function build_order_action($data,$_order_id = ''){
        $user_id = b2_get_current_user_id();

        $allow_guest_buy = b2_get_option('shop_main','shop_xu_open');
        if((int)$allow_guest_buy == 1 && $data['order_type'] === 'gx' && !$user_id){
            if($data['order_commodity'] == 1){
                return ['error'=>__('实物商品不支持游客购买','b2')];
            }
        }

        $data['order_count'] = isset($data['order_count']) ? (int)$data['order_count'] : 1;

        if($data['order_count'] < 1) return array('error'=>__('嗯？','b2'));

        $data['post_id'] = isset($data['post_id']) ? (int)$data['post_id'] : 0;

        if(isset($data['_pay_type'])) return array('error'=>__('嗯？','b2'));

        if(isset($data['order_price']) && $data['order_price'] < 0 && $data['order_type'] !== 'coupon') return array('error'=>__('嗯？','b2'));

        $data['user_id'] = $user_id;

        $order_type = b2_order_type();

        if(!isset($data['order_type']) || !isset($order_type[$data['order_type']])) return array('error'=>__('订单类型错误','b2'));

        $data['_pay_type'] = $data['pay_type'];

        //判断支付类型
        $pay_type = Pay::pay_type($data['pay_type']);
        if(isset($pay_type['error'])) return $pay_type;
        $data['pay_type'] = $pay_type['type'];

        //if($data['order_type'] === 'cz' && ($data['pay_type'] === 'balance' || $data['pay_type'] === 'credit' || $data['pay_type'] === 'card' || $data['pay_type'] === 'coupon')) return array('error'=>__('订单类型错误'));

        //扫码支付还是跳转支付
        $jump = Pay::check_pay_type($data['_pay_type']);
        $data['jump'] = $jump['pay_type'];

        //订单号
        if(($data['order_type'] === 'gx' || $data['order_type'] === 'coupon') && $_order_id){
            $data['order_id'] = $_order_id;
        }else{
            $order_id = self::build_order_no();
            $data['order_id'] = $order_id;
        }

        //检查支付金额
        $order_price = apply_filters('b2_order_price', $data);
        if(isset($order_price['error'])) return $order_price;

        $data['order_price'] = $order_price;

        if($data['order_price'] < 0 && $data['order_type'] !== 'coupon') return array('error'=>__('订单总金额错误','b2'));

        if($data['order_count'] < 1) return array('error'=>__('嗯？','b2'));

        //如果是合并支付
        if($data['order_type'] === 'g' || $data['order_type'] === 'coupon'){
            $total = $order_price;
        }else{
            $total = bcmul($data['order_price'],$data['order_count'],2);
        }

        //检查金额
        if(isset($data['order_total']) && (float)$data['order_total'] !== (float)$total){
            return array('error'=>__('订单总金额错误','b2'));
        }

        $data['order_total'] = $total;

        if($data['order_total'] < 0 && $data['order_type'] !== 'coupon') return array('error'=>__('订单总金额错误','b2'));

        //标题
        if(isset($data['title'])){
            $data['title'] = b2_get_des(0,30,urldecode($data['title']));
        }

        //金额类型
        $money_type = self::money_type($data);
        if(isset($money_type['error'])) return $money_type;
        $data['money_type'] = $money_type;

        //文章ID
        $data['post_id'] = isset($data['post_id']) ? (int)$data['post_id'] : 0;
        if($data['order_type'] === 'g'){
            $data['post_id'] = -1;
        }

        //检查是虚拟物品还是实物
        $commodity = self::check_commodity_type($data);
        $data['order_commodity'] = $commodity;

        //order_key
        $data['order_key'] = isset($data['order_key']) ? esc_sql(str_replace(array('{{','}}'),'',sanitize_text_field($data['order_key']))) : '';

        $data['order_value'] = isset($data['order_value']) && $data['order_value'] ? urldecode($data['order_value']) : '';

        $data['order_value'] = esc_sql(str_replace(array('{{','}}'),'',sanitize_text_field($data['order_value'])));

        if(isset($data['order_mobile']) && $data['order_mobile']){
            if(!in_array($data['order_mobile'],array('mp','app','alipayH5'))){
                return array('error'=>__('订单信息错误','b2'));
            }
        }else{
            $data['order_mobile'] = '';
        }

        //order_content
        $data['order_content'] = isset($data['order_content']) ? urldecode($data['order_content']) : '';
        $data['order_content'] = isset($data['order_content']) && $data['order_content'] != '' ? esc_sql(str_replace(array('{{','}}'),'',sanitize_text_field($data['order_content']))) : '';

        $data['order_address'] = isset($data['order_address']) ? esc_sql(str_replace(array('{{','}}'),'',sanitize_text_field($data['order_address']))) : '';

        // $value = $data['order_content'].$data['order_address'].$data['order_value'];

        // if($value){
        //     $censor = apply_filters('b2_text_censor', $data['order_content'].$data['order_address'].$data['order_value']);
        //     if(isset($censor['error'])) return $censor;
        // }


        $data = apply_filters('b2_order_build_before', $data);

        $check_data = serialize($data);

        if(strlen($check_data) > 50000) return array('error'=>__('非法操作','b2'));

        global $wpdb;
        $table_name = $wpdb->prefix . 'zrz_order';

        $wpdb->insert(
            $table_name,
            array(
                'order_id' => $data['order_id'],
                'user_id' => $data['user_id'],
                'post_id'=>$data['post_id'],
                'order_type'=>$data['order_type'],
                'order_commodity'=>$data['order_commodity'],
                'order_state'=>'w',
                'order_date'=>current_time('mysql'),
                'order_count'=>$data['order_count'],
                'order_price'=>$data['order_price'],
                'order_total'=>$data['order_total'],
                'money_type'=>$data['money_type'],
                'order_key'=>$data['order_key'],
                'order_value'=>$data['order_value'],
                'order_content'=>$data['order_content'],
                'pay_type'=>$data['pay_type'],
                'tracking_number'=>'',
                'order_address'=>self::get_address($data),
                'order_mark'=>b2_get_user_ip(),
                'order_mobile'=>isset($data['order_mobile']) ? $data['order_mobile'] : '',
            ),
            array(
                '%s',
                '%d',
                '%d',
                '%s',
                '%d',
                '%s',
                '%s',
                '%d',
                '%f',
                '%f',
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            )
        );

        return apply_filters('b2_order_build_after',$data,$wpdb->insert_id);
    }

    //判断货币类型
    public static function money_type($data){
        //c : 抽奖 ，d : 兑换 ，g : 合并购买， gx : 合并购买后单品，w : 文章内购 ，ds : 打赏 ，x : 资源下载 ，cz : 充值 ，vip : VIP购买 ,cg : 积分购买,v视频查看
        //0 是货币，1是积分
        switch ($data['order_type']) {
            case 'g':
            case 'ds':
            case 'cz':
            case 'vip':
            case 'verify':
            case 'circle_join':
            case 'custom':
            case 'infomation_sticky':
            case 'coupon':
            case 'gx':
                return 0;
                break;
            case 'd':
            case 'c':
                return 1;
                break;
            case 'x':
                if(!isset($data['order_key'])) return array('error'=>__('金额错误','b2'));
                if(!isset($data['post_id'])) return array('error'=>__('没有相关资源','b2'));

                $download = Post::get_download_page_data($data['post_id'],$data['order_key'],0);
                if(isset($download['error'])) return $download;

                $download = $download['current_user'];
                $download = $download['can'];
                if($download['type'] == 'money' && $download['value']){
                    return 0;
                }elseif($download['type'] == 'credit' && $download['value']){
                    return 1;
                }
                break;
            case 'v':
                $type = get_post_meta($data['post_id'],'b2_single_post_video_role',true);
                if($type === 'money'){
                    return 0;
                }
                if($type === 'credit'){
                    return 1;
                }
                break;
            case 'w':
                $cap = Shortcode::check_reading_cap($data['post_id'],$data['user_id']);
                if(isset($cap['cap']) && $cap['cap'] === 'money'){
                    return 0;
                }else{
                    return 1;
                }
                break;
            case 'circle_read_answer_pay':
                $type = get_post_meta($data['post_id'],'b2_circle_ask_reward',true);
                if($type === 'money') return 0;
                return 1;
                break;
            case 'circle_hidden_content_pay':
                $type = get_post_meta($data['post_id'],'b2_topic_read_role',true);
                if($type === 'money') return 0;
                return 1;
                break;
            case 'mission':
                return 1;
            break;
        }

        return 0;
    }

    //订单异步回调
    public static function order_confirm($order_id,$money){

        if(!b2_check_repo(md5($order_id))) return array('error'=>__('回调错误','b2'));

        if(!$order_id) return array('error'=>__('数据不完整','b2'));

        global $wpdb;
        $table_name = $wpdb->prefix . 'zrz_order';

        //获取订单数据
        $order = $wpdb->get_row(
            $wpdb->prepare("
                SELECT * FROM $table_name
                WHERE order_id = %s
                ",
                $order_id
            )
        ,ARRAY_A);

        //如果已经支付成功，直接返回
        if($order['order_state'] !== 'w') return 'success';

        if(empty($order)){
            return array('error'=>__('没有找到这个订单','b2'));
        }

        if($money && (float)$money != $order['order_total']){
            return array('error'=>__('金额错误','b2'));
        }

        //虚拟物品还是实物
        if((int)$order['order_commodity'] === 0 || $order['order_type'] == 'g'){
            $state = 'q';
        }else{
            $state = 'f';
        }

        //更新订单
        if(apply_filters('b2_update_orders', array('order_state'=>$state,'order'=>$order))){

            $user_id = apply_filters('b2_order_user_id',$order);
            if(isset($user_id['error'])) return $user_id;

            //do_action('b2_order_notify_action',$order);
            return apply_filters('b2_order_notify_return', $order);
        }

        //do_action('b2_order_notify_action',array());
        return array('error'=>__('回调错误','b2'));
    }

    //判断商品是虚拟物品还是实物
    public static function check_commodity_type($data){

        if($data['order_type'] == 'g'){
            return -1;
        }

        //c : 抽奖 ，d : 兑换 ，g : 合并支付 ，gx : 合并支付的单个商品,w : 文章内购 ，ds : 打赏 ，x : 资源下载 ，cz : 充值 ，vip : VIP购买 ,cg : 积分购买
        $type = array('cg','vip','cz','x','verify','circle_join','custom','infomation_sticky','circle_read_answer_pay','circle_hidden_content_pay','mission','coupon');
        if(in_array($data['order_type'],$type)) return 0;

        $post_type = get_post_type($data['post_id']);
        if($post_type !== 'shop'){
            return 0;
        }

        if($post_type === 'shop'){
            return (int)get_post_meta($data['post_id'],'zrz_shop_commodity',true);
        }

        return 1;
    }

    //获取用户的地址数据
    public static function get_address($data){

        if((int)$data['order_commodity'] === 0){
            return $data['order_address'];
        }else{
            $key = isset($data['order_address']) && $data['order_address'] ? $data['order_address'] : '';
            return User::get_default_address($data['user_id'],$key);
        }

        return '';
    }

    //支付成功回调
    public static function order_notify_return($data){

        if(empty($data)) return array('error'=>__('更新订单失败','b2'));

        $order_type = 'callback_'.$data['order_type'];
        return self::$order_type($data);
    }

    //合并支付回调
    public static function callback_g($data){

        $post_data = json_decode(stripslashes($data['order_value']),true);

        if($post_data){
            $ids = $post_data['products'];

            foreach ($ids as $k => $v) {
                self::order_confirm($data['order_id'].'-'.$k,0);
            }

            //优惠劵回调
            if(!empty($post_data['coupons'])){
                self::order_confirm($data['order_id'].'-coupon',0);
            }

            //删除临时合并订单
            global $wpdb;
            $table_name = $wpdb->prefix.'zrz_order';

            $wpdb->delete( $table_name, array( 'order_id' => $data['order_id']) );
        }

        return apply_filters('b2_order_callback_g', $post_data,$data);
    }

    public static function callback_coupon($data){
        // if($data['pay_type'] === 'balance'){

        //     Message::update_data([
        //         'date'=>current_time('mysql'),
        //         'from'=>$user_id,
        //         'to'=>$post_author,
        //         'post_id'=>$post_id,
        //         'msg'=>sprintf(__('您使用了优惠券：%s','b2'),$post_type_text),
        //         'type'=>'post_distribution',
        //         'type_text'=>__('推广成功','b2')
        //     ]);

        //     Gold::update_data([
        //         'date'=>current_time('mysql'),
        //         'to'=>$data['user_id'],
        //         'gold_type'=>1,
        //         'post_id'=>$data['post_id'],
        //         'no'=>-$data['order_total'],
        //         'msg'=>sprintf(__('有${count}人购买了您推广的%s：${post_id}','b2'),$post_type_text),
        //         'type'=>'post_distribution',
        //         'type_text'=>__('使用了优惠券','b2')
        //     ]);

        //     //$money = User::money_change($data['user_id'],-$data['order_total']);
        //     Message::add_message(array(
        //         'user_id'=>$data['user_id'],
        //         'msg_type'=>64,
        //         'msg_read'=>1,
        //         'msg_date'=>current_time('mysql'),
        //         'msg_users'=>'',
        //         'msg_credit'=>-$data['order_total'],
        //         'msg_credit_total'=>get_user_meta($data['user_id'],'zrz_rmb',true),
        //         'msg_key'=>-1,
        //         'msg_value'=>$data['order_value']
        //     ));
        // }

        //删除已领的优惠劵
        $coupons = explode(',',$data['order_value']);
        $my_coupons = get_user_meta($data['user_id'],'b2_coupons',true);
        foreach ($coupons as $k => $v) {
            if(isset($my_coupons[$v])){
                unset($my_coupons[$v]);
            }
        }

        update_user_meta($data['user_id'],'b2_coupons',$my_coupons);

        return apply_filters('b2_order_callback_coupon',$data);
    }

    //合并支付单个商品回调
    public static function callback_gx($data){

        //检查商品是否有赠送积分
        $post_id = $data['post_id'];
        $type = get_post_meta($post_id,'zrz_shop_type',true);

        $money = Shop::get_shop_price($post_id,$data['user_id'],$type);

        if($money['credit'] && $money['credit'] > 0 && $type === 'normal'){
            $credit = bcmul($money['credit'],$data['order_count']);
            // $total = Credit::credit_change($data['user_id'],$credit);

            Gold::update_data([
                'date'=>current_time('mysql'),
                'to'=>$data['user_id'],
                'gold_type'=>0,
                'post_id'=>$data['post_id'],
                'no'=>$credit,
                // 'total'=>$total,
                'msg'=>__('获得购买商品的积分奖励：${post_id}','b2'),
                'type'=>'shop_gx_credit',
                'type_text'=>__('奖励积分','b2')
            ]);

            // Message::add_message(array(
            //     'user_id'=>$data['user_id'],
            //     'msg_type'=>62,
            //     'msg_read'=>1,
            //     'msg_date'=>current_time('mysql'),
            //     'msg_users'=>'',
            //     'msg_credit'=>$credit,
            //     'msg_credit_total'=>$total,
            //     'msg_key'=>$data['post_id'],
            //     'msg_value'=>$data['order_total']
            // ));
        }

        if($data['pay_type'] === 'balance'){
            //订单回调

            global $user_current_money;

            $lass = bcsub($user_current_money,$data['order_total'],2);

            $user_current_money = $lass;

            Gold::update_data([
                'date'=>current_time('mysql'),
                'to'=>$data['user_id'],
                'gold_type'=>1,
                'post_id'=>$data['post_id'],
                'count'=>$data['order_count'],
                'no'=>-$data['order_total'],
                'total'=>$lass,
                'msg'=>__('您购买了商品：${post_id}','b2'),
                'type'=>'shop_gx_credit',
                'type_text'=>__('商城消费','b2')
            ]);
            
            // $res = Message::add_message(array(
            //     'user_id'=>$data['user_id'],
            //     'msg_type'=>63,
            //     'msg_read'=>1,
            //     'msg_date'=>current_time('mysql'),
            //     'msg_users'=>0,
            //     'msg_credit'=>-$data['order_total'],
            //     'msg_credit_total'=>$total,
            //     'msg_key'=>$data['post_id'],
            //     'msg_value'=>''
            // ));

            // if(isset($res['error'])) return $res;
        }

        //库存变更
        Shop::shop_stock_change($data);

        //记录购买信息
        self::buy_resout($data);

        do_action('b2_order_gx_action',$data);

        return apply_filters('b2_order_callback_gx', $money, $data);
    }

    public static function callback_mission($data){
        return apply_filters('b2_order_callback_mission', $data);
    }

    //兑换回调
    public static function callback_d($data){
        //库存变更
        Shop::shop_stock_change($data);

        //记录购买信息
        self::buy_resout($data);
        return apply_filters('b2_order_callback_d', $data);
    }

    //抽奖回调
    public static function callback_c($data){
        //库存变更
        Shop::shop_stock_change($data);

        //记录购买信息
        self::buy_resout($data);
        return apply_filters('b2_order_callback_c', $data);
    }

    //积分充值
    public static function callback_cg($data){
        $dh = (int)b2_get_option('normal_gold','credit_dh');

        $credit = bcmul($data['order_total'],$dh,0);

        Gold::update_data([
            'date'=>current_time('mysql'),
            'to'=>$data['user_id'],
            'gold_type'=>0,
            'post_id'=>0,
            'no'=>$credit,
            'msg'=>__('您购买了积分','b2'),
            'type'=>'shop_cg',
            'type_text'=>__('积分购买','b2')
        ]);

        // $total = Credit::credit_change($data['user_id'],$credit);

        // Message::add_message(array(
        //     'user_id'=>$data['user_id'],
        //     'msg_type'=>56,
        //     'msg_read'=>1,
        //     'msg_date'=>current_time('mysql'),
        //     'msg_users'=>'',
        //     'msg_credit'=>$credit,
        //     'msg_credit_total'=>$total,
        //     'msg_key'=>$data['id'],
        //     'msg_value'=>$data['order_total']
        // ));

        return apply_filters('b2_order_callback_cg', $dh, $data);
    }

    //vip回调
    public static function callback_vip($data){

        $vip = get_user_meta($data['user_id'],'zrz_vip',true);

        $vip_data = b2_get_option('normal_user','user_vip_group');
        $_vip = (string)preg_replace('/\D/s','',$data['order_key']);
        $day = $vip_data[$_vip];
        $day = $day['time'];

        if($vip && $vip === 'vip'.$_vip){
            $time = get_user_meta($data['user_id'],'zrz_vip_time',true);

            if((string)$day === '0'){
                $end = 0;
            }elseif(isset($time['end']) && (string)$time['end'] !== '0'){
                $end = $time['end'] + DAY_IN_SECONDS*$day;
            }else{
                $end = wp_strtotime('+'.$day.' day');
            }

            if(isset($time['start'])){
                $start = $time['start'];
            }else{
                $start = current_time('timestamp');
            }

            if($vip !== $data['order_key']){
                update_user_meta($data['user_id'],'zrz_vip',$data['order_key']);
            }
        }else{
            update_user_meta($data['user_id'],'zrz_vip',$data['order_key']);
            $start = current_time('timestamp');
            if((string)$day === '0'){
                $end = 0;
            }else{
                $end = wp_strtotime('+'.$day.' day');
            }
        }

        update_user_meta($data['user_id'],'zrz_vip_time',array(
            'start'=>$start,
            'end'=>$end
        ));

        wp_cache_delete('b2_user_'.$data['user_id'],'b2_user_custom_data');
        wp_cache_delete('b2_user_'.$data['user_id'],'b2_user_data');

        delete_user_meta($data['user_id'],'b2_download_count');
        return apply_filters('b2_order_callback_vip', $data['order_key'], $data);
    }

    //视频支付成功回调
    public static function callback_v($data){
        $video_payed = get_post_meta($data['post_id'],'b2_video_pay',true);
        $video_payed = is_array($video_payed) ? $video_payed : array();
        $video_payed[] = $data['user_id'];

        update_post_meta($data['post_id'],'b2_video_pay',$video_payed);

        return apply_filters('b2_order_callback_v', $video_payed, $data);
    }

    //支付成功以后，资源下载数据处理
    public static function callback_x($data){

        $buy_data = get_post_meta($data['post_id'],'b2_download_buy',true);
        $buy_data = is_array($buy_data) ? $buy_data : array();

        $buy_data[$data['order_key']] = isset($buy_data[$data['order_key']]) && is_array($buy_data[$data['order_key']]) ? $buy_data[$data['order_key']] : array();
        $buy_data[$data['order_key']][] = $data['user_id'];

        update_post_meta($data['post_id'],'b2_download_buy',$buy_data);

        return apply_filters('b2_order_callback_x', $buy_data, $data);
    }

    //支付成功以后，文章内容阅读
    public static function callback_w($data){

        $buy_data = get_post_meta($data['post_id'],'zrz_buy_user',true);
        $buy_data = is_array($buy_data) ? $buy_data : array();

        $buy_data[] = (int)$data['user_id'];

        update_post_meta($data['post_id'],'zrz_buy_user',$buy_data);

        return apply_filters('b2_order_callback_w', $buy_data, $data);
    }

    //支付成功以后，打赏数据处理
    public static function callback_ds($data){
        $ds = get_post_meta($data['post_id'],'zrz_shang',true);
        $ds = is_array($ds) ? $ds : array();

        $ds[] = array(
            'user'=>$data['user_id'],
            'rmb'=>number_format(round($data['order_price']), 2)
        );

        update_post_meta($data['post_id'],'zrz_shang',$ds);

        return apply_filters('b2_order_callback_ds',$ds, $data);
    }

    //充值成功回调
    public static function callback_cz($data){

        // $total = User::money_change($data['user_id'],$data['order_total']);

        Gold::update_data([
            'date'=>current_time('mysql'),
            'to'=>$data['user_id'],
            'gold_type'=>1,
            'post_id'=>0,
            'no'=>$data['order_total'],
            'msg'=>__('充值成功','b2'),
            'type'=>'shop_cz',
            'type_text'=>__('充值','b2')
        ]);

        // Message::add_message(array(
        //     'user_id'=>$data['user_id'],
        //     'msg_type'=>57,
        //     'msg_read'=>1,
        //     'msg_date'=>current_time('mysql'),
        //     'msg_users'=>'',
        //     'msg_credit'=>$data['order_total'],
        //     'msg_credit_total'=>$total,
        //     'msg_key'=>$data['id'],
        //     'msg_value'=>''
        // ));

        return apply_filters('b2_order_callback_cz','', $data);
    }

    public static function callback_circle_join($data){

        // update_user_meta(1,'test_circle',$data);

        $type = $data['order_key'];

        $arg = array('permanent','year','halfYear','season','month');
        if(!in_array($type,$arg)) return array('error'=>__('有效期错误','b2'));

        $now = current_time('mysql');
        $end = '';

        switch($type){
            case 'year':
                $end = wp_date("Y-m-d H:i:s",wp_strtotime("+1years"));
            break;
            case 'halfYear':
                $end = wp_date("Y-m-d H:i:s",wp_strtotime("+6months"));
            break;
            case 'season':
                $end = wp_date("Y-m-d H:i:s",wp_strtotime("+3months"));
            break;
            case 'month':
                $end = wp_date("Y-m-d H:i:s",wp_strtotime("+1months"));
            break;
        }

        CircleRelate::update_data(array(
            'user_id'=>(int)$data['user_id'],
            'circle_id'=>(int)$data['post_id'],
            'circle_role'=>'member',
            'join_date'=>$now,
            'end_date'=>$end,
            'circle_key'=>$type
        ));

        return apply_filters('b2_order_callback_circle_join','', $data);
    }

    public static function callback_circle_hidden_content_pay($data){

        PostRelationships::update_data(array(
            'type'=>'circle_buy_hidden_content',
            'user_id'=>$data['user_id'],
            'post_id'=>$data['post_id']
        ));

        return apply_filters('b2_order_callback_circle_hidden_content_pay','', $data);
    }

    public static function callback_circle_read_answer_pay($data){

        if($data['pay_type'] !== 'credit' && $data['pay_type'] !== 'balance'){

            $author = get_post_field('post_author', $data['post_id']);

            PostRelationships::update_data(array(
                'type'=>'circle_buy_answer',
                'user_id'=>$data['user_id'],
                'post_id'=>$data['post_id']
            ));

            //给提问者和答主通知
            $answers = Circle::get_answer_authors($data['post_id']);

            $answers = array_merge($answers,array($author));

            $answers = array_flip($answers);

            if(!empty($answers)){
                $average = $data['order_total']/count($answers);
                if($average < 1) return $data;

                foreach ($answers as $v) {
                    $average = intval($average);
                    // $total = User::money_change($v,$average);

                    Message::update_data([
                        'date'=>current_time('mysql'),
                        'from'=>$data['user_id'],
                        'to'=>$v,
                        'post_id'=>$data['post_id'],
                        'msg'=>__('${from}偷瞄了您的回答%s：${post_id}','b2'),
                        'type'=>'author_circle_read_answer',
                        'type_text'=>__('回答被偷瞄','b2'),
                        'old_row'=>1
                    ]);
    
                    Gold::update_data([
                        'date'=>current_time('mysql'),
                        'to'=>$v,
                        'gold_type'=>1,
                        'post_id'=>$data['post_id'],
                        'no'=>$average,
                        'msg'=>__('有${count}人偷瞄了您的回答:${post_id}','b2'),
                        'type'=>'author_circle_read_answer',
                        'type_text'=>__('回答被偷瞄','b2'),
                        'old_row'=>1
                    ]);

                    // self::add_message(array(
                    //     'user_id'=>$v,
                    //     'msg_type'=>76,
                    //     'msg_read'=>0,
                    //     'msg_date'=>current_time('mysql'),
                    //     'msg_users'=>$data['user_id'],
                    //     'msg_credit'=>$average,
                    //     'msg_credit_total'=>$total,
                    //     'msg_key'=>$data['post_id'],
                    //     'msg_value'=>''
                    // ));
                }
            }
        }

        return apply_filters('b2_order_callback_circle_read_answer_pay','', $data);
    }

    //认证支付成功回调
    public static function callback_verify($data){

        $data = array(
            'user_id'=>$data['user_id'],
            'money'=>$data['order_total']
        );

        Verify::add_verify_data($data);

        return apply_filters('b2_order_callback_verify','', $data);
    }

    public static function callback_custom($data){

        $post_id = (int)$data['post_id'];

        $opts = Cpay::get_form_data($post_id);
        $value = json_decode(wp_unslash($data['order_value']),true);
        $data['order_value'] = Cpay::form_str_to_obj($value,$opts,$post_id);

        $callback_fn = get_post_meta($post_id,'b2_single_pay_callback',true);
        if($callback_fn && function_exists($callback_fn)){
            $callback_fn($data);
        }

        return apply_filters('b2_order_callback_custom',$data['order_price'], $data);
    }

    public static function callback_infomation_sticky($data){

        $day_price = b2_get_option('infomation_submit','submit_sticky_price');

        $days = bcdiv($data['order_price'],$day_price);

        $sticky = get_user_meta($data['user_id'],'b2_infomation_sticky_payed',true);

        $sticky = is_array($sticky) ? $sticky : [];

        $sticky[] = [
            'days'=>$days,
            'money'=>$data['order_price'],
            'used'=>false
        ];

        update_user_meta($data['user_id'],'b2_infomation_sticky_payed',$sticky);

        return apply_filters('b2_order_callback_infomation_sticky',$data['order_price'], $data);
    }

    public static function get_gorder_data($products,$user_id){
        $data = array();

        $money = 0;

        foreach ($products as $k => $v) {

            $type = get_post_meta($v['id'],'zrz_shop_type',true);

            $shop_multi = get_post_meta($v['id'],'zrz_shop_multi',true);

            if($type == 'normal' && $shop_multi == 1){
    
                $t = get_post_meta($v['id'],'b2_multi_box',true);
                $t = (array)json_decode($t,true);

                $multi = Shop::get_multi_data($v['id'],$user_id);
                
                if(!empty($multi)){

                    if(isset($multi['attrs'][$v['index']]['price'])){
                        $money = $multi['attrs'][$v['index']]['price'];
                    }
                }
            }else{
                $money = Shop::get_shop_price($v['id'],$user_id,'normal');
            }

            $data[$k] = array(
                'title'=>get_the_title($v['id']),
                'link'=>get_permalink($v['id']),
                'count'=>$v['count'],
                'desc'=>shop::get_keys_by_index($v['id'],$user_id,$v['index']),
                'price'=>$money,
                'thumb'=>b2_get_thumb(array('thumb'=>Post::get_post_thumb($v['id']),'width'=>100,'height'=>100))
            );
        }

        return $data;
    }

    //获取我的订单
    public static function get_my_orders($_user_id,$paged,$state = 'w'){
        $_user_id = (int)$_user_id;
        $user_id = (int)b2_get_current_user_id();

        if($state != 'f' && $state != 'c' && $state != 'all' && $state != 'q' && $state != 'w') return ['error'=>__('参数错误','b2')];

        if(!$_user_id || !$user_id){
            return array('error'=>__('请先登录','b2'));
        }

        if($user_id !== $_user_id && !user_can($user_id, 'administrator' )) return array('error'=>__('权限不足','b2'));

        $user_id = $_user_id;

        $number = 12;
        $offset = ($paged-1)*$number;

        global $wpdb;
        $table_name = $wpdb->prefix . 'zrz_order';

        if($state == 'all'){
            // $sql_and = " AND (order_state = 'f' OR order_state = 'c' OR order_state = 'q' OR (order_state = 'w' && order_type = 'g'))";
            $sql_and = " AND (order_state = 'f' OR order_state = 'c' OR order_state = 'q')";
        }else{
            $sql_and = " AND order_state = '".$state."'";
        }

        //获取订单数据
        $order = $wpdb->get_results(
            $wpdb->prepare("
                SELECT * FROM $table_name
                WHERE user_id = %d $sql_and AND order_type != %s AND order_type != %s ORDER BY `order_date` DESC LIMIT %d,%d
                ",
                $user_id,'coupon','w',$offset,$number
            )
        ,ARRAY_A);

        $data = array();

        $arr = array(
            'w'=>__('等待付款','b2'),
            'f'=>__('已付款未发货','b2'),
            'c'=>__('已发货','b2'),
            's'=>__('已删除','b2'),
            'q'=>__('已签收','b2'),
            't'=>__('已退款','b2')
        );

        foreach ($order as $k => $v) {

            $order_type = b2_order_type($v['post_id']);

            $type = isset($order_type[$v['order_type']]) ? $order_type[$v['order_type']] : __('未知订单类型','b2');

            $title = self::get_order_name($v['order_type'],$v['post_id']);

            $track = maybe_unserialize($v['tracking_number']);
            $tk = b2_express_types();
            $tk = isset($track['type']) ? $tk[$track['type']] : '';
            $nb = isset($track['number']) ? $track['number'] : '';


            $_type = get_post_meta($v['post_id'],'zrz_shop_type',true);

            $shop_multi = get_post_meta($v['post_id'],'zrz_shop_multi',true);

            $desc = [];

            if($_type == 'normal' && $shop_multi == 1){

                $json = json_decode(stripslashes(urldecode($v['order_value'])),true );

                if($json){

                    $desc = $json['desc'];
                }

            }

            $pay_type = $v['pay_type'];

            if(strpos($pay_type,'alipay') !== false){
                $pay_type = __('支付宝','b2');
            }else

            if(strpos($pay_type,'wecatpay') !== false){
                $pay_type = __('微信支付','b2');
            }else

            if($pay_type == 'balance'){
                $pay_type = sprintf(__('%s支付','b2'),B2_MONEY_NAME);
            }else

            if($pay_type == 'credit'){
                $pay_type = __('积分支付','b2');
            }else

            if($pay_type == 'paypal'){
                $pay_type = __('PayPal','b2');
            }

            if($pay_type == 'baidu'){
                $pay_type = __('百度小程序支付','b2');
            }

            if($pay_type == 'toutiao'){
                $pay_type = __('字节小程序支付','b2');
            }

            $pass_days = '';

            if($v['order_state'] === 'c'){

                $pass_days = b2_date_after($v['order_date'],15);
                $current_time =  wp_strtotime(current_time( 'mysql' ));

              //  return array($pass_day,$current_time);

                if($current_time > $pass_days){
                    self::user_change_order_state($v['order_id']);

                    $v['order_state'] = 'q';
                    $v['_order_state'] = 'q';
                    $pass_days = 0;
                }else{
                    $pass_days = b2_get_remainder_time($pass_days,$current_time,0,[],[4,3]);
                }
            }

            $pass_time = '';
            $order_list = [];
            //计算订单过期时间
            if($v['order_state'] === 'w' && $v['order_type'] === 'g'){
                //$v['order_date'] 加 60分钟
                $date=date_create($v['order_date']);
                date_add($date,date_interval_create_from_date_string("60 minutes"));

                // return date_format($date,"Y-m-d H:i:s");
                $end = wp_strtotime(date_format($date,"Y-m-d H:i:s"));

                //计算两个时间的差值
                $pass_time = floor(($end - current_time('timestamp',1))/60);

                $order_list = json_decode(stripslashes($v['order_value']),true);
                $order_list = self::get_gorder_data($order_list['products'],$user_id);
                
            }

            $data[] = apply_filters( 'b2_get_order_item', array(
                'id'=>$v['id'],
                'desc'=>$desc,
                'post_id'=>$v['post_id'],
                'order_id'=>$v['order_id'],
                'order_name'=>$title['title'],
                'order_price'=>$v['order_price'],
                'order_total'=>$v['order_total'],
                'order_count'=>$v['order_count'],
                'order_date'=>$v['order_date'],
                'pass_days'=>$pass_days,
                'order_key'=>$v['order_key'] == 'cq' ? 'cq' : '',
                'order_state'=>isset($arr[$v['order_state']]) ? $arr[$v['order_state']] : 'w',
                '_order_state'=>$v['order_state'],
                'tracking_number'=>isset($v['tracking_number']) ? array('type'=>$tk,'number'=>$nb,'com'=>isset($track['type']) ? $track['type'] : '') : '',
                'money_type'=>$v['money_type'],
                'order_type'=>$type,
                '_order_type'=>$v['order_type'],
                'order_commodity'=>$v['order_commodity'],
                'pay_type'=>$pay_type,
                'thumb'=>$title['img'],
                'address'=>$v['order_address'],
                'order_content'=>$v['order_content'],
                'pass_time'=>$pass_time,
                'g_list'=>$order_list
            ), array(
                'id'=>$v['id'],
                'order_type'=>$v['order_type'],
                'order_id'=>$v['order_id'],
                'post_id'=>$v['post_id'],
                'user_id'=>$v['user_id']
            ));
        }

        $pages = self::get_user_orders_count($user_id,$sql_and);

        return array(
            'pages'=>ceil($pages/$number),
            'data'=>$data
        );
    }

    public static function get_user_orders_count($user_id,$sql_and){
        global $wpdb;
        $table_name = $wpdb->prefix . 'zrz_order';

        return $wpdb->get_var(
            $wpdb->prepare("
                SELECT COUNT(*) FROM $table_name
                WHERE user_id = %d $sql_and
                ",
                $user_id
            ));
    }

    public static function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public static function get_order_name($type,$id){
        $circle_name = b2_get_option('normal_custom','custom_circle_name');
        if($type === 'cz'){
            $name = array(
                'name'=>__('充值','b2'),
                'link'=>b2_get_custom_page_url('gold')
            );
            $img = B2_THEME_URI.'/Assets/fontend/images/order-cz.png';
        }elseif($type === 'cg'){
            $name = array(
                'name'=>__('积分购买','b2'),
                'link'=>b2_get_custom_page_url('gold')
            );
            $img = B2_THEME_URI.'/Assets/fontend/images/order-cg.png';
        }elseif($type === 'vip'){
            $name = array(
                'name'=>__('VIP购买','b2'),
                'link'=>b2_get_custom_page_url('vips')
            );
            $img = B2_THEME_URI.'/Assets/fontend/images/order-vip.png';
        }elseif($type === 'verify'){
            $name = array(
                'name'=>__('认证付费','b2'),
                'link'=>b2_get_custom_page_url('verify')
            );
            $img = B2_THEME_URI.'/Assets/fontend/images/rz-icon.png';
        }elseif($type === 'circle_join'){

            $term = get_term($id,'circle_tags');
            $circle_slug = b2_get_option('normal_custom','custom_circle_link');

            $name = array(
                'name'=>sprintf(__('付费加入%s','b2'),$circle_name),
                'link'=>B2_THEME_URI.'/'.$circle_slug.'/'.$term->slug
            );
            $img = B2_THEME_URI.'/Assets/fontend/images/order-cz.png';
        }elseif($type === 'circle_read_answer_pay'){
            $name = array(
                'name'=>sprintf(__('付费查看%s问答','b2'),$circle_name),
                'link'=>get_permalink( $id)
            );
            $img = B2_THEME_URI.'/Assets/fontend/images/order-cz.png';
        }elseif($type === 'circle_hidden_content_pay'){
            $name = array(
                'name'=>__('付费查看帖子','b2'),
                'link'=>get_permalink( $id)
            );
            $img = B2_THEME_URI.'/Assets/fontend/images/order-cz.png';
        }elseif($type === 'mission'){
            $name = array(
                'name'=>__('签到填坑','b2'),
                'link'=>b2_get_custom_page_url('mission')
            );
            $img = B2_THEME_URI.'/Assets/fontend/images/order-cz.png';
        }elseif($type === 'coupon'){
            $name = array(
                'name'=>__('优惠劵','b2'),
                'link'=>'javascript:void(0)'
            );
            $img = B2_THEME_URI.'/Assets/fontend/images/order-cz.png';
        }elseif($type === 'infomation_sticky'){
            $text = b2_text();
            $name = array(
                'name'=>$text['global']['infomation_sticky_pay_title'],
                'link'=>'javascript:void(0)'
            );
            $img = B2_THEME_URI.'/Assets/fontend/images/info-stocky.png';
        }elseif($type === 'g'){
            $name = array(
                'name'=>__('临时未付款订单','b2'),
                'link'=>'javascript:void(0)'
            );
            $img = B2_THEME_URI.'/Assets/fontend/images/hebing.png';
        }else{
            $name = array(
                'name'=>get_the_title($id),
                'link'=>get_permalink($id)
            );
            $img = b2_get_thumb(array('thumb'=>Post::get_post_thumb($id),'width'=>100,'height'=>100));
        }

        return array(
            'title'=>$name,
            'img'=>$img
        );
    }

    //获取一个管理员生成的邀请码
    public static function get_admin_invite_code($user_id){
        global $wpdb;
        $table_name = $wpdb->prefix.'zrz_invitation';

        $code = $wpdb->get_var(
            $wpdb->prepare("
                SELECT invitation_nub FROM $table_name
                WHERE invitation_owner = %d AND invitation_status = %d AND invitation_user = %d
                ",
                $user_id,0,0
            ));
        
        //如果没有，生成一个
        if(!$code){
            $code = self::create_invite_code($user_id);
        }

        return $code;
    }

    static public function create_guid($inv)
    {

        $guid = '';
        $uid = uniqid("", true);

        $data = AUTH_KEY;
        $data .= $_SERVER['REQUEST_TIME']; // 请求那一刻的时间戳
        $data .= $_SERVER['HTTP_USER_AGENT']; // 获取访问者在用什么操作系统
        $data .= $_SERVER['SERVER_ADDR']; // 服务器IP
        $data .= $_SERVER['SERVER_PORT']; // 端口号
        $data .= $_SERVER['REMOTE_ADDR']; // 远程IP
        $data .= $_SERVER['REMOTE_PORT']; // 端口信息

        $hash = strtoupper(hash('ripemd128', $uid . $guid . md5($data)));

        if ($inv) {
            $guid = substr($hash, 0, 4) . substr($hash, 8, 4) . substr($hash, 12, 4) . substr($hash, 16, 4) . substr($hash, 20, 4);
        } else {
            $guid = substr($hash, 0, 4) . '-' . substr($hash, 8, 4) . '-' . substr($hash, 12, 4) . '-' . substr($hash, 16, 4) . '-' . substr($hash, 20, 4);
        }


        return $guid;
    }

    //生成一个邀请码
    public static function create_invite_code($user_id){
        global $wpdb;
        $table_name = $wpdb->prefix.'zrz_invitation';

        $code = self::create_guid(true);


        $wpdb->insert(
            $table_name,
            array(
                'invitation_owner' => $user_id,
                'invitation_nub' => $code,
                'invitation_status' => 0,
                'invitation_credit' => 0,
                'invitation_user' => 0,
            ),
            array(
                '%d',
                '%s',
                '%d',
                '%s'
            )
        );

        return $code;
    }

    //记录购买结果
    public static function buy_resout($data){

        $post_id = $data['post_id'];
        $user_id = $data['user_id'];

        //虚拟物品还是实物
        $commodity = (int)get_post_meta($post_id,'zrz_shop_commodity',true);

        $res = '';

        if($commodity === 0){
            $xuni = get_post_meta($post_id,'shop_xuni_type',true);
            global $wpdb;
            $table_name = $wpdb->prefix.'zrz_order';

            if($xuni === 'cards'){
                //如果是卡密，记录卡密
                $html = Shop::send_cards($data['post_id'],$data['user_id'],$data['order_count']);
                Shop::send_email($data['order_address'],$data['post_id'],$data['order_count'],$html,$data['order_id'],$data['user_id']);
            }elseif($xuni == 'html'){
                $html = get_post_meta($post_id,'shop_xuni_html_resout',true);
                Shop::send_email($data['order_address'],$data['post_id'],$data['order_count'],$html,$data['order_id'],$data['user_id']);
            }elseif($xuni == 'inv'){
                //如果是邀请码，记录邀请码
                $code = self::get_admin_invite_code(1);
                $html = '<p>'.__('邀请码：','b2').$code.'</p>';

                //将邀请码的拥有者改为购买者
                $wpdb->update(
                    $wpdb->prefix.'zrz_invitation',
                    array(
                        'invitation_user'=>(int)$data['user_id'] !== 0 ? (int)$data['user_id'] : 1
                    ),
                    array(
                        'invitation_nub'=>$code
                    ),
                    '%d',
                    '%s'
                );

                Shop::send_email($data['order_address'],$data['post_id'],$data['order_count'],$html,$data['order_id'],$data['user_id']);
            }

            $wpdb->update(
                $table_name,
                array(
                    'order_content'=>$data['order_content'] ? $data['order_content'].PHP_EOL.PHP_EOL.'[购买结果]：'.PHP_EOL.$html : '[购买结果]：'.PHP_EOL.$html
                ),
                array(
                    'order_id'=>$data['order_id']
                ),
                '%s',
                '%s'
            );

        }

        //记录购买结果
        $buys = get_post_meta($post_id,'b2_buy_users',true);
        $buys = is_array($buys) ? $buys : array();

        if($user_id){
            $buys[$user_id] = $res;
            update_post_meta($post_id,'b2_buy_users',$buys);    
        }
        
        return apply_filters('b2_buy_resout', $data);

    }

    public static function user_change_order_state($order_id,$m = false){

        global $wpdb;
        $table_name = $wpdb->prefix.'zrz_order';

        //获取订单数据
        $order = $wpdb->get_row(
            $wpdb->prepare("
                SELECT * FROM $table_name
                WHERE order_id = %s
                ",
                $order_id
            )
        ,ARRAY_A);

        if(!$m){

            $user_id = b2_get_current_user_id();

            if(!$user_id) return array('error'=>__('非法操作','b2'));

            if(!$order['post_id']) return array('error'=>__('非法操作','b2'));

            if((int)$order['user_id'] !== $user_id) return array('error'=>__('非法操作','b2'));

            if($order['order_state'] === 'q') return array('error'=>__('订单已经确认过了！','b2'));

            if($order['order_state'] !== 'c') return array('error'=>__('非法操作','b2'));

        }else{
            $user_id = $order['user_id'];
        }

        $wpdb->update(
            $table_name,
            array(
                'order_state'=>'q'
            ),
            array(
                'order_id'=>$order_id
            ),
            '%s',
            '%s'
        );

        // PostRelationships::update_data(array('type'=>'shop_buy','user_id'=>$user_id,'post_id'=>$order['post_id']));

        return 'success';
    }
}
