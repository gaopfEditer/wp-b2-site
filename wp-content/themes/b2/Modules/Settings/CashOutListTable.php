<?php 
namespace B2\Modules\Settings;

use \WP_List_Table;

if( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

//提现表单
class CashOutListTable extends WP_List_Table {

    function __construct() {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'id',
            'ajax' => false  
        ));
    }

    function column_default($item, $column_name) {
        switch ($column_name) {
            case 'id':
                return $item->$column_name;
            case 'from':
                $user_data = get_userdata($item->$column_name);
                if($user_data){
                    return '<a href="'.get_author_posts_url($item->$column_name).'" target="_blank">'.$user_data->display_name.'</a>';
                }else{
                    return __('已删除','b2');
                }
            case 'date':
                return $item->$column_name;
            case 'status':
                return (int)$item->$column_name === 0 ? '<span class="red">'.__('未支付','b2').'</span>' : '<span class="green">'.__('已支付','b2').'</span>';
            case 'content':
                return B2_MONEY_SYMBOL.$item->$column_name;
            case 'key':
                return '<span class="green">'.B2_MONEY_SYMBOL.$item->$column_name.'</span>';
            case 'value':
                return B2_MONEY_SYMBOL.$item->$column_name;
            default :
            return $item->$column_name;
        }
    }
    
    function get_status_count($status){
        global $wpdb;
        $table_name = $wpdb->prefix . 'zrz_directmessage';

        if($status === 'all'){
            $query = "SELECT COUNT(*) FROM $table_name WHERE `to`=-1 ";
        }else{
            $status = $status === 'replied' ? 1 : 0;
            $query = "SELECT COUNT(*) FROM $table_name WHERE `status`=$status AND `to`=-1";
        }

        $rowcount = $wpdb->get_var($query);
        
        return $rowcount ? $rowcount : 0;
    }

    function delete_coupons($ids){
        global $wpdb;
        $table_name = $wpdb->prefix.'zrz_directmessage';

        if(is_array($ids)){
            foreach ($ids as $id) {
                $wpdb->query(
                    $wpdb->prepare( 
                        "DELETE FROM $table_name WHERE `id`=%s",
                        $id
                    )
                );
            }
        }
    }

    function column_id($item){
        $paged = isset($_REQUEST['paged']) ? $_REQUEST['paged'] : 1;
        $status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';

        $actions = array(
            'delete'    => sprintf('<a onclick="return confirm(\'您确定删除该提现请求吗?\')" href="?page=%s&action=%s&id=%s&paged=%s">'.__('删除','b2').'</a>','b2_cash_out_main','delete',$item->id,$paged),
            'edit'    => sprintf('<a class="green" href="?page=%s&action=%s&id=%s&paged=%s&status=%s">'.__('操作','b2').'</a>','b2_cash_out_main','edit',$item->id,$paged,$status)
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
            'id' => __('提现ID','b2'),
            'from' => __('提现人','b2'),
            'date' => __('时间','b2'),
            'status' => __('提现状态','b2'),
            'content' => __('申请金额','b2'),
            'key'=>__('实付金额','b2'),
            'value'=>__('手续费','b2')
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
        $table_name = $wpdb->prefix . 'zrz_directmessage';

        $query = "SELECT * FROM $table_name WHERE `to`='-1' ORDER BY `id` DESC";

        //状态筛选
        $status = isset($_GET["status"]) ? esc_sql($_GET["status"]) : '';
        if (!empty($status) && $status != 'all') {
            $status = $status === 'replied' ? 1 : 0;
            $query = "SELECT * FROM $table_name WHERE `to`='-1' AND `status`=$status ORDER BY `id` DESC";
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