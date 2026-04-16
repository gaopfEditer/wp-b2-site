<?php 
namespace B2\Modules\Settings;

use \WP_List_Table;

if( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

//工单表格
class RequestsTable extends WP_List_Table {

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
                return (int)$item->$column_name === 0 ? '<span class="red">'.__('未回复','b2').'</span>' : '<span class="green">'.__('已回复','b2').'</span>';
            case 'value':
                return esc_attr($item->$column_name);
            case 'email':
                return esc_attr($item->$column_name);
            default :
            return $item->$column_name;
        }
    }
    
    function get_status_count($status){
        global $wpdb;
        $table_name = $wpdb->prefix . 'zrz_directmessage';

        if($status === 'all'){
            $from = "(SELECT * FROM $table_name WHERE `to`=0 GROUP BY `mark`)";
        }else{
            $status = $status === 'replied' ? 1 : 0;
            $from = "(SELECT * FROM $table_name WHERE `status`=$status AND `to`=0 GROUP BY `mark`)";
        }
        
        $query = "SELECT COUNT(*) FROM $from b ";

        $rowcount = $wpdb->get_var($query);
        
        return $rowcount ? $rowcount : 0;
    }

    function delete_coupons($ids){
        global $wpdb;
        $table_name = $wpdb->prefix.'zrz_directmessage';
        
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

    function column_id($item){
        $paged = isset($_REQUEST['paged']) ? $_REQUEST['paged'] : 1;
        $status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';

        $actions = array(
            'delete'    => sprintf('<a onclick="return confirm(\'您确定删除该工单吗?\')" href="?page=%s&action=%s&id=%s&paged=%s">'.__('删除','b2').'</a>','b2_request_list','delete',$item->from,$paged),
            'edit'    => sprintf('<a class="green" href="?page=%s&action=%s&id=%s&paged=%s&status=%s&email=%s">'.__('回复','b2').'</a>','b2_request_list','edit',$item->from,$paged,$status,$item->key)
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
            'id' => __('工单ID','b2'),
            'from' => __('来自','b2'),
            'date' => __('时间','b2'),
            'status' => __('回复状态','b2'),
            'value'=>__('标题','b2'),
            'key'=>__('邮箱','b2')
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

        $from = "(SELECT * FROM $table_name WHERE `to`=0 ORDER BY `id` DESC limit 100000)";

        //状态筛选
        $status = isset($_GET["status"]) ? esc_sql($_GET["status"]) : '';
        if (!empty($status) && $status != 'all') {
            $status = $status === 'replied' ? 1 : 0;
            $from = "(SELECT * FROM $table_name WHERE `status`=$status AND `to`=0 ORDER BY `id` DESC limit 100000)";
        }

        $query = "SELECT * FROM $from b GROUP BY `mark` ORDER BY `id` DESC";

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