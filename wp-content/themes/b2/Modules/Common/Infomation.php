<?php namespace B2\Modules\Common;

use B2\Modules\Common\User;
use B2\Modules\Common\Post;

class Infomation{

   public $name = '',$for = '',$get = '',$slug = '';

   public function __construct(){
      $this->name = b2_get_option('normal_custom','custom_infomation_name');
      $this->for = b2_get_option('normal_custom','custom_infomation_for');
      $this->get = b2_get_option('normal_custom','custom_infomation_get');
      $this->slug = b2_get_option('normal_custom','custom_infomation_link');
   }

   public function get_infomation_list($data){

      $paged = isset($data['paged']) && $data['paged'] ? (int)$data['paged'] : 1;

      $count = isset($data['count']) && $data['count'] ? (int)$data['count'] : 12;

      $type = isset($data['type']) && $data['type'] ? $data['type'] : 'all';

      $fliter = isset($data['fliter']) && $data['fliter'] ? $data['fliter'] : 'default';

      $author = isset($data['author']) ? (int)$data['author'] : 0;

      $cat = isset($data['cat']) ? (int)$data['cat'] : 0;

      $s = isset($data['s']) ? sanitize_text_field( $data['s']) : '';

      $offset = ($paged -1)*$count;

      $user_id = b2_get_current_user_id();

      $args = [
         'offset'=>$offset,
         'post_status'=>'publish',
         'include_children' => true,
         'posts_per_page'=>$count,
         'post_type'=>'infomation',
         'fields'=>'ids',
         'meta_query'=>array(
            'relation' => 'AND',
            array(
               'key'     => 'b2_infomation_sticky',
               'type' => 'NUMERIC'
            ),
         ),
         'orderby'=>['meta_value_num' => 'DESC','date' => 'DESC']
      ];

      if($type != 'all'){
         if($type != 'for' && $type != 'get') return array('error'=>__('参数错误','b2'));

         array_push($args['meta_query'],array(
            'key'     => 'b2_infomation_type',
            'value'   => $type,
            'compare' => '=',
         ));
      }

      if($fliter == 'my' && $user_id){
         $likes = get_user_meta($user_id,'b2_infomation_like',true);

         if(!empty($likes)){
            $args['post__in'] = $likes;
         }else{
            return [
               'pages'=>0,
               'paged'=>1,
               'data'=>[]
            ];
         }
      }

      if($fliter == 'hot'){
         $args['orderby'] = ['meta_value_num' => 'DESC','comment_count' => 'DESC'];
      }

      if($author){
         $args['author'] = $author;
         if($user_id == $args['author']){
            $args['post_status'] = ['publish','pending'];
         }
      }

      if($cat){
         $args['tax_query'] = array(
            array (
                'taxonomy' => 'infomation_cat',
                'field' => 'term_id',
                'terms' => $cat,
            )
         );
      }

      if($s){
         $args['s'] = $s;
      }

      $the_query = new \WP_Query( $args );

      $post_data = array();
      $_pages = 1;
      $_count = 0;
      if ( $the_query->have_posts() ) {

         $_pages = $the_query->max_num_pages;
         $_count = $the_query->found_posts;

         while ( $the_query->have_posts() ) {

               $the_query->the_post();
               $id = get_the_ID();
               $d = $this->infomation_data($id);
               if($d){
                  $post_data[] = $d;
               }
               
         }
         
      }
      wp_reset_postdata();

      return [
         'pages'=>$_pages,
         'paged'=>$paged,
         'data'=>$post_data
      ];
   }

   public function info_attrs($str){

      $str = trim($str, " \t\n\r\0\x0B\xC2\xA0");
      $str = explode(PHP_EOL, $str);
      $data = array();

      foreach ($str as $v) {
          $_v = explode('|', $v);
          if(!empty($_v) && isset($_v[1])){
              $data[] = array(
                  'k'=>$_v[0],
                  'v'=>$_v[1]
              );
          }
      }

      return $data;
   }

