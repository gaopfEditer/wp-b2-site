<?php
use B2\Modules\Templates\PostType\Announcement;
get_header();
$paged = get_query_var('paged') ? get_query_var('paged') : 1;
$posts_per_page = get_option( 'posts_per_page' );
?>

	<div id="primary" class="page wrapper">
		<div class="content-area announcement-page">
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

				
                $pagenav = b2_pagenav(array('pages'=>$html['pages'],'paged'=>$paged)); 
                if($pagenav){
                    echo '<div class="b2-pagenav collection-nav post-nav box">'.$pagenav.'</div>';
                }
            
			?>

		</main>
			</div>
	</div>

<?php
get_footer();
