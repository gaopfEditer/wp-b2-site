<?php namespace B2\Modules\Settings;
use B2\Modules\Common\Infomation as Info;
use B2\Modules\Common\User;
class infomation{

    public static $default_settings = array(
        'infomation_open'=>1,
    );
    public $name;

    public function init(){
        add_action('cmb2_admin_init',array($this,'infomation_settings'));
        add_filter( 'manage_infomation_posts_columns', array($this,'filter_infomation_columns'));
        add_action( 'manage_infomation_posts_custom_column', array($this,'realestate_column'), 10, 2);
        add_filter( 'manage_edit-infomation_sortable_columns', array($this,'filter_infomation_sortable'));
        add_filter( 'manage_edit-infomation_sortable_columns', array($this,'filter_infomation_sortable'));
        // add_filter( 'manage_edit-infomation_columns', array($this,'replace_title'));

        $this->name = [
            'name'=> b2_get_option('normal_custom','custom_infomation_name'),
            'link'=>b2_get_option('normal_custom','custom_infomation_link'),
            'for'=>b2_get_option('normal_custom','custom_infomation_for'),
            'get'=>b2_get_option('normal_custom','custom_infomation_get')
        ];
        add_filter( 'pre_get_posts', array($this,'infomation_posts_pre_query'),5);

        add_action( 'wp_insert_post', array($this,'save_infomation_data'),10,3 );
        
        
    }

    public function save_infomation_data($post_id,  $post, $update){
        if(get_post_status( $post_id) == 'publish' && get_post_type( $post_id) == 'infomation'){

            $sticky = get_post_meta($post_id,'b2_infomation_sticky',true);
            if($sticky === ''){
                update_post_meta($post_id,'b2_infomation_sticky',0);
            }

            $author_id = get_post_field( 'post_author', $post_id );
            $sticky = (int)get_post_meta($post_id,'b2_infomation_sticky',true);
            
            $payed = get_user_meta($author_id,'b2_infomation_sticky_payed',true);
            $payed = is_array($payed) ? $payed : [];

            if($sticky){
                
                $days = get_post_meta($post_id,'b2_infomation_sticky_days',true);
                $end_date = b2_date_after(current_time('mysql'), $days);

                $s = false;

                if(!empty($payed)){
                    
                    foreach ( $payed as $k => $v) {
                        if(isset($v['post_id']) && $v['post_id'] == $post_id){
                            
                            $payed[$k]['end_date'] = $end_date;
                            $s = true;
                            break;
                        }
                    }
                    
                }
                
                if(!$s){
                    $payed[] = [
                        'end_date' => $end_date,
                        'used' => true,
                        'post_id' => $post_id,
                        'money'=>0,
                        'days' => $days
                    ];
                }


                update_user_meta($author_id,'b2_infomation_sticky_payed',$payed);
            }else{
                if(!empty($payed)){
                    
                    foreach ( $payed as $k => $v) {
                        if(isset($v['post_id']) && $v['post_id'] == $post_id && $v['used'] == true){
                            if($v['money'] == 0){
                                unset($payed[$k]);
                            }else{
                                $payed[$k]['used'] = false;
                            }
                            update_user_meta($author_id,'b2_infomation_sticky_payed',$payed);
                            update_post_meta($post_id,'b2_infomation_sticky',0);
                            break;
                        }


                    }
                    
                }
            }
        }

        return;
    }

    public function infomation_posts_pre_query($wp_query){
        
        // echo '<pre>';
        // var_dump($q);
        // echo '</pre>';
        
            global $pagenow;
            if($pagenow == 'edit.php' && (isset($_REQUEST['post_type']) && $_REQUEST['post_type'] == 'infomation') && !isset($_REQUEST['orderby'])){
              
                $wp_query->set( 'orderby', array('meta_value_num' => 'DESC','date' => 'DESC' ) );
                $wp_query->set( 'meta_query', array(
                    'relation' => 'or',
                    array(
                        'key'     => 'b2_infomation_sticky',
                        'type' => 'NUMERIC'
                    ),
                    // array(
                    //     'key'     => 'b2_infomation_sticky',
                    //     'compare' => 'NOT EXISTS',
                    // ),
                ));
                // $wp_query->set( 'order', 'DESC' ); 
            }
           // var_dump($q);
        
    }

