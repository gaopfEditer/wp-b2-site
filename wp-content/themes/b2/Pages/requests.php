<?php
use B2\Modules\Common\Document;
get_header();
$paged = get_query_var('paged') ? get_query_var('paged') : 1;
?>
<div class="b2-document-single mg-t- mg-b">
    <div class="document-single-top wrapper">
        <div class="document-breadcrumb b2-hover">
            <?php echo Document::document_breadcrumb(0); ?>
        </div>
        <div class="document-top-search">
        <form method="get" action="<?php echo B2_HOME_URI.'/document'; ?>" class="single-document-search">
            <input type="text" name="s" autocomplete="off" class="search-input b2-radius" placeholder="<?php echo __('请输入关键词','b2'); ?>"> 
            <input type="hidden" name="type" value="document"> 
            <div class="search-button"><button><?php echo b2_get_icon('b2-search-line'); ?></button></div>
        </form>
        </div>
    </div>
</div>
<div class="b2-single-content wrapper">
    <div id="requests-page" class="content-area request-page" ref="requests">
        <main id="main" class="site-main" ref="paged" data-paged="<?php echo $paged; ?>">
            <div class="box b2-radius mg-b request-box">
                <h1 class="b2-pd"><?php echo __('我的工单','b2'); ?><p><?php echo __('一次提交一条，回复之后可再次提交！','b2'); ?></p></h1>
                <div class="button empty b2-loading empty-page text" v-if="requestList === ''"></div>
                <div v-else-if="requestList.length > 0" v-cloak class="request-list-box">
                    <ul>
                        <li v-for="item in requestList" @click="showDrap(item.id)" :class="'b2-pd '+item.type">
                            <span class="requestList-icon b2-color" v-if="showList.length > 0 && showList[item.id] == true">
                                <?php echo b2_get_icon('b2-arrow-down-s-line'); ?>
                            </span>
                            <span class="requestList-icon b2-color" v-else>
                                <?php echo b2_get_icon('b2-arrow-right-s-line'); ?>
                            </span>
                            <div v-html="item.date" class="request-date"></div>
                            <div class="request-title" v-if="item.type === 'from'"><?php echo b2_get_icon('b2-question-line'); ?><span v-text="item.value"></span></div>
                            <div class="request-title" v-else><?php echo b2_get_icon('b2-whatsapp-line'); ?><span><?php echo __('客服回复：','b2'); ?></span></div>
                            <div v-html="item.content" class="request-content jt" v-show="showList.length > 0 && showList[item.id] == true" @click.stop=""></div>
                        </li>
                    </ul>
                </div>
                <div v-else v-cloak class="no-request b2-pd">
                    <?php echo __('没有工单记录','b2'); ?>
                </div>
                <page-nav ref="goldNav" paged="<?php echo $paged; ?>" navtype="json" :pages="pages" type="p" :box="selecter" :opt="opt" :api="api" url="<?php echo b2_get_custom_page_url('requests'); ?>" title="<?php echo __('工单','b2'); ?>" @return="get"></page-nav>
            </div>
            <div class="box b2-radius b2-pd">
                <h1 class="mg-b"><?php echo __('提交工单','b2'); ?></h1>
                <div class="request-box">
                    <div class="request-box-row-header">
                        <label class="email">
                            <p><?php echo __('邮箱地址','b2'); ?><span class="red">*</span></p>
                            <input type="text" v-model="data.email" name="email">
                        </label>
                        <label class="title">
                            <p><?php echo __('标题','b2'); ?><span class="red">*</span></p>
                            <input type="text" v-model="data.title" name="title">
                        </label>
                    </div>
                    <div class="request-box-row-header">
                        <label class="desc">
                            <p><?php echo __('描述','b2'); ?><span class="red">*</span></p>
                            <textarea v-model="data.content"></textarea>
                        </label>
                        <label for="fileinput" class="thumb">
                            <p><?php echo __('截图','b2'); ?></p>
                            <input id="fileinput" type="file" accept="image/jpg,image/jpeg,image/png,image/gif" ref="fileInput" class="b2-hidden-always" @change="getFile($event)">
                            <div class="input-file"><img :src="image" v-if="image" /><span v-else><?php echo __('请选择您的图片','b2'); ?></span></div>
                        </label>
                    </div>
                    <div class="request-submit"><button @click="submit" :disabled="locked" :class="locked ? 'b2-loading' : ''"><?php echo __('提交','b2'); ?></button></div>
                </div>
            </div>
        </main>
    </div>
    <?php get_template_part( 'Sidebars/sidebar'); ?>
</div>
<?php
get_footer();
?>