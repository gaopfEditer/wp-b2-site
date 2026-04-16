<?php namespace B2\Modules\Settings;
use B2\Modules\Common\Circle;
use B2\Modules\Common\User;
use B2\Modules\Settings\Circle as CircleSettings;
class Taxonomies{

    public function init(){
        add_action( 'cmb2_admin_init', array($this,'register_taxonomy_metabox' ));

        
        //, array($this,'link_save_meta_box_data' ));
    }

    public function register_taxonomy_metabox(){
        $cmb_term = new_cmb2_box( array( 
            'id'               => 'b2_tax', 
            'title'            => __( 'Tax 设置', 'b2' ), 
            'object_types'     => array( 'term' ), 
            'taxonomies'       => array( 'category', 'post_tag' ),
            'new_term_section' => true,
        ) );

        $cmb_term->add_field( array( 
            'name'     => __( 'SEO标题', 'b2' ), 
            'id'       => 'seo_title', 
            'type'     => 'text', 
        ) );

        $cmb_term->add_field( array( 
            'name'     => __( 'SEO关键词', 'b2' ), 
            'id'       => 'seo_keywords', 
            'type'     => 'text', 
        ) );

        //筛选设置
        $filter = $cmb_term->add_field( array(
            'id'          => 'b2_filter',
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
            'desc'=>sprintf(__('如果需要批量设置多个分类的的筛选，您可以前往%s分类筛选批量设置%s中进行操作','b2'),'<a target="_blank" href="'.admin_url('/admin.php?page=b2_template_fliter').'">','</a>')
        ));

        $cmb_term->add_group_field( $filter,array(
            'name'    => __( '是否开启筛选', 'b2' ), 
            'id'      => 'show',
            'type'    => 'select',
            'default'          => 0,
            'options'          => array(
                1 => __( '开启', 'cmb2' ),
                0   => __( '关闭', 'cmb2' )
            ),
        ));

        $cats = array();

        $categories = get_categories( array(
            'orderby' => 'name',
            'order'   => 'ASC',
            'hide_empty'      => false,
        ) );
         
        foreach( $categories as $category ) {
            $cats[$category->term_id] = $category->name;
        } 

        $cmb_term->add_group_field($filter,array(
            'name'    => '允许筛选的分类',
            'id'      => 'cat',
            'desc'    => __('请选择要显示的文章分类，可以拖动排序','b2'),
            'type'    => 'pw_multiselect',
            'options' =>$cats,
        ) );

        // $cmb_term->add_group_field( $filter,array(
        //     'name' => __('允许筛选的分类','b2'),
        //     'id'   => 'cat',
        //     'type' => 'taxonomy_multicheck_hierarchical',
        //     'taxonomy'=>'category',
        //     'text'           => array(
        //         'no_terms_text' => sprintf(__('没有分类，请前往%s添加','b2'),'<a target="__blank" href="'.admin_url('//edit-tags.php?taxonomy=category').'"></a>')
        //     ),
        //     'remove_default' => 'true',
        //     'query_args' => array(
        //         'orderby' => 'count',
        //         'hide_empty' => false,
        //     ),
        //     'select_all_button' => true,
        //     'desc'=>__('请确保您的分类别名不是中文，否则无法选中','b2'),
        // ));

        $arr = array();

        $cats = get_terms('collection',array(
            'orderby' => 'name',
            'order'   => 'ASC',
            'hide_empty'      => false,
            'cache_domain'=>'b2_collection'
        ) );
         
        foreach( $cats as $cat ) {
            $arr[$cat->term_id] = $cat->name;
        } 

        $collection_name = b2_get_option('normal_custom','custom_collection_name');

        $cmb_term->add_group_field($filter,array(
            'name'    => '允许筛选的'.$collection_name,
            'id'      => 'collection',
            'desc'    => __('请选择要显示的专题，可以拖动排序','b2'),
            'type'    => 'pw_multiselect',
            'options' =>$arr,
        ) );

        // $cmb_term->add_group_field( $filter,array(
        //     'name' => __('允许筛选的专题','b2'),
        //     'id'   => 'collection',
        //     'type' => 'taxonomy_multicheck_hierarchical',
        //     'taxonomy'=>'collection',
        //     'text'           => array(
        //         'no_terms_text' => sprintf(__('没有专题，请前往%s添加','b2'),'<a target="__blank" href="'.admin_url('//edit-tags.php?taxonomy=collection').'"></a>')
        //     ),
        //     'remove_default' => 'true',
        //     'query_args' => array(
        //         'orderby' => 'count',
        //         'hide_empty' => false,
        //     ),
        //     'select_all_button' => true,
        //     'desc'=>__('请确保您的专题别名不是中文，否则无法选中','b2'),
        // ));

        $cmb_term->add_group_field( $filter,array(
            'name'=>__('自定义字段筛选项','b2'),
            'id'=>'meta',
            'type'=>'textarea',
            'desc'=>sprintf(__(
                '格式为：%s%s%s比如：%s如果不使用请留空%s您可以安装Advanced Custom Fields插件来简化文章编辑页面自定义字段的输入流程','b2'),
                '<br>',
                '<code>name|mete_key|meta_name=meta_value,meta_name=meta_value,meta_name=meta_value</code><br><br>',
                '<code>name|meta_key|meta_name=meta_value,meta_name=meta_value,meta_name=meta_value</code><br><br>',
                '<br><code>电影类型|move_type|爱情片=aiqing,动作片=dongzuo,科幻片=kehuan</code>',
                '<br>'
            )
        ));

        $cmb_term->add_group_field( $filter,array(
            'name'=>__('标签筛选项','b2'),
            'id'=>'tag',
            'type'=>'textarea',
            'desc'=>sprintf(
            __(
                '请输入筛选名称以及允许筛选的标签，比如：%s竖线前面为筛选名称，后面是要筛选的标签，用英文逗号隔开。每行代表一组筛选。如果不启用此筛选，请留空','b2'),
                '<br><code>年份|2019,2018,2017,2016,2015</code><br><code>导演|张三,李四,王五</code></br>'
            )
        ));

        $cmb_term->add_group_field( $filter,array(
            'name'=>__('排序筛选项','b2'),
            'id'=>'order',
            'type'    => 'multicheck_inline',
            'options' => array(
                'date' => __( '最新', 'b2' ),
                'random' => __( '随机', 'b2' ),
                'views' => __( '最多浏览', 'b2' ),
                'like' => __( '最多喜欢', 'b2' ),
                'comments' => __( '最多评论', 'b2' ),
            ),
        ));

        //模块设置
        $group = $cmb_term->add_field( array(
            'id'          => 'b2_group',
            'type'        => 'group',
            'desc' => __( '存档页面布局设置', 'b2' ),
            'repeatable'  => false, 
            'options'     => array(
                'group_title'       => __( '布局设置', 'b2' ),
                'add_button'        => __( '添加新模块', 'b2' ),
                'remove_button'     => __( '删除模块', 'b2' ),
                'sortable'          => false,
                'closed'         => false,
                'remove_confirm' => __( '确定要删除这个模块吗？', 'b2' ),
            ),
        ));

        $post_type = apply_filters('b2_temp_post_type', array(
            'post-1' => array(
                'name'=>__('网格模式','b2'),
                'img'=>'/Assets/admin/images/post-1.svg'
            ),
            'post-2' => array(
                'name'=>__('瀑布流','b2'),
                'img'=>'/Assets/admin/images/post-2.svg'
            ),
            'post-3' => array(
                'name'=>__('列表模式','b2'),
                'img'=>'/Assets/admin/images/post-3.svg'
            ),
            'post-4' => array(
                'name'=>__('组合模式','b2'),
                'img'=>'/Assets/admin/images/post-4.svg'
            ),
            'post-5' => array(
                'name'=>__('纯文字模式','b2'),
                'img'=>'/Assets/admin/images/post-5.svg'
            ),
            'post-6' => array(
                'name'=>__('纯文字带自定义字段','b2'),
                'img'=>'/Assets/admin/images/post-6.svg'
            )
        ));

        $options = array();
        $images = array();

        foreach ($post_type as $k => $v) {
            $options[$k] = $v['name'];
            $images[$k] = $v['img'];
        }

        $cmb_term->add_group_field($group,array(
            'name' => __('列表样式','b2'),
            'id'   => 'post_type',
            'type' => 'radio_image',
            'options'          => $options,
            'images_path'  => B2_THEME_URI,
            'images'       => $images,
            'default'=>'post-1',
        ));

        do_action('b2_temp_post_type_action',$cmb_term,$group);

        $cmb_term->add_group_field($group,array(
            'name'=>__('排序方式','b2'),
            'id'=>'post_order',
            'type'=>'select',
            'options' => array(
                'new' => __('最新','b2'),
                'modified' => __('修改时间','b2'),
                'random' => __('随机','b2'),
                'views' => __('最多浏览','b2'),
                'like' => __('最多喜欢','b2'),
                'comments' => __('最多评论','b2')
            ),
            'default' => 'new',
        ));

        $cmb_term->add_group_field($group,array(
            'name'=>__('每行显示数量','b2'),
            'id'=>'post_row_count',
            'type'=>'text',
            'default'=>4,
        ));

        $cmb_term->add_group_field($group,array(
            'name'=>__('每页显示数量','b2'),
            'id'=>'post_count',
            'type'=>'text',
            'default'=>24,
        ));

        $cmb_term->add_group_field($group,array(
            'before_row'=>'<div class="custom-key">',
            'name'=>'<span class="red">'.__('【纯文字带自定义字段】模式自定义字段','b2').'</span>',
            'id'=>'post_custom_key',
            'type'=>'textarea_small',
            'desc'=>sprintf(__('如果不需要请留空。请根据%s格式设置您要显示的自定义字段，比如：%s','b2'),'<code>自定义字段的中文名1|自定义字段key1</code>','<br>
                <code>国籍|move_country</code><br>
                <code>电影类别|move_type</code><br>
            '),
            'after_row'=>'</div>'
        ));

        $cmb_term->add_group_field($group,array(
            'before_row'=>'<div class="list-width">',
            'name'=>__('PC端缩略图宽度','b2'),
            'id'=>'post_thumb_width',
            'type'=>'text',
            'default'=>'190',
            'desc'=>'<span class="red">'.__('PC端该【列表模式】下缩略图的宽度，单位是px，请直接填写数字即可。').'</span>',
        ));

        $cmb_term->add_group_field($group,array(
            'name'=>__('PC端缩略图比例','b2'),
            'id'=>'post_thumb_ratio_pc',
            'type'=>'text',
            'default'=>'1/0.74',
            'desc'=>'<span class="red">'.sprintf(__('PC端该【列表模式】下缩略图宽和高的比例，比如%s，%s。'),'<code>4/3</code>','<code>1/0.618</code>').'</span>',
        ));

        $cmb_term->add_group_field($group,array(
            'name'=>__('移动端缩略图宽度','b2'),
            'id'=>'post_thumb_width_mobile',
            'type'=>'text',
            'default'=>'100',
            'desc'=>'<span class="red">'.__('移动端该【列表模式】下缩略图的宽度，单位是px，请直接填写数字即可。').'</span>',
        ));

        $cmb_term->add_group_field($group,array(
            'name'=>__('移动端缩略图比例','b2'),
            'id'=>'post_thumb_ratio_mobile',
            'type'=>'text',
            'default'=>'1/0.6',
            'desc'=>'<span class="red">'.sprintf(__('移动端该【列表模式】下缩略图宽和高的比例，比如%s，%s。'),'<code>4/3</code>','<code>1/0.618</code>').'</span>',
            'after_row'=>'</div>'
        ));

        $cmb_term->add_group_field($group,array(
            'name'=>__('PC端标题最多显示几行','b2'),
            'id'=>'post_title_row',
            'type'=>'text',
            'default'=>1,
            'desc'=>__('如果设置成1，标题超过1行会显示省略号，如果设置2，标题超过2行会显示省略号，以此类推。','b2')
        ));

        $cmb_term->add_group_field($group,array(
            'name'=>__('移动端端标题最多显示几行','b2'),
            'id'=>'post_title_row_mobile',
            'type'=>'text',
            'default'=>2,
            'desc'=>__('如果设置成1，标题超过1行会显示省略号，如果设置2，标题超过2行会显示省略号，以此类推。','b2')
        ));

        $cmb_term->add_group_field($group,array(
            'before_row'=>'<div class="list-width-normal">',
            'name'=>__('缩略图比例','b2'),
            'id'=>'post_thumb_ratio',
            'type'=>'text',
            'default'=>'4/3',
            'desc'=>sprintf(__('缩略图高度自适应的情况下不生效，请填写宽和高的比例，比如%s，%s。'),'<code>4/3</code>','<code>1/0.618</code>'),
            'after_row'=>'</div>'
        ));
        
        $cmb_term->add_group_field($group,array(
            'name'=>__('打开方式','b2'),
            'id'=>'post_open_type',
            'type'    => 'select',
            'options' => array(
                1 => __( '原窗口打开', 'b2' ),
                0   => __( '新窗口打开', 'b2' ),
            ),
            'default' => 1,
        ));

        $cmb_term->add_group_field($group,array(
            'name'=>__('meta选择','b2'),
            'id'=>'post_meta',
            'type'    => 'multicheck_inline',
            'options' => array(
                'user' => __( '作者', 'b2' ),
                'date' => __( '时间', 'b2' ),
                'des' => __( '摘要', 'b2' ),
                'cats' => __( '分类', 'b2' ),
                'like' => __( '喜欢数量', 'b2' ),
                'comment'=>__('评论数量','b2'),
                'views' => __( '浏览量', 'b2' ),
                'video'=>__('视频标签','b2'),
                'download'=>__('下载标签','b2'),
                'hide'=>__('隐藏内容标签','b2'),
            ),
        ));
        
        $cmb_term->add_field( array( 
            'name'     => __( '特色图', 'b2' ), 
            'id'       => 'b2_tax_img', 
            'desc'=>__( '显示在聚合页面的最顶端，尽量使用大图，以适应各种模式下的显示清晰', 'b2' ), 
            'type'    => 'file',
            'options' => array(
                'url' => false, 
            ),
            'text'    => array(
                'add_upload_file_text' => __( '选择图片', 'b2' ),
            ),
            'query_args' => array(
                'type' => array(
                    'image/gif',
                    'image/jpeg',
                    'image/png',
                ),
            ),
        ) );

        $cmb_term->add_field( array(
            'name'    => __( '颜色', 'b2' ), 
            'id'      => 'b2_tax_color',
            'type'    => 'colorpicker',
            'desc'=>__('某些情况下，会使用此颜色作为存档名称的颜色','b2'),
            'default' => '#607d8b'
        ));

        $cmb_term->add_field( array(
            'name'    => __( '是否显示侧边栏', 'b2' ), 
            'id'      => 'b2_show_sidebar',
            'type'    => 'select',
            'default'          => 0,
            'options'          => array(
                1 => __( '显示侧边栏', 'cmb2' ),
                0   => __( '隐藏侧边栏', 'cmb2' )
            ),
            'desc'=>__('如果需要显示侧边栏，请前往外观->小工具里面，设置一下第一个侧边栏小工具，否则仍然不显示','b2')
        ));

        $cmb_term->add_field( array(
            'name' => __('分页方式','b2'),
            'id'      => 'b2_tax_pagenav_type',
            'type'    => 'radio_inline',
            'options' => array(
                'normal' => __( '传统分页（无ajax）', 'b2' ),
                'ajax_loader'   => __( '下拉无限加载', 'b2' ),
                'ajax_pagenav'     => __( 'ajax分页加载', 'b2' ),
            ),
            'default' => 'ajax_pagenav',
        ) );

        $shop = new_cmb2_box( array( 
            'id'               => 'b2_shop_tax', 
            'title'            => __( '商品分类设置', 'b2' ), 
            'object_types'     => array( 'term' ), 
            'taxonomies'       => array( 'shoptype'),
            'new_term_section' => true,
        ) );

        $shop->add_field( array( 
            'name'     => __( 'SEO标题', 'b2' ), 
            'id'       => 'seo_title', 
            'type'     => 'text', 
        ) );

        $shop->add_field( array( 
            'name'     => __( 'SEO关键词', 'b2' ), 
            'id'       => 'seo_keywords', 
            'type'     => 'text', 
        ) );

        $shop->add_field( array( 
            'name'     => __( '特色图', 'b2' ), 
            'id'       => 'b2_tax_img', 
            'desc'=>__( '显示在聚合页面的最顶端，尽量使用大图，以适应各种模式下的显示清晰', 'b2' ), 
            'type'    => 'file',
            'options' => array(
                'url' => false, 
            ),
            'text'    => array(
                'add_upload_file_text' => __( '选择图片', 'b2' ),
            ),
            'query_args' => array(
                'type' => array(
                    'image/gif',
                    'image/jpeg',
                    'image/png',
                ),
            ),
        ) );

        $shop->add_field( array(
            'name'    => __( '颜色', 'b2' ), 
            'id'      => 'b2_tax_color',
            'type'    => 'colorpicker',
            'desc'=>__('某些情况下，会使用此颜色作为存档名称的颜色','b2'),
            'default' => '#607d8b'
        ));

        $collection = new_cmb2_box( array( 
            'id'               => 'b2_collection_tax', 
            'title'            => sprintf(__( '%s设置', 'b2' ),$collection_name), 
            'object_types'     => array( 'term' ), 
            'taxonomies'       => array( 'collection'),
            'new_term_section' => true,
        ) );

        $collection->add_field( array( 
            'name'     => __( 'SEO标题', 'b2' ), 
            'id'       => 'seo_title', 
            'type'     => 'text', 
        ) );

        $collection->add_field( array( 
            'name'     => __( 'SEO关键词', 'b2' ), 
            'id'       => 'seo_keywords', 
            'type'     => 'text', 
        ) );

        $collection->add_field( array(
            'name'    => sprintf(__( '%s期数', 'b2' ),$collection_name), 
            'id'      => 'b2_tax_index',
            'type'    => 'text',
            'desc'=>sprintf(__('此为当前%s的期数，比如第二期，请直接填写2','b2'),$collection_name),
        ));

        $collection->add_field( array( 
            'name'     => __( '特色图', 'b2' ), 
            'id'       => 'b2_tax_img', 
            'desc'=>__( '显示在聚合页面的最顶端，尽量使用大图，以适应各种模式下的显示清晰', 'b2' ), 
            'type'    => 'file',
            'options' => array(
                'url' => false, 
            ),
            'text'    => array(
                'add_upload_file_text' => __( '选择图片', 'b2' ),
            ),
            'query_args' => array(
                'type' => array(
                    'image/gif',
                    'image/jpeg',
                    'image/png',
                ),
            ),
        ) );

        $collection->add_field( array(
            'name'    => __( '颜色', 'b2' ), 
            'id'      => 'b2_tax_color',
            'type'    => 'colorpicker',
            'desc'=>__('某些情况下，会使用此颜色作为存档名称的颜色','b2'),
            'default' => '#607d8b'
        ));

        $collection->add_field( array(
            'name' => __('分页方式','b2'),
            'id'      => 'b2_tax_pagenav_type',
            'type'    => 'radio_inline',
            'options' => array(
                'normal' => __( '传统分页（无ajax）', 'b2' ),
                'ajax_loader'   => __( '下拉无限加载', 'b2' ),
                'ajax_pagenav'     => __( 'ajax分页加载', 'b2' ),
            ),
            'default' => 'ajax_pagenav',
        ) );

        $document = new_cmb2_box( array( 
            'id'               => 'b2_document_tax', 
            'title'            => __( '文档设置', 'b2' ), 
            'object_types'     => array( 'term' ), 
            'taxonomies'       => array( 'document_cat','infomation_cat','ask_cat'),
            'new_term_section' => true,
        ) );

        $document->add_field( array( 
            'name'     => __( 'SEO标题', 'b2' ), 
            'id'       => 'seo_title', 
            'type'     => 'text', 
        ) );

        $document->add_field( array( 
            'name'     => __( 'SEO关键词', 'b2' ), 
            'id'       => 'seo_keywords', 
            'type'     => 'text', 
        ) );

        $document_name = b2_get_option('normal_custom','custom_document_name');

        $document->add_field( array( 
            'name'     => sprintf(__( '图标', 'b2' )), 
            'id'       => 'b2_tax_img',
            'type'    => 'file',
            'options' => array(
                'url' => true, 
            ),
            'text'    => array(
                'add_upload_file_text' => __( '选择图片', 'b2' ),
            ),
            'query_args' => array(
                'type' => array(
                    'image/gif',
                    'image/jpeg',
                    'image/png',
                ),
            ),
        ) );

        $document->add_field( array(
            'name'    => __( '颜色', 'b2' ), 
            'id'      => 'b2_tax_color',
            'type'    => 'colorpicker',
            'desc'=>__('某些情况下，会使用此颜色作为存档名称的颜色','b2'),
            'default' => '#607d8b'
        ));

        $newsflashes_name = b2_get_option('normal_custom','custom_newsflashes_name');

        $newsflashes = new_cmb2_box( array( 
            'id'               => 'b2_newsflashes_tags', 
            'title'            => sprintf(__( '%s设置', 'b2' ),$newsflashes_name), 
            'object_types'     => array( 'term' ), 
            'taxonomies'       => array( 'newsflashes_tags'),
            'new_term_section' => true,
        ) );

        $newsflashes->add_field( array( 
            'name'     => __( 'SEO标题', 'b2' ), 
            'id'       => 'seo_title', 
            'type'     => 'text',
        ) );

        $newsflashes->add_field( array( 
            'name'     => __( 'SEO关键词', 'b2' ), 
            'id'       => 'seo_keywords', 
            'type'     => 'text', 
        ) );

        $newsflashes->add_field( array( 
            'name'     => sprintf(__( '%s标签封面图', 'b2' ),$newsflashes_name), 
            'id'       => 'b2_tax_img',
            'type'    => 'file',
            'options' => array(
                'url' => false, 
            ),
            'text'    => array(
                'add_upload_file_text' => __( '选择图片', 'b2' ),
            ),
            'query_args' => array(
                'type' => array(
                    'image/gif',
                    'image/jpeg',
                    'image/png',
                ),
            ),
            'desc'=>sprintf(__('显示在%s首页顶部的图片','b2'),$newsflashes_name)
        ) );

        $newsflashes->add_field( array(
            'name'    => sprintf(__( '%s标签描述', 'b2' ),$newsflashes_name), 
            'id'      => 'b2_tax_desc',
            'type'    => 'text',
            'desc'=>sprintf(__('显示在%s标签封面之上，%s标签之下','b2'),$newsflashes_name,$newsflashes_name),
        ));

        $circle_name = b2_get_option('normal_custom','custom_circle_name');

        $cmb_circle = new_cmb2_box( array( 
            'id'               => 'b2_circle', 
            'title'            => sprintf(__( '%s设置', 'b2' ),$circle_name), 
            'object_types'     => array( 'term' ), 
            'taxonomies'       => array( 'circle_tags'),
            'new_term_section' => true,
        ) );

        $cmb_circle->add_field( array( 
            'name'     => __( 'SEO标题', 'b2' ), 
            'id'       => 'seo_title', 
            'type'     => 'text', 
        ) );

        $cmb_circle->add_field( array( 
            'name'     => __( 'SEO关键词', 'b2' ), 
            'id'       => 'seo_keywords', 
            'type'     => 'text', 
        ) );

        $tags = Circle::get_circle_tags();
        $_tags = array();
        foreach ($tags as $k => $v) {
            $_tags[$v] = $v;
        }

        $cmb_circle->add_field(array(
            'name' => sprintf(__('%s类别','b2'),$circle_name),
            'id'   => 'b2_circle_tag',
            'type' => 'radio_inline',
            'options' => $_tags,
            'remove_default' => 'true',
            'before_row'=>'<div class="cmb2-options-page"><div class="cmb2-metabox cmb-field-list">',
        ));

        $cmb_circle->add_field( array(
            'name'     => sprintf(__( '%s图标', 'b2' ),$circle_name), 
            'id'       => 'b2_circle_icon', 
            'type'    => 'file',
            'options' => array(
                'url' => true, 
            ),
            'text'    => array(
                'add_upload_file_text' => __( '选择图片', 'b2' ),
            ),
            'query_args' => array(
                'type' => array(
                    'image/gif',
                    'image/jpeg',
                    'image/png',
                ),
            ),
        ) );

        $owner_name = b2_get_option('normal_custom','custom_circle_owner_name');

        $cmb_circle->add_field( array( 
            'name'     => $owner_name, 
            'id'       => 'b2_circle_admin', 
            'type' => 'text',
            'desc' => __('请直接输入要设置成管理员的用户的ID'),
        ) );

        // $cmb_circle->add_field( array(
        //     'name'     => __( '圈子封面', 'b2' ), 
        //     'id'       => 'b2_circle_cover', 
        //     'type'    => 'file',
        //     'options' => array(
        //         'url' => true,
        //     ),
        //     'text'    => array(
        //         'add_upload_file_text' => __( '选择图片', 'b2' ),
        //     ),
        //     'query_args' => array(
        //         'type' => array(
        //             'image/gif',
        //             'image/jpeg',
        //             'image/png',
        //         ),
        //     ),
        // ) );
        
        $is_circle_default = isset($_REQUEST['taxonomy']) && isset($_REQUEST['tag_ID']) && $_REQUEST['taxonomy'] == 'circle_tags' && $_REQUEST['tag_ID'] == get_option('b2_circle_default') ? true : false;
        
        if(!$is_circle_default){
            $cmb_circle->add_field( array( 
                'name'     => sprintf(__( '%s形式', 'b2' ),$circle_name), 
                'id'       => 'b2_circle_type', 
                'type'     => 'select',
                'options'=>array(
                    'free'=>sprintf(__('免费%s','b2'),$circle_name),
                    'money'=>sprintf(__('付费%s','b2'),$circle_name),
                    'lv'=>sprintf(__('专属%s','b2'),$circle_name)
                ),
                'default'=>'free'
            ) );
    
            $cmb_circle->add_field( array( 
                'name'     => sprintf(__( '如果是免费%s，请选择加入%s规则', 'b2' ),$circle_name,$circle_name), 
                'id'       => 'b2_circle_join_type', 
                'type'     => 'select',
                'options'=>array(
                    'free'=>sprintf(__('自由加入%s','b2'),$circle_name),
                    'check'=>sprintf(__('用户需要审核加入%s','b2'),$circle_name)
                ),
                'default'=>''
            ) );
    
            $lvs = User::get_user_roles();
    
            $setting_lvs = array();
            foreach($lvs as $k => $v){
                $setting_lvs[$k] = $v['name'];
            }
    
            if(b2_get_option('verify_main','verify_allow')){
                $setting_lvs['verify'] = __('认证用户','b2');
            }
    
            $cmb_circle->add_field(array(
                'name' => sprintf(__('如果是专属%s，请选择允许加入%s的用户组','b2'),$circle_name,$circle_name),
                'id'   => 'b2_circle_join_lv',
                'type' => 'multicheck_inline',
                'desc'=>'<p>'.sprintf(__('如果您修改了此权限，请前往%s%s设置%s中重置以下该%s的数据。%s如果没有用户组，请前往%s用户设置%s进行设置','b2'),'<a href="'.admin_url('/admin.php?page=b2_circle_data').'" target="_blank">',$circle_name,'</a>',$circle_name,'<br>','<a href="'.admin_url().'/admin.php?page=b2_normal_user" target="_blank">','</a>').'</p>',
                'options'=>$setting_lvs,
            ));
    
            $cmb_circle->add_field( array( 
                'name'     => sprintf(__( '如果是收费%s，请设置金额（有效期：永久有效）', 'b2' ),$circle_name), 
                'id'       => 'b2_circle_money_permanent', 
                'type' => 'text_money',
                'sanitization_cb' => 'b2_sanitize_number',
                'before_field' => B2_MONEY_SYMBOL,
            ) );
    
            $cmb_circle->add_field( array( 
                'name'     => sprintf(__( '如果是收费%s，请设置金额（有效期：按年付费）', 'b2' ),$circle_name), 
                'id'       => 'b2_circle_money_year', 
                'type' => 'text_money',
                'sanitization_cb' => 'b2_sanitize_number',
                'before_field' => B2_MONEY_SYMBOL,
            ) );
    
            $cmb_circle->add_field( array( 
                'name'     => sprintf(__( '如果是收费%s，请设置金额（有效期：半年付费）', 'b2' ),$circle_name), 
                'id'       => 'b2_circle_money_halfYear', 
                'type' => 'text_money',
                'sanitization_cb' => 'b2_sanitize_number',
                'before_field' => B2_MONEY_SYMBOL,
            ) );
    
            $cmb_circle->add_field( array( 
                'name'     => sprintf(__( '如果是收费%s，请设置金额（有效期：季度付费）', 'b2' ),$circle_name), 
                'id'       => 'b2_circle_money_season', 
                'type' => 'text_money',
                'sanitization_cb' => 'b2_sanitize_number',
                'before_field' => B2_MONEY_SYMBOL,
            ) );
    
            $cmb_circle->add_field( array( 
                'name'     => sprintf(__( '如果是收费%s，请设置金额（有效期：按月付费）', 'b2' ),$circle_name), 
                'id'       => 'b2_circle_money_month', 
                'type' => 'text_money',
                'sanitization_cb' => 'b2_sanitize_number',
                'before_field' => B2_MONEY_SYMBOL,
            ) );

            $circle_member_name = b2_get_option('normal_custom','custom_circle_member_name');
    
            $cmb_circle->add_field( array( 
                'name'     => sprintf(__( '请选择%s隐私', 'b2' ),$circle_name), 
                'id'       => 'b2_circle_read', 
                'type'=>'select',
                'options'=>array(
                    'public'=>sprintf(__('%s内帖子公开显示','b2'),$circle_name),
                    'private'=>sprintf(__('%s内帖子只对%s开放','b2'),$circle_name,$circle_member_name)
                ),
                'after_row'=>'</div></div>',
                'desc'=>sprintf(__('如果您修改了此权限，请前往%s%s设置%s中重置以下该%s的数据。%s公开显示：即便用户没有入群，也可以查看%s内帖子，同时也会在广场显示；只对%s开放：用户加入%s之后才能查看%s帖子，不对外开放。','b2'),'<a href="'.admin_url('/admin.php?page=b2_circle_data').'" target="_blank">',$circle_name,'</a>',$circle_name,'<br>',$circle_name,$circle_member_name,$circle_name,$circle_name)
            ) );
        }
        

        self::topic_settings();

        self::link_settings();
    }

    public static function link_settings(){
        $link = new_cmb2_box( array( 
            'id'               => 'b2_link_settings', 
            'title'            => __( '连接设置', 'b2' ), 
            'object_types'     => array( 'term' ), 
            'taxonomies'       => array( 'link_cat'),
            'new_term_section' => true,
        ) );

        $link->add_field( array( 
            'name'     => __( 'SEO标题', 'b2' ), 
            'id'       => 'seo_title', 
            'type'     => 'text', 
        ) );

        $link->add_field( array( 
            'name'     => __( 'SEO关键词', 'b2' ), 
            'id'       => 'seo_keywords', 
            'type'     => 'text', 
        ) );

        $link->add_field(array(
            'name'=>__('是否包含子链接分类中的链接','b2'),
            'id'=>'link_show_children',
            'type'    => 'select',
            'options'=>array(
                1=>__('显示','b2'),
                0=>__('不显示','b2')
            ),
            'default' => 0,
            'desc'=>__('如果此链接分类中有子链接分类，您可以选择是否显示子链接分类中的链接，注意，如果子链接分类特别多（大于20个）可能会有性能问题。','b2')
        ));

        $link->add_field(array(
            'name'=>__('点击跳转方式','b2'),
            'id'=>'link_junp',
            'type'    => 'select',
            'options'=>array(
                'self'=>__('跳转到本站内页','b2'),
                'target'=>__('跳转到目标站点','b2')
            ),
            'default' => 'self'
        ));

        $link->add_field(array(
            'name'=>__('每行显示几个','b2'),
            'id'=>'link_count',
            'type'    => 'text',
            'default' => '5'
        ));

        $link->add_field(array(
            'name'=>__('每页显示几个','b2'),
            'id'=>'link_count_total',
            'type'    => 'text',
            'default' => '15',
        ));

        $link->add_field(array(
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

        $link->add_field(array(
            'name'=>__('显示哪些小部件','b2'),
            'id'=>'link_meta',
            'type'    => 'multicheck_inline',
            'options' => array(
                'title'=>__('模块标题','b2'),
                'children' => __( '子链接分类', 'b2' ),
                'more' => __( '更多按钮', 'b2' ),
                'icon'=>__( '图标', 'b2' ),
                'desc'=>__( '描述', 'b2' ),
                'user' => __( '站长', 'b2' ),
                'like' => __( '喜欢数量', 'b2' ),
                'loadmore' => __( '加载更多按钮', 'b2' )
            ),
            // 'default' => array('title','children','more','icon','desc','user','like','loadmore'),
        ));
    }

    public static function topic_settings(){
        $circle_name = b2_get_option('normal_custom','custom_circle_name');
        $topic = new_cmb2_box( array( 
            'id'               => 'b2_circle_topic_role', 
            'title'            => __( '帖子权限设置', 'b2' ), 
            'object_types'     => array( 'term' ), 
            'taxonomies'       => array( 'circle_tags'),
            'new_term_section' => true,
        ) );

        $topic->add_field( array( 
            'before_row'=>sprintf(__('%s帖子权限设置%s%s您可以在这里单独给某个帖子设置发布阅读权限等。%s','b2'),'<h2>','</h2>','<p>','</p>'),
            'name'     => __( '帖子权限设置', 'b2' ), 
            'id'       => 'b2_circle_topic_role_open', 
            'type' => 'select',
            'desc'=>sprintf(__('如果使用全局设置，这里请选择全局设置，并且在主题设置->%s设置->话题设置中编辑全局权限','b2'),$circle_name),
            'options'=>array(
                0=>__('使用全局设置','b2'),
                1=>sprintf(__('自定义该%s的发帖权限','b2'),$circle_name)
            ),
            'default'=>0
        ) );
        
        CircleSettings::topic_settings_items($topic);

    }


}