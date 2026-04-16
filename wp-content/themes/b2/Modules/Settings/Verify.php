<?php
namespace B2\Modules\Settings;

use B2\Modules\Settings\InvitationTable;

class Verify{

    //默认设置项
    public static $default_settings = array(
        'verify_img'=>'',
        'verify_text'=>'如果您是领域中的名人、代表企业或组织，或担心我们的平台有人冒充您，可申请认证。',
        'verify_type'=>array(),
        'verify_money'=>'100',
        'verify_mp_text'=>'关注公众号验证成功，感谢您的关注！',
        'verify_money_text'=>'我们会人工对您的认证信息进行审核，通过认证之后您将获得一些特殊的权力。',
        'verify_allow'=>1,
        'verify_user_role'=>array(),
        'verify_check'=>1
    );

    public function init(){
        add_action('cmb2_admin_init',array($this,'verify_options_page'));
        add_action('cmb2_admin_init',array($this,'list_options_page'));
    }

    /**
     * 获取默认设置项
     *
     * @param string $key 数组键值
     *
     * @return void
     * @author Li Ruchun <lemolee@163.com>
     * @version 1.0.0
     * @since 2018
     */
    public static function get_default_settings($key){

        $arr = array(
            'verify_img'=>B2_THEME_URI.'/Assets/fontend/images/rz.jpg'
        );

        if($key == 'all'){
            return $arr;
        }

        if(isset($arr[$key])){
            return $arr[$key];
        }
    }

    public function verify_options_page(){

        $verify = new_cmb2_box(array(
            'id'           => 'b2_verify_main_options_page',
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_verify_main',
            'tab_group'    => 'b2_verify_options',
            'parent_slug'     => 'b2_main_options',
            'tab_title'    => __('认证设置','b2'), 
            'menu_title'   => __('认证管理','b2'),
        ));

        $verify->add_field(array(
            'name' => __('是否启用认证','b2'),
            'id'   => 'verify_allow',
            'type'             => 'select',
            'options' => array(
                0 => __('关闭','b2'),
                1=>__('启用','b2')
            ),
            'default'          => self::$default_settings['verify_allow']
        ));

        $verify->add_field(array(
            'name' => __('认证页面顶部图片','b2'),
            'id'   => 'verify_img',
            'type'             => 'file',
            'options' => array(
                'url' => true, 
            ),
            'desc'=>__( '显示在认证页面顶部', 'b2' ),
            'default'          => self::get_default_settings('verify_img')
        ));

        $verify->add_field(array(
            'name' => __('认证页面描述','b2'),
            'id'   => 'verify_text',
            'type'             => 'textarea',
            'desc'=>__( '向用户说明为什么需要认证，比如：如果您是领域中的名人、代表企业或组织，或担心我们的平台有人冒充您，可申请认证。', 'b2' ),
            'default'          => self::$default_settings['verify_text']
        ));

        $verify->add_field(array(
            'name' => __('认证必要条件','b2'),
            'id'   => 'verify_type',
            'type'             => 'multicheck',
            'options'=>array(
                1=>__('姓名身份证等个人信息','b2'),
                2=>__('关注公众号','b2'),
                3=>__('支付费用','b2'),
                // 4=>__('验证手机号码','b2')
            ),
            'desc'=>sprintf(__( '身份证等个人信息，需要管理员自行审核。关注公众号，请在主题设置->常规设置->微信设置里面填写公众号信息。支付费用请在下面填写支付的金额', 'b2' ),'<br>'),
            'default'          => self::$default_settings['verify_type']
        ));

        $verify->add_field(array(
            'name' => __('认证需要支付的金额','b2'),
            'id'   => 'verify_money',
            'type'             => 'text_money',
            'sanitization_cb' => 'b2_sanitize_number',
            'before_field' => B2_MONEY_SYMBOL,
            'desc'=>__( '如果开启了付费认证，需要填写此项。', 'b2' ),
            'default'          => self::$default_settings['verify_money']
        ));

        $verify->add_field(array(
            'name' => __('支付费用说明','b2'),
            'id'   => 'verify_money_text',
            'type'             => 'textarea',
            'desc'=>__( '如果开启了付费认证，要向用户说明付费的原因，请填写此项。', 'b2' ),
            'default'          => self::$default_settings['verify_money_text']
        ));

        $verify->add_field(array(
            'name' => __('关注成功微信内部提示信息','b2'),
            'id'   => 'verify_mp_text',
            'type'             => 'textarea',
            'desc'=>__( '如果开启了关注公众号认证，需要填写此项。', 'b2' ),
            'default'          => self::$default_settings['verify_mp_text']
        ));

        $verify->add_field(array(
            'name' => __('认证用户的权限','b2'),
            'id'      => 'verify_user_role',
            'type'    => 'multicheck_inline',
            'options' => b2_roles_arg(),
            'desc'=>sprintf(__('此权限和%s用户等级处设置%s的权限有重合，如果用户等级处设置的权限为否，才会检查此处的权限','b2'),'<a href="'.admin_url('/admin.php?page=b2_normal_user').'" target="_blank">','</a>')
        ) );

        $verify->add_field(array(
            'name' => __('是否手动审核认证信息','b2'),
            'id'      => 'verify_check',
            'type'    => 'select',
            'options' => array(
                0=>__('认证信息人工审核','b2'),
                1=>__('认证信息自动审核','b2')
            ),
            'default'=>self::$default_settings['verify_check'],
            'desc'=>__('如果选择自动审核，用户提交以后自动生效，否则需要管理员在认证列表中进行手动审核','b2')
        ) );

    }