   public function infomation_text($id){
      $type = get_post_meta($id,'b2_infomation_type',true);

      return [
         'type'=>$type,
         'text'=>$type == 'get' ? $this->get : $this->for
      ];
   }

   public function infomation_data($id){
      if(!$id) return [];

      // $sticky = get_post_meta($id,'b2_infomation_sticky',true);
      // if($sticky === ''){
      //    update_post_meta($id,'b2_infomation_sticky',0);
      // }
         
      $current_user_id = b2_get_current_user_id();

      //检查置顶过期
      $user_id = get_post_field('post_author',$id);
      
      $sticky = (int)get_post_meta($id,'b2_infomation_sticky',true);
      if($sticky){

         $days = (int)get_post_meta($id,'b2_infomation_sticky_days',true);

         if($days !== 0){
            $payed = get_user_meta($user_id,'b2_infomation_sticky_payed',true);

            if(!empty($payed)){
               foreach ($payed as $k => $v) {
                  if(isset($v['post_id']) && $v['post_id'] == $id){
                     if( wp_strtotime(current_time( 'mysql' )) > $v['end_date']){
                        update_post_meta($id,'b2_infomation_sticky',0);
                        unset($payed[$k]);
                        update_user_meta($user_id,'b2_infomation_sticky_payed',$payed);
                        do_action('b2_infomation_sticky_expired',$id);
                        break;
                     }
                  }
               }
            }else{
               update_post_meta($id,'b2_infomation_sticky',0);
            }
         }

      }

      $user = User::get_user_public_data($user_id,true);

      $user['link'] = b2_get_custom_page_url('infomation-people').'?id='.$user_id;

      $type = $this->infomation_text($id);

      $price = get_post_meta($id,'b2_infomation_price',true);

      $t_status = $this->get_infomation_status($id);

      if($t_status['status'] == 0){
         $contact = get_post_meta($id,'b2_infomation_contact',true);

         $arr = explode('|', $contact);
   
         if(isset($arr[0]) && isset($arr[1])){
            $contact = [
               'type'=>$arr[0],
               'number'=>$arr[1]
            ];
         }
      }else{
         $contact = [
            'type'=>__('联系方式','b2'),
            'number'=>__('已经完成，请勿再联系','b2')
         ];
      }
      

      $imgs = b2_get_first_img(get_post_field( 'post_content',$id ),'all');
      $_imgs = [];
      if(!empty($imgs)){
         foreach($imgs as $k=>$img){
            if($k > 3) continue;
            $_imgs[$k]['thumb'] = b2_get_thumb(['thumb'=>$img,'width'=>200,'height'=>150]);
            $_imgs[$k]['thumb_webp'] = apply_filters('b2_thumb_webp',$_imgs[$k]['thumb']);
         }
      }

      $cat = [];

      $cats = get_the_terms($id, 'infomation_cat' );

      if($cats && !is_wp_error($cats)){
         $cat = (array)$cats[0];
         $cat['link'] = get_term_link($cat['term_id']);
      }

      $date = get_the_date('Y-m-d H:i:s',$id);

      $meta = get_post_meta($id,'b2_infomation_meta',true);
      if($meta){
         $meta = $this->info_attrs($meta);
      }else{
         $meta = [];
      }

      $vote = $this->get_infomation_vote($id);

      $views = get_post_meta($id,'views',true);

      $comment_count = get_comments_number($id);

      $status = get_post_status($id);

      if($status == 'pending'){
         $link = get_permalink($id).'?viewtoken='.md5(AUTH_KEY.$current_user_id);
      }else{
         $link = get_permalink($id);
      }

      $file_arr = [];
      $files = get_post_meta($id,'b2_infomation_files',true);
      if($files){
         foreach ($files as $k => $v) {
            $file_arr[] = b2_get_thumb(['thumb'=>wp_get_attachment_url($v),'width'=>600,'height'=>'100%']);
         }
      }

      return [
         'id'=>$id,
         'sticky'=>(int)get_post_meta($id,'b2_infomation_sticky',true),
         'images'=>$_imgs,
         'meta'=>$meta,
         'author'=>$user,
         'title'=>get_the_title($id),
         'link'=>$link,
         'type'=>$type,
         'price'=>$price,
         'status'=>$t_status,
         'contact'=>$contact,
         'cat'=>$cat,
         'vote'=>$vote,
         'views'=>$views,
         'comment_count'=>$comment_count,
         'desc'=>b2_get_excerpt($id),
         'date'=>$date,
         'post_status'=>$status,
         'can_edit'=>$user_id ? Post::user_can_edit($id,$current_user_id) : false,
         '_date'=>Post::time_ago($date),
         'files'=>$file_arr
      ];
   }

