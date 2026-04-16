<?php
namespace B2\Modules\Settings;

use B2\Modules\Settings\OrdersListTable;
use B2\Modules\Common\Wecatmp;
use B2\Modules\Common\User;

class Orders{

    public static $default_settings = array(
        'express_appcode'=>''
    );

    public function init(){
        add_action('cmb2_admin_init',array($this,'orders_options_page'));
        
        add_action( 'admin_init', array($this,'down_csv'));

        add_action("wp_ajax_user_list", [$this,"user_list"]);
        add_action("wp_ajax_user_list_money", [$this,"money_top"]);
        add_action("wp_ajax_user_list_credit", [$this,"credit_top"]);
    }

    public static function get_default_settings($key){
        $arr = array(
            'express_appcode'=>''
        );

        if(isset($arr[$key])){
            return $arr[$key];
        }
    }

    public function orders_options_page(){
        if(!current_user_can('administrator')) return;

        $orders = new_cmb2_box( array(
            'id'           => 'b2_orders_main_options_page',
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_orders_main',
            'tab_group'    => 'b2_orders_options',
            'parent_slug'     => 'b2_main_options',
            'tab_title'    => __('订单统计','b2'),
            'title'   => __('订单管理','b2'), 
            'display_cb'      => array($this,'orders_statistics')
        ) );

        $order_list = new_cmb2_box(array(
            'id'           => 'b2_orders_list_options_page',
            'title'   => __('订单管理','b2'), 
            'tab_title'    => __('订单管理','b2'), 
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_orders_list',
            'parent_slug'     => 'b2_main_options',
            'tab_group'    => 'b2_orders_options',
            'parent_slug'     => '/admin.php?page=b2_orders_main',
            'display_cb'=>array($this,'list_option_page_cb')
        ));

        $order_express = new_cmb2_box(array(
            'id'           => 'b2_orders_express_options_page',
            'title'   => __('快递接口信息','b2'), 
            'tab_title'    => __('快递接口信息','b2'), 
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_orders_express',
            'parent_slug'     => '/admin.php?page=b2_orders_main',
            'tab_group'    => 'b2_orders_options',
        ));

        $order_express->add_field( array(
            'before'=>'<p>'.sprintf(__('目前我们集成了易源数据的快递查询业务，请前往阿里云[%s全球物流快递查询_易源数据%s]然后购买（可0元购买试用），并将生成的key填写到下面设置项中（如果已经购买过，请前往阿里云市场买家中心查看）。'),'<a target="_blank" href="https://market.aliyun.com/products/56928004/cmapi025388.html?spm=5176.10695662.1996646101.searchclickresult.936a284anemB9f&aly_as=txv2-Uu5#sku=yuncode1938800000">','</a>').'<p>',
            'name'    => __( 'AppCode', 'b2' ),
            'id'      => 'express_appcode',
            'desc'=> __( '购买之后，请前往阿里云市场买家中心AppCode', 'b2' ),
            'type'    => 'text'
        ) );
    }

    public function user_list(){

        echo json_encode([
            'money'=>$this->money_top(1),
            'credit'=>$this->credit_top(1)
        ]);

        exit;
    }

    public function money_top($paged = 1){

        if(isset($_REQUEST['paged']) && $_REQUEST['paged']){
            $paged = (int)$_REQUEST['paged'];
        }

        $count = 16;
        $offset = ($paged -1)*$count;

        $args = array(
            'number' => $count,
            'offset'=>$offset,
            'order'=>'DESC',
            'meta_key'=>'zrz_rmb',
            'orderby'=> 'meta_value_num'
        );

        $user_query = new \WP_User_Query($args);

        $total = $user_query->get_total();

        $pages = ceil($total/$count);

        if ( ! empty( $user_query->results ) ) {
            foreach ( $user_query->results as $user ) { 
                $money = get_user_meta($user->ID,'zrz_rmb',true);
                $money = $money ? $money : 0;

                $_data = User::get_user_public_data($user->ID,true);

                $_data['money'] = $money;

                if($_data['desc'] === ''){
                    $_data['desc'] = __('这个人很懒，什么都没有留下！','b2');
                }
                $data[] = $_data;
            }
        }

        if(isset($_REQUEST['paged']) && $_REQUEST['paged']){
            echo json_encode($data);
            exit;
        }else{
            return [
                'list'=>$data,
                'pages'=>$pages
            ];
        }
    }

    public function credit_top($paged = 1){

        if(isset($_REQUEST['paged']) && $_REQUEST['paged']){
            $paged = (int)$_REQUEST['paged'];
        }

        $count = 16;
        $offset = ($paged -1)*$count;

        $args = array(
            'number' => $count,
            'offset'=>$offset,
            'order'=>'DESC',
            'meta_key'=>'zrz_credit_total',
            'orderby'=> 'meta_value_num'
        );

        $user_query = new \WP_User_Query($args);
        $total = $user_query->get_total();

        $pages = ceil($total/$count);

        if ( ! empty( $user_query->results ) ) {
            foreach ( $user_query->results as $user ) { 
                $credit = get_user_meta($user->ID,'zrz_credit_total',true);
                $credit = $credit ? $credit : 0;

                $_data = User::get_user_public_data($user->ID,true);

                $_data['credit'] = b2_number_format($credit);

                if($_data['desc'] === ''){
                    $_data['desc'] = __('这个人很懒，什么都没有留下！','b2');
                }
                $data[] = $_data;
            }
        }

        if(isset($_REQUEST['paged']) && $_REQUEST['paged']){
            echo json_encode($data);
            exit;
        }else{
            return [
                'list'=>$data,
                'pages'=>$pages
            ];
        }

    }

