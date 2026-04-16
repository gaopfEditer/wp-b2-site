<?php namespace B2\Modules\Settings;

class Links{

    public static $default_settings = array(
        'link_open'=>1,
        'link_cats'=>[],
        'link_show_children'=>0,
        'link_junp'=>'self',
        'link_count'=>'5',
        'link_count_total'=>'15',
        'link_order'=>'link_rating',
        'link_meta'=>['title','children','more','icon','desc','user','like'],
        'link_submit_content_count'=>100
    );

     
    public function init(){
        add_action('cmb2_admin_init',array($this,'link_settings'));
        add_filter( 'manage_links_posts_columns', array($this,'filter_link_columns'));
        add_action( 'manage_links_posts_custom_column', array($this,'realestate_column'), 10, 2);
        add_filter( 'manage_edit-links_sortable_columns', array($this,'filter_link_sortable'));
    }

    public function filter_link_sortable($sortable){
        $sortable['post_date'] = 'post_date';
        $sortable['b2_link_owner'] = 'b2_link_owner';
        $sortable['post_status'] = 'post_status';
        return $sortable;
    }

    public function filter_link_columns($columns){
       
        $new['b2_link_owner'] = __('站长','b2');
        $new['b2_link_to'] = __('网址','b2');
        $new['post_status'] = __('状态','b2');

        array_insert($columns,2,$new);
        unset($columns['rel']);
        return $columns;
    }

    public function realestate_column($column, $post_id){

        if ( 'b2_link_owner' === $column ) {
            $link_owner = get_post_meta($post_id,'b2_link_owner',true);
            $user = get_userdata($link_owner);
            if($link_owner && $user){
                echo '<a href="'.get_author_posts_url($link_owner).'" target="_blank">'.$user->display_name.'</a>';
            }else{
                echo __('未名','b2');
            }
            
            return;
        }
        if('b2_link_to' === $column){
            $link = get_post_meta($post_id,'b2_link_to',true);
            echo '<a href="'.$link.'" target="_blank">'.$link.'</a>';
        }

        if('post_status' === $column){
            $status = get_post_field( 'post_status', $post_id);
            echo $status == 'publish' ? '<span class="green">'.__('已发布','b2').'</span>' : ($status == 'pending' ? '<span class="red">'.__('待审','b2').'</span>' : __('未发布','b2'));
        }
    }


    public static function get_default_settings($key){
       
        $arr = array(
            'link_open'=>1,
            'link_cats'=>[],
            'link_show_children'=>0,
            'link_junp'=>'self',
            'link_count'=>'5',
            'link_count_total'=>'15',
            'link_order'=>'link_rating',
            'link_meta'=>['title','children','more','icon','desc','user','like'],
            'link_title'=>__('网址导航','b2'),
            'link_name'=>__('网址导航','b2'),
            'link_tdk_desc'=>'',
            'link_tdk_keywords'=>'',
            'link_submit_content_count'=>100
        );

        if($key == 'all'){
            return $arr;
        }
        
        if(isset($arr[$key])){
            return $arr[$key];
        }
    }