   public function get_po_infomation_opts(){

      $user_id = b2_get_current_user_id();

      $allow_opts = b2_get_option('infomation_submit','submit_allow_opts');

      $sticky = get_user_meta($user_id,'b2_infomation_sticky_payed',true);
      $sticky = is_array($sticky) ? array_values($sticky) : [];

      $cats = b2_get_option('infomation_submit','submit_cats');
      $_cats = [];

      if(!empty($cats)){
         foreach ($cats as $v) {
            $term = get_term_by( 'id', $v, 'infomation_cat' ); 
            if(isset($term->name)){
               $_cats[] = [
                  'value'=>$v,
                  'text'=>$term->name
               ];
            }
         }
         
      }

      return array(
         'allow_opts'=>$allow_opts ? $allow_opts : [],
         'sticky_pay'=>b2_get_option('infomation_submit','submit_sticky_price'),
         'sticky'=>$sticky,
         'cats'=>$_cats
      );
   }

   public function infomation_cats($ids){
      if(empty($ids)) return [];

      $cats = [];

      foreach ($ids as $k => $v) {
         $t = get_term($v,'infomation_cat');
         $icon = b2_get_thumb(array('thumb'=>get_term_meta($v, 'b2_tax_img', true),'width'=>300,'height'=>300));

         $cats[] = [
            'name'=>$t->name,
            'id'=>$v,
            'link'=>B2_HOME_URI.'/'.$this->slug.'/'.$t->slug,
            'icon'=>$icon,
            'count'=>$t->count
         ];
      }

      return $cats;
   }

