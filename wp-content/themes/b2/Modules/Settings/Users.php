<?php namespace B2\Modules\Settings;

use B2\Modules\Common\User;

class Users{

    public function init(){
        add_filter( 'manage_users_columns', array($this,'modify_user_table' ),10,1);
        add_filter( 'manage_users_custom_column', array($this,'modify_user_table_row'), 10, 3 );

        //用户页面metabox
        add_action( 'cmb2_admin_init', array($this,'register_user_profile_metabox' ),10,1);
        //add_action( 'admin_post_b2_user', array($this,'save_profile_form'), 10, 3 );
        

        add_action ( 'personal_options_update', array( $this, 'save_profile_form' ),10,1);
        add_action( 'edit_user_profile_update', array( $this, 'save_profile_form' ));

        
        add_action('restrict_manage_users', array($this,'add_course_section_filter'));
        
        
        add_filter('pre_get_users', array($this,'filter_users_by_course_section'));

        add_filter( 'manage_users_sortable_columns', array($this,'user_column_sortable') );
        
        add_filter('manage_users_columns', [$this,'add_user_additional_column']);  
        add_action('manage_users_custom_column',  [$this,'show_user_additional_column_content'], 10, 3);  
    }

    // 添加额外的栏目  
    public function add_user_additional_column($columns) {  
        // $columns['user_nickname'] = '昵称';  
        // $columns['user_url'] = '网站';  
        // $columns['reg_time'] = '注册时间';  
        // $columns['signup_ip'] = '注册IP';  
        // $columns['last_login'] = '上次登录';  
        // $columns['user_phone'] = '手机号码';  
        // 打算将注册IP和注册时间、登录IP和登录时间合并显示，所以我注销下面两行  
        /*$columns['signup_ip'] = '注册IP';*/  
        $columns['last_login_ip'] = '最后登录IP';  
        unset($columns['name']);//移除“姓名”这一栏，如果你需要保留，删除这行即可  
        return $columns;  
    }  
    //显示栏目的内容  

    public function show_user_additional_column_content($value, $column_name, $user_id) {  
        $user = get_userdata( $user_id );  
        // 输出“手机号码”  
        // if ( 'user_phone' == $column_name )  
        //     return $user->phone; 
        //输出“昵称”  
        // if ( 'user_nickname' == $column_name )  
        //     return $user->nickname;  
        // 输出用户的网站  
        // if ( 'user_url' == $column_name )  
        //     return '<a href="'.$user->user_url.'" target="_blank">'.$user->user_url.'</a>';  
        // 输出注册时间和注册IP  
        if('reg_time' == $column_name ){  
            return get_date_from_gmt($user->user_registered) ;  
        }  
         // 输出注册时间和注册IP  
        if('signup' == $column_name ){  
            return get_user_meta( $user->ID, 'signup_ip', true);  
        }  
        // 输出最近登录时间和登录IP  
        // if ( 'last_login' == $column_name && $user->last_login ){  
        //     return get_user_meta( $user->ID, 'last_login', true );  
        // }  
    
        // 输出最近登录时间和登录IP  
        if ( 'last_login_ip' == $column_name ){  
            return get_user_meta( $user->ID, 'last_login_ip', true );  
        }  
        return $value;  
    }  

    function user_column_sortable( $columns ) {
        return wp_parse_args( array( 
            'id'=>'id',
            'registration_date' => 'registered',
            'a_username' => 'display_name',
            'user_lv' => 'user_lv',
            'user_lv_end'=>'user_lv_end'
         ), $columns );
    }

    public function b2_column_orderby($vars){
        if ( isset( $vars['orderby'] ) && 'zrz_lv' == $vars['orderby'] ) {
            $vars = array_merge( $vars, array(
                'meta_key' => 'zrz_vip',
                'orderby' => 'meta_value'
            ) );
        }

        if ( isset( $vars['orderby'] ) && 'a_username' == $vars['orderby'] ) {
            $vars = array_merge( $vars, array(
                'meta_key' => 'a_username',
                'orderby' => 'meta_value'
            ) );
        }
        return $vars;
    }

