<?php namespace B2\Modules\Common;
use B2\Modules\Common\User;

class Cpay{

    // edited by fuzqing
    public static function getCpayInfo($data)
    {
        $id = (int)$data['post_id'];
        $info = b2_is_enable_related_pay_money($id);
        $info['active_time'] = b2_get_cpay_active_time($id);
        return $info;
    }

    public static function get_pay_resout($data){

        $id = (int)$data['id'];

        $user_id = b2_get_current_user_id();

        $allow = get_post_meta($id,'b2_pay_user_list',true);

        if($allow == 2 || $allow == 4){
            $allow = self::user_has_buy($user_id,$id);
        }elseif($allow == 3){
            $allow = $user_id;
        }

        if(user_can($user_id, 'administrator' )){
            $allow = 1;
        }

        if(!$allow) return [
            'allow'=>$allow,
            'data'=>[]
        ];

        if(get_post_type($id) != 'cpay') return ['error'=>__('参数错误','b2')];

        $paged = isset($data['paged']) ? (int)$data['paged'] : 1;
        $count = isset($data['count']) ? (int)$data['count'] : 20;

        $count = $count > 200 ? 20 : $count;

        $offset = ($paged-1)*$count;

        global $wpdb;
        $table_name = $wpdb->prefix . 'zrz_order';

        $where = $wpdb->prepare("
            SELECT * FROM $table_name
            WHERE post_id = %d AND order_type = %s AND order_state = %s ORDER BY order_date DESC LIMIT %d,%d
            ",
            $id,'custom','q',$offset,$count
        );

        $t_where = $wpdb->prepare("
            SELECT count(*) FROM $table_name
            WHERE post_id = %d AND order_type = %s AND order_state = %s
            ",
            $id,'custom','q'
        );

        if(get_post_meta($id,'b2_pay_user_list',true)== 4){
            $where = $wpdb->prepare("
                SELECT * FROM $table_name
                WHERE post_id = %d AND order_type = %s AND order_state = %s AND user_id = %d ORDER BY order_date DESC LIMIT %d,%d
                ",
                $id,'custom','q',$user_id,$offset,$count
            );

            $t_where = $wpdb->prepare("
                SELECT count(*) FROM $table_name
                WHERE post_id = %d AND order_type = %s AND order_state = %s AND user_id = %d
                ",
                $id,'custom','q',$user_id
            );
        }

        $res = $wpdb->get_results($where,ARRAY_A);

        if(empty($res)) return [
            'allow'=>$allow,
            'data'=>[]
        ];

        $total = $wpdb->get_var($t_where);

        $_data = [];

        $opts = self::get_form_data($id);

        foreach ($res as $k => $v) {
            $value = json_decode(wp_unslash($v['order_value']),true);

            $value = self::form_str_to_obj($value,$opts,$id);

            unset($value['price']);

            $_data[] = [
                'user'=>User::get_user_public_data($v['user_id'],true),
                'price'=>$v['order_price'],
                'data'=>$value,
                'cpay'=>[
                    'title'=>get_the_title($v['post_id']),
                    'link'=>get_permalink($v['post_id']),
                    'id'=>$v['post_id']
                ],
                'post'=>$v['order_key'] ? [
                    'title'=>get_the_title($v['order_key']),
                    'link'=>get_permalink($v['order_key']),
                    'id'=>$v['order_key']
                ] : []
            ];
        }

        return [
            'pages'=>ceil($total/$count),
            'allow'=>$allow,
            'data'=>$_data
        ];
    }

    public static function form_str_to_obj($value,$opts,$id){
        foreach ($opts as $_k => $_v) {
            if(!$_v['show_list']) {
                unset($value[$_v['key']]);
                continue;
            }
            foreach ($value as $ck => $cv) {
                if($_k == $ck){
                    if($_v['type'] == 'radio' || $_v['type'] == 'select'){
                        if(isset($_v['value'][$cv])){
                            $value[$ck] = [
                                'name'=>$_v['name'],
                                'value'=>$_v['value'][$cv],
                                'type'=>$_v['type']
                            ];
                        }
                    }else if($_v['type'] == 'checkbox'){

                        if(is_array($cv)){
                            foreach ($cv as $vv => $vk) {
                                if(isset($_v['value'][$vk]) && isset($value[$ck]['value'][$vv])){
                                    $value[$ck]['value'][$vv] = $_v['value'][$vk];
                                    unset( $value[$ck][$vv]);
                                }
                            }
                        }else{
                            if(isset($_v['value']) && isset($value[$ck]['value'])){
                                $value[$ck]['value'] = $_v['value'];
                            }
                        }

                        if(isset($value[$ck]['name']) && isset($_v['name'])){
                            $value[$ck]['name'] = $_v['name'];
                        }

                        if(isset($value[$ck]['type']) && isset($_v['type'])){
                            $value[$ck]['type'] = $_v['type'];
                        }


                    }else{

                        if(is_array($cv)){
                            foreach ($cv as $cvk => $cvv) {
                                if(isset($cvv['id']) && $cvv['type'] == 'image'){
                                    $cv[$cvk]['url'] = wp_get_attachment_url($cvv['id']);
                                    $cv[$cvk]['thumb'] = b2_get_thumb(array('thumb'=>$cv[$cvk]['url'],'width'=>200,'height'=>150));
                                }
                            }
                        }

                        $value[$ck] = [
                            'name'=>$_v['name'],
                            'value'=>$cv,
                            'type'=>$_v['type']
                        ];
                    }
                }
            }
        }

        return apply_filters( 'b2_cpay_form_to_obj', $value,$value,$id );
    }

    public static function get_form_data($id){
        $opts = get_post_meta($id,'b2_pay_custom_group',true);

        $data = [];

        foreach ($opts as $k => $v) {
            if($v['type'] == 'radio' || $v['type'] == 'checkbox' || $v['type'] == 'select'){
                $str = trim($v['value'], " \t\n\r\0\x0B\xC2\xA0");
                if($str){
                    $str = explode(PHP_EOL, $str );
                    $arr = array();
                    foreach($str as $_k=>$_v){
                        $__k = explode('=',$_v);
                        $arr[$__k[0]] = $__k[1];
                    }

                    $data[$v['key']] = $v;
                    $data[$v['key']]['value'] = $arr;
                }

            }else{
                $data[$v['key']] = $v;
            }
        }

        return $data;

    }

    public static function user_has_buy($user_id,$cpay_id){
        global $wpdb;
        $table_name = $wpdb->prefix . 'zrz_order';

        $res = $wpdb->get_row(
            $wpdb->prepare("
                SELECT * FROM $table_name
                WHERE user_id = %d AND post_id = %d AND order_type = %s AND order_state = %s ORDER BY order_date DESC LIMIT %d
                ",
                $user_id,$cpay_id,'custom','q',1
            )
        , ARRAY_A);

        return $res ? 1 : 0;
    }
}
