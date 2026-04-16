<?php
namespace B2\Modules\Settings;

use B2\Modules\Common\User;
use B2\Modules\Common\Message;
class CashOut{

    public function init(){
        add_action('cmb2_admin_init',array($this,'cash_out_settings'));
    }

    public function cash_out_settings(){
        $distribution = new_cmb2_box( array(
            'id'           => 'b2_cash_out_main_options_page',
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_cash_out_main',
            'tab_group'    => 'b2_cash_out_options',
            'parent_slug'     => 'b2_main_options',
            'tab_title'    => __('提现列表','b2'),
            'menu_title'   => __('提现列表','b2'),
            'display_cb'      => array($this,'main_option_page_cb'),
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

    public function main_option_page_cb($cmb_options){
        if(!apply_filters('b2_check_role',0)) return;
        $tabs = $this->cb_options_page_tabs( $cmb_options );
        $order_code = new CashOutListTable();
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

                        if($update){
                            $status = isset($_REQUEST['edit_status']) ? $_REQUEST['edit_status'] : '';
 
                            $res = $wpdb->update(
                                $table_name, 
                                array(
                                    'status'=>$status,
                                )
                                , array('id'=>$id)
                            );

                            b2_settings_error('updated',__('更新成功','b2'));
                            
                        }

                        $res = $wpdb->get_results($wpdb->prepare("
                                SELECT * FROM $table_name
                                WHERE id = %s
                            ",
                            $id
                        ),ARRAY_A);

                        if(empty($res)) {
                            echo __('没有找到此申请','b2').'</div>
                            </div>';
                            return;
                        }
                        $res = $res[0];

                        if($update){
                            if((int)$status === 1){
                                Message::update_data([
                                    'date'=>current_time('mysql'),
                                    'from'=>0,
                                    'to'=>$res['from'],
                                    'post_id'=>0,
                                    'msg'=>sprintf(__('您的提现已操作完成，请注意查收。申请金额 %s；服务费：%s；实付金额：%s','b2'),B2_MONEY_SYMBOL.$res['content'],B2_MONEY_SYMBOL.$res['value'],B2_MONEY_SYMBOL.$res['key']),
                                    'type'=>'user_tx_back',
                                    'type_text'=>__('提现完成','b2')
                                ]);
                            }
                        }
                    ?>
                    <div id="profile-page">
                        <form id="order-edit" method="post">
                            <a href="<?php echo remove_query_arg(array('id','kuaidi','express_number','order_address','order_content','action','order_update','submit-update-order','replay_content'),$ref_url); ?>">返回到提现列表</a>
                            <p>如果是未提现状态，您可以使用用户上传的收款二维码进行付款，然后手动讲状态改为已提现，如果用户未上传二维码，请联系用户上传。</p>
                            <table class="form-table" role="presentation">
                                <tbody>
                                    <tr>
                                        <th scope="row"><label for="blogname">id</label></th>
                                        <td><?php echo $id; ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="blogname"><?php echo __('申请人：','b2'); ?></label></th>
                                        <td>
                                            <?php
                                                $user_data = get_userdata($res['from']);
                                                if($user_data){
                                                    echo '<a href="'.get_author_posts_url($res['from']).'" target="_blank">'.$user_data->display_name.'</a>(ID:'.$res['from'].')';
                                                }else{
                                                    echo __('已删除','b2');
                                                }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="blogname"><?php echo __('申请时间','b2'); ?></label></th>
                                        <td>
                                            <?php echo $res['date']; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="blogname"><?php echo __('提现状态','b2'); ?></label></th>
                                        <td>
                                            <?php
                                                $status = (int)$res['status'];
                                            ?>
                                            <select name="edit_status">
                                                <option value="1" <?php echo $status === 1 ? 'selected="selected"' : ''; ?>><?php echo __('已提现','b2'); ?></option>
                                                <option value="0" <?php echo $status === 0 ? 'selected="selected"' : ''; ?>><?php echo __('未提现','b2'); ?></option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="blogname"><?php echo __('申请金额','b2'); ?></label></th>
                                        <td>
                                            <?php echo B2_MONEY_SYMBOL.$res['content']; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="blogname"><?php echo __('实付金额','b2'); ?></label></th>
                                        <td>
                                            <span class="red"><?php echo B2_MONEY_SYMBOL.$res['key']; ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="blogname"><?php echo __('服务费','b2'); ?></label></th>
                                        <td>
                                            <?php echo B2_MONEY_SYMBOL.$res['value'] ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="blogname"><?php echo __('支付二维码','b2'); ?></label></th>
                                        <td>
                                            <?php
                                                $qrcode = get_user_meta($res['from'],'zrz_qcode',true);

                                                $qrcode_weixin = isset($qrcode['weixin']) ? b2_get_thumb(array(
                                                    'thumb'=>$qrcode['weixin'],
                                                    'type'=>'fill',
                                                    'width'=>120,
                                                    'height'=>'100%'
                                                )) : '';

                                                $qrcode_alipay = isset($qrcode['alipay']) ? b2_get_thumb(array(
                                                    'thumb'=>$qrcode['alipay'],
                                                    'type'=>'fill',
                                                    'width'=>120,
                                                    'height'=>'100%'
                                                )) : '';
                                            ?>
                                            <div style="display:flex">
                                                <div style="margin-right:20px">
                                                    <?php if($qrcode_weixin){ ?>
                                                        <p>微信收款码</p>
                                                        <img src="<?php echo $qrcode_weixin; ?>" />
                                                    <?php } ?>
                                                </div>
                                                <div>
                                                    <?php if($qrcode_alipay){ ?>
                                                        <p>支付宝收款码</p>
                                                        <img src="<?php echo $qrcode_alipay; ?>" />
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <input type="hidden" name="page" value="b2_request_list">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <input type="hidden" name="request_update" value="1">
                            <input type="hidden" name="paged" value="<?php echo isset($_REQUEST['paged']) ? (int)$_REQUEST['paged'] : 0;?>">
                            <p class="submit"><input type="submit" name="submit-update-order" id="submit-cmb" class="button button-primary" value="保存"></p>
                        </form>
                    </div>
                <?php }else{ ?>
                    <div class="filter-row1">
                        <a href="<?php echo remove_query_arg(array('status','s'),$ref_url); ?>" class="<?php echo $status === 'all' ? 'current' : ''; ?>"><?php echo __('所有','b2'); ?><span class="count">（<?php echo $order_code->get_status_count('all'); ?>）</span></a>
                        <a href="<?php echo add_query_arg('status','replied',$ref_url); ?>" class="<?php echo $status === 'replied' ? 'current' : ''; ?>"><?php echo __('已支付','b2'); ?><span class="count">（<?php echo $order_code->get_status_count('replied'); ?>）</span></a>
                        <a href="<?php echo add_query_arg('status','unreplied',$ref_url); ?>" class="<?php echo $status === 'unreplied' ? 'current' : ''; ?>"><?php echo __('未支付','b2'); ?><span class="count">（<?php echo $order_code->get_status_count('unreplied'); ?>）</span></a>
                    </div>
                    <div id="icon-users" class="icon32"><br/></div>  
                    <form id="coupon-filter" method="get">
                        <input type="hidden" name="status" value="<?php echo isset($_REQUEST['status']) ? $_REQUEST['status'] : ''; ?>">
                        <input type="hidden" name="status" value="<?php echo isset($_REQUEST['status']) ? $_REQUEST['status'] : ''; ?>">
                        <?php
                            $order_code->search_box( __('搜索提现用户','b2'), 'search_id' );
                        ?>
                        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />

                        <?php $order_code->display() ?>
                    </form>
                <?php } ?>
            </div>
        </div>
        <?php
    }
}