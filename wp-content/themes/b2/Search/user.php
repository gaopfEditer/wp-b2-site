<?php

    $key = get_search_query();

    $count = 16;

    $paged = get_query_var('paged') ? get_query_var('paged') : 1;
    $offset = ($paged -1)*$count;

    $users = new \WP_User_Query( array(
        'search'         => '*'.$key.'*',
        'search_columns' => array(
            'display_name',
            'user_id'
        ),
        'number'=>$count,
        'offset'=>$offset
    ) );

    $users_found = $users->get_results();
    $total = $users->get_total();

    $pages = ceil($total/$count);


?>

<?php do_action('b2_normal_archive_before'); ?>
<div class="archive-row">
    <div id="user-list" class="">
        <?php $ids = array(); if($users_found){
            echo '<div class="hidden-line"><ul class="user-search-list" ref="searchUser">';
            
            foreach ($users_found as $user) {
                $user_data = B2\Modules\Common\User::get_user_public_data($user->ID);
                $user_lv = B2\Modules\Common\User::get_user_lv($user->ID);
                $user_vip = isset($user_lv['vip']['icon']) ? $user_lv['vip']['icon'] : '';
                $user_lv = isset($user_lv['lv']['icon']) ? $user_lv['lv']['icon'] : '';

                $tips = __('这个人很懒，什么都没有留下！','b2');

                $following = get_user_meta($user->ID,'zrz_follow',true);
                $following = is_array($following) ? count($following) : 0;
                
                $followers = get_user_meta($user->ID,'zrz_followed',true);
                $followers = is_array($followers) ? count($followers) : 0;
                $ids[] = $user->ID;

                echo '<li>
                    <div class="box b2-radius">
                        <div class="user-s-cover">
                        '.b2_get_img(array('src'=>$user_data['cover'])).'
                        <a href="'.$user_data['link'].'" class="link-block"></a></div>
                        <div class="user-s-info">
                        <a href="'.$user_data['link'].'" class="link-block"></a>
                            <div class="user-s-info-avatar avatar-parent">
                                '.b2_get_img(array('src'=>$user_data['avatar'],'class'=>array('avatar','b2-radius'))).'
                                '.($user_data['user_title'] ? $user_data['verify_icon'] : '').'
                            </div>
                            <div class="user-s-info-name">
                                <h2>'.$user_data['name'].'</h2>
                                <p>'.$user_vip.$user_lv.'</p>
                            </div>
                        </div>
                        <div class="user-s-data">
                            <div class="">
                                <span>'.__('文章','b2').'</span>
                                <p>'.count_user_posts($user->ID,'post').'</p>
                            </div>
                            <div class="">
                                <span>'.__('评论','b2').'</span>
                                <p>'.B2\Modules\Common\Comment::get_user_comment_count($user->ID).'</p>
                            </div>
                            <div class="">
                                <span>'.__('粉丝','b2').'</span>
                                <p>'.$followers.'</p>
                            </div>
                            <div class="">
                                <span>'.__('关注','b2').'</span>
                                <p>'.$following.'</p>
                            </div>
                        </div>
                        <div class="user-s-info-desc">
                                '.($user_data['user_title'] ? $user_data['user_title'] : ($user_data['desc'] ? $user_data['desc'] : $tips)).'
                            </div>
                        <div class="user-s-follow">
                            <button class="author-has-follow" v-if="follow['.$user->ID.'] === true" v-cloak @click="followAc('.$user->ID.')">'.__('已关注','b2').'</button>
                            <button class="empty" v-else v-cloak @click="followAc('.$user->ID.')">'.__('关注','b2').'</button>
                            <button @click="dmsg('.$user->ID.')">'.__('私信','b2').'</button>
                        </div>
                    </div>
                </li>';
            }
            echo '</ul></div>';  
        ?>

        <?php }else{
            echo '<div class="box b2-radius">'.B2_EMPTY.'</div>';
        } 

        wp_localize_script( 'b2-js-main', 'b2_search_data', array(
            'users'=>$ids
        ))
        ?>
    </div>
</div>
<?php do_action('b2_normal_archive_after'); ?>
<?php if($pages > 1){ ?>
    <div class="b2-pagenav post-nav box b2-radius mg-t">
        <?php echo b2_pagenav(array('pages'=>$pages,'paged'=>$paged)); ?>
    </div>
<?php } ?>