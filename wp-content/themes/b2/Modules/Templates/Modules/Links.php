<?php namespace B2\Modules\Templates\Modules;

use B2\Modules\Common\Links as cLinks;

class Links{
    public function init($data,$i,$return = false){
        // var_dump($data);

        $data['link_count'] = $data['link_count'] ? (int)$data['link_count'] : 5;

        // var_dump($data);

        if(!isset($data['link_cat']) || $data['link_cat'] == ''){
            return;
        }

        $data['link_meta'] = isset($data['link_meta']) ? (array)$data['link_meta'] : [];

        $children_list = '';
        if(in_array('children',$data['link_meta'])){
            $childrens = cLinks::get_children_cat($data['link_cat'],3);

            if(!empty($childrens)){
                $children_list .= '<div class="child-link-cat">';
                foreach ($childrens as $key => $value) {
                    if(!isset($value->slug)) continue;
                    $children_list .= '<a target="_blank" class="cat-box b2-radius" href="'.cLinks::get_links_cat_url($value->slug).'">'.$value->name.'</a>';              
                }
                $children_list .= '</div>';
            }
        }

        $link_list = cLinks::get_links_data($data);

        if(empty($link_list)) return '';

        $more = '';
        if(in_array('more',$data['link_meta'])){

            $more = '<div class="link-more"><a href="'.cLinks::get_links_cat_url($data['link_cat']).'" target="_blank" class="cat-box b2-radius"><span>'.__('更多','b2').'</span>'.b2_get_icon('b2-arrow-right-s-line').'</a></div>';
        }

        $title = '';
        if(in_array('title',$data['link_meta'])){
            $title = '<div class="link-title"><div class="link-title-left"><h2 id="link-'.$data['link_cat'].'" class="cat-box b2-radius">'.($data['title'] ? $data['title'] : $link_list[0]['cat_name']).'</h2>'.$children_list.'</div>'.$more.'</div>';
        }

        if(is_single()){
            $title = '<div class="link-title"><div class="link-title-left"><h2 id="link-'.$data['link_cat'].'" class="cat-box b2-radius">'.__('相似站点','b2').'</h2></div>'.$more.'</div>';
        }

        $loadmore = '';
        if(in_array('loadmore',$data['link_meta'])){
            $paged = get_query_var('paged') ? get_query_var('paged') : 1;
            $loadmore = is_tax('link_cat') ? '<div class="link-more-button">'.b2_pagenav(array('pages'=>$link_list[0]['pages'],'paged'=>$paged)).'</div>' : '';
        }

        $icon = in_array('icon',$data['link_meta']);
        $desc = in_array('desc',$data['link_meta']);
        $user = in_array('user',$data['link_meta']);
        $like = in_array('like',$data['link_meta']);
        

        $html = '';
        foreach ($link_list as $k => $v) {
            $icon =  $icon ? '<img class="link-img b2-radius" src="'.$v['img'].'" />' : '';
            $desc = $desc ? '<p class="link-desc">'.$v['desc'].'</p>' : '';

            $user = $user ? '<span class="link-author fs12">'.($v['user']['link'] ? '<a target="_blank" href="'.$v['user']['link'].'">'.$v['user']['name'].'</a>' : '未名').'</span>' : '';

            $like = $like ? '<button class="link-up text" @click.stop="addRating(\''.$v['id'].'\')">'.b2_get_icon('b2-thumb-up-line').'<b>'.$v['link_rating'].'</b></button>' : '';

            $html .= '
                <li>              
                    <div class="link-in box b2-radius b2flex">
                        <a href="'.$v['url'].'" target="_blank" class="link-block" ref="nofollow"></a>
                        '.$icon.'
                        <div class="link-info">
                            <div class="link-top">
                                <a href="'.$v['url'].'" target="_blank" class="fd link-right" ref="nofollow">
                                    <h2>'.$v['name'].'</h2>
                                </a>
                            </div>
                            '.$desc.'
                            <div class="link-footer">
                                '.$user.$like.'
                            </div>
                        </div>
                    </div>
                </li>
            ';
        };

        return '
        <style>
        .link-box-'.$i.' .link-list li{
            width:'.(100/$data['link_count']).'%;
        }
        </style>
            <div class="link-box mg-b link-box-'.$i.' box b2-radius">
                '.$title.'
                <div class="link-list">
                    <ul>'.$html.'</ul>
                </div>
                '.$loadmore.'
            </div>
        ';
       
    }

    
}