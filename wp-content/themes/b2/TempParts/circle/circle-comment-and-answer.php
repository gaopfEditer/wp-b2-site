<?php 
    $smile = B2\Modules\Common\Comment::smilies_reset(true);
    $html = '';
    $answer_smile = '';
    foreach ($smile as $k => $v) {
        $html .= '<button class="text smily-button" @click="addSmile(\''.$k.'\')">'.$v.'</button>';
        $answer_smile .= '<button class="text smily-button" @click="answerAddSmile(\''.$k.'\')">'.$v.'</button>';
    }
?>
<div id="comment-form-reset" v-cloak>
    <div class="topic-comment-form show b2-radius" id="topic-comment-form" @click.stop="">
        <div class="topic-answer-login" v-if="!login" v-cloak>
            <p><?php echo __('请先登录！','b2'); ?></p>
            <div><button class="empty" @click="loginAc(1)"><?php echo __('登录','b2'); ?></button>&nbsp;&nbsp;<button @click="loginAc(2)"><?php echo __('快速注册','b2'); ?></button></div>
        </div>
        <div class="topic-comment-left">
            <textarea @focus="commentBox.focus = true" ref="topicForm" placeholder="<?php echo __('您的看法','b2'); ?>"></textarea>
            <div class="topic-comment-tools">
                <button :class="['text',{'hover':commentBox.showImgBox}]">
                    <label>
                        <?php echo b2_get_icon('b2-image-fill'); ?>
                        <input type="file" accept="image/jpg,image/jpeg,image/png,image/gif" @change="getFile($event)" multiple="multiple" class="b2-hidden-always" ref="imageInput"/>
                    </label>
                </button>
                <button :class="['text',{'hover':smileShow}]" @click="smileShow = !smileShow"><?php echo b2_get_icon('b2-emotion-laugh-line'); ?></button>
                <div v-if="commentBox.showImgBox" v-cloak class="topic-comment-img-box">
                    <span>
                        <b v-if="commentBox.progress === 100"><?php echo __('图片审查中...','b2'); ?></b>
                        <b v-else-if="commentBox.progress !== 'success'" v-text="commentBox.progress+'%'"></b>
                        <b v-else class="topic-image-close">
                            <i class="b2font b2-close-line" @click="removeImage()"></i>
                        </b>
                    </span>
                    <img :src="commentBox.img" v-if="commentBox.img"/>
                </div>
                <div :class="['comment-smile-box',{'b2-show':smileShow}]" v-cloak @click.stop="">
                    <?php echo $html; ?>
                </div>
            </div>
        </div>
        <div class="topic-comment-right">
            <button class="text" @click="setCommentBox('#comment-box-'+opt.topicId)"><?php echo __('取消','b2'); ?></button>
            <button :disabled="commentDisabled()" @click="submitComment" :class="commentBox.locked == true ? 'b2-loading' : ''"><span><?php echo __('发布','b2'); ?></span></button>
        </div>
    </div>
</div>
<div id="topic-answer" v-cloak>
    <div id="topic-answer-form" ref="answerBox" :class="(answer.id ? ' change-bg' : '')">
        <div class="topic-answer-login" v-if="!login" v-cloak>
            <p><?php echo __('登录之后回答问题，请先登录！','b2'); ?></p>
            <div><button class="empty" @click="loginAc(1)"><?php echo __('登录','b2'); ?></button>&nbsp;&nbsp;<button @click="loginAc(2)"><?php echo __('快速注册','b2'); ?></button></div>
        </div>
        <div class="answer-tips">
            <span class="b2-color" v-if="this.answer.id"><?php echo __('编辑答案：','b2'); ?></span>
            <span v-else><?php echo __('我的回答：','b2'); ?></span>
            <span><?php echo __('最多上传一张图片和一个附件','b2'); ?></span>
        </div>
        <textarea ref="topicAnswer" placeholder="<?php echo __('请撰写您的答案','b2'); ?>"></textarea>
        <div class="answer-file" v-if="answer.image.size || answer.file.size">
            <div class="answer-file-box file-list-item b2-radius" v-if="answer.image.size">
                <span class="file-mime"><img :src="answer.image.url" v-if="answer.image.url"><b v-text="answer.image.progress+'%'" v-if="answer.image.progress !== 100 && answer.image.progress !== 0"></b></span>
                <span class="file-name" v-text="answer.image.name"></span>
                <span class="file-size" v-text="answer.image.size"></span>
                <span class="circle-img-close" @click="cleanAnswerFile('image')">x</span>
            </div>
            <div class="answer-file-box file-list-item b2-radius" v-if="answer.file.size">
                <span class="file-mime"><b v-text="answer.file.ext"></b><i v-text="answer.file.progress+'%'" v-if="answer.file.progress !== 100 && answer.file.progress !== 0"></i></span>
                <span class="file-name" v-text="answer.file.name"></span>
                <span class="file-size" v-text="answer.file.size"></span>
                <span class="circle-img-close" @click="cleanAnswerFile('file')">x</span>
            </div>
        </div>
        <div class="topic-answer-footer">
            <div class="answer-tools">
                <div class="answer-smile">
                    <label @click="answer.showSmile = !answer.showSmile" @click.stop=""><?php echo b2_get_icon('b2-emotion-laugh-line').__('表情','b2'); ?></label>
                    <div :class="['comment-smile-box',{'b2-show':answer.showSmile}]" v-cloak @click.stop="">
                        <?php echo $answer_smile; ?>
                    </div>
                </div>
                <div class="answer-image">
                    <label @click.stop="answerFileUpload($event,'image')" for="answerImageInput">
                        <?php echo b2_get_icon('b2-image-fill').__('图片','b2'); ?>
                    </label>
                    <input type="file" :disabled="answer.uploadType != ''" accept="image/jpg,image/jpeg,image/png,image/gif" @change="getAnswerFile($event,'image')" class="b2-hidden-always" id="answerImageInput"/>
                </div>
                <div>
                    <label @click.stop="answerFileUpload($event,'file')" for="answerFileInput">
                        <?php echo b2_get_icon('b2-file-3-fill').__('附件','b2'); ?>
                    </label>
                    <input type="file" :disabled="answer.uploadType != ''" accept=".txt,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" @change="getAnswerFile($event,'file')" class="b2-hidden-always" id="answerFileInput"/>
                </div>
            </div>
            <div>
                <span class="canel-answer b2-color" v-if="this.answer.id" @click="canelEdit"><?php echo __('取消编辑','b2'); ?></span>
                <button @click="submitAnswer()" :disabled="answer.locked || answer.uploadType !== ''" :class="answer.locked ? 'b2-loading' : ''">
                    <span v-if="answer.uploadType === ''"><?php echo __('提交回答','b2'); ?></span>
                    <span v-else><?php echo __('文件上传中...','b2'); ?></span>
                </button>
            </div>
        </div>
    </div>
</div>