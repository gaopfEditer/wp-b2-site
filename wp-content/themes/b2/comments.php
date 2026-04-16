<?php
if(is_audit_mode()) return '';
if ( post_password_required() ) {
	return;
}
$paged = get_query_var('cpage') ? get_query_var('cpage') : 1;
$post_id = get_the_id();
$allow = get_option('comment_registration');
$user_id = get_current_user_id();
$can_img = b2_get_option('template_comment','comment_use_image');

?>
<div class="comments-box">
<div id="comments" class="comments-area box b2-radius">
	<?php
		$comment_count = get_comments_number();
		$comment_open = comments_open();
	?>
	<div class="comments-title">
		<div class="comment-info">
			<span ref="commentCount" class="comment-count">
				<?php
					printf(__( '%1$s 条回复', 'b2' ),$comment_count);
				?>
			</span>
			<span><b class="comment-auth-mod comment-auth">A</b><i><?php echo __('文章作者','ziranzhi2'); ?></i></span>
			<span><b class="comment-auth-mod comment-mod">M</b><i><?php echo __('管理员','ziranzhi2'); ?></i></span>
		</div>
		<div class="comment-tips" v-show="tips" v-cloak>
			<span v-if="!tips.url"><span v-text="tips.title"></span></span>
			<a :href="tips.url" v-else target="_blank"><span v-text="tips.title"></span></a>
		</div>
	</div><!-- .comments-title -->

	<div id="comment-form" class="comment-form">
		<?php if ( ! $comment_open ) { ?>
			<p class="no-comments"><?php echo __( '评论已经关闭', 'ziranzhi2' ); ?></p>
		<?php }else{ 
			$get_commenter = B2\Modules\Common\Comment::get_commenter();
			$get_commenter = json_encode($get_commenter);
		?>
		<div id="respond" class="respond" ref="respond">
			<?php if($allow){ ?>
				<div :class="b2token ? 'comment-overlay-hidden' : 'comment-overlay'" v-cloak>
					<div class="comment-overlay-login">
						<p><?php echo __('您必须登录或注册以后才能发表评论','b2'); ?></p>
						<button class="empty" @click="showLogin()"><?php echo __('登录','b2'); ?></button>
					</div>
				</div>
			<?php } ?>
			<div class="com-info">
				<img class="com-info-avatar avatar b2-radius" :src="data.avatar">
			</div>
			<div class="com-form" ref="formData" data-commenter='<?php echo $get_commenter; ?>'>
				<div id="com-form-title" :class="['com-form-title',{'b2-show':!data.link}]" v-cloak v-show="!b2token">
					<div>
						<div class="" v-if="data.user_name" v-html="data.name+'<?php echo __('，欢迎您老朋友！','b2'); ?>'"></div>
						<div class="" v-else>
							<span v-if="!data.name" v-html="data.name+'<?php echo __('欢迎您，新朋友，感谢参与互动！','b2'); ?>'"></span>
							<span v-else v-html="data.name+'<?php echo __('，感谢您的参与！','b2'); ?>'"></span>
						</div>
					</div>
					<div>
						<button class="text" @click="show.info = !show.info">{{show.info ? '<?php echo __('确认修改','b2'); ?>' : '<?php echo __('修改资料','b2'); ?>'}}</button>
					</div>
				</div>
				<div class="b2-radius">
					<div :class="['com-form-input',{'b2-show':show.info}]" v-cloak>
						<input id="author" type="text" name="nickname" v-model="data.name" placeholder="<?php echo __('称呼','b2'); ?>" @focus="focus = true" @blur="focus = false" autocomplete="new-password">
						<input id="email" type="text" name="email" v-model="data.user_email" placeholder="<?php echo __('邮箱','b2'); ?>" @focus="focus = true" @blur="focus = false" autocomplete="new-password">
					</div>
					<div class="com-form-textarea" :id="drawing ? 'drawing-box' : ''" ref="_textarea_box">
						<textarea v-show="!drawing" id="textarea" ref="textarea_box" placeholder="<?php echo __('说说你的看法','b2'); ?>" @focus="focus = true;((data.name && data.user_email) || b2token  ? show.info = false : show.info = true)" @blur="focus = false;((data.name && data.user_email) || b2token ? show.info = false : show.info = true)"></textarea>
			
						<?php if($can_img){ ?>
							<div v-show="b2token && canImg">
								<canvas id="sketchpad" v-show="drawing" v-cloak ref="sketchpad"></canvas>

								<div class="drawing-tools" v-show="drawing" v-cloak>
									<div>
										<div class="d-color">
											<button :class="'text d-black '+(sketchpadOpt.color == '#121212' ? 'picked' : '')" @click="color('#121212')"></button>
											<button :class="'text d-red '+(sketchpadOpt.color == '#FF3355' ? 'picked' : '')" @click="color('#FF3355')"></button>
											<button :class="'text d-green '+(sketchpadOpt.color == '#71a257' ? 'picked' : '')" @click="color('#71a257')"></button>
											<button :class="'text d-yellow '+(sketchpadOpt.color == '#ff9900' ? 'picked' : '')" @click="color('#ff9900')"></button>
										</div>
										<div class="d-weight">
											<button :class="'text '+(sketchpadOpt.penSize == '2' ? 'picked' : '')" @click="penSize(2)"><?php echo __('细','b2'); ?></button>
											<button :class="'text '+(sketchpadOpt.penSize == '5' ? 'picked' : '')" @click="penSize(5)"><?php echo __('中','b2'); ?></button>
											<button :class="'text '+(sketchpadOpt.penSize == '10' ? 'picked' : '')" @click="penSize(10)"><?php echo __('粗','b2'); ?></button>
										</div>
									</div>
									<div class="d-replay">
										<button class="text" @click="undo" data-title="<?php echo __('撤销','b2'); ?>"><?php echo b2_get_icon('b2-arrow-go-back-line'); ?></button>
										<button class="text" @click="redo" data-title="<?php echo __('重做','b2'); ?>"><?php echo b2_get_icon('b2-arrow-go-forward-line'); ?></button>
										<button class="text" @click="animate" data-title="<?php echo __('回放','b2'); ?>"><?php echo b2_get_icon('b2-magic-fill'); ?></button>
									</div>
								</div>
								<div class="comment-type" v-cloak>
									<button :class="'text '+(!drawing ? 'picked' : '')" @click="drawing = false" data-title="<?php echo __('文本','b2'); ?>"><?php echo b2_get_icon('b2-font-size-2'); ?></button>
									<button :class="'text '+(drawing ? 'picked' : '')" @click="drawing = true" data-title="<?php echo __('涂鸦','b2'); ?>"><?php echo b2_get_icon('b2-brush-line'); ?></button>
								</div>
							</div>
						<?php } ?>
					</div>
				</div>
				<div class="com-form-button">
					<div class="com-form-button-l" :id="drawing ? 'toolopt' :''">
						<?php if(b2_get_option('template_comment','comment_use_smiles')){
							$smile = B2\Modules\Common\Comment::smilies_reset(true);
							$html = '';
							foreach ($smile as $k => $v) {
								$html .= '<button class="text smily-button" @click="addSmile(\''.$k.'\')">'.$v.'</button>';
							}
						?>
							<span @click.stop="show.smile = !show.smile;show.image = false" v-cloak><i :class="focus || show.smile ? 'b2font b2-emotion-laugh-line' : 'b2font b2-emotion-line'"></i></span>
							<div :class="['comment-smile-box',{'b2-show':show.smile}]" v-cloak @click.stop="">
								<?php echo $html; ?>
							</div>
						<?php } ?>
						<?php if($can_img){ ?>
							<div class="" v-if="data.link && canImg" v-cloak>
								<label class="comment-img-button" @click.stop="show.smile = false" v-cloak>
									<?php echo b2_get_icon('b2-image-fill'); ?>
									<input id="comment-img" type="file" ref="fileInput" accept="image/jpg,image/jpeg,image/png,image/gif" @change="getFile($event)">
								</label>
								<div :class="['comment-image-box',{'b2-show':progress > 0 && show.smile == false}]" v-cloak @click.stop="">
									<div v-if="commentData.imgUrl">
										<img :src="commentData.imgUrl" class="comment-sub-img">
										<div class="comment-sub-img-button">
											<label for="comment-img"><?php echo __('更换','b2'); ?></label>
											<label @click="deleteImage()"><?php echo __('删除','b2'); ?></label>
										</div>
									</div>
									<div v-else="" class="comment-sub-img-msg">
										<span v-text="progress+'%'" v-if="progress < 99"></span>
										<span v-else-if="progress > 99" v-text="'<?php echo __('合法性检查中...','b2') ; ?>'"></span>
									</div>
								</div>
							</div>
						<?php } ?>
					</div>
					<div class="com-form-button-r">
						<button class="text mg-r" @click="resetmove()" :disabled="subLocked || locked"><?php echo __('取消回复','b2'); ?></button>
						<button @click="submit()" :disabled="subLocked || locked" :class="[{'b2-loading':subLocked}]"><?php echo __('提交','b2'); ?></button>
					</div>
				</div>
			</div>
		</div>
		<?php } ?>
	</div>

	<div class="comments-area-content">

		<ol class="comment-list" ref="commentList">
			<?php
				$comments = null;
				if($comment_count){
					if($user_id){
						$include_unapproved = $user_id;
					}else{
						$guest = wp_get_current_commenter();
						$include_unapproved = $guest['comment_author_email'] ? $guest['comment_author_email'] : 'empty';
					}

					//$term_list = wp_get_post_terms($postid, 'labtype', array('fields' => 'slugs'));

					$order = get_option('comment_orde','asc');

					$ids = implode(",",B2\Modules\Common\Comment::get_comment_replies($post_id,get_post_meta($post_id,'b2_comment_sticky',true)));

					$comments = get_comments('post_id='.$post_id.'&order='.$order.'&status=approve&include_unapproved='.$include_unapproved.'&comment__not_in='.$ids);

					$list = wp_list_comments( array(
						'short_ping' => true,
						'callback' => array('B2\Modules\Common\Comment','comment_callback'),
						'end-callback' => array('B2\Modules\Common\Comment','comment_callback_close'),
						'max_depth'=>2,
						'echo'=>false,
						'page'=>$paged
						),
						$comments
					);

					if($paged == 1){
						$list = B2\Modules\Common\Comment::get_sticky_comments(get_the_id()).$list;
					}

					echo $list;
				}elseif($comment_open){
					echo '<div class="none-comment" ref="noneComment">'.__('暂无讨论，说说你的看法吧','b2').'</div>';
				}else{
					echo '<div class="none-comment">'.__('关闭讨论','b2').'</div>';
				}
			?>
		</ol><!-- .comment-list -->
		<?php 
			//the_comments_navigation();
			//某个评论的所在的页数
			//var_dump(get_comment_pages_count());
			//某个用来的链接
			// var_dump(get_comment_link());
			// var_dump(get_comments_link());
		?>
	</div>
	<div class="b2-pagenav comment-nav b2-radius <?php echo get_comment_pages_count() <= 1 ? 'b2-hidden-always' : '' ?>">
		<page-nav ref="commentPageNav" paged="<?php echo $paged; ?>" navtype="comment" pages="<?php echo get_comment_pages_count($comments); ?>" type="<?php echo b2_get_option('template_comment','nav_type'); ?>" :box="selecter" :opt="opt" :api="api" @finish="finish" url="<?php echo get_permalink(); ?>" title="<?php echo get_the_title(); ?>"></page-nav>
	</div>
</div><!-- #comments -->

</div>