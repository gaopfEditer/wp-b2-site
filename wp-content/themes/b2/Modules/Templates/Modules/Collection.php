<?php namespace B2\Modules\Templates\Modules;

use B2\Modules\Templates\Collection as Coll;
use B2\Modules\Common\Post;

class Collection{

    public function init($data,$i){
        $type = str_replace('-','_',$data['collection_type']);
        
        return self::$type($data,$i);
    }

    public static function collection_1($data,$i,$return = false){

        $i = isset($data['key']) ? $data['key'] : 'ls'.round(100,999);
        
        $collection_data = self::get_data($data);

        $open = self::open_type($data);

        if(empty($collection_data)) return;
        $html = '
        <div class="home-collection-box-1 home-collection home-collection-item-'.$i.'">
        <a class="collection-previous collection-button" href="javascript:void(0)">
            <svg class="flickity-button-icon" viewBox="0 0 100 100"><path d="M 10,50 L 60,100 L 65,95 L 20,50  L 65,5 L 60,0 Z" class="arrow"></path></svg>
        </a>
        <div class="collection-out-row">
        <div class="collection-out">
        <ul class="home-collection-silder">';

        //$size = self::get_size($data);

        $collection_name = b2_get_option('normal_custom','custom_collection_name');

        foreach ($collection_data as $k => $v) {
            $posts = $v['posts'];

            $count = isset($posts['count']) && $posts['count'] ? (int)$posts['count'] : 0;

            $qishu = get_term_meta($v['id'], 'b2_tax_index', true);

            $list = '';

            if((string)$data['collection_count'] !== '0'){
                $list .= '<div class="home-collection-row-2">';
                if(!empty($posts['data'])){
                    foreach ($posts['data'] as $key => $value) {
                        $list .= '<div>'.b2_get_img(array('src'=>$value['thumb'],'class'=>array('b2-radius'),'alt'=>$value['title'])).'
                        <a href="'.$value['href'].'">'.$value['title'].'</a></div>';
                    }
                }
                $list .= '</div>';
            }
            
            $html .= '<li>
                    <div class="home-collection-content">
                        <div>
                            <div class="home-collection-in b2-radius box">
                                <div class="home-collection-image">
                                    <div>
                                    '.($qishu ? '<span class="collection-number ar b2-color b2-radius">'.sprintf(__('%s：第%s期','b2'),$collection_name,$qishu).'</span>' : '').'
                                    <a class="link-block" href="'.$v['link'].'" '.$open.'></a>
                                    '.b2_get_img(array('src'=>$v['thumb'],'class'=>array('home-collection-thumb'),'alt'=>$v['name'])).'
                                    </div>
                                </div>
                                <div class="home-collection-info">
                                    <a href="'.$v['link'].'" '.$open.'><h2>'.$v['name'].'</h2></a>
                                    <div class="home-collection-row-1">
                                        <span>'.sprintf(__('更新%s篇','b2'),$count).'</span>
                                        <a href="'.$v['link'].'" '.$open.'>'.__('前往','b2').'</a>
                                    </div>
                                    '.$list.'
                                </div>
                            </div>
                        </div>
                    </div>
                </li>';
        }

        $html .= '</ul></div></div>
        <a class="collection-next collection-button" href="javascript:void(0)">
            <svg class="flickity-button-icon" viewBox="0 0 100 100"><path d="M 10,50 L 60,100 L 65,95 L 20,50  L 65,5 L 60,0 Z" class="arrow" transform="translate(100, 100) rotate(180) "></path></svg>
        </a>
        </div>';

        return $html;
    }

    public static function collection_2($data,$i,$return = false){

        $i = isset($data['key']) ? $data['key'] : 'ls'.round(100,999);
        
        $collection_data = self::get_data($data);

        $open = self::open_type($data);

        if(empty($collection_data)) return;
        $html = '<div class="collection-box">';

        $collection_name = b2_get_option('normal_custom','custom_collection_name');

        //$size = self::get_size($data);

        foreach ($collection_data as $k => $v) {
            $posts = $v['posts'];

            $count = $posts['count'];

            $posts = $posts['data'];

            $qishu = get_term_meta($v['id'], 'b2_tax_index', true);

            $list = '';

            $posts_list = '';

            if(!empty($posts)){
                foreach ($posts as $key => $value) {
                    $posts_list .= '<li><span><a href="'.$value['cat']['link'].'">'.$value['cat']['name'].'</a></span><a href="'.$value['href'].'" class="post-link">'.$value['title'].'</a></li>';
                }
            }

            $html .= '
            <div class="collection-item">
                <div class="box b2-radius">
                    '.($qishu ? '<div class="collection-number ar b2-radius">
                    <span>'.sprintf(__('%s：第%s%s%s期','b2'),$collection_name,'<b>',$qishu,'</b>').'</span>
                </div>' : '').'
                    <div class="collection-title">
                        <div class="collection-thumb">
                            <a href="'.$v['link'].'" target="_blank">
                                '.b2_get_img(array('src'=>$v['thumb'],'alt'=>$v['name'])).'
                            </a>
                        </div>
                        <div class="collection-info b2-mg">
                            <h2><a href="'.$v['link'].'" target="_blank">'.$v['name'].'</a></h2>
                            <div class="collection-count">
                            '.(!empty($posts) ? Post::time_ago($posts[0]['date']).__('更新','b2').' · ' : '').''.$count.__('篇文章','b2').'
                            </div>
                        </div>
                    </div>
                    '.($posts_list ? '<ul class="collection-posts">
                    '.$posts_list.'
                </ul>' : '').'
                </div>
            </div>';
           
        }

        $html .= '</div>';

        // $r = round(1/$data['collection_row_count'],6)*100;
        // $style = '
        //     <style>
        //         .home-collection-item-'.$i.' ul li{
        //             width:'.$r.'%;
        //             height:auto
        //         }
        //     </style>
        // ';

        return $html;
    }

