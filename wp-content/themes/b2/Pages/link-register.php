<?php
use B2\Modules\Templates\Modules\Links;
use B2\Modules\Common\Links as LinkAction;

$open = b2_get_option('links_main','link_open');
if(!$open){
    wp_safe_redirect(B2_HOME_URI.'/404');
    exit;
}

get_header();




$allow_cats = b2_get_option('links_submit','link_submit_cats');
?>

<div class="b2-single-content wrapper links-register" ref="registerLink">
    <div class="content-area wrapper">
		<main id="main" class="site-main">
            <h1><?php echo __('申请入驻','b2'); ?></h1>
            <div class="register-form box b2-radius">
                <div class="register-disabled" v-if="!login" v-cloak>
                    <div class="comment-overlay-login">
                        <p><?php echo __('请登录后再提交申请','b2'); ?></p>
                        <button class="empty" @click="userTools.login(1)"><?php echo __('登录','b2'); ?></button>
                    </div>
                </div>
                <div class="register-disabled" v-else-if="disabled" v-cloak>
                    <div class="comment-overlay-login">
                        <p><?php echo __('您已提交成功，请等待审核通过','b2'); ?></p>
                    </div>
                </div>
                <form @submit.stop.prevent="submit">
                    <div class="from-in">
                        <label class="link-thumb b2-radius" :style="link_image ? 'background-image:url('+link_image+')' : ''">
                            <input type="file" placeholder="<?php echo __('图标','b2'); ?>" accept="image/jpg,image/jpeg,image/png,image/gif" ref="fileInput" @change="imgUpload" :disabled="locked"/>
                            <template v-if="progress != 100">
                                <span><?php echo __('请选择图标','b2'); ?></span>
                            </template>
                        </label>
                        <div class="link-input-box">
                            <label>
                                <input type="text" placeholder="<?php echo __('网站名称','b2'); ?>" v-model="link_name"/>
                            </label>
                            <label><input type="text" placeholder="<?php echo __('网站网址','b2'); ?>" v-model="link_url"/></label>
                        </div>
                    </div>
                    <label class="register-cat">
                        <?php
                        $dropdown_args = array(
                            'hide_empty'       => 0,
                            'hide_if_empty'    => false,
                            'taxonomy'         => 'link_cat',
                            'name'             => 'parent',
                            'orderby'          => 'name',
                            'hierarchical'     => true,
                            'show_option_none' => __( '请选择入驻类别','b2'),
                            'include'=>$allow_cats
                        );

                        $dropdown_args = apply_filters( 'taxonomy_parent_dropdown_args', $dropdown_args, 'link_cat', 'new' );

                        wp_dropdown_categories( $dropdown_args );
                        ?>
                    </label>
                    <div class="site-info">
                        <?php
                            $args = array (
                                'tinymce' => true,
                                'media_buttons'=>false,
                                'default_editor'=>'tinymce',
                                'quicktags'=>false,
                                'dfw'=>false
                            );
                            wp_editor( '', 'link_content_1', $args );
                        ?>
                        <div class="link-desc"><?php echo __('您有专属的网站介绍页面，请尽量详细描述您站点的相关信息！','b2'); ?></div>
                    </div>
                    <button class="button submit" :disabled="locked"><?php echo __('提交','b2'); ?></button>
                </from>
            </div>
        </main>
    </div>
</div>
<?php
get_footer();