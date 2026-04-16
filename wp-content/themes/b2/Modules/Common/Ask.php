<?php namespace B2\Modules\Common;
use B2\Modules\Common\Post;
use B2\Modules\Common\User;
use B2\Modules\Common\Message;

class Ask{
    public function init(){
       add_filter( 'b2_get_post_data', [$this,'b2_get_post_data'],10,2);
    }

    public function b2_get_post_data($data,$post_id){
        $post_type = get_post_type($post_id);

        if($post_type == 'ask'){
            $data['answer_count'] = self::get_item_answer_count($post_id);
            $data['inv'] = self::get_ask_inv_users($post_id);
            $data['excerpt'] = b2_get_excerpt($post_id,80);
            $data['best'] = (int)get_post_meta($post_id,'b2_ask_best',true);
            $data['post_status'] = get_post_status($post_id);
            $data['metas'] = [
                'endtime'=>self::ask_end_time($post_id),
                'reward'=>get_post_meta($post_id,'b2_ask_reward',true)
            ];
            if(isset($data['current_user']['id'])){
                $data['can_edit'] = Post::user_can_edit($post_id,$data['current_user']['id']);
            }else{
                $data['can_edit'] = false;
            }
            
        }

        if($post_type == 'answer'){
            if(isset($data['current_user']['id'])){
                $data['can_edit'] = Post::user_can_edit($post_id,$data['current_user']['id']);
            }else{
                $data['can_edit'] = false;
            }
            $data['post_status'] = get_post_status($post_id);
        }

        return $data;
    }

