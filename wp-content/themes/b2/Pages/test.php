<?php
use B2\Modules\Common\Post;
use B2\Modules\Templates\Modules\Posts;

use B2\Modules\Common\Circle;
use B2\Modules\Common\PostRelationships;
use B2\Modules\Common\User;
use B2\Modules\Common\Login;
use B2\Modules\Common\CircleRelate;
use B2\Modules\Templates\VueTemplates;
use B2\Modules\Common\Infomation as Info;
use B2\Modules\Common\IntCode;
use B2\Modules\Common\Orders;
use B2\Modules\Common\Ask;
// use B2\Modules\Common\Infomation;


if(!current_user_can('administrator')) wp_die('您无权访问此页');


//1、先执行下面，的删除帖子数据：
// global $wpdb; 
// $table_name = $wpdb->prefix . 'posts';

// //删除某个文章ID之后的所有文章，123 是要某个文章ID
// $wpdb->query($wpdb->prepare("DELETE FROM $table_name WHERE post_type = %s AND post_id > %d", 'post',123));

// //删除完毕之后重建自增，被批量删除后，再新增数据，ID不连续，请使用下面的方法进行重建自增，比如你的ID最后一个是1000，则下面的3964改成1000，然后去执行
// $wpdb->query($wpdb->prepare("alter table $table_name auto_increment = %d;", 3964));

// //2、执行下面的删除多余的postmeta
// $meta_table_name = $wpdb->prefix . 'postmeta';
// $wpdb->query($wpdb->prepare("DELETE FROM $meta_table_name WHERE post_id > %d", 3964));

// //被批量删除后，再新增数据，ID不连续，请使用下面的方法进行重建自增，比如你的ID最后一个是1000，则下面的123改成1000，然后去执行
// $wpdb->query($wpdb->prepare("alter table $meta_table_name auto_increment = %d;", 14689));
