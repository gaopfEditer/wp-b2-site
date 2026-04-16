<?php namespace B2\Modules\Common;

class Task{

    public function init(){
        add_filter('b2_task_arg', array($this,'task_filter'));
        add_filter('b2_task_user_arg', array($this,'task_user_filter'));
    }

    public function task_filter($arg){
        if((int)b2_get_option('normal_write','write_allow') === 0){
            unset($arg['task_post']);
        }

        return $arg;
    }

    public function task_user_filter($arg){
        $wx_pc_open = b2_get_option('normal_login','wx_pc_open');
        $wx_gz_open = b2_get_option('normal_login','wx_gz_open');
        $qq_open = b2_get_option('normal_login','qq_open');
        $weibo_open = b2_get_option('normal_login','weibo_open');

        if((int)$weibo_open === 0){
            unset($arg['task_user_weibo']);
        }

        if((int)$qq_open === 0){
            unset($arg['task_user_qq']);
        }

        if((int)$wx_gz_open === 0 && b2_is_weixin()){
            unset($arg['task_user_weixin']);
        }

        if((int)$wx_pc_open === 0 && !b2_is_weixin()){
            unset($arg['task_user_weixin']);
        }

        if((int)b2_get_option('verify_main','verify_allow') === 0){
            unset($arg['task_user_verify']);
        }

        return $arg;
    }

    public static function get_random_post_url(){
        $posts = get_posts ( array( 'numberposts' => 1, 'post_type'=>'post','orderby' => 'rand' ));

        if(!empty($posts)){
            $post = $posts[0];
        
            return get_permalink($post->ID);
        }
    }

    public static function get_task_data($user_id = 0){

        $user_id = $user_id ? $user_id : b2_get_current_user_id();
        if(!$user_id) return array('error'=>__('请先登录','b2'));

        $current_user_task = self::current_user_task($user_id);

        $task = b2_task_arg();

        $credit = str_replace('-','~',b2_get_option('normal_gold','credit_qd'));

        // if(get_user_meta($user_id,'zrz_vip',true)){
        //     $credit = '50~100';
        // }

        $task['task_mission'] = array(
            'name'=>__( '签到', 'b2' ),
			'times'=>1,
			'credit'=>$credit,
			'icon'=>'b2-qiandao-kaoqindaqia',
            'url'=>'javascript:void(0)',
        );

        $task = apply_filters('b2_task_day', $task);

        $task_user = b2_task_user_arg();
        $task_user = apply_filters('b2_task_always', $task_user);

        foreach($task_user as $k=>$v){
            if($k !== 'task_user_verify'){
                $task_user[$k]['url'] = get_author_posts_url($user_id).'/settings';
            }

            $task_user[$k]['finish'] = isset($current_user_task['always'][$k]['finish']) ? $current_user_task['always'][$k]['finish'] : 0;

            if((int)$v['times'] === 0) unset($task_user[$k]);
        }

        foreach ($task as $k => $v) {
            $task[$k]['finish'] = isset($current_user_task['task']['data'][$k]) ? $current_user_task['task']['data'][$k] : 0;
            if((int)$v['times'] === 0) unset($task[$k]);
        }

        return array(
            'task'=>$task,
            'task_user'=>$task_user
        );
    }

    public static function current_user_task($user_id){
        $task = get_user_meta($user_id,'b2_task',true);
        $task = !empty($task) ? $task : array();

        $task_always = get_user_meta($user_id,'b2_task_always',true);
        $task_always = !empty($task_always) ? $task_always : array();

        //检查过期时间
        if(empty($task) || (isset($task['time']) && $task['time'] < current_time('Y-m-d'))){
            $task['time'] = current_time('Y-m-d');
            $task['data'] = array();
            update_user_meta($user_id,'b2_task',$task);
            wp_cache_delete('b2_user_'.$user_id,'b2_user_custom_data');
        }

        $task['data']['task_mission'] = get_user_meta($user_id,'b2_mission_credit',true) ? 1 : 0;

        //检查新手任务是否已经完成
        $task_always['task_user_qq']['finish'] = get_user_meta($user_id,'zrz_qq_uid',true) ? 1 : 0;
        $task_always['task_user_weixin']['finish'] = get_user_meta($user_id,'zrz_weixin_uid',true) ? 1 : 0;
        $task_always['task_user_weibo']['finish'] = get_user_meta($user_id,'zrz_weibo_uid',true) ? 1 : 0;
        $task_always['task_user_verify']['finish'] = get_user_meta($user_id,'b2_title',true) ? 1 : 0;
        
        return array(
            'task'=>$task,
            'always'=>$task_always
        );
    }

    public static function update_task($user_id,$type){
        $task = self::current_user_task($user_id);
        $task = $task['task'];

        //如果关闭了此任务，或者此任务次数为零，则不限制积分奖励
        $_task = b2_task_arg();
        if(strpos($type,'task_user') === false){
            if(!isset($_task[$type]) || (isset($_task[$type]) && (int)$_task[$type]['times'] === 0)) return true;
        }
        
        $task_user = b2_task_user_arg();
        if(strpos($type,'task_user') !== false){
            if(!isset($task_user[$type]) || (isset($task_user[$type]) && (int)$task_user[$type]['times'] === 0)) return true;
        }

        if(!isset($task['data'][$type])){
            $task['data'][$type] = 1;
        }else{
            $times = b2_get_option('normal_task',$type);
            if((int)$task['data'][$type] >= $times){
                return false;
            }
            $task['data'][$type] = (int)$task['data'][$type] + 1; 
        }

        update_user_meta($user_id,'b2_task',$task);
        wp_cache_delete('b2_user_'.$user_id,'b2_user_custom_data');
        return true;
    }

    public static function user_task_finish($user_id){
        $data = self::get_task_data($user_id);
        $task = $data['task'];

        $total = 0;
        $finish = 0;
        foreach ($task as $k => $v) {
            $total += (int)$v['times'];
            $finish += (int)$v['finish'];
        }

        return array(
            'total'=>$total,
            'finish'=>$finish
        );
    }
}