   public function insert_infomation($data){

      $user_id = b2_get_current_user_id();
 
      if(!$user_id) return array('error'=>__('请先登录','b2'));

      wp_set_current_user($user_id);

      //检查3小时内发布总数
      $post_count_3 = User::check_post($user_id);
      if(isset($post_count_3['error'])) return $post_count_3;

      $public_count = apply_filters('b2_check_repo_before', $user_id);
      if(isset($public_count['error'])) return $public_count;

      if(!b2_get_option('infomation_main','infomation_open') || !b2_get_option('infomation_submit','po_allow')) return array('error'=>__('禁止投稿','b2'));

      //检查是否有权限
      $role = User::check_user_role($user_id,'infomation');

      if(!$role && !user_can( $user_id, 'manage_options' ) && !user_can( $user_id, 'editor' )) return array('error'=>__('您没有权限发布文章','b2'));

      $data['type'] == 'for' ? $data['type'] : 'get';

      $metas = apply_filters( 'b2_infomation_submit_metas', $data['metas']);

      $metas = json_encode($metas,JSON_UNESCAPED_UNICODE);
      $metas = b2_remove_kh($metas,true);

      $data['title'] = isset($data['title']) ? b2_remove_kh($data['title'],true) : '';
      $data['content'] = isset($data['content']) ? str_replace(array('{{','}}'),'',$data['content']) : '';

      if(!$data['title']) return array('error'=>__('请填写标题','b2'));
      if(!$data['content']) return array('error'=>__('请填写内容','b2'));

      //审核字符串
      $censor = apply_filters('b2_text_censor', $data['title'].$data['content'].$metas);
      if(isset($censor['error'])) return $censor;

      $metas = json_decode($metas,true);

      if(!is_array($metas)) return array('error'=>__('参数错误','b2'));

      $metas['passtime'] =  (int)$metas['passtime'];

      if(isset($metas['passtime']) && (!is_numeric($metas['passtime']) || (int)$metas['passtime'] < 0)){
         return array('error'=>__('过期时间请确保为正整数','b2'));
      }

      $data['post_id'] = isset($data['post_id']) ? (int)$data['post_id'] : null;

      //设置置顶
      $attrs = isset($data['attrs']) ? $data['attrs'] : [];

      $allow_sticky = false;

      //检查置顶
      if(in_array('sticky',$attrs)){
         $sticky = get_user_meta($user_id,'b2_infomation_sticky_payed',true);
         $sticky = is_array($sticky) ? $sticky : [];
         
         foreach ($sticky as $k => $v) {
            if($data['post_id'] && isset($v['post_id']) && $v['post_id'] == $data['post_id']){
               if($v['end_date'] <  wp_strtotime(current_time( 'mysql' ))){
                  return array('error'=>__('该置顶权限已到期，无法继续置顶','b2'));
               }
               $allow_sticky = true;
               break;
            }elseif((isset($v['used']) && !$v['used'])){
               $allow_sticky = true;
               break;
            }
         }unset($v);
         

         if(!$allow_sticky) return array('error'=>__('您选择了置顶，但未支付，请先支付','b2'));
      }else{
         $sticky = get_user_meta($user_id,'b2_infomation_sticky_payed',true);
         $sticky = (array)$sticky;
         foreach ($sticky as $k => $v) {
            if($data['post_id'] && isset($v['post_id']) && $v['post_id'] == $data['post_id']){
               if($v['money'] == 0){
                  unset($sticky[$k]);
               }else{
                  $sticky[$k]['used'] = false;
               }

               update_user_meta($user_id,'b2_infomation_sticky_payed',$sticky);
               update_post_meta($v['post_id'],'b2_infomation_sticky',0);
               break;
            }
         }unset($v);
         
      }

      $post_count = b2_get_option('infomation_submit','po_can_post');

      if(!user_can( $user_id, 'manage_options' ) && !user_can( $user_id, 'editor' )){
         //检查是否有草稿
         $args=array(
             'post_type' => 'infomation',
             'post_status' => 'pending',
             'posts_per_page' => $post_count ? $post_count+1 : 3,
             'author' => $user_id
         );

         $posts = get_posts($args);
         if(count($posts) >= $post_count){
             return array('error'=>sprintf(__('您还有未审核的%s，请审核完后再提交','b2'),b2_get_option('normal_custom','custom_infomation_name')));
         }
      }

      //检查文章作者
      if($data['post_id']){
         if((get_post_field( 'post_author', $data['post_id'] ) != $user_id || get_post_type($data['post_id']) != 'infomation') && !user_can($user_id, 'administrator' ) && !user_can( $user_id, 'editor' )){
            return array('error'=>__('非法操作','b2'));
         }
      }

      if((user_can( $user_id, 'manage_options' ) || user_can( $user_id, 'editor' ))){
            $data['status'] = 'publish';
      }else{
            $data['status'] = 'pending';
      }

      $can_publish = User::check_user_media_role($user_id,'infomation');
      if($can_publish){
            $data['status'] = 'publish';
      }
   
      if($data['post_id']){
         $user_id = get_post_field( 'post_author', $data['post_id'] );
      }

      //提交
      $arg = array(
         'ID'=> $data['post_id'] ? $data['post_id'] : null,
         'post_title' => $data['title'],
         'post_content' => wp_slash($data['content']),
         'post_status' => $data['status'],
         'post_type'=>'infomation',
         'post_author' => $user_id
      );

      if($data['post_id']){
            $post_id = wp_update_post($arg);
      }else{
            $post_id = wp_insert_post( $arg );
      }

      if($post_id){

         User::save_check_post_count($user_id);

         apply_filters('b2_check_repo_after', $user_id,$public_count);

         if(isset($data['cat']) && $data['cat']){
            delete_term_meta((int)$data['cat'],'b2_infomation_count');
            wp_set_post_terms($post_id,array((int)$data['cat']),'infomation_cat');
         }

         // return $data['files'];
         if(isset($data['files']) && !empty($data['files'])){
            $file_ids = [];
            foreach ($data['files'] as $k => $v) {

               if(isset($v['id'])){
                  if(get_post_type($v['id']) == 'attachment'){
                     $file_ids[] = (int)$v['id'];
                  }
               }else{
                  if(get_post_type($v) == 'attachment'){
                     $file_ids[] = (int)$v;
                  }
               }
            }

            update_post_meta($post_id,'b2_infomation_files',$file_ids);
         }

         if(in_array('sticky',$attrs)){
            
            self::set_sticky($post_id);

         }else{
            update_post_meta($post_id,'b2_infomation_sticky',0);
         }
         
         $type = isset($data['type']) && $data['type'] === 'get' ? 'get' : 'for';
         update_post_meta($post_id,'b2_infomation_type',$type);

         if(isset($metas['price']) && is_numeric($metas['price']) && $metas['price'] > 0){
            if(b2_check_price($metas['price'])){
               update_post_meta($post_id,'b2_infomation_price',$metas['price']);
            }else{
               return array('error'=>__('请输入正确的价格参数','b2'));
            }
         }

         if(isset($metas['passtime']) && is_numeric($metas['passtime']) && $metas['passtime'] >= 0){
            if((int)$metas['passtime'] > 30){
               $metas['passtime'] = 0;
            }
            update_post_meta($post_id,'b2_infomation_passtime',(int)$metas['passtime']);
         }

         if(isset($metas['contact']['type']) && isset($metas['contact']['number']) && $metas['contact']['type'] && $metas['contact']['number']){
            update_post_meta($post_id,'b2_infomation_contact',$metas['contact']['type'].'|'.$metas['contact']['number']);
         }

         if(isset($metas['attrs']) && !empty($metas['attrs'])){
            $str = '';
            foreach ($metas['attrs'] as $k => $v) {
               if(isset($v['key']) && $v['key'] && isset($v['value']) && $v['value']){
                  $str .= $v['key'].'|'.$v['value'].PHP_EOL;
               }
            }

            update_post_meta($post_id,'b2_infomation_meta',$str);
         }

         //图片挂载到当前文章
         $regex = '/src="([^"]*)"/';
         preg_match_all( $regex, $data['content'], $matches );
         $matches = array_reverse($matches);

         if(!empty($matches[0])){
             foreach($matches[0] as $k => $v){
                 $thumb_id = Post::get_attached_id_by_url($v);
                 if($thumb_id){
                     //检查是否挂载过
                     if(!wp_get_post_parent_id($thumb_id) || (int)wp_get_post_parent_id($thumb_id) === 1){
                         wp_update_post(
                             array(
                                 'ID' => $thumb_id, 
                                 'post_parent' => $post_id
                             )
                         );
                     }
                 }
             }
         }

         if(isset($data['app']) && $data['app']){
            return $post_id;
         }else{
            return b2_get_custom_page_url('infomation-people').'?id='.$user_id;
         }

      }

      return array('error'=>__('发布失败','b2'));

   }

