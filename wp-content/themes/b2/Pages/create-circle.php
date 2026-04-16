<?php
use B2\Modules\Common\Circle;
use B2\Modules\Common\User;
/**
 * 创建圈子
 */
get_header();
$tags = Circle::get_circle_tags();

$lvs = User::get_user_roles();

$setting_lvs = array();
foreach($lvs as $k => $v){
    $setting_lvs[$k] = $v['name'];
}

if(b2_get_option('verify_main','verify_allow')){
    $setting_lvs['verify'] = __('认证用户','b2');
}

$circle_name = b2_get_option('normal_custom','custom_circle_name');
$circle_owner_name = b2_get_option('normal_custom','custom_circle_owner_name');
$circle_member = b2_get_option('normal_custom','custom_circle_member_name');
?>
<div class="b2-single-content wrapper">
    <div id="create-circle" class="content-area wrapper create-circle" >
        <main id="main" class="site-main b2-radius box">
            <div class="create-circle-item">
                <h2>
                    <p><span>1</span><?php echo sprintf(__('%s所属类别','b2'),$circle_name); ?></p>
                </h2>
                <div class="create-form" :data-picked="!tags.picked ? tags.picked = '<?php echo $tags[0]; ?>' : ''">
                    <div class="create-form-item">
                        <?php 
                            if(isset($tags['error'])){
                                 echo '<p><a href="'.admin_url('admin.php?page=b2_circle_main').'" target="_blank" class="b2-color">'.$tags['error'].'</a></p>';
                            }else{
                            foreach ($tags as $k => $v) {
                        ?>
                            <button @click="tags.picked = '<?php echo $v; ?>'" :class="tags.picked === '<?php echo $v; ?>' ? 'picked b2-color' : ''"><?php echo $v; ?></button>
                        <?php
                            }
                            }
                        ?>
                    </div>
                </div>
            </div>
            <div class="create-circle-item">
                <h2>
                    <p><span>2</span><?php echo sprintf(__('%s类型','b2'),$circle_name); ?></p>
                </h2>
                <div class="create-form">
                    <div class="create-form-item">
                        <button @click="pay.type = 'free'" :class="pay.type === 'free' ? 'picked b2-color' : ''"><?php echo sprintf(__('免费%s','b2'),$circle_name); ?></button>
                        <button @click="pay.type = 'money'" :class="pay.type === 'money' ? 'picked b2-color' : ''"><?php echo sprintf(__('付费%s','b2'),$circle_name); ?></button>
                        <button @click="pay.type = 'lv'" :class="pay.type === 'lv' ? 'picked b2-color' : ''"><?php echo sprintf(__('专属%s','b2'),$circle_name); ?></button>
                    </div>
                    <p v-if="pay.type === 'free'" class="desc"><?php echo __('对象：所有用户；时效：永久有效','b2'); ?></p>
                    <p v-else-if="pay.type === 'money'" v-cloak class="desc"><?php echo sprintf(__('对象：所有用户；时效：根据购买时效而定，到期后需要续费，否则自动退出%s。用户加入%s支付的费用由%s获得。','b2'),$circle_name,$circle_name,$circle_owner_name); ?></p>
                    <p v-else-if="pay.type === 'lv'" v-cloak class="desc"><?php echo __('对象：特定用户组；时效：永久有效','b2'); ?></p>
                </div>
            </div>
            <div class="create-circle-item item-guize">
                <h2>
                    <p><span>3</span><?php echo sprintf(__('加入%s规则','b2'),$circle_name); ?></p>
                </h2>
                <div class="create-form">
                    <div v-if="pay.type === 'free'">
                        <div class="create-form-item">
                            <button @click="role.join = 'free'" :class="role.join === 'free' ? 'picked b2-color' : ''"><?php echo sprintf(__('自由加入%s','b2'),$circle_name); ?></button>
                            <button @click="role.join = 'check'" :class="role.join === 'check' ? 'picked b2-color' : ''"><?php echo sprintf(__('用户需要%s审核加入%s','b2'),$circle_owner_name,$circle_name); ?></button>
                        </div>
                    </div>
                    <div v-else-if="pay.type === 'money'" class="create-item-money" v-cloak>
                        <div class="create-form-item">
                            <ul>
                                <li>
                                    <span class="item-title"><?php echo __('永久有效','b2'); ?></span>
                                    <input type="text" v-model="role.money.permanent" placeholder="<?php echo __('请直接输入金额','b2'); ?>"/>
                                </li>
                                <li>
                                    <span class="item-title"><?php echo __('按年付费','b2'); ?></span>
                                    <input type="text" v-model="role.money.year" placeholder="<?php echo __('请直接输入金额','b2'); ?>"/>
                                </li>
                                <li>
                                    <span class="item-title"><?php echo __('半年付费','b2'); ?></span>
                                    <input type="text" v-model="role.money.halfYear" placeholder="<?php echo __('请直接输入金额','b2'); ?>"/>
                                </li>
                                <li>
                                    <span class="item-title"><?php echo __('按季付费','b2'); ?></span>
                                    <input type="text" v-model="role.money.season" placeholder="<?php echo __('请直接输入金额','b2'); ?>"/>
                                </li>
                                <li>
                                    <span class="item-title"><?php echo __('按月付费','b2'); ?></span>
                                    <input type="text" v-model="role.money.month" placeholder="<?php echo __('请直接输入金额','b2'); ?>"/>
                                </li>
                            </ul>
                        </div>
                        <p class="desc"><?php echo __('至少填写一个付费档，也可以填写多个付费档','b2'); ?></p>
                    </div>
                    <div v-else class="create-form-role-lv" v-cloak>
                        <ul>
                            <?php foreach ($setting_lvs as $k => $v) { ?>
                                <li>
                                    <label><input type="checkbox" value="<?php echo $k; ?>" v-model="role.lv"><span><?php echo $v; ?></span></label>
                                </li>
                            <?php } ?>
                        </ul>
                        <p class="desc"><?php echo sprintf(__('请选择允许加入%s的用户组','b2'),$circle_name); ?></p>
                    </div>
                </div>
            </div>
            <div class="create-circle-item">
                <h2>
                    <p><span>4</span><?php echo sprintf(__('%s隐私','b2'),$circle_name); ?></p>
                </h2>
                <div class="create-form">
                    <div class="create-form-item">
                        <button @click="read = 'public'" :class="read === 'public' ? 'picked b2-color' : ''"><?php echo sprintf(__('%s内帖子公开显示','b2'),$circle_name); ?></button>
                        <button @click="read = 'private'" :class="read === 'private' ? 'picked b2-color' : ''"><?php echo sprintf(__('%s内帖子只对%s开放','b2'),$circle_name,$circle_member); ?></button>
                    </div>
                    <p v-if="read === 'public'" class="desc"><?php echo sprintf(__('即便用户没有加入%s，也可以查看%s内帖子，同时也会在广场显示','b2'),$circle_name,$circle_name); ?></p>
                    <p v-else-if="read === 'private'" v-cloak class="desc"><?php echo sprintf(__('用户加入%s之后才能查看%s内帖子，不对外开放','b2'),$circle_name,$circle_name); ?></p>
                </div>
            </div>
            <div class="create-circle-item circle-info-box">
                <h2>
                    <p><span>5</span><?php echo sprintf(__('%s资料','b2'),$circle_name); ?></p>
                </h2>
                <div class="create-form">
                    <div>
                        <span class="item-title"><?php echo sprintf(__('%s图标','b2'),$circle_name); ?></span>
                        <label class="create-form-icon">
                            <img :src="info.icon" v-if="info.icon" v-cloak/>
                            <span class="picked-image" v-else>
                                <b v-if="info.iconUpload" v-cloak><?php echo __('上传中...','b2'); ?></b>
                                <b v-else><?php echo __('选择图片','b2'); ?></b>
                            </span>
                            <input type="file" accept="image/jpg,image/jpeg,image/png,image/gif" @change="getFile($event,'icon')" class="b2-hidden-always" ref="iconInput"/>
                        </label>
                    </div>
                    <!-- <div>
                        <span class="item-title"><?php echo __('圈子封面','b2'); ?></span>
                        <label class="create-form-cover">
                            <img :src="info.cover" v-if="info.cover" v-cloak/>
                            <span class="picked-image" v-else>
                                <b v-if="info.coverUpload" v-cloak><?php echo __('上传中...','b2'); ?></b>
                                <b v-else><?php echo __('选择图片','b2'); ?></b>
                            </span>
                            <input type="file" accept="image/jpg,image/jpeg,image/png,image/gif" @change="getFile($event,'cover')" class="b2-hidden-always" ref="coverInput"/>
                        </label>
                    </div> -->
                    <div>
                        <span class="item-title"><?php echo sprintf(__('%s名称','b2'),$circle_name); ?></span>
                        <input type="text" v-model="info.name">
                        <p class="desc"><?php echo __('介于2-10个字之间','b2'); ?></p>
                    </div>
                    <div class="create-circle-link">
                        <span class="item-title"><?php echo sprintf(__('%s别名（英文名）','b2'),$circle_name); ?></span>
                        <input v-model="info.slug" type="text">
                        <p class="desc"><?php echo sprintf(__('会在%s网址中显示，一般为%s的英文名称或拼音，只要是字母即可','b2'),$circle_name,$circle_name); ?></p>
                    </div>
                    <div>
                        <span class="item-title"><?php echo sprintf(__('%s简介','b2'),$circle_name); ?></span>
                        <textarea v-model="info.desc"></textarea>
                        <p class="desc"><?php echo __('介于10-100个字之间','b2'); ?></p>
                    </div>
                </div>
            </div>
            <div class="create-form-submit" v-cloak>
                <button @click="submit()" :class="locked ? 'b2-loading' : ''" :disabled="locked"><?php echo __('创建','b2'); ?></button>
            </div>
        </main>
    </div>
</div>
<?php
get_footer();