    public function add_course_section_filter() {

        $settings_vip = User::get_user_roles();

        $arr = array();

        if($settings_vip){
            foreach ($settings_vip as $k => $v) {
                if(strpos($k,'vip') !== false){
                    $arr[$k] = $v['name'];
                }
            }
        }

        if ( isset( $_REQUEST[ 'course_section' ]) ) {
            $section = $_REQUEST[ 'course_section' ];
            $section = !empty( $section[ 0 ] ) ? $section[ 0 ] : $section[ 1 ];
        } else {
            $section = -1;
        }
        echo ' <select name="course_section[]" style="float:none;margin-left:14px"><option value="">选择等级</option>';
        
        foreach ($arr as $k => $v) {
            $selected = $k == $section ? ' selected="selected"' : '';
            echo '<option value="' . $k . '"' . $selected . '>' . $v . '</option>';
        }
            
        echo '</select>';
        echo '<input type="submit" class="button" value="会员筛选">';
    }

    public function filter_users_by_course_section($query)
        {
            global $pagenow;
            if (is_admin() && 'users.php' == $pagenow) {
                $section = isset($_REQUEST[ 'course_section']) ? $_REQUEST[ 'course_section'][0] : '';

                if ($section) {
                    $meta_query = [['key' => 'zrz_vip','value' => $section, 'compare' => 'LIKE']];
                    $query->set('meta_key', 'zrz_vip');
                    $query->set('meta_query', $meta_query);
                }
            }
        }

    public function modify_user_table( $columns ) {
        unset( $columns['name'] ); // maybe you would like to remove default columns
        $columns['id'] = 'id';
        $columns['registration_date'] = __('注册日期','b2'); // add new
        $columns['a_username'] = __('昵称','b2');
        $columns['user_lv'] = __('会员','b2');
        $columns['user_lv_end'] = __('会员过期时间','b2');
        return $columns;
    
    }

    public function modify_user_table_row($row_output, $column_id_attr, $user ){

        switch ( $column_id_attr ) {
            case 'id':
                return $user;
                break;
            case 'registration_date' :
                return get_date_from_gmt(get_the_author_meta( 'registered', $user ),'Y-m-d H:i:s' );
                break;
            case 'a_username' :
                return '<a href="'.get_author_posts_url($user).'">'.get_the_author_meta('display_name', $user).'</a>';
                break;
            case 'user_lv' :
                $lv = User::get_user_lv($user);

                return $lv['vip']['icon'].' '.$lv['lv']['icon'];
            break;
            case 'user_lv_end':
                $vip_time = get_user_meta( $user,'zrz_vip_time',true);
                if(isset($vip_time['end'])){
                    if((int)$vip_time['end'] === 0){
                        return __('永不过期','b2');
                    }else{
                        return wp_date("Y-m-d H:i:s",$vip_time['end']);
                    }
                }

                return 'none';
            break;
            default:
            break;
        }
    
        return $row_output;
    }

