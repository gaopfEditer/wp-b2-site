<?php namespace B2\Modules\Common;
use B2\Modules\Common\User;
use B2\Modules\Common\Credit;

/*
* 积分余额与通知
* $type 是积分类型，用数字表示，对应 zrz_message 表中的 type ,目前最大91项。含义如下 ：
* 
* 后面标注1的是已经实装的通知
* 
* 4 新注册用户增加积分和通知 1 a
* 46 邀请注册奖励积分（邀请） 1
*
* 1 如果当前评论有父级，给父级评论作者通知（评论）1 a
* 2 给评论者增加积分（评论）1 a
* 3 文章被回复，给文章作者通知（评论）1 a
* 8 不喜欢某个评论，给评论作者通知（评论）1 a
* 10 喜欢某个评论，给评论作者通知（评论）1 a
*
* 11 关注了某人，给某人通知（关注）1 a
* 15 取消关注某人，给某人通知（关注）1 a
* 42 关注了某人，给自己增加积分（关注）1 a
* 43 取消关注了某人，给自己减掉的积分（关注）1 a 
* 
* 12 私信通知（私信）1
* 13 私信内容（私信）
*
* 14 管理员给某人变更了积分，通知某人（用户）1 a
* 37 管理员给某人变更了余额，通知某人（用户）1 a
* 16 签到的通知（用户）1 a
* 61 签到填坑（用户）1 a
*
* 17 发表帖子通知（bbpress）
* 18 帖子回复通知（bbpress）
* 19 给帖子的作者通知（bbpress）
* 20 帖子回复时提到某人，给这个人通知(bbpress)
* 
* 47 视频出售后给文章作者通知（余额）1 a
* 48 视频购买后给购买者通知（余额）1 a
* 49 视频出售后给文章作者通知（积分）1 a
* 50 视频购买后给购买者通知（积分）1 a
* 51 资源下载后给文章作者通知（余额）1 a
* 52 资源下载后给购买这通知（余额）1 a
* 53 资源下载后给文章作者通知（积分）1 a
* 54 资源下载后给购买这通知（积分）1 a
* 55 卡密充值（余额）1 a
* 57 余额充值（除卡密）1 a
* 58 vip购买（余额）1
* 
* 59 认证付款（余额）
*
* 60 认证积分奖励（积分）
* 
* 5 发表文章（文章）
* 6 文章被点赞，给文章作者通知（文章）1 a
* 7 文章被取消点赞，给文章作者通知（文章）1 a
* 21 打赏人减掉金额时通知（文章）(余额) a
* 22 被打赏人增加金额时通知（文章）(余额) a
* 25 文章被删除时发出通知（文章）
* 31 付费文章购买通知（文章）（余额）1 a
* 32 付费文章出售通知（文章）（余额）1 a
* 33 积分文章购买通知（文章）(积分) 1 a
* 34 积分文章出售通知（文章）（积分）1 a
*
* 36 发表研究（研究）
*
* 23 有人申请了有情链接，给管理员通知（友情链接）
* 24 发表了冒泡，给冒泡作者通知（冒泡）
* 26 冒泡被点赞，给冒泡作者通知（冒泡）
* 27 冒泡被取消点赞，给冒泡作者通知（冒泡）
*
* 28 积分购买（商城）
* 29 积分抽奖（商城）
* 30 购买（商城）
* 64 优惠劵消息（商城）
* 63 余额购买（余额，购买者）
* 62 购买赠送积分（商城）
* 38 使用余额购买积分（余额）1 a
* 56 购买积分通知（积分）1 a
*
* 39 邀请别人成功，给自己增加积分
* 40 被邀请人增加积分
*
* 41 提现申请
*
* 44 报名通知（活动）
* 45 给报名的人减掉积分（活动）
*
* 65 发布快讯（积分）
*
* 66 一级分销奖励（余额）
* 67 二级分销奖励（余额）
* 68 三级分销奖励（余额）
* 69 分销减掉余额（余额）
*
* 70 入圈付费，购买者（余额）
* 71 入圈付费，圈主（余额）
*
* 72 圈子提问扣除积分（积分）
* 73 圈子提问扣除余额（余额）
* 
* 74 圈子邀请回答问题（圈子消息）
*
* 75 偷瞄答案,答主（积分）
* 76 偷瞄答案，答主（余额）
*
* 77 偷瞄答案，偷瞄者（积分）
* 78 偷瞄答案，偷瞄者（余额）
* 
* 79 问题被采纳（积分）
* 80 问题被采纳（余额）
* 
* 81 过期分红（积分）
* 82 过期分红（余额）
* 
* 83 没有回答，返还积分
* 84 没有回答，返还到余额
* 
* 85 付费查看帖子，作者（积分）
* 86 付费查看帖子，作者（余额）
* 
* 87 付费查看帖子，购买者（积分）
* 88 付费查看帖子，购买者（余额）

* 89 关小黑屋（消息）
* 90 自定义支付（余额）
* 91 信息置顶（余额）
* 92 发帖奖励（余额）
* 93 发帖奖励通知
*/

class Message{

