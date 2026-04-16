<?php
namespace B2\Modules\Settings;

class Filter{
    public function init(){

        add_action('cmb2_admin_init',array($this,'filter_options_page'));
    }

    public function filter_options_page(){
        $filter = new_cmb2_box( array(
            'id'           => 'b2_filter_main_options_page',
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_filter_main',
            'tab_group'    => 'b2_filter_options',
            'parent_slug'     => 'b2_main_options',
            'title'=>__('多级筛选','b2'),
            'menu_title'   => __('存档页面设置','b2'),
        ) );

        //多级筛选
        $group = $filter->add_field( array(
            'id'          => 'filter_group',
            'type'        => 'group',
            'desc' => __( '多级筛选设置', 'b2' ),
            'repeatable'  => false, 
            'options'     => array(
                'group_title'       => __( '多级筛选设置设置', 'b2' ),
                'add_button'        => __( '添加新模块', 'b2' ),
                'remove_button'     => __( '删除模块', 'b2' ),
                'sortable'          => false,
                'closed'         => false,
                'remove_confirm' => __( '确定要删除这个模块吗？', 'b2' ),
            ),
        ));

        $filter->add_group_field( $group,array(
            'name'    => __( '是否开启筛选', 'b2' ), 
            'id'      => 'show',
            'type'    => 'select',
            'default'          => 0,
            'options'          => array(
                1 => __( '开启', 'cmb2' ),
                0   => __( '关闭', 'cmb2' )
            ),
        ));

        $filter->add_group_field( $group,array(
            'name' => __('允许筛选的分类','b2'),
            'id'   => 'cat',
            'type' => 'taxonomy_multicheck_hierarchical',
            'taxonomy'=>'category',
            'text'           => array(
                'no_terms_text' => sprintf(__('没有分类，请前往%s添加','b2'),'<a target="__blank" href="'.admin_url('//edit-tags.php?taxonomy=category').'"></a>')
            ),
            'remove_default' => 'true',
            'query_args' => array(
                'orderby' => 'count',
                'hide_empty' => false,
            ),
            'select_all_button' => true,
            'desc'=>__('请确保您的分类别名不是中文，否则无法选中','b2'),
        ));

        $collection_name = b2_get_option('normal_custom','custom_collection_name');

        $filter->add_group_field( $group,array(
            'name' => sprintf(__('允许筛选的%s','b2'),$collection_name),
            'id'   => 'collection',
            'type' => 'taxonomy_multicheck_hierarchical',
            'taxonomy'=>'collection',
            'text'           => array(
                'no_terms_text' => sprintf(__('没有%s，请前往%s添加','b2'),$collection_name,'<a target="__blank" href="'.admin_url('//edit-tags.php?taxonomy=collection').'"></a>')
            ),
            'remove_default' => 'true',
            'query_args' => array(
                'orderby' => 'count',
                'hide_empty' => false,
            ),
            'select_all_button' => true,
            'desc'=>sprintf(__('请确保您的%s别名不是中文，否则无法选中','b2'),$collection_name),
        ));

        $filter->add_group_field( $group,array(
            'name'=>__('自定义字段筛选项','b2'),
            'id'=>'meta',
            'type'=>'textarea',
            'desc'=>sprintf(__(
                '格式为：%s%s%s比如：%s如果不使用请留空','b2'),
                '<br>',
                '<code>name|mete_key|meta_name=meta_value,meta_name=meta_value,meta_name=meta_value</code><br><br>',
                '<code>name|meta_key|meta_name=meta_value,meta_name=meta_value,meta_name=meta_value</code><br><br>',
                '<br><code>电影类型|move_type|爱情片=aiqing,动作片=dongzuo,科幻片=kehuan</code>'
            )
        ));

        $filter->add_group_field( $group,array(
            'name'=>__('标签筛选项','b2'),
            'id'=>'tag',
            'type'=>'textarea',
            'desc'=>sprintf(
            __(
                '请输入筛选名称以及允许筛选的标签，比如：%s竖线前面为筛选名称，后面是要筛选的标签，用英文逗号隔开，如果不启用此筛选，请留空','b2'),
                '<code>年份|2019,2018,2017,2016,2015</code><br>'
            )
        ));

        $filter->add_group_field( $group,array(
            'name'=>__('排序筛选项','b2'),
            'id'=>'order',
            'type'    => 'multicheck_inline',
            'options' => array(
                'date' => __( '时间', 'b2' ),
                'views' => __( '浏览量', 'b2' ),
                'like' => __( '喜欢数量', 'b2' ),
                'comments' => __( '评论数', 'b2' ),
            ),
            'default' => array('date','views','like','comments')
        ));
    }
}