    public function filter_infomation_sortable($sortable){
        $sortable['post_author'] = 'post_author';
        $sortable['b2_infomation_type'] = 'b2_infomation_type';
        $sortable['b2_infomation_price'] = 'b2_infomation_price';
        $sortable['b2_infomation_passtime'] = 'b2_infomation_passtime';
        $sortable['b2_infomation_contact'] = 'b2_infomation_contact';
        $sortable['taxonomy-infomation_cat'] = 'taxonomy-infomation_cat';
        return $sortable;
    }

    public function filter_infomation_columns($columns){
        $new = [];
        $new['b2_infomation_sticky'] = __('置顶','b2');
        $new['post_author'] = __('用户','b2');
        $new['b2_infomation_type'] = __('供求形式','b2');
        $new['b2_infomation_price'] = __('价格','b2');
        $new['b2_infomation_passtime'] = __('帖子有效期','b2');
        $new['b2_infomation_contact'] = __('联系方式','b2');

        array_insert($columns,2,$new);
        unset($columns['rel']);
        
        return $columns;
    }

    public function realestate_column($column, $post_id){

        // var_dump($column);

        if ( 'b2_infomation_sticky' === $column ) {
            $sticky = get_post_meta( $post_id,'b2_infomation_sticky',true);
   
            if($sticky){
                echo __('置顶','b2');
            }else{
                echo '--';
            }
            
            return;
        }

        if ( 'post_author' === $column ) {
            $user_id = get_post_field('post_author',  $post_id);
            $user = get_userdata($user_id);
            if($user_id && $user){
                echo '<a href="'.get_author_posts_url($user_id).'" target="_blank">'.$user->display_name.'</a>';
            }else{
                echo __('未名','b2');
            }
            
            return;
        }

        if('b2_infomation_type' === $column){
            $link = get_post_meta($post_id,'b2_infomation_type',true);
            echo $link == 'for' ? $this->name['for'] : $this->name['get'];
        }

        if('b2_infomation_price' === $column){
            $price = get_post_meta($post_id,'b2_infomation_price',true);
            echo $price ? B2_MONEY_SYMBOL.$price : '—';
        }

        if('b2_infomation_passtime' === $column){
            $days = get_post_meta($post_id,'b2_infomation_passtime',true);
            if(!(int)$days){
                echo __('无限制','b2');
                return;
            }

            $info = new Info();
            $status = $info->get_infomation_status($post_id);
            if($status['status'] == 0){
                echo $status['text'].__('过期','b2');
            }else{
                echo $status['text'];
            }
            
        }

        if('b2_infomation_contact' === $column){
            $contact = get_post_meta($post_id,'b2_infomation_contact',true);
            $arr = explode('|', $contact);

            if(isset($arr[0]) && isset($arr[1])){
                echo $arr[0].'：'.$arr[1];
            }
        }
    }


    public static function get_default_settings($key){
       
        $arr = array(
            'infomation_open'=>1,
            'infomation_name'=>b2_get_option('normal_custom','custom_infomation_name'),
            'infomation_title'=>b2_get_option('normal_custom','custom_infomation_name'),
            'infomation_tdk_desc'=>'',
            'infomation_tdk_keywords'=>'',
            'infomation_desc'=>__('这是一个描述信息','b2'),
            'infomation_per_count'=>20,
            'submit_allow_opts'=>[],
            'po_allow'=>1,
            'po_can_post'=>3,
            'submit_sticky_price'=>20
        );

        if($key == 'all'){
            return $arr;
        }

        if(isset($arr[$key])){
            return $arr[$key];
        }
    }

