<?php
use B2\Modules\Common\Post;
use B2\Modules\Templates\VueTemplates;
use B2\Modules\Templates\Footer;
?>
<!doctype html>
<html <?php language_attributes(); ?> class="avgrund-ready">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<meta http-equiv="Cache-Control" content="no-transform" />
	<meta http-equiv="Cache-Control" content="no-siteapp" />
	<meta name="renderer" content="webkit"/>
	<meta name="force-rendering" content="webkit"/>
	<meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1"/>
	<link rel="profile" href="http://gmpg.org/xfn/11">
    
	<?php wp_head();
	?>

</head>
<?php
    $post_id = isset($_GET['post_id']) && $_GET['post_id'] ? (int)$_GET['post_id'] : 0;
    $index = isset($_GET['index']) && $_GET['index'] ? (int)$_GET['index'] : 0;
    $i = isset($_GET['i']) && $_GET['i'] ? (int)$_GET['i'] : 0;

    $download_data = Post::get_post_download_data($post_id);
    if(!isset($download_data[$index])){
        wp_die('没有找到这个资源','b2');
    }

    $download_data = $download_data[$index];

    $top = b2_get_option('template_download','download_ads_top');
    $middle = b2_get_option('template_download','download_ads_middle');
    $bottom = b2_get_option('template_download','download_ads_bottom');
?>
<body <?php body_class(); ?>>

<div id="page" class="site">

    <div id="content" class="site-content">
        <div class="b2-content wrapper">
            <div id="primary-home" class="content-area">
                <div class="download-page-title"><a href="<?php echo B2_HOME_URI; ?>"><?php echo VueTemplates::get_logo(); ?></a></div>
                <div class="download-page-box box b2-radius">
                    <?php echo $top; ?>
                    <h1><?php echo $download_data['name']; ?></h1>
                    <div class="download-page-info">
                        <div class="download-meta">
                            <ul>
                                <?php 
                                    foreach ($download_data['attrs'] as $k => $v) {
                                        echo '<li>
                                        <span>'.$v['name'].'：</span><span>'.$v['value'].'</span>
                                    </li>';
                                    }
                                ?>
                            </ul>
                            <div class="download-page-button" id="download-page" ref="downloadPage">
                                <div class="" v-if="data === ''">
                                    <div class="b2-loading empty-page"></div>
                                </div>
                                <div class="download-current" v-else v-cloak>
                                    <span><?php echo __('您当前的等级为','b2'); ?></span>
                                    <span v-if="data.current_user.lv.lv" v-html="data.current_user.lv.lv.icon"></span>
                                    <span v-if="data.current_user.lv.vip" v-html="data.current_user.lv.vip.icon"></span>

                                    <div class="" v-if="!data.current_user.can.allow">
                                        <span class="red"><?php echo __('没有下载权限，请','b2'); ?></span>
                                        <span v-if="data.current_user.lv.lv.lv == 'guest'">
                                            <a href="javascript:void(0)" @click="login()"><?php echo __('登录','b2'); ?></a>
                                        </span>
                                        <span v-else-if="data.current_user.can.type == 'comment'">
                                            <a href="javascript:void(0)"><?php echo __('评论之后下载','b2'); ?></a>
                                        </span>
                                        <span v-else-if="data.current_user.can.type == 'credit'">
                                            <a href="javascript:void(0)"><?php echo __('支付积分以后下载','b2'); ?></a>
                                        </span>
                                        <span v-else-if="data.current_user.can.type == 'money'">
                                            <a href="javascript:void(0)"><?php echo __('支付费用以后下载','b2'); ?></a>
                                        </span>
                                        <span v-else>
                                            <a href="javascript:void(0)"><?php echo __('升级会员','b2'); ?></a>
                                        </span>
                                    </div>
                                    <div class="" v-else>
                                        <span v-if="data.current_user.can.type == 'allow_all'" class="green">
                                            <?php echo __('您有每天下载所有资源','b2'); ?><b v-text="data.current_user.can.total_count"></b><?php echo __('次的特权，今日剩余','b2'); ?><b v-text="data.current_user.can.count"></b><?php echo __('次','b2'); ?>
                                        </span>
                                        <span class="green" v-else><?php echo __('已取得下载权限','b2'); ?></span>
                                    </div>
                                </div>
                                <div class="tqma" v-if="data !== '' && data.button.attr">
                                    <div v-if="data.button.attr.tq" v-cloak class="fuzhi" data-clipboard-target='#tq'><?php echo __('提取码（点击复制）：','b2'); ?><span v-text="data.button.attr.tq" :data-clipboard-text="data.button.attr.tq" id="tq"></span></div>
                                    <div v-if="data.button.attr.jy" v-cloak class="fuzhi" data-clipboard-target='#jy'><?php echo __('解压码（点击复制）：','b2'); ?><span v-text="data.button.attr.jy" :data-clipboard-text="data.button.attr.jy" id="jy"></span></div>
                                </div>
                                <a target="_blank" :href="'<?php echo b2_get_custom_page_url('redirect');?>'+'?token='+data.button.url" v-text="data.button.name" v-if="data.length != 0" v-cloak class="empty button"></a>
                            </div>
                        </div>
                        <div class="download-middle-ads">
                            <?php echo $middle; ?>
                        </div>
                    </div>
                    <?php echo $bottom; ?>
                </div>
            </div>
        </div>
    </div>

</div>
<?php echo Footer::vue_template(); ?>
<?php wp_footer(); ?>
</body>
</html>