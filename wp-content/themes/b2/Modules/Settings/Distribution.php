<?php
namespace B2\Modules\Settings;

use B2\Modules\Common\User;

class Distribution{

    public static $default_settings = array(
       'distribution_open'=>1,
       'distribution_conditions'=>0,
       'distribution_money'=>100,
       'distribution_lv1'=>'0.1',
       'distribution_lv2'=>'0.05',
       'distribution_lv3'=>'0.03',
       'distribution_post'=>0,
       'distribution_shop'=>0,
       'distribution_vip'=>0,
       'distribution_cg'=>0,
       'distribution_verify'=>0
    );

    public function init(){
        add_action('cmb2_admin_init',array($this,'distribution_settings'));
    }

    public static function get_default_settings($key){
        $arr = array(
            'distribution_user_lv'=>array()
        );

        if($key == 'all'){
            return $arr;
        }

        if(isset($arr[$key])){
            return $arr[$key];
        }
    }

    public function distribution_settings(){
        //常规设置
        $distribution = new_cmb2_box( array(
            'id'           => 'b2_distribution_main_options_page',
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_distribution_main',
            'tab_group'    => 'b2_distribution_options',
            'parent_slug'     => 'b2_main_options',
            'tab_title'    => __('推广基本设置','b2'),
            'menu_title'   => __('推广设置','b2'),
            'save_button'     => __( '保存设置', 'b2' )
        ));

        $circle_name = b2_get_option('normal_custom','custom_circle_name');

        $distribution->add_field(array(
            'name'    => __( '是否启用推广功能', 'b2' ),
            'id'=>'distribution_open',
            'type'=>'select',
            'options'=>array(
                1=>__('开启','b2'),
                0=>__('关闭','b2')
            ),
            'default'=>self::$default_settings['distribution_open'],
            'before_row'=>'<p>'.sprintf(__('1、您可以在文章、商品、问答、%s中设置是否允许该商品进行分红，只有这些商品开启了分红功能，推广系统才会进行分红操作','b2'),$circle_name).'</p>
            <p>'.__('2、拥有推广权限的用户会拥有一个推广连接，通过这个推广连接购买后，会按照比例给推广人分红','b2').'</p>
            <p>'.sprintf(__('3、%s充值、打赏等不会触发分红','b2'),B2_MONEY_NAME).'</p>
            <p>'.__('4、自己使用自己的推广连接购买不进行分红','b2').'</p>
            <p>'.__('6、推广关系在第一次使用推广连接的时候就会固化','b2').'</p>
            <p>'.__('7、更多内容请参考：推广系统说明','b2').'</p>'
        ));

        $distribution->add_field(array(
            'name'    => __( '成为推广员的条件', 'b2' ),
            'id'=>'distribution_conditions',
            'type'=>'select',
            'options'=>array(
                0=>__('无条件','b2'),
                1=>__('认证用户','b2'),
                2=>__('某些等级的用户','b2'),
                3=>__('手动设置','b2')
            ),
            'default'=>self::$default_settings['distribution_conditions'],
            'desc'=>sprintf(__('选择无条件，则任何人都拥有推广权限。%s选择认证会员后，所有已认证的会员都拥有推广权限。%s选择某些vip会员或者某些普通会员，请在下面选择vip或者普通会员的等级。%s手动设置则没有自动触发成为推广员的权限，需要管理员在用户列表中编辑用户资料，然后给用户设定推广员的权限','b2'),'<br>','<br>','<br>','<br>')
        ));

        // $distribution->add_field(array(
        //     'name'    => __( '消费满足的金额', 'b2' ),
        //     'id'=>'distribution_money',
        //     'type' => 'text_money',
        //     'sanitization_cb' => 'b2_sanitize_number',
        //     'before_field' => B2_MONEY_SYMBOL,
        //     'default'=>self::$default_settings['distribution_money'],
        //     'desc'=>__('用户消费满足金额以后自动成为推广员','b2')
        // ));

        $lvs = User::get_user_roles();

        $setting_lvs = array();
        foreach($lvs as $k => $v){
            $setting_lvs[$k] = $v['name'];
        }

        $distribution->add_field(array(
            'name' => __('哪些等级可以直接成为推广员？','b2'),
            'id'   => 'distribution_user_lv',
            'type' => 'multicheck_inline',
            'options'=>$setting_lvs,
            'desc'=> __('选择之后，这些用户可以直接成为推广员','b2')
        ));

        $distribution->add_field(array(
            'name'    => __( '一级推广分红比例', 'b2' ),
            'id'=>'distribution_lv1',
            'type' => 'text',
            'default'=>self::$default_settings['distribution_lv1'],
            'desc'=>__('请填写数字，比如0.1就是10%','b2')
        ));

        $distribution->add_field(array(
            'name'    => __( '二级推广分红比例', 'b2' ),
            'id'=>'distribution_lv2',
            'type' => 'text',
            'default'=>self::$default_settings['distribution_lv2'],
            'desc'=>__('填0则不启用二级推广，请填写数字，比如0.05就是5%','b2')
        ));

        $distribution->add_field(array(
            'name'    => __( '三级推广分红比例', 'b2' ),
            'id'=>'distribution_lv3',
            'type' => 'text',
            'default'=>self::$default_settings['distribution_lv3'],
            'desc'=>__('填0则不启用三级推广，请填写数字，比如0.03就是3%','b2')
        ));

        $list = new_cmb2_box(array(
            'id'           => 'b2_distribution_user_main_options_page',
            'tab_title'    => __('推广关系列表','b2'), 
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_distribution_list',
            'parent_slug'     => '/admin.php?page=b2_distribution_main',
            'tab_group'    => 'b2_distribution_options',
            'display_cb'      => array($this,'list_option_page_cb'),
            'save_button'     => false,
        ));

        $list = new_cmb2_box(array(
            'id'           => 'b2_distribution_order_main_options_page',
            'tab_title'    => __('推广订单列表','b2'), 
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_distribution_order_list',
            'parent_slug'     => '/admin.php?page=b2_distribution_main',
            'tab_group'    => 'b2_distribution_options',
            'display_cb'      => array($this,'order_list_option_page_cb'),
            'save_button'     => false,
        ));

        $distribution->add_field(array(
            'name'    => __( '文章内容出售默认是否允许分红？', 'b2' ),
            'id'=>'distribution_post',
            'type' => 'select',
            'options'=>array(
                1=>__('默认允许分红','b2'),
                0=>__('默认禁止分红','b2')
            ),
            'default'=>self::$default_settings['distribution_post'],
            'desc'=>__('文章中可以单独设置是否允许分红，不设置则默认使用此处的设置','b2')
        ));

        $distribution->add_field(array(
            'name'    => __( '商品出售默认是否允许分红？', 'b2' ),
            'id'=>'distribution_shop',
            'type' => 'select',
            'options'=>array(
                1=>__('默认允许分红','b2'),
                0=>__('默认禁止分红','b2')
            ),
            'default'=>self::$default_settings['distribution_shop'],
            'desc'=>__('商品中可以单独设置是否允许分红，不设置则默认使用此处的设置','b2')
        ));

        $distribution->add_field(array(
            'name'    => __( 'VIP购买是否允许分红？', 'b2' ),
            'id'=>'distribution_vip',
            'type' => 'select',
            'options'=>array(
                1=>__('允许分红','b2'),
                0=>__('禁止分红','b2')
            ),
            'default'=>self::$default_settings['distribution_vip']
        ));

        $distribution->add_field(array(
            'name'    => __( '购买积分是否允许分红？', 'b2' ),
            'id'=>'distribution_cg',
            'type' => 'select',
            'options'=>array(
                1=>__('允许分红','b2'),
                0=>__('禁止分红','b2')
            ),
            'default'=>self::$default_settings['distribution_cg']
        ));

        $distribution->add_field(array(
            'name'    => __( '付费认证是否允许分红？', 'b2' ),
            'id'=>'distribution_verify',
            'type' => 'select',
            'options'=>array(
                1=>__('允许分红','b2'),
                0=>__('禁止分红','b2')
            ),
            'default'=>self::$default_settings['distribution_verify']
        ));
    }

