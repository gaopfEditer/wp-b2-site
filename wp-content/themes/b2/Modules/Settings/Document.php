<?php
namespace B2\Modules\Settings;

class Document{

    public static $default_settings = array(
       'document_open'=>1,
       'document_show_sidebar'=>0,
       'document_show_count'=>20,
       'document_tdk_desc'=>'',
       'document_tdk_keywords'=>''
    );

    public function init(){
        add_action('cmb2_admin_init',array($this,'document_settings'));
        add_action( 'quick_edit_custom_box', array($this,'quick_edit_add'), 10, 2 );
        add_action( 'save_post', array($this,'save_quick_edit_data') );
        add_action( 'admin_footer', array($this,'quick_edit_javascript') );
        add_filter( 'post_row_actions', array($this,'expand_quick_edit_link'), 10, 2 );
    }

    function expand_quick_edit_link( $actions, $post ) {
        global $current_screen;
     
        if(!isset($current_screen->post_type)) return $actions;
        
        if ( 'document' == $current_screen->post_type ) {

            $data                               = get_post_meta( $post->ID, 'b2_document_order', true );
            $actions['inline hide-if-no-js']    = '<a href="#" class="editinline" title="';
            $actions['inline hide-if-no-js']    .= esc_attr( 'Edit this item inline' ) . '"';
            $actions['inline hide-if-no-js']    .= " onclick=\"checked_headline_news('{$data}','{$post->ID}')\" >";
            $actions['inline hide-if-no-js']    .= __('快速编辑','b2');
            $actions['inline hide-if-no-js']    .= '</a>';


        }

        if ( 'post' == $current_screen->post_type ) {

            $data                               = get_post_meta( $post->ID, 'single_post_mp_back_key', true );
            $actions['inline hide-if-no-js']    = '<a href="#" class="editinline" title="';
            $actions['inline hide-if-no-js']    .= esc_attr( 'Edit this item inline' ) . '"';
            $actions['inline hide-if-no-js']    .= " onclick=\"checked_headline_news('{$data}','{$post->ID}')\" >";
            $actions['inline hide-if-no-js']    .= __('快速编辑','b2');
            $actions['inline hide-if-no-js']    .= '</a>';
        }
     
        return $actions;
    }

    public function quick_edit_javascript() {
        global $current_screen;
     
        if ( 'document' == $current_screen->post_type) {

        
    ?>
        <script type="text/javascript">
            function checked_headline_news( fieldValue ,postid) {

                inlineEditPost.revert();
                jQuery( '.d_order' ).val(fieldValue);
            }
        </script>
    <?php
        }
        if('post' == $current_screen->post_type){
    ?>
        <script type="text/javascript">
            function checked_headline_news( fieldValue ,postid) {
                inlineEditPost.revert();
                jQuery( '.d_mp' ).val(fieldValue);
            }
        </script>
    <?php
        }
    }

    public function save_quick_edit_data($post_id){
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }
     
        if ( ! current_user_can( 'edit_post', $post_id ) || (isset($_POST['post_type']) && 'document' != $_POST['post_type'] && 'post' != $_POST['post_type'])) {
            return $post_id;
        }

        if(isset($_POST['d_order'])){
            $data = empty( $_POST['d_order'] ) ? 0 : $_POST['d_order'];
            update_post_meta( $post_id, 'b2_document_order', $data );
        }
     