    public static function collection_3($data,$i,$return = false){
        $i = isset($data['key']) ? $data['key'] : 'ls'.round(100,999);
        
        $data['collection_count'] = 3;
        $collection_data = self::get_data($data);

        $open = self::open_type($data);

        if(empty($collection_data)) return;
        $html = '<div class="collection-box-3 collection-box collection-index-'.$i.'">
        <a class="collection-previous collection-button" href="javascript:void(0)">
            <svg class="flickity-button-icon" viewBox="0 0 100 100"><path d="M 10,50 L 60,100 L 65,95 L 20,50  L 65,5 L 60,0 Z" class="arrow"></path></svg>
        </a>
        <div class="collection-box-3-in home-collection-silder">';

        //$size = self::get_size($data);

        foreach ($collection_data as $k => $v) {
            $posts = $v['posts'];

            $count = $posts['count'];

            $posts = $posts['data'];

            $qishu = get_term_meta($v['id'], 'b2_tax_index', true);
            $qishu = $qishu ? $qishu : 0;

            $list = '';

            $posts_list = '';

            if(!empty($posts)){
                foreach ($posts as $key => $value) {
                    $thumb = b2_get_thumb(array(
                        'thumb'=>$value['thumb_full'],
                        'width'=>120,
                        'height'=>90
                    ));

                    $posts_list .= '<li data-title="'.$value['title'].'" class="b2tooltipbox">
                    <div>'.b2_get_img(array('src'=> $thumb,'alt'=>$value['title'])).'<a href="'.$value['href'].'" class="post-link link-block" target="_blank"></a>
                    </div>
                    </li>';
                }
            }

            $html .= '
            <div class="coll-3-box">
                <div class="coll-3-box-in box b2-radius">
                    <div class="coll-3-top">
                        '.b2_get_img(array('src'=>$v['thumb'],'alt'=>$v['name'])).'
                        <a href="'.$v['link'].'" target="_blank" class="link-block"></a>
                        <span>'.sprintf(__('第%s%s%s期','b2'),'<b>',$qishu,'</b>').'</span>
                    </div>
                    '.($posts_list ? '<div class="coll-3-bottom">
                    <ul>
                        '.$posts_list.'
                    </ul>
                </div>' : '').'
                </div>
            </div>';
           
        }

        $html .= '</div><a class="collection-next collection-button" href="javascript:void(0)">
        <svg class="flickity-button-icon" viewBox="0 0 100 100"><path d="M 10,50 L 60,100 L 65,95 L 20,50  L 65,5 L 60,0 Z" class="arrow" transform="translate(100, 100) rotate(180) "></path></svg>
    </a></div>';

        return $html;
    }

    public static function get_data($data){

        $terms = get_terms(array(
            'taxonomy' => 'collection',
            'hide_empty' => false,
            'order'=>'desc',
            'orderby' => 'meta_value_num',
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'b2_tax_index',
                    'type' => 'NUMERIC',
                ),
                array(
                    'key' => 'b2_tax_index',
                    'compare' => 'NOT EXISTS'
                )
            ),
            'cache_domain'=>'b2_collection',
            'slug'    => isset($data['collections']) ? $data['collections'] : '',
        ));

        if(empty($terms)) return array();

        $size = array(
            'w'=>185,
            'h'=>250
        );

        if($data['collection_type'] === 'collection-2'){
            $size = array(
                'w'=>542,
                'h'=>217
            );
        }

        if($data['collection_type'] === 'collection-3'){
            $size = array(
                'w'=>350,
                'h'=>188
            );
        }

        $arr = array();
        foreach ($terms as $k => $v) {
            $thumb = get_term_meta($v->term_id,'b2_tax_img',true);

            $thumb = b2_get_thumb(array(
                'thumb'=>$thumb,
                'width'=>$size['w'],
                'height'=>$size['h']
            ));

            $arr[] = array(
                'id'=>$v->term_id,
                'thumb'=>$thumb,
                'name'=>$v->name,
                'posts'=>$data['collection_count'] ? Coll::get_collection_post_list($v->term_id,$data['collection_count']) : array(),
                'desc'=>$v->description ? $v->description : '',
                'link'=>get_term_link($v->term_id)
            );
        }

        return $arr;
    }

    public static function get_size($data){
        if(!$data['collection_thumb_ratio']) return 1;
        //获取缩略图比例
        $ratio = explode('/',$data['collection_thumb_ratio']);
        $w_ratio = $ratio[0];
        $h_ratio = $ratio[1];

        $page_width = $data['width'];

        $data['collection_row_count'] = $data['collection_row_count'] ? $data['collection_row_count']  : 1;

        $w = ($page_width - ($data['collection_row_count'] - 1)*B2_GAP) / $data['collection_row_count'];

        //计算高度
        $h = round($w/$w_ratio*$h_ratio,6);

        return apply_filters('b2_collection_thumb_size', array(
            'w'=>$w,
            'h'=>$h,
            'page_w'=>$page_width,
            'ratio'=>round($h_ratio/$w_ratio*100,6),
            'padding'=>round(($h+40*$data['collection_count']+67+8)/$w*100,6)
        ));
    }

    public static function open_type($data){
        //是否新窗口打开
        $open = isset($data['collection_open']) ? $data['collection_open'] : '';

        if(!$open){
            return ' target="__blank"';
        }else{
            return '';
        }
    }
}