<?php
/**
 * 提问页面
 */
get_header();
$allow_cats = b2_get_option('ask_submit','ask_submit_cats');
?>
<div class="b2-single-content wrapper">
    <div id="ask" class="content-area ask-page" ref="poask">
        <div class="comment-overlay" v-if="!b2token || !userRole.can_ask" v-cloak>
            <div class="comment-overlay-login">
                <template v-if="!b2token">
                    <p><?php echo __('请先登录再进行提问','b2'); ?></p> 
                    <button class="empty" @click="login.show = true"><?php echo __('登录','b2'); ?></button>
                </template>
                <template v-else-if="userRole">
                    <p><?php echo __('您暂时无权提交问题，如果需要提问，您可以私信管理员','b2'); ?></p> 
                    <button class="empty" @click="dmsg"><?php echo __('私信管理员','b2'); ?></button>
                </template>
            </div>
        </div>
        <main id="main" class="site-main box b2-radius">
            <div class="po-ask-top b2flex">
                <div class="po-ask-left">
                    <h1><?php echo __('提问','b2'); ?></h1>
                    <div class="po-xs">
                        <label>
                            <input type="checkbox" v-model="reward" /><span><?php echo __('悬赏','b2'); ?></span>
                        </label>
                    </div>
                </div>
                <div class="po-ask-right"><button @click="showModal = true" :disabled="ask.pickedList.length >= 4 ? true :false"><?php echo __('邀请回答','b2'); ?></button></div>
            </div>
            <div class="reward" v-if="reward" v-cloak>
                <div class="reward-left">
                    <div class="reward-type">
                        <span :class="rewardType == 'credit' ? 'picked red' : ''" @click="rewardType = 'credit'"><?php echo __('积分','b2'); ?></span>
                        <span :class="rewardType == 'money' ? 'picked red' : ''" @click="rewardType = 'money'"><?php echo B2_MONEY_NAME; ?></span>
                    </div>
                    <div>
                        <input type="number" :placeholder="rewardType == 'money' ? '<?php echo __('请输入金额（直接填写金额数字最少1元）','b2'); ?>' : '<?php echo __('请输入积分数额（整数）','b2'); ?>'" v-model="money"/>
                    </div>
                    <div class="reward-desc">
                        <div v-if="rewardType == 'credit'"><?php echo sprintf(__('问题提交之后会立刻扣除相应的积分。您的当前积分为：%s','b2'),b2_get_icon('b2-coin-line').'<span>{{userData.credit}}</span>'); ?></div>
                        <div v-if="rewardType == 'money'"><?php echo sprintf(__('问题提交之后会立刻扣除相应的%s。您的当前%s为：%s','b2'),B2_MONEY_NAME,B2_MONEY_NAME,B2_MONEY_SYMBOL.'<span>{{userData.money}}</span>'); ?></div>
                    </div>
                </div>
                <div class="reward-right">
                    <div class="reward-type">
                        <span><?php echo __('过期时间','b2'); ?></span>
                    </div>
                    <div>
                        <input type="number" placeholder="<?php echo __('请输入过期时间（天）','b2'); ?>" v-model="passtime"/>
                    </div>
                    <div class="reward-desc">
                        <div>
                            <?php echo __('请直接填写数字，并且大于1小于30。如果没有回答被采纳，提问过期后会自动关闭，然后对奖励进行结算。'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ask-search-user-input" v-if="ask.pickedList.length > 0" v-cloak>
                <div class="po-ask-desc mg-b"><?php echo __('邀请回答','b2'); ?></div>
                <ul>
                    <li v-for="(item,index) in ask.pickedList" @click.stop.prevent="" class="search-user-li" :key="index">
                        <div>
                            <img :src="item.avatar" class="avatar"/>
                            <span v-text="item.name"></span><b @click.stop.prevent="removePickedUser(index)"><?php echo b2_get_icon('b2-close-line'); ?></b>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="po-ask-cats">
                <div class="po-ask-desc mg-b"><?php echo __('请选择标签','b2'); ?></div>
                <div style="display: flex;">
                    <div class="b2flex" v-if="Object.keys(cats).length > 0" v-cloak>
                        <div v-for="(cat,key,index) in cats" :key="key" class="po-cat-item">
                            <div>
                                <span v-text="cat"></span>
                                <b @click="removeCat(key)" class="removeb">x</b>
                            </div>
                        </div>
                    </div>
                    <label class="register-cat" ref="catpicked">
                        <?php
                        $dropdown_args = array(
                            'hide_empty'       => 0,
                            'hide_if_empty'    => false,
                            'taxonomy'         => 'ask_cat',
                            'name'             => 'parent',
                            'orderby'          => 'name',
                            'hierarchical'     => true,
                            'show_option_none' => __( '请选择板块','b2'),
                            'include'=>$allow_cats
                        );

                        $dropdown_args = apply_filters( 'taxonomy_parent_dropdown_args', $dropdown_args, 'ask_cat', 'new' );

                        wp_dropdown_categories( $dropdown_args );
                        ?>
                    </label>
                </div>
            </div>
            <div>
                <textarea type="text" class="write-textarea" placeholder="<?php echo __('标题','b2'); ?>" ref="title"></textarea>
            </div>
            <div id="b2-editor-box">
                <textarea id="ask-edit-content" v-cloak></textarea>
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
                    <div :class="['trix-dialog trix-dialog--video modal',{'show-modal':showPostBox}]" v-cloak>
                        <div class="trix-dialog__link-fields box">
                            <div class="trix-dialog-title"><?php echo __('插入文章','b2'); ?></div>
                            <span class="close-button" @click.stop="close('post')">×</span>
                            <div class="trix-dialog-input-box">
                                <div class="trix-dialog-input">
                                    <label for="postLink"><?php echo __('文章网址','b2'); ?></label>
                                    <input type="url" name="postLink" class="trix-input trix-input--dialog" id="postLink">
                                </div>
                                <p class="dialog-desc"><?php echo __('只允许插入本站的文章连接','b2'); ?></p>
                            </div>
                            <div class="trix-button-group">
                                <button  @click.stop="close('post')" class="empty"><?php echo __('取消','b2'); ?></button>
                                <button @click.stop="insertPost()"><?php echo __('插入','b2'); ?></button>
                            </div>
                        </div>
                    </div>
                    <div :class="['trix-dialog trix-dialog--video modal',{'show-modal':showFileBox}]" v-cloak>
                        <div class="trix-dialog__link-fields box">
                            <div class="trix-dialog-title"><?php echo __('插入附件','b2'); ?></div>
                            <span class="close-button" @click.stop="close('file')">×</span>
                            <div class="trix-dialog-input-box">
                                <div class="trix-dialog-input">
                                    <label for="fileTitle"><?php echo __('标题','b2'); ?></label>
                                    <input type="url" name="fileTitle" class="trix-input trix-input--dialog" id="fileTitle">
                                </div>
                            </div>
                            <div class="trix-dialog-input-box">
                                <div class="trix-dialog-input">
                                    <label for="fileLink"><?php echo __('连接','b2'); ?></label>
                                    <input type="url" name="fileLink" class="trix-input trix-input--dialog" id="fileLink">
                                </div>
                            </div>
                            <div class="trix-dialog-input-box">
                                <div class="trix-dialog-input">
                                    <label for="fileTq"><?php echo __('提取码','b2'); ?></label>
                                    <input type="url" name="fileTq" class="trix-input trix-input--dialog" id="fileTq">
                                </div>
                                <p class="dialog-desc"><?php echo __('选填','b2'); ?></p>
                            </div>
                            <div class="trix-dialog-input-box">
                                <div class="trix-dialog-input">
                                    <label for="fileJy"><?php echo __('解压码','b2'); ?></label>
                                    <input type="url" name="fileJy" class="trix-input trix-input--dialog" id="fileJy">
                                </div>
                                <p class="dialog-desc"><?php echo __('选填','b2'); ?></p>
                            </div>
                            <div class="trix-button-group">
                                <button @click.stop="close('file')" class="empty"><?php echo __('取消','b2'); ?></button>
                                <button @click.stop="insertFile"><?php echo __('插入','b2'); ?></button>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="po-ask-button">
                <div>
                    
                </div>
                <button @click="submit"><?php echo __('发布','b2'); ?></button>
            </div>
        </main>
        <div :class="['po-ask-users modal',{'show-modal':showModal}]" id="po-ask-users" v-cloak>
            <div class="modal-content b2-radius">
                <span class="close-button" @click="showModal = false">×</span>
                <div class="ask-search-user-input search-text">
                    <input type="text" autocomplete="off" name="user" placeholder="<?php echo __('搜索邀请回答的用户','b2'); ?>" v-model="userInput" @focus="ask.focus = true" @input="searchUser" @click.stop="">
                </div>
                <div class="search-users" v-cloak v-show="ask.focus">
                    <div v-if="ask.userList.length > 0" class="search-users-list">
                        <ul>
                            <li v-for="item in ask.userList" @click.stop.prevent="pickedUser(item.id,item.name,item.avatar)" :key="item.id" class="b2flex">
                                <div class="b2flex">
                                    <?php echo b2_get_img(array(
                                        'source_data'=>':srcset="item.avatar_webp"',
                                        'src_data'=>':src="item.avatar"',
                                        'class'=>array('avatar')
                                    ));?>
                                    <span v-text="item.name"></span>（<b v-text="'ID:'+item.id"></b>）
                                </div>
                                <div>
                                    <button class="mini"><?php echo __('邀请回答','b2'); ?>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="search-users-none search-users-list" v-else>
                        <span v-if="ask.empty"><?php echo __('没有找到用户','b2'); ?></span>
                        <span v-else><?php echo __('请输入您要搜索的用户昵称','b2'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php get_template_part( 'Sidebars/sidebar'); ?>
</div>
<?php
get_footer();