   public static function set_sticky($post_id){

      $author = get_post_field( 'post_author',$post_id );

      $status = get_post_status($post_id);

      $sticky = get_user_meta($author,'b2_infomation_sticky_payed',true);

      //计算结束时间
      foreach ($sticky as $k => $v) {
         if((isset($v['used']) && !$v['used']) || (isset($v['used']) && !$v['used'] && isset($v['post_id']) && $v['post_id'] != $post_id)){
            $end_date = b2_date_after(current_time('mysql'),$v['days']);
            if($end_date){
               $sticky[$k]['end_date'] = $end_date;
               $sticky[$k]['used'] = true;
               $sticky[$k]['post_id'] = $post_id;

               update_user_meta($author,'b2_infomation_sticky_payed',$sticky);
               update_post_meta($post_id,'b2_infomation_sticky',1);
               update_post_meta($post_id,'b2_infomation_sticky_days',$v['days']);
               break;
            }
         }
      }unset($v);
      
      return;
   }

   public function get_infomation_vote($id){
      $vote = [
         'count'=>0,
         'list'=>[]
      ];

      $vote_count = (int)get_post_meta($id,'b2_vote_up_count',true);
      if($vote_count){
         $vote_list = PostRelationships::get_data(array('type'=>'post_up','post_id'=>$id,'count'=>5));
         $users = [];

         if($vote_list){
            $vote_list = array_slice($vote_list,0,5);
            

            foreach ($vote_list as $key => $value) {
               $user_data = get_userdata($value['user_id']);
               $avatar = get_avatar_url( $value['user_id'],array('size'=>100));
               if($user_data){
                  $users[] = [
                     'id'=>$value['user_id'],
                     'name'=>$user_data->display_name,
                     'link'=>get_author_posts_url($value['user_id']),
                     'avatar'=>$avatar,
                     'avatar_web'=>apply_filters('b2_thumb_webp',$avatar)
                  ];
               }
               
            }
         }

         $vote = [
            'count'=>$vote_count,
            'list'=>$users
         ];
      }

      return $vote;
   }

