<?php namespace B2\Modules\Common;

class Document{ 

    public static function document_breadcrumb($post_id = 0){
        $home = B2_HOME_URI;
        $shop = get_post_type_archive_link('document');
        $tax = '';

        $tax = get_the_terms($post_id, 'document_cat');
        $tax_links = '';
        $post_link = '';

        if($tax && $post_id){
            $tax = get_term($tax[0]->term_id, 'document_cat' );

            $term_id = $tax->term_id;

        }else{
            $term = get_queried_object();
            $term_id = isset($term->term_id) ? $term->term_id : 0;
        }

        if($term_id){
            $tax_links = get_term_parents_list($term_id,'document_cat');
            $tax_links = str_replace('>/<','><span>></span><',$tax_links);
            $tax_links = rtrim($tax_links,'/');
        }else{
            if(isset($_GET['s'])){
                $tax_links = __('搜索','b2');
            }else{
                $tax_links = __('工单中心','b2');
            }
        }

        if($post_id){
            $post_link = '<span>></span>'.get_the_title($post_id);
        }

        return '<a href="'.B2_HOME_URI.'">'.__('首页','b2').'</a><span>></span>'.'<a href="'.$shop.'">'.b2_get_option('normal_custom','custom_document_name').'</a><span>></span>'.$tax_links.$post_link;
    }

    public static function submit_request($data){

        $current_user_id = b2_get_current_user_id();

        if(!$current_user_id) return array('error'=>__('请先登录','b2'));

        $public_count = apply_filters('b2_check_repo_before',$current_user_id);
        if(isset($public_count['error'])) return $public_count;

        $censor = apply_filters('b2_text_censor', $data['content'].$data['title']);
        if(isset($censor['error'])) return $censor;

        $data['content'] = str_replace(array('{{','}}'),'',$data['content']);

        $content = sanitize_textarea_field($data['content']);

        if(!$content) return array('error'=>__('请填写工单内容','b2'));

        $data['title'] = str_replace(array('{{','}}'),'',$data['title']);

        $title = sanitize_text_field($data['title']);
        
        if(!$title) return array('error'=>__('请填写标题','b2'));

        if(!is_email($data['email'])){
            return array('error'=>__('请填写正确的邮箱地址','b2'));
        }

        $image = '';
        if((int)$data['image']){
            $img_data = wp_get_attachment_url($data['image']);
            if($img_data){
                $image = '<a href="'.$img_data.'" target="_blank"><img src="'.$img_data.'" /></a>';
            }
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'zrz_directmessage';

        $mark = '0+'.$current_user_id;

        //检查是否有未回复的工单
        $res = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table_name WHERE `mark`=%s order by id desc limit 1",$mark),
            ARRAY_A
        );

        if($res && isset($res[0]['to']) && $res[0]['to'] === '0'){
            return array('error'=>__('您有未处理的工单，请处理完毕后再提交！','b2'));
        }

        $res = $wpdb->insert($table_name, array(
            'mark'=>$mark,
            'from'=> (int)$current_user_id,
            'to'=> 0,
            'date'=> current_time('mysql'),
            'status'=> 0,
            'content'=> $content.$image,
            'key'=>$data['email'],
            'value'=>$title
        ));

        do_action('b2_submit_request',$data);

        if($res){
            apply_filters('b2_check_repo_after',$current_user_id,$public_count);

            Message::update_data([
                'date'=>current_time('mysql'),
                'from'=>$current_user_id,
                'to'=>1,
                'post_id'=>0,
                'msg'=>__('你收到一个来自${from}的工单','b2'),
                'type'=>'author_request',
                'type_text'=>__('收到工单','b2'),
                'old_row'=>1
            ]);

            return true;
        }

        return false;
    }
}