    public static function ask_answer_html($data){
        return '
            <div class="ask-page">
                <div id="b2-editor-box" ref="poask" data-id="'.$data['id'].'">
                    <textarea id="ask-edit-content"></textarea>
                    <div :class="[\'trix-dialog trix-dialog--images modal\',{\'show-modal\':showImageBox}]" v-cloak>
                        <div class="trix-dialog__link-fields box">
                            <div class="trix-dialog-title" v-if="!thumbPicked">'.__('插入图片','b2').'</div>
                            <div class="trix-dialog-title" v-else>'.__('设置题图','b2').'</div>
                            <span class="close-button" @click.stop="close(\'image\')">×</span>
                            <div class="image-table">
                                <div :class="imgTable == \'upload\' ? \'picked\' : \'\'" @click.stop="imgTable = \'upload\'">'.__('上传图片','b2').'</div>
                                <div :class="imgTable == \'lib\' ? \'picked\' : \'\'" @click.stop="imgTable = \'lib\'">'.__('我的图片','b2').'</div>
                                <div :class="imgTable == \'link\' ? \'picked\' : \'\'" @click.stop="imgTable = \'link\'" v-if="!thumbPicked">'.__('外链图片','b2').'</div>
                            </div>
                            <label class="image-upload-box" v-if="imgTable == \'upload\'">
                                <div class="">
                                    <svg class="b2 b2--BackToTop" fill="currentColor" viewBox="0 0 24 24" width="70" height="70"><path d="M16.036 19.59a1 1 0 0 1-.997.995H9.032a.996.996 0 0 1-.997-.996v-7.005H5.03c-1.1 0-1.36-.633-.578-1.416L11.33 4.29a1.003 1.003 0 0 1 1.412 0l6.878 6.88c.782.78.523 1.415-.58 1.415h-3.004v7.005z"></path></svg>
                                    <p>'.__('请选择要上传的图片','b2').'</p>
                                </div>
                                <input type="file" class="b2-hidden-always" accept="image/jpg,image/jpeg,image/png,image/gif" @change="fileUpload($event,\'image\')" multiple="multiple">
                            </label>
                            <div class="trix-dialog-image-box" v-if="imgTable == \'lib\'">
                                <div class="" v-if="imageList.length > 0">    
                                    <ul class="editor-images-list">
                                        <li v-for="item in imageList" @click.stop="item.thumb ? picked(\'image\',item.att_url) : \'\'" :class="(imagePicked.indexOf(item.att_url) !== -1 && !thumbPicked) || (thumb == item.att_url && thumbPicked) ? \'picked\' : \'\'">
                                            <div class="editor-image">
                                                <span v-if="!item.thumb" class="b2-loading button text"></span>
                                                <img :src="item.thumb" v-if="item.thumb">
                                            </div>
                                        </li>
                                    </ul>
                                    <div class="image-nav" v-if="imagePages > 0">
                                        <button class="text" :disabled="locked.pre || paged == 1 ? true : false" @click.stop="per(\'image\')">'.__('上一页','b2').'</button>
                                        <button class="text" :disabled="locked.next || paged == imagePages ? true : false" @click.stop="next(\'image\')">'.__('下一页','b2').'</button>
                                    </div>
                                </div>
                                <div class="editor-file-none" v-else>
                                    '.__('您未上传过图片','b2').'
                                </div>
                            </div>
                            <div class="trix-dialog-input-box" v-if="imgTable == \'link\'">
                                <div class="trix-dialog-input input-textarea">
                                    <textarea type="text" name="imageLink" class="trix-input trix-input--dialog" id="imageLink"></textarea>
                                </div>
                                <p class="dialog-desc">'.__('支持多张外链图片，每个图片连接占一行','b2').'</p>
                            </div>
                            <div class="trix-button-group" v-show="imgTable != \'upload\'">
                                <button  class="empty" @click.stop="close(\'image\')">'.__('取消','b2').'</button>
                                <button @click.stop="insert(\'image\')"  v-if="!thumbPicked">'.__('插入','b2').'</button>
                                <button @click.stop="setThumb"  v-else>'.__('设置','b2').'</button>
                            </div>
                        </div>
                    </div>
                    <div :class="[\'trix-dialog trix-dialog--video modal\',{\'show-modal\':showVideoBox}]" v-cloak>
                        <div class="trix-dialog__link-fields box">
                            <div class="trix-dialog-title">'.__('插入视频','b2').'</div>
                            <span class="close-button" @click.stop="close(\'video\')">×</span>
                            <div class="image-table">
                                <div :class="videoTable == \'upload\' ? \'picked\' : \'\'" @click.stop="videoTable = \'upload\'">'.__('上传视频','b2').'</div>
                                <div :class="videoTable == \'lib\' ? \'picked\' : \'\'" @click.stop="videoTable = \'lib\'">'.__('我的视频','b2').'</div>
                                <div :class="videoTable == \'link\' ? \'picked\' : \'\'" @click.stop="videoTable = \'link\'">'.__('外链视频','b2').'</div>
                                <div :class="videoTable == \'html\' ? \'picked\' : \'\'" @click.stop="videoTable = \'html\'">'.__('内嵌视频','b2').'</div>
                            </div>
                            <label class="image-upload-box" v-if="videoTable == \'upload\'">
                                <div class="">
                                    <svg class="b2 b2--BackToTop" fill="currentColor" viewBox="0 0 24 24" width="70" height="70"><path d="M16.036 19.59a1 1 0 0 1-.997.995H9.032a.996.996 0 0 1-.997-.996v-7.005H5.03c-1.1 0-1.36-.633-.578-1.416L11.33 4.29a1.003 1.003 0 0 1 1.412 0l6.878 6.88c.782.78.523 1.415-.58 1.415h-3.004v7.005z"></path></svg>
                                    <p>'.__('请选择要上传的视频','b2').'</p>
                                </div>
                                <input type="file" class="b2-hidden-always" accept="video/mp4,video/x-ms-asf,video/x-ms-wmv,video/x-ms-wmx,video/x-ms-wm,video/avi,video/divx,video/x-flv,video/quicktime,video/mpeg,video/ogg,video/webm,video/x-matroska,video/3gpp,video/3gpp2" @change="fileUpload($event,\'video\')">
                            </label>
                            <div class="trix-dialog-video-box" v-if="videoTable == \'lib\'">
                                <div class="" v-if="videoList.length > 0">    
                                    <ul class="editor-images-list">
                                        <li v-for="item in videoList" @click.stop="item.att_url ? picked(\'video\',item.att_url) : \'\'" :class="videoPicked && videoPicked.indexOf(item.att_url) !== -1 ? \'picked\' : \'\'">
                                            <div class="editor-image">
                                                <span v-if="!item.att_url" class="b2-loading button text"></span>
                                                <video muted :src="item.att_url" v-if="item.att_url" @mouseenter="videoplay($event,\'play\')" @mouseleave="videoplay($event,\'stop\')"></video>
                                            </div>
                                        </li>
                                    </ul>
                                    <div class="image-nav" v-if="videoPages > 0">
                                        <button class="text" :disabled="locked.pre || paged == 1 ? true : false" @click.stop="per(\'video\')">'.__('上一页','b2').'</button>
                                        <button class="text" :disabled="locked.next || paged == videoPages ? true : false" @click.stop="next(\'video\')">'.__('下一页','b2').'</button>
                                    </div>
                                </div>
                                <div class="editor-file-none" v-else>
                                    '.__('您未上传过视频','b2').'
                                </div>
                            </div>
                            <div v-if="videoTable == \'link\'">
                                <div class="trix-dialog-input-box">
                                    <div class="trix-dialog-input">
                                        <label for="videoLink">'.__('视频地址','b2').'</label>
                                        <input type="url" name="videoLink" class="trix-input trix-input--dialog" id="videoLink">
                                    </div>
                                    <p class="dialog-desc">'.__('复制视频文件地址或各大视频网站视频地址','b2').'</p>
                                </div>
                                <div class="trix-dialog-input-box">
                                    <div class="trix-dialog-input">
                                        <label for="videoThumb">'.__('视频封面','b2').'</label>
                                        <input type="url" name="videoThumb" class="trix-input trix-input--dialog" id="videoThumb">
                                    </div>
                                    <p class="dialog-desc">'.__('如果不设置封面，程序会自动获取','b2').'</p>
                                </div>
                            </div>
                            <div class="trix-dialog-input-box" v-if="videoTable == \'html\'" v-cloak>
                                <div class="trix-dialog-input input-textarea">
                                    <textarea type="url" class="trix-input trix-input--dialog" id="videoHtml"></textarea>
                                </div>
                                <p class="dialog-desc">'.__('将内嵌视频代码粘贴在上面','b2').'</p>
                            </div>
                            <div class="trix-button-group" v-if="videoTable != \'upload\'">
                                <button  class="empty" @click.stop="close(\'video\')">'.__('取消','b2').'</button>
                                <button  @click.stop="insert(\'video\')">'.__('插入','b2').'</button>
                            </div>
                        </div>
                    </div>
                    <div :class="[\'trix-dialog trix-dialog--video modal\',{\'show-modal\':showPostBox}]" v-cloak>
                        <div class="trix-dialog__link-fields box">
                            <div class="trix-dialog-title">'.__('插入文章','b2').'</div>
                            <span class="close-button" @click.stop="close(\'post\')">×</span>
                            <div class="trix-dialog-input-box">
                                <div class="trix-dialog-input">
                                    <label for="postLink">'.__('文章网址','b2').'</label>
                                    <input type="url" name="postLink" class="trix-input trix-input--dialog" id="postLink">
                                </div>
                                <p class="dialog-desc">'.__('只允许插入本站的文章连接','b2').'</p>
                            </div>
                            <div class="trix-button-group">
                                <button  @click.stop="close(\'post\')" class="empty">'.__('取消','b2').'</button>
                                <button @click.stop="insertPost()">'.__('插入','b2').'</button>
                            </div>
                        </div>
                    </div>
                    <div :class="[\'trix-dialog trix-dialog--video modal\',{\'show-modal\':showFileBox}]" v-cloak>
                        <div class="trix-dialog__link-fields box">
                            <div class="trix-dialog-title">'.__('插入附件','b2').'</div>
                            <span class="close-button" @click.stop="close(\'file\')">×</span>
                            <div class="trix-dialog-input-box">
                                <div class="trix-dialog-input">
                                    <label for="fileTitle">'.__('标题','b2').'</label>
                                    <input type="url" name="fileTitle" class="trix-input trix-input--dialog" id="fileTitle">
                                </div>
                            </div>
                            <div class="trix-dialog-input-box">
                                <div class="trix-dialog-input">
                                    <label for="fileLink">'.__('连接','b2').'</label>
                                    <input type="url" name="fileLink" class="trix-input trix-input--dialog" id="fileLink">
                                </div>
                            </div>
                            <div class="trix-dialog-input-box">
                                <div class="trix-dialog-input">
                                    <label for="fileTq">'.__('提取码','b2').'</label>
                                    <input type="url" name="fileTq" class="trix-input trix-input--dialog" id="fileTq">
                                </div>
                                <p class="dialog-desc">'.__('选填','b2').'</p>
                            </div>
                            <div class="trix-dialog-input-box">
                                <div class="trix-dialog-input">
                                    <label for="fileJy">'.__('解压码','b2').'</label>
                                    <input type="url" name="fileJy" class="trix-input trix-input--dialog" id="fileJy">
                                </div>
                                <p class="dialog-desc">'.__('选填','b2').'</p>
                            </div>
                            <div class="trix-button-group">
                                <button @click.stop="close(\'file\')" class="empty">'.__('取消','b2').'</button>
                                <button @click.stop="insertFile">'.__('插入','b2').'</button>
                            </div>
                        </div>
                    </div>
                    <div class="submit-answer"><button @click="submitAnswer">'.__('提交','b2').'</button></div>
                </div>
            </div>
        ';
    }

