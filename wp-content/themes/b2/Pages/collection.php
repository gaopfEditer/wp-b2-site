<?php
use B2\Modules\Templates\Collection;
use B2\Modules\Common\Post;
/**
 * 专题聚合页面
 */
get_header();

$paged = get_query_var('paged');
$paged = $paged ? $paged : 1;

$data = Collection::get_collection_list(array('paged'=>$paged));

$_pages = $data['pages'];

$data = $data['data'];

$img = b2_get_option('template_collection','collection_image');

$name = b2_get_option('template_collection','collection_title');


$desc = b2_get_option('template_collection','collection_desc');

$order = b2_get_option('template_collection','collection_post_order');
$order = !empty($order) ? $order : 'asc';

?>
<?php if(b2_get_option('template_collection','collection_open_cover')) { ?>
    <div class="collection-top mg-b" style="background-image:url(<?php echo $img; ?>)">
        <h1><?php echo $name; ?></h1>
        <p><?php echo $desc; ?></p>
    </div>
<?php }else{ ?>
    <div class="pianli">
        <h1><?php echo $name; ?></h1>
        <p><?php echo $desc; ?></p>
    </div>
<?php } ?>
<div id="primary-home" class="wrapper">
    <main class="site-main">
        <div class="collection-box <?php echo empty($data) ? 'box' : ''; ?>">
            <?php 
                if(!empty($data)){
                foreach ($data as $k => $v) {
                $post_data = $v['posts']['data'];
                $q = get_term_meta($v['id'],'b2_tax_index',true);
                $collection_name = b2_get_option('normal_custom','custom_collection_name');
            ?>
                <div class="collection-item">
                    <div class="box b2-radius">
                        <?php if($q) { ?>
                            <div class="collection-number ar">
                                <span><?php echo sprintf(__('%s：第%s%s%s期','b2'),$collection_name,'<b>',$q,'</b>'); ?></span>
                            </div>
                        <?php } ?>
                        <div class="collection-title">
                            <div class="collection-thumb">
                                <a href="<?php echo $v['link']; ?>" target="_blank">
                                    <?php echo b2_get_img(array(
                                        'src'=>$v['thumb'],
                                        'alt'=>$v['name']
                                    ));?>
                                </a>
                            </div>
                            <div class="collection-info b2-mg">
                                <h2><a href="<?php echo $v['link']; ?>" target="_blank"><?php echo $v['name']; ?></a></h2>
                                <div class="collection-count">
                                    <?php
                                       $latest = false; 
                                       if( !empty($post_data) ) {
                                           $latest = ( $order == 'asc') ? end($post_data) : $post_data[0];
                                       }
                                       echo $latest ? Post::time_ago($latest['date']).__('更新','b2').' · ' : '';
                                       echo $v['posts']['count'].__('篇文章','b2');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <ul class="collection-posts">
                            <?php 
                                if(!empty($post_data)){
                                    foreach ($post_data as $key => $value) {
                                        
                                        ?>
                                        <li><span><a href="<?php echo $value['cat']['link'];?>" target="_blank"><?php echo $value['cat']['name'];?></a></span><a href="<?php echo $value['href']; ?>" class="post-link" terget="_blank"><?php echo $value['title'] ;?></a></li>
                                        <?php
                                    }
                                }
                            ?>
                        </ul>
                    </div>
                </div>
            <?php }
                }else{
                    echo B2_EMPTY;
                }
            ?>
        </div>
        <?php 
            $pagenav = b2_pagenav(array('pages'=>$_pages,'paged'=>$paged)); 
            if($pagenav && !empty($data)){
                echo '<div class="b2-pagenav collection-nav post-nav box b2-radius mg-t">'.$pagenav.'</div>';
            }
        ?>
    </main>
</div>
<?php
get_footer();