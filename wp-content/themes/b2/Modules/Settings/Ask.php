<?php namespace B2\Modules\Settings;

use B2\Modules\Common\Ask as AskFn;
use B2\Modules\Common\User;

class Ask{

    public static $default_settings = array(
        'ask_open'=>1,
        'ask_cats'=>[],
        'ask_page_count'=>'15',
        'ask_order'=>'qz',
        'ask_can_post'=>3,
        'ask_can_answer'=>8,
        'po_answer_role'=>[],
        'po_role'=>[]
    );

     
    public function init(){
        add_action('cmb2_admin_init',array($this,'ask_settings'));
        add_filter( 'manage_ask_posts_columns', array($this,'filter_ask_columns'));
        add_action( 'manage_ask_posts_custom_column', array($this,'realestate_column'), 10, 2);
        add_filter( 'manage_edit-ask_sortable_columns', array($this,'filter_ask_sortable'));
    }

    public function filter_ask_sortable($sortable){
        $sortable['post_date'] = 'post_date';
        $sortable['b2_ask_owner'] = 'b2_ask_owner';
        $sortable['post_status'] = 'post_status';
        return $sortable;
    }

    public function filter_ask_columns($columns){
       
        $new['b2_ask_owner'] = __('提问者','b2');
        $new['post_status'] = __('状态','b2');

        array_insert($columns,2,$new);
        unset($columns['rel']);
        return $columns;
    }

    public function realestate_column($column, $post_id){

        if ( 'b2_ask_owner' === $column ) {
            $ask_owner = get_post_field('post_author',$post_id);

            $user = get_userdata($ask_owner);

            if($ask_owner && $user){
                echo '<a href="'.get_author_posts_url($ask_owner).'" target="_blank">'.$user->display_name.'</a>';
            }else{
                echo __('未名','b2');
            }
            
            return;
        }


        if('post_status' === $column){
            $status = get_post_field( 'post_status', $post_id);
            echo $status == 'publish' ? '<span class="green">'.__('已发布','b2').'</span>' : ($status == 'pending' ? '<span class="red">'.__('待审','b2').'</span>' : __('未发布','b2'));
        }

        return;
    }


    public static function get_default_settings($key){
       
        $arr = array(
            'ask_open'=>1,
            'ask_cats'=>[],
            'ask_page_count'=>'15',
            'ask_title'=>__('问答','b2'),
            'ask_name'=>__('问答','b2'),
            'ask_tdk_desc'=>'',
            'ask_tdk_keywords'=>'',
            'ask_order'=>'qz',
            'ask_can_post'=>3,
            'ask_can_answer'=>8,
            'po_answer_role'=>[],
            'po_role'=>[]
        );

        if($key == 'all'){
            return $arr;
        }
        
        if(isset($arr[$key])){
            return $arr[$key];
        }
    }

