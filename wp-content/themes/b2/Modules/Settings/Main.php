<?php
namespace B2\Modules\Settings;

use B2\Modules\Common\CircleRelate;
use B2\Modules\Common\Cache;
class Main{
    public function init(){

        //创建设置页面
        add_action('cmb2_admin_init',array($this,'main_options_page'));
        if(!apply_filters('b2_check_role',0)) return;

        //加载css和js
        add_action( 'admin_enqueue_scripts', array( $this, 'setup_admin_scripts' ),99999 );

        
        add_action( 'enqueue_block_editor_assets', array( $this, 'setup_gd_scripts' ));
        ob_start();

        //加载设置项
        $this->load_settings();

        add_action('cmb2_admin_init',array($this,'vip_count'),99999);

        //微信菜单
        add_action('cmb2_admin_init',array($this,'weixin_menu'),99999);

        add_action( 'wp_ajax_b2_insert_settings', array($this,'wp_ajax_b2_insert_settings' ));
        add_action( 'wp_ajax_b2_get_dogecloud_data', array($this,'wp_ajax_b2_get_dogecloud_data' ));
        

        //后台上传支持 SVG格式
        add_filter('upload_mimes', array($this,'mimes_support'));

        add_action( 'cmb2_render_radio_image', array( $this, 'callback' ), 10, 5 );
        add_filter( 'cmb2_list_input_attributes', array( $this, 'attributes' ), 10, 4 );
        
        add_action( 'cmb2_render_text_two', array($this,'cmb2_render_callback_for_text_two'), 10, 5 );

        //允许搜索用户名
        add_filter( 'user_search_columns',  array($this,'allow_search_disply_name'));

        if((int)b2_get_option('template_main','prettify_load')){
            add_action('after_wp_tiny_mce', array($this,'prettify_bottom'));
        }

        add_filter( 'manage_post_posts_columns', array($this,'filter_posts_columns'));
        add_action( 'manage_post_posts_custom_column', array($this,'realestate_column'), 10, 2);

        add_filter( 'manage_document_posts_columns', array($this,'filter_document_columns'));
        add_action( 'manage_document_posts_custom_column', array($this,'document_column'), 10, 2);
        add_action('admin_notices', array($this,'cg_note'),0);

        foreach ( array('category','circle_tags','collection','shoptype','document_cat','newsflashes_tags','link_cat') as $taxonomy ) {
            add_filter( "manage_edit-${taxonomy}_columns",          array($this,'t5_add_col' ),10);
            add_action( "manage_${taxonomy}_custom_column",         array($this,'t5_show_id'),10, 3 );
        }

        add_filter( "manage_edit-collection_columns",          array($this,'t5_add_col_c' ),10);
        add_action( "manage_collection_custom_column",         array($this,'t5_show_id_c'),10, 3 );

        add_action( 'admin_print_styles-edit-tags.php', array($this,'t5_tax_id_style' ));

        add_action( 'admin_notices', array($this,'sample_admin_notice__success') );

        add_filter( 'manage_answer_posts_columns', array($this,'filter_answer_columns'));
        add_action( 'manage_answer_posts_custom_column', array($this,'answer_column'), 10, 2);
    }

    function get_browser_name($user_agent){
        $t = strtolower($user_agent);
        $t = " " . $t;
        if     (strpos($t, 'opera'     ) || strpos($t, 'opr/')     ) return 'Opera'            ;   
        elseif (strpos($t, 'edge'      )                           ) return 'Edge'             ;   
        elseif (strpos($t, 'chrome'    )                           ) return 'Chrome'           ;   
        elseif (strpos($t, 'safari'    )                           ) return 'Safari'           ;   
        elseif (strpos($t, 'firefox'   )                           ) return 'Firefox'          ;   
        elseif (strpos($t, 'msie'      ) || strpos($t, 'trident/7')) return 'Internet Explorer';
        return 'Unkown';
    }

    public function wp_ajax_b2_get_dogecloud_data(){

        if(!current_user_can('administrator') && !current_user_can('editor') && !current_user_can('author')) return;

        if(!isset($_REQUEST['id']) || !$_REQUEST['id']){
            wp_send_json_error('请输入视频ID');
            exit;
        }

        $dogeId = sanitize_text_field($_REQUEST['id']);

        if(is_numeric($dogeId)){
            $id = 'vid='.$dogeId;
        }else{
            $id = 'vcode='.$dogeId;
        }
        $video_res = $this->dogecloud_api('/video/streams.json?platform=wap&'.$id.'&ip='.b2_get_user_ip());

        if(!$video_res){
            
            wp_send_json(['success'=>true,'data'=>['code'=>403,'msg'=>__('设置防盗链了？请将当前域名加入到dogecloud的白名单中。','b2')]]);
            exit;
            
        }

        if(isset($video_res['code']) && $video_res['code'] != 200){
            wp_send_json(['success'=>true,'data'=>$video_res]);
            exit;
        }

        $data = [];

        $videos = $video_res['data']['stream'];
        if(!empty($videos)){
            foreach ($videos as $k => $v) {
                if($k == 0){
                    $data['url'] = $v['url'];
                    break;
                }
            }
        }

        $data['name'] = $video_res['data']['video']['name'];
        $data['poster'] = $video_res['data']['video']['thumb'];

        wp_send_json(['success'=>true,'data'=>$data]);
        exit;
    }