   public function get_infomation_hot_comments($arg){

      $args = array(
         'post_type'=>'infomation',
         'date_query' => array(
            'after' => '24 hours ago'
         ),
         'number'=>10,
         'status'=>'approve',
         'type'=>'comment'
      );
      $comments = get_comments($args);

      $data = [];

      if(count($comments) > 0){
         foreach ($comments as $key => $value) {
            $data[] = [
               'author'=>User::get_user_public_data($value->user_id,true),
               'post'=>[
                  'title'=>get_the_title($value->comment_post_ID),
                  'link'=>get_permalink($value->comment_post_ID)
               ],
               'content'=>$value->comment_content
            ];
         }
      }

      return $data;
   }

   public function get_infomation_cats(){
   
      $cats = get_terms( 'infomation_cat', array(
         'hide_empty' => false,
         'orderby'=>'count',
         'order'=>'desc',
         'cache_domain'=>'b2_infomation_cat'
      ));
   
      $data = [];

      if(!empty($cats)){
         foreach ($cats as $key => $value) {
            $data[] = [
               'id'=>$value->term_id,
               'name'=>$value->name,
               'link'=>get_term_link($value->term_id),
               'count'=>$value->count
            ];
         }
      }

      return $data;
   }

   public function get_infomation_status($id){
    
      $status = [
         'status'=>0,
         'text'=> __('长期','b2')
      ];

      $s = get_post_meta($id,'b2_infomation_status',true);
      $s = (int)$s;
 
      if($s == 1){
         $status = [
            'status'=>1,
            'text'=> __('已完成','b2')
         ];
      }else{
         $days = (int)get_post_meta($id,'b2_infomation_passtime',true);
     
         if($days){
            $public_date = get_the_date('Y-m-d H:i:s',$id);
            $finish = b2_date_after($public_date,$days);
        
            $finish = b2Sec2Time($finish - wp_strtotime(current_time( 'mysql' )));
           
            if(!$finish){
               $status = [
                  'status'=>1,
                  'text'=> __('已过期','b2')
               ];
               
               if(!$s){
                  update_post_meta($id,'b2_infomation_status',1);
               }
   
            }else{
               $status = [
                  'status'=>0,
                  'text'=> sprintf(__('%s后','b2'),$finish)
               ];
            }
         }else{
            $status = [
               'status'=>0,
               'text'=> __('长期','b2')
            ];
         }
      }

      

      return $status;
   }