    public function ask_settings(){

        // $ask_meta = new_cmb2_box(array( 
        //     'id'            => 'single_ask_metabox',
        //     'title'         => __( '网址属性', 'b2' ),
        //     'object_types'  => array( 'ask'),
        //     'context'       => 'side',
        //     'priority'      => 'high',
        //     'show_names'    => true,
        // ));

        // $ask_meta->add_field(array(
        //     'name' => __('站长ID','b2'),
        //     'id'   => 'b2_ask_owner',
        //     'type' => 'text',
        //     'desc'=> __('请直接填写用户ID，没有留空即可','b2'),
        // ));

        // $ask_meta->add_field(array(
        //     'name' => __('网址','b2'),
        //     'id'   => 'b2_ask_to',
        //     'type' => 'text',
        //     'desc'=> __('请直接输入网址，记得带上 http','b2'),
        // ));

        // $ask_meta->add_field(array(
        //     'name' => __('网址图标','b2'),
        //     'id'   => 'b2_ask_icon',
        //     'type'             => 'file',
        //     'options' => array(
        //         'url' => true, 
        //     )
        // ));


        $ask_name = b2_get_option('normal_custom','custom_ask_name');

        //常规设置
        $ask = new_cmb2_box( array(
            'id'           => 'b2_ask_main_options_page',
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_ask_main',
            'tab_group'    => 'b2_ask_options',
            'parent_slug'     => 'b2_main_options',
            'tab_title'    => sprintf(__('%s首页','b2'),$ask_name),
            'menu_title'   => sprintf(__('%s设置','b2'),$ask_name),
            'save_button'     => __( '保存设置', 'b2' )
        ));

        $ask_cats = array();

        $ask_data = get_terms( 'ask_cat', array(
            'hierarchical' => true,
            'hide_empty' => true,
            'cache_domain'=>'b2_ask_cat'
        ) );

        foreach( $ask_data as $v ) {
            $ask_cats[$v->term_id] = $v->name;
        };

        $ask->add_field(array(
            'name'    => sprintf(__( '是否启用%s', 'b2' ),$ask_name),
            'id'=>'ask_open',
            'type'    => 'select',
            'options'=>array(
                1=>__('启用','b2'),
                0=>__('关闭','b2')
            )
        ));

        $ask->add_field(array(
            'name'    => sprintf(__( '%s首页标题', 'b2' ), $ask_name),
            'id'=>'ask_title',
            'type'    => 'text',
            'default' =>__('问答','b2'),
            'desc'=>sprintf(__('显示在%s首页的顶部'), $ask_name)
        ));

        $ask->add_field(array(
            'name'    => sprintf(__( '%s首页SEO名称', 'b2' ),$ask_name),
            'id'=>'ask_name',
            'type'=>'text',
            'default'=>self::get_default_settings('ask_name')
        ));

        $ask->add_field(array(
            'name'    => sprintf(__( '%s首页SEO描述', 'b2' ),$ask_name),
            'id'=>'ask_tdk_desc',
            'type'=>'textarea_small',
            'default'=>self::get_default_settings('ask_tdk_desc')
        ));

        $ask->add_field(array(
            'name'    => sprintf(__( '%s首页SEO标签', 'b2' ),$ask_name),
            'id'=>'ask_tdk_keywords',
            'type'=>'text',
            'default'=>self::get_default_settings('ask_tdk_keywords')
        ));

        // $ask->add_field(array(
        //     'name'    => sprintf(__( '问答首页顶部要显示的%s分类', 'b2' ),$ask_name),
        //     'id'=>'ask_cats',
        //     'type'    => 'pw_multiselect',
        //     'options' =>$ask_cats,
        //     'desc'=>sprintf(__('请确保%s中有帖子，否则此处不显示','b2'),$ask_name)
        // ));

        $ask->add_field(array(
            'name'=>sprintf(__('%s列表中每页显示的数量','b2'),$ask_name),
            'id'=>'ask_page_count',
            'type'    => 'text',
            'default' => '15',
        ));

        // $ask->add_field(array(
        //     'name'=>__('排序方法','b2'),
        //     'id'=>'ask_order',
        //     'type'    => 'select',
        //     'options'=>array(
        //         'date'=>__('按照时间排序','b2'),
        //         'qz'=>__('按照权重智能排序','b2'),
        //     ),
        //     'default' => 'qz'
        // ));

        $ask_submit = new_cmb2_box( array(
            'id'           => 'b2_ask_submit_options_page',
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_ask_submit',
            'tab_group'    => 'b2_ask_options',
            'parent_slug'     => '/admin.php?page=b2_ask_main',
            'tab_title'    => __('发帖设置','b2'),
            'save_button'     => __( '保存设置', 'b2' )
        ));

        $ask_submit->add_field(array(
            'name'    => sprintf(__( '允许入驻的%s分类', 'b2' ),$ask_name),
            'id'=>'ask_submit_cats',
            'type'    => 'pw_multiselect',
            'options' =>$ask_cats,
        ));

        $ask_submit->add_field(array(
            'name'    => sprintf(__( '允许入驻的%s分类', 'b2' ),$ask_name),
            'id'=>'ask_submit_cats',
            'type'    => 'pw_multiselect',
            'options' =>$ask_cats,
            'desc'=>sprintf(__('请确保%s中有帖子，否则此处不显示','b2'),$ask_name)
        ));

        $ask_submit->add_field(array(
            'name'=>sprintf(__('允许提交的待审核的帖子数量','b2'),$ask_name),
            'id'=>'ask_can_post',
            'type'    => 'text',
            'default' => '3',
        ));

        $lvs = User::get_user_roles();

        $setting_lvs = array();
        foreach($lvs as $k => $v){
            $setting_lvs[$k] = $v['name'];
        }

        if(b2_get_option('verify_main','verify_allow')){
            $setting_lvs['verify'] = __('认证用户','b2');
        }

        $ask_submit->add_field(array(
            'name' => __('哪些等级发帖时直接发布，不用审核','b2'),
            'id'   => 'po_role',
            'type' => 'multicheck_inline',
            'options'=>$setting_lvs,
            'desc'=> __('选择之后，这些等级的用户发布帖子之后不用审核','b2')
        ));

        $ask_submit->add_field(array(
            'name'=>sprintf(__('允许提交的待审核的回答数量','b2'),$ask_name),
            'id'=>'ask_can_answer',
            'type'    => 'text',
            'default' => '8',
        ));

        $ask_submit->add_field(array(
            'name' => __('哪些等级发布回答时直接发布，不用审核','b2'),
            'id'   => 'po_answer_role',
            'type' => 'multicheck_inline',
            'options'=>$setting_lvs,
            'desc'=> __('选择之后，这些等级的用户发布回答之后不用审核','b2')
        ));


        // $ask_submit->add_field(array(
        //     'name'    => sprintf(__( '网站描述允许的最小字数', 'b2' ),$ask_name),
        //     'id'=>'ask_submit_content_count',
        //     'type'    => 'text',
        //     'default' =>'100',
        //     'desc'=>__('网站描述需要最少输入多少个字，才允许发布','b2')
        // ));

        // $ask_single = new_cmb2_box( array(
        //     'id'           => 'b2_ask_single_options_page',
        //     'object_types' => array( 'options-page' ),
        //     'option_key'      => 'b2_ask_single',
        //     'tab_group'    => 'b2_ask_options',
        //     'parent_slug'     => '/admin.php?page=b2_ask_main',
        //     'tab_title'    => __('连接内页设置','b2'),
        //     'save_button'     => __( '保存设置', 'b2' )
        // ));

        // $ask_single->add_field(array(
        //     'name'    => __( '连接内页顶部广告位', 'b2' ),
        //     'id'=>'ask_single_top',
        //     'type'    => 'textarea_code',
        // ));

        // $ask_single->add_field(array(
        //     'name'    => __( '连接内页底部广告位', 'b2' ),
        //     'id'=>'ask_single_bottom',
        //     'type'    => 'textarea_code',
        // ));
    }
}