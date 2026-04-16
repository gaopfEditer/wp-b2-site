<?php 
namespace B2\Modules\Settings;

use \WP_List_Table;

if( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

//邀请码表格
class CardListTable extends WP_List_Table {

    function __construct() {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'id',
            'ajax' => false  
        ));
    }

    function column_default($item, $column_name) {
        switch ($column_name) {
            case 'card_user':
                if($item->$column_name){
                    return b2_get_userdata($item->$column_name,'link');
                }
            case 'card_status':
                return $item->$column_name == 1 ? '<span class="red">'.__('已使用','b2').'</span>' : '<span class="green">'.__('未使用','b2').'</span>';
            case 'card_rmb':
                return B2_MONEY_SYMBOL.$item->$column_name;
            default :
            return $item->$column_name;
        }
    }
    
    function get_status_count($status){
        global $wpdb;
        $table_name = $wpdb->prefix . 'zrz_card';
        if($status === 'all'){
            $rowcount = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        }else{
            $rowcount = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE card_status = $status");
        }
        
        return $rowcount ? $rowcount : 0;
    }

    function delete_coupons($ids){
        global $wpdb;
        $table_name = $wpdb->prefix . 'zrz_card';

        if(is_array($ids)){
            foreach ($ids as $id) {
                $wpdb->query(
                    $wpdb->prepare( 
                        "DELETE FROM $table_name WHERE id = %d",
                        $id
                    )
                );
            }
        }
    }

    function column_id($item){

        $actions = array(
            'delete'    => sprintf('<a href="?page=%s&action=%s&id=%s'.(isset($_REQUEST['paged']) ? '&paged="%s"' : '').'">'.__('删除','b2').'</a>','b2_card_list','delete',$item->id,isset($_REQUEST['paged']) ? $_REQUEST['paged'] : 1),
        );

        return sprintf('%1$s %2$s',
            $item->id,
            $this->row_actions($actions)
        );
    }

    function column_cb($item){

        return sprintf(
            '<input type="checkbox" name="id[]" value="%1$s" />',
            $item->id
        );
    }

    function get_columns() {
        return $columns = array(
            'cb' => '<input type="checkbox" />',
            'id' => __('ID','b2'),
            'card_key' => __('卡号','b2'),
            'card_value' => __('密码','b2'),
            'card_rmb' => __('面值','b2'),
            'card_status'=>__('状态','b2'),
            'card_user'=>__('使用者','b2')
        );
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            'id' => array('id',false),
            'card_key' => array('card_key',false),
            'card_value' => array('card_value',false),
            'card_rmb' => array('card_rmb',false),
            'card_status'=>array('card_status',false),
            'card_user'=>array('card_user',false),
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

        if(!apply_filters('b2_check_role',0)) return;

        $this->process_bulk_action();

        global $wpdb; 
        $table_name = $wpdb->prefix . 'zrz_card';

        $query = "SELECT * FROM $table_name";

        //搜索
        $s = isset($_GET["s"]) ? esc_sql($_GET["s"]) : '';
        if(!empty($s)){
            $query.= $wpdb->prepare("
                WHERE card_key LIKE %s
                OR card_value = %s
                OR card_user = %d
                ",
                $s, $s, $s
            );
        }

        //状态筛选
        $status = isset($_GET["card_status"]) ? esc_sql($_GET["card_status"]) : '';
        if (!empty($status) && $status != 'all') {
            $query.=" WHERE `card_status` = $status";
        }

        //排序
        $orderby = isset($_GET["orderby"]) ? esc_sql($_GET["orderby"]) : 'id';
        $order = isset($_GET["order"]) ? esc_sql($_GET["order"]) : 'DESC';
        if (!empty($orderby) & !empty($order)) {
            $query.=' ORDER BY ' . $orderby . ' ' . $order;
        }

        $totalitems = $wpdb->query($query);

        $perpage = 20;

        $paged = isset($_GET["paged"]) ? esc_sql($_GET["paged"]) : '';

        if (empty($paged) || !is_numeric($paged) || $paged <= 0) {
            $paged = 1;
        }

        $totalpages = ceil($totalitems / $perpage);

        if (!empty($paged) && !empty($perpage)) {
            $offset = ($paged - 1) * $perpage;
            $query.=' LIMIT ' . (int) $offset . ',' . (int) $perpage;
        }

        $this->set_pagination_args(array(
            "total_items" => $totalitems,
            "total_pages" => $totalpages,
            "per_page" => $perpage,
        ));

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $this->items = $wpdb->get_results($query);
    }
}