<?php
namespace B2\Modules\Settings;

use B2\Modules\Common\User;

class Newsflashes{

    public static $default_settings = array(
       'newsflashes_open'=>1,
       'newsflashes_show_count'=>20,
       'newsflashes_tags'=>20,
       'newsflashes_vote_up_text'=>'利好',
       'newsflashes_vote_down_text'=>'利空',
       'newsflashes_tdk_keywords'=>'',
       'newsflashes_tdk_desc'=>'',
       'newsflashes_tdk_title'=>''
       
    );

    public function init(){
        add_action('cmb2_admin_init',array($this,'newsflashes_settings'));
        //add_filter( 'wp_editor_settings', array($this,'type_editor_settings' ));
    }

    public function type_editor_settings($settings){

        global $post_type;

        if ( $post_type == 'newsflashes' ) {
    
            $settings[ 'tinymce' ] = false;
            $settings['_content_editor_dfw'] = false;
            $settings['drag_drop_upload'] = false;
        }

    
        return $settings;
    }

    public static function get_default_settings($key){
       
        $arr = array(
            'newsflashes_name'=>b2_get_option('normal_custom','custom_newsflashes_name'),
            'newsflashes_can_post'=>array(),
            'newsflashes_cover'=>B2_THEME_URI.'/Assets/fontend/images/footer-bg.jpg'
        );

        if($key == 'all'){
            return $arr;
        }

        if(isset($arr[$key])){
            return $arr[$key];
        }
    }

    public function newsflashes_settings(){

        $newsflashes_name = b2_get_option('normal_custom','custom_newsflashes_name');
        //常规设置
        $newsflashes = new_cmb2_box( array(
            'id'           => 'b2_newsflashes_main_options_page',
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_newsflashes_main',
            'tab_group'    => 'b2_newsflashes_options',
            'parent_slug'     => 'b2_main_options',
            'tab_title'    => sprintf(__('%s首页','b2'),$newsflashes_name),
            'menu_title'   => sprintf(__('%s设置','b2'),$newsflashes_name),
            'save_button'     => __( '保存设置', 'b2' )
        ));

        $newsflashes->add_field(array(
            'name'    => sprintf(__( '是否启用%s功能', 'b2' ),$newsflashes_name),
            'id'=>'newsflashes_open',
            'type'=>'select',
            'options'=>array(
                1=>__('开启','b2'),
                0=>__('关闭','b2')
            ),
            'default'=>self::$default_settings['newsflashes_open']
        ));

        $newsflashes->add_field(array(
            'name'    => sprintf(__( '%s首页封面图片', 'b2' ),$newsflashes_name),
            'id'=>'newsflashes_cover',
            'type'=>'file',
            'options' => array(
                'url' => true, 
            ),
            'default'=>self::get_default_settings('newsflashes_cover'),
            'desc'=>'显示在'.$newsflashes_name.'首页顶部的图片，其他'.$newsflashes_name.'标签的封面，请前往<a href="'.admin_url('/edit-tags.php?taxonomy=newsflashes_tags&post_type=newsflashes').'" target="_blank">'.$newsflashes_name.'标签设置</a>',
        ));

        $newsflashes->add_field(array(
            'name'    => sprintf(__( '%s首页描述', 'b2' ),$newsflashes_name),
            'id'=>'newsflashes_desc',
            'type'=>'text',
            'desc'=>sprintf(__('显示在%s封面之上，%s标题之下，其他%s标签的封面图，请前往%s设置','b2'),$newsflashes_name,$newsflashes_name,$newsflashes_name,'<a href="'.admin_url('/edit-tags.php?taxonomy=newsflashes_tags&post_type=newsflashes').'" target="_blank">'.$newsflashes_name.'标签设置</a>'),
        ));

        $newsflashes->add_field(array(
            'name'    => sprintf(__( '%s首页SEO标题', 'b2' ),$newsflashes_name),
            'id'=>'newsflashes_tdk_title',
            'type'=>'text',
            'default'=>self::get_default_settings('newsflashes_tdk_title'),
        ));

        $newsflashes->add_field(array(
            'name'    => sprintf(__( '%s首页SEO描述', 'b2' ),$newsflashes_name),
            'id'=>'newsflashes_tdk_desc',
            'type'=>'textarea_small',
            'default'=>self::get_default_settings('newsflashes_tdk_desc'),
        ));

        $newsflashes->add_field(array(
            'name'    => sprintf(__( '%s首页SEO标签', 'b2' ),$newsflashes_name),
            'id'=>'newsflashes_tdk_keywords',
            'type'=>'text',
            'default'=>self::get_default_settings('newsflashes_tdk_keywords'),
        ));

        $newsflashes->add_field(array(
            'name' => sprintf(__('发布%s待选标签','b2'),$newsflashes_name),
            'id'   => 'newsflashes_tags',
            'type' => 'textarea_small',
            'desc'=>sprintf(__('用户在发布%s的时候可以选择这些待选的标签，请直接输入%s标签的名称，多个标签用英文逗号隔开','b2'),$newsflashes_name,$newsflashes_name),
        ));

        $newsflashes->add_field(array(
            'name'    => sprintf(__( '%s每页显示多少篇最新%s', 'b2' ),$newsflashes_name,$newsflashes_name),
            'id'=>'newsflashes_show_count',
            'type'=>'text',
            'default'=>self::$default_settings['newsflashes_show_count']
        ));

        $newsflashes->add_field(array(
            'name'    => sprintf(__( '%s投票文字(利好)', 'b2' ),$newsflashes_name),
            'id'=>'newsflashes_vote_up_text',
            'type'=>'text',
            'default'=>self::$default_settings['newsflashes_vote_up_text']
        ));

        $newsflashes->add_field(array(
            'name'    => sprintf(__( '%s投票文字(利空)', 'b2' ),$newsflashes_name),
            'id'=>'newsflashes_vote_down_text',
            'type'=>'text',
            'default'=>self::$default_settings['newsflashes_vote_down_text']
        ));

        $lvs = User::get_user_roles();

        $setting_lvs = array();
        foreach($lvs as $k => $v){
            $setting_lvs[$k] = $v['name'];
        }

        $newsflashes->add_field(array(
            'name' => __('哪些用户可以直接发布，不用审核','b2'),
            'id'   => 'newsflashes_can_post',
            'type' => 'multicheck_inline',
            'options'=>$setting_lvs,
            'desc'=> __('建议只选择特定级别的用户，以免出现乱发广告的情况，除此之外，管理员和编辑默认也不需要审核','b2'),
        ));
    }
}