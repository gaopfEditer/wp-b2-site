<?php namespace B2\Modules\Templates\Modules;

use B2\Modules\Common\Circle as CircleFn;

class Circle{

    public $circle_member_name;

    public function init($data,$i,$return = false){

        $this->circle_member_name = b2_get_option('normal_custom','custom_circle_member_name');

        if(!isset($data['circle_cats'])) return;

        $data['circle_cats'] = (array)$data['circle_cats'];

        $circle_data = $this->get_circle_data($data);

        // var_dump($circle_data);
        if(empty($circle_data)) return ;

        $data['circle_row_count'] = isset($data['circle_row_count']) && $data['circle_row_count'] ? (int)$data['circle_row_count'] : 2;

        $data['circle_meta'] = isset($data['circle_meta']) ? (array)$data['circle_meta'] : [];
        $show_title = in_array('title',$data['circle_meta']);
        $show_more = in_array('more',$data['circle_meta']);

        $show_title = $show_title ? '<div class="modules-title-box post-list">
            <h2 class="module-title">'.$data['title'].'</h2>
            </div>' : '';

        if(count($data['circle_cats']) == 1){
            $more = '<a href="'.$circle_data[0]['circle_data']['link'].'" target="_blank" class="cat-list post-load-button post-load-button-more"><span>'.$circle_data[0]['circle_data']['name'].b2_get_icon('b2-arrow-right-s-line').'</span></a>';
        }else{
            $circle_link = b2_get_option('normal_custom','custom_circle_link');
            $more = '<a href="'.B2_HOME_URI.'/'.$circle_link.'" target="_blank" class="cat-list post-load-button post-load-button-more"><span>'.__('全部','b2').b2_get_icon('b2-arrow-right-s-line').'</span></a>';
        }

        $show_more = $show_more ? '<div class="post-list-cats post-list-cats-has-title">
            '.$more.'
        </div>' : '';

        $html = '
        <style>
        .index-circle-'.$i.' li{
            width:'.(1/$data['circle_row_count']*100).'%
        }
        </style>
        ';

        $html .= '
        '.($show_title || $show_more ? '
            <div class="post-modules-top  ">
                '.$show_title.$show_more.'
            </div>
        ' : '').'
        <div class="index-circle-box index-circle-'.$i.'"><ul>';

        foreach ($circle_data as $k => $v) {
            $html .= '<li><div class="i-c-in box b2-radius">'.$this->circle_html($v['circle_data']);
            $html .= $this->topic_html($v['topic_data']).'</div></li>';
        }unset($v);

        $html .= '</ul></div>';

        return $html;
        
    }

    public function circle_html($data){

        return '
            <div class="c-c-top">
                <div class="c-c-left">
                    <a href="'.$data['link'].'" class="link-block" target="_blank"></a>
                    '.b2_get_img(array('src'=>$data['icon'],'class'=>array('avatar','b2-radius'))).'
                    <div class="m-circle-info">
                        <div class="c-c-name">'.$data['name'].'</div>
                        <div class="c-c-count gray">
                            <p><span>'.__('话题','b2').'</span> '.$data['topic_count'].'</p>
                            <p><span>'.$this->circle_member_name.'</span> '.$data['user_count'].'</p>
                        </div>
                    </div>
                </div>
                <div class="c-c-right">
                    <a herf="'.$data['link'].'" target="_blank" class="b2-color"><span>'.__('前往','b2').'</span>'.b2_get_icon('b2-arrow-right-s-line').'</a>
                </div>
            </div>
        ';
    }

    public function topic_html($data){
        if(count($data) == 0) return '';

        $li = '<div class="c-topic-list">';

        foreach ($data as $k => $v) {
            $type = $this->get_topic_type($v['topic_id']);

            $li .= '<div><span class="c-'.$type['type'].'">['.$type['text'].']</span>
                <a href="'.$v['link'].'" target="_blank">'.$v['title'].'</a></div>
            ';
        }unset($v);

        return $li.'</div>';
    }

    public function get_circle_data($data){

        $list = [];

        if(!empty($data['circle_cats'])){
            foreach ($data['circle_cats'] as $k => $v) {
                $list[] = array(
                    'circle_data'=>CircleFn::get_circle_data($v),
                    'topic_data'=>$this->get_topic_data($v,$data['circle_topic_count'])
                );    
            }unset($v);
        }

        return $list;
    }

    public function get_topic_data($circle_id,$count){

        if((int)$count == 0 ) return [];

        $stickys = get_term_meta($circle_id,'b2_topic_sticky');

        $args = [
            'orderby'  => 'date',
            'order'=>'DESC',
            'paged'=>1,
            'posts_per_page'=>$count,
            'suppress_filters' => false,
            'post__not_in'=>$stickys,
            'no_found_rows'=>true,
            'tax_query' => array(
                array(
                    'taxonomy' => 'circle_tags',
                    'field' => 'term_id',
                    'terms' => $circle_id
                )
            )
        ];

        $topic_query = new \WP_Query( $args );

        $topics = [];
        if ( $topic_query->have_posts()) {

            while ( $topic_query->have_posts() ) {
                $topic_query->the_post();

                $topic_id = $topic_query->post->ID;

                $title = get_the_title($topic_id);

                if(!$title){
                    $content = $topic_query->post_content;
                    if($content){
                        $title = mb_strimwidth($topic_query->post_content,0,100,'','utf-8');
                    }else{
                        $title = __('话题','b2').$topic_id;
                    }
                }

                $topics[] = [
                    'topic_id'=> $topic_id,
                    'title'=>html_entity_decode($title),
                    'link'=>get_permalink($topic_id),
                    'type'=>$this->get_topic_type($topic_id)
                ];
            }
        }

        wp_reset_postdata();

        return $topics;
    }

    public function get_topic_type($topic_id){

        $type = get_post_meta($topic_id,'b2_circle_topic_type',true);

        switch ($type) {
            case 'ask':
                return array(
                    'type'=>'ask',
                    'text'=>__('提问','b2')
                );
                break;
            case 'vote':
                $vote_type = get_post_meta($topic_id,'b2_circle_vote_type',true);
                if($vote_type == 'pk'){
                    return array(
                        'type'=>'pk',
                        'text'=>__('PK','b2')
                    );
                }
                return array(
                    'type'=>'vote',
                    'text'=>__('投票','b2')
                );
                break;
            case 'guess':
                return array(
                    'type'=>'guess',
                    'text'=>__('你猜','b2')
                );
                break;
            default:
                return array(
                    'type'=>'say',
                    'text'=>__('我说','b2')
                );
                break;
        }
    }

}