    public function init(){

        //出售者通知
        add_filter('b2_order_notify_return',array($this,'order_notify_return'),2, 1);

        //购买者通知（余额）
        add_filter('b2_balance_pay_after',array($this,'balance_pay_after_message'),5, 2);

        //购买者通知（积分）
        add_filter('b2_credit_pay_after',array($this,'credit_pay_after_message'),5, 2);

    }

    public static function get_count($arg){

        $where = '';

        global $wpdb;

        if(isset($arg['to']) && $arg['to'] !== ''){
            $where .= $wpdb->prepare(' AND `to`=%d',$arg['to']);
        }

        if(isset($arg['type']) && $arg['type']){
            $where .= $wpdb->prepare(' AND `type`=%s',$arg['type']);
        }

        if(isset($arg['post_id']) && $arg['post_id'] !== ''){
            $where .= $wpdb->prepare(' AND `post_id`=%d',$arg['post_id']);
        }

        if(isset($arg['read']) && $arg['read'] !== ''){
            $where .= $wpdb->prepare(' AND `read`=%d',$arg['read']);
        }

        if(!$where) return 0;

        $where = substr($where,4);

        global $wpdb;
        $table_name = $wpdb->prefix . 'b2_msg';

        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE $where");
        
        return apply_filters('b2_message_get_count',(int)$count,$arg);
    }

    public static function update_data($new_data){
       
        global $wpdb;
        $table_name = $wpdb->prefix . 'b2_msg';

        if(!isset($new_data['count'])){
            $new_data['count'] = 1;
        }

        if(!isset($new_data['read'])){
            $new_data['read'] = 0;
        }

        if(isset($new_data['from'])){
            $new_data['from'] = [$new_data['from']];
        }else{
            $new_data['from'] = [];
        }

        $arr = array(
            'id'=>'%d',
            'from'=>'%s',
            'date'=>'%s',
            'count'=>'%d',
            'to'=>'%d',
            'msg'=>'%s',
            'type'=>'%s',
            'type_text'=>'%s',
            'post_id'=>'%d',
            'read'=>'%d'
        );

        $format_new_data = array();
        foreach ($new_data as $k => $v) {
            if(isset($arr[$k])){
                $format_new_data[] = $arr[$k];
            }
        }

        $msg = false;

        if(isset($new_data['to']) && isset($new_data['post_id']) && isset($new_data['type']) && isset($new_data['old_row']) && $new_data['from'][0] !== 0){

            $where = [
                'to'=>$new_data['to'],
                'post_id'=>$new_data['post_id'],
                'type'=>$new_data['type'],
                'read'=>0
            ];

            $msg = self::get_data($where);
            
            if(count($msg['data']) > 0){
                $msg = $msg['data'][0];
                $msg['from'] = maybe_unserialize($msg['from']);

                $msg['from'] = array_diff($msg['from'],$new_data['from']);
                if(isset($msg['from'][0])){
                    $new_data['from'][1] = $msg['from'][0];
                }
                if(isset($msg['from'][1])){
                    $new_data['from'][2] = $msg['from'][1];
                }
                
                $new_data['count'] = $msg['count'] + 1;
            }else{
                $msg = false;
            }
        }

        unset($new_data['old_row']);

        $new_data['from'] = maybe_serialize($new_data['from']);

        if(isset($new_data['to'])){
            delete_user_meta($new_data['to'],'b2_user_unread_msg');
        }

        if(!$msg){

            if($wpdb->insert($table_name,$new_data,$format_new_data)){

                do_action('b2_message_update_data_insert',$new_data);
                return true;
            }
            return false;

        }else{
            
            $format_where = array();
            foreach ($where as $k => $v) {
                $format_where[] = $arr[$k];
            }

            if($wpdb->update( 
                $table_name,
                $new_data,
                $where,
                $format_new_data,
                $format_where
            )){
                do_action('b2_message_update_data_update',$new_data);
                return true;
            }
            return false;

        }

    }

