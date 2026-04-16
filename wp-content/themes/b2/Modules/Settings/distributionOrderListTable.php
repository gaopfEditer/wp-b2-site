<?php 
namespace B2\Modules\Settings;

use \WP_List_Table;
use B2\Modules\Common\Gold;
use B2\Modules\Common\User;

if( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

//分销订单表
class distributionOrderListTable extends WP_List_Table {

    function __construct() {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'id',
            'ajax' => false  
        ));
    }


function object_to_array($obj) {
    $obj = (array)$obj;
    foreach ($obj as $k => $v) {
        if (gettype($v) == 'resource') {
            return;
        }
        if (gettype($v) == 'object' || gettype($v) == 'array') {
            $obj[$k] = (array)object_to_array($v);
        }
    }
 
    return $obj;
}

    function column_default($item, $column_name) {

        $content = Gold::get_gold_data_map([$this->object_to_array($item)]);

        switch ($column_name) {
            case 'from':
                if(isset($content[0]['from']['link'])){
                    return '<a href="'.$content[0]['from']['link'].'" target="_blank">'.$content[0]['from']['name'].'</a><p>(ID:'.$content[0]['from']['id'].')</p>';
                }else{
                    return __('未记录','b2');
                }
                
            case 'to':
                if(isset($content[0]['to'])){
                    $user_data = User::get_user_normal_data($item->to);
                    
                    return '<a href="'.$user_data['link'].'" target="_blank">'.$user_data['name'].'</a><p>(ID:'.$user_data['id'].')</p>';
                }else{
                    return __('未记录','b2');
                }
            case 'date':
                return $item->date;
            case 'msg':
                if(isset($content[0]['post']['link'])){
                    return '<a href="'.$content[0]['post']['link'].'" target="_blank">'.$content[0]['post']['title'].'</a>';
                }elseif($item->msg){
                    return str_replace('${from}','',$item->msg);
                }else{
                    return __('未记录','b2');
                }
                
            case 'lv':
                if(get_user_meta($item->from,'b2_distribution_related',true) == (int)$item->to){
                    return __('一级伙伴','b2');
                }elseif(get_user_meta($item->from,'b2_distribution_related_lv2',true) == (int)$item->to){
                    return __('二级伙伴','b2');
                }elseif(get_user_meta($item->from,'b2_distribution_related_lv4',true) == (int)$item->to){
                    return __('三级伙伴','b2');
                }else{
                    return __('未记录','b2');
                }
            case 'money':
                return B2_MONEY_SYMBOL.bcdiv($item->no,100,2);
            case 'ratio':
                if(isset($content[0]['value']['ratio'])){
                    return B2_MONEY_SYMBOL.$content[0]['value']['ratio'];
                }else{
                    return __('未记录','b2');
                }
            case 'no':
                if(isset($content[0]['value']['money'])){
                    return B2_MONEY_SYMBOL.$content[0]['value']['money'];
                }else{
                    return __('未记录','b2');
                }
        }
    }
    
    function delete_coupons($ids){
        global $wpdb;
        $table_name = $wpdb->prefix.'b2_gold';
        
        if(is_array($ids)){
            foreach ($ids as $id) {
                $mark = '0+'.$id;

                $wpdb->query(
                    $wpdb->prepare( 
                        "DELETE FROM $table_name WHERE `mark`=%s AND `from` = %d",
                        $mark,$id
                    )
                );

                $wpdb->query(
                    $wpdb->prepare( 
                        "DELETE FROM $table_name WHERE `mark`=%s AND `to` = %d",
                        $mark,$id
                    )
                );
            }
        }
    }

    function column_umeta_id($item){
        $paged = isset($_REQUEST['paged']) ? $_REQUEST['paged'] : 1;
        $status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';

        $actions = array(
            'delete'    => sprintf('<a onclick="return confirm(\'您确定删除该工单吗?\')" href="?page=%s&action=%s&id=%s&paged=%s">'.__('删除','b2').'</a>','b2_distribution_list','delete',$item->umeta_id,$paged),
            'edit'    => sprintf('<a class="green" href="?page=%s&action=%s&id=%s&paged=%s">'.__('编辑','b2').'</a>','b2_distribution_list','edit',$item->umeta_id,$paged)
        );

        return sprintf('%1$s %2$s',
            $item->umeta_id,
            $this->row_actions($actions)
        );
    }

    function column_cb($item){

        return sprintf(
            '<input type="checkbox" name="id[]" value="%1$s" />',
            $item->umeta_id
        );
    }

    function get_columns() {
        return $columns = array(
            'from' => __('消费者','b2'),
            'date'=>__('购买日期','b2'),
            'msg' => __('商品名称','b2'),
            'no' => __('商品总价','b2'),
            'to' => __('收益人','b2'),
            'lv' => __('伙伴层级','b2'),
            'ratio'=>__('收益比','b2'),
            'money'=>__('收益','b2'),
        );
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            'status' => array('status',false)
        );
        return $sortable_columns;
    }

    function display_tablenav( $which ) {

        ?>
        <div class="tablenav <?php echo esc_attr( $which ); ?>">

            <?php if ( $this->has_items() ): ?>
                <div class="alignleft actions bulkactions">
                    <?php $this->bulk_actions( $which ); ?>
                </div>
            <?php endif;
                $this->extra_tablenav( $which );
                $this->pagination( $which );
            ?>

            <br class="clear" />
        </div>
        <?php
    }

    function get_bulk_actions() {
        $actions = array(
            'delete'    => __('删除','b2')
        );
        return $actions;
    }

    function prepare_items($val ='') {

        $this->process_bulk_action();

        global $wpdb; 
        $table_name = $wpdb->prefix . 'b2_gold';

        $totalitems = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM $table_name
            WHERE (`type`=%s OR `type`=%s OR `type`=%s)",
            'post_distribution_lv1','post_distribution_lv2','post_distribution_lv3'
        ));

        $perpage = 20;

        $paged = isset($_GET["paged"]) ? esc_sql($_GET["paged"]) : '';

        if (empty($paged) || !is_numeric($paged) || $paged <= 0) {
            $paged = 1;
        }


        $offset = 0;

        $totalpages = ceil($totalitems / $perpage);

        $this->set_pagination_args(array(
            "total_items" => $totalitems,
            "total_pages" => $totalpages,
            "per_page" => $perpage,
        ));

        
        $query = $wpdb->prepare("SELECT * FROM $table_name
                WHERE (`type`=%s OR `type`=%s OR `type`=%s) ORDER BY id DESC LIMIT %d,%d",
                'post_distribution_lv1','post_distribution_lv2','post_distribution_lv3',$offset,$perpage
            );

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $this->items = $wpdb->get_results($query);
    }
}