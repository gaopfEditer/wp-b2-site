<?php
namespace B2\Modules\Settings;

use B2\Modules\Settings\InvitationTable;

class Invitation
{

    //默认设置项
    public static $default_settings = array(
        'required' => 0,
        'invitation_text' => '获取邀请码|http://#',
        'user_can_invitation' => 1,
        'user_invitation_code_count' => 3,
        'user_invitation_value' => 100
    );

    public function init()
    {
        add_action('cmb2_admin_init', array($this, 'invitation_options_page'));
        add_action('cmb2_admin_init', array($this, 'bulid_options_page'));
        add_action('cmb2_admin_init', array($this, 'list_options_page'));
        add_action('cmb2_override_option_save_b2_invitation_bulid', array($this, 'save_action'), 10, 3);
        add_action( 'admin_init', array($this,'down_csv'));
    }

    public function down_csv(){
        
        if(!apply_filters('b2_check_role',0)) return;

        // $result = $_REQUEST['params'];

        // $start = $result['start'];
        // $end = $result['end'];
        // $order_state = $result['order_state'];
        // $order_type = $result['order_type'];

        if(!isset($_REQUEST['invitation_out'])) return;
 
        $start = isset($_REQUEST['outStart']) && $_REQUEST['outStart'] ? $_REQUEST['outStart'] : '';
        $end = isset($_REQUEST['outEnd']) && $_REQUEST['outEnd'] ? $_REQUEST['outEnd'] : '';

        global $wpdb; 
        $table_name = $wpdb->prefix . 'zrz_card';

        $query = "SELECT * FROM $table_name";


        if($start && $end){
            $w = 'WHERE';
            if(strpos($query,'WHERE') !== false){
                $w = 'AND';
            }
            $query.= $wpdb->prepare(" $w `id` >= %s AND `id` <= %s",$start,$end);
        }

        $arg = $wpdb->get_results($query,ARRAY_A);

        if(empty($arg)){
            wp_die('没有数据');
        }

        set_time_limit(0);
        $csv = new Array2Csv();
        $filename = 'inv_'.$start.'_to_'.$end.'.csv';
        $csv->cvsHeader($filename);
    
        $head =  ['id','key','value','金额','状态','使用者'];
        $csv->outputData($head);
    
        $limit = 10000; 
        $cnt   = 0;    

        foreach ($arg as $k => $v) {

            $author_obj = get_userdata($v['card_user']);
            $data = [
                $v['id'],
                $v['card_key'],
                $v['card_value'],
                $v['card_rmb'],
                $v['card_status'] == 0 ? '未使用' : '已使用',
                str_replace(',','，',(isset($author_obj->display_name) ? $author_obj->display_name : '无').'(id:'.$v['card_user'].')')
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

    /**
     * 获取默认设置项
     *
     * @param string $key 数组键值
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function get_default_settings($key)
    {

        $arr = array();

        if ($key == 'all') {
            return $arr;
        }

        if (isset($arr[$key])) {
            return $arr[$key];
        }
    }

    public function save_action($cmb2_no_override_option_save, $this_options, $instance)
    {
        if (isset($this_options['invitation_code_count']) && isset($this_options['invitation_credit'])) {

            if (isset($this_options['invitation_owner']) && (int) $this_options['invitation_owner'] !== 0) {
                $current_user = (int) $this_options['invitation_owner'];
            } else {
                $current_user = get_current_user_id();
            }

            global $wpdb;
            $table_name = $wpdb->prefix . 'zrz_invitation';

            for ($i = 0; $i < (int) $this_options['invitation_code_count']; $i++) {
                $code = self::create_guid(true);

                $res = $wpdb->insert($table_name, array(
                    'invitation_nub' => $code,
                    'invitation_owner' => $current_user,
                    'invitation_status' => 0,
                    'invitation_user' => 0,
                    'invitation_credit' => (int) $this_options['invitation_credit'],
                ));
            }
        }
    }

    public function invitation_options_page()
    {
        //常规设置
        $invitation = new_cmb2_box(
            array(
                'id' => 'b2_invitation_main_options_page',
                'object_types' => array('options-page'),
                'option_key' => 'b2_invitation_main',
                'tab_group' => 'b2_invitation_options',
                'parent_slug' => 'b2_main_options',
                'tab_title' => __('综合设置', 'b2'),
                'menu_title' => __('邀请管理', 'b2'),
            ));

        $invitation->add_field(
            array(
                'before_row' => '<p>您可以在页面中使用邀请码列表短代码，来显示某些邀请码给用户查看：<code>[zrz_inv start=61 end=200]</code>，其中start 和 end 后面跟的数字，是邀请码的ID，这句短代码意思是说显示ID为61到200之间的所有邀请码。</p>',
                'name' => __('注册时邀请码是否必填', 'b2'),
                'id' => 'required',
                'type' => 'select',
                'options' => array(
                    2 => __('必填', 'b2'),
                    1 => __('选填', 'b2'),
                    0 => __('不使用邀请码', 'b2'),
                ),
                'default' => self::$default_settings['required'],
            ));

        $invitation->add_field(
            array(
                'name' => __('邀请码获取链接', 'b2'),
                'id' => 'invitation_text',
                'desc' => sprintf(
                    __('此项内容显示在注册窗口，邀请码填写窗口格式如下：%s。%s比如：%s', 'b2'),
                    '<code>文字描述|链接</code>',
                    '<br />',
                    '<code>获取邀请码|https://www.xxx.com/buy-invitation</code>'
                ),
                'type' => 'text',
                'default' => self::$default_settings['invitation_text'],
            ));

        $invitation->add_field(
            array(
                'name' => __('是否给注册用户发放邀请码', 'b2'),
                'id' => 'user_can_invitation',
                'desc' => __('前期站点推广的时候极为有效，建议开启', 'b2'),
                'type' => 'select',
                'options' => array(
                    1 => __('是', 'b2'),
                    0 => __('否', 'b2'),
                ),
                'default' => self::$default_settings['user_can_invitation'],
            ));

        $invitation->add_field(
            array(
                'name' => __('给注册用户发放邀请码的数量', 'b2'),
                'id' => 'user_invitation_code_count',
                'desc' => __('不要太多，不然用户不知道珍惜，默认3个', 'b2'),
                'type' => 'text',
                'default' => self::$default_settings['user_invitation_code_count'],
                'attributes' => array(
                    'type' => 'number',
                    'pattern' => '\d*',
                ),
            ));

        $invitation->add_field(
            array(
                'name' => __('发放邀请码的奖励值', 'b2'),
                'id' => 'user_invitation_value',
                'desc' => __('给用户发放邀请码的奖励值', 'b2'),
                'type' => 'text',
                'default' => self::$default_settings['user_invitation_value'],
                'attributes' => array(
                    'type' => 'number',
                    'pattern' => '\d*',
                ),
            ));
    }

    public function bulid_options_page()
    {
        $bulid = new_cmb2_box(
            array(
                'id' => 'b2_invitation_bulid_options_page',
                'tab_title' => __('邀请码生成', 'b2'),
                'object_types' => array('options-page'),
                'option_key' => 'b2_invitation_bulid',
                'parent_slug' => '/admin.php?page=b2_invitation_main',
                'tab_group' => 'b2_invitation_options',
                'save_button' => __('生成', 'b2'),
                'message_cb' => array($this, 'bulid_message_cb'),
            )
        );

        $bulid->add_field(
            array(
                'name' => __('生成邀请码的数量', 'b2'),
                'id' => 'invitation_code_count',
                'desc' => __('不要一次生成太多，以免造成服务器阻塞，建议少于500个', 'b2'),
                'type' => 'text',
                'default' => 100,
                'attributes' => array(
                    'type' => 'number',
                    'pattern' => '\d*',
                ),
            ));

        $bulid->add_field(
            array(
                'name' => __('奖励的积分', 'b2'),
                'id' => 'invitation_credit',
                'type' => 'text',
                'default' => 100,
                'attributes' => array(
                    'type' => 'number',
                    'pattern' => '\d*',
                ),
                'desc' => __('用户通过邀请码注册之后会得到的积分奖励（奖励给通过邀请码注册的人）', 'b2'),
            ));

        $bulid->add_field(
            array(
                'name' => __('邀请码创建人', 'b2'),
                'id' => 'invitation_owner',
                'type' => 'text',
                'default' => 0,
                'attributes' => array(
                    'type' => 'number',
                    'pattern' => '\d*',
                ),
                'desc' => __('如果需要指定邀请码创建人，请填写创建人的用户ID，邀请码生成成功之后会进入该创建人的邀请码列表中。如果创建人是当前管理员，请直接填0', 'b2'),
            ));
    }

    public function bulid_message_cb($cmb, $args)
    {
        if (!empty($args['should_notify'])) {
            add_settings_error($args['setting'], $args['code'], sprintf(__('邀请码生成成功， 请前往%s查看。', 'b2'), '<a href="' . admin_url('/admin.php?page=b2_invitation_list') . '">邀请码列表</a>'), 'updated');
        }
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

    public function list_options_page()
    {
        $list = new_cmb2_box(
            array(
                'id' => 'b2_invitation_list_options_page',
                'tab_title' => __('邀请码管理', 'b2'),
                'object_types' => array('options-page'),
                'option_key' => 'b2_invitation_list',
                'parent_slug' => '/admin.php?page=b2_invitation_main',
                'tab_group' => 'b2_invitation_options',
                'display_cb' => array($this, 'list_option_page_cb'),
                'save_button' => false,
            )
        );
    }

    static function cb_options_page_tabs($cmb_options)
    {
        $tab_group = $cmb_options->cmb->prop('tab_group');
        $tabs = array();
        foreach (\CMB2_Boxes::get_all() as $cmb_id => $cmb) {
            if ($tab_group === $cmb->prop('tab_group')) {
                $tabs[$cmb->options_page_keys()[0]] = $cmb->prop('tab_title')
                    ? $cmb->prop('tab_title')
                    : $cmb->prop('title');
            }
        }
        return $tabs;
    }

    public function list_option_page_cb($cmb_options)
    {
        $tabs = $this->cb_options_page_tabs($cmb_options);
        $invitation_code = new InvitationTable();
        $invitation_code->prepare_items();
        $status = isset($_GET["invitation_status"]) ? esc_sql($_GET["invitation_status"]) : 'all';
        $ref_url = admin_url('admin.php?'.$_SERVER['QUERY_STRING']);
        if ((isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete') || (isset($_REQUEST['action2']) && $_REQUEST['action2'] == 'delete')) {

            $order_ids = isset($_REQUEST['id']) ? (array) $_REQUEST['id'] : '';
           
            if ($order_ids) {
                $invitation_code->delete_coupons($order_ids);
                $ref_url = wp_get_referer();
                $ref_url = remove_query_arg(array('id', 'action', 'action2', 's'), $ref_url);
                
                exit(header("Location: " . $ref_url));
                //echo '<script> location.replace("'.$ref_url.'"); </script>';
            }
        }
        ?>
        <div class="wrap cmb2-options-page option-<?php echo $cmb_options->option_key; ?>">
            
            <?php if (get_admin_page_title()): ?>
                <h2>
                    <?php echo wp_kses_post(get_admin_page_title()); ?>
                </h2>
            <?php endif; ?>
            <h2 class="nav-tab-wrapper">
                <?php foreach ($tabs as $option_key => $tab_title): ?>
                    <a class="nav-tab<?php if (isset($_GET['page']) && $option_key === $_GET['page']): ?> nav-tab-active<?php endif; ?>"
                        href="<?php menu_page_url($option_key); ?>"><?php echo wp_kses_post($tab_title); ?></a>
                <?php endforeach; ?>
            </h2>
            <div class="order-box" id="inv-fliter-box">
                <div class="box">
                    <div class="order-title">导出邀请码</div>
                    <div class="fliter-box">
                        <div><span>开始ID</span><input type="number" name="out_start" v-model="outStart"/></div>
                        <div><span>结束ID</span><input type="number" name="out_end" v-model="outEnd"/></div>
                        <div><a class="button action" :href="'<?php echo add_query_arg('invitation_out','1',$ref_url); ?>&outStart='+outStart+'&outEnd='+outEnd">导出</a></div>
                    </div>
                </div>
            </div>
            <div class="wrap">
                <ul class="subsubsub">
                    <li class="all"><a
                            href="<?php echo admin_url('/admin.php?page=b2_invitation_list&invitation_status=all'); ?>"
                            class="<?php echo $status === 'all' ? 'current' : ''; ?>"><?php echo __('所有', 'b2'); ?><span
                                class="count">（
                                <?php echo $invitation_code->get_status_count('all'); ?>）
                            </span></a> |</li>
                    <li class="mine"><a
                            href="<?php echo admin_url('/admin.php?page=b2_invitation_list&invitation_status=0'); ?>"
                            class="<?php echo $status === '0' ? 'current' : ''; ?>"><?php echo __('未使用', 'b2'); ?><span
                                class="count">（
                                <?php echo $invitation_code->get_status_count(0); ?>）
                            </span></a> |</li>
                    <li class="publish"><a
                            href="<?php echo admin_url('/admin.php?page=b2_invitation_list&invitation_status=1'); ?>"
                            class="<?php echo $status === '1' ? 'current' : ''; ?>"><?php echo __('使用', 'b2'); ?><span
                                class="count">（
                                <?php echo $invitation_code->get_status_count(1); ?>）
                            </span></a></li>
                </ul>
                <div id="icon-users" class="icon32"><br /></div>
                <form id="coupon-filter" method="get">
                    <?php
                    $invitation_code->search_box(__('搜索邀请码', 'b2'), 'search_id');
                    ?>
                    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />

                    <?php $invitation_code->display() ?>
                </form>
                <?php

                ?>
            </div>
        </div>
        <?php
    }
}