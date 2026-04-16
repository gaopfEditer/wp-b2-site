<?php
namespace B2;

use B2\Modules\Settings\Main as SettingsLoader;
use B2\Modules\Templates\Main as TemplatesLoader;
use B2\Modules\Common\Main as CommonLoader;

if ( ! class_exists( 'B2', false ) ) {
    class B2{

        private $is_admin = false;

        public function __construct(){

            if($this->is_admin && (!defined('AUTH_KEY') || strlen(AUTH_KEY) < 64)){
                add_filter( 'admin_notices', [$this,'run_jwt_plugin'] );
            }

            if(!class_exists('Jwt_Auth')){

                if(!defined('JWT_AUTH_SECRET_KEY')){
                    define('JWT_AUTH_SECRET_KEY', strrev(AUTH_KEY));
                }
    
                if(!defined('JWT_AUTH_CORS_ENABLE')){
                    define('JWT_AUTH_CORS_ENABLE', true);
                }
    
                require_once B2_THEME_DIR.B2_DS.'Library'.B2_DS.'jwt'.B2_DS.'includes/class-jwt-auth.php';
                $plugin = new \Jwt_Auth();
                $plugin->run();
                
            }else{
                if($this->is_admin){
                    add_filter( 'admin_notices', [$this,'remove_jwt_plugin'] );
                }
            }

            $this->is_admin = is_admin() || $GLOBALS['pagenow'] === 'wp-login.php';

            spl_autoload_register([$this,'autoload']);

            $this->load_library();

            $this->load_modules();
            
        }

        /**
         * 加载依赖
         *
         * @return void
         * @author Li Ruchun <lemolee@163.com>
         * @version 1.0.0
         * @since 2018
         */
        public function load_library(){
            

            try {
                $ext = new \ReflectionExtension('swoole_loader');
                $ver = $ext->getVersion();

                preg_match("#^\d.\d#", PHP_VERSION, $p_v);
                if($p_v < 7.2){
                    wp_die('<h2>'.__('PHP版本太低，请升级到 7.2 以上的版本。','b2').'</h2><p>'.__('如果您是管理员，请登陆后台操作','b2').'</p>');
                }

                if($ver < '3.1'){
                    if(!$this->is_admin){
                        if(current_user_can('administrator')){
                            wp_die('<h2>'.__('请激活主题','b2').'</h2><p>'.sprintf(__('请前往%sB2主题激活%s页面激活主题','b2'),'<a href="'.admin_url('/admin.php?page=b2_main_options').'" target="_blank">','</a>').'</p>');
                        }else{
                            wp_die('<h2>'.__('系统维护中.....','b2').'</h2><p>'.__('如果您是管理员，请登陆后台操作','b2').'</p>');
                        }
                    }
                }else{
 
                    try {
                        require_once B2_THEME_DIR .B2_DS.'Modules'.B2_DS.'Common'.B2_DS.'Private'.B2_DS.'private'.$p_v[0].'.php';
                        require_once B2_THEME_DIR .B2_DS.'Modules'.B2_DS.'Common'.B2_DS.'Private'.B2_DS.'filter'.$p_v[0].'.php';
                    }catch (\Throwable $th) {
                        if(!$this->is_admin){
                            if(current_user_can('administrator')){
                                wp_die('<h2>'.__('请重启一下php','b2').'</h2><p>'.__('显示这个页面说明扩展已经升级，请重启一下您的PHP','b2').'</p>');
                            }else{
                                wp_die('<h2>'.__('系统维护中.....','b2').'</h2><p>'.__('如果您是管理员，请登陆后台操作','b2').'</p>');
                            }
                        }
                    }
                }
            } catch (\Throwable $th) {
                if(!$this->is_admin){
                    if(current_user_can('administrator')){
                        wp_die('<h2>'.__('安装扩展','b2').'</h2><p>'.sprintf(__('请前往%sB2安装扩展%s页面安装扩展','b2'),'<a href="'.admin_url('/admin.php?page=b2_main_options').'" target="_blank">','</a>').'</p>');
                    }else{
                        wp_die('<h2>'.__('系统维护中.....','b2').'</h2><p>'.__('如果您是管理员，请登陆后台操作','b2').'</p>');
                    }
                }
            }

            if($this->is_admin){
                //加载cmb2
                require_once B2_THEME_DIR .B2_DS.'Library'.B2_DS.'Cmb2'.B2_DS.'init.php';
			    require_once B2_THEME_DIR .B2_DS.'Library'.B2_DS.'cmb2-term-select'.B2_DS.'cmb2-term-select.php';
                require_once B2_THEME_DIR .B2_DS.'Library'.B2_DS.'cmb2-nav-menu'.B2_DS.'cmb2-nav-menus.php';
                require_once B2_THEME_DIR .B2_DS.'Library'.B2_DS.'cmb-field-select2'.B2_DS.'cmb-field-select2.php';
                require_once B2_THEME_DIR .B2_DS.'Library'.B2_DS.'cmb2-page-select-master'.B2_DS.'cmb2_page_select.php';
				
                // add_action('admin_enqueue_scripts', function() {
                //     wp_register_style('cmb2_widgets', B2_THEME_URI.'/Library/cmb2-widget/assets/cmb2-widgets.css', false, '1.0.0');
                //     wp_register_script('cmb2_widgets', B2_THEME_URI.'/Library/cmb2-widget/assets/cmb2-widgets.js', ['jquery'], '1.0.0');
        
                //     wp_enqueue_style('cmb2_widgets');
                //     wp_enqueue_script('cmb2_widgets');
                // });

               
            }
            
            //操作dom库
            //require_once B2_THEME_DIR.B2_DS.'Library'.B2_DS.'simple_html_dom.php';

            require_once B2_THEME_DIR.B2_DS.'Library'.B2_DS.'WeChatDeveloper'.B2_DS.'include.php';

            //加载图片裁剪库
            require_once B2_THEME_DIR.B2_DS.'Library'.B2_DS.'Grafika'.B2_DS.'Grafika.php';

            //微信官方
            require_once B2_THEME_DIR .B2_DS.'Library'.B2_DS.'Wxjs'.B2_DS.'jssdk.php';

            add_action( 'rest_api_init',  function() {
                register_rest_route('cmb2-term-select/v1', '/(?P<taxonomy>[\w-]+)', array(
                  'methods' => 'get',
                  'callback' => array($this,'rest_get_term_search'),
                  'permission_callback' => '__return_true'
                ));
            });

        }

        public function run_jwt_plugin() {
            ?>
            <div class="notice notice-error">
                <p><?php echo sprintf(__('您的 Wordpress 安装不完整，请按照链接中的说明进行修复：%shttps://7b2.com/document/59388.html%s','b2'),
                '<a href="https://7b2.com/document/59388.html" target="_blank">','</a>'); ?></p>
            </div>
            <?php
        }

        public function remove_jwt_plugin(){
            ?>
            <div class="notice notice-error">
                <p><?php echo sprintf(__('B2主题不再必须单独安装 JWT（JWT Authentication for WP-API） 插件，现在，您可以将其 %s禁用或删除%s。','b2'),
                '<a href="'.admin_url('/plugins.php').'" target="_blank">','</a>'); ?></p>
            </div>
            <?php
        }
           
        /**
         * 加载模块
         *
         * @return void
         * @author Li Ruchun <lemolee@163.com>
         * @version 1.0.0
         * @since 2018
         */
        public function load_modules(){

            //加载设置项
            if($this->is_admin){
                $settings = new SettingsLoader();
                $settings->init();
            }

            //加载公共类
            $common = new CommonLoader();
            $common->init();

            // //加载模板
            $templates = new TemplatesLoader();
            $templates->init();
        }

        public function rest_get_term_search($request){

            $taxonomy     = $request->get_param( 'taxonomy' );
            $search_term = $request->get_param( 'term' );
    
    
            if ( empty( $search_term ) ) {
                return false;
            }
    
            $terms = get_terms( $taxonomy, array(
                'number'       => 10,
                'hide_empty'   => false,
                'name__like'   => sanitize_text_field( $search_term ),
                'cache_domain' => 'cmb2_term_select_' . $taxonomy,
            ) );
    
            if ( empty( $terms ) ) {
                return false;
            }
    
            $results = array();
            foreach ( $terms as $term ) {
    
                $name = $term->name;
    
                if ( $term->parent && ( $parent_term = get_term_by( 'id', $term->parent, $taxonomy ) ) ) {
                    $name = $parent_term->name .' / ' . $name;
                }
    
                $results[] = array(
                    'label' => $name,
                    'value' => $term->term_id,
                );
            }
    
            return $results;
        }

        /**
         * 自动加载命名空间
         *
         * @return void
         * @author Li Ruchun <lemolee@163.com>
         * @version 1.0.0
         * @since 2018
         */
        public function autoload($class){

            //主题模块
            if (strpos($class, 'B2\\') !== false) {
                $class = str_replace('B2\\','',$class);
                require_once B2_THEME_DIR.B2_DS.str_replace('\\', B2_DS, $class).'.php';
            }

            //图片裁剪库
            if(preg_match("/^Grafika\\\/i", $class)){
                $filename = B2_THEME_DIR.B2_DS.'Library'.B2_DS.str_replace('\\', B2_DS, $class).'.php';
                require_once $filename;
            }
        }
    }

    new B2();
}