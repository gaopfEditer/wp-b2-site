<?php
get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main wrapper">

			<section class="error-404 not-found box pos-r" style="min-height:500px">
				<div class="page-content">
					<img  src="<?php echo B2_THEME_URI.'/Assets/fontend/images/404.png'; ?>">
					<div>
						<h1><?php echo __('未找到页面 - 404','b2'); ?></h1>
						<p ><?php echo __('右上角有个搜索按钮，你可以试试','b2'); ?></p>
						<p><?php echo __('或者随便点击上面的链接吧，总会有你需要的','b2'); ?></p>
					</div>
				</div><!-- .page-content -->
			</section><!-- .error-404 -->

		</main><!-- #main -->
	</div><!-- #primary -->

<?php
get_footer();
