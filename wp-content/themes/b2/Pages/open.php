<?php
use B2\Modules\Common\OAuth;

get_header();

$inv = b2_get_inv_settings();

echo '<div id="open-page"><div id="social-box" class="social-box empty-page" ref="socialBox" v-cloak>
    <div class="" v-show="locked && data.token == \'\'" v-cloak>
        <h2>'.__('拉取数据中，请不要关闭此页面...','b2').'</h2>
        <div class="b2-loading button text empty social-loading"></div>
    </div>
    <div class="invitation-box b2-radius box" v-show="type === \'invitation\'" v-cloak>
        <p>'.b2_get_icon('b2-gift-2-line').'</p>
        <p>'.($inv['type'] == 1 ? __('填写邀请码，有好礼相送！','b2') : __('注册必须使用邀请码','b2')).'</p>
        <p class="invitation-tips">'.__('请输入邀请码','b2').'</p>
        <input type="text" v-model="data.invitation"/>
        <div class="invitation-box-button">
            <div class=""><a href="'.$inv['link'].'" target="_blank">'.$inv['text'].'</a></div>
            <div>
            '.($inv['type'] == 1 ? '<button :class="[\'empty\',{\'b2-loading\':locked == \'pass\'}]" @click="invRegeister(\'pass\')" :disabled="locked">'.__('跳过','b2').'</button>' : '').'
            <button class="locked == \'sub\' ? \'b2-loading\' ; \'\'" @click="invRegeister(\'sub\')" :disabled="locked">'.__('提交','b2').'</button>
            </div>
        </div>
    </div>
    <div class="invitation-error" v-show="error">
        <p class="red" v-html="error"></p>
        <div class="oauth-tips" v-if="oauth !== \'\'">
            <div @click="loginOut(oauth[type].url)" class="box b2-pd mg-b">
                <h2>切换登录到名为<span v-html="name" class="green"></span>的账户</h2>
                <p>使用<span v-html="name"></span>的身份重新登录</p>
            </div>
        </div>
        <p><button @click="back()">'.__('返回','b2').'</button></p>
    </div>
</div></div>';

get_footer();