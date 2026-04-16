<?php namespace B2\Modules\Templates\PostType;
use B2\Modules\Templates\Modules\Sliders;
class Announcement{
    public function init(){
        //创建公告文章形式
        //add_action( 'init', array($this,'create_announcement' ),10,0);

        //如果是文章内页，添加查看全部公告按钮
        add_action('b2_single_content_before',array($this,'announcement_show_more'),3);

        //如果发布公告，清除顶部html缓存
    }

    /**
     * 创建公告文章形式
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public function create_announcement(){

        
    }

    /**
     * 获取公告数据
     *
     * @param array $arg
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function get_announcements($arg){

        $the_query = new \WP_Query($arg);

        $html = '';
        $_pages = 0;
        $titles = array();

        if ( $the_query->have_posts() ) {

            $_pages = $the_query->max_num_pages;

            ob_start();

            while ( $the_query->have_posts() ) {
                $the_query->the_post();

                //只获取标题
                if(isset($arg['announcement_type']) && $arg['announcement_type'] == 'title'){

                    $titles[] = array(
                        'title' => get_the_title(),
                        'link' => get_permalink(),
                        'date' => b2_timeago(get_the_date('Y-m-d G:i:s'))
                    );
        
                }else{
                    get_template_part( 'TempParts/Announcement/content');
                }
                
            }
            
            $html = ob_get_clean();
            
        }
        wp_reset_postdata();
        return array(
            'html'=>$html,
            'pages'=>$_pages,
            'titles'=>$titles
        );
    }

    /**
     * 文章内页公告导航
     *
     * @return string
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function announcement_show_more(){
        if(get_post_type() ==  'announcement'){
            $g_name = b2_get_option('normal_custom','custom_announcement_name');
            echo '
            <div class="announcement-show-more b2-pd box b2-radius">
                <span>'.$g_name.'</span>
                <span><a href="'.get_post_type_archive_link('announcement').'">'.sprintf(__('所有%s','b2'),$g_name).'</a></span>
            </div>
        ';
        }
        
    }
}