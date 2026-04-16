<?php 
use B2\Modules\Common\User;
$pagename = get_query_var('b2_page'); 
$circle_name = b2_get_option('normal_custom','custom_circle_name');
?>

<?php if($pagename === 'message'){ ?>
    <aside id="secondary" class="widget-area custom-page-widget">
        <div id="sidebar-innter">
            <section class="widget b2-widget-hot mg-b box b2-radius">
                <ul class="b2-widget-box">
                    <li>
                        <p><b><?php echo __('说明','b2'); ?></b></p>
                        <p><?php echo __('这里会显示您在本站的互动消息。','b2'); ?></p>
                        <p><?php echo __('参与互动，结交朋友，这是一件很棒的事情。','b2'); ?></p>
                    </li>
                    <li>
                        <p><b><?php echo __('过期消息清理时间','b2'); ?></b></p>
                        <p><?php echo __('本站已经开启了过期消息清理机制','b2'); ?></p>
                        <p><?php echo __('超过30天的已读消息会自动清理。','b2'); ?></p>
                    </li>
                </ul>
            </section>
        </div>
    </aside>
<?php } ?>

<?php if($pagename === 'write'){ ?>
    <aside id="secondary" class="widget-area custom-page-widget">
        <div id="sidebar-innter">
            <section class="widget b2-widget-hot mg-b box b2-radius">
                <ul class="b2-widget-box">
                    <li>
                        <p><b><?php echo __('尊重原创','b2'); ?></b></p>
                        <p><?php echo __('请不要发布任何盗版下载链接，包括软件、音乐、电影等等。我们尊重原创。','b2'); ?></p>
                    </li>
                    <li>
                        <p><b><?php echo __('友好互助','b2'); ?></b></p>
                        <p><?php echo __('您的文章将会有成千上万人阅读，保持对陌生人的友善，用知识去帮助别人也是一种快乐。','b2'); ?></p>
                    </li>
                    <li>
                        <p><b><?php echo __('处罚','b2'); ?></b></p>
                        <p><?php echo __('禁止发布垃圾广告','b2'); ?></p>
                        <p><?php echo __('发现垃圾广告，本站会立刻封停您的账户','b2'); ?></p>
                    </li>
                </ul>
            </section>
        </div>
    </aside>
<?php } ?>

<?php if($pagename === 'po-'.b2_get_option('normal_custom','custom_infomation_link')){ ?>
    <aside id="secondary" class="widget-area custom-page-widget">
        <div id="sidebar-innter">
            <section class="widget b2-widget-hot mg-b box b2-radius">
                <ul class="b2-widget-box">
                    <li>
                        <p><b><?php echo __('尊重原创','b2'); ?></b></p>
                        <p><?php echo __('请不要发布任何盗版下载链接，包括软件、音乐、电影等等。我们尊重原创。','b2'); ?></p>
                    </li>
                    <li>
                        <p><b><?php echo __('友好互助','b2'); ?></b></p>
                        <p><?php echo __('您的文章将会有成千上万人阅读，保持对陌生人的友善，用知识去帮助别人也是一种快乐。','b2'); ?></p>
                    </li>
                    <li>
                        <p><b><?php echo __('处罚','b2'); ?></b></p>
                        <p><?php echo __('禁止发布垃圾广告','b2'); ?></p>
                        <p><?php echo __('发现垃圾广告，本站会立刻封停您的账户','b2'); ?></p>
                    </li>
                </ul>
            </section>
        </div>
    </aside>
<?php } ?>

<?php if($pagename === 'directmessage'){ ?>
    <aside id="secondary" class="widget-area custom-page-widget">
        <div id="sidebar-innter">
            <section class="widget b2-widget-hot mg-b box b2-radius">
                <ul class="b2-widget-box">
                    <li>
                        <p><b><?php echo __('规范','b2'); ?></b></p>
                        <p><?php echo __('您可以通过私信和网站的其他人进行秘密沟通。','b2'); ?></p>
                        <p><?php echo __('沟通的过程中，请保持礼貌。','b2'); ?></p>
                        <p><?php echo __('禁止故意通过私信传播垃圾广告信息。','b2'); ?></p>
                    </li>
                    <li>
                        <p><b><?php echo __('处罚','b2'); ?></b></p>
                        <p><?php echo __('私信启用了防垃圾机制，如果我们检测到您正在发送垃圾私信，将会封停您的账户','b2'); ?></p>
                    </li>
                </ul>
            </section>
        </div>
    </aside>
<?php } ?>