   public function get_infomation_single_data($arg){

      $user_id = b2_get_current_user_id();

      $arg['id'] = (int)$arg['id'];

      if(!$arg['id']) return array('error'=>__('参数不全','b2'));

      $post_author = get_post_field( 'post_author', $arg['id']);

      $counts = $this->get_author_infomation_data($post_author);

      $finish = $this->get_infomation_status($arg['id']);

      $can_edit = Post::user_can_edit($arg['id'],$user_id);

      $after = '';

      if($can_edit){
         $days = (int)get_post_meta($arg['id'],'b2_infomation_sticky_days',true);

         if($days != 0){
            $payed = get_user_meta($post_author,'b2_infomation_sticky_payed',true);
         
            $payed = is_array($payed) ? $payed : [];
            foreach ($payed as $k => $v) {
               if($v['post_id'] == $arg['id']){
                  $after = b2Sec2Time($v['end_date'] -  wp_strtotime(current_time( 'mysql' ))).__('后','b2');
               }
            }
         }else{
            $after = __('永不过期','b2');
         }
         
      }
      $status = get_post_meta($arg['id'],'b2_infomation_status',true);

      return [
         'author_count'=>$counts,
         'vote'=>$this->get_infomation_vote($arg['id']),
         'finish'=>$finish,
         'status'=> $status,
         'sticky'=>get_post_meta($arg['id'],'b2_infomation_sticky',true),
         'sticky_expired_date'=>$after,
         'post_status'=>get_post_status($arg['id']),
         'can_edit'=>$can_edit
      ];

   }

   public function get_infomation_count($term_id){

      if(!$term_id){
         return [
            'for'=>0,
            'get'=>0
         ];
      }

      $count = get_term_meta($term_id,'b2_infomation_count',true);

      if($count){
         return $count;
      }

      $term = get_term($term_id,'infomation_cat');

      if(isset($term->name)){
         $for = new \WP_Query(
            array(
               'posts_per_page' => -1,
               'post_type'=>'infomation',
               'meta_key' => 'b2_infomation_type', 
               'meta_value' => 'for',
               'post_status'=>'publish',
               'fields' => 'ids',
               // 'no_found_rows' => true,
               'tax_query' => array(
                  array (
                      'taxonomy' => 'infomation_cat',
                      'field' => 'term_id',
                      'terms' =>$term_id,
                  )
               )
            )
         );

         $data = [
            'for'=>$for->found_posts,
            'get'=>$term->count - $for->found_posts
         ];
      }else{
         $data = [
            'for'=>0,
            'get'=>0
         ];
      }

      update_term_meta($term_id,'b2_infomation_count',$data);

      return $data;
   }

   public function get_author_infomation_data($user_id){

      $counts = get_user_meta($user_id,'b2_infomation_counts',true);
      if($counts) return $counts;

      $public_count = count_user_posts($user_id,'infomation',true);

      
      $query = new \WP_Query( array( 'post_type'=>'infomation','meta_key' => 'b2_infomation_type', 'meta_value' => 'get','author'=> $user_id,'post_status'=>'publish') );

      $get_count = $query->post_count;

      $for_count = $public_count - $get_count;

      $query = new \WP_Query( array( 'post_type'=>'infomation','meta_key' => 'b2_infomation_status', 'meta_value' => 1,'author'=> $user_id,'post_status'=>'publish') );

      $finish_count = $query->post_count;

      $doing_count = $public_count - $finish_count;

      $arg = [
         'total'=>$public_count,
         'get'=>$get_count,
         'for'=>$for_count,
         'finish'=>$finish_count,
         'doing'=>$doing_count
      ];

      update_user_meta($user_id,'b2_infomation_counts',$arg);

      return $arg;
   }

