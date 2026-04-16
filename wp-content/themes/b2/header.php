<!doctype html>
<html <?php language_attributes(); ?> class="avgrund-ready b2dark">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover" />
	<meta http-equiv="Cache-Control" content="no-transform" />
	<meta http-equiv="Cache-Control" content="no-siteapp" />
	<meta name="renderer" content="webkit"/>
	<meta name="force-rendering" content="webkit"/>
	<meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1"/>
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<meta name="theme-color" content="<?php echo b2_get_option('template_top','gg_bg_color'); ?>">
	<?php wp_head();?>
</head>

<body <?php body_class(b2_get_option('template_top','top_type')); ?>>
	<?php 
		$bg = b2_get_option('template_main','bg_image'); 
		$bg_repeat = b2_get_option('template_main','bg_image_repeat');
		if($bg && $bg_repeat == 2){
			echo '<div class="b2-page-bg">
				<img src="'.b2_get_thumb(array('thumb'=>$bg,'width'=>80,'height'=>'100%')).'" />
			</div>';
		}
	?>

	<div id="page" class="site">
		
		<?php do_action('b2_header'); ?>
		<!-- <div class="topshory-box">
			<div class="wrapper">
				<img class="topshory-bunner" src="http://192.168.1.5:2256/wp-content/uploads/2022/06/v2-a4ff18cc184e45b953e949ffff1f3f8c.jpg" />
			</div>
		</div> -->
	<div id="content" class="site-content">
	
		<?php do_action('b2_content_before'); ?>