    public function orders_statistics($cmb_options){
        $tabs = $this->cb_options_page_tabs( $cmb_options );

        $money = $this->order_money();
        $money = $money[0];
        $count = $this->order_count();
        $count = $count[0];

        $total = $this->order_total();
        $total = $total[0];

        $order_refund = $this->order_refund();
        $order_refund = $order_refund[0];

        ?>
        <div class="wrap cmb2-options-page option-<?php echo $cmb_options->option_key; ?>">
            <h2><?php echo __('订单统计','b2'); ?></h2>
            <h2 class="nav-tab-wrapper">
                <?php foreach ( $tabs as $option_key => $tab_title ) : ?>
                    <a class="nav-tab<?php if ( isset( $_GET['page'] ) && $option_key === $_GET['page'] ) : ?> nav-tab-active<?php endif; ?>" href="<?php menu_page_url( $option_key ); ?>"><?php echo wp_kses_post( $tab_title ); ?></a>
                <?php endforeach; ?>
            </h2>
            <div class="order-box">
                <p class="red">只统计货币订单，不统计积分订单。不统计余额充值订单，以避免重复统计。</p>
                <div class="box">
                    <div class="order-title">累计收益</div>
                    <div class="order-row">
                        <div class="order-in-row">
                            <div class="row-item green">
                                <span>总收入</span>
                                <span><?php echo '<b>'.B2_MONEY_SYMBOL.'</b>'.($total['total'] ? round($total['total'],2) : 0); ?></span>
                            </div>
                        </div>
                        <div class="order-in-row">
                            <div class="row-item red">
                                <span>已退款</span>
                                <span><?php echo '<b>'.B2_MONEY_SYMBOL.'</b>'.($total['refund'] ? round($total['refund'],2) : 0); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box">
                    <div class="order-title">收益</div>
                    <div class="order-row">
                        <div class="order-in-row">
                            <div class="row-item green">
                                <span>今日收益</span>
                                <span><?php echo '<b>'.B2_MONEY_SYMBOL.'</b>'.($money['day'] ? round($money['day'],2) : 0); ?></span>
                            </div>
                            <div class="row-item">
                                <span>昨日收益</span>
                                <span><?php echo '<b>'.B2_MONEY_SYMBOL.'</b>'.($money['yesterday'] ? round($money['yesterday'],2) : 0); ?></span>
                            </div>
                        </div>
                        <div class="order-in-row">
                            <div class="row-item">
                                <span>本月收益</span>
                                <span><?php echo '<b>'.B2_MONEY_SYMBOL.'</b>'.($money['month'] ? round($money['month'],2) : 0); ?></span>
                            </div>
                            <div class="row-item">
                                <span>上月收益</span>
                                <span><?php echo '<b>'.B2_MONEY_SYMBOL.'</b>'.($money['last_month'] ? round($money['last_month'],2) : 0); ?></span>
                            </div>
                        </div>
                        <div class="order-in-row">
                            <div class="row-item">
                                <span>今年收益</span>
                                <span><?php echo '<b>'.B2_MONEY_SYMBOL.'</b>'.($money['year'] ? round($money['year'],2) : 0); ?></span>
                            </div>
                            <div class="row-item">
                                <span>去年收益</span>
                                <span><?php echo '<b>'.B2_MONEY_SYMBOL.'</b>'.($money['last_year'] ? round($money['last_year'],2) : 0); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box">
                    <div class="order-title">订单</div>
                    <div class="order-row">
                        <div class="order-in-row">
                            <div class="row-item green">
                                <span>今日订单数</span>
                                <span><?php echo ($count['day'] ?? 0); ?></span>
                            </div>
                            <div class="row-item">
                                <span>昨日订单数</span>
                                <span><?php echo ($count['yesterday'] ?? 0); ?></span>
                            </div>
                        </div>
                        <div class="order-in-row">
                            <div class="row-item">
                                <span>本月订单数</span>
                                <span><?php echo ($count['month'] ?? 0); ?></span>
                            </div>
                            <div class="row-item">
                                <span>上月订单数</span>
                                <span><?php echo ($count['last_month'] ?? 0); ?></span>
                            </div>
                        </div>
                        <div class="order-in-row">
                            <div class="row-item">
                                <span>今年订单数</span>
                                <span><?php echo ($count['year'] ?? 0); ?></span>
                            </div>
                            <div class="row-item">
                                <span>去年订单数</span>
                                <span><?php echo ($count['last_year'] ?? 0); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box">
                    <div class="order-title">退款</div>
                    <div class="order-row">
                        <div class="order-in-row">
                            <div class="row-item green">
                                <span>今日退款</span>
                                <span><?php echo '<b>'.B2_MONEY_SYMBOL.'</b>'.($order_refund['day'] ? round($order_refund['day'],2) : 0 ); ?></span>
                            </div>
                            <div class="row-item">
                                <span>昨日退款</span>
                                <span><?php echo '<b>'.B2_MONEY_SYMBOL.'</b>'.($order_refund['yesterday'] ? round($order_refund['yesterday'],2) : 0); ?></span>
                            </div>
                        </div>
                        <div class="order-in-row">
                            <div class="row-item">
                                <span>本月退款</span>
                                <span><?php echo '<b>'.B2_MONEY_SYMBOL.'</b>'.($order_refund['month'] ? round($order_refund['month'],2) : 0); ?></span>
                            </div>
                            <div class="row-item">
                                <span>上月退款</span>
                                <span><?php echo '<b>'.B2_MONEY_SYMBOL.'</b>'.($order_refund['last_month'] ? round($order_refund['last_month'],2) : 0); ?></span>
                            </div>
                        </div>
                        <div class="order-in-row">
                            <div class="row-item">
                                <span>今年退款</span>
                                <span><?php echo '<b>'.B2_MONEY_SYMBOL.'</b>'.($order_refund['year'] ? round($order_refund['year'],2) : 0); ?></span>
                            </div>
                            <div class="row-item">
                                <span>去年退款</span>
                                <span><?php echo '<b>'.B2_MONEY_SYMBOL.'</b>'.($order_refund['last_year'] ? round($order_refund['last_year'],2) : 0); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="paihang order-box">
                <div id="money-top">
                    <div class="money-box box">
                        <h2>财富排行</h2>
                        <div v-if="list">
                            <ul class="money-list" v-if="list.money.list.length > 0">
                                <li v-for="item,i in list.money.list" :key="i">
                                    <a :href="'<?php echo home_url('/gold').'?uid=';?>'+item.id" target="_blank"></a>
                                    <img :src="item.avatar" class="avatar"/>
                                    <div>
                                        <div>{{item.name}}</div>
                                        <div><?php echo B2_MONEY_SYMBOL; ?>{{item.money}}</div>
                                    </div>
                                </li>
                            </ul>
                            <div v-else>
                                没有数据
                            </div>
                        </div>
                        <div class="nav-row" v-if="list">
                            <button class="button action" :disabled="mpaged == 1 || locked" @click="mpaged --;getMoneyList()">上一页</button>
                            <button class="button action" :disabled="mpaged >= list.money.pages || locked" @click="mpaged ++;getMoneyList()">下一页</button>
                        </div>
                    </div>
                    <div class="credit-box box">
                        <h2>积分排行</h2>
                        <div v-if="list">
                            <ul class="money-list" v-if="list.money.list.length > 0">
                                <li v-for="item,i in list.credit.list" :key="i">
                                    <a :href="'<?php echo home_url('/gold').'?uid=';?>'+item.id" target="_blank"></a>
                                    <img :src="item.avatar" class="avatar"/>
                                    <div>
                                        <div>{{item.name}}</div>
                                        <div>{{item.credit}}</div>
                                    </div>
                                </li>
                            </ul>
                            <div v-else>
                                没有数据
                            </div>
                        </div>
                        <div class="nav-row" v-if="list">
                            <button class="button action" :disabled="cpaged == 1 || locked" @click="cpaged --;getCreditList()">上一页</button>
                            <button class="button action" :disabled="cpaged >= list.credit.pages || locked" @click="cpaged ++;getCreditList()">下一页</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function cb_options_page_tabs( $cmb_options ) {
        $tab_group = $cmb_options->cmb->prop( 'tab_group' );
        $tabs      = array();
        foreach ( \CMB2_Boxes::get_all() as $cmb_id => $cmb ) {
            if ( $tab_group === $cmb->prop( 'tab_group' ) ) {
                $tabs[ $cmb->options_page_keys()[0] ] = $cmb->prop( 'tab_title' )
                    ? $cmb->prop( 'tab_title' )
                    : $cmb->prop( 'title' );
            }
        }
        return $tabs;
    }

    public function order_total(){
        global $wpdb;
        $table_name = $wpdb->prefix . 'zrz_order';

        $data = $wpdb->get_results("
            select 
            sum(IF(order_state != 't',order_total,0)) as total,
            sum(IF(order_state = 't',order_total,0)) as refund
            from $table_name where order_state != 'w' AND money_type = 0 AND order_type != 'cz'
        ",ARRAY_A);
        
        return $data;
    }

    public function down_csv(){

        if(!apply_filters('b2_check_role',0)) return;

        // $result = $_REQUEST['params'];

        // $start = $result['start'];
        // $end = $result['end'];
        // $order_state = $result['order_state'];
        // $order_type = $result['order_type'];

        if(!isset($_REQUEST['order_out'])) return;
 
        $start = isset($_REQUEST['start']) && $_REQUEST['start'] ? $_REQUEST['start'] : '';
        $end = isset($_REQUEST['end']) && $_REQUEST['end'] ? $_REQUEST['end'] : '';
        $order_state = isset($_REQUEST['order_state']) && $_REQUEST['order_state'] ? $_REQUEST['order_state'] : '';
        $order_type = isset($_REQUEST['order_type']) && $_REQUEST['order_type'] ? $_REQUEST['order_type'] : '';

        global $wpdb; 
        $table_name = $wpdb->prefix . 'zrz_order';

        $query = "SELECT * FROM $table_name";

        //状态筛选
        if ($order_state && $order_state != 'all') {
            $w = 'WHERE';
            if(strpos($query,'WHERE') !== false){
                $w = 'AND';
            }

            if($order_state === 'total'){
                $query.= $wpdb->prepare(" $w `order_state` != %s",'w');
            }elseif($order_state === 'w'){
                $query.= $wpdb->prepare(" $w `order_state` = %s",'w');
            }elseif($order_state === 'f'){
                $query.= $wpdb->prepare(" $w `order_state` = %s",'f');
            }elseif($order_state === 'c'){
                $query.= $wpdb->prepare(" $w `order_state` = %s",'c');
            }elseif($order_state === 'q'){
                $query.= $wpdb->prepare(" $w `order_state` = %s",'q');
            }elseif($order_state === 't'){
                $query.= $wpdb->prepare(" $w `order_state` = %s",'t');
            }

        }

        if($start && $end){
            $w = 'WHERE';
            if(strpos($query,'WHERE') !== false){
                $w = 'AND';
            }
            $query.= $wpdb->prepare(" $w `order_date` >= %s AND `order_date` <= %s",$start,$end);
        }

        //筛选商品类型
        if ($order_type) {
            if(strpos($query,'WHERE') !== false){
                $query.= $wpdb->prepare(" AND `order_type`= %s",$order_type);
            }else{
                $query.= $wpdb->prepare(" WHERE `order_type`= %s",$order_type);
            }
        }


        $arg = $wpdb->get_results($query,ARRAY_A);

        if(empty($arg)){
            print json_encode(array('status'=>401,'msg'=>__('没有订单','b2')));
            exit;
        }

        $order_code = new OrdersListTable();

        set_time_limit(0);
        $csv = new Array2Csv();
        $filename = 'orders_'.$start.'_to_'.$end.'.csv';
        $csv->cvsHeader($filename);
    
        $head =  ['id','订单号','买家','购买内容','订单类型','商品类型','订单状态','订单日期','订单数量','单价','总价','货币类型','key','value','内容','支付渠道','快递单号','收货地址','标记','mobile'];
        $csv->outputData($head);
    
        $limit = 10000; 
        $cnt   = 0;    

        foreach ($arg as $k => $v) {
            $author_obj = get_userdata($v['user_id']);
            $data = [
                $v['id'],
                $v['order_id'],
                $this->dot2s((isset($author_obj->display_name) ? $author_obj->display_name : '').'(id:'.$v['user_id'].')'),
                $this->dot2s(get_the_title($v['post_id']).'(id:'.$v['post_id'].')'),
                $order_code->get_shop_order('order_type',$v['order_type'],true),
                $order_code->get_shop_order('order_commodity',$v['order_commodity'],true),
                $order_code->get_shop_order('order_state',$v['order_state'],true),
                $v['order_date'],
                $v['order_count'],
                $v['money_type'] == 0 ? B2_MONEY_SYMBOL.$v['order_price'] : '积分：'.$v['order_price'],
                $v['money_type'] == 0 ? B2_MONEY_SYMBOL.$v['order_total'] : '积分：'.$v['order_total'],
                $v['money_type'] == 0 ? '货币' : '积分',
                $this->dot2s($v['order_key']),
                $this->dot2s($v['order_value']),
                $this->dot2s($v['order_content']),
                $order_code->get_shop_order('pay_type',$v['pay_type'],true),
                $v['tracking_number'],
                $this->dot2s($v['order_address']),
                $v['order_mark'],
                $v['order_mobile']
            ];
            $cnt++;
            if ($limit == $cnt) {
                $csv->csvFlush($cnt);
            }
            $csv->outputData($data);
        }
 
        
        $csv->closeFile();
        exit;
           
        
    }

    public function dot2s($str){
        
        return str_replace(',','，',$str);
    }

    public function fliter_total(){
        global $wpdb;
        $table_name = $wpdb->prefix . 'zrz_order';

        $start = isset($_REQUEST['start']) && $_REQUEST['start'] ? $_REQUEST['start'] : '';
        $end = isset($_REQUEST['end']) && $_REQUEST['end'] ? $_REQUEST['end'] : '';

        $data = [];

        $data[0] = [
            'total'=>0,
            'refund'=>0
        ];

        if($start && $end){
            $data = $wpdb->get_results("
            select 
            sum(IF(order_state != 't' && DATE_FORMAT(order_date,'%Y-%m-%d') >= '$start' && DATE_FORMAT(order_date,'%Y-%m-%d') <= '$end',order_total,0)) as total,
            sum(IF(order_state = 't' && DATE_FORMAT(order_date,'%Y-%m-%d') >= '$start' && DATE_FORMAT(order_date,'%Y-%m-%d') <= '$end',order_total,0)) as refund
            from $table_name where order_state != 'w' AND money_type = 0 AND order_type != 'cz'
            ",ARRAY_A);
        }
        
        
        return $data;
    }

    public function order_refund(){
        global $wpdb;
        $table_name = $wpdb->prefix . 'zrz_order';

        $data = $wpdb->get_results("
            select 
            SUM(CASE WHEN DATE(order_date) = CURDATE() THEN order_total ELSE 0 END) AS day,
            SUM(CASE WHEN DATE(order_date) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) THEN order_total ELSE 0 END) AS yesterday,
            SUM(CASE WHEN YEAR(order_date) = YEAR(CURRENT_DATE()) AND MONTH(order_date) = MONTH(CURRENT_DATE()) THEN order_total ELSE 0 END) AS month,
            SUM(CASE WHEN YEAR(order_date) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH)) AND MONTH(order_date) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH)) THEN order_total ELSE 0 END) AS last_month,
            SUM(CASE WHEN YEAR(order_date) = YEAR(CURRENT_DATE()) THEN order_total ELSE 0 END) AS year,
            SUM(CASE WHEN YEAR(order_date) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 YEAR)) THEN order_total ELSE 0 END) AS last_year
            from $table_name where order_state = 't' AND money_type = 0
        ",ARRAY_A);

        
        return $data;
    }

    public function order_money(){
        // $cache = wp_cache_get('order_money', 'b2_order_data');
        // if($cache) return  $cache;
        // * w : 等待付款 ，f : 已付款未发货 ，c : 已发货 ，s : 已删除 ，q : 已签收 ，t : 已退款
        global $wpdb;
        $table_name = $wpdb->prefix . 'zrz_order';

        $data = $wpdb->get_results("
            select 
            SUM(CASE WHEN DATE(order_date) = CURDATE() THEN order_total ELSE 0 END) AS day,
            SUM(CASE WHEN DATE(order_date) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) THEN order_total ELSE 0 END) AS yesterday,
            SUM(CASE WHEN YEAR(order_date) = YEAR(CURRENT_DATE()) AND MONTH(order_date) = MONTH(CURRENT_DATE()) THEN order_total ELSE 0 END) AS month,
            SUM(CASE WHEN YEAR(order_date) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH)) AND MONTH(order_date) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH)) THEN order_total ELSE 0 END) AS last_month,
            SUM(CASE WHEN YEAR(order_date) = YEAR(CURRENT_DATE()) THEN order_total ELSE 0 END) AS year,
            SUM(CASE WHEN YEAR(order_date) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 YEAR)) THEN order_total ELSE 0 END) AS last_year
            from $table_name where order_state != 'w' AND order_state != 't' AND money_type = 0 AND order_type != 'cz'
        ",ARRAY_A);

        
        return $data;
    }

    public function order_count(){
        // $cache = wp_cache_get('order_money', 'b2_order_data');
        // if($cache) return  $cache;
        // * w : 等待付款 ，f : 已付款未发货 ，c : 已发货 ，s : 已删除 ，q : 已签收 ，t : 已退款
        /**
         * 
         * * $order_type //订单类型
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
        global $wpdb;
        $table_name = $wpdb->prefix . 'zrz_order';

        $data = $wpdb->get_results("
            select 
            SUM(CASE WHEN DATE(order_date) = CURDATE() THEN 1 ELSE 0 END) AS day,
            SUM(CASE WHEN DATE(order_date) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) THEN 1 ELSE 0 END) AS yesterday,
            SUM(CASE WHEN YEAR(order_date) = YEAR(CURRENT_DATE()) AND MONTH(order_date) = MONTH(CURRENT_DATE()) THEN 1 ELSE 0 END) AS month,
            SUM(CASE WHEN YEAR(order_date) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH)) AND MONTH(order_date) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH)) THEN 1 ELSE 0 END) AS last_month,
            SUM(CASE WHEN YEAR(order_date) = YEAR(CURRENT_DATE()) THEN 1 ELSE 0 END) AS year,
            SUM(CASE WHEN YEAR(order_date) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 YEAR)) THEN 1 ELSE 0 END) AS last_year
            from $table_name where order_state != 'w' AND order_state != 't' AND money_type = 0 AND order_type != 'cz'
        ",ARRAY_A);
        
        return $data;
    }


    public function list_option_page_cb($cmb_options){

        $this->delete_expired_orders();
        $tabs = $this->cb_options_page_tabs( $cmb_options );
        $order_code = new OrdersListTable();
        $order_code->prepare_items();
        $status = isset($_REQUEST["order_state"]) ? esc_sql($_REQUEST["order_state"]) : 'total';
        $ref_url = admin_url('admin.php?'.$_SERVER['QUERY_STRING']);

        if((isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete') || (isset($_REQUEST['action2']) && $_REQUEST['action2'] == 'delete')){
            
            $order_ids = isset($_REQUEST['id']) ? (array)$_REQUEST['id'] : '';

            if($order_ids){
                $order_code->delete_coupons($order_ids);
                $ref_url = wp_get_referer();
                $ref_url = remove_query_arg(array('id', 'action','action2','s'), $ref_url);
                exit(header("Location: ".$ref_url));
                echo '<script> location.replace("'.$ref_url.'"); </script>';
            }
        }

        $type = b2_order_type();

        $type_get = isset($_REQUEST['order_type']) ? $_REQUEST['order_type'] : '';

        $fliter = [
            'total'=>0,
            'refund'=>0
        ];

        if(isset($_REQUEST["start"]) && isset($_REQUEST["end"]) && $_REQUEST["start"] && $_REQUEST["end"]){
            $fliter = $this->fliter_total();
            $fliter = $fliter[0];
        }

        $status_total = $order_code->get_status_count();
        $status_total = $status_total[0];

    ?>
        <div class="wrap cmb2-options-page option-<?php echo $cmb_options->option_key; ?>">
            <?php if ( get_admin_page_title() ) : ?>
                <h2><?php echo wp_kses_post( get_admin_page_title() ); ?></h2>
            <?php endif; ?>

            <h2 class="nav-tab-wrapper">
                <?php foreach ( $tabs as $option_key => $tab_title ) : ?>
                    <a class="nav-tab<?php if ( isset( $_REQUEST['page'] ) && $option_key === $_REQUEST['page'] ) : ?> nav-tab-active<?php endif; ?>" href="<?php menu_page_url( $option_key ); ?>"><?php echo wp_kses_post( $tab_title ); ?></a>
                <?php endforeach; ?>
            </h2>
            <div class="wrap">
                <?php if(isset($_REQUEST['action']) && $_REQUEST['action'] === 'edit'){ ?>
                    <?php 
                        $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;

                        $update = isset($_REQUEST['order_update']) ? (int)$_REQUEST['order_update'] : 0;

                        global $wpdb;
                        $table_name = $wpdb->prefix . 'zrz_order';

                        if($update){
                            $address = isset($_REQUEST['order_address']) ? $_REQUEST['order_address'] : '';
                            $content = isset($_REQUEST['order_content']) ? $_REQUEST['order_content'] : '';

                            $kd = isset($_REQUEST['kuaidi']) ? $_REQUEST['kuaidi'] : '';
                            $number = isset($_REQUEST['express_number']) ? $_REQUEST['express_number'] : '';
                            $state = isset($_REQUEST['edit_state']) ? $_REQUEST['edit_state'] : '';
                            $_res = $wpdb->update(
                                $table_name, 
                                array(
                                    'order_state'=>$state,
                                    'order_address'=>$address,
                                    'order_content'=>$content,
                                    'tracking_number'=>maybe_serialize(array(
                                        'type'=>$kd,
                                        'number'=>$number
                                    ))
                                )
                                , array('id'=>$id)
                            );

                            if($_res){
                                b2_settings_error('updated',__('更新成功','b2'));
                            }
                        }

                        $res = $wpdb->get_row($wpdb->prepare("
                                SELECT * FROM $table_name
                                WHERE id = %d
                            ",
                            $id
                        ),ARRAY_A);

                        $kds = b2_express_types(); 

                        if($update && $_res){

                            if($res['order_state'] == 'c'){
    
                                Wecatmp::message_success([
                                    'type'=>'order_ship',
                                    "touser"=>$res['user_id'],
                                    "template_id"=>'',
                                    "url"=>get_permalink($res['post_id']),      
                                    "data"=>[
                                        "first"=> [
                                            "value"=>'您的订单已经发货。',
                                            "color"=>"#173177"
                                        ],
                                        "keyword1"=>[
                                            "value"=>$res['order_id'],
                                            "color"=>"#173177"
                                        ],
                                        "keyword2"=> [
                                            "value"=>current_time('mysql'),
                                            "color"=>"#173177"
                                        ],
                                        "keyword3"=> [
                                            "value"=>$kds[$kd],
                                            "color"=>"#173177"
                                        ],
                                        "keyword4"=> [
                                            "value"=>$number,
                                            "color"=>"#173177"
                                        ],
                                        "remark"=>[
                                            "value"=>'请注意查收货物！',
                                            "color"=>"#173177"
                                        ]
                                    ]
                                ]);
                            }
                            
                        }

                        if(empty($res)) {
                            echo __('没有找到此订单','b2').'</div>
                            </div>';
                            return;
                        }

                        $kuaidi = maybe_unserialize($res['tracking_number']);
                    ?>
                    <div id="profile-page">
                        <form id="order-edit" method="post">
                            <?php 
                                // global $wp;
                                // $url = home_url( $wp->request );
                                // $url = preg_replace('#page/([^/]*)$#','', $url);
                            ?>
                            <a href="<?php echo remove_query_arg(array('id','kuaidi','express_number','order_address','order_content','action','order_update','submit-update-order'),$ref_url); ?>">返回到订单列表</a>
                            <table class="form-table" role="presentation">
                                <tbody>
                                    <tr>
                                        <th scope="row"><label for="blogname"><?php echo __('订单ID：'); ?></label></th>
                                        <td><?php echo $id; ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="blogname"><?php echo __('订单号：'); ?></label></th>
                                        <td><?php echo $res['order_id']; ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="blogname"><?php echo __('订单日期：'); ?></label></th>
                                        <td><?php echo $res['order_date']; ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="blogname"><?php echo __('订单类型：'); ?></label></th>
                                        <td><?php echo $order_code->get_shop_order('order_type',$res['order_type']); ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="blogname"><?php echo __('商品类型：'); ?></label></th>
                                        <td><?php echo $order_code->get_shop_order('order_commodity',$res['order_commodity']); ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="blogname"><?php echo __('支付渠道：'); ?></label></th>
                                        <td><?php echo $order_code->get_shop_order('pay_type',$res['pay_type']); ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="blogname"><?php echo __('商品名称：'); ?></label></th>
                                        <td>
                                            <?php 
                                                if($res['post_id'] == -1){
                                                    echo __('合并付款临时订单','b2');
                                                }else{
                                                    $title = \B2\Modules\Common\Orders::get_order_name($res['order_type'],$res['post_id']);
                                                    echo '<a href="'.$title['title']['link'].'" target="_blank">'.$title['title']['name'].'</a>';
                                                }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php 
                                            $type = get_post_meta($res['post_id'],'zrz_shop_type',true);

                                            $shop_multi = get_post_meta($res['post_id'],'zrz_shop_multi',true);
                        
                                            if($type == 'normal' && $shop_multi == 1){
                        
                                                $json = json_decode(stripslashes(urldecode($res['order_value'])),true );
                        
                                                if($json){
                        
                                                    $html = '';
                        
                                                    foreach ($json['desc'] as $key => $value) {
                                                        $html .= '<p><span>'.$value['name'].'</span>：<span>'.$value['value'].'</span></p>';
                                                    }
                        
                                                    ?>
                                                    <tr>
                                                        <th><?php echo __('商品规格：'); ?></th>
                                                        <td><?php echo $html; ?></td>
                                                    </tr>
                                                    <?php
                                                }
                                            }
                                        ?>
                                    <tr>
                                        <th scope="row"><label for="blogname"><?php echo __('产品单价：'); ?></label></th>
                                        <td><?php echo ($res['money_type'] == 1 ? __('积分：','b2') : B2_MONEY_SYMBOL).$res['order_price']; ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="blogname"><?php echo __('订单总价：'); ?></label></th>
                                        <td><?php echo ($res['money_type'] == 1 ? __('积分：','b2') : B2_MONEY_SYMBOL).$res['order_total']; ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="blogname"><?php echo __('订单数量：'); ?></label></th>
                                        <td><?php echo $res['order_count']; ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="blogname"><?php echo __('订单状态：'); ?></label></th>
                                        <td>
                                            <?php
                                                $arr = array(
                                                    'w'=>__('等待付款','b2'),
                                                    'f'=>__('已付款未发货','b2'),
                                                    'c'=>__('已发货','b2'),
                                                    's'=>__('已删除','b2'),
                                                    'q'=>__('已签收','b2'),
                                                    't'=>__('已退款','b2'),
                                                );
                                            ?>
                                            <select name="edit_state" id="">
                                                <?php 
                                                    foreach($arr as $k => $v){
                                                        echo '<option value="'.$k.'" '.($res['order_state'] === $k ? 'selected' : false).'>'.$v.'</option>';
                                                    }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="blogname"><?php echo __('快递公司：'); ?></label></th>
                                        <td>
                                            <?php 
                                                $kd_type = isset($kuaidi['type']) ? $kuaidi['type'] : 'shunfeng';
                                            ?>
                                            <select name="kuaidi" id="">
                                                <option value="">无</option>
                                                <?php 
                                                    foreach($kds as $k => $v){
                                                        echo '<option value="'.$k.'" '.($kd_type === $k ? 'selected' : false).'>'.$v.'</option>';
                                                    }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr class="user-email-wrap">
                                        <th scope="row"><label for="blogname"><?php echo __('运单号：'); ?></label></th>
                                        <td><input type="text" name="express_number" class="regular-text ltr" value="<?php echo isset($kuaidi['number']) ? $kuaidi['number'] : ''; ?>"></td>
                                    </tr>
                                    <tr class="user-description-wrap">
                                        <th scope="row"><label for="blogname"><?php echo __('订单地址：'); ?></label></th>
                                        <td><textarea rows="5" cols="30" name="order_address"><?php echo $res['order_address']; ?></textarea></td>
                                    </tr>
                                    <tr class="user-description-wrap">
                                        <th scope="row"><label for="blogname"><?php echo __('买家留言：'); ?></label></th>
                                        <td><textarea rows="5" cols="30" name="order_content"><?php echo $res['order_content']; ?></textarea></td>
                                    </tr>
                                </tbody>
                            </table>
                            <input type="hidden" name="page" value="b2_orders_list">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <input type="hidden" name="order_update" value="1">
                            <input type="hidden" name="order_type" value="<?php echo isset($_REQUEST['order_type']) ? $_REQUEST['order_type'] : 0;?>">
                            <input type="hidden" name="order_state" value="<?php echo isset($_REQUEST['order_state']) ? $_REQUEST['order_state'] : 0;?>">
                            <input type="hidden" name="paged" value="<?php echo isset($_REQUEST['paged']) ? (int)$_REQUEST['paged'] : 0;?>">
                            <p class="submit"><input type="submit" name="submit-update-order" id="submit-cmb" class="button button-primary" value="保存"></p>
                        </form>
                    </div>
                <?php }else{ ?>
                    <div class="order-box" id="fliter-box">
                        <p class="red">只统计货币订单，不统计积分订单。不统计余额充值订单，以避免重复统计。</p>
                        <div class="box">
                            <div class="order-title">订单筛选</div>
                            <div class="fliter-box">
                                <div><span>开始时间</span><vuejs-datepicker :language="zh" v-model="start"></vuejs-datepicker></div>
                                <div><span>结束时间</span><vuejs-datepicker :language="zh" v-model="end"></vuejs-datepicker></div>
                                <div><button class="button action" @click="fliter">筛选</button></div>
                                <div><button class="button action" @click="canel">取消</button></div>
                            </div>
                        </div>
                        <?php if(isset($_REQUEST["start"]) && isset($_REQUEST["end"]) && $_REQUEST["start"] && $_REQUEST["end"]){ ?>
                        <div class="box fliter-box-total">
                            <div class="order-title">筛选统计</div>
                            <div class="order-row">
                                <div class="order-in-row">
                                    <div class="row-item green">
                                        <span>收入</span>
                                        <span><?php echo '<b>'.B2_MONEY_SYMBOL.'</b>'.($fliter['total'] ?? 0); ?></span>
                                    </div>
                                </div>
                                <div class="order-in-row">
                                    <div class="row-item red">
                                        <span>退款</span>
                                        <span><?php echo '<b>'.B2_MONEY_SYMBOL.'</b>'.($fliter['refund'] ?? 0); ?></span>
                                    </div>
                                </div>
                                <div>
                                    <a class="button action" href="<?php echo add_query_arg('order_out','1',$ref_url); ?>">导出所选订单</a>
                                </div>
                            </div>
                            <a class="close-fliter" href="<?php echo remove_query_arg(array('start','end'),$ref_url) ?>">x关闭筛选</a>
                        </div>
                        <?php } ?>
                    </div>
                       
                    <div class="filter-row1">
                        <a href="<?php echo remove_query_arg(array('order_state','s'),$ref_url); ?>" class="<?php echo $status === 'total' ? 'current' : ''; ?>"><?php echo __('所有','b2'); ?><span class="count">（<?php echo $status_total['total']; ?>）</span></a>
                        <a href="<?php echo add_query_arg('order_state','q',$ref_url); ?>" class="<?php echo $status === 'q' ? 'current' : ''; ?>"><?php echo __('已签收','b2'); ?><span class="count">（<?php echo $status_total['q']; ?>）</span></a>
                        <a href="<?php echo add_query_arg('order_state','w',$ref_url); ?>" class="<?php echo $status === 'w' ? 'current' : ''; ?>"><?php echo __('等待付款','b2'); ?><span class="count">（<?php echo $status_total['w']; ?>）</span></a>
                        <a href="<?php echo add_query_arg('order_state','f',$ref_url); ?>" class="<?php echo $status === 'f' ? 'current' : ''; ?>"><?php echo __('已付款，未发货','b2'); ?><span class="count">（<?php echo $status_total['f']; ?>）</span></a>
                        <a href="<?php echo add_query_arg('order_state','c',$ref_url); ?>" class="<?php echo $status === 'c' ? 'current' : ''; ?>"><?php echo __('已发货','b2'); ?><span class="count">（<?php echo $status_total['c']; ?>）</span></a>
                        <a href="<?php echo add_query_arg('order_state','t',$ref_url); ?>" class="<?php echo $status === 't' ? 'current' : ''; ?>"><?php echo __('已退款','b2'); ?><span class="count">（<?php echo $status_total['t']; ?>）</span></a>
                    </div>
                    <ul class="subsubsub">
                        <li><a href="<?php echo remove_query_arg(array('order_type','s'),$ref_url); ?>" class="<?php echo $type_get === '' ? 'current' : ''; ?>"><?php echo __('所有','b2'); ?><span class="count">（<?php echo $status_total['total']; ?>）</span></a></li>
                        <?php
                            foreach ($type as $k => $v) {
                                if($k == 'c') $k = 'cj';
                                if($k == 'w') $k = 'wz';
                        ?>
                            <li>| <a href="<?php echo add_query_arg('order_type',$k,$ref_url); ?>" class="<?php echo $type_get === $k ? 'current' : ''; ?>"><?php echo $v; ?><span class="count">（<?php echo $status_total[$k]; ?>）</span></a></li>
                        <?php
                            }
                        ?>
                    </ul>
                    <div id="icon-users" class="icon32"><br/></div>  
                    <form id="coupon-filter" method="get">
                        <input type="hidden" name="order_state" value="<?php echo isset($_REQUEST['order_state']) ? $_REQUEST['order_state'] : ''; ?>">
                        <input type="hidden" name="order_type" value="<?php echo isset($_REQUEST['order_type']) ? $_REQUEST['order_type'] : ''; ?>">
                        <?php
                            $order_code->search_box( __('搜索订单','b2'), 'search_id' );
                        ?>
                        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />

                        <?php $order_code->display() ?>
                    </form>
                <?php } ?>
            </div>
        </div>
        <?php
    }

    public function delete_expired_orders(){
        global $wpdb; 
        $table_name = $wpdb->prefix . 'zrz_order';

        $res = $wpdb->query($wpdb->prepare("DELETE FROM $table_name WHERE order_state = %s AND order_date < date_sub(now(), interval 60 minute)", 'w'));

        return $res;
    }
}
?>