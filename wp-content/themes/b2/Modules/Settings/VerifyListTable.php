<?php 
namespace B2\Modules\Settings;

use \WP_List_Table;

if( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

//邀请码表格
class VerifyListTable extends WP_List_Table {

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
            case 'date':
                return $item->$column_name;
            case 'user_id':
                if($item->$column_name){
                    return b2_get_userdata($item->$column_name,'link');
                }
                return __('未指定','b2');
            case 'verified':
                return $item->$column_name == 1 ? '<span class="green">'.__('已完成','b2').'</span>' : __('未完成','b2');
            case 'name':
                return $item->$column_name;
            case 'identification':
                return $item->$column_name == 0 ? '' : $item->$column_name;
            case 'card':
                return $item->$column_name ? '<img src="'.$item->$column_name.'" />' : '';
            case 'mp':
                return $item->$column_name == 1 ? '<span class="green">'.__('已关注','b2').'</span>' : __('未关注','b2');
            case 'money':
                return $item->$column_name != 0 ? '<span class="green">'.__('已付款','b2').'('.B2_MONEY_SYMBOL.$item->$column_name.')</span>' : __('未付款','b2');
            case 'title':
                return $item->$column_name;
            case 'status':
                $num = (int)$item->$column_name;
                $text = '';
                if($num === 1){
                    $text = __('未认证','b2');
                }
                if($num === 2){
                    $text = '<span class="green">'.__('已认证','b2').'</span>';
                }
                if($num === 3){
                    $text = '<span class="red">'.__('已拉黑','b2').'</span>';
                }
                if($num === 4){
                    $text = '<span style="color:blue">'.__('待审中','b2').'</span>';
                }
                return $text;
        }
    }
    
    function get_status_count($status){
        global $wpdb;
        $table_name = $wpdb->prefix . 'b2_verify';
        if($status === 'all'){
            $rowcount = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        }else{
            $rowcount = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = $status");
        }
        
        return $rowcount ? $rowcount : 0;
    }

    function delete_coupons($ids){
        global $wpdb;
        $table_name = $wpdb->prefix . 'b2_verify';

        if(is_array($ids)){
            foreach ($ids as $id) {
                $res = $wpdb->get_row(
                    $wpdb->prepare("
                        SELECT * FROM $table_name
                        WHERE id=%d
                        ",
                        $id
                ),ARRAY_A);

                $user_id = $res['user_id'];

                delete_user_meta($user_id,'b2_title');
                wp_cache_delete('b2_user_'.$user_id,'b2_user_data');
                
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
            'delete'    => sprintf('<a href="?page=%s&action=%s&id=%s&status1=%s'.(isset($_REQUEST['paged']) ? '&paged=%s' : '').'">'.__('删除','b2').'</a>','b2_verify_list','delete',$item->id,isset($_REQUEST['status1']) ? $_REQUEST['status1'] : 'all',isset($_REQUEST['paged']) ? $_REQUEST['paged'] : 1),
            'edit'    => sprintf('<a href="?page=%s&action=%s&id=%s&status1=%s'.(isset($_REQUEST['paged']) ? '&paged=%s' : '').'">'.__('编辑','b2').'</a>','b2_verify_list','edit',$item->id,isset($_REQUEST['status1']) ? $_REQUEST['status1'] : 'all',isset($_REQUEST['paged']) ? $_REQUEST['paged'] : 1),
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
            'date' => __('认证日期','b2'),
            'user_id' => __('认证用户','b2'),
            'verified' => __('实名状态','b2'),
            'name' => __('姓名','b2'),
            'identification' => __('身份证号码','b2'),
            'card' => __('身份证照片','b2'),
            'mp'=>__('公众号关注状态','b2'),
            'money'=>__('付款状态','b2'),
            'title'=>__('称号','b2'),
            'status'=>__('认证状态','b2')
        );
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            'id' => array('id',false),
            'date' => array('date',false),
            'user_id' => array('user_id',false),
            'verified' => array('verified',false),
            'name'=>array('name',false),
            'identification'=>array('identification',false),
            'card'=>array('card',false),
            'mp'=>array('mp',false),
            'money'=>array('money',false),
            'title'=>array('title',false),
            'status'=>array('status',false)
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
            'delete'    => __('删除','ziranzhi2'),
        );
        return $actions;
    }

    function prepare_items($val ='') {

        $this->process_bulk_action();

        global $wpdb; 
        $table_name = $wpdb->prefix . 'b2_verify';

        $query = "SELECT * FROM $table_name";

        //搜索
        $s = isset($_GET["s"]) ? esc_sql($_GET["s"]) : '';
        if(!empty($s)){
            $query.= $wpdb->prepare("
                WHERE user_id = %d
                ",
                $s
            );
        }

        //状态筛选
        $status = isset($_GET["status1"]) ? esc_sql($_GET["status1"]) : '';
        if (!empty($status) && $status !== 'all') {
            $query.=$wpdb->prepare("
            WHERE status = %d
            ",
            $status);
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