<?php if($pagename === 'gold'){ ?>
    <aside id="secondary" class="widget-area custom-page-widget">
        <div id="sidebar-innter">
            <section class="widget b2-widget-hot mg-b box b2-radius">
                <ul class="b2-widget-box">
                    <li>
                        <p><b><?php echo __('如何获取积分？','b2'); ?></b></p>
                        <p><?php echo __('您可以参与本站的互动获得积分奖励，比如评论，投稿，发帖，或者冒泡等','b2'); ?></p>
                        <p><?php echo __('您可以在此处购买积分','b2'); ?></p>
                    </li>
                    <li>
                        <p><b><?php echo __('如何获得收益','b2'); ?></b></p>
                        <p><?php echo sprintf(__('您可以通过在本站发布收费内容、发布商品等方式获得收益，用户给您的打赏也将进入到您的%s之中','b2'),B2_MONEY_NAME); ?></p>
                    </li>
                </ul>
            </section>
        </div>
    </aside>
<?php } ?>

<?php if($pagename === 'task'){ ?>
    <aside id="secondary" class="widget-area custom-page-widget">
        <div id="sidebar-innter">
            <section class="widget b2-widget-hot mg-b box b2-radius">
                <ul class="b2-widget-box">
                    <li>
                        <p><b><?php echo __('完成任务的奖励','b2'); ?></b></p>
                        <p><?php echo __('您在网站上的互动都将得到积分奖励，通过积分的增长，您的等级也会得到提升','b2'); ?></p>
                    </li>
                    <li>
                        <p><b><?php echo __('奖励规则','b2'); ?></b></p>
                        <p><?php echo __('并不是每次互动都会得到奖励，如果您今天的任务次数已经达成，将不会再获得积分奖励，不过对您在网站上的互动没有任何影响','b2'); ?></p>
                    </li>
                </ul>
            </section>
        </div>
    </aside>
<?php } ?>

<?php if($pagename === 'gold-top'){ ?>
    <aside id="secondary" class="widget-area custom-page-widget">
        <div id="sidebar-innter">
            <section class="widget mg-b box b2-radius">
                <ul class="b2-widget-box">
                    <li>
                        <p><b><?php echo __('排名规则','b2'); ?></b></p>
                        <p><?php echo __('根据现有积分数据排名，取前15名。','b2'); ?></p>
                    </li>
                    <li>
                        <p><b><?php echo __('如何获取积分？','b2'); ?></b></p>
                        <p><?php echo __('1、您可以参与本站的互动获得积分奖励，比如评论，投稿等','b2'); ?></p>
                        <p><?php echo sprintf(__('2、%s1兑换%s积分','b2'),B2_MONEY_SYMBOL,b2_get_option('normal_gold','credit_dh')); ?></p>
                    </li>
                    <li>
                        <a class="button empty" style="display:inline-block" href="<?php echo b2_get_custom_page_url('gold'); ?>"><?php echo __('积分购买','b2'); ?></a>
                    </li>
                </ul>
            </section>
        </div>
    </aside>
<?php } ?>

<?php if($pagename === 'requests'){ ?>
    <aside id="secondary" class="widget-area custom-page-widget">
        <div id="sidebar-innter">
            <section class="widget mg-b box b2-radius">
                <ul class="b2-widget-box">
                    <li>
                        <p><b><?php echo __('注意事项','b2'); ?></b></p>
                        <p><?php echo __('1、请详细描述您的问题，最好携带问题相关的截图与网址连接，方便我们进一步了解情况。','b2'); ?></p>
                        <p><?php echo __('2、只接受与本站相关的问题，其他问题不予回答。','b2'); ?></p>
                    </li>
                </ul>
            </section>
        </div>
    </aside>
<?php } ?>

<?php if($pagename === 'dark-room'){ ?>
    <aside id="secondary" class="widget-area custom-page-widget">
        <div id="sidebar-innter">
            <section class="widget mg-b box b2-radius">
                <ul class="b2-widget-box">
                    <li>
                        <p><b><?php echo __('为什么会被关进小黑屋？','b2'); ?></b></p>
                        <p><?php echo __('1、故意频繁请求网站接口；','b2'); ?></p>
                        <p><?php echo __('2、威胁网站安全；','b2'); ?></p>
                        <p><?php echo __('3、违反国家相关法律和道德要求；','b2'); ?></p>
                        <p><?php echo __('4、谩骂他人以及对他人进行言语攻击；','b2'); ?></p>
                        <p><?php echo __('5、发布带有广告性质的内容；','b2'); ?></p>
                    </li>
                </ul>
            </section>
        </div>
    </aside>
<?php } ?>