    public function save_profile_form($user_id){

        if(!current_user_can('administrator')) return;
        
        $credit = (int)get_user_meta($user_id,'zrz_credit_total',true);
        if(isset($_REQUEST['zrz_credit_total']) && $credit !== (int)$_REQUEST['zrz_credit_total']){
            $credit = (int)$_REQUEST['zrz_credit_total'] - $credit;

            \B2\Modules\Common\Message::update_data([
                'date'=>current_time('mysql'),
                'from'=>0,
                'to'=>$user_id,
                'post_id'=>0,
                'msg'=>sprintf(__('管理员对您的积分进行了变更：${gold_page}，变更原因：%s','b2'),$_REQUEST['zrz_rmb_change_why'] ? $_REQUEST['zrz_rmb_change_why'] : __('未注明','b2')),
                'type'=>'credit_change',
                'type_text'=>__('积分变更','b2')
            ]);

            \B2\Modules\Common\Gold::update_data([
                'date'=>current_time('mysql'),
                'to'=>$user_id,
                'gold_type'=>0,
                'post_id'=>0,
                'no'=>$credit,
                'msg'=>sprintf(__('管理员对您的积分进行了变更：${gold_page}，变更原因：%s','b2'),$_REQUEST['zrz_rmb_change_why'] ? $_REQUEST['zrz_rmb_change_why'] : __('未注明','b2')),
                'type'=>'credit_change',
                'type_text'=>__('积分变更','b2')
            ]);

            //积分记录
            // \B2\Modules\Common\Message::add_message(array(
            //     'user_id'=>$user_id,
            //     'msg_type'=>14,
            //     'msg_read'=>0,
            //     'msg_date'=>current_time('mysql'),
            //     'msg_users'=>0,
            //     'msg_credit'=>$credit,
            //     'msg_credit_total'=>(int)$_REQUEST['zrz_credit_total'],
            //     'msg_key'=>'',
            //     'msg_value'=>$_REQUEST['zrz_rmb_change_why']
            // ));
        }

        $money = (float)get_user_meta($user_id,'zrz_rmb',true);

        if(isset($_REQUEST['zrz_rmb']) && $money != (float)$_REQUEST['zrz_rmb']){
            $money = (float)$_REQUEST['zrz_rmb'] - $money;

            $money_name = b2_get_option('normal_main','money_name');

            \B2\Modules\Common\Message::update_data([
                'date'=>current_time('mysql'),
                'from'=>0,
                'to'=>$user_id,
                'post_id'=>0,
                'msg'=>sprintf(__('管理员对您的%s进行了变更，变更原因：%s','b2'),$money_name,$_REQUEST['zrz_rmb_change_why'] ? $_REQUEST['zrz_rmb_change_why'] : __('未注明','b2')),
                'type'=>'credit_change',
                'type_text'=>sprintf(__('%s变更','b2'),$money_name)
            ]);

            \B2\Modules\Common\Gold::update_data([
                'date'=>current_time('mysql'),
                'to'=>$user_id,
                'gold_type'=>1,
                'post_id'=>0,
                'no'=>$money,
                'msg'=>sprintf(__('管理员对您的%s进行了变更，变更原因：%s','b2'),$money_name,$_REQUEST['zrz_rmb_change_why'] ? $_REQUEST['zrz_rmb_change_why'] : __('未注明','b2')),
                'type'=>'credit_change',
                'type_text'=>sprintf(__('%s变更','b2'),$money_name)
            ]);

            //金额记录
            // \B2\Modules\Common\Message::add_message(array(
            //     'user_id'=>$user_id,
            //     'msg_type'=>37,
            //     'msg_read'=>0,
            //     'msg_date'=>current_time('mysql'),
            //     'msg_users'=>0,
            //     'msg_credit'=>$money,
            //     'msg_credit_total'=>(float)$_REQUEST['zrz_rmb'],
            //     'msg_key'=>'',
            //     'msg_value'=>$_REQUEST['zrz_rmb_change_why']
            // ));
        }

        if(isset($_REQUEST['zrz_vip'])){
            $lv = get_user_meta($user_id,'zrz_vip',true);
            $lv = $lv ? $lv : false;

            $count = get_option('b2_vip_count');
            $count = is_array($count) ? $count : array();

            if($lv && $_REQUEST['zrz_vip'] !== $lv){
                if(isset($count[$lv])){
                    $count[$lv]--;
                }

                if(isset($count[ $_REQUEST['zrz_vip']])){
                    $count[$_REQUEST['zrz_vip']]++;
                }
                delete_user_meta($user_id,'b2_download_count');
            }

            update_option('b2_vip_count',$count);
            
            // $vip_time = get_user_meta($user_id,'zrz_vip_time',true);
            // if(isset($vip_time['end']) && isset($_REQUEST['zrz_vip_end_time']) && (int)$vip_time['end'] !== (int)$_REQUEST['zrz_vip_end_time']){
            //     $vip_time['end'] = $_REQUEST['zrz_vip_end_time'];

            //     update_user_meta($user_id,'zrz_vip_time',true);
            // }
        }

        if(isset($_REQUEST['zrz_lv'])){
            $lv = get_user_meta($user_id,'zrz_lv',true);
            $lv = $lv ? $lv : false;

            if($_REQUEST['zrz_lv'] !== $lv){
                delete_user_meta($user_id,'b2_download_count');
            }
        }

        if(isset($_REQUEST['b2_dark_room']) && $_REQUEST['b2_dark_room']){
            
            $days = get_user_meta($user_id,'b2_dark_room_days',true);

            if($_REQUEST['b2_dark_room_days'] != $days){
                User::put_dark_room($user_id,$_REQUEST['b2_dark_room_days'],$_REQUEST['b2_dark_room_why']);
            }

            //记录更新时间
            update_user_meta($user_id,'b2_user_update_date',current_time('mysql'));
        }

        if(isset($_REQUEST['zrz_vip_end_time']) && $_REQUEST['zrz_vip_end_time'] !== ''){

            if($_REQUEST['zrz_vip']){
                $vip_time = get_user_meta($user_id,'zrz_vip_time',true);
                $vip_time = is_array($vip_time) ? $vip_time : array(
                    'start'=> wp_strtotime(current_time( 'mysql' )),
                    'end'=>''
                );


                // var_dump($vip_time);

                // var_dump(strtotime($_REQUEST['zrz_vip_end_time']));

                //if($vip_time['end'] === strtotime($_REQUEST['zrz_vip_end_time'])) return;

                if($_REQUEST['zrz_vip_end_time'] && $_REQUEST['zrz_vip_end_time'] != '9999-01-01'){

                    $vip_time['end'] = wp_strtotime($_REQUEST['zrz_vip_end_time']);
                }else{
                    $vip_time['end'] = 0;
                }

             
                update_user_meta($user_id,'zrz_vip_time',$vip_time);
            }
            
        }

        wp_cache_delete('b2_user_'.$user_id,'b2_user_custom_data');
        wp_cache_delete('b2_user_'.$user_id,'b2_user_data');

    }

