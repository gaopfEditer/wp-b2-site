<div class="user-inv-page b2-radius b2-pd box entry-content" id="user-inv-page" ref="invpage">
    <div class="button empty b2-loading empty-page text" v-if="!invList && none == false"></div>
    <table class="wp-list-table widefat fixed striped shop_page_order_option" v-else-if="invList && none == false" v-cloak>
        <thead>
            <tr><td class="tab-mobile-hidden"><?php echo __('编号','b2'); ?></td><td class="inv-page-code"><?php echo __('邀请码','b2'); ?></td><td><?php echo __('奖励','b2'); ?></td><td><?php echo __('使用状态','b2'); ?></td><td><?php echo __('使用者','b2'); ?></td></tr>
        </thead>
        <tbody>
        <tr v-for="(inv,index) in invList">
            <td v-text="index+1" class="tab-mobile-hidden"></td>
            <td class="inv-page-code" v-text="inv.inv_code"></td>
            <td><?php echo b2_get_icon('b2-coin-line'); ?><span v-text="inv.credit"></span></td>
            <td><span style="color:green" v-if="inv.status == 1"><?php echo __('已使用','b2'); ?></span><span style="color:red" v-else><?php echo __('未使用','b2'); ?></span></td>
            <td>
                <span v-if="inv.user == 0"><?php echo __('未使用','b2'); ?></span>
                <span v-else><a :href="inv.user.link" v-text="inv.user.name"></a></span>
            </td>
        </tr>
        </tbody>
    </table>
    <div class="b2-radius b2-pd box" v-cloak v-else>
        <?php echo B2_EMPTY; ?>
    </div>
</div>