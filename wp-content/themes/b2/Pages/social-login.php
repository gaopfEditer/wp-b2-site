<?php
get_header();

$inv = b2_get_inv_settings();

echo '<div id="juhe-page"><div id="juhe-social" class="empty-page" ref="juhebox" v-cloak>
    <div class="" v-show="locked" v-cloak>
        <h2>'.__('拉取数据中，请不要关闭此页面...','b2').'</h2>
        <div class="b2-loading button text empty social-loading"></div>
    </div>
    <div class="invitation-error" v-show="error">
        <p class="red" v-html="error"></p>
        <p><button @click="back()">'.__('返回','b2').'</button></p>
    </div>
</div></div>';

get_footer();