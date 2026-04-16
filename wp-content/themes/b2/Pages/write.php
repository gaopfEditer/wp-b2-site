<?php
use \Firebase\JWT\JWT;

$allow = b2_get_option('normal_write','write_allow');
if(!$allow){
    wp_safe_redirect(B2_HOME_URI.'/404');
    exit;
}

$post_id = 0;
if(isset($_GET['token'])){
    try{
        //检查验证码
        $decoded = JWT::decode($_GET['token'], AUTH_KEY,array('HS256'));
        //return array('error'=>$decoded);
        if(!isset($decoded->data->post_id)){
            wp_die(__('参数错误','b2'));
        }

        $post_id = $decoded->data->post_id;

    }catch(\Firebase\JWT\ExpiredException $e) {
        wp_die(sprintf(__('文章编辑过期，请返回重新发起编辑','b2')));
    }catch(\Exception $e) { 
        wp_die(__('文章不存在','b2'));
    }
}

/**
 * 投稿页面
 */
get_header();

$cats = b2_get_option('normal_write','write_cats');
$default_cats = b2_get_option('normal_write','write_cats_default');
$collections = b2_get_option('normal_write','write_callections');
$default_collections = b2_get_option('normal_write','write_callections_default');
$p_cat = [];
$cats = $cats ? $cats : array();
$arr_arr = array();
if(!empty($cats)){
    foreach ($cats as $k => $v) {
        $cat = get_term_by('slug', $v,'category');
        if($cat){
            $arr_arr[] = array(
                'id'=>$cat->term_id,
                'name'=>$cat->name
            );
            $p_cat[] = $cat->term_id;
        }
    }
}

$collections = $collections ? $collections : array();
$collection_arr = array();
if(!empty($collections)){
    foreach ($collections as $k => $v) {
        $collection = get_term_by('slug', $v,'collection');
        if($collection){
            $collection_arr[] = array(
                'id'=>$collection->term_id,
                'name'=>$collection->name
            );
        }
    }
}

//获取用户权限
$settings = get_option('b2_normal_user');
$settings_lv = isset($settings['user_lv_group']) ? $settings['user_lv_group'] : array();
$settings_vip = isset($settings['user_vip_group']) ? $settings['user_vip_group'] : array();

//自定义字段
$write_custom = b2_get_option('normal_write','write_custom_code');
$write_custom_arg = b2_get_option('normal_write','write_custom_group');
if((int)$write_custom == 1 && !empty($write_custom_arg)){
    foreach($write_custom_arg as $k=>$v){
        if($v['type'] === 'radio' || $v['type'] === 'checkbox'){
            $str = trim($v['value'], " \t\n\r\0\x0B\xC2\xA0");
            $str = explode(PHP_EOL, $str );
            $arr = array();
            foreach($str as $_k=>$_v){
                $__k = explode('=',$_v);
                $arr[] = array(
                    'k'=>$__k[0],
                    'v'=>$__k[1]
                );
            }
            $write_custom_arg[$k]['value_arg'] =  $arr;
        }
    }
}

$edit_cats = array();
$edit_collections = array();
$edit_roles = array();
$edit_customs = array();
$edit_thumb = '';
$edit_tags = array();
$excerpt = '';
$content = '';
$title = '';
if($post_id){

    //分类
    $categorys = get_the_category($post_id);//$post->ID
    foreach($categorys as $cat){
        $edit_cats[] = $cat->term_id;
    }

    //专题
    $collections = wp_get_post_terms($post_id, 'collection' ); 
    foreach( $collections as $collection ) {
        $edit_collections[] = $collection->term_id;
    }

    $roles = get_post_meta($post_id,'b2_post_roles',true);
    $roles = $roles ? $roles : array();

    //权限
    $edit_roles = array(
        'key'=>get_post_meta($post_id,'b2_post_reading_role',true),
        'money'=>get_post_meta($post_id,'b2_post_money',true),
        'credit'=>get_post_meta($post_id,'b2_post_credit',true),
        'roles'=>$roles
    );
    
    //自定义字段
    $customs = get_post_meta($post_id,'b2_custom_key',true);

    if($customs){
        foreach($customs as $custom){
            $edit_customs[] = array(
                'key'=>(string)$custom,
                'value'=>get_post_meta($post_id,$custom,true)
            );
        }
    }

    //特色图
    $edit_thumb = wp_get_attachment_url(get_post_thumbnail_id($post_id));

    //标签
    $tags = get_the_tags($post_id);
    if($tags){
        foreach($tags as $tag) {
            $edit_tags[] = $tag->name;
        }
    }

    $excerpt = get_post_field('post_excerpt', $post_id);
    $content = '';
    
    // $content = preg_replace( '/<!-- \/?wp:(.*?) -->/', '', get_post_field('post_content', $post_id) );
    // $content = wpautop($content);

    //echo $content;

    $title = get_post_field('post_title', $post_id);

}
//wp_add_inline_script('helloworld','var site_config ='.$content);

