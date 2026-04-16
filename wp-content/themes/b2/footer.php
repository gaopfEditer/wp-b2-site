
	</div>
	<style>
		.site-footer{
			background-color:<?php echo b2_get_option('template_footer','footer_color'); ?>
		}
		.site-footer-nav{
			background-color:<?php echo b2_get_option('template_footer','footer_nav_color'); ?>
		}
		#bigTriangleColor{
			background-color:<?php echo b2_get_option('template_footer','footer_color'); ?>
		}
	</style>
	<!-- <svg id="bigTriangleColor" width="100%" height="40" viewBox="0 0 100 102" preserveAspectRatio="none"><path d="M0 0 L50 100 L100 0 Z"></path></svg> -->
	<footer id="colophon" class="footer">
		<?php do_action('b2_footer_before'); ?>
		<?php if(is_active_sidebar( 'sidebar-2' )){ ?>
			<div class="site-footer">
				<div class="wrapper">
					<div class="site-footer-widget-in">
						<?php dynamic_sidebar( 'sidebar-2' ); ?>
					</div>
				</div>
				<?php do_action('footer-column-1'); ?>
			</div>
		<?php } ?>
		<div class="site-footer-nav">
			<div class="wrapper">
				
				<?php 
					$link_cats = b2_get_option('template_footer','link_cat');
					$beian = b2_get_option('template_footer','footer_beian');
					$gongan = b2_get_option('template_footer','footer_gongan');
					$gongan_code = (int) filter_var($gongan, FILTER_SANITIZE_NUMBER_INT);
					$mobile_show_link =  b2_get_option('template_footer','footer_mobile_show_links');
					$ids = array();
					$bookmarks = array();
					if($link_cats){
						foreach($link_cats as $v){
							$links = get_term_by('slug', $v, 'link_category');
							if($links){
								$ids[] = $links->term_id;
							}
						}
					}
					$ids = implode(",", $ids);
					if($ids){
						$bookmarks = get_bookmarks(array(
							'category'=>$ids,
							'orderby'=>'link_rating',
							'order'=>'DESC'
						));
					}
				?>
				<?php if((is_home() || is_front_page()) && !empty($link_cats) && !empty($bookmarks)){ ?>
					<div class="footer-links <?php echo (int)$mobile_show_link === 0 ? 'mobile-hidden' : ''; ?>">
						<?php
							echo '<ul>';
								foreach ($bookmarks as $bookmark) {
									echo '<li><a target="_blank" href="' . $bookmark->link_url . '">' . $bookmark->link_name . '</a></li>';
								}
							echo '</ul>';
							
						?>
					</div>
				<?php } ?>
				<div class="footer-bottom">
					<div class="footer-bottom-left">
						<div class="copyright"><?php echo 'Copyright &copy; '.wp_date('Y').'<a href="'.B2_HOME_URI.'" rel="home">&nbsp;'.B2_BLOG_NAME.'</a>'; ?></div>
						<div class="beian">
							<?php if($beian){
								echo '<span class="b2-dot">・</span><a rel="nofollow" target="__blank" href="https://beian.miit.gov.cn">'.$beian.'</a>';
							} ?>
							<?php if($gongan){
								echo '<span class="b2-dot">・</span><a rel="nofollow" target="__blank" href="http://www.beian.gov.cn/portal/registerSystemInfo?recordcode='.$gongan_code.'"><img src="'.B2_THEME_URI.'/Assets/fontend/images/beian-ico.png">'.b2_get_option('template_footer','footer_gongan').'</a>';
							}?>
						</div>
					</div>
					<div class="footer-bottom-right">
						<?php
							echo sprintf(__('查询 %s 次，','b2'),get_num_queries());
							echo sprintf(__('耗时 %s 秒','b2'),timer_stop(0,4));
						?>
					</div>
				</div>
			
			</div>
			<?php do_action('footer-column-2'); ?>
		</div>
	</footer>
	<?php if(!is_audit_mode()) { ?>
	<div id="mobile-footer-menu" class="mobile-footer-menu mobile-show footer-fixed" ref="footerMenu" v-show="show">
		<?php 
		if(apply_filters('b2_is_page', 'links')){
            echo '<div class="link-join-button mobile-show"><a href="'.b2_get_custom_page_url('link-register').'" target="_blank">'.__('申请入驻','b2').'</a></div>';
        }
		?>
		<div class="mobile-footer-left">
			<?php echo B2\Modules\Templates\Footer::footer_menu_left(); ?>
		</div>
		<div class="mobile-footer-center">
			<button @click="postPoBox.show = true"><span><?php echo b2_get_icon('b2-add-line b2-radius'); ?></span></button>
		</div>
		<div class="mobile-footer-right">
			<?php echo B2\Modules\Templates\Footer::footer_menu_right(); ?>
		</div>
	</div>
	<?php } ?>
	<?php 
		$allow_newsflashes = b2_get_option('newsflashes_main','newsflashes_open');
		$allow_document = b2_get_option('document_main','document_open');
		$allow_circle = b2_get_option('circle_main','circle_open');

		$circle_sulg = b2_get_option('normal_custom','custom_circle_link');
		$circle_name = b2_get_option('normal_custom','custom_circle_name');

		$newsflashes_name = b2_get_option('normal_custom','custom_newsflashes_name');

	?>
	<div id="post-po-box" class="post-po-box">
		<div :class="['post-box-content',{'show':show}]" @click="show = false">
			<div class="po-post-in b2-radius" v-cloak>
				<div class="po-post-icons">
					<div>
						<button @click.stop="go('<?php echo b2_get_custom_page_url('write'); ?>','write')">
							<span class="po-post-icon"><?php echo b2_get_icon('b2-quill-pen-line'); ?></span>
							<span class="po-post-title"><?php echo __('发布文章','b2'); ?></span>
						</button>
					</div>
					<?php if($allow_newsflashes){ ?>
						<div>
							<button @click.stop="go('<?php echo get_post_type_archive_link('newsflashes'); ?>?action=showbox','newsflashes')">
								<span class="po-post-icon"><?php echo b2_get_icon('b2-flashlight-line'); ?></span>
								<span class="po-post-title"><?php echo sprintf(__('发布%s','b2'),$newsflashes_name); ?></span>
							</button>
						</div>
					<?php } ?>
					<?php if($allow_circle){ ?>
						<div>
							<button @click.stop="go('<?php echo b2_get_custom_page_url('create-circle'); ?>','create_circle')">
								<span class="po-post-icon"><?php echo b2_get_icon('b2-donut-chart-fill'); ?></span>
								<span class="po-post-title"><?php echo sprintf(__('创建%s','b2'),$circle_name); ?></span>
							</button>
						</div>
						<div>
							<button @click.stop="go('<?php echo B2_HOME_URI.'/'.$circle_sulg; ?>','create_topic')">
								<span class="po-post-icon"><?php echo b2_get_icon('b2-chat-smile-3-line'); ?></span>
								<span class="po-post-title"><?php echo __('发表话题','b2'); ?></span>
							</button>
						</div>
					<?php } ?>
					<?php if($allow_document){ ?>
						<div class="po-verify">
							<button @click.stop="go('<?php echo b2_get_custom_page_url('requests'); ?>','request')">
								<?php echo b2_get_icon('b2-clipboard-line'); ?><span><?php echo __('提交工单','b2'); ?></span>
							</button>
						</div>
					<?php } ?>
				</div>
				<div class="po-close-button">
					<button @click.stop="show = false"><?php echo b2_get_icon('b2-close-line'); ?></button>
				</div>
			</div>
		</div>
    </div>
	<form id="wechataction" name="wechataction" action="" method="post">
    	<input type="submit" value="ok" style="display:none;">
	</form>
</div>

<?php wp_footer(); ?>
<!-- <script>

</script> -->
</body>
</html>