    public function dogecloud_play_token($id,$view = false){
        $SecretKey = b2_get_option('template_single','doge_secretKey');
        $vcode = $id;

        // 生成播放策略
        $myPolicy = json_encode( array(
            'e' => time() + 120,
            'v' => $vcode,
            'full' => true
        ) );
        // 将得到形如 {"e":1535109037,"v":"70804c544d067f03","full":true} 的 JSON

        // 生成随机 IV
        $iv = random_bytes(16);

        // 进行加密得到二进制数据（使用 openssl_encrypt 需要启用 openssl 扩展），然后进行 Base64 编码，得到第一段
        $encodedData = base64_encode(openssl_encrypt($myPolicy, 'aes-256-cfb', $SecretKey, OPENSSL_RAW_DATA, $iv));

        // 进行 HMAC-SHA1，然后进行 Base64 编码，得到第三段
        $hashedData = base64_encode(hash_hmac('sha1', $myPolicy, $SecretKey, true));

        // 连接
        $encodedData = $encodedData . ':' . base64_encode($iv) . ':' . $hashedData;

        // 替换特殊符号
        $playToken = strtr($encodedData, array('+' => '-', '/' => '_'));
    }

    public function dogecloud_api($apiPath, $data = array(), $jsonMode = false) {

        $accessKey = b2_get_option('template_single','doge_accessKey');
        $secretKey = b2_get_option('template_single','doge_secretKey');

        $body = $jsonMode ? json_encode($data) : http_build_query($data);
        $signStr = $apiPath . "\n" . $body;
        $sign = hash_hmac('sha1', $signStr, $secretKey);
        $Authorization = "TOKEN " . $accessKey . ":" . $sign;
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.dogecloud.com" . $apiPath);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 如果是本地调试，或者根本不在乎中间人攻击，可以把这里的 1 和 2 修改为 0，就可以避免报错
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 建议实际使用环境下 cURL 还是配置好本地证书
        if(isset($data) && $data){
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: ' . ($jsonMode ? 'application/json' : 'application/x-www-form-urlencoded'),
            'Authorization: ' . $Authorization
        ));
        $ret = curl_exec($ch);
        curl_close($ch);
        return json_decode($ret, true);
    }

    public function sample_admin_notice__success() {
        $status = apply_filters('b2_theme_check', 'check');

        $status = $status === true || $status === 'test' ? true : false;
        if(!$status){
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo sprintf(__('B2Pro 主题未激活，请先正确安装并%s激活主题%s后再使用','b2'),'<a href="'.admin_url('/wp-admin/admin.php?page=b2_main_options').'">','</a>'); ?></p>
        </div>
        <?php
        }
    }

    public function t5_add_col( $columns ){
        return array('tax_id'=>'ID') + $columns;
    }
    public function t5_show_id( $v, $name, $id ){  
        return 'tax_id' === $name ? $id : $v;
    }
    public function t5_add_col_c( $columns ){
        $collection_name = b2_get_option('normal_custom','custom_collection_name');
        return array('b2_tax_index'=>sprintf(__('%s期数','b2'),$collection_name)) + $columns;
    }
    public function t5_show_id_c( $v, $name, $id ){  
        if($name == 'b2_tax_index'){
            $collection_name = b2_get_option('normal_custom','custom_collection_name');
            $b2_tax_index = get_term_meta($id, 'b2_tax_index', true);
            $b2_tax_index = $b2_tax_index ? sprintf(__('第%s期','b2'),$b2_tax_index) : sprintf(__('请设置一个%s期数','b2'),$collection_name);
            return $b2_tax_index;
        }
        return $v;
    }
    public function t5_tax_id_style(){
        print '<style>#tax_id{width:4em}</style>';
    }

    public function cg_note(){
        $text = $this->check_cg();
        if($text){
            echo '<div class="" style="padding:11px 15px;margin: 5px 15px 2px 2px;box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);background:#fff;border-left:4px solid red">扩展未能正确安装。请前往 <a href="'.admin_url('/admin.php?page=b2_main_options').'">激活页面</a> 根据提示进行操作</div>';
        }
    }

    public function cmb2_render_callback_for_text_two( $field, $value, $object_id, $object_type, $field_type ) {
        $value = wp_parse_args( $value, array(
            'top' => '',
            'bottom' => ''
        ) );
        ?>
        <div><p><label for="<?php echo $field_type->_id( '_address_1' ); ?>"><?php echo __('距离上一个模块高度','b2'); ?></label></p>
		<?php echo $field_type->input( array(
			'name'  => $field_type->_name( '[top]' ),
			'id'    => $field_type->_id( '_top' ),
			'value' => $value['top'],
			'desc'  => '',
		) ); ?>
        </div>
        <div><p><label for="<?php echo $field_type->_id( '_address_2' ); ?>'"><?php echo __('距离下一个模块高度','b2'); ?></label></p>
            <?php echo $field_type->input( array(
                'name'  => $field_type->_name( '[bottom]' ),
                'id'    => $field_type->_id( '_bottom' ),
                'value' => $value['bottom'],
                'desc'  => '',
            ) ); ?>
        </div>
        <?php
    }

    public function filter_posts_columns( $columns ) {
        
        $new['id'] = 'ID';
        $new['d_mp'] = __('微信关键词','b2');
        array_insert($columns,2,$new);
        return $columns;
    }

    public function realestate_column($column, $post_id){
        if ( 'id' === $column ) {
            echo $post_id;
            return;
        }
        if ( 'd_mp' === $column ) {
            $key = get_post_meta($post_id,'single_post_mp_back_key',true);
            echo $key ? $key : '';
            return;
        }
    }

    public function filter_document_columns( $columns){

        $new['d_order'] = __('排序','b2');
        array_insert($columns,3,$new);
        return $columns;
    }

    public function document_column($column, $post_id){
        if ( 'd_order' === $column ) {
            $id = get_post_meta($post_id,'b2_document_order',true);
            if(!$id){
                echo __('缺少排序，前台不显示','b2');
            }else{
                echo $id;
            }
        }
    }

    public function filter_answer_columns( $columns){

        $new['ask'] = __('问题','b2');
        $new['author'] = __('答主','b2');
        array_insert($columns,2,$new);
        return $columns;
    }

    public function answer_column($column, $post_id){
        if ( 'ask' === $column ) {
            $parent = get_post_field('post_parent',$post_id);
            if(!$parent){
                echo __('问题不存在','b2');
            }else{
                echo '<a href="'.get_permalink($parent).'" target="_blank">'.get_the_title($parent).'</a>';
            }
        }
        if('author' === $column){
            $author = get_post_field('post_author',$post_id);

            $user_data = get_userdata($author);
            if($user_data){
                return '<a href="'.get_author_posts_url($author).'" target="_blank">'.$user_data->display_name.'</a>';
            }else{
                return __('游客','b2');
            }
        }
    }

    function wp_ajax_b2_insert_settings(){

        if(!current_user_can('administrator')) return;

        $status = apply_filters('b2_theme_check', 'check');

        $status = $status === true || $status === 'test' ? true : false;
        
        if(!$status){
            print json_encode(array('status'=>401,'data' =>__('请先激活主题再导入数据','b2')));
            exit;
        }

        if(!isset($_FILES['file']['type'])){
            print json_encode(array('status'=>401,'data' =>'上传文件错误'));
            exit;
        }

        if(strpos($_FILES['file']['type'],'text') === false){
            print json_encode(array('status'=>401,'data' =>$_FILES));
            exit;
        }

        $fileName = $_FILES['file']['tmp_name'];

        $fp = fopen($fileName, 'r'); //

        $str = fread ( $fp , filesize ( $fileName ));

        fclose($fp);


        $home = B2_HOME_URI;

        $arg = maybe_unserialize($str);

        if(!$arg){
            print json_encode(array('status'=>401,'data' =>'设置项错误'));
            exit;
        }
        
        foreach ($arg as $k => $v) {

            if(isset($v['option_value'])){
                if(is_array($arg[$k])){
                    $arg[$k]['option_value'] = b2_strReplace(array('https://test.7b2.com','http://test522.jikelao.com'),array($home,$home),maybe_unserialize($v['option_value']));
                }else{
                    $arg[$k] = [];
                    $arg[$k]['option_value'] = b2_strReplace(array('https://test.7b2.com','http://test522.jikelao.com'),array($home,$home),maybe_unserialize($v['option_value']));
                }
                
            }
        }

        if(!empty($arg)){
            foreach ($arg as $k => $v) {
                if(isset($v['option_name']) && ($v['option_name'] != 'b2_circle_default' && $v['option_name'] != 'default_term_circle_tags')) {
                    update_option( $v['option_name'],$v['option_value']);
                }
            }
            
            b2_delete_index_cache();

        }
        print json_encode(array('status'=>401,'data' =>'success'));
        exit;

    }

    public function prettify_bottom($mce_settings) {
    ?>
        <script type="text/javascript">
        QTags.addButton( 'b2pre', '代码高亮', '<pre>\n\n</pre>', "" );//添加高亮代码
        function prettify_bottom() {
        }
        </script>
    <?php
    }
    
    public function allow_search_disply_name($search_columns){
        $search_columns[] = 'display_name';
        return $search_columns;
    }

    public function weixin_menu(){

        if(!current_user_can('administrator')) return;
        
        if(isset($_POST['weixin_menu'])){

            $settings = \B2\Modules\Common\Wecatmp::get_wecat_option();
            
            if($_POST['weixin_menu']){
                $data = stripslashes($_POST['weixin_menu']);

                $data = json_decode($data,true);
    
                try {
    
                    // 实例接口
                    $menu = new \WeChat\Menu($settings);
                
                    // 执行创建菜单
                    $menu->create($data);
                    
                } catch (\Exception $e){
                    // 异常处理
                    wp_die($e->getMessage());
                }
            }else{
                try {

                    // 实例接口
                    $menu = new \WeChat\Menu($settings);
                
                    // 执行删除菜单
                    $data = $menu->delete();
                    
                } catch (\Exception $e){
                    // 异常处理
                    wp_die($e->getMessage());
                }
            }
            
        }
    }

    /**
     * 加载后台使用的CSS和JS文件
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public function setup_admin_scripts(){
        wp_enqueue_script( 'vue', B2_THEME_URI.'/Assets/fontend/library/vue.min.js', array(), B2_VERSION , true );
        wp_enqueue_script( 'v-datepicker', B2_THEME_URI.'/Assets/admin/vuejs-datepicker.min.js', array(), B2_VERSION , true );
        wp_enqueue_script( 'v-datepicker-lg', B2_THEME_URI.'/Assets/admin/zh.js', array(), B2_VERSION , true );

        global $typenow;

        if($this->is_edit_page() && "shop" == $typenow){

            $template = b2_get_option('mult_bulid','multi_group');
            $set = [];
            if(!empty($template)){
               
                foreach ($template as $key => $value) {

                    $g = explode(PHP_EOL,trim($value['values'], " \t\n\r\0\x0B\xC2\xA0"));
                    if($g){

                        foreach ($g as $k => $v) {
                            $row = explode('|',trim($v, " \t\n\r\0\x0B\xC2\xA0"));
                            $set[$key]['keys'][] = $row[0];
                            $set[$key]['values'][] = explode(',',$row[1]);
                        }
                        
                    }
                }
            }

            $post_id = isset($_REQUEST['post']) ? $_REQUEST['post'] : 0;

            $default = '';

            if($post_id){
                $default = get_post_meta($post_id,'b2_multi_box',true);
            }

            $translation_array = array(
                'templates' => $set,
                'default'=>$default
            );

            

            wp_localize_script( 'vue', 'b2shoptemplate', $translation_array );
        }

       

        $opt = b2_get_option('template_downloads','downloads_group');
        $opt = is_array($opt) ? $opt : [];

        wp_localize_script( 'vue', 'b2downloadtemplate',$opt);
        

        //wp_enqueue_script('admin-widgets');
        wp_enqueue_script( 'jike_admin_js',B2_THEME_URI.'/Assets/admin/admin.js?v='.B2_VERSION, array(
            'jquery',
            'jquery-ui-sortable',
            'jquery-ui-draggable',
            'jquery-ui-droppable',
            ), B2_VERSION, true );

        wp_enqueue_style( 'jike_admin_css', B2_THEME_URI.'/Assets/admin/admin.css?v='.B2_VERSION, B2_VERSION, null);
        
    }

    public function is_edit_page($new_edit = null){
        global $pagenow;

        if (!is_admin()) return false;
    
        
        if($new_edit == "edit")
            return in_array( $pagenow, array( 'post.php',  ) );
        elseif($new_edit == "new") 
            return in_array( $pagenow, array( 'post-new.php' ) );
        else 
            return in_array( $pagenow, array( 'post.php', 'post-new.php' ) );
    }

    public function setup_gd_scripts(){
        // 古腾堡编辑器扩展
        if (function_exists('register_block_type')) { //判断是否使用古腾堡编辑器
            wp_register_script( //引入核心js文件
                'b2_block_js',
                B2_THEME_URI.'/Assets/admin/gd_block.js?v='.B2_VERSION,
                array( 'wp-blocks', 'wp-element', 'wp-editor','wp-i18n', 'wp-components' )
            );

            wp_register_style(  //引入css外观样式文件
                'b2_block_css',
                B2_THEME_URI.'/Assets/admin/gd_block.css?v='.B2_VERSION,
                array( 'wp-edit-blocks' )
            );

            register_block_type( 'b2/block', array(
                'editor_script' => 'b2_block_js',
                'editor_style'  => 'b2_block_css',
            ) );
        }
    }

    /**
     * 后台文件上传支持SVG格式
     *
     * @param array $file_types
     *
     * @return array
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public function mimes_support($file_types){
        $new_filetypes = array();
        $new_filetypes['svg'] = 'image/svg+xml';
        $file_types = array_merge($file_types, $new_filetypes );
    
        return $file_types;
    }

    /**
     * 加载后台的设置页面及设置项
     *
     * @return bool
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public function load_settings(){

        do_action('b2_setting_action');

        //自定义菜单功能
        $menu = new Menu();
        $menu->init();

        //Tax页面设置项
        $tax = new Taxonomies();
        $tax->init();

        //加载邀请码设置
        $menu = new Invitation();
        $menu->init();

        //商铺管理
        $shop = new Shop();
        $shop->init();

        //订单管理
        $orders = new Orders();
        $orders->init();

        //文章页面设置
        $post = new Post();
        $post->init();

        //自定义支付设置
        $post = new CustomPay();
        $post->init();

        //自定义编辑器按钮
        $post = new Editor();
        $post->init();

        //加载SEO
        $seo = new Seo();
        $seo->init();

        //加载SEO
        $links = new Links();
        $links->init();

        //信息
        $cash_out = new Infomation();
        $cash_out->init();
        
        $users = new Users();
        $users->init();

        //文档
        $document = new Document();
        $document->init();

        //快讯
        $newsflashes = new Newsflashes();
        $newsflashes->init();

        //圈子
        $circle = new Circle();
        $circle->init();

        //圈子
        $circle = new Ask();
        $circle->init();

        //卡密管理
        $orders = new Card();
        $orders->init();

        //认证管理
        $orders = new Verify();
        $orders->init();

        //多级分销
        $distribution = new Distribution();
        $distribution->init();

        //提现
        $cash_out = new CashOut();
        $cash_out->init();
    }

    public function callback($field, $escaped_value, $object_id, $object_type, $field_type_object) {
        echo $field_type_object->radio();
    }

    public function attributes($args, $defaults, $field, $cmb) {
        if ($field->args['type'] == 'radio_image' && isset($field->args['images'])) {
            foreach ($field->args['images'] as $field_id => $image) {
                if ($field_id == $args['value']) {
                    $image = trailingslashit($field->args['images_path']) . $image;
                    $args['label'] = '<img src="' . $image . '" alt="' . $args['value'] . '" title="' . $args['label'] . '" /><br><span>'.$args['label'].'</span>';
                }
            }
        }
        return $args;
    }

    /**
     * 创建设置页面
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public function main_options_page(){

        $options = new_cmb2_box(array(
            'id'	=>	'b2_main_options_page',
            'title'	=>	__('B2主题设置','b2'),
            'icon_url'	=>	'dashicons-admin-generic',
            'option_key'      => 'b2_main_options',
            'show_on'	=>	array(
                'options-page'	=>'b2_main_options',
            ),
            'object_types' => array( 'options-page' ),
            'display_cb'      => array($this,'main_option_page_cb'),
            'menu_title'    => __('B2主题设置','b2'),
            'tab_title'   => __('B2主题设置','b2'),
        ));
    }

    public function check_cg(){
        preg_match("#^\d.\d#", PHP_VERSION, $p_v);

        $text = '';

        if($p_v[0] < '7.2'){
            $text = '<h2 class="red">请升级您的PHP，建议使用 PHP7.3</h2>';
        }

        // if($p_v[0] >= '8.0'){
        //     $text = '<h2 class="red">当前版本暂未支持 php8.0，建议使用 php7.3 版本</h2> ';
        // }

        $loader_name = PATH_SEPARATOR==':' ? 'loader'.str_replace('.','',$p_v[0]).'.so' : 'win_loader'.str_replace('.','',$p_v[0]).'.dll';

        $path = B2_THEME_DIR;

        $path =  PATH_SEPARATOR!=':' ? str_replace('/',B2_DS,$path) : $path;

        if(!$text){
            if(extension_loaded('swoole_loader')){
                $ext = new \ReflectionExtension('swoole_loader');
                $ver = $ext->getVersion();

                // if($ver >= '3.0'){
                //     $text = '<h2 class="red">当前版本暂未支持 php8.0，建议使用 php7.3 版本 </h2>';
                // }

                if($ver < '3.1'){
                    $text = '<h2 class="red">升级扩展：请按照如下提示进行操作</h2>
                    <p>'.sprintf(__('1、打开您的php.ini文件（%s），删除类似%s的整行代码。一般在php.ini文件最下面几行','b2'),'<code>'.php_ini_loaded_file().'</code>','<code>='.$loader_name.'</code>').'</p>
                    <p>'.sprintf(__('2、将%s复制到php.ini文件的最后一行保存','b2'),'<code>extension='.$path.B2_DS.'Assets'.B2_DS.'admin'.B2_DS.'loader'.B2_DS.$loader_name.'</code>').'</p>
                    <p>'.__('3、重启php','b2').'</p>
                    <p>'.__('4、刷新本页后激活','b2').'</p>';
                }
    
            }else{
                $text = '<h2 class="red">请安装扩展</h2>
                <p>'.__('未安装扩展，请按照下面的方法进行安装','b2').'</p>
                <p>'.sprintf(__('1、打开您的php.ini文件（%s），然后将%s复制到php.ini文件的最后一行保存','b2'),'<code>'.php_ini_loaded_file().'</code>','<code>extension='.$path.B2_DS.'Assets'.B2_DS.'admin'.B2_DS.'loader'.B2_DS.$loader_name.'</code>').'</p>
                <p>'.__('2、重启php','b2').'</p>
                <p>'.__('3、刷新本页后激活','b2').'</p>';
            }
        }

        return $text;
    }

    /**
     * 设置页面首页，欢迎页面
     *
     * @return string
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public function main_option_page_cb(){
        $status = apply_filters('b2_theme_check', 'check');

        // $status = $status === true || $status === 'test' ? true : false;
        
        $id = apply_filters('b2_get_theme_id',1);
        $id = isset($id['id']) ? (int)$id['id'] : '';

        $text = $this->check_cg();
        ?>
        <div id="b2-settings-opt"></div>
        <div class="wrap">
            <style>
                .jihuo p{
                    font-size:15px
                }
            </style>
            <h1><?php echo __('感谢您使用B2主题','b2');?></h1>
                <?php if($text){ echo '<div class="jihuo">'.$text.'</div>';}else{  ?>
                    <h2 class="title"><?php echo __('激活','b2');?></h2>
                    <form method="post">
                        <input type="hidden" id="_wpnonce" name="_wpnonce" value="<?php echo wp_create_nonce( 'check-nonce' );?>">
                
                        <?php
                            echo '<div style="margin-top:20px;margin-bottom:10px">主题当前状态：'.($status === 'test' ? '<b style="color:green">测试环境</b>' :($status === true ? '<b style="color:green">已激活</b>' : '<b style="color:red">未激活</b>')).'</div>';
                        ?>

                        <input type="text" value="<?php echo $id  ?>" name="zrz_theme_id">
                        <p><?php echo __('请在官网个人中心查看是第几号会员，然后把会员号填在此处激活','b2'); ?></p>
                        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo $status ? '手动更新授权' : '激活' ;?>"></p>
                    </form>
                    <?php if($status){ ?>
                    <h2 class="title"><?php echo __('设置项导出','b2');?></h2>
                    <a class="button empty" id="b2-settings-output" href="<?php echo admin_url('?page=b2_main_options&output_settings=1'); ?>"><?php echo __('导出B2主题设置项','b2'); ?></a>
                    <p class="desc"><?php echo sprintf(__('%s如果您的主题已经设置完毕，或者有其他重要的设置需要变更，建议及时导出.%s只限B2主题的设置项和小工具，不包括文章、分类设置、订单、卡密、邀请码等数据%s','b2'),'<p class="red">','<br>','</p>'); ?></p>

                    <h2 class="title"><?php echo __('设置项导入','b2');?></h2>
                    <label class="button empty"><?php echo __('导入B2主题设置项','b2'); ?><input type="file" accept="text/plain" id="inputsettings" style="display:none" onchange="b2getFilename(event)"></label>
                    <p class="desc"><?php echo __('1、导入的设置项将会替换原有的设置项，请谨慎操作。导入的文件格式为.txt','b2'); ?></p>
                    <p class="desc"><?php echo __('2、如果您已有设置好的项目，请先导出备份一下。导入的文件格式为.txt','b2'); ?></p>
                    <?php } ?>
                <?php } ?>

        <h2><?php echo __('说明','b2'); ?></h2>
        <p>感谢您选择B2主题，这是一个来自未来的主题，我们使用了众多新的技术，及其方便的扩展能力让您不必操心站点的问题，专心经营内容。<p>
        <p>我们已经为3000多个用户提供了优质的服务，您将也是其中之一。如果您还没有购买主题，请加QQ联系我们：110613846</p>
        <p>B2主题涉及到众多敏感信息，包括支付宝，微信的账户的私密信息，实名认证信息等，请不要使用来路不明或未经授权的主题，否则造成的一切后果我们概不负责</p>
        <p>未激活的主题不能正常使用，请第一时间激活主题</p>
        <p>如果您已经购买了我们的主题，请加入我们的售后群：一群：<del>424186042</del>（已满）；二群：612496574</p>
        <h2>主题安装方法：</h2>
        <p>请根据下面的提示进行设置：</p>
        <p>1、设置服务器伪静态和固定连接：<a href="https://7b2.com/document/36878.html" target="_blank">设置伪静态和固定连接</a></p>
        <p>2、开启opcache，可以大幅提升PHP的执行效率。<span class="red">强烈建议安装</span>，安装方法：<a href="https://7b2.com/document/59371.html" target="_blank">https://7b2.com/document/59371.html</a></p>
        <p>3、安装 redis 缓存：一方面可以大幅提高主题运行速度，另一方面主题一些安全功能依赖数据缓存，<span class="red">强烈建议安装</span>，安装方法：<a href="https://7b2.com/document/56500.html" target="_blank">https://7b2.com/document/56500.html</a></p>
    </div>
    <?php
    }

    public function vip_count(){


        if(isset($_REQUEST['index_group'])){
            Cache::clean_index_module_cache();
            wp_cache_incr( 'widget' );
        }

        if(isset($_REQUEST['weixin_message_open']) && $_REQUEST['weixin_message_open'] == 1){
            do_action('b2_weixin_message_action');
        }

        if(isset($_REQUEST['user_slug'])){

            global $wpdb;
            $vip_info = b2_get_option('normal_user','user_vip_group');
            $count = array();
            foreach ($vip_info as $k => $v) {
                $row = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT COUNT(*)
                        FROM {$wpdb->usermeta}
                        WHERE meta_key = %s AND meta_value = %s
                    ",'zrz_vip','vip'.$k),
                    ARRAY_N
                );

                $count['vip'.$k] = $row[0];
            }

            update_option('b2_vip_count',$count);
        }

        if(isset($_GET['page']) && $_GET['page'] === 'b2_verify_list' && isset($_GET['status']) && isset($_GET['action']) && $_GET['action'] ==='edit' ){

            
            global $wpdb;
            $table_name = $wpdb->prefix . 'b2_verify';
            $res = $wpdb->get_row(
                $wpdb->prepare("
                    SELECT * FROM $table_name
                    WHERE user_id=%s
                    ",
                    $_GET['user_id']
            ),ARRAY_A);

            $data = array(
                'user_id'=>$_GET['user_id'],
                'verified'=>$_GET['verified'],
                'name'=>$_GET['name'],
                'identification'=>$_GET['identification'],
                'card'=>$_GET['card'],
                'title'=>$_GET['title'],
                'status'=>$_GET['status'],
            );
            \B2\Modules\Common\Verify::add_verify_data($data);

            if((int)$res['status'] === 4 && (int)$_GET['status'] === 2){

                $task_check = get_user_meta($_GET['user_id'],'b2_task_check',true);
               
                if($task_check === ''){
                    $credit = b2_get_option('normal_task','task_user_verify');
                    if((int)$credit !== 0){
                        // $total = \B2\Modules\Common\Credit::credit_change($_GET['user_id'],$credit);
        
                        \B2\Modules\Common\Message::update_data([
                            'date'=>current_time('mysql'),
                            'from'=>0,
                            'to'=>$_GET['user_id'],
                            'post_id'=>0,
                            'msg'=>__('您已经完成了认证任务','b2'),
                            'type'=>'user_verify',
                            'type_text'=>__('认证成功','b2')
                        ]);

                        \B2\Modules\Common\Gold::update_data([
                            'date'=>current_time('mysql'),
                            'to'=>$_GET['user_id'],
                            'gold_type'=>0,
                            'post_id'=>0,
                            'no'=>$credit,
                            'msg'=>__('认证任务完成奖励','b2'),
                            'type'=>'user_verify',
                            'type_text'=>__('认证完成','b2')
                        ]);

                        //积分记录
                        // \B2\Modules\Common\Message::add_message(array(
                        //     'user_id'=>$_GET['user_id'],
                        //     'msg_type'=>60,
                        //     'msg_read'=>0,
                        //     'msg_date'=>current_time('mysql'),
                        //     'msg_users'=>'',
                        //     'msg_credit'=>$credit,
                        //     'msg_credit_total'=>$total,
                        //     'msg_key'=>'',
                        //     'msg_value'=>''
                        // ));

                        update_user_meta($_GET['user_id'],'b2_task_check',1);
                    }
                }
            }

            do_action('b2_notify_verify_change',$_GET['user_id'],$res['status'],(int)$_GET['status']);

            if((int)$_GET['status'] === 1 || (int)$_GET['status'] === 3 || (int)$_GET['status'] === 4){
                delete_user_meta($_GET['user_id'], 'b2_title');
            }else{
                update_user_meta($_GET['user_id'],'b2_title',$_GET['title']);
            }
            wp_cache_delete('b2_user_'.$_GET['user_id'],'b2_user_data');
            wp_cache_delete('b2_user_'.$_GET['user_id'],'b2_user_custom_data');
        }

        if(isset($_GET['output_settings']) && (int)$_GET['output_settings'] === 1){
            global $wpdb;
            $results = $wpdb->get_results("SELECT * FROM $wpdb->options WHERE (`option_name` LIKE '%b2\_%' OR `option_name` LIKE '%widget\_b2%' OR  `option_name`='sidebars_widgets' OR `option_name`='theme_mods_b2') AND `option_name` NOT LIKE '%\_transient\_%'",ARRAY_A);

            $date = current_time('mysql');
            $date = date_create($date);

            $y = date_format($date,'Y');
            $m = date_format($date,'m');
            $d = date_format($date,'d');
            $time = date_format($date,'H');

            header("Content-Type: application/octet-stream");    
            $center = serialize($results);   
            $filename = 'b2-settings-'.$y.'-'.$m.'-'.$d.'-'.$time.'.txt';//生成的文件名 
            if (preg_match("/MSIE/", $_SERVER['HTTP_USER_AGENT']) ) { 
                header('Content-Disposition:  attachment; filename="' . $filename . '"'); 
            } elseif (preg_match("/Firefox/", $_SERVER['HTTP_USER_AGENT'])) { 
                // header('Content-Disposition: attachment; filename*="utf8' .  $filename . '"');
                header('Content-Disposition: attachment; filename*="' .  $filename . '"'); 
            } else { 
                header('Content-Disposition: attachment; filename="' .  $filename . '"'); 
            }
            echo $center;
            exit;
        }

        if(isset($_POST['b2_circle_admin']) && !empty($_POST['b2_circle_admin'])){

            global $wpdb;
            $table_name = $wpdb->prefix . 'b2_circle_related';

            $old =  $wpdb->get_row(
                $wpdb->prepare("
                    SELECT * FROM $table_name
                    WHERE circle_id= %d
                    AND circle_role=%s
                    ",
                    (int)$_POST['tag_ID'],'admin'
            ),ARRAY_A);

            if(empty($old)){
                CircleRelate::update_data(array(
                    'user_id'=>(int)$_POST['b2_circle_admin'],
                    'circle_id'=>(int)$_POST['tag_ID'],
                    'circle_role'=>'admin',
                    'join_date'=>current_time('mysql')
                ));
            }else{
                CircleRelate::update_data(
                    array(
                        'circle_role' => 'member',
                    ),
                    array(
                        'user_id'=>$old['user_id'],
                        'circle_id'=>(int)$_POST['tag_ID'],
                        'circle_role'=>'admin',
                    )
                );

                $old_user =  $wpdb->get_row(
                    $wpdb->prepare("
                        SELECT * FROM $table_name
                        WHERE user_id=%d
                        AND circle_id= %d
                        ",
                        (int)$_POST['b2_circle_admin'],(int)$_POST['tag_ID']
                ),ARRAY_A);

                if($old_user){
                    CircleRelate::update_data(
                        array(
                            'circle_role' => 'admin',
                        ),
                        array(
                            'circle_id'=>(int)$_POST['tag_ID'],
                            'user_id'=>(int)$_POST['b2_circle_admin'],
                            'circle_role'=>$old_user['circle_role']
                        )
                    );
                }else{
                    CircleRelate::update_data(array(
                        'user_id'=>(int)$_POST['b2_circle_admin'],
                        'circle_id'=>(int)$_POST['tag_ID'],
                        'circle_role'=>'admin',
                        'join_date'=>current_time('mysql')
                    ));
                }
            }
        }

    }
}