<div class="author-edit-page box b2-radius" id="author-edit-page" ref="authorEdit">
    <h2><?php echo __('基本资料','b2'); ?></h2>
    <ul class="author-edit-list">
        <li v-if="userData.check_type == 'text' || userData.check_type == 'luo'" v-cloak>
            <div class="edit-name"><?php echo __('登录用户名','b2'); ?></div>
            <div class="edit-item">
                <div class="edit-input">
                    <input type="text" v-model="userData.login" disabled>
                </div>
                <p class="setting-des"><?php echo __('登录用户名，不可修改','b2'); ?></p>
            </div>
        </li>
        <li>
            <div class="edit-name"><?php echo __('昵称','b2'); ?></div>
            <div class="edit-item">
                <div class="edit-value" v-if="!show.nickname"><span v-text="userData.display_name"></span><span class="user-edit-button" @click="show.nickname = true"><?php echo b2_get_icon('b2-edit-2-line').__('编辑','b2'); ?></span></div>
                <div class="edit-input" v-else v-cloak>
                    <input type="text" v-model="userData.display_name">
                    <p class="setting-des"><?php echo __('中文、英文或数字','b2'); ?></p>
                    <div class="edit-button"><button class="empty" @click="show.nickname = false"><?php echo __('取消','b2'); ?></button><button @click="saveNickName()"><?php echo __('保存','b2'); ?></button></div>
                </div>
            </div>
        </li>
        <li>
            <div class="edit-name"><?php echo __('性别','b2'); ?></div>
            <div class="edit-item">
                <div class="edit-value" v-if="!show.sex"><span v-text="userData.sex == 1 ? '<?php echo __('男','b2'); ?>' : '<?php echo __('女','b2'); ?>'"></span><span class="user-edit-button" @click="show.sex = true"><?php echo b2_get_icon('b2-edit-2-line').__('编辑','b2'); ?></span></div>
                <div class="edit-input setting-sex" v-else v-cloak>
                    <span><label><input type="radio" v-model="userData.sex" value="1"><?php echo __('男','b2'); ?></label></span>
                    <span><label><input type="radio" v-model="userData.sex" value="0"><?php echo __('女','b2'); ?></label></span>
                    <div class="edit-button"><button class="empty" @click="show.sex = false"><?php echo __('取消','b2'); ?></button><button @click="saveSex()"><?php echo __('保存','b2'); ?></button></div>
                </div>
            </div>
        </li>
        <li>
            <div class="edit-name"><?php echo __('网址','b2'); ?></div>
            <div class="edit-item">
                <div class="edit-value" v-if="!show.url"><span v-text="userData.url"></span><span class="user-edit-button" @click="show.url = true"><?php echo b2_get_icon('b2-edit-2-line').__('编辑','b2'); ?></span></div>
                <div class="edit-input" v-else v-cloak>
                    <input type="text" v-model="userData.url" placeholder="<?php echo __('您的网址','b2'); ?>">
                    <div class="edit-button"><button class="empty" @click="show.url = false"><?php echo __('取消','b2'); ?></button><button @click="saveUrl()"><?php echo __('保存','b2'); ?></button></div>
                </div>
            </div>
        </li>
        <li>
            <div class="edit-name"><?php echo __('一句话介绍自己','b2'); ?></div>
            <div class="edit-item">
                <div class="edit-value" v-if="!show.desc"><span v-html="userData.desc ? userData.desc : '<?php echo __('这人很懒什么都没留下','b2'); ?>'"></span><span class="user-edit-button" @click="show.desc = true"><?php echo b2_get_icon('b2-edit-2-line').__('编辑','b2'); ?></span></div>
                <div class="edit-input" v-else v-cloak>
                    <input type="text" v-model="userData.desc" placeholder="<?php echo __('一句话介绍自己','b2'); ?>">
                    <div class="edit-button"><button class="empty" @click="show.desc = false"><?php echo __('取消','b2'); ?></button><button @click="saveDesc()"><?php echo __('保存','b2'); ?></button></div>
                </div>
            </div>
        </li>
        <li>
            <div class="edit-name"><?php echo __('收货地址','b2'); ?></div>
            <div class="edit-item">
                <div class="edit-value user-address-list" v-if="userData.address">
                    <div class="user-address-title" v-if="userData.default_address"><?php echo __('默认地址：','b2'); ?></div>
                    <ul v-if="userData.default_address">
                        <li>
                            <div class="user-address-info address-default">    
                                <span v-text="userData.address[userData.default_address].province ? userData.address[userData.default_address].province+' '+userData.address[userData.default_address].city+' '+userData.address[userData.default_address].county+' '+userData.address[userData.default_address].address : userData.address[userData.default_address].address"></span>
                                <span v-text="userData.address[userData.default_address].name" class="mar10-l red"></span>
                                <span v-text="userData.address[userData.default_address].phone" class="mar10-l"></span>
                                <div class="pos-a">
                                    <button class="text" @click="deleteAddress(userData.default_address)"><?php echo __('删除','b2'); ?></button>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div class="user-address-title" v-if="addressLength() > 1"><?php echo __('备用地址：','b2'); ?></div>
                    <ul>
                        <li v-for="(address,key,index) in userData.address" v-if="userData.default_address != key">
                            <div class="user-address-info">    
                                <span v-text="address.province ? address.province+' '+address.city+' '+address.county+' '+address.address : address.address"></span>
                                <span v-text="address.name"></span>
                                <span v-text="address.phone"></span>
                                <div class="pos-a">
                                    <button class="text" @click="saveDefaultAddress(key)"><?php echo __('设为默认','b2'); ?></button><button class="text" @click="deleteAddress(key)"><?php echo __('删除','b2'); ?></button>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <button class="empty setting-des-add" @click="show.address = true" v-show="!show.address"><?php echo b2_get_icon('b2-add-line').' '.__('添加收货地址','b2'); ?></button>
                </div>
                <div class="edit-input address-input" v-if="show.address" v-cloak>
                    <input type="text" v-model="addresses.address" placeholder="<?php echo __('地址','b2'); ?>">
                    <input type="text" v-model="addresses.name" placeholder="<?php echo __('姓名','b2'); ?>">
                    <input type="text" v-model="addresses.phone" placeholder="<?php echo __('电话','b2'); ?>">
                    <div class="edit-button"><button class="empty" @click="show.address = false"><?php echo __('取消','b2'); ?></button><button @click="saveAddress()"><?php echo __('添加','b2'); ?></button></div>
                </div>
                <p class="setting-des"><?php echo __('如果您在本站购物，请务必填写此项，以便发货！','b2'); ?></p>
            </div>
        </li>
        <li v-if="userData.check_type == 'tel' || userData.check_type == 'telandemail'">
            <div class="edit-name"><?php echo __('绑定手机','b2'); ?></div>
            <div class="edit-item">
                <div class="edit-value" v-if="!show.phone"><span v-text="userData.phone"></span><span class="user-edit-button" @click="show.phone = true"><?php echo b2_get_icon('b2-edit-2-line').__('编辑','b2'); ?></span></div>
                <div class="edit-input" v-else v-cloak>
                    <input type="text" v-model="userData.phone" placeholder="<?php echo __('手机号码','b2'); ?>">
                    <div class="user-settings-code">
                        <input type="text" v-model="data.code" placeholder="<?php echo __('验证码','b2'); ?>">
                        <button class="empty" @click.stop.prevent="!SMSLocked && count == 60 ? checkCode('phone') : ''">{{count < 60 ? count+'<?php echo __('秒后可重发', 'b2'); ?>' : '<?php echo __('发送验证码', 'b2'); ?> '}}</button>
                    </div>
                    <div class="edit-button"><button class="empty" @click="show.phone = false"><?php echo __('取消','b2'); ?></button><button @click="saveUsername()"><?php echo __('保存','b2'); ?></button></div>
                </div>
                <p class="setting-des"><?php echo __('手机号码可用作登录','b2'); ?></p>
            </div>
        </li>
        <li v-if="userData.check_type != 'tel'">
            <div class="edit-name"><?php echo __('绑定邮箱','b2'); ?></div>
            <div class="edit-item">
                <div class="edit-value" v-if="!show.email"><span v-text="userData.email"></span><span class="user-edit-button" @click="show.email = true"><?php echo b2_get_icon('b2-edit-2-line').__('编辑','b2'); ?></span></div>
                <div class="edit-input" v-else v-cloak>
                    <input type="text" v-model="userData.email" placeholder="<?php echo __('邮箱','b2'); ?>">
                    <div class="user-settings-code">
                        <input type="text" v-model="data.code" placeholder="<?php echo __('验证码','b2'); ?>">
                        <button class="empty" @click.stop.prevent="!SMSLocked && count == 60 ? checkCode('email') : ''">{{count < 60 ? count+'<?php echo __('秒后可重发', 'b2'); ?>' : '<?php echo __('发送验证码', 'b2'); ?> '}}</button>
                    </div>
                    <div class="edit-button"><button class="empty" @click="show.email = false"><?php echo __('取消','b2'); ?></button><button @click="saveUsername()"><?php echo __('保存','b2'); ?></button></div>
                </div>
                <p class="setting-des"><?php echo __('邮箱可用作登录','b2'); ?></p>
            </div>
        </li>
        <input type="text" style="position: absolute;left: -999999px;" />
        <li v-if="userData.email || userData.phone">
            <div class="edit-name"><?php echo __('修改密码','b2'); ?></div>
            <div class="edit-item user-setting-password">
                <div class="edit-value" v-if="!show.password"><span class="user-edit-button" @click="show.password = true"><?php echo b2_get_icon('b2-edit-2-line').__('修改','b2'); ?></span></div>
                <div class="edit-input reset-pass" v-else v-cloak>
                    <input type="password" placeholder="<?php echo __('新密码','b2'); ?>" v-model="userData.password">
                    <input type="password" placeholder="<?php echo __('重复新密码','b2'); ?>" v-model="userData.repassword">
                    <div class="edit-button"><button class="empty" @click="show.password = false"><?php echo __('取消','b2'); ?></button><button @click="editPass()"><?php echo __('保存','b2'); ?></button></div>
                </div>
                <p class="setting-des"><?php echo __('请确保两次密码一致，并且密码大于6个字符！修改密码后请重新登录','b2'); ?></p>
            </div>
        </li>
    </ul>
    <h2><?php echo __('头像选择','b2'); ?></h2>
    <ul class="user-avatar-chose">
        <li v-for="(open,key,index) in userData.open" v-if="open.avatar && open.open" class="b2-radius" @click="changeAvatar(key)">
            <img :src="open.avatar">
            <span class="avatar-set" v-if="key == avatarType"><?php echo b2_get_icon('b2-check-line'); ?></span>
        </li>
    </ul>
    <h2 v-show="userData.open.qq.open || userData.open.weibo.open || userData.open.weixin.open"><?php echo __('社交绑定','b2'); ?></h2>
    <ul class="user-open-list">
        <li v-for="(open,key,index) in userData.open" v-if="key != 'default' && open.open">
            <div class="user-open-avatar b2-radius">
                <img :src="open.avatar" class="avatar" v-if="open.avatar"/>
            </div>
            <div class="">
                <div class="user-open-name" v-text="open.name">
                </div>
                <div class="user-open-bind">
                    <span v-if="open.isset" class="green"><?php echo __('已绑定','b2'); ?></span>
                    <span v-else class="red"><?php echo __('未绑定','b2'); ?></span>
                </div>
                <div class="user-open-button">
                    <button v-if="open.isset" class="empty" @click="unBuild(key)"><?php echo __('解除绑定','b2'); ?></button>
                    <a v-else class="button" :href="open.url" @click="markHistory(key)"><?php echo __('添加绑定','b2'); ?></a>
                </div>
            </div>
        </li>
    </ul>
    <h2><?php echo __('上传收款码','b2'); ?></h2>
    <div class="user-qrcode">
        <div class="weixin-qrcode">
            <img :src="userData.qrcode_weixin" v-if="userData.qrcode_weixin">
            <label><i><?php echo __('微信','b2'); ?></i><input type="file" class="b2-hidden" @change="getFile($event,'weixin')" ref="weixin"></label>
        </div>
        <div class="alipay-qrcode">
            <img :src="userData.qrcode_alipay" v-if="userData.qrcode_alipay">
            <label><i><?php echo __('支付宝','b2'); ?></i><input type="file" class="b2-hidden" @change="getFile($event,'alipay')" ref="alipay"></label>
        </div>
    </div>
    <p class="setting-des"><?php echo __('如果您需要提现，我们会通过此二维码进行转账。','b2'); ?></p>
</div>