wp_localize_script( 'b2-js-write', 'b2_write_data', array(
    'custom_code'=>$write_custom_arg,
    'cats'=>$arr_arr,
    'cats_default'=>$default_cats ? $default_cats : 0,
    'collections'=>$collection_arr,
    'collections_default'=>$default_collections ? $default_collections : 0,
    'edit_cats'=>$edit_cats,
    'edit_collections'=>$edit_collections,
    'edit_roles'=>$edit_roles,
    'edit_customs'=>$edit_customs,
    'edit_thumb'=>$edit_thumb,
    'edit_tags'=>$edit_tags,
    'edit_excerpt'=>$excerpt,
    'edit_content'=>$content,
    'edit_title'=>$title,
    'post_id'=>$post_id,
    'from'=>array(
        'url'=>get_post_meta($post_id,'b2_post_from_url',true),
        'name'=>get_post_meta($post_id,'b2_post_from_name',true)
    )
    //'l10n_print_after' => 'var content ="'.$content.'"'
));

?>
<div class="b2-single-content wrapper">
    <div id="write" class="content-area write-page">
        <main id="main" class="site-main box b2-radius">
            <?php if(isset($_GET['id']) && is_numeric($_GET['id']) && !isset($_GET['token'])){ ?>
                <div class="write-loading" data-postId="<?php echo $_GET['id']; ?>" ref="check">
                    <template v-if="!msg">
                        <h2><?php echo __('请不要关闭此页，身份核实中...','b2'); ?></h2>
                        <div class="b2-loading button text empty social-loading"></div>
                    </template>
                    <template v-if>
                        <h2 v-text="msg"></h2>
                    </template>
                </div>
            <?php }else{?>
                <div id="write-head">
                    <div class="allow-write" v-cloak v-if="!allowWrite()">
                        <?php echo __('您还未获得发布文章的权限，请联系管理员','b2'); ?>
                    </div>
                    <div class="write-thumb" :style="postData.thumb ? 'min-height:auto' : ''">
                        <label @click.stop="showImgLib" v-if="!postData.thumb && !locked" v-cloak>
                            <svg class="b2 b2--Camera WriteCover-uploadIcon" fill="currentColor" viewBox="0 0 24 24" width="42" height="42"><path d="M20.094 6S22 6 22 8v10.017S22 20 19 20H4.036S2 20 2 18V7.967S2 6 4 6h3s1-2 2-2h6c1 0 2 2 2 2h3.094zM12 16a3.5 3.5 0 1 1 0-7 3.5 3.5 0 0 1 0 7zm0 1.5a5 5 0 1 0-.001-10.001A5 5 0 0 0 12 17.5zm7.5-8a1 1 0 1 0 0-2 1 1 0 0 0 0 2z" fill-rule="evenodd"></path></svg>
                            <p><?php echo __('添加题图','b2'); ?></p>
                        </label>
                        <img :src="postData.thumb" v-if="postData.thumb"/>
                        <div v-if="postData.thumb && !locked" class="write-reset-thumb" v-cloak>
                            <label class="text button" @click.stop="showImgLib">
                                <svg class="b2 b2--Camera" fill="currentColor" viewBox="0 0 24 24" width="24" height="24"><path d="M20.094 6S22 6 22 8v10.017S22 20 19 20H4.036S2 20 2 18V7.967S2 6 4 6h3s1-2 2-2h6c1 0 2 2 2 2h3.094zM12 16a3.5 3.5 0 1 1 0-7 3.5 3.5 0 0 1 0 7zm0 1.5a5 5 0 1 0-.001-10.001A5 5 0 0 0 12 17.5zm7.5-8a1 1 0 1 0 0-2 1 1 0 0 0 0 2z" fill-rule="evenodd"></path></svg>
                            </label>
                            <button class="text" @click.stop="postData.thumb = ''">
                                <svg class="b2 b2--TrashOutline" fill="currentColor" viewBox="0 0 24 24" width="24" height="24"><path d="M16.213 18.638h-7.83V8.085H7.021v10.553c0 .751.611 1.362 1.362 1.362h7.83c.75 0 1.361-.61 1.361-1.362V8.085h-1.361v10.553zM15.19 5.362c0-.751-.61-1.362-1.361-1.362h-3.064c-.751 0-1.362.61-1.362 1.362v.68H6.766S6 6 6 6.715c0 .714.766.69.766.69H17.89s.705 0 .705-.688c0-.689-.705-.673-.705-.673h-2.7v-.681zm-1.361.68h-3.064v-.68h3.064v.68zm-3.745 3.064v8.17h1.362v-8.17h-1.362zm3.064 0v8.17h1.362v-8.17h-1.362z" fill-rule="evenodd"></path></svg>
                            </button>
                        </div>
                        <div class="thumb-upload-loading" v-if="locked" v-cloak>
                            <span class="button text b2-loading"></span>
                        </div>
                    </div>
                    <?php if(!empty($arr_arr)){ ?>
                        <div class="write-select-row-top">
                            <div class="write-select-title"><?php echo __('分类','b2'); ?></div>
                            <div class="cat-picked">
                                <span v-for="item in postData.cats" v-text="catPicked(item)" @click.stop="removeCat(item)" v-if="item"></span>
                            </div>
                            <div class="write-select-box">
                                <?php 
                                $dropdown_args = array(
                                    'hide_empty'       => 0,
                                    'hide_if_empty'    => false,
                                    'taxonomy'         => 'category',
                                    'name'             => 'parent',
                                    'orderby'          => 'name',
                                    'hierarchical'     => true,
                                    'show_option_none' => __( '分类','b2'),
                                    'include'=>$p_cat
                                );
        
                                $dropdown_args = apply_filters( 'taxonomy_parent_dropdown_args', $dropdown_args, 'category', 'new' );
        
                                wp_dropdown_categories( $dropdown_args );
                                ?>
                                <!-- <select v-model="cat" :disabled="postData.cats.length > 3 ? true : false">
                                    <option disabled :value="0"><?php echo __('分类','b2'); ?></option>
                                    <option :value="cat.id" v-for="cat in cats" v-text="cat.name" :disabled="postData.cats.indexOf(cat.id) !== -1 ? true : false"></option>
                                </select> -->
                            </div>
                        </div>
                    <?php } ?>
                    <?php if(!empty($collection_arr)){ $collection_name = b2_get_option('normal_custom','custom_collection_name');?>
                        <div class="write-select-row-top">
                            <div class="write-select-title"><?php echo $collection_name; ?></div>
                            <div class="cat-picked">
                                <span v-for="item in postData.collections" v-text="collectionPicked(item)" @click.stop="removeCollection(item)" v-if="item"></span>
                            </div>
                            <div class="write-select-box">
                                <select v-model="collection" :disabled="postData.collections.length > 3 ? true : false">
                                    <option disabled :value="0"><?php echo $collection_name; ?></option>
                                    <option :value="collection.id" v-for="collection in collections" v-text="collection.name" :disabled="postData.collections.indexOf(collection.id) !== -1 ? true : false"></option>
                                </select>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="write-select-row-top">
                        <textarea id="write-textarea" class="write-textarea" ref="writeTitle" rows="1" placeholder="<?php echo __('标题','b2'); ?>"></textarea>
                    </div>
                </div>
                <div id="b2-editor-box">
                    <textarea id="mytextarea"></textarea>
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
                <div id="write-footer">
                    <?php 
                        if($write_custom){
                    ?>
                        <div class="write-select-row" v-for="(item,index) in custom" v-cloak>
                            <div class="write-select-row-title" @click.stop="showSetting(item.key)">
                                <div class="write-title" v-text="item.name"></div>
                                <div class="write-select">
                                    <?php echo __('选填','b2'); ?>
                                </div>
                            </div>
                            <div class="write-role-settings" v-show="show[item.key]" v-cloak>
                                <p v-text="item.desc"></p>
                                <div class="write-custom" v-if="item.type == 'radio'">
                                    <label v-for="list in item.value_arg">
                                        <input type="radio" v-model="customSettings[item.key]" :value="list.k"><span v-text="list.v"></span>
                                    </label>
                                </div>
                                <div class="write-custom" v-if="item.type == 'checkbox'">
                                    <label v-for="list in item.value_arg">
                                        <input type="checkbox" v-model="customSettings[item.key]" :value="list.k"><span v-text="list.v"></span>
                                    </label>
                                </div>
                                <div class="write-custom" v-if="item.type == 'text'">
                                    <input type="text" v-model="customSettings[item.key]">
                                </div>
                                <div class="write-custom" v-if="item.type == 'textarea'">
                                    <textarea type="text" v-model="customSettings[item.key]"></textarea>
                                </div>
                            </div>
                        </div>
                    <?php
                        } 
                    ?>
                    <div class="write-select-row" v-cloak>
                        <div class="write-select-row-title" @click.stop="showSetting('from')">
                            <div class="write-title"><?php echo __('文章来源','b2'); ?></div>
                            <div class="write-select">
                                <?php echo __('选填','b2'); ?>
                            </div>
                        </div>
                        <div class="write-role-settings" v-show="show.from" v-cloak>
                            <p><?php echo __('如果您的文章是转载的，请在这里注明来源。','b2'); ?></p>
                            <div class="write-role-select">
                                <div class="write-custom">
                                    <input type="text" placeholder="<?php echo __('来源网址','b2'); ?>" v-model="from.url"/>
                                </div>
                                <div class="write-custom mg-t">
                                    <input type="text" placeholder="<?php echo __('来源站点名称','b2'); ?>" v-model="from.name"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="write-select-row" v-cloak>
                        <div class="write-select-row-title" @click.stop="showSetting('role')">
                            <div class="write-title"><?php echo __('权限','b2'); ?></div>
                            <div class="write-select">
                                <?php echo __('选填','b2'); ?>
                            </div>
                        </div>
                        <div class="write-role-settings" v-show="show.role" v-cloak>
                            <p><?php echo __('如果您在文章中插入了隐藏内容，需要在此处设置查看权限，方可正常隐藏。','b2'); ?></p>
                            <div class="write-role-select">
                                <select v-model="role.key">
                                    <option value="none"><?php echo __('无限制','b2'); ?></option>
                                    <option value="money"><?php echo __('支付费用','b2'); ?></option>
                                    <option value="credit"><?php echo __('支付积分','b2'); ?></option>
                                    <option value="roles"><?php echo __('限制等级','b2'); ?></option>
                                    <option value="login"><?php echo __('登录可见','b2'); ?></option>
                                    <option value="comment"><?php echo __('评论可见','b2'); ?></option>
                                </select>
                                <div class="write-role-settings-right">
                                    <div class="" v-if="role.key == 'money'">
                                        <p><?php echo __('请输入需要支付的费用：','b2'); ?></p>
                                        <input type="text" v-model="role.money">
                                    </div>
                                    <div class="" v-if="role.key == 'credit'">
                                        <p><?php echo __('请输入需要支付的积分：','b2'); ?></p>
                                        <input type="text" v-model="role.credit">
                                    </div>
                                    <div class="" v-if="role.key == 'roles'">
                                        <div class="write-normal-lv">
                                            <p><?php echo __('普通等级：','b2'); ?></p>
                                            <?php 
                                                foreach ($settings_lv  as $k => $v) {
                                                    echo '<label><input type="checkbox" id="lv'.$k.'" value="lv'.$k.'" v-model="role.roles"><span>'.$v['name'].'</span></label>';
                                                }
                                            ?>
                                        </div>
                                        <?php if(!empty($settings_vip)){ ?>
                                            <div class="write-normal-lv">
                                                <p><?php echo __('会员等级：','b2'); ?></p>
                                                <?php 
                                                    foreach ($settings_vip  as $k => $v) {
                                                        echo '<label><input type="checkbox" id="vip'.$k.'" value="vip'.$k.'" v-model="role.roles"><span>'.$v['name'].'</span></label>';
                                                    }
                                                ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="write-select-row">
                        <div class="write-select-row-title" @click.stop="showSetting('tag')">
                            <div class="write-title"><?php echo __('标签','b2'); ?></div>
                            <div class="write-select">
                                <?php echo __('选填','b2'); ?>
                            </div>
                        </div>
                        <div class="write-role-settings" v-show="show.tag" v-cloak>
                            <p><?php echo __('请输入标签，最多可以设置4个','b2'); ?></p>
                            <label class="write-tags">
                                <?php echo b2_get_icon('b2-price-tag-3-line'); ?>
                                <div class="cat-picked">
                                    <span v-for="(t,index) in tags" @click.stop="removeTag(index)" v-text="t"></span>
                                </div>
                                <input type="text" @input="tagChange($event)" @keydown="tagChange($event)" @blur="tagChange($event)" v-model="tag" placeholder="<?php echo __('请输入标签','b2'); ?>">
                            </label>            
                        </div>
                    </div>
                    <div class="write-select-row">
                        <div class="write-select-row-title" @click.stop="showSetting('excerpt')">
                            <div class="write-title"><?php echo __('摘要','b2'); ?></div>
                            <div class="write-select">
                                <?php echo __('选填','b2'); ?>
                            </div>
                        </div>
                        <div class="write-role-settings" v-show="show.excerpt" v-cloak>
                            <p><?php echo __('请输入描述内容','b2'); ?></p>
                            <div class="write-textarea-box">
                                <textarea v-model="excerpt" ref="excerpt"></textarea>
                            </div>            
                        </div>
                    </div>
                    <div class="write-bottom">
                        <div class="mobile-hidden"><?php echo __('请尊重自己和别人的时间，不要发布垃圾和广告内容。','b2'); ?></div>
                        <div>
                            <button @click.stop="submit('draft')" :disabled="locked"><?php echo __('保存草稿到服务器','b2'); ?></button>
                            <button @click.stop="submit('publish')" :disabled="locked"><?php echo __('发布','b2'); ?></button>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </main>
    </div>
    <?php get_template_part( 'Sidebars/sidebar'); ?>
</div>
<?php
get_footer();