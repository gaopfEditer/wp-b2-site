<?php
namespace B2\Modules\Templates;
use B2\Modules\Common\Post;
class Archive{

    public function init(){
        add_action('b2_archive_category_top',array($this,'archive_top_info'),9);
        add_action('b2_archive_post_tag_top',array($this,'archive_top_info'),9);
        add_action('b2_archive_normal_top',array($this,'archive_top_info'),9);
        add_filter( 'get_the_archive_title', array($this,'remove_archive_title'));
    }

    public function remove_archive_title( $title ) {
        if ( is_category() ) {
            $title = single_cat_title( '', false );
        } elseif ( is_tag() ) {
            $title = single_tag_title( '', false );
        } elseif ( is_author() ) {
            $title = '<span class="vcard">' . get_the_author() . '</span>';
        } elseif ( is_post_type_archive() ) {
            $title = post_type_archive_title( '', false );
        } elseif ( is_tax() ) {
            $title = single_term_title( '', false );
        }
     
        return $title;
    }

    public static function get_fliter_data($term_id){

        $fliter_group = (array)b2_get_option('template_fliter','fliter_group');

        if(!empty($fliter_group)){
            foreach ($fliter_group as $key => $value) {
                if(isset($value['cat']) && $value['show']){
                    if(in_array((string)$term_id,$value['cat'])){
                        return array($value);
                    }
                }
            }
        }

        $filters = get_term_meta($term_id,'b2_filter',true);

        return $filters;
    }

