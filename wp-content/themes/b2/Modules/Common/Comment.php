<?php namespace B2\Modules\Common;

use B2\Modules\Common\Post;
use B2\Modules\Common\PostRelationships;
use B2\Modules\Common\Circle;

class Comment{
    public function init(){

        add_filter( 'comment_text', array(__CLASS__,'remove_kh'));

        add_action('comment_unapproved_to_approved',array($this,'comment_unapproved_to_approved_action'));
        add_action('comment_unapproved_to_approved_action', array($this,'comment_unapproved_to_approved'));

        add_action('comment_post', array($this,'comment_unapproved_to_approved'));

        add_filter('b2_comment_post_type', array(__CLASS__,'get_post_type'));

        add_filter('preprocess_comment',array($this,'stop_dark_room_user_comments')); 
    }

    public function stop_dark_room_user_comments($comment_data){

        $user_id = $comment_data['user_id'];
        $dark_room = (int)get_user_meta($user_id,'b2_dark_room',true);
        if($dark_room) wp_die(__('小黑屋禁闭中，禁止发言'));   
        
        return $comment_data;
    }

    public static function get_post_type($post_id){
        $name = '';

        $post_type = get_post_type($post_id);
        switch ($post_type) {
            case 'post':
                $name = __('文章','b2');
                break;
            case 'shop':
                $name = __('商品','b2');
                break;
            case 'page':
                $name = __('页面','b2');
                break;
            case 'circle':
                $name = __('话题','b2');
                break;
            case 'links':
                $name = b2_get_option('normal_custom','custom_links_name');
                break;
            case 'infomation':
                $name = b2_get_option('normal_custom','custom_infomation_name');
                break;
            case 'document':
                $name = b2_get_option('normal_custom','custom_document_name');
                break;
            case 'newsflashes':
                $name = b2_get_option('normal_custom','custom_newsflashes_name');
                break;
            case 'ask':
                $name = b2_get_option('normal_custom','custom_ask_name');
                break;
            case 'answer':
                $name = b2_get_option('normal_custom','custom_answer_name');
                break;
            default:
                # code...
                break;
        }
        
        return $name ? '['.$name.']' : '';
    }

    public function comment_unapproved_to_approved_action($comment){
        do_action( "comment_unapproved_to_approved_action", $comment->comment_ID);
    }

    public function comment_unapproved_to_approved($comment_id){

        $comment  = get_comment($comment_id);

        //如果评论未得到批准
        if($comment->comment_approved != 1) return;

        //获取评论的作者
        $comment_author = $comment->user_id ? (int)$comment->user_id : (string)$comment->comment_author;

        //获取评论的文章ID
        $post_id = $comment->comment_post_ID;

        //获取评论所在的文章作者ID
        $post_author_id = (int)get_post_field( 'post_author', $post_id);

        //如果有父级评论
        $parent_user_id = false;
        if($comment->comment_parent > 0){
            $parent_user_id = get_comment( $comment->comment_parent );
            $parent_user_id = $parent_user_id->user_id ? (int)$parent_user_id->user_id : false;
        }


        $self = $post_author_id === $comment_author;

        $post_type = get_post_type($post_id);

        $post_type_text = b2_get_post_type_name($post_type);

        if(is_int($comment_author) && !$self){

            $task = Task::update_task($comment_author,'task_comment');
            if($task){
                $credit = (int)b2_get_option('normal_gold','credit_comment');
                Gold::update_data([
                    'date'=>current_time('mysql'),
                    'to'=>$comment_author,
                    'gold_type'=>0,
                    'no'=>$credit,
                    'post_id'=>$post_id,
                    'msg'=>__('您发布了${count}条评论：${post_id}','b2'),
                    'type'=>'po_comment',
                    'type_text'=>__('评论','b2'),
                    'old_row'=>1
                ]);
            }
        }

        if(!$self){
            Message::update_data([
                'date'=>current_time('mysql'),
                'from'=>$comment_author,
                'to'=>$post_author_id,
                'post_id'=>$post_id,
                'msg'=>sprintf(__('${from}评论了您的%s：${post_id}','b2'),$post_type_text),
                'type'=>'comment_to_post',
                'type_text'=>__('新的留言','b2'),
                'old_row'=>1
            ]);

            $task = Task::update_task($post_author_id,'task_post_comment');
            if($task){
                $credit = (int)b2_get_option('normal_gold','credit_post_comment');
                Gold::update_data([
                    'date'=>current_time('mysql'),
                    'to'=>$post_author_id,
                    'gold_type'=>0,
                    'no'=>$credit,
                    'post_id'=>$post_id,
                    'msg'=>sprintf(__('有${count}人评论了您的%s：${post_id}','b2'),$post_type_text),
                    'type'=>'comment_to_post',
                    'type_text'=>sprintf(__('%s被评论','b2'),$post_type_text),
                    'old_row'=>1
                ]);
            }
            
        }
        
        if($parent_user_id && $parent_user_id != $comment_author){
            Message::update_data([
                'date'=>current_time('mysql'),
                'from'=>$comment_author,
                'to'=>$parent_user_id,
                'post_id'=>$post_id,
                'msg'=>sprintf(__('${from}在%s中回复了您：${post_id}','b2'),$post_type_text),
                'type'=>'comment_to_post_reply',
                'type_text'=>__('收到回复','b2'),
                'old_row'=>1
            ]);
        }

        update_post_meta($post_id,'b2_comment_update', wp_strtotime(current_time( 'mysql' )));

        wp_cache_delete('b2_user_'.$comment->user_id,'b2_user_custom_data');
        do_action('b2_rebuild_hotness',$post_id);
        return true;
        
    }