   public static function link_breadcrumb($post_id = 0){
      $home = B2_HOME_URI;
      $shop = get_post_type_archive_link('infomation');
      $tax = '';

      $tax = get_the_terms($post_id, 'infomation_cat');
      $tax_links = '';
      $post_link = '';

      if($tax && $post_id){
          $tax = get_term($tax[0]->term_id, 'infomation_cat' );

          $term_id = $tax->term_id;

      }else{
          $term = get_queried_object();
          $term_id = isset($term->term_id) ? $term->term_id : 0;

      }

      if($term_id){
          $tax_links = get_term_parents_list($term_id,'infomation_cat');
          $tax_links = str_replace('>/<','><span>></span><',$tax_links);
          $tax_links = rtrim($tax_links,'/');
      }else{
          if(isset($_GET['s'])){
              $tax_links = __('搜索','b2');
          }else{
              $tax_links = __('未分类','b2');
          }
      }

      if($post_id){
          $post_link = '<span>></span>'.get_the_title($post_id);
      }

      return '<a href="'.B2_HOME_URI.'">'.__('首页','b2').'</a><span>></span>'.'<a href="'.$shop.'">'.b2_get_option('normal_custom','custom_infomation_name').'</a><span>></span>'.$tax_links;
   }

   public function edit_infomation_data($data){
      $user_id = b2_get_current_user_id();

      if(!$user_id) return array('error'=>__('请先登录','b2'));

      if(!isset($data['id'])) return array('error'=>__('参数错误','b2'));

      $data['id'] = (int)$data['id'];

      if(get_post_type($data['id']) !== 'infomation') return array('error'=>__('参数错误','b2'));

      $can_edit = Post::user_can_edit($data['id'],$user_id);

      if(!$can_edit) return array('error'=>__('权限不足','b2'));

      $title = get_the_title($data['id']);
      $content = Post::get_write_countent($data['id']);

      $type = get_post_meta($data['id'],'b2_infomation_type',true);

      $sticky = get_post_meta($data['id'],'b2_infomation_sticky',true);

      $sticky_days = get_post_meta($data['id'],'b2_infomation_sticky_days',true);

      $price = get_post_meta($data['id'],'b2_infomation_price',true);

      $passtime = get_post_meta($data['id'],'b2_infomation_passtime',true);

      $meta = get_post_meta($data['id'],'b2_infomation_meta',true);
      if(!$meta){
         $_meta = [
            [
               'key'=>'',
               'value'=>''
            ]
         ];
      }else{

         $meta = $this->info_attrs($meta);

         $_meta = [];

         foreach ($meta as $k => $v) {
            $_meta[$k] = [
               'key'=>$v['k'],
               'value'=>$v['v']
            ];
         }
      }

      $contact = get_post_meta($data['id'],'b2_infomation_contact',true);

      $arr = explode('|', $contact);

      if(isset($arr[0]) && isset($arr[1])){
         $contact = [
            'type'=>$arr[0],
            'number'=>$arr[1]
         ];
      }else{
         $contact = [
            'type'=>'',
            'number'=>''
         ];
      }

      $cat = [];

      $cats = get_the_terms($data['id'], 'infomation_cat' );

      if($cats && !is_wp_error($cats)){
         $cat = (array)$cats[0];
         $cat['link'] = get_term_link($cat['term_id']);
      }

      $file_arr = [];
      $files = get_post_meta($data['id'],'b2_infomation_files',true);
      if($files){
         foreach ($files as $k => $v) {
            $file_arr[] = [
               'id'=>$v,
               'url'=>wp_get_attachment_url($v)
            ];
         }
      }

      return [
         'title'=>$title,
         'content'=>$content,
         'meta'=>[
            'price'=>$price,
            'passtime'=>$passtime,
            'contact'=>$contact,
            'attrs'=>$_meta
         ],
         'sticky_days'=>$sticky_days,
         'type'=>$type,
         'cat'=>$cat,
         'files'=>$file_arr
      ];
   }
   
}