    public function infomation_settings(){

        $infomation_meta = new_cmb2_box(array( 
            'id'            => 'single_infomation_metabox',
            'title'         => __( '信息属性', 'b2' ),
            'object_types'  => array( 'infomation'),
            'context'       => 'side',
            'priority'      => 'high',
            'show_names'    => true,
        ));

        $infomation_meta->add_field(array(
            'name' => __('是否置顶','b2'),
            'id'   => 'b2_infomation_sticky',
            'type' => 'select',
            'options'=>array(
                0=>__('不置顶','b2'),
                1=>__('置顶','b2')
            ),
            'desc'=> __('置顶后此条信息会显示在列表最前端','b2'),
        ));

        $infomation_meta->add_field(array(
            'name' => __('置顶几天','b2'),
            'id'   => 'b2_infomation_sticky_days',
            'type' => 'text',
            'desc'=> __('留空或者填写0，则一直置顶。否则请直接填写置顶天数','b2'),
        ));

        $infomation_meta->add_field(array(
            'name' => __('形式','b2'),
            'id'   => 'b2_infomation_type',
            'type' => 'select',
            'options'=>array(
                'for'=>$this->name['for'],
                'get'=>$this->name['get']
            )
        ));

        $infomation_meta->add_field(array(
            'name' => __('价格','b2'),
            'id'   => 'b2_infomation_price',
            'type' => 'text_money',
            'sanitization_cb' => 'b2_sanitize_number',
            'desc'=> __('请直接输入价格，没有价格请直接留空','b2'),
        ));

        $infomation_meta->add_field(array(
            'name' => __('信息属性','b2'),
            'id'   => 'b2_infomation_meta',
            'type'             => 'textarea',
            'desc'=>'请按照属性名|属性值的格式设置供求属性，每个属性占一行，不填则不显示此项。比如<br>
            <code>颜色|红色</code><br>
            <code>尺码|30</code><br>
            <code>成色|95新</code>'
        ));

        $infomation_meta->add_field(array(
            'name' => __('帖子有效期','b2'),
            'id'   => 'b2_infomation_passtime',
            'type' => 'text',
            'desc'=> __('请填写过期天数，永不过期，请填写0','b2'),
        ));

        $infomation_meta->add_field(array(
            'name' => __('状态','b2'),
            'id'   => 'b2_infomation_status',
            'type' => 'select',
            'options'=>[
                0=>__('进行中','b2'),
                1=>__('已结束','b2')
            ],
            'desc'=> __('您可以手动设置已过期，否则到期之后也会自动过期','b2'),
        ));

        $infomation_meta->add_field(array(
            'name' => __('联系方式','b2'),
            'id'   => 'b2_infomation_contact',
            'type' => 'text',
            'desc'=> sprintf(__('请按照如下格式填写，比如：%s电话|199043xxxx%sQQ|12345678%s','b2'),'<br><code>','</code><br><code>','</code>'),
        ));

        //常规设置
        $infomation = new_cmb2_box( array(
            'id'           => 'b2_infomation_main_options_page',
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_infomation_main',
            'tab_group'    => 'b2_infomation_options',
            'parent_slug'     => 'b2_main_options',
            'tab_title'    => sprintf(__('%s首页','b2'), $this->name['name']),
            'menu_title'   => sprintf(__('%s设置','b2'), $this->name['name']),
            'save_button'     => __( '保存设置', 'b2' )
        ));

        $infomation->add_field(array(
            'name'    => sprintf(__( '是否启用%s', 'b2' ), $this->name['name']),
            'id'=>'infomation_open',
            'type'    => 'select',
            'options'=>array(
                1=>__('启用','b2'),
                0=>__('关闭','b2')
            )
        ));

        $infomation->add_field(array(
            'name'    => sprintf(__( '%s首页标题', 'b2' ), $this->name['name']),
            'id'=>'infomation_title',
            'type'    => 'text',
            'default' =>__('导航链接','b2'),
            'desc'=>sprintf(__('显示在%s首页的顶部'), $this->name['name'])
        ));

        $infomation->add_field(array(
            'name'    => sprintf(__( '%s首页SEO名称', 'b2' ), $this->name['name']),
            'id'=>'infomation_name',
            'type'=>'text',
            'default'=>self::get_default_settings('infomation_name')
        ));

        $infomation->add_field(array(
            'name'    => sprintf(__( '%s首页SEO描述', 'b2' ), $this->name['name']),
            'id'=>'infomation_tdk_desc',
            'type'=>'textarea_small',
            'default'=>self::get_default_settings('infomation_tdk_desc')
        ));

        $infomation->add_field(array(
            'name'    => sprintf(__( '%s首页SEO标签', 'b2' ), $this->name['name']),
            'id'=>'infomation_tdk_keywords',
            'type'=>'text',
            'default'=>self::get_default_settings('infomation_tdk_keywords')
        ));

        $infomation->add_field(array(
            'name'    => sprintf(__( '%s首页描述信息', 'b2' ), $this->name['name']),
            'id'=>'infomation_desc',
            'type'=>'textarea_code',
            'default'=>self::get_default_settings('infomation_desc'),
            'desc'=>sprintf(__('显示在%s首页标题下面，支持html','b2'),$this->name['name'])
        ));

        $infomation->add_field(array(
            'name'    => sprintf(__( '%s页面每页多少条信息', 'b2' ), $this->name['name']),
            'id'=>'infomation_per_count',
            'type'=>'text',
            'default'=>self::get_default_settings('infomation_per_count'),
            'desc'=>sprintf(__('%s列表每页显示多少条','b2'),$this->name['name'])
        ));

        $infomation_submit = new_cmb2_box( array(
            'id'           => 'b2_infomation_submit_options_page',
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_infomation_submit',
            'tab_group'    => 'b2_infomation_options',
            'parent_slug'     => '/admin.php?page=b2_infomation_main',
            'tab_title'    => __('发布设置','b2'),
            'save_button'     => __( '保存设置', 'b2' )
        ));

        $cats = array();

        $info_cats = get_terms( 'infomation_cat', array(
            'hierarchical' => true,
            'hide_empty' => false,
            'cache_domain'=>'b2_infomation_cat'
        ) );

        foreach( $info_cats as $v ) {
            $cats[$v->term_id] = $v->name;
        };

        //是否允许用户投稿
        $infomation_submit->add_field(array(
            'name'    => __( '是否允许发布', 'b2' ),
            'id'      => 'po_allow',
            'type'    => 'radio_inline',
            'options' => array(
                1 => __( '允许发布', 'b2' ),
                0   => __( '禁止发布', 'b2' ),
            ),
            'default' => self::get_default_settings('po_allow'),
        ));


        $infomation_submit->add_field(array(
            'name'=>sprintf(__('允许发布的%s分类','b2'),$this->name['name']),
            'id'=>'submit_cats',
            'type'    => 'pw_multiselect',
            'options' =>$cats,
        ));

        $infomation_submit->add_field(array(
            'name'=>sprintf(__('限制投稿','b2'),$this->name['name']),
            'id'=>'po_can_post',
            'type'    => 'text',
            'default' =>3,
            'desc'=>__('普通用户多少篇投稿未审核时不允许再次投稿，管理员，编辑不受此数量限制.（此项设置可以防止垃圾投稿）','b2')
        ));

        $infomation_submit->add_field(array(
            'name'=>__('允许用户填写的设置项','b2'),
            'id'=>'submit_allow_opts',
            'type'    => 'multicheck_inline',
            'options' =>[
                'sticky'=>__('置顶','b2'),
                'price'=>__('价格','b2'),
                'passtime'=>__('过期时间','b2'),
                'contact'=>__('联系方式','b2'),
                'attrs'=>__('属性','b2')
            ],
            'default'=>self::get_default_settings('submit_allow_opts')
        ));

        $lvs = User::get_user_roles();

        $setting_lvs = array();
        foreach($lvs as $k => $v){
            $setting_lvs[$k] = $v['name'];
        }

        if(b2_get_option('verify_main','verify_allow')){
            $setting_lvs['verify'] = __('认证用户','b2');
        }

        $infomation_submit->add_field(array(
            'name' => __('哪些等级投稿时直接发布，不用审核','b2'),
            'id'   => 'po_role',
            'type' => 'multicheck_inline',
            'options'=>$setting_lvs,
            'desc'=> __('选择之后，这些等级的用户发布文章之后不用审核','b2')
        ));

        $infomation_submit->add_field(array(
            'name'=>__('置顶价格','b2'),
            'id'=>'submit_sticky_price',
            'type'    => 'text_money',
            'sanitization_cb' => 'b2_sanitize_number',
            'before_field' => B2_MONEY_SYMBOL,
            'after_field' => '/'.__('天','b2'),
            'default'=>self::get_default_settings('submit_sticky_price'),
            'after'=>'<p>'.__('用户如果要自己的帖子置顶，在发布的之前，需要支付对应的金额','b2').'</p>'
        ));

        $infomation_single = new_cmb2_box( array(
            'id'           => 'b2_infomation_single_options_page',
            'object_types' => array( 'options-page' ),
            'option_key'      => 'b2_infomation_single',
            'tab_group'    => 'b2_infomation_options',
            'parent_slug'     => '/admin.php?page=b2_infomation_main',
            'tab_title'    => __('连接内页设置','b2'),
            'save_button'     => __( '保存设置', 'b2' )
        ));

        $infomation_single->add_field(array(
            'name'    => __( '连接内页顶部广告位', 'b2' ),
            'id'=>'link_single_top',
            'type'    => 'textarea',
        ));

        $infomation_single->add_field(array(
            'name'    => __( '连接内页底部广告位', 'b2' ),
            'id'=>'link_single_bottom',
            'type'    => 'textarea',
        ));
    }
}