    public function link_settings(){

        $links_meta = new_cmb2_box(array( 
            'id'            => 'single_links_metabox',
            'title'         => __( '网址属性', 'b2' ),
            'object_types'  => array( 'links'),
            'context'       => 'side',
            'priority'      => 'high',
            'show_names'    => true,
        ));

        $links_meta->add_field(array(
            'name' => __('站长ID','b2'),
            'id'   => 'b2_link_owner',
            'type' => 'text',
            'desc'=> __('请直接填写用户ID，没有留空即可','b2'),
        ));

        $links_meta->add_field(array(
            'name' => __('网址','b2'),
            'id'   => 'b2_link_to',
            'type' => 'text_url',
            'desc'=> __('请直接输入网址，记得带上 http','b2'),
        ));

        $links_meta->add_field(array(
            'name' => __('网址图标','b2'),
            'id'   => 'b2_link_icon',
            'type'             => 'file',
            'options' => array(
                'url' => true, 
            )
        ));


        $links_name = b2_get_option('normal_custom','custom_links_name');

        //常规设置
        $links = new_cmb2_box( array(
            'id'           => 'b2_links_main_options_page',
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_links_main',
            'tab_group'    => 'b2_links_options',
            'parent_slug'     => 'b2_main_options',
            'tab_title'    => sprintf(__('%s首页','b2'),$links_name),
            'menu_title'   => sprintf(__('%s设置','b2'),$links_name),
            'save_button'     => __( '保存设置', 'b2' )
        ));

        $link_cats = array();

        $links_data = get_terms( 'link_cat', array(
            'hierarchical' => true,
            'hide_empty' => true,
            'cache_domain'=>'b2_link_cat'
        ) );

        foreach( $links_data as $v ) {
            $link_cats[$v->term_id] = $v->name;
        };

        $links->add_field(array(
            'name'    => sprintf(__( '是否启用%s', 'b2' ),$links_name),
            'id'=>'link_open',
            'type'    => 'select',
            'options'=>array(
                1=>__('启用','b2'),
                0=>__('关闭','b2')
            )
        ));

        $links->add_field(array(
            'name'    => sprintf(__( '%s首页标题', 'b2' ),$links_name),
            'id'=>'link_title',
            'type'    => 'text',
            'default' =>__('导航链接','b2'),
            'desc'=>sprintf(__('显示在%s首页的顶部'),$links_name)
        ));

        $links->add_field(array(
            'name'    => sprintf(__( '%s首页SEO名称', 'b2' ),$links_name),
            'id'=>'link_name',
            'type'=>'text',
            'default'=>self::get_default_settings('link_name')
        ));

        $links->add_field(array(
            'name'    => sprintf(__( '%s首页SEO描述', 'b2' ),$links_name),
            'id'=>'link_tdk_desc',
            'type'=>'textarea_small',
            'default'=>self::get_default_settings('link_tdk_desc')
        ));

        $links->add_field(array(
            'name'    => sprintf(__( '%s首页SEO标签', 'b2' ),$links_name),
            'id'=>'link_tdk_keywords',
            'type'=>'text',
            'default'=>self::get_default_settings('link_tdk_keywords')
        ));

        $links->add_field(array(
            'name'    => sprintf(__( '要显示的%s分类', 'b2' ),$links_name),
            'id'=>'link_cats',
            'type'    => 'pw_multiselect',
            'options' =>$link_cats,
            'desc'=>sprintf(__('请确保%s中有链接，否则此处不显示','b2'),$links_name)
        ));

        $links->add_field(array(
            'name'=>sprintf(__('是否包含子%s中的链接','b2'),$links_name),
            'id'=>'link_show_children',
            'type'    => 'select',
            'options'=>array(
                1=>__('显示','b2'),
                0=>__('不显示','b2')
            ),
            'default' => 0,
            'desc'=>sprintf(__('如果此%s中有子%s，您可以选择是否显示子%s中的链接，注意，如果子%s特别多（大于20个）可能会有性能问题。','b2'),$links_name,$links_name,$links_name,$links_name)
        ));

        $links->add_field(array(
            'name'=>__('点击跳转方式','b2'),
            'id'=>'link_junp',
            'type'    => 'select',
            'options'=>array(
                'self'=>__('跳转到本站内页','b2'),
                'target'=>__('跳转到目标站点','b2')
            ),
            'default' => 'self'
        ));

        $links->add_field(array(
            'name'=>__('每行显示几个','b2'),
            'id'=>'link_count',
            'type'    => 'text',
            'default' => '5'
        ));

        $links->add_field(array(
            'name'=>__('一共显示几个','b2'),
            'id'=>'link_count_total',
            'type'    => 'text',
            'default' => '15',
        ));

        $links->add_field(array(
            'name'=>__('排序方法','b2'),
            'id'=>'link_order',
            'type'    => 'select',
            'options'=>array(
                'DESC'=>__('最新添加的排在前面','b2'),
                'ASC'=>__('最后添加的排在前面','b2'),
                'link_rating'=>__('点赞最高的排在前面','b2')
            ),
            'default' => 'link_rating'
        ));

        $links->add_field(array(
            'name'=>__('显示哪些小部件','b2'),
            'id'=>'link_meta',
            'type'    => 'multicheck_inline',
            'options' => array(
                'title'=>__('模块标题','b2'),
                'children' => sprintf(__( '子%s', 'b2' ),$links_name),
                'more' => __( '更多按钮', 'b2' ),
                'icon'=>__( '图标', 'b2' ),
                'desc'=>__( '描述', 'b2' ),
                'user' => __( '站长', 'b2' ),
                'like' => __( '喜欢数量', 'b2' )
            ),
            'default' => array('title','children','more','icon','desc','user','like'),
        ));

        $link_submit = new_cmb2_box( array(
            'id'           => 'b2_links_submit_options_page',
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_links_submit',
            'tab_group'    => 'b2_links_options',
            'parent_slug'     => '/admin.php?page=b2_links_main',
            'tab_title'    => __('入驻设置','b2'),
            'save_button'     => __( '保存设置', 'b2' )
        ));

        $link_submit->add_field(array(
            'name'    => sprintf(__( '允许入驻的%s分类', 'b2' ),$links_name),
            'id'=>'link_submit_cats',
            'type'    => 'pw_multiselect',
            'options' =>$link_cats,
        ));

        $link_submit->add_field(array(
            'name'    => sprintf(__( '网站描述允许的最小字数', 'b2' ),$links_name),
            'id'=>'link_submit_content_count',
            'type'    => 'text',
            'default' =>'100',
            'desc'=>__('网站描述需要最少输入多少个字，才允许发布','b2')
        ));

        $link_single = new_cmb2_box( array(
            'id'           => 'b2_links_single_options_page',
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_links_single',
            'tab_group'    => 'b2_links_options',
            'parent_slug'     => '/admin.php?page=b2_links_main',
            'tab_title'    => __('连接内页设置','b2'),
            'save_button'     => __( '保存设置', 'b2' )
        ));

        $link_single->add_field(array(
            'name'    => __( '连接内页顶部广告位', 'b2' ),
            'id'=>'link_single_top',
            'type'    => 'textarea_code',
        ));

        $link_single->add_field(array(
            'name'    => __( '连接内页底部广告位', 'b2' ),
            'id'=>'link_single_bottom',
            'type'    => 'textarea_code',
        ));
    }
}