    public static function get_msg_list($arg){

        $user_id = b2_get_current_user_id();

        if(!$user_id) return ['error'=>__('请先登录','b2')];

        if(!user_can($user_id, 'administrator' )){
            $arg['to'] = $user_id;
        }elseif(!isset($arg['to']) || !$arg['to']){
            $arg['to'] = $user_id;
        }

        $arg['pre_count'] = isset($arg['count']) ? $arg['count'] : 20;

        unset($arg['count']);

        $arg['show_pages'] = 1;

        $list = self::get_data($arg);

        // if(empty($list['data'])) return [];

        foreach ($list['data'] as $k => $v) {
            $list['data'][$k]['from'] = maybe_unserialize($list['data'][$k]['from']);

            foreach ($list['data'][$k]['from'] as $_k => $_v) {
                if(is_numeric($_v) && (int)$_v !== 0){
                    $list['data'][$k]['from'][$_k] = User::get_user_normal_data($_v);
                }elseif((int)$_v === 0){
                    $list['data'][$k]['from'][$_k] = [
                        'avatar'=>B2_DEFAULT_AVATAR,
                        'id'=>0,
                        'name'=>__('游客','b2'),
                        'link'=>''
                    ];
                }else{
                    $list['data'][$k]['from'][$_k] = [
                        'avatar'=>B2_DEFAULT_AVATAR,
                        'id'=>-1,
                        'name'=>$_v,
                        'link'=>''
                    ];
                }
            }

            if($list['data'][$k]['post_id']){
                if($list['data'][$k]['type'] == 'comment_down' || $list['data'][$k]['type'] == 'comment_up'){
                    $comment = get_comment($list['data'][$k]['post_id']);
                    if(isset($comment->comment_post_ID)){
                        $list['data'][$k]['post'] = [
                            'title'=>get_the_title($comment->comment_post_ID),
                            'link'=>get_permalink($comment->comment_post_ID),
                            'post_type'=>get_post_type($comment->comment_post_ID)
                        ];
                    }
                }elseif($list['data'][$k]['type'] == 'author_circle_join'){
                    $term = get_term($list['data'][$k]['post_id'],'circle_tags');
                    $circle_slug = b2_get_option('normal_custom','custom_circle_link');
                    $list['data'][$k]['post'] = [
                        'title'=>$term->name,
                        'link'=>B2_HOME_URI.'/'.$circle_slug.'/'.$term->slug,
                        'post_type'=>'circle_tags'
                    ];
                }else{
                    $list['data'][$k]['post'] = [
                        'title'=>get_the_title($list['data'][$k]['post_id']),
                        'link'=>get_permalink($list['data'][$k]['post_id']),
                        'post_type'=>get_post_type($list['data'][$k]['post_id'])
                    ];
                }

                // $list['data'][$k]['post_type'] = get_post_type($list['data'][$k]['post_id']);
            }
            $date = date_create($list['data'][$k]['date']);
            $list['data'][$k]['date'] = [
                'day'=>date_format($date,"Y-m-d"),
                'time'=>date_format($date,"H:i")
            ];
        }

        $list['unread'] = self::get_user_unread_msg_count($arg['to']);

        if($list['unread'] > 0 && isset($arg['read_clean']) && $user_id == $arg['to']){
            self::msg_unread_clean($user_id);
        }

        return apply_filters('b2_message_get_msg_list',$list,$arg);
    }

    public static function msg_unread_clean($user_id){
        global $wpdb;
        $table_name = $wpdb->prefix . 'b2_msg';

        $wpdb->update(
            $table_name,
            array( 
                'read' => 1,
            ), 
            array( 'read' => 0,'to'=> $user_id),
            array( 
                '%d'
            ), 
            array( '%d','%d' ) 
        );

        delete_user_meta($user_id,'b2_user_unread_msg');
    }

    public static function get_user_unread_msg_count($user_id){

        $unread = get_user_meta($user_id,'b2_user_unread_msg',true);
        if($unread !== '') return $unread;

        $count = self::get_count(['to'=>$user_id,'read'=>0]);

        update_user_meta($user_id,'b2_user_unread_msg',$count);

        return $count;
        
    }

    public static function get_data($arg){

        $where = '';

        global $wpdb;

        if(isset($arg['id']) && $arg['id']){
            $where .= $wpdb->prepare(' AND `id`="%d"',$arg['id']);
        }

        if(isset($arg['from']) && $arg['from'] !== ''){
            $where .= $wpdb->prepare(' AND `from`=%s',$arg['from']);
        }

        if(isset($arg['to']) && $arg['to'] !== ''){
            $where .= $wpdb->prepare(' AND `to`=%d',$arg['to']);
        }

        if(isset($arg['type']) && $arg['type'] !== ''){
            $where .= $wpdb->prepare(' AND `type`=%s',$arg['type']);
        }

        if(isset($arg['post_id']) && $arg['post_id'] !== ''){
            $where .= $wpdb->prepare(' AND `post_id`=%d',$arg['post_id']);
        }

        if(isset($arg['read']) && $arg['read'] !== ''){
            $where .= $wpdb->prepare(' AND `read`=%d',$arg['read']);
        }

        if(isset($arg['pre_count'])){

            $offset = '';

            if(isset($arg['paged']) && $arg['paged']){
                $offset = (((int)$arg['paged'] - 1) * $arg['pre_count']).',';
            }

            $arg['pre_count'] = (int)$arg['pre_count'];
            if($arg['pre_count'] > 50) $arg['pre_count'] = 1;
            $where .= ' ORDER BY date DESC LIMIT '.$offset.''.$arg['pre_count'];
        }else{
            $where .= ' ORDER BY date DESC LIMIT 1';
        }

        $pages = 0;

        if(!$where) return [
            'pages'=>$pages,
            'data'=>[]
        ];

        $where = substr($where,4);

        $table_name = $wpdb->prefix . 'b2_msg';

        $res = $wpdb->get_results(
            "SELECT * FROM $table_name WHERE $where "
        ,ARRAY_A);

        if(isset($arg['show_pages']) && $arg['show_pages'] && isset($arg['pre_count'])){

            $index = strpos($where," ORDER BY");
            $where = substr_replace($where,"",$index);

            $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE $where");

            $pages = ceil($count/$arg['pre_count']);
        }

        return [
            'pages'=>$pages,
            'data'=>$res
        ];
    }
    
