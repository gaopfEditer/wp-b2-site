<?php 
namespace B2\Modules\Settings;

use \WP_List_Table;
use B2\Modules\Common\User;

if( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

//邀请码表格
class CouponListTable extends WP_List_Table {

    function __construct() {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'id',
            'ajax' => false  
        ));
    }

    function column_default($item, $column_name) {
        switch ($column_name) {
            case 'money':
                return B2_MONEY_SYMBOL.$item->$column_name;
            case 'receive_date':
                if($item->$column_name === '0'){
                    return __('无限制','b2');
                }else{
                    return '<p>'.$item->$column_name.'</p><p>'.($item->$column_name > current_time('mysql') ? '<span class="green">'.__('有效','b2').'</span>' : '<span class="red">'.__('过期','b2')).'</span></p>';
                }
            case 'expiration_date':
                if($item->$column_name === '0'){
                    return __('无限制','b2');
                }else{
                    return $item->$column_name.__('天','b2');
                }
            case 'roles':
                $lvs = maybe_unserialize($item->$column_name);
                $str = '';
                if(!empty($lvs)){
                    foreach ($lvs as $k => $v) {
                        $str .= User::get_lv_icon($v).' / ';
                    }
                }
                return $str ? rtrim ( $str ,  " / " ) : __('无限制','b2');
            case 'cats':
                $cats = maybe_unserialize($item->$column_name);
                $str = '';
                if(!empty($cats)){
                    foreach ($cats as $k => $v) {
                        $term = get_term_by( 'slug', $v, 'shoptype');
                        if(isset($term->term_id)){
                            $str .= '<a href="'.get_term_link($term->term_id).'">'.$term->name.'</a> / ';
                        }
                    }
                }
                return $str ? rtrim ( $str ,  " / " ) : __('无限制','b2');
            case 'products':
                $posts = maybe_unserialize($item->$column_name);
                $str = '';
                if(!empty($posts)){
                    foreach ($posts as $k => $v) {
                        $str .= '<a href="'.get_permalink($v).'">'.get_the_title($v).'</a> / '; 
                    }
                }
                return $str ? rtrim ( $str ,  " / " ) : __('无限制','b2');
            default :
            return $item->$column_name;
        }
    }
    
    function get_status_count($status){
        global $wpdb;
        $table_name = $wpdb->prefix . 'b2_coupon';
        if($status === 'all'){
            $rowcount = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        }elseif($status === 'receive'){
            $rowcount = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE `receive_date` > ".current_time('mysql'));
        }elseif($status === 'expiration'){
            $rowcount = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE `receive_date` > ".current_time('mysql'));
        }
        
        return $rowcount ? $rowcount : 0;
    }

    function delete_coupons($ids){
        global $wpdb;
        $table_name = $wpdb->prefix.'b2_coupon';

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
            'delete'    => sprintf('<a href="?page=%s&action=%s&id=%s'.(isset($_REQUEST['paged']) ? '&paged="%s"' : '').'">'.__('删除','b2').'</a>','b2_coupon_list','delete',$item->id,isset($_REQUEST['paged']) ? $_REQUEST['paged'] : 1),
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
            'id' => __('优惠劵ID','b2'),
            'money' => __('面值','b2'),
            'receive_date' => __('领取过期时间','b2'),
            'expiration_date' => __('使用过期时间','b2'),
            'roles'=>__('允许领取用户组','b2'),
            'cats'=>__('允许使用的商品分类','b2'),
            'products'=>__('允许使用的商品','b2'),
        );
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            'id' => array('id',false),
            'money' => array('card_key',false),
            'receive_date' => array('card_value',false),
            'expiration_date' => array('card_rmb',false),
            'roles'=>array('card_status',false),
            'cats'=>array('card_user',false),
            'products'=>array('products',false)
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
        $table_name = $wpdb->prefix . 'b2_coupon';

        $query = "SELECT * FROM $table_name";

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