<?php if($pagename === 'po-'.b2_get_option('normal_custom','custom_ask_link')){ ?>
    <aside id="secondary" class="widget-area custom-page-widget">
        <div id="sidebar-innter">
            <section class="widget mg-b box b2-radius">
                <ul class="b2-widget-box">
                    <li>
                        <p><b><?php echo __('为什么会被关进小黑屋？','b2'); ?></b></p>
                        <p><?php echo __('1、故意频繁请求网站接口；','b2'); ?></p>
                        <p><?php echo __('2、威胁网站安全；','b2'); ?></p>
                        <p><?php echo __('3、违反国家相关法律和道德要求；','b2'); ?></p>
                        <p><?php echo __('4、谩骂他人以及对他人进行言语攻击；','b2'); ?></p>
                        <p><?php echo __('5、发布带有广告性质的内容；','b2'); ?></p>
                    </li>
                </ul>
            </section>
        </div>
    </aside>
<?php } ?>

<?php if($pagename === 'all-'.b2_get_option('normal_custom','custom_circle_link').'s'){ ?>
    <aside id="secondary" class="widget-area custom-page-widget">
        <div id="sidebar-innter">
            <section class="widget mg-b box b2-radius">
                <ul class="b2-widget-box">
                    <li>
                        <p><b><?php echo sprintf(__('如何创建%s？','b2'),$circle_name); ?></b></p>
                        <p><?php echo sprintf(__('1、您有时间管理%s；','b2'),$circle_name); ?></p>
                        <p><?php echo sprintf(__('2、对您创建的%s内容有一定的专业性；','b2'),$circle_name); ?></p>
                        <p><?php echo sprintf(__('3、拥有本站创建%s的权限；','b2'),$circle_name); ?></p>
                    </li>
                </ul>
                <div class="circle-widget-button"> <button class="text" onclick="postPoBox.go('<?php echo B2_HOME_URI.'/create-circle'; ?>','create_circle')"><?php echo sprintf(__('创建%s','b2'),$circle_name); ?></button></div>
            </section>
        </div>
    </aside>
<?php } ?>

<?php if($pagename === 'distribution'){ 
    $role = (int)b2_get_option('distribution_main','distribution_conditions');
?>
    <aside id="secondary" class="widget-area custom-page-widget">
        <div id="sidebar-innter">
            <section class="widget mg-b box b2-radius">
                <ul class="b2-widget-box">
                    <li>
                        <p><b><?php echo __('如何成为合作伙伴','b2'); ?></b></p>
                        <?php if($role === 0){ ?>
                            <p><?php echo __('注册用户均自动拥有推广权限。','b2'); ?></p>
                            </p><?php echo __('通过您的连接注册的用户也将自动成为您的合作伙伴，你的伙伴消费后您也可获得推广佣金。','b2'); ?></p>
                        <?php } ?>
                        <?php if($role === 1){ ?>
                            <p><?php echo __('认证用户拥有推广权限','b2'); ?></p>
                            <p><a class="button" href="<?php echo b2_get_custom_page_url('verify'); ?>" target="_blank"><?php echo __('前往认证','b2'); ?></a></p>
                        <?php } ?>
                        <?php if($role === 2){ 
                            $users = b2_get_option('distribution_main','distribution_user_lv'); 
                            if(empty($users)){
                                ?>
                                <p><?php echo __('未指定允许推广的用户组','b2'); ?></p>
                                <?php
                            }else{
                        ?>
                            <p><?php echo __('限制以下用户拥有推广权限','b2'); ?></p>
                            <div>
                                <?php 
                                    $lvs = '';
                                    foreach ($users as $k => $v) {
                                        $lvs .= User::get_lv_icon($v);
                                    }
                                    echo $lvs;
                                ?>
                            </div>
                        <?php }} ?>
                        <?php if($role === 3) { ?>
                            <p><?php echo __('请联系管理员获得推广权限','b2'); ?></p>
                        <?php } ?>
                    </li>
                    <li>
                        <p><b><?php echo __('如何获得收益？','b2'); ?></b></p>
                        <p><?php echo sprintf(__('您的伙伴消费后，您将获得相应比例的推广提成，进入到您的%s中','b2'),B2_MONEY_NAME); ?></p>
                    </li>
                    <li>
                        <p><b><?php echo __('如何提现','b2'); ?></b></p>
                        <p><?php echo sprintf(__('财富中心有您的%s记录，详细记录了每笔收付账单，您可以在财富中心进行提现操作','b2'),B2_MONEY_NAME); ?></p>
                    </li>
                </ul>
            </section>
        </div>
    </aside>
<?php } ?>