        if(isset($_POST['d_mp'])){
            $data = empty( $_POST['d_mp'] ) ? 0 : $_POST['d_mp'];
            update_post_meta( $post_id, 'single_post_mp_back_key', $data );
        }
        
    }

    public function quick_edit_add( $column_name, $post_type ) {

        if($column_name == 'd_order'){
            printf( '<fieldset class="inline-edit-col-right"><div class="inline-edit-col">
            <span class="title">%s</span><span class="input-text-wrap"><input type="text" name="d_order" class="d_order"></span></div></fieldset>',
                __('在该组别的排序','b2')
            );
        }
     
        if($column_name == 'd_mp'){
            printf( '<fieldset class="inline-edit-col-right"><div class="inline-edit-col">
            <span class="title">%s</span><span class="input-text-wrap"><input type="text" name="d_mp" class="d_mp"></span></div></fieldset>',
                __('微信关键词','b2')
            );
        }

        return;
    }

    public static function get_default_settings($key){
        
        $arr = array(
            'document_name'=>__('文档','b2'),
            'document_image'=>B2_THEME_URI.'/Assets/fontend/images/footer-bg.jpg',
            'document_search_title'=>__('有什么可以帮到您的？','b2'),
            'document_search_attr'=>__('搜索帮助文档','b2')
        );

        if($key == 'all'){
            return $arr;
        }

        if(isset($arr[$key])){
            return $arr[$key];
        }
    }

    public function document_settings(){

        $document_name = b2_get_option('normal_custom','custom_document_name');
        //常规设置
        $document = new_cmb2_box( array(
            'id'           => 'b2_document_main_options_page',
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_document_main',
            'tab_group'    => 'b2_document_options',
            'parent_slug'     => 'b2_main_options',
            'tab_title'    => sprintf(__('%s首页','b2'),$document_name),
            'menu_title'   => sprintf(__('%s设置','b2'),$document_name),
            'save_button'     => __( '保存设置', 'b2' )
        ));

        $document->add_field(array(
            'name'    => sprintf(__( '是否启用%s功能', 'b2' ),$document_name),
            'id'=>'document_open',
            'type'=>'select',
            'options'=>array(
                1=>__('开启','b2'),
                0=>__('关闭','b2')
            ),
            'default'=>self::$default_settings['document_open']
        ));

        $document->add_field(array(
            'name'    => sprintf(__( '%s首页SEO名称', 'b2' ),$document_name),
            'id'=>'document_name',
            'type'=>'text',
            'default'=>self::get_default_settings('document_name')
        ));

        $document->add_field(array(
            'name'    => sprintf(__( '%s首页SEO描述', 'b2' ),$document_name),
            'id'=>'document_tdk_desc',
            'type'=>'textarea_small',
            'default'=>self::get_default_settings('document_tdk_desc')
        ));

        $document->add_field(array(
            'name'    => sprintf(__( '%s首页SEO标签', 'b2' ),$document_name),
            'id'=>'document_tdk_keywords',
            'type'=>'text',
            'default'=>self::get_default_settings('document_tdk_keywords')
        ));

        $document->add_field(array(
            'name'    => sprintf(__( '%s首页顶部图片', 'b2' ),$document_name),
            'id'=>'document_image',
            'type'    => 'file',
            'options' => array(
                'url' => true, 
            ),
            'text'    => array(
                'add_upload_file_text' => __( '选择图片', 'b2' ),
            ),
            'query_args' => array(
                'type' => array(
                    'image/svg+xml',
                    'image/gif',
                    'image/jpeg',
                    'image/png',
                ),
            ),
        ));

        $document->add_field(array(
            'name'    => sprintf(__( '%s首页搜索标题', 'b2' ),$document_name),
            'id'=>'document_search_title',
            'type'=>'text',
            'default'=>self::get_default_settings('document_search_title')
        ));

        $document->add_field(array(
            'name'    => sprintf(__( '%s首页搜索框提示', 'b2' ),$document_name),
            'id'=>'document_search_attr',
            'type'=>'text',
            'default'=>self::get_default_settings('document_search_attr')
        ));

        $arr = array();

        $cats = get_terms('document_cat',array(
            'orderby' => 'name',
            'order'   => 'ASC',
            'hide_empty'      => false,
            'cache_domain'=>'b2_document_cat'
        ) );
         
        foreach( $cats as $cat ) {
            $arr[$cat->term_id] = $cat->name;
        } 

        $document->add_field(array(
            'name'    => sprintf(__('首页显示的%s分类','b2'),$document_name),
            'id'      => 'document_cat',
            'desc'    => __('可拖动排序','b2'),
            'type'    => 'pw_multiselect',
            'options' =>$arr,
        ));

        $document->add_field(array(
            'name'    => sprintf(__( '%s首页显示多少篇最新%s', 'b2' ),$document_name,$document_name),
            'id'=>'document_show_count',
            'type'=>'text',
            'default'=>self::$default_settings['document_show_count']
        ));

        $this->document_requests();
    }

    public function document_requests(){
        $request = new_cmb2_box(array(
            'id'           => 'b2_request_options_page',
            'title'   => __('工单管理','b2'), 
            'tab_title'    => __('工单管理','b2'), 
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_request_list',
            'parent_slug'     => '/admin.php?page=b2_document_main',
            'tab_group'    => 'b2_document_options',
            'display_cb'=>array($this,'list_option_page_cb')
        ));
    }

    public function cb_options_page_tabs( $cmb_options ) {
        $tab_group = $cmb_options->cmb->prop( 'tab_group' );
        $tabs      = array();
        foreach ( \CMB2_Boxes::get_all() as $cmb_id => $cmb ) {
            if ( $tab_group === $cmb->prop( 'tab_group' ) ) {
                $tabs[ $cmb->options_page_keys()[0] ] = $cmb->prop( 'tab_title' )
                    ? $cmb->prop( 'tab_title' )
                    : $cmb->prop( 'title' );
            }
        }
        return $tabs;
    }

    public function list_option_page_cb($cmb_options){
        $tabs = $this->cb_options_page_tabs( $cmb_options );
        $order_code = new RequestsTable();
        $order_code->prepare_items();
        $status = isset($_REQUEST["status"]) ? esc_sql($_REQUEST["status"]) : 'all';
        $ref_url = admin_url('admin.php?'.$_SERVER['QUERY_STRING']);

        if((isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete') || (isset($_REQUEST['action2']) && $_REQUEST['action2'] == 'delete')){
            
            $order_ids = isset($_REQUEST['id']) ? (array)$_REQUEST['id'] : '';

            if($order_ids){
                $order_code->delete_coupons($order_ids);
                $ref_url = wp_get_referer();
                $ref_url = remove_query_arg(array('id', 'action','action2','s'), $ref_url);
                exit(header("Location: ".$ref_url));
                echo '<script> location.replace("'.$ref_url.'"); </script>';
            }
        }

    ?>
        <div class="wrap cmb2-options-page option-<?php echo $cmb_options->option_key; ?>">
            <?php if ( get_admin_page_title() ) : ?>
                <h2><?php echo wp_kses_post( get_admin_page_title() ); ?></h2>
            <?php endif; ?>

            <h2 class="nav-tab-wrapper">
                <?php foreach ( $tabs as $option_key => $tab_title ) : ?>
                    <a class="nav-tab<?php if ( isset( $_REQUEST['page'] ) && $option_key === $_REQUEST['page'] ) : ?> nav-tab-active<?php endif; ?>" href="<?php menu_page_url( $option_key ); ?>"><?php echo wp_kses_post( $tab_title ); ?></a>
                <?php endforeach; ?>
            </h2>
            <div class="wrap">
                <?php if(isset($_REQUEST['action']) && $_REQUEST['action'] === 'edit'){ ?>
                    <?php 
                        $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;

                        $update = isset($_REQUEST['request_update']) ? (int)$_REQUEST['request_update'] : 0;

                        global $wpdb;
                        $table_name = $wpdb->prefix . 'zrz_directmessage';
                        $count = 10;

                        if($update){
                            $email = isset($_REQUEST['email']) ? $_REQUEST['email'] : '';
                            $content = isset($_REQUEST['replay_content']) ? $_REQUEST['replay_content'] : '';
                            $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
                            $res = $wpdb->insert($table_name, array(
                                'mark'=>'0+'.$id,
                                'from'=> 0,
                                'to'=> $id,
                                'date'=> current_time('mysql'),
                                'status'=> 1,
                                'content'=> $content,
                                'key'=>'',
                                'value'=>''
                            ));

                            if($res){
                                $res = $wpdb->update(
                                    $table_name, 
                                    array(
                                        'status'=>1,
                                    )
                                    , array('mark'=>'0+'.$id)
                                );

                                \B2\Modules\Common\Message::update_data([
                                    'date'=>current_time('mysql'),
                                    'from'=>0,
                                    'to'=>$id,
                                    'post_id'=>0,
                                    'msg'=>__('您的工单已经被回复：${request_page}','b2'),
                                    'type'=>'user_request',
                                    'type_text'=>__('收到工单','b2'),
                                    'old_row'=>1
                                ]);

                                if($email){
                                    self::send_email($email,$content);
                                }

                                b2_settings_error('updated',__('更新成功','b2'));
                            }
                        }

                        $total_count = $wpdb->get_var($wpdb->prepare("
                                SELECT COUNT(*) FROM $table_name
                                WHERE mark = %s
                            ",
                            '0+'.$id
                        ));

                        $_pages = ceil($total_count/$count);

                        $request_paged = isset($_REQUEST['request_paged']) ? (int)$_REQUEST['request_paged'] :  $_pages;

                        $offset = ($request_paged-1)*$count;

                        $res = $wpdb->get_results($wpdb->prepare("
                                SELECT * FROM $table_name
                                WHERE mark = %s ORDER BY id ASC LIMIT %d,%d
                            ",
                            '0+'.$id,$offset,$count
                        ),ARRAY_A);

                        if(empty($res)) {
                            echo __('没有找到此订单','b2').'</div>
                            </div>';
                            return;
                        }

                    ?>
                    <div id="profile-page">
                        <form id="order-edit" method="post">
                            <a href="<?php echo remove_query_arg(array('id','kuaidi','express_number','order_address','order_content','action','order_update','submit-update-order','replay_content'),$ref_url); ?>">返回到工单列表</a>
                            <div class="request-table" role="presentation">
                                <ul>
                                    <?php 
                                        foreach ($res as $k => $v) {
                                            if((int)$v['from']){
                                                $user_data = get_userdata($v['from']);
                                                $type = 'from';
                                            }else{
                                                $type = 'to';
                                            }
                                            
                                            echo '
                                                <li class="'.$type.'">
                                                    <div class="request-title">'.$v['value'].'</div>
                                                    '.($type === 'from' ? '<div class="request-date"><a href="'.get_author_posts_url($v['from']).'" target="_blank">'.$user_data->display_name.'</a> - '.$v['date'].' - '.$v['key'].'</div>' : __('客服回复：','b2').$v['date']).'
                                                    <div class="request-content"><pre>'.$v['content'].'</pre></div>
                                                </li>
                                            ';
                                        }
                                    ?>
                                </ul>
                                <div class=""></div>
                            </div>
                            <?php
                                $next = '';
                                $pre = '';
      
                                if($request_paged < $_pages){
                                    $next = add_query_arg('request_paged',$request_paged+1,$ref_url);
                                }

                                if($request_paged > 1){
                                    $pre = add_query_arg('request_paged',$request_paged-1,$ref_url);
                                }
                                
                            ?>
                            <div class="request-nav">
                                <?php 
                                    if($pre){
                                        echo '<a href="'.$pre.'">'.__('上一页','b2').'</a>';
                                    }
                                    if($next){
                                        echo '<a href="'.$next.'">'.__('下一页','b2').'</a>';
                                    }
                                ?>

                            </div>
                            <div class="request-replay">
                                <p><?php echo __('客服回复：','b2'); ?></p>
                                <textarea class="small-text code" name="replay_content"></textarea>
                            </div>
                            <input type="hidden" name="page" value="b2_request_list">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <input type="hidden" name="request_update" value="1">
                            <input type="hidden" name="status" value="<?php echo isset($_REQUEST['status']) ? $_REQUEST['status'] : '';?>">
                            <input type="hidden" name="paged" value="<?php echo isset($_REQUEST['paged']) ? (int)$_REQUEST['paged'] : 0;?>">
                            <p class="submit"><input type="submit" name="submit-update-order" id="submit-cmb" class="button button-primary" value="保存"></p>
                        </form>
                    </div>
                <?php }else{ ?>
                    <div class="filter-row1">
                        <a href="<?php echo remove_query_arg(array('status','s'),$ref_url); ?>" class="<?php echo $status === 'all' ? 'current' : ''; ?>"><?php echo __('所有','b2'); ?><span class="count">（<?php echo $order_code->get_status_count('all'); ?>）</span></a>
                        <a href="<?php echo add_query_arg('status','replied',$ref_url); ?>" class="<?php echo $status === 'replied' ? 'current' : ''; ?>"><?php echo __('已回复','b2'); ?><span class="count">（<?php echo $order_code->get_status_count('replied'); ?>）</span></a>
                        <a href="<?php echo add_query_arg('status','unreplied',$ref_url); ?>" class="<?php echo $status === 'unreplied' ? 'current' : ''; ?>"><?php echo __('未回复','b2'); ?><span class="count">（<?php echo $order_code->get_status_count('unreplied'); ?>）</span></a>
                    </div>
                    <div id="icon-users" class="icon32"><br/></div>  
                    <form id="coupon-filter" method="get">
                        <input type="hidden" name="status" value="<?php echo isset($_REQUEST['status']) ? $_REQUEST['status'] : ''; ?>">
                        <input type="hidden" name="status" value="<?php echo isset($_REQUEST['status']) ? $_REQUEST['status'] : ''; ?>">
                        <?php
                            $order_code->search_box( __('搜索工单','b2'), 'search_id' );
                        ?>
                        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />

                        <?php $order_code->display() ?>
                    </form>
                <?php } ?>
            </div>
        </div>
        <?php
    }

    public static function send_email($email,$content){

        $site_name = B2_BLOG_NAME;
        $subject = '['.$site_name.']'.__('：您的工单有新的回复','b2');


 
        $message = '<div style="width:700px;background-color:#fff;margin:0 auto;border: 1px solid #ccc;">
            <div style="height:64px;margin:0;padding:0;width:100%;">
                <a href="'.B2_HOME_URI.'" style="display:block;padding: 12px 30px;text-decoration: none;font-size: 24px;letter-spacing: 3px;border-bottom: 1px solid #ccc;" rel="noopener" target="_blank">
                    '.$site_name.'
                </a>
            </div>
            <div style="padding: 30px;margin:0;">
                <p style="font-size:14px;color:#333;">
                    '.__('你的工单得到了回复：','b2').'
                </p>
                <div style="font-size:16px;color: green;"><pre>'.$content.'</pre></div>
                <p style="font-size:12px;color:#999;border-top:1px dotted #E3E3E3;margin-top:30px;padding-top:30px;">
                    '.__('本邮件为系统邮件不能回复，请勿回复。','b2').'
                </p>
            </div>
        </div>';

        $send = wp_mail( $email, $subject, $message );

        if(!$send){
            return false;
        }

        return true;
    }
}