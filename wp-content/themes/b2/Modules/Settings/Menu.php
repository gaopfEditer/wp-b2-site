<?php
namespace B2\Modules\Settings;

class Menu {
    public function init(){
        add_filter('cmb2_nav_menus', array(__CLASS__,'regeister_menu'),90, 1);

        //add_filter('cmb2_nav_menu_fields_ym-menu', array(__CLASS__,'top_menu_custon_field'),99, 1);
        add_filter('cmb2_nav_menu_fields_top', array(__CLASS__,'top_menu_custon_field'),99, 1);
        add_filter('cmb2_nav_menu_fields_post', array(__CLASS__,'top_menu_custon_field'),99, 1);
        add_filter('cmb2_nav_menu_fields_newsflashes', array(__CLASS__,'top_menu_custon_field'),99, 1);
        add_filter('cmb2_nav_menu_fields_document', array(__CLASS__,'top_menu_custon_field'),99, 1);
        add_filter('cmb2_nav_menu_fields_shop', array(__CLASS__,'top_menu_custon_field'),99, 1);
        add_filter('cmb2_nav_menu_fields_circle', array(__CLASS__,'top_menu_custon_field'),99, 1);
        add_filter('cmb2_nav_menu_fields_links', array(__CLASS__,'top_menu_custon_field'),99, 1);
        add_filter('cmb2_nav_menu_fields_infomation', array(__CLASS__,'top_menu_custon_field'),99, 1);
        add_filter('cmb2_nav_menu_fields_ask', array(__CLASS__,'top_menu_custon_field'),99, 1);
    }

    public static function regeister_menu($menu_slugs){
        //$menu_slugs[] = 'ym-menu';
        $menu_slugs[] = 'top';
        $menu_slugs[] = 'post';
        $menu_slugs[] = 'newsflashes';
        $menu_slugs[] = 'document';
        $menu_slugs[] = 'shop';
        $menu_slugs[] = 'circle';
        $menu_slugs[] = 'links';
        $menu_slugs[] = 'infomation';
        $menu_slugs[] = 'ask';
        return $menu_slugs;
    }

    public static function top_menu_custon_field($fields){

        $fields['menu_type'] = array(
            'name'    => __( '菜单形式', 'b2' ),
            'id'=>'menu_type',
            'type' => 'radio_image',
            'options'          => array(
                'menu-3'    => __('下拉菜单','jikelao'), 
                'menu-4'  => __('列表菜单','jikelao'), 
                'menu-1' => __('图片菜单','jikelao'), 
                'menu-2' => __('分类菜单','jikelao'),
            ),
            'images_path'      => B2_THEME_URI,
            'images'           => array(
                'menu-3'    => '/Assets/admin/images/menu-3.svg',
                'menu-4'  => '/Assets/admin/images/menu-4.svg',
                'menu-1' => '/Assets/admin/images/menu-1.svg',
                'menu-2' => '/Assets/admin/images/menu-2.svg'
            ),
            'default'=>'menu-3'
        );

        $fields['menu_color'] = array(
            'name' =>__('菜单颜色','b2'),
            'id'=>'menu_color',
            'type' => 'colorpicker',
            'default'=>'#fc3c2d',
            'desc' => __('请选择菜单的颜色','b2')
        );

        $fields['menu_img'] = array(
            'name' =>__('菜单图片（图标）','b2'),
            'id'=>'menu_img',
            'type' => 'file',
            'desc' => __('请选择菜单图片（图标）,在选择图片菜单模式下将会显示在菜单中','b2')
        );

        return $fields;
    }
}