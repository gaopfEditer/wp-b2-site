<?php
/**
 * 发布信息
 */
get_header();

$name = b2_get_option('normal_custom','custom_infomation_name');
$for = b2_get_option('normal_custom','custom_infomation_for');
$get = b2_get_option('normal_custom','custom_infomation_get');

$cats = b2_get_option('infomation_submit','submit_cats');

$post_id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;

$cat_id = 0;

if($post_id){
    $cat = get_the_terms($post_id, 'infomation_cat' );
    if(isset($cat[0]->term_id)){
        $cat_id = $cat[0]->term_id;
    }
}

?>
<div class="b2-single-content wrapper">
    <div id="po-infomation" class="content-area mg-b" ref="poinfomation" data-id="<?php echo $post_id; ?>">
        <main id="main" class="site-main box b2-radius">
            <div class="info-po-type">
                <div class="info-po-type-in">
                    <div :class="type == 'for' ? 'picked' : ''" @click="type = 'for'"><?php echo $for; ?></div>
                    <div :class="type == 'get' ? 'picked' : ''" @click="type = 'get'"><?php echo $get; ?></div>
                </div>
                <div class="po-attrs-buttons" v-if="opts != ''" v-cloak>
                    <label class="po-info-sticky red" v-if="opts.allow_opts.indexOf('sticky') != -1">
                        <input type="checkbox" v-model="attrs" value="sticky"/>
                        <?php echo b2_get_icon('b2-exchange-cny-line').__('我要置顶','b2'); ?>
                    </label>
                    <!-- <label v-if="opts.allow_opts.indexOf('price') != -1">
                        <input type="checkbox" v-model="attrs" value="price" />
                        <?php echo __('价格','b2'); ?>
                    </label>
                    <label v-if="opts.allow_opts.indexOf('passtime') != -1">
                        <input type="checkbox" v-model="attrs" value="passtime" />
                        <?php echo __('过期时间','b2'); ?>
                    </label> -->
                    <label v-if="opts.allow_opts.indexOf('contact') != -1">
                        <input type="checkbox" v-model="attrs" value="contact" />
                        <?php echo __('联系方式','b2'); ?>
                    </label>
                    <label v-if="opts.allow_opts.indexOf('attrs') != -1">
                        <input type="checkbox" v-model="attrs" value="attrs"/>
                        <?php echo __('属性','b2'); ?>
                    </label>
                </div>
            </div>
            <div class="info-po-attrs" v-if="attrs.length > 0" v-cloak>
                <div class="po-info-sticky-box" v-if="attrs.indexOf('sticky') != -1">
                    <p>
                        <span class="po-attr-title"><?php echo __('置顶设置：','b2'); ?></span>
                    </p>
                    <div v-if="!sticky">
                        <p v-if="!meta.sticky"><?php echo sprintf(__('置顶价格为%s/天，您需要置顶几天？','b2'),'<b v-text="\''.B2_MONEY_SYMBOL.'\'+opts.sticky_pay"></b>'); ?></p>
                        <p v-else><?php echo sprintf(__('置顶价格为%s/天，置顶%s天，价格为%s。','b2'),'<b v-text="\''.B2_MONEY_SYMBOL.'\'+opts.sticky_pay"></b>','<b v-text="meta.sticky"></b>','<b v-text="\''.B2_MONEY_SYMBOL.'\'+totalPay()"></b>'); ?></p>
                        <p class="info-po-input"><input type="number" v-model="meta.sticky" /><span><?php echo __('天','b2'); ?></span></p>
                        <button class="empty" v-if="meta.sticky" @click="pay"><?php echo __('支付','b2'); ?></button>
                        <p class="info-po-desc"><?php echo __('支付成功后，如果此贴不使用置顶，可在后续新帖中继续使用。支付成功后无法退款，请考虑清楚后再付款。','b2'); ?></p>
                    </div>
                    <div v-else>
                        <p class="green"><?php echo sprintf(__('您已支付%s，可以置顶%s天，该帖发布后立刻生效。','b2'),B2_MONEY_SYMBOL.'<b v-text="sticky.money"></b>','<b v-text="sticky.days == 0 ? \'N\' : sticky.days"></b>'); ?></p>
                        <p class="info-po-desc"><?php echo __('如果此贴不使用置顶，可在后续新帖中继续使用。','b2'); ?></p>
                    </div>
                </div>
                <div class="info-po-attrs-row1" v-if="attrs.indexOf('price') != -1 || attrs.indexOf('passtime') != -1">
                    <div v-if="attrs.indexOf('price') != -1" class="attr-price">
                        <p>
                            <span class="po-attr-title"><?php echo __('预计价格：','b2'); ?></span>
                        </p>
                        <label class="info-po-input"><?php echo B2_MONEY_SYMBOL; ?><input type="number" v-model="meta.price" placeholder="<?php echo __('例如：120','b2'); ?>"/><?php echo __('元','b2'); ?></label>
                        <p class="info-po-desc"><?php echo __('该商品的预计价格，可以留空。','b2'); ?></p>
                    </div>
                    <div v-if="attrs.indexOf('passtime') != -1" class="attr-passtime">
                        <p>
                            <span class="po-attr-title"><?php echo __('帖子有效期：','b2'); ?></span>
                        </p>
                        <label class="info-po-input"><input type="number" v-model="meta.passtime" placeholder="<?php echo __('例如：2','b2'); ?>"/><?php echo __('天','b2'); ?></label>
                        <p class="info-po-desc"><?php echo __('到期之后会隐藏联系方式，不能超过30天。留空则永不过期。','b2'); ?></p>
                    </div>
                </div>
                <div v-if="attrs.indexOf('contact') != -1" class="attr-contact">
                    <p><span class="po-attr-title"><?php echo __('联系方式：','b2'); ?></span></p>
                    <div>
                        <label class="info-po-input">
                            <span><?php echo __('方式','b2'); ?></span><input type="text" v-model="meta.contact.type" placeholder="<?php echo __('例如：QQ号','b2'); ?>"/>
                        </label>
                        <label class="info-po-input">
                            <span><?php echo __('号码','b2'); ?></span><input type="text" v-model="meta.contact.number" placeholder="<?php echo __('例如：110613846','b2'); ?>"/>
                        </label>
                    </div>
                </div>
                <div v-if="attrs.indexOf('attrs') != -1" class="attr-contact">
                    <p><span class="po-attr-title"><?php echo __('属性：','b2'); ?></span></p>
                    <div v-for="(item,index) in meta.attrs" :key="index">
                        <label class="info-po-input">
                            <span><?php echo __('属性名','b2'); ?></span><input type="text" v-model="item.key" placeholder="<?php echo __('例如：数量','b2'); ?>"/>
                        </label>
                        <label class="info-po-input">
                            <span><?php echo __('属性值','b2'); ?></span><input type="text" v-model="item.value" placeholder="<?php echo __('例如：100个','b2'); ?>"/>
                        </label>
                        <span class="po-attr-add" @click="addAttr" v-if="index == meta.attrs.length - 1"><?php echo b2_get_icon('b2-add-circle-line1'); ?></span>
                        <span class="po-attr-add" @click="subAttr(index)" v-else><?php echo b2_get_icon('b2-indeterminate-circle-line'); ?></span>
                    </div>
                </div>
            </div>
            <div class="info-po-edit-box">
                <div class="info-po-edit-box-top">
                    <div class="info-po-edit-title">
                        <textarea placeholder="<?php echo __('标题','b2'); ?>" class="write-textarea" ref="writeTitle" v-model="title"></textarea>
                    </div>
                </div>
                
                <div class="info-po-edit-content" id="b2-editor-box">
                    <textarea id="info-edit-content"></textarea>
                    <div :class="['trix-dialog trix-dialog--images modal',{'show-modal':showImageBox}]" v-cloak>
                        <div class="trix-dialog__link-fields box">
                            <div class="trix-dialog-title" v-if="!thumbPicked"><?php echo __('插入图片','b2'); ?></div>
                            <div class="trix-dialog-title" v-else><?php echo __('设置题图','b2'); ?></div>
                            <span class="close-button" @click.stop="close('image')">×</span>
                            <div class="image-table">
                                <div :class="imgTable == 'upload' ? 'picked' : ''" @click.stop="imgTable = 'upload'"><?php echo __('上传图片','b2'); ?></div>
                                <div :class="imgTable == 'lib' ? 'picked' : ''" @click.stop="imgTable = 'lib'"><?php echo __('我的图片','b2'); ?></div>
                                <div :class="imgTable == 'link' ? 'picked' : ''" @click.stop="imgTable = 'link'" v-if="!thumbPicked"><?php echo __('外链图片','b2'); ?></div>
                            </div>
                            <label class="image-upload-box" v-if="imgTable == 'upload'">
                                <div class="">
                                    <svg class="b2 b2--BackToTop" fill="currentColor" viewBox="0 0 24 24" width="70" height="70"><path d="M16.036 19.59a1 1 0 0 1-.997.995H9.032a.996.996 0 0 1-.997-.996v-7.005H5.03c-1.1 0-1.36-.633-.578-1.416L11.33 4.29a1.003 1.003 0 0 1 1.412 0l6.878 6.88c.782.78.523 1.415-.58 1.415h-3.004v7.005z"></path></svg>
                                    <p><?php echo __('请选择要上传的图片','b2'); ?></p>
                                </div>
                                <input type="file" class="b2-hidden-always" accept="image/jpg,image/jpeg,image/png,image/gif" @change="fileUpload($event,'image')" multiple="multiple">
                            </label>
                            <div class="trix-dialog-image-box" v-if="imgTable == 'lib'">
                                <div class="" v-if="imageList.length > 0">    
                                    <ul class="editor-images-list">
                                        <li v-for="item in imageList" @click.stop="item.thumb ? picked('image',item.att_url) : ''" :class="(imagePicked.indexOf(item.att_url) !== -1 && !thumbPicked) || (thumb == item.att_url && thumbPicked) ? 'picked' : ''">
                                            <div class="editor-image">
                                                <span v-if="!item.thumb" class="b2-loading button text"></span>
                                                <img :src="item.thumb" v-if="item.thumb">
                                            </div>
                                        </li>
                                    </ul>
                                    <div class="image-nav" v-if="imagePages > 0">
                                        <button class="text" :disabled="locked.pre || paged == 1 ? true : false" @click.stop="per('image')"><?php echo __('上一页','b2'); ?></button>
                                        <button class="text" :disabled="locked.next || paged == imagePages ? true : false" @click.stop="next('image')"><?php echo __('下一页','b2'); ?></button>
                                    </div>
                                </div>
                                <div class="editor-file-none" v-else>
                                    <?php echo __('您未上传过图片','b2'); ?>
                                </div>
                            </div>
                            <div class="trix-dialog-input-box" v-if="imgTable == 'link'">
                                <div class="trix-dialog-input input-textarea">
                                    <textarea type="text" name="imageLink" class="trix-input trix-input--dialog" id="imageLink"></textarea>
                                </div>
                                <p class="dialog-desc"><?php echo __('支持多张外链图片，每个图片连接占一行','b2'); ?></p>
                            </div>
                            <div class="trix-button-group" v-show="imgTable != 'upload'">
                                <button  class="empty" @click.stop="close('image')"><?php echo __('取消','b2'); ?></button>
                                <button @click.stop="insert('image')"  v-if="!thumbPicked"><?php echo __('插入','b2'); ?></button>
                                <button @click.stop="setThumb"  v-else><?php echo __('设置','b2'); ?></button>
                            </div>
                        </div>
                    </div>
                    <div :class="['trix-dialog trix-dialog--video modal',{'show-modal':showVideoBox}]" v-cloak>
                        <div class="trix-dialog__link-fields box">
                            <div class="trix-dialog-title"><?php echo __('插入视频','b2'); ?></div>
                            <span class="close-button" @click.stop="close('video')">×</span>
                            <div class="image-table">
                                <div :class="videoTable == 'upload' ? 'picked' : ''" @click.stop="videoTable = 'upload'"><?php echo __('上传视频','b2'); ?></div>
                                <div :class="videoTable == 'lib' ? 'picked' : ''" @click.stop="videoTable = 'lib'"><?php echo __('我的视频','b2'); ?></div>
                                <div :class="videoTable == 'link' ? 'picked' : ''" @click.stop="videoTable = 'link'"><?php echo __('外链视频','b2'); ?></div>
                                <div :class="videoTable == 'html' ? 'picked' : ''" @click.stop="videoTable = 'html'"><?php echo __('内嵌视频','b2'); ?></div>
                            </div>
                            <label class="image-upload-box" v-if="videoTable == 'upload'">
                                <div class="">
                                    <svg class="b2 b2--BackToTop" fill="currentColor" viewBox="0 0 24 24" width="70" height="70"><path d="M16.036 19.59a1 1 0 0 1-.997.995H9.032a.996.996 0 0 1-.997-.996v-7.005H5.03c-1.1 0-1.36-.633-.578-1.416L11.33 4.29a1.003 1.003 0 0 1 1.412 0l6.878 6.88c.782.78.523 1.415-.58 1.415h-3.004v7.005z"></path></svg>
                                    <p><?php echo __('请选择要上传的视频','b2'); ?></p>
                                </div>
                                <input type="file" class="b2-hidden-always" accept="video/mp4,video/x-ms-asf,video/x-ms-wmv,video/x-ms-wmx,video/x-ms-wm,video/avi,video/divx,video/x-flv,video/quicktime,video/mpeg,video/ogg,video/webm,video/x-matroska,video/3gpp,video/3gpp2" @change="fileUpload($event,'video')">
                            </label>
                            <div class="trix-dialog-video-box" v-if="videoTable == 'lib'">
                                <div class="" v-if="videoList.length > 0">    
                                    <ul class="editor-images-list">
                                        <li v-for="item in videoList" @click.stop="item.att_url ? picked('video',item.att_url) : ''" :class="videoPicked && videoPicked.indexOf(item.att_url) !== -1 ? 'picked' : ''">
                                            <div class="editor-image">
                                                <span v-if="!item.att_url" class="b2-loading button text"></span>
                                                <video muted :src="item.att_url" v-if="item.att_url" @mouseenter="videoplay($event,'play')" @mouseleave="videoplay($event,'stop')"></video>
                                            </div>
                                        </li>
                                    </ul>
                                    <div class="image-nav" v-if="videoPages > 0">
                                        <button class="text" :disabled="locked.pre || paged == 1 ? true : false" @click.stop="per('video')"><?php echo __('上一页','b2'); ?></button>
                                        <button class="text" :disabled="locked.next || paged == videoPages ? true : false" @click.stop="next('video')"><?php echo __('下一页','b2'); ?></button>
                                    </div>
                                </div>
                                <div class="editor-file-none" v-else>
                                    <?php echo __('您未上传过视频','b2'); ?>
                                </div>
                            </div>
                            <div v-if="videoTable == 'link'">
                                <div class="trix-dialog-input-box">
                                    <div class="trix-dialog-input">
                                        <label for="videoLink"><?php echo __('视频地址','b2'); ?></label>
                                        <input type="url" name="videoLink" class="trix-input trix-input--dialog" id="videoLink">
                                    </div>
                                    <p class="dialog-desc"><?php echo __('复制视频文件地址或各大视频网站视频地址','b2'); ?></p>
                                </div>
                                <div class="trix-dialog-input-box">
                                    <div class="trix-dialog-input">
                                        <label for="videoThumb"><?php echo __('视频封面','b2'); ?></label>
                                        <input type="url" name="videoThumb" class="trix-input trix-input--dialog" id="videoThumb">
                                    </div>
                                    <p class="dialog-desc"><?php echo __('如果不设置封面，程序会自动获取','b2'); ?></p>
                                </div>
                            </div>
                            <div class="trix-dialog-input-box" v-if="videoTable == 'html'" v-cloak>
                                <div class="trix-dialog-input input-textarea">
                                    <textarea type="url" class="trix-input trix-input--dialog" id="videoHtml"></textarea>
                                </div>
                                <p class="dialog-desc"><?php echo __('将内嵌视频代码粘贴在上面','b2'); ?></p>
                            </div>
                            <div class="trix-button-group" v-if="videoTable != 'upload'">
                                <button  class="empty" @click.stop="close('video')"><?php echo __('取消','b2'); ?></button>
                                <button  @click.stop="insert('video')"><?php echo __('插入','b2'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="po-infomation-footer">
                    <div class="info-po-cats">
                        <label class="infomation-cat-select">
                            <?php
                            $dropdown_args = array(
                                'hide_empty'       => 0,
                                'hide_if_empty'    => false,
                                'taxonomy'         => 'infomation_cat',
                                'name'             => 'infomation-cat',
                                'orderby'          => 'count',
                                'hierarchical'     => true,
                                'show_option_none' => __( '请选择分类','b2'),
                                'include'=>$cats,
                                'selected'=>$cat_id
                            );

                            $dropdown_args = apply_filters( 'taxonomy_parent_dropdown_args', $dropdown_args, 'link_cat', 'new' );

                            wp_dropdown_categories( $dropdown_args );
                            ?>
                        </label>
                    </div>
                    <button @click="submit"><?php echo __('发布','b2'); ?></button>
                </div>
            </div>
        </main>
    </div>
    <?php get_template_part( 'Sidebars/sidebar'); ?>
</div>
<?php
get_footer();