    public function register_user_profile_metabox(){
        $user = new_cmb2_box( array(
            'id'               => 'b2_user_page',
            'title'            => __( '用户页面', 'b2' ), // Doesn't output for user boxes
            'option_key'      => 'b2_user',
            'object_types'     => array( 'user' ), // Tells CMB2 to use user_meta vs post_meta
            'show_names'       => true,
            'new_user_section' => 'add-new-user', // where form will show on new user page. 'add-existing-user' is only other valid option.
        ) );

        $user->add_field( array(
            'name'             => __( '用户积分', 'b2' ),
            'id'               => 'zrz_credit_total',
            'type'             => 'input',
            'desc'=>__('积分变更以后，用户登录以后或者访问此用户个人主页会重新计算等级','b2')
        ) );

        $user->add_field( array(
            'name'             => sprintf(__( '用户%s', 'b2' ),B2_MONEY_NAME),
            'id'               => 'zrz_rmb',
            'type'             => 'text_money',
            'sanitization_cb' => 'b2_sanitize_number',
            'before_field' => B2_MONEY_SYMBOL
        ) );

        $user->add_field( array(
            'name'             => sprintf(__( '积分或%s变更原因', 'b2' ),B2_MONEY_NAME),
            'id'               => 'zrz_rmb_change_why',
            'type'             => 'textarea_small',
            'desc'=>sprintf(__('当用户的积分或%s有变更的时候，可以在此备注原因，用户的通知或财富中将会显示变更原因','b2'),B2_MONEY_NAME)
        ) );

        $lvs = \B2\Modules\Common\User::get_user_roles();
        
        $lv = array();
        $vip = array();

        foreach ($lvs as $k => $v) {
            if(strpos($k,'vip') !== false){
                $vip[$k] = $v['name']; 
            }

            if(strpos($k,'lv') !== false){
                $lv[$k] = $v['name'];
            }
        }

        $user->add_field( array(
            'name'             => __( '普通等级', 'b2' ),
            'id'               => 'zrz_lv',
            'type'             => 'select',
            'show_option_none' => true,
            'options'          =>  $lv,
            'desc'=>__('等级会根据用户当前的积分进行计算，如果您设置的等级小于此用户当前积分所在的等级，将不会生效','b2')
        ) );

        $user->add_field( array(
            'name'             => __( 'VIP等级', 'b2' ),
            'id'               => 'zrz_vip',
            'type'             => 'select',
            'show_option_none' => true,
            'options'          => $vip,
        ) );

        $end_time = '';
        
        $user_id = get_current_user_id();

        if(isset($_REQUEST['user_id'])){
            $user_id = $_REQUEST['user_id'];
        }
        $vip_time = get_user_meta($user_id,'zrz_vip_time',true);

        if(isset($vip_time['end'])){
            if((int)$vip_time['end'] === 0){
                $end_time = '9999-01-01';
            }else{
                $end_time = wp_date("Y-m-d",$vip_time['end']);
            }
        }

        $user->add_field( array(
            'name'             => __( 'VIP过期时间', 'b2' ),
            'id'               => 'zrz_vip_end_time',
            'type'             => 'text_date_timestamp',
            'default'=>$end_time ? $end_time : '',
            'save_field' => false,
            'date_format' => 'Y-m-d',
            'desc'=>__('请填写会员的过期时间，如果永不过期，请填写0或者9999-01-01')
        ) );

        // $user_id = isset($_REQUEST['user_id']) ? (int)$_REQUEST['user_id'] : 0;

        // $vip_time = get_user_meta($user_id,'zrz_vip_time',true);

        // $user->add_field( array(
        //     'before_row'=>'<div id="vip-end-time">',
        //     'name'             => __( '自定义会员到期时间', 'b2' ),
        //     'id'               => 'zrz_vip_end_time',
        //     'type'             => 'text',
        //     'default'=>isset($vip_time['end']) ? $vip_time['end'] : 0,
        //     'desc'=>sprintf(__('会员到期时间为 Unix timestamp 格式，请将时间%s转换为 Unix timestamp 时间戳格式%s后填入此处，如果此会员永久有效，请直接填0。'),'<a href="https://tool.chinaz.com/tools/unixtime.aspx" target="_blank">','</a>'),
        //     'after_row'=>'</div>',
        // ) );

        $user->add_field( array(
            'name'             => __( '用户是否为分销员', 'b2' ),
            'id'               => 'b2_distribution',
            'type'             => 'select',
            'options'          => array(
                0=>__('不是分销员','b2'),
                1=>__('是分销员','b2')
            ),
            'default'=>0,
            'desc'=>__('当设置用户为分销员，开启分销的情况下，用户会有自己的专属分销连接和分销管理页面')
        ) );

        $user->add_field( array(
            'name'             => __( '小黑屋', 'b2' ),
            'id'               => 'b2_dark_room',
            'type'             => 'select',
            'options'          => array(
                0=>__('自由人','b2'),
                1=>__('关进小黑屋')
            ),
            'default'=>0
        ) );

        $user->add_field( array(
            'before_row'=>'<div class="set-hidden" id="dark-room">',
            'name'             => __( '关进小黑屋的原因', 'b2' ),
            'id'               => 'b2_dark_room_why',
            'type'             => 'textarea',
            'desc'=>__('关进小黑屋的原因。'),
        ) );

        $user->add_field( array(
            'name'             => __( '关进小黑屋天数', 'b2' ),
            'id'               => 'b2_dark_room_days',
            'type'             => 'input',
            'default'=>'',
            'desc'=>__('关进小黑屋之后多少天成为自由人。如果设置成0，将永久关进小黑屋。'),
            'after_row'=>'</div>',
        ) );

    }
}