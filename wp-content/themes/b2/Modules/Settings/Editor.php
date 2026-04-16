<?php namespace B2\Modules\Settings;

class Editor{
    public function init(){
        add_action('admin_head', array($this,'editor_button'));
        foreach ( array('post.php','post-new.php') as $hook ) {
            add_action( "admin_head-$hook", array($this,'admin_head') );
        }
        add_filter('mce_buttons',array($this,'fenye_editor'));

    }

    function fenye_editor($mce_buttons){
        $pos = array_search('wp_more',$mce_buttons,true);
        if ($pos !== false) {
            $tmp_buttons = array_slice($mce_buttons, 0, $pos+1);
            $tmp_buttons[] = 'wp_page';
            $mce_buttons = array_merge($tmp_buttons, array_slice($mce_buttons, $pos+1));
        }
        return $mce_buttons;
    }

    function admin_head() {
        ?>
            <script type='text/javascript'>
                var b2_admin_global = {
                    'theme_url': '<?php echo B2_THEME_URI; ?>',
                    'text':{
                        'start_hidden_desc':'<?php echo __('隐藏内容开始','b2'); ?>',
                        'end_hidden_desc':'<?php echo __('隐藏内容结束','b2'); ?>',
                        'b2_video_desc':'<?php echo __('插入视频','b2'); ?>',
                        'b2_video_box_title':'<?php echo __('插入视频','b2'); ?>',
                        'b2_video_box_src':'<?php echo __('视频地址','b2'); ?>',
                        'b2_video_box_poster':'<?php echo __('视频封面','b2'); ?>',
                        'b2_video_box_desc':'<?php echo sprintf(__('请直接复制视频网址到此处%s支持%s等视频格式%s也支持各大视频平台的网址比如%s如果不设置封面，程序会自动获取%s不保证每次都能获取成功！','b2'),'<br>','<code>.mp4</code>','<br>','<br><code>https://v.youku.com/xxxx</code><br>-----------------<br>','<br>'); ?>',
                        'b2_file_desc':'<?php echo __('添加附件','b2'); ?>',
                        'b2_file_title':'<?php echo __('标题','b2'); ?>',
                        'b2_file_url':'<?php echo __('连接','b2'); ?>',
                        'b2_file_tiqu':'<?php echo __('提取码','b2'); ?>',
                        'b2_file_jieya':'<?php echo __('解压码','b2'); ?>',
                        'b2_file_box_desc':'<?php echo __('如果没有提取码或解压码，请留空','b2'); ?>',
                        'b2_post_desc':'<?php echo __('插入站内链接','b2'); ?>',
                        'b2_post_id':'<?php echo __('文章\商品\研究所的ID','b2'); ?>',
                        'b2_post_box_desc':'<?php echo __('请直接填写文章、商品、帖子、导航等站内网址或ID','b2'); ?>',
                        'b2_inv_desc':'<?php echo __('插入邀请列表','b2'); ?>',
                        'b2_inv_start':'<?php echo __('开始ID','b2'); ?>',
                        'b2_inv_end':'<?php echo __('结束ID','b2'); ?>',
                        'b2_inv_box_desc':'<?php echo __('请输入列表的开始ID和结束ID','b2'); ?>',
                    }
                };
            </script>
        <?php
    }

    function editor_button() {
        // 检查用户权限
        if ( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) ) {
            return;
        }
        // 检查是否启用可视化编辑
        if ( 'true' == get_user_option( 'rich_editing' ) ) {
            add_filter( 'mce_external_plugins', array($this,'add_tinymce_plugin' ));
            add_filter( 'mce_buttons', array($this,'register_mce_button' ));
        }
    }

    function add_tinymce_plugin($plugin_array){
        global $pagenow;

        if(!isset($_GET['taxonomy']) && in_array( $pagenow, array( 'post.php','post-new.php' ), true )){
            $plugin_array['b2_editor_button'] = B2_THEME_URI .'/Assets/admin/editor-buttons.js';
        }
        return $plugin_array;
    }

    function register_mce_button($buttons){
        array_push( $buttons, 'hidden_start','hidden_end','b2_video','b2_file','b2_post','b2_inv');
        return $buttons;
    }
}