    public static function po_ask($data){
        $user_id = b2_get_current_user_id();

        if(!$user_id) return ['error'=>__('请先登陆','b2')];

        wp_set_current_user($user_id);

        //检查3小时内发布总数
        $post_count_3 = User::check_post($user_id);
        if(isset($post_count_3['error'])) return $post_count_3;

        $public_count = apply_filters('b2_check_repo_before', $user_id);
        if(isset($public_count['error'])) return $public_count;

        //检查是否有权限
        $role = User::check_user_role($user_id,'ask');

        if(!$role && !user_can( $user_id, 'manage_options' ) && !user_can( $user_id, 'editor' )) return array('error'=>__('您没有权限提问','b2'));

        //检查悬赏金额
        $data['reward'] = $data['reward'] === 'true' ? true : false;

        if($data['reward']){
            $data['money'] = (float)$data['money'];

            $data['money'] = round($data['money'],2);

            $data['passtime'] = (int)$data['passtime'] >= 0 ? (int)$data['passtime'] : 7;

            if($data['passtime'] == 0){
                $data['passtime'] = 7;
            }

            if($data['rewardType'] === 'money'){
                if($data['money'] > 100000 || $data['money'] <= 0){
                    return ['error'=>__('金额错误','b2')];
                }

                $money = (float)get_user_meta($user_id,'zrz_rmb',true);
                if($data['money'] > $money){
                    return ['error'=>__('您的余额不足以支付赏金','b2')];
                }
            }else{
                $data['money'] = (int)$data['money'];

                if($data['money'] > 10000000 || $data['money'] <= 0){
                    return ['error'=>__('金额错误','b2')];
                }

                $credit = get_user_meta($user_id,'zrz_credit_total',true);

                if($data['money'] > $credit){
                    return ['error'=>__('您的积分不足以支付赏金','b2')];
                }
            }
        }

        // 新增钩子
        // edited by fuzqing
        do_action('b2_user_write_ask',$user_id);

        $post_count = b2_get_option('ask_submit','ask_can_post');

        $data['title'] = isset($data['title']) ? b2_remove_kh($data['title']) : '';
        $data['content'] = isset($data['content']) ? str_replace(array('{{','}}'),'',$data['content']) : '';

        if(!$data['title']){
            return array('error'=>__('标题不可为空','b2'));
        }

        //检查文章内容
        if(!$data['content']){
            return array('error'=>__('内容不可为空','b2'));
        }

        $censor = apply_filters('b2_text_censor', $data['title'].$data['content']);
        if(isset($censor['error'])) return $censor;

        if(!user_can( $user_id, 'manage_options' ) && !user_can( $user_id, 'editor' )){
            //检查是否有草稿
            $args=array(
                'post_type' => 'ask',
                'post_status' => 'pending',
                'posts_per_page' => $post_count ? $post_count+1 : 3,
                'author' => $user_id
            );

            $posts = get_posts($args);
            if(count($posts) >= $post_count){
                return array('error'=>__('您还有未审核的文章，请审核完后再提交','b2'));
            }
        }

        $data['post_id'] = isset($data['post_id']) ? (int)$data['post_id'] : null;

        //检查文章作者
        if($data['post_id']){
            if((get_post_field( 'post_author', $data['post_id'] ) != $user_id || get_post_type($data['post_id']) != 'ask') && !user_can($user_id, 'administrator' ) && !user_can( $user_id, 'editor' )){
                return array('error'=>__('非法操作','b2'));
            }
        }

        $post_id = false;

        if((user_can( $user_id, 'manage_options' ) || user_can( $user_id, 'editor' ))){
            $data['type'] = 'publish';

        }else{
            $data['type'] = 'pending';
        }

        $can_publish = User::check_user_media_role($user_id,'ask');
        if($can_publish){
            $data['type'] = 'publish';
        }

        $post_author = null;

        if($data['post_id']){
            $post_author = get_post_field( 'post_author', $data['post_id'] );
        }
        $arg = array(
            'ID'=> $data['post_id'] ? $data['post_id'] : null,
            'post_title' => $data['title'],
            'post_content' => wp_slash($data['content']),
            'post_status' => $data['type'],
            'post_author' => $post_author,
            'post_type'=>'ask'
        );

        if($data['post_id']){
            $post_id = wp_update_post($arg);
        }else{
            $post_id = wp_insert_post( $arg );
        }

        if($post_id){
            apply_filters('b2_check_repo_after', $user_id,$public_count);
            User::save_check_post_count($user_id);

            //记录邀请人
            if(!empty($data['inv'])){
                
                $data['inv'] = b2_recursive_sanitize_text_field($data['inv']);

                $inv = [];

                foreach ($data['inv'] as $k => $v) {
                    if(isset($v['id']) && isset($v['name']) && isset($v['avatar'])){
                        $inv[] = [
                            'id'=>(int)$v['id'],
                            'name'=>b2_remove_kh($v['name'],true),
                            'avatar'=>esc_url($v['avatar'])
                        ];
                    }
                }
                if(!empty($inv)){
                    update_post_meta($post_id,'b2_ask_inv_users', $inv);
                }
            }

            //问题标签
            if(isset($data['cats']) && count($data['cats']) > 0){
                $cats = [];
                foreach ($data['cats'] as $k => $v) {
                    $cats[] = $k;
                }
                wp_set_object_terms($post_id, $cats, 'ask_cat');
            }

            if($data['post_id']){
                if(!get_post_meta($data['post_id'],'b2_ask_has_reward',true)){

                    $reward = get_post_meta($data['post_id'],'b2_ask_reward',true);
                    if(isset($reward['reward']) && $reward['reward']){
                        
                        self::back_money($data['post_id'],__('您编辑了帖子，重新计算悬赏，返还赏金：${post_id}','b2'));

                        delete_post_meta($data['post_id'],'b2_ask_passtime');
                        delete_post_meta($data['post_id'],'b2_ask_reward');

                    }
                    
                }
            }
        
            if($data['reward']){
                //扣除费用
                self::sub_reward([
                    'user_id'=>$user_id,
                    'post_id'=>$post_id,
                    'reward'=>$data['reward'],
                    'rewardType'=>sanitize_text_field($data['rewardType']),
                    'money'=>$data['money'],
                    'passtime'=>$data['passtime']
                ]);
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

            do_action('b2_user_ask_post_success',$user_id,$post_id);

            $status = get_post_status($post_id);
            if($status == 'pending'){
                return get_post_permalink($post_id).'&viewtoken='.md5(AUTH_KEY.$user_id);
            }else{
                return get_permalink($post_id);
            }

        }

        return ['error'=>__('发布失败','b2')];
    }

    public static function set_inv($post_id){
        $users = get_post_meta($post_id,'b2_ask_inv_users',true);
        $users = is_array($users) ? $users : [];
        $author = get_post_field('post_author',$post_id);

        foreach ($users as $k => $v) {
            Message::update_data([
                'date'=>current_time('mysql'),
                'from'=>$author,
                'to'=>$v['id'],
                'post_id'=>$post_id,
                'msg'=>__('${from}邀请您回答问题：${post_id}','b2'),
                'type'=>'ask_inv',
                'type_text'=>__('邀请回答','b2')
            ]);
        }
    }

    public static function sub_reward($data){

        $gold_type = '';

        if($data['rewardType'] == 'money'){
            $gold_type = 1;
        }else if($data['rewardType'] == 'credit'){
            $gold_type = 0;
        }

        if($gold_type === '') return;
        
        Gold::update_data([
            'date'=>current_time('mysql'),
            'to'=>$data['user_id'],
            'gold_type'=>$gold_type,
            'no'=>-$data['money'],
            'post_id'=>$data['post_id'],
            'msg'=>__('您提交了一篇悬赏提问：${post_id}','b2'),
            'type_text'=>__('悬赏提问','b2'),
            'type'=>'ask_reward'
        ]);

        //记录到期时间
        $end_time = wp_date('Y-m-d H:i:s',wp_strtotime('+'.$data['passtime'].' day'));
        update_post_meta($data['post_id'],'b2_ask_passtime',$end_time);

        update_post_meta($data['post_id'],'b2_ask_reward',$data);
    }

    public static function back_money($post_id,$msg){

        $old = get_post_meta($post_id,'b2_ask_reward',true);

        if(!isset($old['rewardType']) || !isset($old['money'])) return;
        $gold_type = '';

        if($old['rewardType'] == 'money'){
            $gold_type = 1;
        }else if($old['rewardType'] == 'credit'){
            $gold_type = 0;
        }

        Gold::update_data([
            'date'=>current_time('mysql'),
            'to'=>$old['user_id'],
            'gold_type'=>$gold_type,
            'no'=>$old['money'],
            'post_id'=>$post_id,
            'msg'=>$msg,
            'type_text'=>__('退回赏金','b2'),
            'type'=>'ask_reward_back'
        ]);

    }

    public static function get_ask_data($data){

        $data['count'] = isset($data['count']) && (int)$data['count'] && $data['count'] < 50 ? (int)$data['count'] : b2_get_option('ask_main','ask_page_count');

        $paged = isset($data['paged']) ? (int)$data['paged'] : 1;
        // $offset = ($paged -1)*(int)$data['count'];

        $args = [
            'post_status'=>'publish',
            'post_type'=>'ask',
            'posts_per_page' => $data['count'],
            'paged'=>$paged
        ];

        $user_id = b2_get_current_user_id();

        $args['orderby'] = 'date';
        $args['order'] = 'DESC';

        if(isset($data['author']) && $data['author']){
            $args['author'] = (int)$data['author'];
            if($user_id === $args['author'] || user_can( $user_id, 'manage_options' )){
                $args['post_status'] = ['publish','pending'];
            }
        }

        if(isset($data['cat']) && (int)$data['cat'] !== 0){
            $args['tax_query'] = array(
                array (
                    'taxonomy' => 'ask_cat',
                    'field' => 'term_id',
                    'terms' => $data['cat'],
                )
             );
        }

        // $orderby = b2_get_option('ask_main','ask_order');
        // if($orderby == 'qz'){
            $args['meta_query'] = [
                'hot'=>[
                    'key' => 'b2_hotness'
                ]
            ];
            $args['orderby'] = ['hot'=>'DESC'];
        // }

        if(isset($data['s']) && $data['s']){
            $args['search_tax_query'] = true;
            $args['s'] = $data['s'];
        }

        if(isset($data['type'])){
            $args['meta_query']['relation'] = 'AND';
            if($data['type'] == 'waiting'){
                $args['meta_query'][] = [
                    'waiting'=>[
                        'key'=>'b2_has_answer',
                        'compare'=>'NOT EXISTS'
                    ]
                ];
            }elseif($data['type'] == 'last'){
                unset($args['meta_query']['hot']);
                $args['orderby'] = 'DESC';
            }
        }

        $the_query = new \WP_Query( $args );

        $arr = [
            'pages'=>0,
            'data'=>[],
            'paged'=>1
        ];

        if ( $the_query->have_posts()) {
            $_pages = $the_query->max_num_pages;
            while ( $the_query->have_posts() ) {
                $the_query->the_post();

                $post_id = get_the_ID();
 
                $arr['data'][] = self::get_ask_item($post_id,$user_id);
                
            }
            
            $arr['pages'] = $_pages;

            wp_reset_postdata();
        }

        $arr['paged'] = $paged;

        return $arr;
    }

    public static function get_edit_data($data){

        $user_id = b2_get_current_user_id();

        if(!$user_id) return array('error'=>__('请先登录','b2'));
  
        if(!isset($data['id'])) return array('error'=>__('参数错误','b2'));
  
        $data['id'] = (int)$data['id'];
  
        if(get_post_type($data['id']) !== 'ask') return array('error'=>__('参数错误','b2'));
  
        $can_edit = Post::user_can_edit($data['id'],$user_id);
  
        if(!$can_edit) return array('error'=>__('权限不足','b2'));

        $title = get_the_title($data['id']);
        $content = Post::get_write_countent($data['id']);

        $tagsarr = get_the_terms($data['id'], 'ask_cat' );
        $tagsarr = is_array($tagsarr) ? $tagsarr : [];

        $tags = [];

        foreach ($tagsarr as $k => $v) {
            $tags[$v->term_id] = $v->name;
        }

        $inv = get_post_meta($data['id'],'b2_ask_inv_users',true);
        $inv = is_array($inv) ? $inv : [];

        $reward = get_post_meta($data['id'],'b2_ask_reward',true);

        return apply_filters( 'b2_get_ask_edit', [
            'id'=>$data['id'],
            'title'=>$title,
            'content'=>$content,
            'inv'=>$inv,
            'tags'=>$tags,
            'text'=>$reward,
            'reward'=>isset($reward['reward']) ? $reward['reward'] : 0,
            'money'=>isset($reward['money']) ? $reward['money'] : '',
            'rewardType'=>isset($reward['rewardType']) ? $reward['rewardType'] : 'credit',
            'passtime'=>isset($reward['passtime']) ? $reward['passtime'] : '',
            'can_edit'=>Post::user_can_edit($data['id'],$user_id)
        ], $data);
    }

    public static function get_ask_inv_users($post_id){
        $inv = get_post_meta($post_id,'b2_ask_inv_users',true);

        $inv = is_array($inv) ? $inv : [];

        foreach ($inv  as $k => $v) {
            $inv[$k]['avatar_webp'] = apply_filters('b2_thumb_webp',$v['avatar']);
            $inv[$k]['name'] = $v['name'];
            $inv[$k]['link'] = b2_get_custom_page_url('ask-people').'?id='.$v['id'];
        }

        return $inv;
    }

    public static function ask_end_time($post_id){
        $end_time = get_post_meta($post_id,'b2_ask_passtime',true);

        if($end_time){
           return b2Sec2Time(wp_strtotime($end_time) - wp_strtotime(current_time( 'mysql' ))); 
        }

        return false;
    }

    public static function get_ask_item($post_id,$user_id){

        $author = get_post_field('post_author',$post_id);
        $author_data = User::get_user_normal_data($author);

        $author_data['link'] = b2_get_custom_page_url('ask-people').'?id='.$author;

        $title = get_the_title($post_id);
        $desc = b2_get_excerpt($post_id);

        $tagsarr = self::get_ask_tags($post_id);

        $date = get_the_date('Y-m-d H:i:s',$post_id);
        $view = (int)get_post_meta($post_id,'views',true);

        $inv = self::get_ask_inv_users($post_id);

        $favorites = Post::get_post_favorites($user_id,$post_id);

        $end_time = self::ask_end_time($post_id);

        $metas = [
            'date'=>Post::time_ago($date),
            '_date'=>$date,
            'date_c'=>get_the_date('c',$post_id),
            'answer_count'=>self::get_item_answer_count($post_id),
            'inv'=>$inv,
            'favorites_isset'=>$favorites['favorites_isset'],
            'favorites'=>$favorites['favorites'],
            'views'=>b2_number_format($view),
            'reward'=>get_post_meta($post_id,'b2_ask_reward',true),
            'endtime'=>$end_time
        ];

        $thumb = Post::get_post_thumb($post_id,true);

        if($thumb){
            $thumb = b2_get_thumb(['thumb'=>$thumb,'width'=>112,'height'=>112]);
        }

        $last_author = [
            'name'=>''
        ];

        $last = self::get_last_answer($post_id);
        if($last){
            $last_author = User::get_user_normal_data($last);
            $last_author['link'] = b2_get_custom_page_url('ask-people').'?id='.$last;
        }

        $data = apply_filters('b2_get_ask_item', [
            'id'=>$post_id,
            'author'=>$author_data,
            'title'=>$title,
            'desc'=>$desc,
            'tags'=>$tagsarr,
            'thumb'=>$thumb,
            'thumb_webp'=>$thumb ? apply_filters('b2_thumb_webp',$thumb) : '',
            'metas'=>$metas,
            'link'=>get_permalink($post_id),
            'can_edit'=>Post::user_can_edit($post_id,$user_id),
            'last_answer'=>$last_author
        ], $post_id );

        if(!$end_time){
            self::check_money_back($post_id);
        }

        return $data;

    }

    public static function check_money_back($post_id){

        if(get_post_meta($post_id,'b2_ask_has_reward',true)) return;

        $reward = get_post_meta($post_id,'b2_ask_reward',true);

        if(isset($reward['reward']) && $reward['reward']){
            //检查是否有人回答
            if((int)self::get_item_answer_count($post_id) === 0){
                self::back_money($post_id,__('悬赏到期，没有人回答您的问题，返还赏金：${post_id}','b2'));
                update_post_meta($post_id,'b2_ask_has_reward',1);
                return;
            }

            $users = self::get_answer_authors($post_id);

            //每人分得金额
            $pay_one = intval($reward['money']/count($users));

            //如果每人分得小于1，不处理
            if($pay_one < 1) {
                update_post_meta($post_id,'b2_ask_has_reward',1);
                return;
            }

            //所有回答者平分奖励
            foreach ($users as $v) {

                $gold_type = 1;

                if($reward['rewardType'] === 'credit'){
                    $gold_type = 0;
                }

                Gold::update_data([
                    'date'=>current_time('mysql'),
                    'to'=>$v,
                    'gold_type'=>$gold_type,
                    'no'=>$pay_one,
                    'post_id'=>$post_id,
                    'msg'=>sprintf(__('提问者没有选择最佳答案，所有回答者均分奖金 %s ：${post_id}','b2'),$pay_one),
                    'type_text'=>__('奖金均分','b2'),
                    'type'=>'ask_average'
                ]);

            }

            update_post_meta($post_id,'b2_ask_has_reward',1);
        }

        

    }

    //获取回答的用户
    public static function get_answer_authors($post_id){
        $topic_query = new \WP_Query(array(
            'post_parent'=>$post_id,
            'post_type'=>'answer',
            'post_status' => 'publish'
        ));

        $authors = array();

        if ( $topic_query->have_posts()) {
            while ( $topic_query->have_posts() ) {
                $topic_query->the_post();
                $authors[] = $topic_query->post->post_author;;
            }
        }

        wp_reset_postdata();
        array_unique($authors);

        return $authors;
    }

    public static function get_ask_tags($post_id){
        $tagsarr = [];

        $tags = wp_get_post_terms( $post_id, 'ask_cat');

        if(!empty($tags)){
            $colorarr = [
                ['#CC6767','#FFE9E9'],
                ['#8686BF','#EFEFFF'],
                ['#E19B64','#FFF1E6'],
                ['#5CADD1','#EEF7FB'],
                ['#B688C6','#F7ECFB'],
                ['#71A6E8','#E4F1FF']
            ];
            foreach ($tags as $tag) {
                $color = get_term_meta($tag->term_id,'b2_tax_color',true);
                if(!$color) {
                    $rand = rand(0,5);
                    $color = $colorarr[$rand][0];
                    $bg = $colorarr[$rand][1];
                }else{
                    $bg = b2_hex2rgba($color,'0.1');
                }
                
                $tagsarr[] = [
                    'id'=>$tag->term_id,
                    'name'=>$tag->name,
                    'slug'=>$tag->slug,
                    'link'=>get_term_link($tag->slug,'ask_cat'),
                    'color'=>$color,
                    'bgcolor'=>$bg
                ];
            }
        }

        return $tagsarr;
    }

    public static function get_item_answer_count($post_id){

        $count = get_post_meta($post_id,'b2_ask_answer_count',true);
        if($count !== '') return $count;

        global $wpdb;

        $count = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_parent = $post_id AND post_status = 'publish' AND post_type = 'answer'");

        update_post_meta($post_id,'b2_ask_answer_count',$count);

        return $count;
    }

    public static function get_last_answer($post_id){
        $last = get_post_meta($post_id,'b2_ask_answer_last',true);
        if($last !== '') return $last;

        global $wpdb;

        $last_answer = $wpdb->get_results("SELECT post_author FROM $wpdb->posts WHERE post_parent = $post_id AND post_status = 'publish' AND post_type = 'answer' ORDER BY ID DESC LIMIT 0,1");
      
        if(isset($last_answer[0]->post_author)){
            $last_answer = $last_answer[0];

            update_post_meta($post_id,'b2_ask_answer_last',$last_answer->post_author);

            return $last_answer->post_author;
        }

        return false;
    }

    public static function po_ask_answer($data){
        $user_id = b2_get_current_user_id();

        if(!$user_id) return ['error'=>__('请先登陆','b2')];

        wp_set_current_user($user_id);

        $data['parent_id'] = isset($data['parent_id']) && $data['parent_id'] ? (int)$data['parent_id'] : 0;

        if(!$data['parent_id']) return ['error'=>__('非法操作','b2')];

        if(get_post_type($data['parent_id']) !== 'ask') return ['error'=>__('非法操作','b2')];

        //检查3小时内发布总数
        $post_count_3 = User::check_post($user_id);
        if(isset($post_count_3['error'])) return $post_count_3;

        $public_count = apply_filters('b2_check_repo_before', $user_id);
        if(isset($public_count['error'])) return $public_count;

        //检查是否有权限
        $role = User::check_user_role($user_id,'answer');

        if(!$role && !user_can( $user_id, 'manage_options' ) && !user_can( $user_id, 'editor' )) return array('error'=>__('您没有权限回答问题','b2'));

        if(!$data['content']){
            return array('error'=>__('内容不可为空','b2'));
        }

        $data['title'] = b2_get_des(0,60,$data['content']);

        $censor = apply_filters('b2_text_censor', $data['content']);
        if(isset($censor['error'])) return $censor;

        $post_count = (int)b2_get_option('ask_submit','ask_can_answer');

        if(!user_can( $user_id, 'manage_options' ) && !user_can( $user_id, 'editor' )){
            //检查是否有草稿
            $args=array(
                'post_type' => 'answer',
                'post_status' => 'pending',
                'posts_per_page' => $post_count ? $post_count+1 : 3,
                'author' => $user_id
            );

            $posts = get_posts($args);

            if(count($posts) >= $post_count){
                return array('error'=>__('您还有未审核的回答，请审核完后再提交','b2'));
            }
        }

        $data['post_id'] = isset($data['post_id']) ? (int)$data['post_id'] : null;

        //检查文章作者
        if($data['post_id']){
            if((get_post_field( 'post_author', $data['post_id'] ) != $user_id || get_post_type($data['post_id']) != 'answer') && !user_can($user_id, 'administrator' ) && !user_can( $user_id, 'editor' )){
                return array('error'=>__('非法操作','b2'));
            }
        }

        $post_id = false;

        if((user_can( $user_id, 'manage_options' ) || user_can( $user_id, 'editor' ))){
            $data['type'] = 'publish';

        }else{
            $data['type'] = 'pending';
        }

        $can_publish = User::check_user_media_role($user_id,'answer');
        if($can_publish){
            $data['type'] = 'publish';
        }

        $post_author = null;

        if($data['post_id']){
            $post_author = get_post_field( 'post_author', $data['post_id'] );
        }

        $arg = array(
            'ID'=> $data['post_id'] ? $data['post_id'] : null,
            'post_title'=>$data['title'],
            'post_content' => wp_slash($data['content']),
            'post_status' => $data['type'],
            'post_author' => $post_author,
            'post_parent'=>$data['parent_id'],
            'post_type'=>'answer'
        );

        if($data['post_id']){
            $post_id = wp_update_post($arg);
        }else{
            $post_id = wp_insert_post($arg);
        }

        if($post_id){
            if($data['type'] == 'publish'){
                do_action('b2_ask_hotness',$data['parent_id']);
                do_action('b2_rebuild_hotness',$post_id);
            }
            
            return self::get_answer_item($post_id,$user_id);
        }

        return ['error'=>__('发布失败','b2')];
    }

    public static function get_answer_data($data){
        $data['parent_id'] = (int)$data['parent_id'];

        $user_id = b2_get_current_user_id();

        $data['count'] = isset($data['count']) ? (int)$data['count'] : 15;
        $data['paged'] = isset($data['paged']) ? (int)$data['paged'] : 1;

        $args = [
            'post_status'=>'publish',
            'post_type'=>'answer',
            'post_parent'=>$data['parent_id'],
            'posts_per_page' => $data['count'],
            'paged'=>$data['paged']
        ];

        $args['meta_query'] = [
            'hot'=>[
                'key' => 'b2_hotness'
            ]
        ];

        $args['orderby'] = ['date'=>'ASC','hot'=>'DESC'];

        if(isset($data['fliter']) && $data['fliter'] == 'date'){
            $args['orderby'] = ['date'=>'DESC','hot'=>'DESC'];
        }

        $data['author_id'] = isset($data['author_id']) ? (int)$data['author_id'] : 0;

        if($data['author_id']){
            $args['author'] = (int)$data['author_id'];
            $args['orderby'] = ['date'=>'DESC'];

            if($data['author_id'] == $user_id || user_can( $user_id, 'manage_options' )){
                $args['post_status'] = ['publish','pending'];
            }

            unset($args['meta_query']);
            unset($args['post_parent']);
        }

        $the_query = new \WP_Query( $args );

        $arr = [
            'pages'=>0,
            'data'=>[],
            'paged'=>1
        ];

        if ( $the_query->have_posts()) {
            $_pages = $the_query->max_num_pages;
            while ( $the_query->have_posts() ) {
                $the_query->the_post();

                $post_id = get_the_ID();
 
                $arr['data'][] = self::get_answer_item($post_id,$user_id,$data['author_id']);
                
            }
            
            $arr['pages'] = $_pages;

            wp_reset_postdata();
        }

        $arr['paged'] = $data['paged'];

        return $arr;
    }

    public static function get_answer_item($post_id,$user_id,$author_id = 0){
        if(!$post_id) return [];

        $author = get_post_field( 'post_author', $post_id);

        $author_data = User::get_user_public_data($author,true);

        $following = get_user_meta($user_id,'zrz_follow',true);
        $following = is_array($following) ? $following : array();

        $key_following = array_search((int)$author,$following);

        $author_data['followed'] = $key_following === false ? false : true;
        $author_data['link'] = b2_get_custom_page_url('ask-people').'?id='.$author.'&type=answer';

        if(!$author_id){
            $content = get_post_field('post_content', $post_id);
            $content = Post::b2_lazyload_action($content);
            $content = apply_filters('the_content',$content );
        }else{
            $content = '<p>'.b2_get_des($post_id,100).'</p>';
        }
        
        $view = (int)get_post_meta($post_id,'views',true);

        $date = get_the_date('Y-m-d H:i:s',$post_id);
        $favorites = Post::get_post_favorites($user_id,$post_id);

        $post_vote = Post::get_post_vote_up($post_id);

        $data = [
            'post_id'=>$post_id,
            'comment'=>b2_number_format(get_comments_number($post_id)),
            'views'=>b2_number_format($view),
            'link'=>get_permalink($post_id),
            'content'=>$content,
            // 'date'=>Post::time_ago($date),
            'date'=>$date,
            'date_normal'=>get_the_date('Y-m-d',$post_id),
            'ctime'=>get_the_date('c',$post_id),
            'author'=>$author_data,
            'favorites_isset'=>$favorites['favorites_isset'],
            'favorites'=>$favorites['favorites'],
            'post_status'=>get_post_status($post_id),
            'can_edit'=>Post::user_can_edit($post_id,$user_id),
            'self'=>$author == $user_id ? true : false,
            'vote'=>array(
                'locked'=>false,
                'up'=>$post_vote['up'],
                'down'=>$post_vote['down'],
                'isset_up'=>PostRelationships::isset(array('type'=>'post_up','user_id'=>$user_id,'post_id'=>$post_id)),
                'isset_down'=>PostRelationships::isset(array('type'=>'post_down','user_id'=>$user_id,'post_id'=>$post_id))
            ),
            'best'=>(int)get_post_meta($post_id,'b2_answer_best',true)
        ];

        if($author_id){
            $parent = get_post_field('post_parent',$post_id);
            $data['parent_data'] = [
                'title'=>get_the_title($parent),
                'id'=>$parent,
                'link'=>get_permalink( $parent),
                'desc'=>b2_get_des($parent,100)
            ];
        }

        return $data;

    }

    //问答采纳
    public static function answer_right($data){

        $user_id = (int)b2_get_current_user_id();

        if(!$user_id) return array('error'=>__('请先登录','b2'));

        $answer_id = (int)$data['answer_id'];

        $parent = (int)get_post_field('post_parent', $answer_id);

        //提问者
        $asker = (int)get_post_field('post_author', $parent);

        $answer = (int)get_post_field('post_author', $answer_id);

        if($user_id !== $asker){
            return array('error'=>__('无权操作！','b2'));
        }

        if($answer === $asker){
            return array('error'=>__('不能采纳自己的答案','b2'));
        }

        $best = get_post_meta($parent,'b2_ask_best',true);
        if($best) return array('error'=>__('该问题已有最佳答案','b2'));

        update_post_meta($parent,'b2_ask_best',$answer_id);
        update_post_meta($answer_id,'b2_answer_best',1);

        $pay_type = get_post_meta($parent,'b2_ask_reward',true);

        if(get_post_meta($parent,'b2_ask_has_reward',true)) return true; 
        if(isset($pay_type['reward']) && $pay_type['reward']){

            if(!self::ask_end_time($parent)) return false;

            if(!isset($pay_type['rewardType']) || !isset($pay_type['money'])) return ['error'=>__('参数错误','b2')];

            $gold_type = 1;
    
            if($pay_type['rewardType'] === 'credit'){
                $gold_type = 0;
            }
    
            if($pay_type['money'] <= 0 || $pay_type['money'] > 9999999) return ['error'=>__('参数错误','b2')];
    
            Message::update_data([
                'date'=>current_time('mysql'),
                'from'=>$asker,
                'to'=>$answer,
                'post_id'=>$parent,
                'msg'=>__('您的回答被${from}采纳了：${post_id}','b2'),
                'type'=>'best_answer',
                'type_text'=>__('最佳答案','b2')
            ]);
    
            Gold::update_data([
                'date'=>current_time('mysql'),
                'to'=>$answer,
                'gold_type'=>$gold_type,
                'no'=>$pay_type['money'],
                'post_id'=>$parent,
                'msg'=>sprintf(__('您的回答被采纳了，奖励 %s ：${post_id}','b2'),$pay_type['money']),
                'type_text'=>__('最佳答案','b2'),
                'type'=>'best_answer'
            ]);
            update_post_meta($parent,'b2_ask_has_reward',1);
        }

        return true;

    }

    public static function get_edit_answer_data($data){
        if(!isset($data['answer_id'])) return ['error'=>__('参数错误','b2')];
        $data['answer_id'] = (int)$data['answer_id'];
        $user_id = b2_get_current_user_id();
        if(Post::user_can_edit($data['answer_id'],$user_id)){
            return get_post_field('post_content',$data['answer_id']);
        }

        return ['error'=>__('权限不足','b2')];
    }
}