    public function list_options_page(){
        $list = new_cmb2_box(array(
            'id'           => 'b2_verify_list_options_page',
            'tab_title'    => __('认证列表','b2'), 
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_verify_list',
            'parent_slug'     => '/admin.php?page=b2_verify_main',
            'tab_group'    => 'b2_verify_options',
            'display_cb'      => array($this,'list_option_page_cb'),
            'save_button'     => false,
        ));
    }

    static function cb_options_page_tabs( $cmb_options ) {
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
        $verify_code = new VerifyListTable();
        $verify_code->prepare_items();
        $status = isset($_GET["status1"]) ? esc_sql($_GET["status1"]) : 'all';
        $ref_url = wp_get_referer();
        $ref_url = remove_query_arg(array('id', 'action','action2','s'), $ref_url);
        if((isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete') || (isset($_REQUEST['action2']) && $_REQUEST['action2'] == 'delete')){
                
            $order_ids = isset($_REQUEST['id']) ? (array)$_REQUEST['id'] : '';
            if($order_ids){
                $verify_code->delete_coupons($order_ids);

                
                exit(header("Location: ".$ref_url));
                //echo '<script> location.replace("'.$ref_url.'"); </script>';
            }
        }
        ?>
        <div class="wrap cmb2-options-page option-<?php echo $cmb_options->option_key; ?>">
            <?php if ( get_admin_page_title() ) : ?>
                <h2><?php echo wp_kses_post( get_admin_page_title() ); ?></h2>
            <?php endif; ?>
            <h2 class="nav-tab-wrapper">
                <?php foreach ( $tabs as $option_key => $tab_title ) : ?>
                    <a class="nav-tab<?php if ( isset( $_GET['page'] ) && $option_key === $_GET['page'] ) : ?> nav-tab-active<?php endif; ?>" href="<?php menu_page_url( $option_key ); ?>"><?php echo wp_kses_post( $tab_title ); ?></a>
                <?php endforeach; ?>
            </h2>
            <div class="wrap">
                <?php
                    if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit'){
                        $id = $_REQUEST['id'];
                        global $wpdb;
                        $table_name = $wpdb->prefix . 'b2_verify';
                        $data = $wpdb->get_row(
                            $wpdb->prepare("
                                SELECT * FROM $table_name
                                WHERE id=%d
                                ",
                                $id
                        ),ARRAY_A);

                        wp_cache_delete('b2_user_'.$id,'b2_user_custom_data');
                        wp_cache_delete('b2_user_'.$id,'b2_user_data');
                        ?>
                        <a href="<?php echo remove_query_arg(array('id','kuaidi','express_number','order_address','order_content','action','order_update','submit-update-order'),$ref_url); ?>">返回到订单列表</a>
                            <form id="coupon-filter" method="get">
                                <h2><?php echo __('编辑认证资料','b2'); ?></h2>
                                <table class="form-table" role="presentation">
                                    <tbody>
                                        <tr class="user-user-login-wrap">
                                            <th><label for="user_login"><?php echo __('认证用户','b2'); ?></label></th>
                                            <td>
                                                <a href="<?php echo get_the_author_link($data['user_id']);?>" target="_blank"><?php echo get_the_author_meta('display_name', $data['user_id']); ?></a>
                                            </td>
                                        </tr>
                                        <tr class="user-user-login-wrap">
                                            <th><label for="user_login"><?php echo __('认证日期','b2'); ?></label></th>
                                            <td><?php echo $data['date'];?></td>
                                        </tr>
                                        <tr class="user-user-login-wrap">
                                            <th><label for="user_login"><?php echo __('实名状态','b2'); ?></label></th>
                                            <td>
                                                <select name="verified" id="locale">
                                                    <option value="0" <?php echo selected($data['verified'],'0',false); ?>><?php echo __('未实名','b2'); ?></option>
                                                    <option value="1" <?php echo selected($data['verified'],'1',false); ?>><?php echo __('已实名','b2'); ?></option>
                                                </select>
                                                <p class="description"><?php echo __('如果用户填写了实名信息（姓名，身份证等）,检查通过请设置为已实名','b2'); ?></p>
                                            </td>
                                        </tr>
                                        <tr class="user-user-login-wrap">
                                            <th><label for="user_login"><?php echo __('姓名','b2'); ?></label></th>
                                            <td><input type="text" name="name" value="<?php echo $data['name']; ?>" class="regular-text"></td>
                                        </tr>
                                        <tr class="user-user-login-wrap">
                                            <th><label for="user_login"><?php echo __('身份证号码','b2'); ?></label></th>
                                            <td><input type="text" name="identification" value="<?php echo $data['identification']; ?>" class="regular-text"></td>
                                        </tr>
                                        <tr class="user-user-login-wrap">
                                            <th><label for="user_login"><?php echo __('身份证图片','b2'); ?></label></th>
                                            <td><input type="text" name="card" value="<?php echo $data['card']; ?>" class="regular-text"></td>
                                        </tr>
                                        <tr class="user-user-login-wrap">
                                            <th><label for="user_login"><?php echo __('是否关注公众号','b2'); ?></label></th>
                                            <td><?php echo (int)$data['mp'] == 1 ? __('已关注','b2') : __('未关注','b2'); ?></td>
                                        </tr>
                                        <tr class="user-user-login-wrap">
                                            <th><label for="user_login"><?php echo __('付款状态','b2'); ?></label></th>
                                            <td><?php echo (int)$data['money'] > 0 ? __('已付款','b2') : __('未付款','b2'); ?></td>
                                        </tr>
                                        <tr class="user-user-login-wrap">
                                            <th><label for="user_login"><?php echo __('称号','b2'); ?></label></th>
                                            <td><input type="text" name="title" value="<?php echo $data['title']; ?>" class="regular-text"></td>
                                        </tr>
                                        <tr class="user-user-login-wrap">
                                            <th><label for="user_login"><?php echo __('认证状态','b2'); ?></label></th>
                                            <td>
                                                <select name="status" id="locale">
                                                    <option value="1" <?php echo selected($data['status'],'1',false); ?>><?php echo __('未认证','b2'); ?></option>
                                                    <option value="2" <?php echo selected($data['status'],'2',false); ?>><?php echo __('已认证','b2'); ?></option>
                                                    <option value="3" <?php echo selected($data['status'],'3',false); ?>><?php echo __('已拉黑','b2'); ?></option>
                                                    <option value="4" <?php echo selected($data['status'],'4',false); ?>><?php echo __('待审中','b2'); ?></option>
                                                </select>
                                                <p class="description"><?php echo __('用户关注了公众号，然后又取消关注公众号，会处于拉黑状态，这里修改为非拉黑状态，并不能改变用户取消关注的事实，但是可以允许用户再次申请认证。','b2'); ?></p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="user_id" value="<?php echo $data['user_id']; ?>">
                                <input type="hidden" name="id" value="<?php echo $data['id']; ?>">
                                <input name="page" value="b2_verify_list" type="hidden">
                                <input type="hidden" name="paged" value="<?php echo isset($_GET['paged']) ? (int)$_GET['paged'] : 0;?>">
                                <p class="submit"><button type="submit" id="submit-cmb" class="button button-primary"><?php echo __('保存更改','b2'); ?></button></p>
                            </form>
                        <?php
                    }else{
                ?>
                    <ul class="subsubsub">
                        <li class="all"><a href="<?php echo home_url('/wp-admin/admin.php?page=b2_verify_list&status1=all'); ?>" class="<?php echo $status === 'all' ? 'current' : ''; ?>"><?php echo __('所有','b2'); ?><span class="count">（<?php echo $verify_code->get_status_count('all'); ?>）</span></a> |</li>
                        <li class="mine"><a href="<?php echo home_url('/wp-admin/admin.php?page=b2_verify_list&status1=2'); ?>" class="<?php echo $status === '2' ? 'current' : ''; ?>"><?php echo __('已认证','b2'); ?><span class="count">（<?php echo $verify_code->get_status_count(2); ?>）</span></a> |</li>
                        <li class="publish"><a href="<?php echo home_url('/wp-admin/admin.php?page=b2_verify_list&status1=4'); ?>" class="<?php echo $status === '4' ? 'current' : ''; ?>"><?php echo __('待审中','b2'); ?><span class="count">（<?php echo $verify_code->get_status_count(4); ?>）</span></a></li>
                        <li class="publish"><a href="<?php echo home_url('/wp-admin/admin.php?page=b2_verify_list&status1=3'); ?>" class="<?php echo $status === '3' ? 'current' : ''; ?>"><?php echo __('黑名单','b2'); ?><span class="count">（<?php echo $verify_code->get_status_count(3); ?>）</span></a></li>
                    </ul>
                    <div id="icon-users" class="icon32"><br/></div>  
                    <form id="coupon-filter" method="get">
                        <?php
                            $verify_code->search_box( __('搜索认证用户（请输入用户ID）','b2'), 'search_id' );
                        ?>
                        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />

                        <?php $verify_code->display() ?>
                    </form>
                <?php
                    }
                ?>
            </div>
        </div>
        <?php
    }
}