    public function order_list_option_page_cb($cmb_options){
        $tabs = $this->cb_options_page_tabs( $cmb_options );
        $card_code = new distributionOrderListTable();
        $card_code->prepare_items();

        if((isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete') || (isset($_REQUEST['action2']) && $_REQUEST['action2'] == 'delete')){
                
            $order_ids = isset($_REQUEST['id']) ? (array)$_REQUEST['id'] : '';
            if($order_ids){
                $card_code->delete_coupons($order_ids);

                $ref_url = wp_get_referer();
                $ref_url = remove_query_arg(array('id', 'action','action2','s'), $ref_url);
                exit(header("Location: ".$ref_url));
            }
        }
        $ref_url = wp_get_referer();
        $ref_url = remove_query_arg(array('id', 'action','action2','s'), $ref_url);
        
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
                <div id="icon-users" class="icon32"><br/></div>  
                <form id="coupon-filter" method="get">
                    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
                    <?php
                        $card_code->search_box( __('搜索用户','b2'), 'search_id' );
                    ?>
                    <?php $card_code->display() ?>
                </form>
            </div>
        </div>
        <?php
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
        $card_code = new distributionUserListTable();
        $card_code->prepare_items();

        if((isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete') || (isset($_REQUEST['action2']) && $_REQUEST['action2'] == 'delete')){
                
            $order_ids = isset($_REQUEST['id']) ? (array)$_REQUEST['id'] : '';
            if($order_ids){
                $card_code->delete_coupons($order_ids);

                $ref_url = wp_get_referer();
                $ref_url = remove_query_arg(array('id', 'action','action2','s'), $ref_url);
                exit(header("Location: ".$ref_url));
            }
        }
        $ref_url = wp_get_referer();
        $ref_url = remove_query_arg(array('id', 'action','action2','s'), $ref_url);
        
        
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
            if(isset($_GET['action']) && $_GET['action'] === 'edit'){

                $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : '';
                global $wpdb; 
                $table_name = $wpdb->prefix . 'usermeta';
    
                $res = $wpdb->get_results($wpdb->prepare("
                SELECT * FROM $table_name WHERE `umeta_id`=%d
                ",
                $id
                ));
    
                $res = $res[0];
                $user_id = $res->user_id;

                if(isset($_REQUEST['distribution_lv1'])){
                    update_user_meta($user_id,'b2_distribution_related',$_REQUEST['distribution_lv1']);
                }

                if(isset($_REQUEST['distribution_lv2'])){
                    update_user_meta($user_id,'b2_distribution_related_lv2',$_REQUEST['distribution_lv2']);
                }

                if(isset($_REQUEST['distribution_lv3'])){
                    update_user_meta($user_id,'b2_distribution_related_lv3',$_REQUEST['distribution_lv3']);
                }

                if(isset($_REQUEST['allow_distribution'])){
                    update_user_meta($user_id,'b2_distribution',$_REQUEST['allow_distribution']);
                }

                ?>
                    <div id="profile-page">
                        <form id="order-edit" method="get">
                            <a href="<?php echo remove_query_arg(array('distribution_lv1','distribution_lv2','distribution_lv3'),$ref_url); ?>"><?php echo __('返回到推广关系列表','b2'); ?></a>
                            <table class="form-table" role="presentation">
                                <tbody>
                                    <tr>
                                        <th scope="row"><label for="blogname">umeta_id</label></th>
                                        <td><?php echo $id; ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="blogname"><?php echo __('用户：','b2'); ?></label></th>
                                        <td>
                                            <?php
                                                $user_data = get_userdata($user_id);
                                                if($user_data){
                                                    echo '<a href="'.get_author_posts_url($user_id).'" target="_blank">'.$user_data->display_name.'</a>(ID:'.$user_id.')';
                                                }else{
                                                    echo __('已删除','b2');
                                                }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="blogname"><?php echo sprintf(__('是否允许 %s 推广','b2'),$user_data->display_name); ?></label></th>
                                        <td>
                                            <?php
                                                $allow = (int)get_user_meta($user_id,'b2_distribution',true);
                                            ?>
                                            <select name="allow_distribution">
                                                <option value="1" <?php echo $allow === 1 ? 'selected="selected"' : ''; ?>><?php echo __('允许','b2'); ?></option>
                                                <option value="0" <?php echo $allow === 0 ? 'selected="selected"' : ''; ?>><?php echo __('不允许','b2'); ?></option>
                                            </select>
                                            <p>请选择是否允许推广</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="blogname"><?php echo __('上一级推广'); ?></label></th>
                                        <td>
                                            <?php
                                                $lv2 = get_user_meta($user_id,'b2_distribution_related',true);
                                                $user_data = get_userdata($lv2);
                                                if($user_data){
                                                    echo '<a href="'.get_author_posts_url($lv2).'" target="_blank">'.$user_data->display_name.'</a>';
                                                }else{
                                                    echo __('已删除','b2');
                                                }
                                            ?>
                                            <p>用户ID：<input type="text" value="<?php echo $lv2; ?>" name="distribution_lv1"/></p>
                                            <p>如需编辑，请直接填写新的用户ID。删除关联，请留空</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="blogname"><?php echo __('上二级推广'); ?></label></th>
                                        <td>
                                            <?php
                                                $lv2 = get_user_meta($user_id,'b2_distribution_related_lv2',true);
                                                $user_data = get_userdata($lv2);
                                                if($user_data){
                                                    echo '<a href="'.get_author_posts_url($lv2).'" target="_blank">'.$user_data->display_name.'</a>';
                                                }else{
                                                    echo __('不存在','b2');
                                                }
                                            ?>
                                            <p>用户ID：<input type="text" value="<?php echo $lv2; ?>" name="distribution_lv2"/></p>
                                            <p>如需编辑，请直接填写新的用户ID。删除关联，请留空</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="blogname"><?php echo __('上三级推广'); ?></label></th>
                                        <td>
                                            <?php
                                                $lv2 = get_user_meta($user_id,'b2_distribution_related_lv3',true);
                                                $user_data = get_userdata($lv2);
                                                if($user_data){
                                                    echo '<a href="'.get_author_posts_url($lv2).'" target="_blank">'.$user_data->display_name.'</a>';
                                                }else{
                                                    echo __('不存在','b2');
                                                }
                                            ?>
                                            <p>用户ID：<input type="text" value="<?php echo $lv2; ?>" name="distribution_lv3"/></p>
                                            <p>如需编辑，请直接填写新的用户ID。删除关联，请留空</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <input type="hidden" name="page" value="b2_distribution_list">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <input type="hidden" name="distribution_update" value="1">
                            <input type="hidden" name="paged" value="<?php echo isset($_GET['paged']) ? (int)$_GET['paged'] : 0;?>">
                            <p class="submit"><input type="submit" name="submit-update-order" id="submit-cmb" class="button button-primary" value="保存"></p>
                        </form>
                    </div>
                <?php
                    }else{
                ?>
                    <div id="icon-users" class="icon32"><br/></div>  
                    <form id="coupon-filter" method="get">
                        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
                        <?php
                            $card_code->search_box( __('搜索用户','b2'), 'search_id' );
                        ?>
                        <?php $card_code->display() ?>
                    </form>
                    <?php } ?>
                </div>
            </div>
            <?php
        
    }
}