    /**
     * 存档页面筛选html
     *
     * @return string
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public function archive_filters(){

        $term = get_queried_object();
        if(!isset($term->term_id)) return;
        
        $request = http_build_query($_REQUEST);
        $request = $request ? '?'.$request : '';

        $request = remove_query_arg('archiveSearch',$request);
        
        global $wp;
        $url = B2_HOME_URI.'/'.$wp->request;
        $url = preg_replace('#page/([^/]*)$#','', $url);

        $filters = self::get_fliter_data($term->term_id);

        if(!isset($filters[0]['show']) || !$filters[0]['show']) return '';

        $html = '<div id="filter-top">
        <div class="filters-box">
        <ul>';

        if(isset($filters[0]['cat'])){
            $html .= self::filter_cat($filters[0]['cat'],$term,$request,$url);
        }
        if(isset($filters[0]['collection'])){
            $html .= self::filter_collection($filters[0]['collection'],$term,$request,$url);
        }
        if(isset($filters[0]['tag'])){
            $html .= self::filter_tag($filters[0]['tag'],$term,$request,$url);
        }
        if(isset($filters[0]['meta'])){
            $html .= self::filter_meta($filters[0]['meta'],$request,$url);
        }

        $html .= '</ul></div></div>';
        
        return $html;
    }

    /**
     * 自定义字段筛选
     *
     * @param string $meta 自定义字段的设置项
     *
     * @return bool 设置项错误或为空
     * @return string 设置项转html
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function filter_meta($meta,$request,$url){

        if(empty($meta)) return;

        $meta_filters = self::meta_str_to_array($meta);
        if(!$meta_filters) return;

        $html = '';
        foreach($meta_filters as $k=>$v){

            $a = '<div class="filter-items"><a href="'.(remove_query_arg($v['meta_key'],$request) ?: $url).'" data-key="all" class="'.(!isset($_GET[$v['meta_key']]) ? 'current' : '').'">'.__('全部','b2').'</a>';
            foreach($v['meta_value'] as $_k=>$_v){
                $a .= '<a class="'.(isset($_GET[$v['meta_key']]) && $_GET[$v['meta_key']] == $_v['meta_key'] ? 'current' : '').'" href="'.(add_query_arg($v['meta_key'],$_v['meta_key'],$url.$request)).'" data-key="'.$_v['meta_key'].'" title="'.$_v['meta_name'].'">'.$_v['meta_name'].'</a>';
            }
            $a .= '</div>';

            $html .= '<li><div class="filter-name">'.$v['name'].'：</div>'.$a.'</li>';
        }

        return $html;
    }

    /**
     * 排序筛选
     *
     * @param array $orders 排序的筛选项
     *
     * @return bool 设置项错误或为空
     * @return string 设置项转html
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function filter_orderby($request,$url,$term,$filters){

            $html = '';
        
            if(in_array('date',$filters[0]['order'])){
                $html .= '<a href="'.(add_query_arg('post_order','new',$url.$request)).'" class="'.(!isset($_GET['post_order']) || (isset($_GET['post_order']) && $_GET['post_order'] == 'new') ? 'current' : '').'">'.__('最新','b2').'</a>';
            }
           
            if(in_array('random',$filters[0]['order'])){
                $html .= '<a href="'.(add_query_arg('post_order','random',$url.$request)).'" class="'.(isset($_GET['post_order']) && $_GET['post_order'] == 'random' ? 'current' : '').'">'.__('随机','b2').'</a>';
            }
    
            if(in_array('views',$filters[0]['order'])){
                $html .= '<a href="'.(add_query_arg('post_order','views',$url.$request)).'" class="'.(isset($_GET['post_order']) && $_GET['post_order'] == 'views' ? 'current' : '').'">'.__('最多浏览','b2').'</a>';
            }
    
            if(in_array('like',$filters[0]['order'])){
                $html .= '<a href="'.(add_query_arg('post_order','like',$url.$request)).'" class="'.(isset($_GET['post_order']) && $_GET['post_order'] == 'like' ? 'current' : '').'">'.__('最多喜欢','b2').'</a>';
            }
            
            if(in_array('comments',$filters[0]['order'])){
                $html .= '<a href="'.(add_query_arg('post_order','comments',$url.$request)).'" class="'.(isset($_GET['post_order']) && $_GET['post_order'] == 'comments' ? 'current' : '').'">'.__('最多评论','b2').'</a>';
            }
    
            if($html){
                return '<div class="tax-order-box"><div class="order-items">'.$html.'</div></div>';
            }
        

    }

    /**
     * 分类目录筛选
     *
     * @param array $term_id 允许筛选的分类目录数组
     *
     * @return bool 设置项错误或为空
     * @return string 设置项转html
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function filter_cat($cats,$term,$request,$url){
        if(empty($cats)) return;

        $is_tax = $term->taxonomy === 'category';

        $a = '';
        foreach($cats as $k=>$v){
            $_term = get_term_by('id',$v, 'category');
            if(isset($_term->term_id)){
                if($is_tax){
                    $url = get_term_link($_term->term_id).$request;
                }else{
                    $url = add_query_arg('post_cat',$v,$url.$request);
                }
    
                $a .= '<a href="'.$url.'" class="'.($term->term_id == $v || isset($_GET['post_cat']) && $_GET['post_cat'] == $v ? 'current' : '').'" title="'.$_term->name.'">'.$_term->name.'</a>';
            }
        }

        if($a){
            if(!$is_tax){
                $a = '<a href="'.(remove_query_arg('post_cat',$url.$request)).'" class="'.(!isset($_GET['post_cat']) ? 'current' : '').'">'.__('全部','b2').'</a>'.$a;
            }
            return '<li><div class="filter-name">'.__('分类','b2').'：</div><div class="filter-items">'.$a.'</div></li>';
        }
    }

    
    /**
     * 专题筛选
     *
     * @param array $term_id 允许筛选的分类目录数组
     *
     * @return bool 设置项错误或为空
     * @return string 设置项转html
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function filter_collection($collection,$term,$request,$url){
        if(empty($collection)) return;

        $is_tax = $term->taxonomy === 'collection';

        $a = '';
        foreach($collection as $k=>$v){
            $_term = get_term_by('id',$v, 'collection');
            if(isset($_term->term_id)){
                if($is_tax){
                    $url = get_term_link($_term->term_id).$request;
                }else{
                    $url = add_query_arg('collection',$v,$url.$request);
                }
    
                $a .= '<a href="'.$url.'" class="'.($term->term_id === $v || (isset($_GET['collection']) && $_GET['collection'] == $v) ? 'current' : '').'" title="'.$_term->name.'">'.$_term->name.'</a>';
            }
        }

        $collection_name = b2_get_option('normal_custom','custom_collection_name');

        if($a){
            if(!$is_tax){
                $a = '<a href="'.(remove_query_arg('collection',$url.$request)).'" class="'.(!isset($_GET['collection']) ? 'current' : '').'">'.__('全部','b2').'</a>'.$a;
            }
            return '<li><div class="filter-name">'.$collection_name.'：</div><div class="filter-items">'.$a.'</div></li>';
        }
    }

    /**
     * 标签筛选
     *
     * @param array $tags 允许筛选的标签
     *
     * @return bool 设置项错误或为空
     * @return string 设置项转html
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function filter_tag($tags,$term,$request,$_url){
        if(empty($tags)) return;

        $is_tax = $term->taxonomy === 'post_tag';

        //分行
        $tags = explode(PHP_EOL,$tags);
        $tags = array_filter($tags);

        $html = '';
        $i = 0;
        foreach ($tags as $k_l => $v_l) {
            $list = explode('|',$v_l);
            if($list){
                $i++;
                $name = $list[0];
                $list = explode(',',$list[1]);
                $list = array_filter($list);
    
                if(!empty($list)){

                    $a = '';
                    foreach($list as $k=>$v){
                        $_term = get_term_by('name',$v, 'post_tag');
                        
                        if(isset($_term->term_id)){
                            if($is_tax){
                                $url = get_term_link($_term->term_id).$request;
                            }else{
                                $url = add_query_arg('tags'.$i,$_term->slug,$_url.$request);
                            }
        
                            $a .= '<a href="'.$url.'" class="'.($term->slug === $_term->slug || (isset($_GET['tags'.$i]) && $_GET['tags'.$i] == urldecode($_term->slug)) ? 'current' : '').'" title="'.$v.'">'.$v.'</a>';
                        }
                        
                    }
    
                    if($a){
                        if(!$is_tax){
                            $a = '<a href="'.(remove_query_arg('tags'.$i,$_url.$request)).'" class="'.(!isset($_GET['tags'.$i]) ? 'current' : '').'">'.__('全部','b2').'</a>'.$a;
                        }
                        $html .= '<li><div class="filter-name">'.$name.'：</div><div class="filter-items">'.$a.'</div></li>';
                    }
                    
                }
            }
        }

        if($html){
            return $html;
        }

        return;
    }

    /**
     * 自定义字段的字符串设置项转数组
     *
     * @param string $meta_filters 字符串设置项
     *
     * @return bool 设置项错误或为空
     * @return array 设置项的数组
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function meta_str_to_array($meta_filters){

        if($meta_filters){
            $meta_filters = trim($meta_filters);

            //分行转数组
            $lin_arg = explode(PHP_EOL,$meta_filters);
            if(!empty($lin_arg)){

                $arg = array();

                foreach($lin_arg as $k=>$v){
                    $_arg = array();

                    //每行根据|切割数组
                    $r = explode('|',$v);
                    if(!empty($r)){
                        $_arg['name'] = trim($r[0]);
                        $_arg['meta_key'] = trim($r[1]);

                        //每行根据,切割数组
                        $v_arr = explode(',',trim($r[2]));
                        $_lin_arg = array();
                        foreach($v_arr as $_v){

                            //每行根据=切割数组
                            $_k_v = explode('=',$_v);
                            $_lin_arg[] = array(
                                'meta_name'=>$_k_v[0],
                                'meta_key'=>isset($_k_v[1]) ? $_k_v[1] : ''
                            );
                        }

                        $_arg['meta_value'] = $_lin_arg;
                        $arg[] = $_arg;
                    }
                    
                }

                return $arg;
            }
        }

        return false;
    }

    /**
     * 存档页面顶部的特色图
     *
     * @return string
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public function archive_top_info(){

        $thumb = '';
        $term = get_queried_object();

        $title = get_the_archive_title();
        global $wp;
        $url = B2_HOME_URI.'/'.$wp->request;
        $request = http_build_query($_REQUEST);
        $request = $request ? '?'.$request : '';
        $request = remove_query_arg('archiveSearch',$request);
        $url = preg_replace('#page/([^/]*)$#','', $url);

        $img = '';
        if(isset($term->term_id)){
            $img = get_term_meta($term->term_id,'b2_tax_img',true);
        }else{
            return;
        }

        $img = b2_get_thumb(array('thumb'=>$img,'width'=>300,'height'=>80));

        $search = isset($_GET['archiveSearch']) && !empty($_GET['archiveSearch']) ? $_GET['archiveSearch'] : '';
        
        // $tags = Post::get_post_tags(4);

        // $tag_list = '';

        // foreach ($tags as $k => $v) {
        //     $tag_list .= '<a href="'.$v['link'].'">'.$v['name'].'</a> ';
        // }

        $fliter = self::archive_filters();

        $filters = self::get_fliter_data($term->term_id);

        do_action('b2_archive_top',$term);

        ?>
        <div class="tax-header mg-t-">
            <div>
            <!-- <div class="b2-light-dark-outside-box">
                <div class="b2-light-dark-outside">
                    <div class="b2-light-dark-bg" style="background-image:url(<?php echo $img; ?>)"></div>
                </div>
            </div> -->
            <div class="wrapper">
                <div class="tax-info">
                    <?php if(isset($filters[0]['order']) && $filters[0]['order']){ ?>
                        <div class="tax-info-item">
                            <button class="fliter-button" @click.stop="show('hot')">
                            <?php 
                                $order = isset($_GET['post_order']) ? $_GET['post_order'] : '';
                                switch ($order) {
                                    case 'comments':
                                        $text = __('最多评论','b2');
                                        break;
                                    case 'views':
                                        $text = __('最多浏览','b2');
                                        break;
                                    case 'like':
                                        $text = __('最多喜欢','b2');
                                        break;
                                    case 'new':
                                        $text = __('最新','b2');
                                        break;
                                    case 'random':
                                        $text = __('随机','b2');
                                        break;
                                    default:
                                        $text = __('排序','b2');
                                        break;
                                }

                            echo $text.b2_get_icon('b2-arrow-down-s-line'); 
                            ?></button>
                        </div>
                    <?php } ?>
                    <?php if($fliter){ ?>
                        <div class="tax-info-item mobile-show" @click.stop="show('cat')">
                            <button class="fliter-button"><?php echo __('筛选','b2').b2_get_icon('b2-arrow-down-s-line'); ?></button>
                        </div>
                    <?php } ?>
                    <div class="tax-search tax-info-item">
                        <form method="get" action="<?php echo $url.$request; ?>" autocomplete="off">
                            <input type="text" name="archiveSearch" class="b2-radius" placeholder="<?php echo sprintf(__('在「%s」中搜索','b2'),$title);?>" value="<?php echo $search; ?>">
                            <button class="text"><?php echo b2_get_icon('b2-search-line'); ?></button>
                        </form>
                    </div>
                    <div class="tax-info-item">
                        <a class="fliter-button button" href="<?php echo b2_get_custom_page_url('tags'); ?>" target="_blank"><?php echo __('全部标签','b2'); ?></a>
                    </div>
                    <div class="tax-title">
                        <h1 class="b2-radius" style="background-image:url(<?php echo $img; ?>)"><span><?php echo $title; ?></span></h1>
                    </div>
                </div>
                <?php if(isset($filters[0]['order']) && $filters[0]['order']){ ?>
                    <div class="tax-fliter-hot" v-show="showFliter.hot" v-cloak>
                        <?php echo self::filter_orderby($request,$url,$term,$filters); ?>
                    </div>
                <?php } ?>
                <?php if($fliter){ ?>
                    <div :id="showFliter.cat ? 'fliter-show' : ''" class="tax-fliter-cat box b2-radius mg-b" ref="filterContent">
                        <?php echo $fliter; ?>
                    </div>
                <?php } ?>
            </div>
        </div>
        </div>
        
    <?php
    }
}