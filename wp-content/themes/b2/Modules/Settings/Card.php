<?php
namespace B2\Modules\Settings;

use B2\Modules\Common\Orders as Corders;

class Card{

    public function init(){
        add_action('cmb2_admin_init',array($this,'card_options_page'));
        add_action('cmb2_admin_init',array($this,'list_options_page'));
        add_action('cmb2_admin_init',array($this,'card_out'));
        add_action( 'cmb2_override_option_save_b2_card_bulid', array($this,'save_action'), 10, 3 );
        add_action( 'admin_init', array($this,'down_csv'));
    }

    public function card_options_page(){
        //常规设置
        $card = new_cmb2_box( array(
            'id'           => 'b2_card_bulid_options_page',
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_card_bulid',
            'tab_group'    => 'b2_card_options',
            'parent_slug'     => 'b2_main_options',
            'tab_title'    => __('卡密生成','b2'),
            'menu_title'   => __('卡密管理','b2'),
            'save_button'     => __( '生成', 'b2' ),
            'message_cb'=>array($this,'bulid_message_cb'),
        ));

        $card->add_field( array(
            'name'    => __( '生成卡密的数量', 'b2' ),
            'id'      => 'card_code_count',
            'desc'=> __( '不要一次生成太多，以免造成服务器阻塞，建议少于500个', 'b2' ),
            'type'    => 'text',
            'default'=>100,
            'attributes' => array(
                'type' => 'number',
                'pattern' => '\d*',
            ),
        ) );

        $card->add_field( array(
            'name'    => __( '生成卡密的面值', 'b2' ),
            'id'      => 'card_money',
            'type' => 'text_money',
            'sanitization_cb' => 'b2_sanitize_number',
            'before_field' => B2_MONEY_SYMBOL,
            'default'=>100
        ) );
    }

    //卡密导出
    public static function card_out(){
        $card = new_cmb2_box( array(
            'id'           => 'b2_card_out_options_page',
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_card_out',
            'tab_group'    => 'b2_card_options',
            'parent_slug'     => '/admin.php?page=b2_card_main',
            'tab_title'    => __('卡密导出','b2'),
            'menu_title'   => __('卡密导出','b2'),
            'save_button'     => __( '导出', 'b2' ),
            'display_cb'      => array(__CLASS__,'out_option_page_cb'),
        ));

        $card->add_field( array(
            'name'    => __( '开始ID', 'b2' ),
            'id'      => 'card_code_start',
            'desc'=> __( '从哪个ID开始导出', 'b2' ),
            'type'    => 'text',
            'default'=>1,
            'attributes' => array(
                'type' => 'number',
                'pattern' => '\d*',
            ),
        ) );
        $card->add_field( array(
            'name'    => __( '结束ID', 'b2' ),
            'id'      => 'card_code_end',
            'desc'=> __( '从哪个ID结束导出', 'b2' ),
            'type'    => 'text',
            'default'=>100,
            'attributes' => array(
                'type' => 'number',
                'pattern' => '\d*',
            ),
        ) );
    }

    public function get_card_list($start,$end,$status){
        global $wpdb; 
        $table_name = $wpdb->prefix . 'zrz_card';

        $and = '';

        if((int)$status !== 2){
            $and = $wpdb->prepare("AND `card_status`=%d",$status);
        }

        $res = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table_name WHERE `id` >= %d AND `id` <= %d $and ORDER BY `id` ASC ",$start,$end),
            ARRAY_A
        );