    //获取消息
    public static function get_user_message($user_id,$type,$paged){
        $user_id = (int)$user_id;

        $_user_id = (int)b2_get_current_user_id();

        if(!$_user_id || !$user_id){
            return array('error'=>__('请先登录','b2'));
        }

        if(!$type || !$paged) return array('error'=>__('参数错误','b2'));

        if($user_id !== $_user_id && !user_can($_user_id, 'administrator' )) return array('error'=>__('权限不足','b2'));

        $_user_id = $user_id;

        global $wpdb;
        $table_name = $wpdb->prefix . 'zrz_message';

        $number = 20;
        $offset = ($paged-1)*$number;

        $credit = apply_filters('b2_message_type_credit',array(4,46,1,2,3,5,8,10,11,15,42,43,14,16,49,50,53,54,6,7,33,34,56,60,61,62,28,29,65,72,75,79,81,83,85,87,92));

        $money = apply_filters('b2_message_type_money',array(37,47,48,51,52,21,22,31,32,55,38,57,58,59,63,64,66,67,68,69,41,70,71,73,76,78,80,82,84,86,88,90,91,92,93));

        if($type === 'credit'){
            $a = $credit;
            $orderby = 'ORDER BY `msg_date` DESC';
        }

        if($type === 'money'){
            $a = $money;
            $orderby = 'ORDER BY `msg_date` DESC';
        }

        if($type === 'all'){
            $a = array_merge($money,$credit);
            $a = array_diff($a, array(2,42,43,16,50,54,31,33,41,48,52,32,34,21,55,56,38,57,58,59,60,62,63,64,28,29,65,66,67,68,69,70,72,73,75,76,78,81,82,83,84,85,86,87,88,90,91,92));

            $a = array_merge($a,array(74,89));
            $orderby = 'ORDER BY `msg_read`,`msg_date` DESC';
        }

        $a = implode("','",$a);

        // $a = array_map(function($v) {
        //     return "'" . esc_sql($v) . "'";
        // }, $a);
        // $a = implode(',', $a);

        $and = '';
        
        if($type !== 'all'){
            $and = "AND msg_credit != 0";
        }

        //获取消息数据
        $data = $wpdb->get_results(
            $wpdb->prepare("
                SELECT * FROM $table_name
                WHERE user_id = %d AND msg_type IN ('".$a."') $and $orderby LIMIT %d,%d
                ",
                $_user_id,$offset,$number
            )
        ,ARRAY_A);

        $total = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(msg_id) FROM $table_name
            WHERE user_id = %d AND msg_type IN ('".$a."') $and
            ",
            $_user_id
        ));

        $read_count = 0;
        if($type === 'all'){
            $read_count = $wpdb->get_var($wpdb->prepare("
                SELECT COUNT(msg_id) FROM $table_name
                WHERE user_id = %d AND msg_type IN ('".$a."') AND msg_read=%d
                ",
                $_user_id,0
            ));

            $wpdb->update(
                $table_name,
                array( 
                    'msg_read' => 1,
                ), 
                array( 'msg_read' => 0,'user_id'=> $_user_id),
                array( 
                    '%d'
                ), 
                array( '%d','%d' ) 
            );
        }

        return array(
            'pages'=>ceil($total/$number),
            'data'=>self::order_data_map($data),
            'read_count'=>$read_count
        );
    }

    public static function order_data_map($data){
        if(empty($data)) return array();

        $_data = array();
        foreach ($data as $k => $v) {
            $title = array();

            switch ($v['msg_type']) {
                case 59:
                    $title = array(
                        'name'=>__('认证费用','b2'),
                        'link'=>b2_get_custom_page_url('verify')
                    );
                    break;
                case 61:
                    $title = array(
                        'name'=>__('签到填坑','b2'),
                        'link'=>b2_get_custom_page_url('verify')
                    );
                    break;
                case 64:
                    $title = array(
                        'name'=>__('优惠劵消费','b2'),
                        'link'=>''
                    );
                    break;
                case 89:
                    $days = (int)get_user_meta($v['user_id'],'b2_dark_room_days',true);
                    $title = array(
                        'name'=>$days === 0 ? __('永久关进小黑屋','b2') : sprintf(__('关进小黑屋%s天','b2'),$days),
                        'link'=>b2_get_custom_page_url('dark-room')
                    );
                    break;
                case 70:
                case 71:
                    $title = array(
                        'name'=>get_term( (int)$v['msg_key'] )->name,
                        'link'=>get_term_link((int)$v['msg_key'])
                    );
                    break;
                case 91:
                    $title = array(
                        'name'=>__('置顶','b2'),
                        'link'=>get_term_link((int)$v['msg_key'])
                    );
                    break;
                case 92:
                    $title = array(
                        'name'=>__('奖励','b2'),
                        'link'=>get_term_link((int)$v['msg_key'])
                    );
                    break;
                default:
                    $link = is_numeric($v['msg_key']) ? get_permalink($v['msg_key']) : 'javascript:void(0)';
                    $_title = get_the_title($v['msg_key']);
                    if(!$_title){
                        $_title = b2_get_excerpt($v['msg_key'],50);
                    }
                    $title = array(
                        'name'=>$_title,
                        'link'=>$link
                    );
                    break;
            }

            $_data[] = array(
                'type'=>$v['msg_type'],
                'users'=>self::get_users($v['msg_users']),
                'title'=>$title,
                'number'=>$v['msg_credit'],
                'total'=>$v['msg_credit_total'],
                'date'=>b2_timeago($v['msg_date']),
                '_date'=>$v['msg_date'],
                'content'=>self::msg_value($v['msg_value'],$v['msg_type']),
                'read'=>$v['msg_read'],
                'value'=>$v['msg_value']
            );
        }

        return $_data;
    }

    public static function msg_value($value,$type){
        switch($type){
            case '1':
            case '2':
            case '3':
            case '8':
            case '10':
                return Comment::get_comment_content($value);
            case '66':
            case '67':
            case '68':
            case '69':
            case '70':
            case '71':
            case '91':
                $arg = explode('/',$value);
                $text = '';
                switch($arg[0]){
                    case 'gx':
                        $text = __('商品购买','b2');
                    break;
                    case 'w':
                        $text = __('购买隐藏内容','b2');
                    break;
                    case 'x':
                        $text = __('购买下载内容','b2');
                    break;
                    case 'v':
                        $text = __('视频购买','b2');
                    break;
                    case 'vip':
                        $text = __('会员购买','b2');
                    break;
                    case 'cg':
                        $text = __('积分购买','b2');
                    break;
                    case 'verify':
                        $text = __('购买认证服务','b2');
                        break;
                    case 'circle_join':
                        $text = __('付费入圈','b2');
                        break;
                    case 'infomation_sticky':
                        $text = sprintf(__('%s置顶','b2'),b2_get_option('normal_custom','custom_infomation_name'));
                    break;
                }
                
                return array(
                    'type'=>$text,
                    'money'=>isset($arg[1]) ? $arg[1] : '',
                    'ratio'=>isset($arg[2]) ? $arg[2] : ''
                );
                
            default :
                return $value;
                break;
        }
    }

    public static function get_users($users){
        $users = json_decode($users);

        $_users = array();
        if(is_array($users)){
            $users = array_reverse($users);
            $i = 1;
            foreach ($users as $k => $v) {

                if($i >= 5) break;
                if(is_numeric($v)){
                    $u = User::get_user_public_data($v,true);
                    if(!isset($u['error'])){
                        $_users[] = $u;
                    }else{
                        $_users[] = __('游客','b2');
                    }
                    $i++;
                }else{
                    $i++;
                    $_users[] = $v;
                }
                
            }
        }else{
            $_users[] = User::get_user_public_data($users,true);
        }

        return $_users;
    }

    public static function add_message($data){

        global $wpdb;
        $table_name = $wpdb->prefix . 'zrz_message';

        //检查之前是否有相同的数据
        $res = $wpdb->get_results(
            $wpdb->prepare("
                SELECT * FROM $table_name
                WHERE msg_read = %d AND user_id = %d AND msg_key = %s AND msg_type = %d AND msg_value = %s
                ",
                0,$data['user_id'],$data['msg_key'],$data['msg_type'],$data['msg_value']
            )
        ,ARRAY_A);

        if($res){
            $old = $res[0];
            $msg_users = $old['msg_users'];
            $msg_users = json_decode($msg_users,true);
            $msg_users = is_array($msg_users) ? $msg_users : array();

            if(!in_array($data['msg_users'],$msg_users)){
                $msg_users[] = $data['msg_users'];

                return $wpdb->update(
                    $table_name, 
                    array( 
                        'msg_users' => json_encode($msg_users),
                        'msg_date'=>current_time('mysql'),
                        'msg_credit'=>bcadd($old['msg_credit'],$data['msg_credit'],2),
                        'msg_credit_total'=>$data['msg_credit_total']
                    ), 
                    array( 'msg_id' => $old['msg_id']), 
                    array( 
                        '%s',
                        '%s',
                        '%s',
                        '%s'
                    ),
                    array( '%d' ) 
                );
            }else{
                return $wpdb->update(
                    $table_name, 
                    array( 
                        'msg_date'=>current_time('mysql'),
                        'msg_credit'=>bcadd($old['msg_credit'],$data['msg_credit'],2),
                        'msg_credit_total'=>$data['msg_credit_total']
                    ), 
                    array( 'msg_id' => $old['msg_id']), 
                    array( 
                        '%s',
                        '%s',
                        '%s'
                    ),
                    array( '%d' ) 
                );
            }
        }else{
            $users = array($data['msg_users']);
            $users = json_encode($users);
            $data['msg_users'] = $users;

            return $wpdb->insert(
                $table_name, 
                $data,
                array( 
                    '%d',//user_id
                    '%d',//msg_type
                    '%d',//msg_read
                    '%s',//msg_date
                    '%s',//msg_users
                    '%s',//msg_credit
                    '%s',//msg_credit_total
                    '%s',//msg_key
                    '%s'//msg_value
                )
            );
        }
    }

    //给出售者通知
    public function order_notify_return($data){

        $author_id = get_post_field('post_author', $data['post_id']);

        if($data['order_type'] === 'circle_join'){
            global $wpdb;

            $table_name = $wpdb->prefix . 'b2_circle_related';

            $res = $wpdb->get_results(
                $wpdb->prepare("
                    SELECT * FROM $table_name
                    WHERE circle_id=%d
                    AND circle_role=%s
                    ",
                    $data['post_id'],'admin'
            ),ARRAY_A);

            if(empty($res)) return $data;
            $res = $res[0];

            $author_id = $res['user_id'];
        }

        $array = array(
            'v'=>array(
                'title'=>__('视频出售'),
                'type_text'=>__('视频','b2'),
                'type'=>'author_v'
            ),
            'x'=>array(
                'title'=>__('下载资源出售'),
                'type_text'=>__('下载','b2'),
                'type'=>'author_x'
            ),
            'w'=>array(
                'title'=>__('隐藏内容出售'),
                'type_text'=>__('隐藏内容','b2'),
                'type'=>'author_w'
            ),
            'ds'=>array(),
            'circle_join'=>array(),
            'circle_hidden_content_pay'=>array(
                'title'=>__('帖子隐藏内容出售'),
                'type_text'=>__('隐藏内容','b2'),
                'type'=>'author_circle_hidden_content_pay'
            )
        );

        if(!isset($array[$data['order_type']])) return $data;
        
        if($data['order_total'] <= 0) return array('error'=>__('金额错误','b2'));

        if($data['pay_type'] === 'credit'){
            $gold_type = 0;
        }else{
            $gold_type = 1;
        }

        if($data['order_type'] == 'ds'){
            $author = is_numeric($data['user_id']) && $data['user_id'] != 0 ? get_the_author_meta('display_name', $data['user_id'] ) : __('未名游客','b2');
            $comment = wp_insert_comment( array(
                'comment_post_ID'=>$data['post_id'],
                'comment_author_email'=>'guest@guest.com',
                'comment_author'=>$author,
                'user_id'=>is_numeric($data['user_id']) ? $data['user_id'] : 0,
                'comment_content'=>'<div class="comment-ds-box">'.sprintf(__('%s给作者打赏了%s','b2'),'<span class="ds-author">'.$author.'</span>','<span class="ds-cmoney">'.B2_MONEY_SYMBOL.$data['order_total'].'</span>').'</div>'
            ));

            Gold::update_data([
                'date'=>current_time('mysql'),
                'to'=>$author_id,
                'gold_type'=>1,
                'no'=>$data['order_total'],
                'post_id'=>$data['post_id'],
                'msg'=>__('有人给您打赏：${post_id}','b2'),
                'type'=>'author_ds',
                'type_text'=>__('打赏','b2'),
            ]);
    
            Message::update_data([
                'date'=>current_time('mysql'),
                'from'=>$data['user_id'],
                'to'=>$author_id,
                'post_id'=>$data['post_id'],
                'msg'=>__('收到 ${from}的打赏：${post_id}','b2'),
                'type'=>'author_ds',
                'type_text'=>__('打赏','b2'),
                'old_row'=>1
            ]);

            Message::update_data([
                'date'=>current_time('mysql'),
                'from'=>$data['user_id'],
                'to'=>$author_id,
                'post_id'=>$data['post_id'],
                'msg'=>sprintf(__('${from}打赏后留言：%s','b2'),$data['order_content']),
                'type'=>'author_ds_content',
                'type_text'=>__('打赏留言','b2')
            ]);
            
        }elseif($data['order_type'] == 'circle_join'){

            $circle_name = b2_get_option('normal_custom','custom_circle_name');

            Gold::update_data([
                'date'=>current_time('mysql'),
                'to'=>$author_id,
                'gold_type'=>1,
                'no'=>$data['order_total'],
                'post_id'=>$data['post_id'],
                'msg'=>sprintf(__('有${count}人加入了您的%s：${post_id}','b2'),$circle_name),
                'type'=>'author_circle_join',
                'type_text'=>__('加入圈子','b2'),
                'old_row'=>1
            ]);
    
            Message::update_data([
                'date'=>current_time('mysql'),
                'from'=>$data['user_id'],
                'to'=>$author_id,
                'post_id'=>$data['post_id'],
                'msg'=>sprintf(__('${from}加入了您的%s：${post_id}','b2'),$circle_name),
                'type'=>'author_circle_join',
                'type_text'=>__('加入圈子','b2'),
                'old_row'=>1
            ]);
        }else{
            Gold::update_data([
                'date'=>current_time('mysql'),
                'to'=>$author_id,
                'gold_type'=>$gold_type,
                'no'=>$data['order_total'],
                'post_id'=>$data['post_id'],
                'msg'=>sprintf(__('有${count}人购买了您的%s：${post_id}','b2'),$array[$data['order_type']]['type_text']),
                'type'=>$array[$data['order_type']]['type'],
                'type_text'=>$array[$data['order_type']]['title'],
                'old_row'=>1
            ]);
    
            Message::update_data([
                'date'=>current_time('mysql'),
                'from'=>$data['user_id'],
                'to'=>$author_id,
                'post_id'=>$data['post_id'],
                'msg'=>sprintf(__('${from}购买了您的%s：${post_id}','b2'),$array[$data['order_type']]['type_text']),
                'type'=>$array[$data['order_type']]['type'],
                'type_text'=>$array[$data['order_type']]['title'],
                'old_row'=>1
            ]);
        }
       
        return apply_filters('b2_order_notify_return_success',$data);
    }

    /**
     * 给购买者通知
     * $data 订单数据
     * $balance 支付用户的总余额
     */
    public function balance_pay_after_message($data,$balance){

        if($data['order_type'] === 'circle_read_answer_pay') 
        return $this->circle_answer_read_after($data,$balance);

        $author_id = get_post_field('post_author', $data['post_id']);

        $circle_name = b2_get_option('normal_custom','custom_circle_name');
        $infomation_name = b2_get_option('normal_custom','custom_infomation_name');

        $array = array(
            'v'=>[
                'msg'=>__('您购买了视频：${post_id}','b2'),
                'type'=>'user_v',
                'type_text'=>__('购买视频','b2')
            ],
            'x'=>[
                'msg'=>__('您购买了下载内容：${post_id}','b2'),
                'type'=>'user_x',
                'type_text'=>__('购买下载','b2')
            ],
            'w'=>[
                'msg'=>__('您购买了隐藏内容：${post_id}','b2'),
                'type'=>'user_w',
                'type_text'=>__('购买隐藏内容','b2')
            ],
            'ds'=>[
                'msg'=>__('您打赏了作者：${post_id}','b2'),
                'type'=>'user_ds',
                'type_text'=>__('打赏','b2')
            ],
            'cg'=>[
                'msg'=>__('您购买了积分：${gold_page}','b2'),
                'type'=>'user_cg',
                'type_text'=>__('购买积分','b2')
            ],
            'vip'=>[
                'msg'=>__('您购买了会员','b2'),
                'type'=>'user_vip',
                'type_text'=>__('购买会员','b2')
            ],
            'verify'=>[
                'msg'=>__('您进行了认证','b2'),
                'type'=>'user_verify',
                'type_text'=>__('认证','b2')
            ],
            'circle_join'=>[
                'msg'=>sprintf(__('您加入了%s：${post_id}','b2'),$circle_name),
                'type'=>'user_circle_join',
                'type_text'=>sprintf(__('加入%s','b2'),$circle_name)
            ],
            'circle_hidden_content_pay'=>[
                'msg'=>__('您查看了帖子隐藏内容：${post_id}','b2'),
                'type'=>'user_circle_hidden_content_pay',
                'type_text'=>__('查看帖子隐藏内容','b2')
            ],
            'custom'=>[
                'msg'=>__('您参与了%s：${post_id}','b2'),
                'type'=>'user_custom',
                'type_text'=>__('自定义支付','b2')
            ],
            'infomation_sticky'=>[
                'msg'=>sprintf(__('您购买了%s置顶','b2'),$infomation_name),
                'type'=>'user_infomation_sticky',
                'type_text'=>sprintf(__('%s置顶','b2'),$infomation_name)
            ]
        );

        if(!isset($array[$data['order_type']])) return $data;

        if($data['order_type'] === 'cg'){
            $data['post_id'] = $data['id'];
        }

        if($data['order_type'] === 'circle_join'){
            $author_id = '';
        }

        Gold::update_data([
            'date'=>current_time('mysql'),
            'to'=>$data['user_id'],
            'gold_type'=>1,
            'post_id'=>$data['post_id'],
            'no'=>-$data['order_total'],
            'total'=>$balance,
            'msg'=>$array[$data['order_type']]['msg'],
            'type'=>$array[$data['order_type']]['type'],
            'type_text'=>$array[$data['order_type']]['type_text']
        ]);

        return apply_filters('b2_balance_pay_after_message',$data);
    }
    
    public static function no_read($type){
        switch ($type) {
            case 'cg':
            case 'mission':
            case 'gx':
            case 'd':
            case 'c':
            case 'circle_join':
            case 'circle_read_answer_pay':
            case 'circle_hidden_content_pay':
            case 'custom':
            case 'infomation_sticky':
                return 1;
            
            default:
                return 0;
        }
        return 0;
    }
    /**
     * 给购买者通知(积分)
     * $data 订单数据
     * $balance 支付用户的总余额
     */
    public function credit_pay_after_message($data,$credit){

        if($data['order_type'] === 'circle_read_answer_pay') 
        return $this->circle_answer_read_after($data,$credit);

        $author_id = '';

        if($data['post_id']){
            $author_id = get_post_field('post_author', $data['post_id']);
        }
        
        // $array = array(
        //     'v'=>50,
        //     'x'=>54,
        //     'w'=>33,
        //     'mission'=>61,
        //     'd'=>28,
        //     'c'=>29,
        //     'circle_hidden_content_pay'=>87
        // );

        $array = array(
            'v'=>[
                'msg'=>__('您购买了视频：${post_id}','b2'),
                'type'=>'user_v',
                'type_text'=>__('购买视频','b2')
            ],
            'x'=>[
                'msg'=>__('您购买了下载内容：${post_id}','b2'),
                'type'=>'user_x',
                'type_text'=>__('购买下载','b2')
            ],
            'w'=>[
                'msg'=>__('您购买了隐藏内容：${post_id}','b2'),
                'type'=>'user_w',
                'type_text'=>__('购买隐藏内容','b2')
            ],
            'mission'=>[
                'msg'=>__('您操作了签到填坑','b2'),
                'type'=>'user_mission_tk',
                'type_text'=>__('签到填坑','b2')
            ],
            'd'=>[
                'msg'=>__('您成功兑换了：${post_id}','b2'),
                'type'=>'user_d',
                'type_text'=>__('积分兑换','b2')
            ],
            'c'=>[
                'msg'=>__('您参与了抽奖：${post_id}','b2'),
                'type'=>'user_c',
                'type_text'=>__('积分抽奖','b2')
            ],
            'circle_hidden_content_pay'=>[
                'msg'=>__('您查看了帖子隐藏内容：${post_id}','b2'),
                'type'=>'user_circle_hidden_content_pay',
                'type_text'=>__('查看帖子隐藏内容','b2')
            ]
        );

        if(!isset($array[$data['order_type']])) return $data;

        Gold::update_data([
            'date'=>current_time('mysql'),
            'to'=>$data['user_id'],
            'gold_type'=>0,
            'post_id'=>$data['post_id'],
            'no'=>-$data['order_total'],
            'total'=>$credit,
            'msg'=>$array[$data['order_type']]['msg'],
            'type'=>$array[$data['order_type']]['type'],
            'type_text'=>$array[$data['order_type']]['type_text']
        ]);

        return apply_filters('b2_credit_pay_after_message',$data);
        


        // self::add_message(array(
        //     'user_id'=>$data['user_id'],
        //     'msg_type'=>$array[$data['order_type']],
        //     'msg_read'=>self::no_read($data['order_type']),
        //     'msg_date'=>current_time('mysql'),
        //     'msg_users'=>$author_id,
        //     'msg_credit'=>-$data['order_total'],
        //     'msg_credit_total'=>$credit,
        //     'msg_key'=>$data['post_id'],
        //     'msg_value'=>''
        // ));

        // return $data;

    }

    public function circle_answer_read_after($data,$credit){

        $author = get_post_field('post_author', (int)$data['post_id']);

        $gold_type = 0;
        if($data['pay_type'] !== 'credit'){
            $gold_type = 1;
        }

        Gold::update_data([
            'date'=>current_time('mysql'),
            'to'=>$data['user_id'],
            'gold_type'=>$gold_type,
            'post_id'=>$data['post_id'],
            'no'=>-$data['order_total'],
            'total'=>$credit,
            'msg'=>__('您偷瞄了别人的回答:${post_id}','b2'),
            'type'=>'user_circle_read_answer',
            'type_text'=>__('偷瞄答案','b2')
        ]);

        //给偷瞄者通知
        // self::add_message(array(
        //     'user_id'=>$data['user_id'],
        //     'msg_type'=>$data['pay_type'] === 'credit' ? 77 : 78,
        //     'msg_read'=>1,
        //     'msg_date'=>current_time('mysql'),
        //     'msg_users'=>$author,
        //     'msg_credit'=>-$data['order_total'],
        //     'msg_credit_total'=>$credit,
        //     'msg_key'=>$data['post_id'],
        //     'msg_value'=>''
        // ));

        //给查看答案权限
        PostRelationships::update_data(array(
            'type'=>'circle_buy_answer',
            'user_id'=>$data['user_id'],
            'post_id'=>$data['post_id']
        ));

        //给提问者和答主通知
        $answers = Circle::get_answer_authors($data['post_id']);

        $answers = array_merge($answers,array($author));

        $answers = array_flip($answers);
        $answers = array_flip($answers);

        if(!empty($answers)){
            $average = $data['order_total']/count($answers);
            if($average < 1) return $data;

            foreach ($answers as $v) {
                $average = intval($average);

                // if($data['pay_type'] === 'credit'){
                //     $total = Credit::credit_change($v,$average);
                // }else{
                //     $total = User::money_change($v,$average);
                // }

                self::update_data([
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
                    'gold_type'=>$gold_type,
                    'post_id'=>$data['post_id'],
                    'no'=>$average,
                    'msg'=>__('有${count}人偷瞄了您的回答:${post_id}','b2'),
                    'type'=>'author_circle_read_answer',
                    'type_text'=>__('回答被偷瞄','b2'),
                    'old_row'=>1
                ]);
                
                // self::add_message(array(
                //     'user_id'=>$v,
                //     'msg_type'=>$data['pay_type'] === 'credit' ? 75 : 76,
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

        return $data;

    }
}