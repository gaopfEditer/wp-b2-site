<?php
use B2\Modules\Templates\PostType\Announcement;
/**
 * 公告页面
 */
get_header();
$paged = get_query_var('paged') ? get_query_var('paged') : 1;
$posts_per_page = get_option( 'posts_per_page' );
?>
<div class="page">
	<div id="primary" class="content-area announcement-page wrapper">
		<main id="main" class="site-main">

			<?php 
				$arg = array(
					'post_type'=>'announcement',
					'posts_per_page'=>get_option( 'posts_per_page' ),
					'offset'=>($paged-1)*$posts_per_page,
					'paged'=>$paged
				);

				$html = Announcement::get_announcements($arg);

				if($html['html']){
					echo $html['html'];
				}else{
					get_template_part( 'TempParts/content', 'none' );
				}
			?>

		</main>
	</div>
</div>
<?php
get_footer();