    public static function remove_kh( $comment_text ) {
        $comment_text = str_replace(array('{{','}}'), '', $comment_text );
        return $comment_text;
    }

    public static function comment_callback($comment, $args, $depth){

            $author = $self = $mod = $user = $parent_user  = $author_parent = $mod_parent = '';

            //置顶
            $comment_sticky = get_post_meta($comment->comment_post_ID,'b2_comment_sticky',true);
        
            $user_id = $comment->user_id;

            $commenter = $user_id === '0' ? (string)$comment->comment_author : b2_get_userdata($user_id,'link',$comment->comment_post_ID);

            $post_author = get_post_field( 'post_author', $comment->comment_post_ID );

            //作者
            if($user_id == $post_author){
                $author = '<b class="comment-auth comment-auth-mod">A</b>';
            }
        
            //管理员
            if($user_id != '0' && is_super_admin($user_id)){
                $mod = '<b class="comment-mod comment-auth-mod">M</b>';
            }

            if($user_id){
                $user_lv = User::get_user_lv($user_id);
                $user_vip = isset($user_lv['vip']['icon']) ? $user_lv['vip']['icon'] : '';
                $user_lv = isset($user_lv['lv']['icon']) ? $user_lv['lv']['icon'] : '';
            }else{
                $user_vip = '';
                $user_lv = '<span class="lv-icon user-lv">'.__('Guest','b2').'</span>';
            }
            

            $user = $commenter.'<span>'.$author.$mod.'</span>';

            $user_title = get_user_meta($user_id,'b2_title',true);
            $verify_icon = '';
            if($user_title && !$comment->comment_parent){
                $verify_icon = B2_VERIFY_ICON;
            }

            if(!$comment->comment_parent){
                $user .= '<span class="user-title">'.$user_title.'</span>';
            }

            //评论喜欢，反对
            

            if($comment->comment_parent){
                $comment_parent = get_comment($comment->comment_parent);
                $user_id = $comment_parent->user_id;

                $commenter_parent = $user_id === '0' ? get_comment_author($comment->comment_parent) : b2_get_userdata($user_id,'link',$comment->comment_post_ID);

                //作者
                if($user_id === $post_author){
                    $author_parent = '<b class="comment-auth comment-auth-mod">A</b>';
                }
                //管理员
                if($user_id != '0' && is_super_admin($user_id)){
                    $mod_parent = '<b class="comment-mod comment-auth-mod">M</b>';
                }
                $parent_user = '<span class="comment_at">@</span>'.$commenter_parent.'<span>'.$author_parent.$mod_parent.'</span>';
            }

        ?>
        <li>
        <article class="<?php echo empty( $args['has_children'] ) ? 'comment' :'parent comment'; echo $comment_sticky == $comment->comment_ID ? ' sticky-itme' : ''; ?>" id="comment-<?php echo $comment->comment_ID; ?>" itemscope itemtype="http://schema.org/Comment">
            <figure class="gravatar avatar-parent">
                <?php echo 
                    b2_get_img(array(
                        'src'=>get_avatar_url( $comment->user_id, 43),
                        'class'=>array('avatar'),
                        'alt'=>$user_id ? esc_attr(get_the_author_meta('display_name', $comment->user_id)) : ''
                    )).$verify_icon; ?>
            </figure>
            <div class="comment-item b2-radius">
                <div class="comment-meta" role="complementary">
                    <div class="comment-user-info">
                        <div class="">
                            <span class="comment-author">
                                <?php echo $user.$parent_user; ?>
                            </span>
                            <span>
                                <?php echo $user_vip.$user_lv; ?>
                            </span>
                            <?php if($comment_sticky == $comment->comment_ID){ ?>
                                <span class="sticky-icon"><?php echo __('置顶','b2'); ?></span>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="comment-floor"><?php ; ?><?php echo b2_timeago($comment->comment_date); ?></div>
                </div>
                <div class="comment-content post-content" itemprop="text">
                    <div class="comment-content-text"><?php do_action('b2_comment_text', $comment); comment_text(); ?></div>
                    <div class="comment-footer">
                        <div class="comment-footer-tools">
                            <div class="comment-vote-hidden">
                                <button id="comment-up-<?php echo $comment->comment_ID; ?>" class="'comment-up text" onclick="b2CommentList.vote(this,'comment_up','<?php echo $comment->comment_ID; ?>')"><?php echo b2_get_icon('b2-heart-fill').'<span></span>'; ?></button>
                                <button id="comment-down-<?php echo $comment->comment_ID; ?>" class="'comment-down text" onclick="b2CommentList.vote(this,'comment_down','<?php echo $comment->comment_ID; ?>')"><?php echo b2_get_icon('b2-dislike-fill').'<span></span>'; ?></button>
                            </div>
                            <div class="comment-item-hidden">
                                <!-- <button class="text"><?php echo __('举报','b2'); ?></button> -->
                                <?php if(!$comment->comment_parent){ ?>
                                    <button class="text comment-zd" onclick="b2CommentList.sticky('<?php echo $comment->comment_ID; ?>')"><?php echo $comment_sticky == $comment->comment_ID ? __('取消置顶','b2') : __('置顶','b2'); ?></button>
                                <?php } ?>
                                <button class="text fr reply" data-id="<?php echo $comment->comment_ID; ?>" onclick="b2CommentList.move('<?php echo $comment->comment_ID; ?>')"><?php echo __('回复','b2'); ?></button>
                            </div>
                            <?php if ($comment->comment_approved == '0') : ?>
                                <p class="comment-meta-item red"><?php echo __('审核中...','b2');?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div id="comment-form-<?php echo $comment->comment_ID; ?>"></div>
                </div>
            </div>
            <div id="comment-children-<?php echo $comment->comment_ID; ?>" class="children children-mark"></div>
        <?php
    }

    public static function comment_callback_close(){
        echo '</article></li>';
    }

    /**
     * 获取游客评论信息
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function get_commenter(){
        $comment_user = array(
            'name'=>'',
            'user_email'=>'',
            'avatar'=>User::get_letter_avatar(__('空','b2'))
        );

        $current_comment_user = wp_get_current_commenter();
        if(!empty($current_comment_user['comment_author_email'])){
            $comment_user = array(
                'name'=>sanitize_text_field($current_comment_user['comment_author']),
                'user_email'=>sanitize_text_field($current_comment_user['comment_author_email']),
                'avatar'=>User::get_letter_avatar($current_comment_user['comment_author'])
            );
        }

        return $comment_user;
    }

    public static function smilies_reset($return = false) {

        return apply_filters('b2_smilies', array(
            '😁'=>'😁',
            '😊'=>'😊',
            '😎'=>'😎',
            '😤'=>'😤',
            '😥'=>'😥',
            '😂'=>'😂',
            '😍'=>'😍',
            '😏'=>'😏',
            '😙'=>'😙',
            '😟'=>'😟',
            '😖'=>'😖',
            '😜'=>'😜',
            '😱'=>'😱',
            '😲'=>'😲',
            '😭'=>'😭',
            '😚'=>'😚',
            '💀'=>'💀',
            '👻'=>'👻',
            '👍'=>'👍',
            '💪'=>'💪',
            '👊'=>'👊'
        ));

    }

    public static function more_comments($post_id,$paged){
        $paged = (int)$paged;
        $user_id = b2_get_current_user_id();

        if($post_id){
            $include_unapproved = $user_id;
        }else{
            $guest = wp_get_current_commenter();
            $include_unapproved = $guest['comment_author_email'] ? $guest['comment_author_email'] : 'empty';
        }

        //$term_list = wp_get_post_terms($postid, 'labtype', array('fields' => 'slugs'));

        $order = get_option('comment_orde','asc');

        $ids = implode(",", self::get_comment_replies($post_id,get_post_meta($post_id,'b2_comment_sticky',true)));

        $comments = get_comments('post_id='.$post_id.'&order='.$order.'&status=approve&include_unapproved='.$include_unapproved.'&comment__not_in='.$ids);

        $list = wp_list_comments( array(
            'callback' => array(__CLASS__,'comment_callback'),
            'page' => $paged,
            'per_page' =>get_option('comments_per_page',10),
            'max_depth'=>2,
            'echo'=>false
            ), 
            $comments
        );

        $_list = $paged == 1 ? self::get_sticky_comments($post_id) : '';

        if($list || $_list){
            return array('data'=>$_list.$list);
        }else{
            return array('error'=>__('没有更多评论了','b2'));
        }
    }

    public static function get_sticky_comments($post_id){
        $ids = implode(",", self::get_comment_replies($post_id,get_post_meta($post_id,'b2_comment_sticky',true)));

        $list_s = '';

        if($ids){
            $comments_s = get_comments('post_id='.$post_id.'&status=approve&comment__in='.$ids);
            
            $list_s = wp_list_comments( array(
                'callback' => array(__CLASS__,'comment_callback'),
                'page' => 1,
                'per_page' =>-1,
                'echo'=>false
                ), 
                $comments_s
            );
        }

        return $list_s;
    }

    public static function get_tips(){

        $tips = b2_get_option('template_comment','comment_tips');

        if(!$tips) return;

        $tips = explode(PHP_EOL, $tips );

        $list = array();
        if(!empty($tips)){
            foreach ($tips as $k => $v) {
                $v = explode('|', $v);
                $list[] = array(
                    'title'=>esc_attr($v[0]),
                    'url'=>isset($v[1]) ? esc_url($v[1]) : ''
                );
            }
        }

        return $list[array_rand($list,1)];
    }

    public static function comment_vote($type,$comment_id){
        $user_id = b2_get_current_user_id();

        if(!$user_id) return false;

        $success = array(
            'comment_down'=>0,
            'comment_up'=>0
        );

        $up = PostRelationships::isset(array('type'=>'comment_up','user_id'=>$user_id,'comment_id'=>$comment_id));
        $down = PostRelationships::isset(array('type'=>'comment_down','user_id'=>$user_id,'comment_id'=>$comment_id));

        $comment = get_comment($comment_id);
        $comment_author = $comment->user_id ? (int)$comment->user_id : (string)$comment->comment_author;

        if($user_id == $comment_author) return ['error'=>__('不能点评自己的评论','b2')];

        $credit = (int)b2_get_option('normal_gold','credit_comment_up');

        if($type === 'comment_up'){
            if($up){
                $success['comment_up'] = -1;
                PostRelationships::delete_data(array('type'=>'comment_up','user_id'=>$user_id,'comment_id'=>$comment_id));
    
                if($comment->user_id){

                    Message::update_data([
                        'date'=>current_time('mysql'),
                        'from'=>$user_id,
                        'to'=>$comment_author,
                        'post_id'=>$comment_id,
                        'msg'=>__('${from}不太认同您的评论：${post_id}','b2'),
                        'type'=>'comment_down',
                        'type_text'=>__('不认同评论','b2'),
                        'old_row'=>1
                    ]);
                    $task = Task::update_task($comment_author,'task_comment_vote');
                    if($task){
                        Gold::update_data([
                            'date'=>current_time('mysql'),
                            'to'=>$comment_author,
                            'gold_type'=>0,
                            'no'=>-$credit,
                            'post_id'=>$comment_id,
                            'msg'=>__('有${count}人不太认同您的评论：${post_id}','b2'),
                            'type'=>'comment_down',
                            'type_text'=>__('评论被踩','b2'),
                            'old_row'=>1
                        ]);
                    }
                }
            }else{
                $success['comment_up'] = 1;
                PostRelationships::update_data(array('type'=>'comment_up','user_id'=>$user_id,'post_id'=>$comment->comment_post_ID,'comment_id'=>$comment_id));
                if($comment->user_id){

                    Message::update_data([
                        'date'=>current_time('mysql'),
                        'from'=>$user_id,
                        'to'=>$comment_author,
                        'post_id'=>$comment_id,
                        'msg'=>__('${from}给您的评论点了赞：${post_id}','b2'),
                        'type'=>'comment_up',
                        'type_text'=>__('认同您的评论','b2'),
                        'old_row'=>1
                    ]);

                    $task = Task::update_task($comment_author,'task_comment_vote');
                    
                    if($task){
                        Gold::update_data([
                            'date'=>current_time('mysql'),
                            'to'=>$comment_author,
                            'gold_type'=>0,
                            'no'=>$credit,
                            'post_id'=>$comment_id,
                            'msg'=>__('有${count}人给您的评论点了赞：${post_id}','b2'),
                            'type'=>'comment_up',
                            'type_text'=>__('认同您的评论','b2'),
                            'old_row'=>1
                        ]);
                    }
                    
                }
            }

            if($down){
                $success['comment_down'] = -1;
                PostRelationships::delete_data(array('type'=>'comment_down','user_id'=>$user_id,'comment_id'=>$comment_id));
            }
        }

        if($type === 'comment_down'){
            if($up){
                $success['comment_up'] = -1;
                PostRelationships::delete_data(array('type'=>'comment_up','user_id'=>$user_id,'comment_id'=>$comment_id));
    
                if($comment->user_id){

                    Message::update_data([
                        'date'=>current_time('mysql'),
                        'from'=>$user_id,
                        'to'=>$comment_author,
                        'post_id'=>$comment_id,
                        'msg'=>__('${from}不太认同您的评论：${post_id}','b2'),
                        'type'=>'comment_down',
                        'type_text'=>__('不认同评论','b2'),
                        'old_row'=>1
                    ]);
        
                    $task = Task::update_task($comment_author,'task_comment_vote');
                    if($task){
                        Gold::update_data([
                            'date'=>current_time('mysql'),
                            'to'=>$comment_author,
                            'gold_type'=>0,
                            'no'=>-$credit,
                            'post_id'=>$comment_id,
                            'msg'=>__('有${count}人不太认同您的评论：${post_id}','b2'),
                            'type'=>'comment_down',
                            'type_text'=>__('评论被踩','b2'),
                            'old_row'=>1
                        ]);
                    }

                }
            }

            if($down){
                $success['comment_down'] = -1;
                PostRelationships::delete_data(array('type'=>'comment_down','user_id'=>$user_id,'comment_id'=>$comment_id));
            }else{
                $success['comment_down'] = 1;
                PostRelationships::update_data(array('type'=>'comment_down','user_id'=>$user_id,'post_id'=>$comment->comment_post_ID,'comment_id'=>$comment_id)); 
            }
        }

        do_action('b2_comment_vote',$success,$comment_id,$user_id);

        return $success;
    }

    public static function comment_vote_data($ids,$post_id){
        $user_id = b2_get_current_user_id();
        

        if(empty($ids)) return false;

        $data = array();

        foreach ($ids as $k => $v) {

            $data[$v]['vote']['comment_up'] = PostRelationships::get_count(array('type'=>'comment_up','comment_id'=>$v));
            $data[$v]['vote']['comment_down'] = PostRelationships::get_count(array('type'=>'comment_down','comment_id'=>$v));

            if($user_id){
                $up = PostRelationships::isset(array('type'=>'comment_up','user_id'=>$user_id,'comment_id'=>$v));
                $down = PostRelationships::isset(array('type'=>'comment_down','user_id'=>$user_id,'comment_id'=>$v));
                $data[$v]['isset'] = $up ? 'comment_up' : ($down ? 'comment_down' : false);
            }
            
        }

        $_user_id = get_post_field('post_author',$post_id);

        return array(
            'list'=>$data,
            'can_sticky'=>user_can( $user_id, 'administrator' ) || ($user_id == $_user_id ? true : false)
        );
    }

    public static function comment_sticky($post_id,$comment_id){
        $user_id = b2_get_current_user_id();

        $comment_id_7 = get_comment($comment_id);

        if($comment_id_7){
            $comment_post_id = $comment_id_7->comment_post_ID ;

            if($comment_post_id != $post_id) return false;
        }
        

        $_user_id = get_post_field('post_author',$post_id);

        if($user_id && $post_id && $comment_id && (user_can($user_id, 'administrator' ) || $_user_id == $user_id)){
            $sticky_id = get_post_meta($post_id,'b2_comment_sticky',true);
        
            if($comment_id == $sticky_id){
                delete_post_meta((int)$post_id,'b2_comment_sticky');
            }else{
                update_post_meta((int)$post_id,'b2_comment_sticky',(int)$comment_id);
            }
            
            return true;
        }

        return false;

       
    }

    public static function get_comment_replies($post_id,$comment_id){
        $args = array(
            'post_id'=>$post_id,
            'orderby'=>'comment_parent',
            'order'=>'ASC'
        );
        $comments = get_comments($args);

        $ids = array();
        foreach($comments as $k => $v){
            if($v->comment_parent){
                $ids[$v->comment_ID] = $v->comment_parent;
            }
        }

        return self::b2_ids_arr($ids,array($comment_id));
    }

    public static function b2_ids_arr($ids,$id){
        foreach ($ids as $k => $v) {
            if(in_array($v,$id)){
                $id[] = $k;
            }
        }
        return $id;
    }

    public static function submit_comment($args){

        $user_id = b2_get_current_user_id();

        if($user_id){
            $role = User::check_user_role($user_id,'comment');
            if(!$role) return array('error'=>__('您无权进行此操作','b2'));

            //检查3小时内发布总数
            $post_count_3 = User::check_post($user_id);
            if(isset($post_count_3['error'])) return $post_count_3;
            
            
            wp_set_current_user($user_id);
        }else{

            if(isset($args['author']) && $args['author']){
                b2_setcookie('comment_author_' . COOKIEHASH,$args['author']);
            }

            if(isset($args['email']) && $args['email'] && is_email($args['email'])){
                b2_setcookie('comment_author_email_' . COOKIEHASH ,$args['email']);
            }
        }

        $post_id = (int)$args['comment_post_ID'];

        $post_type = get_post_type($args['comment_post_ID']);

        // if($post_type == 'shop'){
        //     if(!PostRelationships::isset(array('type'=>'shop_buy','user_id'=>$user_id,'post_id'=>$post_id)) && !user_can($user_id, 'administrator' )){
        //         return array('error'=>__('购买用户方可评论','b2'));
        //     }
        // }

        if($post_type === 'circle' && !$user_id){
            return array('error'=>__('请先登录之后再参与讨论','b2'));
        }

        $text = $args['comment'];

        $args['author'] = isset($args['author']) ? b2_remove_kh($args['author'],true) : '';
        $args['comment'] = b2_remove_kh($args['comment'],true);

        if(isset($args['author'])){
            $text = $args['author'].$args['comment'];
        }

        $censor = apply_filters('b2_text_censor', $text);
        if(isset($censor['error'])) return $censor;

        //防止重复提交
        $public_count = apply_filters('b2_check_repo_before', $user_id);
        if(isset($public_count['error'])) return $public_count;

        if(isset($args['_wp_unfiltered_html_comment'])){
            unset($args['_wp_unfiltered_html_comment']);
        }

        $img = $args['img'];

        if(isset($img['imgUrl']) && !empty($img['imgUrl'])){
            if(!attachment_url_to_postid($img['imgUrl'])) return array('error'=>__('参数错误','b2'));
        }

        $comment = wp_handle_comment_submission( wp_unslash( $args ) );

        if ( is_wp_error( $comment )) {
            return array(
                'error'=>$comment->get_error_messages()
            );
        }

        User::save_check_post_count($user_id);

        apply_filters('b2_check_repo_after', $user_id,$public_count);

        if(isset($img['imgUrl']) && !empty($img['imgUrl'])){
            update_comment_meta($comment->comment_ID,'b2_comment_img',array(
                'url'=>esc_url($img['imgUrl']),
                'id'=>(int)$img['imgId'],
            ));
        }

        if(!$comment->comment_parent){
            $floor = (int)get_post_meta($comment->comment_post_ID,'b2_floor_max',true);

            update_comment_meta($comment->comment_ID,'b2_floor',$floor+1);
            update_post_meta($comment->comment_post_ID,'b2_floor_max',$floor+1);
        }else{
            $floor = (int)get_comment_meta($comment->comment_parent,'b2_children_floor_max',true);

            update_comment_meta($comment->comment_ID,'b2_children_floor',$floor+1);
            update_comment_meta($comment->comment_parent,'b2_children_floor_max',$floor+1);
        }
        
        if($post_type === 'circle' || (isset($args['json']) && $args['json'])){
            return self::get_comment_data_by_id($comment->comment_ID,$floor+1);
        }
    
        if($user_id){
            $include_unapproved = $user_id;
        }else{
            $guest = wp_get_current_commenter();
            $include_unapproved = $guest['comment_author_email'] ? $guest['comment_author_email'] : 'empty';
        }

        $comments = get_comments('post_id='.$post_id.'&comment__in='.$comment->comment_ID.'&include_unapproved='.$include_unapproved);

        $list = wp_list_comments( array(
                'callback' => array(__CLASS__,'comment_callback'),
                'max_depth'=>2,
                'echo'=>false
            ), 
            $comments
        );

        return $list;
    }
     
    public static function get_user_comment_list($arg){

        $arg = apply_filters('b2_comment_list', $arg);

        $arg['offset'] = ($arg['paged'] -1)*$arg['number'];

        $arg['status'] = 'approve';

        $comments = get_comments($arg);

        $data = array();

        if(count($arg) < 2) return array('error'=>__('参数不全','b2'));

        if(!empty($comments)){
            foreach ($comments as $k => $v) {
                $img = get_comment_meta($v->comment_ID,'b2_comment_img',true);
                $img = isset($img['url']) ? $img['url'] : '';
                $data[] = array(
                    'display_name'=>get_the_author_meta('display_name',$v->user_id),
                    'user_link'=>get_author_posts_url($v->user_id),
                    'avatar'=>get_avatar_url($v->user_id),
                    'post_link'=>get_permalink($v->comment_post_ID),
                    'post_title'=>get_the_title($v->comment_post_ID),
                    'comment_content'=>wpautop(convert_smilies(strip_tags($v->comment_content))),
                    'comment_img'=>$img,
                    'comment_date'=>b2_timeago($v->comment_date)
                );
            }
        }

        global $wpdb;
        $count = $wpdb->get_var($wpdb->prepare( "SELECT COUNT(comment_ID) AS total FROM $wpdb->comments WHERE comment_approved = 1 AND user_id = %s", $arg['user_id'] ));

        return array(
            'count'=>$count,
            'data'=>$data
        );
    }

    public static function get_user_comment_count($user_id){
        global $wpdb;
        $count = $wpdb->get_var($wpdb->prepare('
                    SELECT COUNT(comment_ID) 
                    FROM ' . $wpdb->comments. ' 
                    WHERE user_id = %d',$user_id));
        return $count ? $count : 0;
    }

    public static function get_comment_content($comment_id){
        $comment_status = wp_get_comment_status($comment_id);
        if($comment_status == 'approved'){
            $comment_text = get_comment_text($comment_id);
            $comment_text = convert_smilies(mb_strimwidth(strip_tags($comment_text),0, 100 ,"..."));

            return $comment_text;
        }elseif($comment_status == 'unapproved'){
            return '<span class="gray">'.__('评论正在审核中','b2').'</span>';
        }else{
            return '<del>'.__('评论不存在','b2').'</del>';
        }
    }
    
    //小工具最新评论
    public static function get_new_comments($paged,$hidden,$count){

        $count = (int)$count;
        if($count > 30) return;
        
        $paged = (int)$paged;
        $offset = $count*($paged-1);
        $pages = 0;
    
        $args = array(
            'number'=>$count,
            'status'=>'approve',
            'author__not_in' =>$hidden,
            'offset'=>$offset,
            'type'=>'comment'
        );
        $comments = get_comments($args);
    
        $comments_count = get_comments('status=approve&type=comment&author__not_in='.$hidden.'&count=true');
        $pages = ceil($comments_count/$count);
        
        $width = b2_get_option('template_main','sidebar_width');

        $html = array();
        if(!empty($comments)){
            foreach ($comments as $comment) {

                $post_name = '';

                if(isset($comment->comment_post_ID)){
                    $post_name = apply_filters('b2_comment_post_type',$comment->comment_post_ID);
                }
                
                $avatar = get_avatar_url($comment,array('size'=>50));

                if($comment->user_id === '0'){
                    $user = array(
                        'name'=>$comment->comment_author,
                        'avatar'=>get_avatar_url(AUTH_KEY.'-'.$comment->comment_author, array('size'=>60)),
                        'link'=>''
                    );
                }else{
                    $user = User::get_user_public_data($comment->user_id,true);
                }

                $img = get_comment_meta($comment->comment_ID,'b2_comment_img',true);
                $img = isset($img['url']) ? $img['url'] : '';
                $title = get_the_title($comment->comment_post_ID);

                if(!$title){
                    $title = b2_get_excerpt($comment->comment_post_ID);
                }

                $html[] = array(
                    'user'=>$user,
                    'post'=>array(
                        'title'=>$title ? $title : b2_get_excerpt($comment->comment_post_ID,30),
                        'link'=>get_comment_link($comment->comment_ID),
                    ),
                    'content'=>self::get_comment_content($comment->comment_ID),
                    'comment_img'=>'',
                    //$img ? b2_get_thumb(array('thumb'=>$img,'width'=>300,'height'=>200)) : '',
                    'post_type'=> $post_name,
                    'date'=>b2_timeago(get_comment_date('Y-n-j G:i:s',$comment->comment_ID))
                );
            }
 
            return array('data'=>$html,'pages'=>$pages);
        }else{
            return array('data'=>array(),'pages'=>0);
        }
    }

    public static function get_comments_json($post_id,$paged,$order_by = 'ASC'){
        $paged = (int)$paged;
        $num = 10;

        if(!$paged) return array('error'=>__('参数错误','b2'));

        $offset = $num*($paged-1);

        if($order_by !== 'ASC' && $order_by !== 'DESC') return array('error'=>__('错误的参数','b2'));

        $user_id = b2_get_current_user_id();

        $out = '';

        $a = [];
        if($user_id){
            $a = get_user_meta($user_id,'b2_shields',true);
        }

        $query = new \WP_Comment_Query;

        $lists = $query->query([
            'post_id'=>$post_id,
            'status'=>'approve',
            'no_found_rows'=>false,
            'parent'=>0,
            'orderby'=>'comment_date',
            'order'=>$order_by,
            'author__not_in'=>$a,
            'offset'=>$offset,
            'number'=>$num,
            'paged'=>$paged
        ]);

        $pages = $query->max_num_pages;
        
        // return $lists;

        // global $wpdb;

        // $lists = $wpdb->get_results($wpdb->prepare(
        //     "SELECT comment_ID,comment_author,comment_date,comment_content,comment_parent,user_id,comment_approved,comment_post_ID FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_approved = 1 AND comment_parent = 0 $out ORDER BY comment_date $order_by LIMIT $offset,$num",
        //     $post_id
        // ),ARRAY_A);

        // $total_query = (int)$wpdb->get_var($wpdb->prepare(
        //     "SELECT COUNT(comment_ID) FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_approved = 1 AND comment_parent = 0 $out",
        //     $post_id
        // ));

       

        // $pages = ceil($total_query/$num);

        $data = array();

        $floor = 0;

        if(!empty($lists)){
            foreach ($lists as $k => $v) {

                //获取当前评论的楼层
                if(!$v->comment_parent){
                    $floor = (int)get_comment_meta($v->comment_ID,'b2_floor',true);
                    if(!$floor){
                        $floor = $offset+$k+1;
                        update_comment_meta($v->comment_ID,'b2_floor',$floor);
                    }
                }

                $arr = self::get_item_data($v,$user_id,$floor);
                $arr['child_comments'] = self::get_child_comments($v->comment_ID,1);
                $data[] = $arr;
            }
        }

        return array(
            'list'=>$data,
            'pages'=>$pages
        );
    }

    public static function get_comment_data_by_id($comment_id,$floor = 0){

        $user_id = b2_get_current_user_id();

        $lists = get_comment($comment_id);

        return self::get_item_data($lists,$user_id,$floor);
    }

    public static function get_child_comments($comment_id,$paged = 1){

        $user_id = b2_get_current_user_id();

        $paged = (int)$paged;
        $num = 3;
        $offset = 0;

        if((int)$paged > 1){
            $num = 6;
            $offset =  ($paged-1)*6 -3;
        }

        $a = [];
        if($user_id){
            $a = get_user_meta($user_id,'b2_shields',true);
        }

        $query = new \WP_Comment_Query;

        $lists = $query->query([
            'parent' => $comment_id,
            'status'=>'approve',
            'hierarchical'=>'flat',
            'orderby'=>'comment_date',
            'order'=>'asc',
            'author__not_in'=>$a,
            'no_found_rows'=>false,
            'offset'=>$offset,
            'number'=>$num,
            'paged'=>$paged
        ]);

        $count = count( $query->comments );

        // global $wpdb;

        $child = array();

        

        // $lists = $wpdb->get_results($wpdb->prepare(
        //     "SELECT comment_ID,comment_author,comment_date,comment_content,comment_parent,user_id,comment_approved
        //     from    (select comment_ID,comment_author,comment_date,comment_content,comment_parent,user_id,comment_approved from $wpdb->comments where comment_approved=1
        //             order by comment_parent,comment_ID) products_sorted,
        //             (select @pv := '%d') initialisation
        //     where   find_in_set(comment_parent, @pv)
        //     and length(@pv := concat(@pv, ',', comment_ID))
        //     LIMIT %d,%d",
        //     $comment_id,$offset,$num
        // ),ARRAY_A);

        // $count = $wpdb->get_var($wpdb->prepare(
        //     "SELECT COUNT(comment_ID)
        //     from    (select comment_parent,comment_ID,comment_approved from $wpdb->comments where comment_approved=1) products_sorted,
        //             (select @pv := '%d') initialisation
        //     where   find_in_set(comment_parent, @pv)
        //     and length(@pv := concat(@pv, ',', comment_ID))",
        //     $comment_id
        // ));

        $floor = (int)get_comment_meta($comment_id,'b2_children_floor_max',true);
        if(!$floor){
            update_comment_meta($comment_id,'b2_children_floor_max',$floor);
        }

        $floor = 0;

        $pages = ceil(($count-3)/6)+1;

        if(!empty($lists)){
            foreach ($lists as $k => $v) {

                $floor = (int)get_comment_meta($v->comment_ID,'b2_children_floor',true);
                if(!$floor){
                    $floor = $offset+$k+1;
                    update_comment_meta($v->comment_ID,'b2_children_floor',$floor);
                }

                $child[] = self::get_item_data($v,$user_id,$floor);
            }
        }

        return array(
            'list'=>$child,
            'count'=>(int)$count,
            'paged'=>(int)$paged,
            'pages'=>(int)$pages,
            'locked'=>false
        );
    }

    public static function get_user_id_by_comment_id($comment_id){
        global $wpdb;

        $author = $wpdb->get_row($wpdb->prepare(
            "SELECT user_id,comment_author FROM $wpdb->comments WHERE comment_ID = %d",
            $comment_id
        ),ARRAY_A);

        if(empty($author)) return 0;

        return $author;
    }

    public static function delete_comment($comment_id){

        $user_id = b2_get_current_user_id();

        $comment = get_comment( $comment_id );

        $can_edit = false;

        if(isset($comment -> user_id) && $comment -> user_id){
            if($user_id == $comment -> user_id){
                $can_edit = apply_filters('b2_can_edit', $comment->comment_date);
            }
    
            if(user_can( $user_id, 'manage_options' )){
                $can_edit = true;
            }
    
            if(get_post_type($comment -> comment_post_ID ) == 'circle'){
                $circle_id = Circle::get_circle_id_by_topic_id($comment -> comment_post_ID);
                if(Circle::is_circle_admin($user_id,$circle_id)){
                    $can_edit = true;
                }
            }
        }

        if($can_edit){
            return wp_delete_comment($comment_id);
        }

        return array('error'=>__('无权删除','b2'));
        
    }

    public static function get_item_data($item,$user_id = 0,$floor = 0){
        $img = get_comment_meta($item->comment_ID,'b2_comment_img',true);
        $width = '';
        $height = '';
        if(isset($img['id'])){
            $img_data = wp_get_attachment_image_src($img['id'],'full');
            $width = $img_data[1];
            $height = $img_data[2];
        }

        $up = PostRelationships::get_count(array('type'=>'comment_up','comment_id'=>$item->comment_ID));
        $down = PostRelationships::get_count(array('type'=>'comment_down','comment_id'=>$item->comment_ID));

        $picked = PostRelationships::isset(array('type'=>'comment_up','user_id'=>$user_id,'comment_id'=>$item->comment_ID));

        $content = str_replace(array('{{','}}'),'',$item->comment_content);
        // $content = sanitize_textarea_field($content);

        $thumb = $img ? b2_get_thumb(array('thumb'=>$img['url'],'width'=>202,'height'=>'100%')) : '';
        $thumb_webp = $img ? b2_get_thumb(array('thumb'=>$img['url'],'width'=>1000,'height'=>'100%')) : '';

        $can_edit = false;

        if($user_id == $item->user_id){
            $can_edit = apply_filters('b2_can_edit', $item->comment_date);
        }

        if(user_can( $user_id, 'manage_options' )){
            $can_edit = true;
        }

        $type = false;

        if(isset($item->comment_post_ID) && get_post_type($item->comment_post_ID) == 'circle'){
            $circle_id = Circle::get_circle_id_by_topic_id($item->comment_post_ID);
            if(Circle::is_circle_admin($user_id,$circle_id)){
                $can_edit = true;
            }
            $type = 'circle';
        }

        if(isset($item->comment_post_ID) && get_post_type($item->comment_post_ID) == 'infomation'){
            $type = 'infomation';
        }

        $parent_author = self::get_user_id_by_comment_id($item->comment_parent);
        $parent_author = isset($parent_author['user_id']) && $parent_author['user_id'] ? self::get_comment_author_data($parent_author['user_id'],'',$type) : (isset($parent_author['comment_author']) ? $parent_author['comment_author'] : __('未名','b2'));

        return array(
            'floor'=>$floor,
            'can_edit'=>$can_edit,
            'at'=>$parent_author,
            '_date'=>Post::time_ago($item->comment_date,true),
            '__date'=>$item->comment_date,
            'date'=>b2_timeago($item->comment_date),
            'comment_author'=>self::get_comment_author_data($item->user_id,$item->comment_author,$type),
            'comment_ID'=>$item->comment_ID,
            'comment_content'=>$content,
            'parent'=>$item->comment_parent,
            'child_comments'=>array(
                'list'=>array(),
                'count'=>0,
                'paged'=>1,
                'pages'=>1,
                'locked'=>false
            ),
            'status'=>wp_get_comment_status($item->comment_ID),
            'vote'=>array(
                'up'=>$up,
                'down'=>$down,
                'picked'=>$picked
            ),
            'img'=>$img ? array(
                'thumb'=>$thumb,
                'thumb_webp'=>apply_filters('b2_thumb_webp',$thumb_webp),
                'width'=>$width,
                'height'=>$height,
                'full'=>$img['url']
            ) : array()
        );
    }

    public static function get_comment_author_data($user_id,$author = '',$type = false){

        if($user_id){
            $data = get_userdata($user_id);
            $avatar = get_avatar_url($user_id,array('size'=>100));
    
            $data = array(
                'id'=>$user_id,
                'name'=>isset($data->display_name) ? $data->display_name : __('未名','b2'),
                'avatar'=>$avatar,
                'avatar_webp'=>apply_filters('b2_thumb_webp',$avatar),
                'link'=>get_author_posts_url($user_id),
                'lv'=>get_user_meta($user_id,'zrz_lv',true),
                'vip'=>get_user_meta($user_id,'zrz_vip',true)
            );

            if($type == 'circle'){
                return Circle::get_circle_people_page($data);
            }

            if($type == 'infomation'){
                $data['link'] = b2_get_custom_page_url('infomation-people').'?id='.$user_id;
            }

            return $data;
        }else{
            $avatar = get_avatar_url(0,array('size'=>100));
            return array(
                'id'=>0,
                'name'=>esc_attr($author),
                'avatar'=>$avatar,
                'avatar_webp'=>apply_filters('b2_thumb_webp',$avatar),
                'link'=>'',
                'lv'=>'',
                'vip'=>''
            );
        }
    }

    public static function get_hot_comment($post_id){

        $cache = wp_cache_get($post_id,'b2_hot_comment');

        if($cache) return $cache;

        global $wpdb;
        $table_name = $wpdb->prefix . 'b2_post_relationships';

        $count = $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT count(*) AS count,comment_id FROM $table_name WHERE type=%s AND post_id=%d GROUP BY comment_id ORDER BY count DESC LIMIT 1"
        ,'comment_up',$post_id),ARRAY_A);

        if(!empty($count)){
            $count = $count[0];
            if((int)$count['count'] < 3) return array();

            $comment_id = $count['comment_id'];

            $comment_data = get_comment($comment_id);

            if($comment_data->comment_approved != '1') return array();

            $content = str_replace(array('{{','}}'),'',$comment_data->comment_content);
            $content = sanitize_textarea_field($content);

            $data = array(
                'author'=>esc_attr($comment_data->comment_author),
                'content'=>$content,
                'comment_up'=>$count['count']
            );
        }else{
            $data = array();
        }

        wp_cache_set($post_id,$data,'b2_hot_comment',10 * MINUTE_IN_SECONDS);

        return $data;
        
    }

}