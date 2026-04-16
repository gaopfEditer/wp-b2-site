<?php 
namespace B2\Modules\Settings;

use \WP_List_Table;

if( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

//邀请码表格
class distributionUserListTable extends WP_List_Table {

    function __construct() {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'id',
            'ajax' => false  
        ));
    }

    function column_default($item, $column_name) {
        switch ($column_name) {
            case 'umeta_id':
                return $item->$column_name;
            case 'user_id':
            case 'meta_value':
                $user_data = get_userdata($item->$column_name);
                if($user_data){
                    return '<a href="'.get_author_posts_url($item->$column_name).'" target="_blank">'.$user_data->display_name.'</a><p>(ID:'.$item->$column_name.')</p>';
                }else{
                    return __('已删除','b2');
                }
            case 'lv2':
                $user_id = get_user_meta($item->user_id,'b2_distribution_related_lv2',true);
                if(!$user_id) return 'none';
                $user_data = get_userdata($user_id);
                if($user_data){
                    return '<a href="'.get_author_posts_url($user_id).'" target="_blank">'.$user_data->display_name.'</a><p>(ID:'.$user_id.')</p>';
                }else{
                    return __('已删除','b2');
                }
            case 'lv3':
                $user_id = get_user_meta($item->user_id,'b2_distribution_related_lv3',true);
                if(!$user_id) return 'none';
                $user_data = get_userdata($user_id);
                if($user_data){
                    return '<a href="'.get_author_posts_url($user_id).'" target="_blank">'.$user_data->display_name.'</a><p>(ID:'.$user_id.')</p>';
                }else{
                    return __('已删除','b2');
                }
            default :
            return $item->$column_name;
        }
    }
    
    function delete_coupons($ids){
        global $wpdb;
        $table_name = $wpdb->prefix.'usermeta';
        
        if(is_array($ids)){
            foreach ($ids as $k => $v) {

                $res = $wpdb->get_results($wpdb->prepare("
                SELECT * FROM $table_name WHERE `umeta_id`=%s
                ",
                $v
                ));

                delete_user_meta($res[0]->user_id,'b2_distribution_related');
                delete_user_meta($res[0]->user_id,'b2_distribution_related_lv2');
                delete_user_meta($res[0]->user_id,'b2_distribution_related_lv3');
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
            'umeta_id' => __('umeta_id','b2'),
            'user_id' => __('用户','b2'),
            'meta_value' => __('上一级推广','b2'),
            'lv2' => __('上二级推广','b2'),
            'lv3'=>__('上三级推广','b2')
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
        $table_name = $wpdb->prefix . 'usermeta';

        $query = $wpdb->prepare("
        SELECT * FROM $table_name WHERE `meta_key`=%s OR `meta_key`=%s OR `meta_key`=%s ORDER BY `umeta_id` DESC
        ",
        'b2_distribution_related','b2_distribution_related_lv2','b2_distribution_related_lv3'
        );

        //搜索
        $s = isset($_REQUEST["s"]) ? esc_sql($_REQUEST["s"]) : '';
      
        if(!empty($s)){

            $users = new \WP_User_Query( array(
                'search'         => '*'.$s.'*',
                'search_columns' => array(
                    'display_name',
                ),
                'number' => 15,
                'paged' => 1
            ) );
    
            $users_found = $users->get_results();
    
           
            $ids = array();
    
            foreach ($users_found as $user) {
    
                $ids[] = $user->ID;
           
            }
     
            
            $a = implode("','",$ids);

            $query = $wpdb->prepare("
            SELECT * FROM $table_name WHERE `user_id` IN ('".$a."') AND `meta_key`=%s ORDER BY `umeta_id` DESC
            ",
            'b2_distribution'
            );
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