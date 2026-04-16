<?php namespace B2\Modules\Templates\Modules;

use B2\Modules\Templates\Modules\Sliders;
use B2\Modules\Common\Post;

class Posts{

    /**
     * 文章模块启动
     *
     * @param array $data 设置数据
     * @param int $i 第几个模块
     *
     * @return string
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public function init($data,$i,$return = false){
        if(!$data) return;

        if(!isset($data['post_type']) || $data['post_type'] == '') return;
        $type = str_replace('-','_',$data['post_type']);

        return self::$type($data,$i,$return);
    }

    /**
     * 获取文章列表html(post_1)
     *
     * @param array $data 设置项数据
     * @param int $i 第几个模块
     * @param bool $return 是否直接返回 li 标签中的 html 代码，用作ajax加载
     *
     * @return string
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function post_1($data,$i,$return = false){

        $index = $i;

        $i = isset($data['key']) ? $data['key'] : 'ls'.round(100,999);

        $_post_data = self::get_post_data($data);

        $post_data = $_post_data['data'];

        //是否新窗口打开
        $open = self::open_type($data);

        //显示哪些post_meta
        $post_meta = isset($data['post_meta']) && $data['post_meta'] ? $data['post_meta'] : array();

        //计算宽度
        $size = self::get_thumb_size($data,$data['post_row_count']);

        //分类列表
        $cats_list = self::get_cats($data,'category',$i,$_post_data['pages'],$index);

        $html = '';

        $data['post_load_more'] = isset($data['post_load_more']) && $data['post_load_more'] && $_post_data['pages'] > 1 ? true : false;

        $user_id = b2_get_current_user_id();

        foreach ($post_data as $k => $v) {

            $post_style = apply_filters('b2_post_1_get_type', get_post_meta($v['id'],'b2_single_post_style',true));

            //$post_style = 'post-style-1';

            $thumb = b2_get_thumb(array(
                'thumb'=>$v['thumb'],
                'width'=>$size['w'],
                'height'=>$post_style === 'post-style-3' ? $size['h']*2 : $size['h'],
                'ratio'=>2
            ));

            //文章分类
            $cats = self::get_post_cats($open,$v,$post_meta,'post_1');
            
            //显示哪些post_meta
            $meta_html = '';
            if(!empty($post_meta)){
                $meta_html .= '<ul class="post-list-meta">';
                
                foreach($post_meta as $meta){
                    if($meta === 'like'){
                        $meta_html .= '<li class="post-list-meta-like"><span>'.b2_get_icon('b2-heart-fill').$v['like'].'</span></li>';
                    }elseif($meta === 'comment'){
                        $meta_html .= '<li class="post-list-meta-comment"><span>'.b2_get_icon('b2-chat-2-fill').$v['comment'].'</span></li>';
                    }elseif($meta === 'views'){
                        $meta_html .= '<li class="post-list-meta-views"><span>'.b2_get_icon('b2-eye-fill').$v['views'].'</span></li>';
                    }
                }unset($meta);

                $meta_html .= '</ul>';
            }

            if($meta_html || $cats){
                $meta_html = '
                    <div class="post-list-meta-box">
                        '.$cats.$meta_html.'
                    </div>
                ';
            }

            $hove_avatar = in_array('user',$post_meta);
            $avatar = $hove_avatar ? '<a class="post-list-meta-avatar" href="'.$v['user_link'].'">'.b2_get_img(array('src'=>$v['user_avatar'],'class'=>array('avatar','b2-radius'),'alt'=>$v['user_name'])).'<span>'.$v['user_name'].'</span></a>' : '';

            $hove_date = in_array('date',$post_meta);
            $date = $hove_date ? '<span>'.$v['date'].'</span>' : '';

            $list_footer = '';
            if($hove_avatar || $hove_date){
                $list_footer = '<div class="list-footer">
                    '.$avatar.$date.'
                </div>';
            }

            if(in_array('edit',$post_meta)){
                $user_id = b2_get_current_user_id();

                $time = Post::user_can_edit($v['id'],$user_id);

                if($time === 'long' || $time){
                    $list_footer = ($time !== 'long' ? '<span class="allow-edit-time">'.sprintf(__('%s分钟内允许编辑','b2'),'<b>'.$time.'</b>').'</span>' : '').'<div class="list-footer">'.$date.'<div><a class="button text" href="'.b2_get_custom_page_url('write').'?id='.$v['id'].'">'.__('编辑','b2').'</a><button class="text" onclick="b2AuthorPost.delete('.$v['id'].')">'.__('删除','b2').'</button></div></div>';
                }
            }

            if($v['status'] === 'pending' || $v['status'] === 'draft'){
                $v['link'] = get_post_permalink($v['id']).'&viewtoken='.md5(AUTH_KEY.$user_id);
            }

            $pay_type = self::get_post_pay_data($v['pay_type'],$post_meta);

            //文章列表
            $html .= '<li class="post-list-item item-'.$post_style.'" id="item-'.$v['id'].'">
                <div class="item-in box b2-radius">
                    <div class="post-module-thumb" style="padding-top:'.$size['ratio'].'%">
                        <a '.$open.' href="'.$v['link'].'" rel="nofollow" class="thumb-link">'.b2_get_img(array('src'=>$thumb,'class'=>array('post-thumb'),'alt'=>$v['title'])).self::get_post_icon($v['id']).'</a>
                        '.($v['status'] === 'pending' ? '<span class="post-status">'.__('审核中...','b2').'</span>' : ($v['status'] === 'draft' ? '<span class="post-status">'.__('草稿','b2').'</span>' : '')).'
                        '.$pay_type.'
                    </div>
                    <div class="post-info">
                        <h2><a '.$open.' href="'.$v['link'].'">'.$v['title'].'</a></h2>
                        '.(in_array('des',$post_meta) ? 
                        '<div class="post-excerpt">
                            '.$v['des'].'
                        </div>' : 
                        '').'
                        '.$meta_html.'
                        '.$list_footer.'
                    </div>
                </div>
            </li>';
        }unset($v);

        if($return){
            return array(
                'count'=>$_post_data['count'],
                'index'=>$i,
                'pages'=>$_post_data['pages'],
                'data'=>$html
            );
        }

        $post_meta = isset($data['post_meta']) && is_array($data['post_meta']) ? $data['post_meta'] : array();

        $r = ((floor((1/$data['post_row_count'])*10000)/10000)*100);

        $title_row = isset($data['post_title_row']) && $data['post_title_row'] ? $data['post_title_row'] : 1;
        $title_row_m = isset($data['post_title_row_mobile']) && $data['post_title_row_mobile'] ? $data['post_title_row_mobile'] : 1;

        return '
        <style>
            .post-item-'.$i.' ul.b2_gap > li{
                width:'.$r.'%
            }
            .post-item-'.$i.' .item-in .post-info h2{
                -webkit-line-clamp: '.$title_row.';
            }
            @media screen and (max-width: 768px){
                .post-item-'.$i.' .item-in .post-info h2{
                    -webkit-line-clamp: '.$title_row_m.';
                }
            }
        </style>
        <div class="'.$data['post_type'].' post-list post-item-'.$i.'" id="post-item-'.$i.'" data-key="'.$i.'" data-i="'.$index.'">
            <div class="post-modules-top'.(!in_array('links',$post_meta) && !in_array('title',$post_meta) ? 'b2-hidden' : '').' '.(!in_array('title',$post_meta) ? 'cats-full' : '').'">
                '.$cats_list.'
            </div>
            <div class="hidden-line">
                <ul class="b2_gap">'.$html.'</ul>
            </div>
            <div v-show="showButton" @click="getList(\'\',\'more\',\''.$data['post_type'].'\')" class="load-more box-in box b2-radius mg-t '.($data['post_load_more'] ? '' : 'load-more-hidden').'" data-showButton="'.($data['post_load_more'] ? true : false).'"><button class="empty post-load-button" :disabled="locked || finish"><span v-if="locked && !finish" v-cloak>'.__('加载中...','b2').'</span><span v-else-if="finish" v-cloak>'.__('没有更多了','b2').'</span><span v-else>'.__('加载更多','b2').'</span></button></div>
        </div>';
    }

    /**
     * 获取文章列表html(post_2)
     *
     * @param array $data 设置项数据
     * @param int $i 第几个模块
     * @param bool $return 是否直接返回 li 标签中的 html 代码，用作ajax加载
     *
     * @return string
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function post_2($data,$i,$return = false){

        $index = $i;

        $i = isset($data['key']) ? $data['key'] : 'ls'.round(100,999);

        $_post_data = self::get_post_data($data);
        $post_data = $_post_data['data'];

        //是否新窗口打开
        $open = self::open_type($data);

        //显示哪些post_meta
        $post_meta = isset($data['post_meta']) && $data['post_meta'] ? $data['post_meta'] : array();

        //计算宽度
        $size = self::get_thumb_size($data,$data['post_row_count']);

        //分类列表
        $cats_list = self::get_cats($data,'category',$i,$_post_data['pages'],$index);

        $data['post_load_more'] = isset($data['post_load_more']) && $data['post_load_more'] && $_post_data['pages'] > 1 ? true : false;

        $html = '';

        foreach ($post_data as $k => $v) {
            $thumb = b2_get_thumb(array(
                'thumb'=>$v['thumb'],
                'width'=>$size['w'],
                'height'=>$v['thumb_ratio'] == 0 ? $size['h'] : '100%',
                'ratio'=>2
            ));

            $avatar = in_array('user',$post_meta) ? '<a class="post-list-meta-avatar" href="'.$v['user_link'].'">'.b2_get_img(array('src'=>$v['user_avatar'],'class'=>array('avatar','b2-radius'),'alt'=>$v['user_name'])).'<span>'.$v['user_name'].'</span></a>' : '';
            
            //文章分类
            $cats = self::get_post_cats($open,$v,$post_meta,'post_2');
            
            //显示哪些post_meta
            $meta_html = '';
            if(!empty($post_meta)){
                $meta_html .= '<ul class="post-list-meta">';
                foreach($post_meta as $meta){
                    if($meta === 'like'){
                        $meta_html .= '<li class="post-list-meta-like"><span>'.b2_get_icon('b2-heart-fill').$v['like'].'</span></li>';
                    }elseif($meta === 'comment'){
                        $meta_html .= '<li class="post-list-meta-comment"><span>'.b2_get_icon('b2-chat-2-fill').$v['comment'].'</span></li>';
                    }elseif($meta === 'views'){
                        $meta_html .= '<li class="post-list-meta-views"><span>'.b2_get_icon('b2-eye-fill').$v['views'].'</span></li>';
                    }
                }unset($meta);

                $meta_html .= '</ul>';
            }
            
            $height = $v['thumb_ratio'] == 0 ? $size['h'] : intval($size['w']/$v['thumb_ratio']);

            if($meta_html || $cats){
                $meta_html = '
                    <div class="post-list-meta-box">
                        '.$cats.$meta_html.'
                    </div>
                ';
            }

            $hove_avatar = in_array('user',$post_meta);
            $avatar = $hove_avatar ? '<a class="post-list-meta-avatar" href="'.$v['user_link'].'">'.b2_get_img(array('src'=>$v['user_avatar'],'class'=>array('avatar','b2-radius'),'alt'=>$v['user_name'])).'<span>'.$v['user_name'].'</span></a>' : '';

            $hove_date = in_array('date',$post_meta);
            $date = $hove_date ? '<span>'.$v['date'].'</span>' : '';

            $list_footer = '';
            if($hove_avatar || $hove_date){
                $list_footer = '<div class="list-footer">
                    '.$avatar.$date.'
                </div>';
            }

            $pay_type = self::get_post_pay_data($v['pay_type'],$post_meta);

            //文章列表
            $html .= '<li class="grid-item post-list-item" id="item-'.$v['id'].'">
                <div class="item-in box b2-radius">
                    <div class="post-module-thumb" style="padding-top:'.($v['thumb_ratio']*100).'%">
                        <a '.$open.' href="'.$v['link'].'" rel="nofollow" class="thumb-link">'.b2_get_img(array('src'=>$thumb,'class'=>array('post-thumb'),'alt'=>$v['title'])).self::get_post_icon($v['id']).'</a>
                        '.$pay_type.'
                    </div>
                    <div class="post-info">
                        <h2><a '.$open.' href="'.$v['link'].'">'.$v['title'].'</a></h2>
                        '.(in_array('des',$post_meta) ? 
                        '<div class="post-excerpt">
                            '.$v['des'].'
                        </div>' : 
                        '').'
                        '.$meta_html.'
                        '.$list_footer.'
                    </div>
                </div>
            </li>';
        }unset($v);

        if($return){
            return array(
                'count'=>$_post_data['count'],
                'index'=>$i,
                'pages'=>$_post_data['pages'],
                'data'=>$html
            );
        }

        $post_meta = isset($data['post_meta']) && is_array($data['post_meta']) ? $data['post_meta'] : array();

        $r = ((floor((1/$data['post_row_count'])*10000)/10000)*100);

        $title_row = isset($data['post_title_row']) && $data['post_title_row'] ? $data['post_title_row'] : 1;
        $title_row_m = isset($data['post_title_row_mobile']) && $data['post_title_row_mobile'] ? $data['post_title_row_mobile'] : 1;
        return '
        <style>
            .post-item-'.$i.' ul.b2_gap > li{
                width:'.$r.'%
            }

            .post-item-'.$i.' .item-in .post-info h2{
                -webkit-line-clamp: '.$title_row.';
            }
            @media screen and (max-width: 768px){
                .post-item-'.$i.' .item-in .post-info h2{
                    -webkit-line-clamp: '.$title_row_m.';
                }
            }
        </style>
        <div class="'.$data['post_type'].' post-list post-item-'.$i.'" id="post-item-'.$i.'" data-key="'.$i.'" data-i="'.$index.'">
            <div class="post-modules-top '.(!in_array('links',$post_meta) && !in_array('title',$post_meta) ? 'b2-hidden' : '').' '.(!in_array('title',$post_meta) ? 'cats-full' : '').'">
                '.$cats_list.'
            </div>
            <div class="hidden-line">
                <ul class="b2_gap grid">'.$html.'</ul>
            </div>
            <div v-show="showButton" @click="getList(\'\',\'more\',\''.$data['post_type'].'\')" class="load-more box-in box b2-radius mg-t '.($data['post_load_more'] ? '' : 'load-more-hidden').'" data-showButton="'.($data['post_load_more'] ? true : false).'"><button class="empty post-load-button" :disabled="locked || finish"><span v-if="locked && !finish" v-cloak>'.__('加载中...','b2').'</span><span v-else-if="finish" v-cloak>'.__('没有更多了','b2').'</span><span v-else>'.__('加载更多','b2').'</span></button></div>
        </div>';
    }

    /**
     * 获取文章列表html(post_3)
     *
     * @param array $data 设置项数据
     * @param int $i 第几个模块
     * @param bool $return 是否直接返回 li 标签中的 html 代码，用作ajax加载
     *
     * @return string
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function post_3($data,$i,$return = false){
        
        $index = $i;

        $i = isset($data['key']) ? $data['key'] : 'ls'.round(100,999);

        $_post_data = self::get_post_data($data);

        $post_data = $_post_data['data'];

        //是否新窗口打开
        $open = self::open_type($data);

        //显示哪些post_meta
        $post_meta = isset($data['post_meta']) && $data['post_meta'] ? $data['post_meta'] : array();

        //计算宽度
        $size = self::get_thumb_size($data,$data['post_row_count']);

        //分类列表
        $cats_list = self::get_cats($data,'category',$i,$_post_data['pages'],$index);

        $data['post_load_more'] = isset($data['post_load_more']) && $data['post_load_more'] && $_post_data['pages'] > 1 ? true : false;

        $html = '';

        foreach ($post_data as $k => $v) {
            $thumb = b2_get_thumb(array(
                'thumb'=>$v['thumb'],
                'width'=>$size['w'],
                'height'=>$size['h'],
                'ratio'=>2
            ));

            $m_thumb = b2_get_thumb(array(
                'thumb'=>$v['thumb'],
                'width'=>$size['m_w'],
                'height'=>$size['m_h'],
                'ratio'=>2
            ));

            //文章分类
            $cats = self::get_post_cats($open,$v,$post_meta,'post_3');
            
            //显示哪些post_meta
            $meta_html = '';
            if(!empty($post_meta)){
                $meta_html .= '<ul class="post-list-meta">';

                foreach($post_meta as $meta){
                    if($meta === 'user'){
                        $meta_html .= '<li class="post-list-meta-user"><a class="post-list-meta-avatar" href="'.$v['user_link'].'">'.b2_get_img(array('src'=>$v['user_avatar'],'class'=>array('avatar','b2-radius'),'alt'=>$v['user_name'])).'<span>'.$v['user_name'].'</span></a></li>';
                    }elseif($meta === 'date'){
                        $meta_html .= '<li class="post-list-meta-date"><span>'.$v['date'].'</span></li>';
                    }elseif($meta === 'like'){
                        $meta_html .= '<li class="post-list-meta-like"><span>'.b2_get_icon('b2-heart-fill').$v['like'].'</span></li>';
                    }elseif($meta === 'comment'){
                        $meta_html .= '<li class="post-list-meta-comment"><span>'.b2_get_icon('b2-chat-2-fill').$v['comment'].'</span></li>';
                    }elseif($meta === 'views'){
                        $meta_html .= '<li class="post-list-meta-views"><span>'.b2_get_icon('b2-eye-fill').$v['views'].'</span></li>';
                    }
                }unset($meta);

                $meta_html .= '</ul>';
            }

            if($meta_html || $cats){
                $meta_html = '
                    <div class="post-list-meta-box '.(strpos($meta_html,'</li>') === false ? 'b2-hidden-always' : '').'">
                    '.$meta_html.'
                    </div>
                ';
            }

            $pay_type = self::get_post_pay_data($v['pay_type'],$post_meta);

            //文章列表
            $html .= '<li class="post-3-li post-list-item" id="item-'.$v['id'].'">
                <div class="item-in '.($data['post_row_count'] > 1 ? 'box b2-radius' : '').'">
                    <div class="post-module-thumb mobile-hidden" style="">
                        <div style="padding-top:'.($size['ratio']).'%" class="b2-radius">
                            '.$pay_type.'
                            <a '.$open.' href="'.$v['link'].'" rel="nofollow" class="thumb-link">'.b2_get_img(array('src'=>$thumb,'class'=>array('post-thumb'),'alt'=>$v['title'])).self::get_post_icon($v['id']).'</a>
                        </div>
                    </div>
                    <div class="post-module-thumb mobile-show" style="">
                        <div style="padding-top:'.($size['m_ratio']).'%" class="b2-radius">
                            '.$pay_type.'
                            <a '.$open.' href="'.$v['link'].'" rel="nofollow" class="thumb-link">'.b2_get_img(array('src'=>$m_thumb,'class'=>array('post-thumb'),'alt'=>$v['title'])).self::get_post_icon($v['id']).'</a>
                        </div>
                    </div>
                    <div class="post-info">
                        <div>'.$cats.'</div>
                        <h2><a '.$open.' href="'.$v['link'].'">'.$v['title'].'</a></h2>
                        '.(in_array('des',$post_meta) ? 
                        '<div class="post-excerpt">
                            '.$v['des'].'...
                        </div>' : 
                        '').'
                        '.$meta_html.'
                    </div>
                </div>
            </li>';
        }unset($v);

        if($return){
            return array(
                'count'=>$_post_data['count'],
                'index'=>$i,
                'pages'=>$_post_data['pages'],
                'data'=>$html
            );
        }

        $post_meta = isset($data['post_meta']) && is_array($data['post_meta']) ? $data['post_meta'] : array();

        $r = ((floor((1/$data['post_row_count'])*10000)/10000)*100);

        $title_row = isset($data['post_title_row']) && $data['post_title_row'] ? $data['post_title_row'] : 1;
        $title_row_m = isset($data['post_title_row_mobile']) && $data['post_title_row_mobile'] ? $data['post_title_row_mobile'] : 1;

        return '
        <style>
            .post-item-'.$i.' ul.b2_gap > li .post-module-thumb{
                width:'.$size['w'].'px;min-width:'.$size['w'].'px;
            }
            .post-item-'.$i.' .item-in .post-info h2{
                -webkit-line-clamp: '.$title_row.';
            }
            @media screen and (max-width:720px){
                .post-item-'.$i.' ul.b2_gap > li .post-module-thumb{
                    width:'.$size['m_w'].'px;min-width:'.$size['m_w'].'px;
                }
                .post-item-'.$i.' .item-in .post-info h2{
                    -webkit-line-clamp: '.$title_row_m.';
                }
            }
            '.($data['post_row_count'] > 1 ? 
            '
            @media screen and (min-width:720px){
                .post-item-'.$i.' ul.b2_gap > li + li{margin:0}
                .post-item-'.$i.' ul.b2_gap > li{width:'.$r.'%}
                .post-item-'.$i.' ul.b2_gap > li:nth-last-child(2),.post-item-'.$i.' ul.b2_gap > li:nth-last-child(1){
                    margin-bottom:0!important
                }
                .post-item-'.$i.' .post-info h2{
                    font-size: 15px;
                    font-weight: 700;
                }
                .post-item-'.$i.' .post-excerpt{
                    -webkit-line-clamp: 1;
                }
                .post-3-li-dubble.post-item-'.$i.' .b2_gap{
                    margin-bottom:0!important
                }
            }
            ' : ''
            ).'
        </style>
        <div class="'.($data['post_row_count'] > 1 ? 'post-3-li-dubble' : '').' '.$data['post_type'].' post-list post-item-'.$i.' '.($data['post_row_count'] == 1 ? 'box b2-radius' : '').'" id="post-item-'.$i.'" data-key="'.$i.'" data-i="'.$index.'">
            <div class="post-modules-top '.(!in_array('links',$post_meta) && !in_array('title',$post_meta) ? 'b2-hidden' : '').' '.(!in_array('title',$post_meta) ? 'cats-full' : '').'">
                '.$cats_list.'
            </div>
            <div class="hidden-line">
                <ul class="b2_gap">'.$html.'</ul>
            </div>
            <div v-show="showButton" @click="getList(\'\',\'more\',\''.$data['post_type'].'\')" class="load-more '.($data['post_row_count'] > 1 ? 'box b2-radius' : '').' '.($data['post_load_more'] ? '' : 'load-more-hidden').'" data-showButton="'.($data['post_load_more'] ? true : false).'"><button class="empty post-load-button" :disabled="locked || finish"><span v-if="locked && !finish" v-cloak>'.__('加载中...','b2').'</span><span v-else-if="finish" v-cloak>'.__('没有更多了','b2').'</span><span v-else>'.__('加载更多','b2').'</span></button></div>
        </div>';
    }

    /**
     * 获取文章列表html(post_4)
     *
     * @param array $data 设置项数据
     * @param int $i 第几个模块
     * @param bool $return 是否直接返回 li 标签中的 html 代码，用作ajax加载
     *
     * @return string
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function post_4($data,$i,$return = false){

        $index = $i;

        $i = isset($data['key']) ? $data['key'] : 'ls'.round(100,999);

        $_post_data = self::get_post_data($data);
        $post_data = $_post_data['data'];

        //是否新窗口打开
        $open = self::open_type($data);

        //显示哪些post_meta
        $post_meta = isset($data['post_meta']) && $data['post_meta'] ? $data['post_meta'] : array();

        //计算宽度
        $size = self::get_thumb_size($data,$data['post_row_count']);

        //分类列表
        $cats_list = self::get_cats($data,'category',$i,$_post_data['pages'],$index);

        $data['post_load_more'] = isset($data['post_load_more']) && $data['post_load_more'] && $_post_data['pages'] > 1 ? true : false;

        $html = '';
        
        $__i = 1;
       
        foreach ($post_data as $k => $v) {

            if($__i > $data['post_row_count']){
                $__i = 1;
            }

            if($__i == 1 && $data['post_row_count'] != 1){
                $w = $size['page_w'];
                $h = intval($size['h']*4.4);

                $arg = array(
                    'thumb'=>$v['thumb'],
                    'width'=>$w,
                    'height'=>$h,
                );
            }else{
                $w = $size['w'];
                $h = $size['h'];

                $arg = array(
                    'thumb'=>$v['thumb'],
                    'width'=>$w,
                    'height'=>$h,
                    'ratio'=>2
                );
            }

            $thumb = b2_get_thumb($arg);

            $avatar = in_array('user',$post_meta) ? '<a class="post-list-meta-avatar" href="'.$v['user_link'].'">'.b2_get_img(array('src'=>$v['user_avatar'],'class'=>array('avatar','b2-radius'),'alt'=>$v['user_name'])).'<span>'.$v['user_name'].'</span></a>' : '';
            
            $cats = self::get_post_cats($open,$v,$post_meta,'post_4');
            
            //显示哪些post_meta
            $meta_html = '';
            if(!empty($post_meta)){
                $meta_html .= '<ul class="post-list-meta">';
                
                foreach($post_meta as $meta){
                    if($meta === 'like'){
                        $meta_html .= '<li class="post-list-meta-like"><span>'.b2_get_icon('b2-heart-fill').$v['like'].'</span></li>';
                    }elseif($meta === 'comment'){
                        $meta_html .= '<li class="post-list-meta-comment"><span>'.b2_get_icon('b2-chat-2-fill').$v['comment'].'</span></li>';
                    }elseif($meta === 'views'){
                        $meta_html .= '<li class="post-list-meta-views"><span>'.b2_get_icon('b2-eye-fill').$v['views'].'</span></li>';
                    }
                }unset($meta);

                $meta_html .= '</ul>';
            }
            
            $r = 100;
            if($data['post_row_count']-1 > 0){
                $r = ((floor((1/($data['post_row_count']-1))*10000)/10000)*100);
                
            }

            if($meta_html || $cats){
                $meta_html = '
                    <div class="post-list-meta-box">
                        '.$cats.$meta_html.'
                    </div>
                ';
            }

            $have_avatar = in_array('user',$post_meta);
            $avatar = $have_avatar ? '<a class="post-list-meta-avatar" href="'.$v['user_link'].'">'.b2_get_img(array('src'=>$v['user_avatar'],'class'=>array('avatar','b2-radius'),'alt'=>$v['user_name'])).'<span>'.$v['user_name'].'</span></a>' : '';
            
            $hove_date = in_array('date',$post_meta);
            $date = $hove_date ? '<span>'.$v['date'].'</span>' : '';

            $list_footer = '';
            if($have_avatar || $hove_date){
                $list_footer = '<div class="list-footer">
                    '.$avatar.$date.'
                </div>';
            }

            $pay_type = self::get_post_pay_data($v['pay_type'],$post_meta);

            //文章列表
            $html .= '<li class="post-list-item '.($__i == 1 ? 'post-4-parent-li' : 'post-4-child-li').'" style="'.($__i == 1 ? 'width:100%!important' : 'width:'.$r.'%').'" id="item-'.$v['id'].'">
                <div class="item-in box b2-radius">    
                    <div class="post-module-thumb" style="padding-top:'.($__i == 1 ? $size['ratio']*0.8 : $size['ratio']).'%">
                        <a '.$open.' href="'.$v['link'].'" rel="nofollow" class="thumb-link">'.b2_get_img(array('src'=>$thumb,'class'=>array('post-thumb'),'alt'=>$v['title'])).self::get_post_icon($v['id']).'</a>
                        '.$pay_type.'
                    </div>
                    <div class="post-info">
                        <h2><a '.$open.' href="'.$v['link'].'">'.$v['title'].'</a></h2>
                        '.(in_array('des',$post_meta) ? 
                        '<div class="post-excerpt">
                            '.$v['des'].'
                        </div>' : 
                        '').'
                        '.$meta_html.'
                        '.$list_footer.'
                    </div>
                </div>
            </li>';

            $__i++;
        }unset($v);

        if($return){
            return array(
                'count'=>$_post_data['count'],
                'index'=>$i,
                'pages'=>$_post_data['pages'],
                'data'=>$html
            );
        }

        $post_meta = isset($data['post_meta']) && is_array($data['post_meta']) ? $data['post_meta'] : array();

        $title_row = isset($data['post_title_row']) && $data['post_title_row'] ? $data['post_title_row'] : 1;
        $title_row_m = isset($data['post_title_row_mobile']) && $data['post_title_row_mobile'] ? $data['post_title_row_mobile'] : 1;

        return '
        <style>
        .post-item-'.$i.' .item-in .post-info h2{
            -webkit-line-clamp: '.$title_row.';
        }
        @media screen and (max-width: 768px){
            .post-item-'.$i.' .item-in .post-info h2{
                -webkit-line-clamp: '.$title_row_m.';
            }
        }
        </style>
        <div class="'.$data['post_type'].' post-list post-item-'.$i.'" id="post-item-'.$i.'" data-key="'.$i.'" data-i="'.$index.'">
            <div class="post-modules-top '.(!in_array('links',$post_meta) && !in_array('title',$post_meta) ? 'b2-hidden' : '').' '.(!in_array('title',$post_meta) ? 'cats-full' : '').'">
                '.$cats_list.'
            </div>
            <div class="hidden-line">
                <ul class="b2_gap">'.$html.'</ul>
            </div>
            <div v-show="showButton" @click="getList(\'\',\'more\',\''.$data['post_type'].'\')" class="load-more box-in box b2-radius mg-t '.($data['post_load_more'] ? '' : 'load-more-hidden').'" data-showButton="'.($data['post_load_more'] ? true : false).'"><button class="empty post-load-button" :disabled="locked || finish"><span v-if="locked && !finish" v-cloak>'.__('加载中...','b2').'</span><span v-else-if="finish" v-cloak>'.__('没有更多了','b2').'</span><span v-else>'.__('加载更多','b2').'</span></button></div>
        </div>';
    }

    public static function post_5($data,$i,$return = false){

        $index = $i;

        $i = isset($data['key']) ? $data['key'] : 'ls'.round(100,999);

        $_post_data = self::get_post_data($data);
        $post_data = $_post_data['data'];

        //是否新窗口打开
        $open = self::open_type($data);

        //显示哪些post_meta
        $post_meta = isset($data['post_meta']) && $data['post_meta'] ? $data['post_meta'] : array();

        //分类列表
        $cats_list = self::get_cats($data,'category',$i,$_post_data['pages'],$index);

        $data['post_load_more'] = isset($data['post_load_more']) && $data['post_load_more'] && $_post_data['pages'] > 1 ? true : false;

        $html = '';

        foreach ($post_data as $k => $v) {

            $hove_date = in_array('date',$post_meta);
            $date = $hove_date ? '<span>'.$v['date'].'</span>' : '';

            //文章列表
            $html .= '<li class="grid-item post-list-item" id="item-'.$v['id'].'">
                <div class="item-in">
                    <div class="post-info">
                        <h2><a '.$open.' href="'.$v['link'].'">'.$v['title'].'</a></h2>'.$date.'
                    </div>
                </div>
            </li>';
        }unset($v);

        if($return){
            return array(
                'count'=>$_post_data['count'],
                'index'=>$i,
                'pages'=>$_post_data['pages'],
                'data'=>$html
            );
        }

        $r = ((floor((1/$data['post_row_count'])*10000)/10000)*100);

        $title_row = isset($data['post_title_row']) && $data['post_title_row'] ? $data['post_title_row'] : 1;
        $title_row_m = isset($data['post_title_row_mobile']) && $data['post_title_row_mobile'] ? $data['post_title_row_mobile'] : 1;

        return '
        <style>
            .post-item-'.$i.' ul.b2_gap > li{
                width:'.$r.'%
            }
            .post-item-'.$i.' .post-info a{
                -webkit-line-clamp: '.$title_row.';
            }
            @media screen and (max-width: 768px){
                .post-item-'.$i.' .item-in .post-info h2{
                    -webkit-line-clamp: '.$title_row_m.';
                }
            }
        </style>
        <div class="'.$data['post_type'].' post-list box b2-radius post-item-'.$i.'" id="post-item-'.$i.'" data-key="'.$i.'" data-i="'.$index.'">
            <div class="post-modules-top '.(!in_array('links',$post_meta) && !in_array('title',$post_meta) ? 'b2-hidden' : '').' '.(!in_array('title',$post_meta) ? 'cats-full' : '').'">
                '.$cats_list.'
            </div>
            <div class="hidden-line">
                <ul class="b2_gap">'.$html.'</ul>
            </div>
            <div v-show="showButton" @click="getList(\'\',\'more\',\''.$data['post_type'].'\')" class="load-more '.($data['post_load_more'] ? '' : 'load-more-hidden').'" data-showButton="'.($data['post_load_more'] ? true : false).'"><button class="empty post-load-button" :disabled="locked || finish"><span v-if="locked && !finish" v-cloak>'.__('加载中...','b2').'</span><span v-else-if="finish" v-cloak>'.__('没有更多了','b2').'</span><span v-else>'.__('加载更多','b2').'</span></button></div>
        </div>';
    }

    public static function post_6($data,$i,$return = false){

        $index = $i;

        $i = isset($data['key']) ? $data['key'] : 'ls'.round(100,999);

        $_post_data = self::get_post_data($data);
        $post_data = $_post_data['data'];

        //是否新窗口打开
        $open = self::open_type($data);

        //显示哪些post_meta
        $post_meta = isset($data['post_meta']) && $data['post_meta'] ? $data['post_meta'] : array();

        //分类列表
        $cats_list = self::get_cats($data,'category',$i,$_post_data['pages'],$index);

        $custom = isset($data['post_custom_key']) ? $data['post_custom_key'] : '';

        $data['post_load_more'] = isset($data['post_load_more']) && $data['post_load_more'] && $_post_data['pages'] > 1 ? true : false;

        $html_list = array();

        if(in_array('date',$post_meta)){
            $html_list['date'] = __('时间','b2');
        }

        $html_list['title'] = __('标题','b2');

        $arg = array();
        $arg = self::list_array($custom);

        if(!empty($arg)){
            foreach ($arg as $key => $value) {
                $html_list[$value['key']] = $value['name'];
            }unset($value);
        }

        if(in_array('cats',$post_meta)){
            $html_list['cats'] = __('分类','b2');
        }

        if(in_array('user',$post_meta)){
            $html_list['user'] = __('作者','b2');
        }

        $custom_html = '';
        foreach ($html_list as $key => $value) {
            $custom_html .= '<td class="post-6-'.$key.' post-6-item"><span>'.$value.'</span></td>';
        }unset($value);

        $html = '<div class="post-6-table"><table><thead>
        <tr class="grid-item post-list-item post-6-header">'.$custom_html.'</tr></thead><tbody class="b2_gap">';

        $html_in = '';
        foreach ($post_data as $k => $v) {

            $date = isset($html_list['date']) ? '<td class="post-6-date post-6-item"><span>'.$v['date'].'</span></td>' : '';

            $custom_row =  '';
            if(!empty($arg)){
                foreach ($arg as $key => $value) {
                    $_v = get_post_meta($v['id'],$value['key'],true);
                    $_v = $_v ? $_v : '-';
                    $custom_row .= '<td class="post-6-'.$value['key'].' post-6-item"><span>'.$_v.'</span></td>';
                }unset($value);
            }

            $cats = isset($html_list['cats']) ? '<td class="post-6-cats post-6-item">'.self::get_post_cats($open,$v,$post_meta,'post-6').'</td>' : '';

            $avatar = isset($html_list['user']) ? '<td class="post-6-user post-6-item"><a class="post-list-meta-avatar" href="'.$v['user_link'].'">'.b2_get_img(array('src'=>$v['user_avatar'],'class'=>array('avatar','b2-radius'),'alt'=>$v['user_name'])).'<span>'.$v['user_name'].'</span></a></td>' : '';
            
            //文章列表
            $html_in .= '<tr class="grid-item post-list-item" id="item-'.$v['id'].'">
                    '.$date.'
                    <td class="post-6-title post-6-item">
                        <h2><a '.$open.' href="'.$v['link'].'">'.$v['title'].'</a></h2>
                    </td>
                    '.$custom_row.'
                    '.$cats.'
                    '.$avatar.'
            </tr>';
        }unset($v);

        if($return){
            return array(
                'count'=>$_post_data['count'],
                'index'=>$i,
                'pages'=>$_post_data['pages'],
                'data'=>$html_in,
                'parent'=>$html
            );
        }

        $html = $html.$html_in;

        $html .='</tbody></table></div>';

        $title_row = isset($data['post_title_row']) && $data['post_title_row'] ? $data['post_title_row'] : 1;
        $title_row_m = isset($data['post_title_row_mobile']) && $data['post_title_row_mobile'] ? $data['post_title_row_mobile'] : 1;

        $r = ((floor((1/$data['post_row_count'])*10000)/10000)*100);
        return '
        <style>
            .post-item-'.$i.' ul.b2_gap > li{
                width:'.$r.'%
            }
            .post-item-'.$i.' td h2{
                -webkit-line-clamp: '.$title_row.';
            }
            @media screen and (max-width: 768px){
                .post-item-'.$i.' .item-in .post-info h2{
                    -webkit-line-clamp: '.$title_row_m.';
                }
            }
        </style>
        <div class="'.$data['post_type'].' post-list box b2-radius post-item-'.$i.'" id="post-item-'.$i.'" data-key="'.$i.'" data-i="'.$index.'" >
            <div class="post-modules-top '.(!in_array('links',$post_meta) && !in_array('title',$post_meta) ? 'b2-hidden' : '').' '.(!in_array('title',$post_meta) ? 'cats-full' : '').'">
                '.$cats_list.'
            </div>
            <div class="hidden-line">
                '.$html.'
            </div>
            <div v-show="showButton" @click="getList(\'\',\'more\',\''.$data['post_type'].'\')" class="load-more '.($data['post_load_more'] ? '' : 'load-more-hidden').'" data-showButton="'.($data['post_load_more'] ? true : false).'"><button class="empty post-load-button" :disabled="locked || finish"><span v-if="locked && !finish" v-cloak>'.__('加载中...','b2').'</span><span v-else-if="finish" v-cloak>'.__('没有更多了','b2').'</span><span v-else>'.__('加载更多','b2').'</span></button></div>
        </div>';
    }

    public static function list_array($str){
        $str = trim($str, " \t\n\r\0");
        $str = explode(PHP_EOL, $str );
        $arg = array();

        foreach ($str as $k => $v) {
            if($v){
                $_v = explode('|', $v);

                $arg[] = array(
                    'name'=>isset($_v[0]) ? $_v[0] : '-',
                    'key'=>isset($_v[1]) ? trim($_v[1], " \t\n\r\0") : '-',
                );
            }
        }unset($v);

        return $arg;
    }

    /**
     * 将文章的设置项转换成json嵌入html
     *
     * @param array $data 设置项
     *
     * @return string json字符串
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function opt_to_json($data){

        $_data = array(
            'post_type'=>$data['post_type'],
            'post_order'=>$data['post_order'],
            'post_row_count'=>$data['post_row_count'],
            'post_count'=>$data['post_count'],
            'post_thumb_ratio'=>$data['post_thumb_ratio'],
            'post_thumb_ratio_mobile'=>isset($data['post_thumb_ratio_mobile']) ? $data['post_thumb_ratio_mobile'] : '1/0.7',
            'post_thumb_ratio_pc'=>isset($data['post_thumb_ratio_pc']) ? $data['post_thumb_ratio_pc'] : '1/0.7',
            'post_open_type'=>$data['post_open_type'],
            'post_meta'=>isset($data['post_meta']) && !empty($data['post_meta']) ? $data['post_meta'] : array(),
            'post_custom_key'=>isset($data['post_custom_key']) && !empty($data['post_custom_key']) ? $data['post_custom_key'] : array(),
            'width'=>$data['width'],
            'show_widget'=>isset($data['show_widget']) ? $data['show_widget'] : 0,
            'post_thumb_width'=>isset($data['post_thumb_width']) ? $data['post_thumb_width'] : 170,
            'post_thumb_width_mobile'=>isset($data['post_thumb_width_mobile']) ? $data['post_thumb_width_mobile'] : 100
        );

        $_data = json_encode($_data);

        return $_data;
    }

    public static function get_modules_title($data){

        $post_meta = isset($data['post_meta']) && is_array($data['post_meta']) ? $data['post_meta'] : array();

        $title = in_array('title',$post_meta);
        $html = '';
        //$desc = in_array('desc',$post_meta);
        $html .= '<div class="modules-title-box">';
        if($title && isset($data['title'])){
            
            $html .= '<h2 class="module-title">'.$data['title'].'</h2>';
            
        }
        // if($desc && isset($data['desc'])){
        //     $html .= '<p class="module-desc">'.$data['desc'].'</p>';
        // }
        $html .= '</div>';

        if(!$title){
            return '';
        }else{
            return $html;
        }
    }

    /**
     * 获取缩略图宽高
     *
     * @param array $data 设置项
     * @param string $thumb_count 每行显示数量
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function get_thumb_size($data,$thumb_count){

        $thumb_count = (int)$thumb_count;

        $data['post_thumb_ratio'] = $data['post_thumb_ratio'] ? $data['post_thumb_ratio'] : '1/0.618';
        $page_width = $data['width'];

        $m_w = '';

        //如果是post-3的样式，固定宽度
        if($data['post_type'] === 'post-3'){

            $data['post_thumb_ratio_mobile'] = isset($data['post_thumb_ratio_mobile']) && $data['post_thumb_ratio_mobile'] ? $data['post_thumb_ratio_mobile'] : '1/0.76';
            $data['post_thumb_ratio_pc'] = isset($data['post_thumb_ratio_pc']) && $data['post_thumb_ratio_pc'] ? $data['post_thumb_ratio_pc'] : '1/0.74';
 
            $w = isset($data['post_thumb_width']) ? (int)filter_var($data['post_thumb_width'], FILTER_SANITIZE_NUMBER_INT) : 170;
            $m_w = isset($data['post_thumb_width_mobile']) ? (int)filter_var($data['post_thumb_width_mobile'], FILTER_SANITIZE_NUMBER_INT) : 100;

            //获取缩略图比例
            $ratio = explode('/',$data['post_thumb_ratio_pc']);
            $w_ratio = $ratio[0];
            $h_ratio = $ratio[1];

            //获取缩略图比例
            $m_ratio = explode('/',$data['post_thumb_ratio_mobile']);
            $m_w_ratio = $m_ratio[0];
            $m_h_ratio = $m_ratio[1];

            $h = round($w/$w_ratio*$h_ratio);
            $m_h = round($m_w/$m_w_ratio*$m_h_ratio);

            return apply_filters('b2_post_thumb_size', array(
                'w'=>$w,
                'm_w'=>$m_w,
                'h'=>$h,
                'm_h'=>$m_h,
                'page_w'=>$page_width,
                'ratio'=>round($h_ratio/$w_ratio*100,6),
                'm_ratio'=>round($m_h_ratio/$m_w_ratio*100,6)
            ));

        //如果是 post-4 的样式，缩略图宽度平分
        }else{

            //获取缩略图比例
            $ratio = explode('/',$data['post_thumb_ratio']);
            $w_ratio = $ratio[0];
            $h_ratio = $ratio[1];

            if($data['post_type'] === 'post-4'){
                if($thumb_count != 1){
                    $w = ($page_width - ($thumb_count - 2)*B2_GAP)/($thumb_count-1);
                }else{
                    $w = $page_width;
                }
                
            //如果是其他样式，平分缩略图宽度
            }else{
                $w = ($page_width - ($thumb_count - 1)*B2_GAP) / $thumb_count;
            }

            //计算高度
            $h = round($w/$w_ratio*$h_ratio);
        }

        return apply_filters('b2_post_thumb_size', array(
            'w'=>$w,
            'm_w'=>$m_w,
            'h'=>$h,
            'page_w'=>$page_width,
            'ratio'=>round($h_ratio/$w_ratio*100,6)
        ));
    }

    /**
     * 页面打开方式
     *
     * @param array $data 设置项
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function open_type($data){
        //是否新窗口打开
        $open = isset($data['post_open_type']) ? $data['post_open_type'] : '';

        if(!$open){
            return ' target="_blank"';
        }else{
            return '';
        }
    }

    /**
     * 获取当前文章的所有分类
     *
     * @param bool $open 打开方式
     * @param array $v 当前文章的数据
     * @param array $post_meta 设置项
     * @param string $post_type 当前文章列表的展现形式
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function get_post_cats($open,$v,$post_meta,$post_type){
        //文章分类
        $cats = '';
        if(in_array('cats',$post_meta)){
            $cats = '<div class="post-list-cat  b2-radius">';
            foreach($v['cats'] as $cat){
                if($post_type === 'post_3'){
                    $cats .= '<a '.$open.' class="post-list-cat-item b2-radius" href="'.$cat['link'].'" style="color:'.$cat['color'].'">
                    '.$cat['name'].'</a>';
                }else{
                    $cats .= '<a '.$open.' class="post-list-cat-item b2-radius" href="'.$cat['link'].'" style="color:'.$cat['color'].'">'.$cat['name'].'</a>';
                    break;
                }
            }unset($cat);
            $cats .= '</div>';
        }

        return $cats;
    }

    /**
     * 分类导航列表
     *
     * @param array $cats 分类的 slug 数组
     * @param string $type taxonomy 类型
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function get_cats($data,$type,$i,$_pages,$index){

        $title = self::get_modules_title($data);

        $cats = isset($data['post_cat']) ? (array)$data['post_cat'] : array();
        $post_meta = isset($data['post_meta']) && is_array($data['post_meta']) ? $data['post_meta'] : array();

        // $terms = get_terms( array(
        //     'taxonomy' =>  $type,
        //     'hide_empty' => false,
        //     'id'    => $cats,
        // ));
        $terms = array();

        foreach ($cats as $k => $v) {
            $t = get_term_by('id', $v, $type);
            if(is_wp_error( $t ) || !isset($t->name)) continue;
            $terms[] = array(
                'id'=>$v,
                'name'=>$t->name,
                'link'=>get_term_link( $t->slug, $type )
            );
        }unset($v);

        $html = $title;

        $html .= '<div class="post-list-cats '.($title ? 'post-list-cats-has-title' : '').'" style="'.(!in_array('links',$post_meta) ? 'display:none' : '').'">';

        $html .= '<div class="post-carts-list-row">';

        if(count($terms) > 1){
            $html .= '<a :class="\'cat-list post-load-button \' + (!id ? \'picked\' : \'\')" @click="getList(\'\',\'cat\',\''.$data['post_type'].'\')" href="javascript:void(0)"><span data-type="cat">'.__('全部','b2').'</span></a>';
        }
        
        if(count($terms) > 1){
            $t_i = 0;
            foreach ($terms as $k => $v) {
                $t_i ++;
                if($t_i > 4) break;
                $html .= '<a :class="\'cat-list post-load-button \' + (id == '.$v['id'].' ? \'picked\' : \'\')" @click="getList('.$v['id'].',\'cat\',\''.$data['post_type'].'\')"  href="javascript:void(0)"><span data-type="cat">'.$v['name'].'</span></a>';
            }unset($v);
        }
        
        if(!empty($terms)){
            if(count($terms) == 1){
                $html .= '<a class="cat-list post-load-button post-load-button-more" href="'.$terms[0]['link'].'" target="_blank"><span data-type="cat">'.__('更多','b2').b2_get_icon('b2-arrow-right-s-line').'</span></a>';
            }else{
                $html .= '<a class="cat-list post-load-button post-load-button-more" href="'.(B2_HOME_URI.'/cat-group/'.$i).'" target="_blank"><span data-type="cat">'.__('更多','b2').b2_get_icon('b2-arrow-right-s-line').'</span></a>';
            }
            
        }

        $html .= '</div></div>';

        return $html;
    }

    public static function get_post_icon($post_id){
        $post_style = \B2\Modules\Templates\Single::get_single_post_settings($post_id,'single_post_style');
        $html = '';
        if($post_style == 'post-style-5'){
            $html = b2_get_icon('b2-play-circle-line');
        }
        
        return apply_filters('b2_post_list_icon', $html,$post_id);
    }

    /**
     * 获取文章信息
     *
     * @param array $data
     *
     * @return array
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function get_post_data($data){
        $paged = isset($data['post_paged']) ? (int)$data['post_paged'] : 1;
        $offset = ($paged -1)*(int)$data['post_count'];

        $post_type = isset($data['_post_type']) && $data['_post_type'] ? esc_attr($data['_post_type']) : 'post';

        $user_id = b2_get_current_user_id();

        if((isset($data['author__in']) && (int)$user_id === (int)$data['author__in'][0]  && (int)$data['author__in'][0] !== 0) || (user_can( $user_id, 'manage_options') && isset($data['author__in']) && (int)$data['author__in'][0])){
            $data['post_status'] = array('publish','pending','draft');
        }else{
            $data['post_status'] = array('publish');
        }

        $args = array(
            'post_type'=>$post_type,
            'posts_per_page' => (int)$data['post_count'] ? (int)$data['post_count'] : 10,
            'tax_query' => array(
                'relation' => 'AND',
            ),
            'meta_query'=>array(
                'relation' => 'AND',
            ),
            'date_query'=>array(
                'relation'=>'AND',
            ),
            'offset'=>$offset,
            'post_status'=>$data['post_status']
            // 'include_children' => true,
        );

        if(isset($data['post_order']) && !empty($data['post_order'])){
            switch($data['post_order']){
                case 'random':
                    $args['orderby'] = 'rand';
                    break;
                case 'sticky':
                    $args['post__in'] = get_option( 'sticky_posts' );
                    $args['ignore_sticky_posts'] = 1;
                    break;
                case 'modified':
                    $args['orderby'] = 'modified';
                    break;
                case 'views':
                    $args['meta_key'] = 'views';
                    $args['orderby'] = 'meta_value_num';
                    break;
                case 'like':
                    $args['meta_key'] = 'b2_vote_up_count';
                    $args['orderby'] = 'meta_value';
                    break;
                case 'comments':
                    $args['orderby'] = 'comment_count';
                    break;
            }
        }

        //如果存在用户
        if(isset($data['author__in']) && !empty($data['author__in'])){
            $args['author__in'] = $data['author__in'];
        }

        //如果是存在分类
        if(isset($data['post_cat']) && !empty($data['post_cat'])){
            // if(count($data['post_cat']) > 1){
            //     $data['post_cat'] = $data['post_cat'][0];
            // }
            array_push($args['tax_query'],array(
                'taxonomy' => 'category',
                'field'    => 'id',
                'terms'    => (array)$data['post_cat'],
                'include_children' => true,
                'operator' => 'IN'
            ));
        }

        //如果存在专辑
        if(isset($data['collection_slug']) && !empty($data['collection_slug'])){
            $order = b2_get_option('template_collection','collection_post_order');
  
            $args['order'] = $order ? $order : 'asc';
            array_push($args['tax_query'],array(
                'taxonomy' => 'collection',
                'field'    => 'id',
                'terms'    => (array)$data['collection_slug'],
                'include_children' => true,
                'operator' => 'IN'
            ));
        }

        //如果存在标签
        if(isset($data['post_tag']) && !empty($data['post_tag'])){
            array_push($args['tax_query'],array(
                'taxonomy' => 'post_tag',
                'field'    => 'slug',
                'terms'    => (array)$data['post_tag'],
                'include_children' => true,
                'operator' => 'IN'
            ));
        }

        //如果存在标签
        if(isset($data['tags']) && !empty($data['tags'])){
            array_push($args['tax_query'],array(
                'taxonomy' => 'post_tag',
                'field'    => 'slug',
                'terms'    => (array)$data['tags'],
                'operator' => 'AND'
            ));
        }

        //如果自定义字段筛选
        if(isset($data['metas']) && !empty($data['metas'])){
            foreach($data['metas'] as $k=>$v){
                array_push($args['meta_query'],array(
                    'key'     => $k,
                    'value'   => $v,
                    'compare' => '=',
                ));
            }unset($v);
        }

        //如果是月度筛选
        if(isset($data['month']) && !empty($data['month'])){
            array_push($args['date_query'],array(
                'month' => esc_attr($data['month'])
            ));
        }

        //如果是年度筛选
        if(isset($data['month']) && !empty($data['month'])){
            array_push($args['date_query'],array(
                'year' => esc_attr($data['year'])
            ));
        }

        if(isset($data['search']) && !empty($data['search'])){
            $args['search_tax_query'] = true;
            $args['s'] = esc_attr($data['search']);
        }

        if(isset($data['post_ignore_sticky_posts'])){
            $args['ignore_sticky_posts'] = $data['post_ignore_sticky_posts'];
        }

        // $args['no_found_rows'] = isset($data['no_rows']) ? $data['no_rows'] : false;
        // $args['update_post_meta_cache'] = false;
        // $args['update_post_term_cache'] = false;
        // $args['fields'] = 'ids';

        $args = apply_filters( 'b2_post_loop_args', $args, $data );
        
        // $the_query = new \WP_Query( $args );
        // $my_posts = $the_query->get_posts();

        // $post_data = array();
        // $_pages = 1;
        // $_count = 0;

        // if($my_posts){
        //     $_pages = $the_query->max_num_pages;
        //     $_count = $the_query->found_posts;
        //     foreach ($my_posts as $p) {

        //         $post_data[] = self::get_post_metas($p,$data);
        //     }
        // }

        // wp_reset_postdata();

        $the_query = new \WP_Query( $args );

        $post_data = array();
        $_pages = 1;
        $_count = 0;
        if ( $the_query->have_posts() ) {

            $_pages = $the_query->max_num_pages;
            $_count = $the_query->found_posts;

            while ( $the_query->have_posts() ) {

                $the_query->the_post();

                $post_data[] = self::get_post_metas($the_query->post->ID,$data);
                //apply_filters('b2_get_post_meta', $the_query->post->ID,$data);
            }
            wp_reset_postdata();
        }

        
        // if ( $the_query->have_posts() ) {

        //     $_pages = $the_query->max_num_pages;
        //     $_count = $the_query->found_posts;

        //     while ( $the_query->have_posts() ) {

        //         $the_query->the_post();

        //         $post_data[] = self::get_post_metas($the_query->post->ID,$data);
        //         //apply_filters('b2_get_post_meta', $the_query->post->ID,$data);
        //     }
        //     
        // }
        
        return array(
            'count'=>$_count,
            'pages'=>$_pages,
            'data'=>$post_data
        );
    }

    public static function get_post_metas($post_id,$data = ''){

        $thumb_id = get_post_thumbnail_id($post_id);
        $thumb_url = wp_get_attachment_image_src($thumb_id,'full');

        if(!isset($thumb_url[0]) || !$thumb_url[0]){
            $thumb_url = array(
                \B2\Modules\Common\Post::get_post_thumb($post_id),
                400,
                300
            );
        }

       // var_dump($thumb_url);

        // if($data['post_type'] === 'post-2'){
        //     if($thumb_url[2] > 200) $thumb_url[2] = 200;
        // }

        $post_meta = Post::post_meta($post_id);
        $post_meta['id'] = $post_id;
        $post_meta['title'] = get_the_title($post_id);
        $post_meta['link'] = get_permalink($post_id);
        $post_meta['thumb'] = $thumb_url[0];
        $post_meta['thumb_ratio'] = isset($thumb_url[2]) && isset($thumb_url[1]) && $thumb_url[1] > 0 ? round($thumb_url[2]/$thumb_url[1],6) : 1;
        $post_meta['des'] = b2_get_excerpt($post_id);
        $post_meta['status'] = get_post_status($post_id);
        // unset($data);
        $post_meta['pay_type'] = Post::get_post_pay_data($post_id);
        return $post_meta;
    }

    public static function get_post_pay_data($data,$post_meta){
        $html = '';
        if(!empty($data)){
            foreach ($data as $k => $v) {
                if($v['type'] === 'video' && in_array('video',$post_meta)){
                    $html .= '<div><span class="post-pay-type-icon">'.b2_get_icon('b2-caozuoshili').'<b>'.__('视频','b2').'</b></span><span>'.sprintf(__('共%s节','b2'),$v['count']).'</span></div>';
                }elseif($v['type'] === 'download' && in_array('download',$post_meta)){
                    $html .= '<div><span class="post-pay-type-icon">'.b2_get_icon('b2-download1').'<b>'.__('下载','b2').'</b></span><span>'.sprintf(__('%s个资源','b2'),$v['count']).'</span></div>';
                }elseif($v['type'] === 'hide' && in_array('hide',$post_meta)){

                    $role = $v['pay_type'];
                    $role_text = '';
                    switch ($role) {
                        case 'money':
                            $role_text = __('付费阅读','b2');
                            break;
                        case 'credit':
                            $role_text = __('支付积分','b2');
                            break;
                        case 'roles':
                            $role_text = __('限制等级','b2');
                            break;
                        case 'login':
                            $role_text = __('登陆可见','b2');
                            break;
                        case 'comment':
                            $role_text = __('评论可见','b2');
                            break;
                    }

                    $html .= '<div><span class="post-pay-type-icon">'.b2_get_icon('b2-suo1').'<b>'.__('隐藏','b2').'</b></span><span>'.$role_text.'</span></div>';
                }
                
            }unset($v);
        }

        if($html){
            $html = '<div class="post-pay-type">'.$html.'</div>';
        }

        return $html;
    }

    

}