        return $res;
    }

    public function down_csv(){

        if(!apply_filters('b2_check_role',0)) return;

        $start = isset($_GET['card_code_start']) ? (int)$_GET['card_code_start'] : '';
        $end = isset($_GET['card_code_end']) ? (int)$_GET['card_code_end'] : '';
        $status = isset($_GET['card_code_status']) ? (int)$_GET['card_code_status'] : '';
        $down = isset($_GET['down']) ? (int)$_GET['down'] : '';
       
        if($start && $end && $down){
            $data = $this->get_card_list($start,$end,$status);
            if($data){
                set_time_limit(0);
                $csv = new Array2Csv();
                $filename = 'card_'.$start.'_to_'.$end.'.csv';
                $csv->cvsHeader($filename);
            
                $head =  [ 'id',__('卡号','b2'), __('密码','b2'), __('面值','b2'),__('状态','b2'),__('使用者','b2')];
                $csv->outputData($head);
            
                $limit = 10000; 
                $cnt   = 0;    
                $size  = 2000;

                foreach ($data as $k => $v) {
                    $data = [
                        $v['id'],
                        $v['card_key'],
                        $v['card_value'],
                        B2_MONEY_SYMBOL.$v['card_rmb'],
                        $v['card_status'] == 1 ? __('已使用','b2') : __('未使用','b2'),
                        $v['card_user']
                    ];
                    $cnt++;
                    if ($limit == $cnt) {
                        $csv->csvFlush($cnt);
                    }
                    $csv->outputData($data);
                }
                unset($v);
                
                $csv->closeFile();
                exit;
            }else{
                add_settings_error('b2_settings_message',esc_attr( 'b2_settings_updated' ), __('没有找到这个范围内的卡密','b2') , 'updated');
            }
        }
    }

    public static function out_option_page_cb($cmb_options){
        if(!apply_filters('b2_check_role',0)) return;
        $tabs = self::cb_options_page_tabs( $cmb_options );
        $start = isset($_GET['card_code_start']) ? (int)$_GET['card_code_start'] : 1;
        $end = isset($_GET['card_code_end']) ? (int)$_GET['card_code_end'] : 100;
        $status = isset($_GET['card_code_status']) ? (int)$_GET['card_code_status'] : 1;
    ?>
       <div class="wrap cmb2-options-page option-<?php echo $cmb_options->option_key; ?>">
            <h2 class="nav-tab-wrapper">
                <?php foreach ( $tabs as $option_key => $tab_title ) : ?>
                    <a class="nav-tab<?php if ( isset( $_GET['page'] ) && $option_key === $_GET['page'] ) : ?> nav-tab-active<?php endif; ?>" href="<?php menu_page_url( $option_key ); ?>"><?php echo wp_kses_post( $tab_title ); ?></a>
                <?php endforeach; ?>
            </h2>
            <div class="wrap">
                <form id="coupon-filter" method="get" get="<?php echo admin_url('admin.php'); ?>">
                    <input value="b2_card_out" name="page" type="hidden">
                    <input value="1" name="down" type="hidden">
                    <div class="cmb2-wrap form-table">
                        <div id="cmb2-metabox-b2_card_out_options_page" class="cmb2-metabox cmb-field-list">
                            <div class="cmb-row cmb-type-text cmb2-id-card-code-start table-layout" data-fieldtype="text">
                                <div class="cmb-th">
                                    <label for="card_code_start"><?php echo __('开始ID','b2'); ?></label>
                                </div>
                                <div class="cmb-td">
                                    <input type="number" class="regular-text" name="card_code_start" id="card_code_start" value="<?php echo $start; ?>" data-hash="4v2m9fovo9a0" pattern="\d*">
                                    <p class="cmb2-metabox-description"><?php echo __('从哪个ID开始导出','b2'); ?></p>
                                </div>
                            </div>
                            <div class="cmb-row cmb-type-text cmb2-id-card-code-end table-layout" data-fieldtype="text">
                                <div class="cmb-th">
                                    <label for="card_code_end"><?php echo __('结束ID','b2'); ?></label>
                                </div>
                                <div class="cmb-td">
                                    <input type="number" class="regular-text" name="card_code_end" id="card_code_end" value="<?php echo $end; ?>" data-hash="6p22bsr877b0" pattern="\d*">
                                    <p class="cmb2-metabox-description"><?php echo __('从哪个ID结束导出','b2'); ?></p>
                                </div>
                            </div>
                            <div class="cmb-row cmb-type-text cmb2-id-card-code-end table-layout" data-fieldtype="text">
                                <div class="cmb-th">
                                    <label for="card_code_end"><?php echo __('使用状态','b2'); ?></label>
                                </div>
                                <div class="cmb-td">
                                    <select class="cmb2_select" name="card_code_status" id="user_can_invitation" data-hash="4pniose4b3d0">
                                        <option value="0">未使用</option>
                                        <option value="1" selected="selected">已使用</option>
                                        <option value="2">全部</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="submit"><input type="submit" name="submit-cmb" id="submit-cmb" class="button button-primary" value="<?php echo __('导出','b2'); ?>"></p>
                </form>
            </div>
        </div>
    <?php
    }

    public function bulid_message_cb($cmb, $args){
        if ( ! empty( $args['should_notify'] )) {
            add_settings_error( $args['setting'], $args['code'],sprintf(__( '卡密生成成功， 请前往%s查看。', 'b2' ),'<a href="'.admin_url('/admin.php?page=b2_card_list').'">卡密列表</a>') , 'updated' );
        }
    }

    public function create_guid(){

        $guid = '';
        $uid = uniqid ( "", true );

        $data = AUTH_KEY;
        $data .= $_SERVER ['REQUEST_TIME'];     // 请求那一刻的时间戳
        $data .= $_SERVER ['HTTP_USER_AGENT'];  // 获取访问者在用什么操作系统
        $data .= $_SERVER ['SERVER_ADDR'];      // 服务器IP
        $data .= $_SERVER ['SERVER_PORT'];      // 端口号
        $data .= $_SERVER ['REMOTE_ADDR'];      // 远程IP
        $data .= $_SERVER ['REMOTE_PORT'];      // 端口信息

        $hash = strtoupper ( hash ( 'ripemd128', $uid . $guid . md5 ( $data ) ) );

        $guid = substr ( $hash, 0, 4 ) . '-' . substr ( $hash, 8, 4 ) . '-' . substr ( $hash, 12, 4 ) . '-' . substr ( $hash, 16, 4 ) . '-' . substr ( $hash, 20, 4 );

        return $guid;
    }

    public function save_action( $cmb2_no_override_option_save, $this_options, $instance ) {
        if(!apply_filters('b2_check_role',0)) return;
        if(isset($this_options['card_code_count']) && isset($this_options['card_money'])){
            
            $current_user = get_current_user_id();

            global $wpdb;
            $table_name = $wpdb->prefix . 'zrz_card';
    
            for ($i=0; $i < (int)$this_options['card_code_count']; $i++) {
                $key = $this->create_guid();
        
                $res = $wpdb->insert($table_name, array(
                    'card_key'=> $key,
                    'card_value'=> wp_create_nonce(Corders::build_order_no()),
                    'card_rmb'=> $this_options['card_money'],
                    'card_status'=> 0,
                    'card_user'=> 0
                ) );
            }
        }
    }

    public function list_options_page(){
        $list = new_cmb2_box(array(
            'id'           => 'b2_card_list_options_page',
            'tab_title'    => __('卡密管理','b2'), 
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_card_list',
            'parent_slug'     => '/admin.php?page=b2_card_main',
            'tab_group'    => 'b2_card_options',
            'display_cb'      => array($this,'list_option_page_cb'),
            'save_button'     => false,
        ));
    }

    public static function cb_options_page_tabs( $cmb_options ) {
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

    public function list_option_page_cb($cmb_options){
        if(!apply_filters('b2_check_role',0)) return;
        $tabs = self::cb_options_page_tabs( $cmb_options );
        $card_code = new CardListTable();
        $card_code->prepare_items();
        $status = isset($_GET["card_status"]) ? esc_sql($_GET["card_status"]) : 'all';

        if((isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete') || (isset($_REQUEST['action2']) && $_REQUEST['action2'] == 'delete')){
                
            $order_ids = isset($_REQUEST['id']) ? (array)$_REQUEST['id'] : '';
            if($order_ids){
                $card_code->delete_coupons($order_ids);

                $ref_url = wp_get_referer();
                $ref_url = remove_query_arg(array('id', 'action','action2','s'), $ref_url);
                exit(header("Location: ".$ref_url));
            }
        }
        ?>
        <div class="wrap cmb2-options-page option-<?php echo $cmb_options->option_key; ?>">
            <?php if ( get_admin_page_title() ) : ?>
                <h2><?php echo wp_kses_post( get_admin_page_title() ); ?></h2>
            <?php endif; ?>
            <h2 class="nav-tab-wrapper">
                <?php foreach ( $tabs as $option_key => $tab_title ) : ?>
                    <a class="nav-tab<?php if ( isset( $_GET['page'] ) && $option_key === $_GET['page'] ) : ?> nav-tab-active<?php endif; ?>" href="<?php menu_page_url( $option_key ); ?>"><?php echo wp_kses_post( $tab_title ); ?></a>
                <?php endforeach; ?>
            </h2>
            <div class="wrap">
                <ul class="subsubsub">
                    <li class="all"><a href="<?php echo admin_url('/admin.php?page=b2_card_list&card_status=all'); ?>" class="<?php echo $status === 'all' ? 'current' : ''; ?>"><?php echo __('所有','b2'); ?><span class="count">（<?php echo $card_code->get_status_count('all'); ?>）</span></a> |</li>
                    <li class="mine"><a href="<?php echo admin_url('/admin.php?page=b2_card_list&card_status=0'); ?>" class="<?php echo $status === '0' ? 'current' : ''; ?>"><?php echo __('未使用','b2'); ?><span class="count">（<?php echo $card_code->get_status_count(0); ?>）</span></a> |</li>
                    <li class="publish"><a href="<?php echo admin_url('/admin.php?page=b2_card_list&card_status=1'); ?>" class="<?php echo $status === '1' ? 'current' : ''; ?>"><?php echo __('使用','b2'); ?><span class="count">（<?php echo $card_code->get_status_count(1); ?>）</span></a></li>
                </ul>
                <div id="icon-users" class="icon32"><br/></div>  
                <form id="coupon-filter" method="get">
                    <?php
                        $card_code->search_box( __('搜索卡密','b2'), 'search_id' );
                    ?>
                    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />

                    <?php $card_code->display() ?>
                </form>
            </div>
        </div>
        <?php
    }
}