<?php
/*
 * @Author: 子比主题老唐-Qinver
 * @Url: zibll.com
 * @Date: 2023-10-12 12:18:41
 * @LastEditTime: 2023-11-06 21:46:47
 * @Read me : 本工具为免费开源工具，您可以自由使用和分享，如您需要分享或二次开发，请务必保留并在显眼处体现作者名，以及www.zibll.com的网站
 * @Read me : 不要用于任何商业用途，否则将依法追究相关责任，谢谢合作！
 */

namespace zib\sql;

class SqlReplace
{
    public $plugin_name     = '';
    public $admin_page_suge = 'sql-batch-replace';

    public function __construct()
    {
        add_action('admin_print_footer_scripts', [$this, 'home_url_remind']);
        add_action('admin_menu', [$this, 'admin_menu']);
        add_filter('plugin_action_links_' . SQL_REPLACE_PLUGIN_BASENAME, [$this, 'action_links']);

        add_action('wp_ajax_sql_replace_next', [$this, 'ajax_next']);
        add_action('wp_ajax_sql_replace_submit', [$this, 'ajax_submit']);

    }

    //提醒
    public function home_url_remind()
    {
        if (!isset($GLOBALS['pagenow']) || 'options-general.php' !== $GLOBALS['pagenow']) {
            return;
        }

        $home_url = home_url();

        echo '<style>
        .admin-url-set-warning{
            display:none;
        }</style>
        <script>
        (function ($, document) {$(document).ready(function ($) {
            var admin_options_url_input = $(\'.options-general-php input#home\');
            if (admin_options_url_input.length) {
                var html = \'<div style="color: #0068f0;background: #eaf4fb;padding: 10px 20px;border-radius: 6px;border: 1px solid #bde6ff;margin-top: 6px;"><div><div style="margin-bottom: 10px;">' . __('请使用数据库批量替换插件修改网站地址，一键完美替换', SQL_REPLACE_TEXT_DOMAIN) . '</div><a class="button button-primary" href="' . admin_url('options-general.php?page=sql-batch-replace&old=' . $home_url) . '">' . __('立即修改', SQL_REPLACE_TEXT_DOMAIN) . '</a></div></div>\';
                admin_options_url_input.after(html);
                admin_options_url_input.attr(\'disabled\',true);
                $(\'.options-general-php input#siteurl\').attr(\'disabled\',true);
            }
        });})(jQuery, document);
        </script>';
    }

    public function admin_menu()
    {
        if (is_multisite() && (!is_main_site() || !is_super_admin())) {
            return;
        }
        $menu = __('数据库批量替换', SQL_REPLACE_TEXT_DOMAIN);
        add_options_page($menu, $menu, 'manage_options', $this->admin_page_suge, [$this, 'admin_page']);

    }

    public function action_links($links)
    {
        if (is_multisite() && (!is_main_site() || !is_super_admin())) {
            return $links;
        }

        $osslink = array('<a href="options-general.php?page=' . $this->admin_page_suge . '">' . __('开始替换', SQL_REPLACE_TEXT_DOMAIN) . '</a>');
        return array_merge($osslink, $links);
    }

    public function script_json()
    {
        $data = array(
            'ajax_url'    => admin_url('admin-ajax.php'),
            'nonce'       => wp_create_nonce('sql-replace-nonce'),
            'replace_url' => admin_url('admin-ajax.php?action=sql-replace'),
            'lang'        => array(
                'replace_reminder'        => __('请确认将{old}替换为{new}', SQL_REPLACE_TEXT_DOMAIN),
                'replace_count_reminder'  => __('共计替换{count}条数据，替换后无法撤回，请确保已备份数据库！', SQL_REPLACE_TEXT_DOMAIN),
                'query_title'             => __('替换明细：表 => 列 => 数量 || 序列化数量', SQL_REPLACE_TEXT_DOMAIN),
                'requery_btn'             => __('重新查询', SQL_REPLACE_TEXT_DOMAIN),
                'not_reminder'            => __('没有找到匹配的替换内容', SQL_REPLACE_TEXT_DOMAIN),
                'reconfirm_reminder'      => __('请再次确认替换【{old}】为【{new}】吗？', SQL_REPLACE_TEXT_DOMAIN),
                'parameter_error'         => __('替换参数错误，请刷新后重试', SQL_REPLACE_TEXT_DOMAIN),
                'loading_text'            => __('正在批量替换中，请稍后...', SQL_REPLACE_TEXT_DOMAIN),
                'replace_title'           => __('数据替换明细：表 => 列 => 数量', SQL_REPLACE_TEXT_DOMAIN),
                'replace_serialize_title' => __('序列化数据替换明细：表 => 列 => 数量', SQL_REPLACE_TEXT_DOMAIN),
                'replace_success_title'   => __('操作成功', SQL_REPLACE_TEXT_DOMAIN),
                'replace_success_text'    => __('已将{old}替换为{new}', SQL_REPLACE_TEXT_DOMAIN),
            ),
        );

        return '<script>_sql = ' . json_encode($data) . '</script>';
    }

    public function admin_page()
    {

        $old_val = !empty($_GET['old']) ? $_GET['old'] : '';

        $css_link = '<link rel="stylesheet" href="' . SQL_REPLACE_URL . '/assets/css/main.min.css?ver=' . SQL_REPLACE_VERSION . '">'; //main
        $js_link  = $this->script_json();
        $js_link .= '<script src="' . SQL_REPLACE_URL . '/assets/js/main.min.js?ver=' . SQL_REPLACE_VERSION . '"></>';

        $input = '<div class="">
                    <div class="sql-st">' . __('将', SQL_REPLACE_TEXT_DOMAIN) . '</div>
                    <input class="sql-input-old" type="text" name="old" value="' . $old_val . '">
                </div>
                <div class="">
                <div class="sql-st">' . __('替换为', SQL_REPLACE_TEXT_DOMAIN) . '</div>
                    <input class="sql-input-new" type="text" name="new" value="">
                </div>';

        $desc = __('快速的批量替换数据库中的旧内容为新的内容，适用于网站换域名、加证书后网址改为https、更换云储存后批量更换媒体链接等操作', SQL_REPLACE_TEXT_DOMAIN) . '
        <br>' . __('操作仅需两步，全程自动化，快捷方便！', SQL_REPLACE_TEXT_DOMAIN) . '
        <br>' . __('工具会自动分析数据库表，支持所有主题及插件！', SQL_REPLACE_TEXT_DOMAIN) . '
        <br>' . __('自动判断序列化数据，序列化数据自动转义并替换，确保数据安全，真正意义上解决SQL命令批量替换会出现的各种bug！', SQL_REPLACE_TEXT_DOMAIN) . ' <a target="_blank" href="https://www.zibll.com/18629.html" class="sql-ad">' . __('【了解相关原理】', SQL_REPLACE_TEXT_DOMAIN) . '</a>';

        $warning_desc = '<div calss="">' . __('注意事项：', SQL_REPLACE_TEXT_DOMAIN) . '</div>';
        $warning_desc .= '<div calss="">' . __('1.使用此工具前，请务必先备份数据库！！！！以避免操作错误带来的损失', SQL_REPLACE_TEXT_DOMAIN) . '</div>';
        $warning_desc .= '<div calss="">' . __('2.替换时，请勿输入很短的替换值，容易误修改', SQL_REPLACE_TEXT_DOMAIN) . '</div>';
        $warning_desc .= '<div calss="">' . __('3.此工具仅批量修改数据库内容，对修改后会导致的结果无法判断，如果您不清楚您的操作会带来什么结果，请谨慎操作', SQL_REPLACE_TEXT_DOMAIN) . '</div>';
        $warning_desc .= '<div calss="">' . __('4.此工具可无脑使用，但仍建议先了解数据库相关原理', SQL_REPLACE_TEXT_DOMAIN) . '<a target="_blank" href="https://www.zibll.com/18629.html" class="sql-ad">' . __('【了解相关原理】', SQL_REPLACE_TEXT_DOMAIN) . '</a></div>';

        $warning_desc = '<div class="sql-warning">' . $warning_desc . '</div>';
        $input        = '<div class="sql-ks">' . __('开始替换', SQL_REPLACE_TEXT_DOMAIN) . '</div><div class="sql-input">' . $input . '</div>';
        $input .= '<div class="sql-btns"><a href="javascript:;" class="sql-btn sql-next">' . __('下一步', SQL_REPLACE_TEXT_DOMAIN) . '</a><a href="javascript:;" class="sql-btn sql-submit" style="display: none;">' . __('确认替换', SQL_REPLACE_TEXT_DOMAIN) . '</a></div>';

        $input .= '<div class="sql-notice">' . $desc . '</div>';
        $html = '<div class="wrap"><div class="sql-replace-wrap"><div class="sql-header"><div class="sql-titel">' . __('数据库批量替换小工具', SQL_REPLACE_TEXT_DOMAIN) . '</div><div class="sql-by">By：zibll-老唐 | <a target="_blank" href="https://www.zibll.com/19369.html">' . __('查看官方教程', SQL_REPLACE_TEXT_DOMAIN) . '</a></div></div>' . $warning_desc . '<div class="sql-content-wrap">' . $input . '</div><div class="sql-footer"><a target="_blank" href="https://www.zibll.com" class="sql-ad">AD：' . __('zibll子比主题是一款功能强大、设计精美的资讯、商城、社区、论坛主题，如果您还不知道，那就OUT了，点此了解详情', SQL_REPLACE_TEXT_DOMAIN) . '</a></div></div></div>';

        echo $css_link;
        echo $html;
        echo $js_link;
    }

    public function get_db_table_args()
    {

        global $wpdb;

        $args = array(
            array(
                'table_name'  => $wpdb->posts,
                'column_name' => 'post_content',
            ),
            array(
                'table_name'  => $wpdb->posts,
                'column_name' => 'guid',
            ),
            array(
                'table_name'  => $wpdb->posts,
                'column_name' => 'post_excerpt',
            ),
            array(
                'table_name'  => $wpdb->posts,
                'column_name' => 'post_title',
            ),
            array(
                'table_name'  => $wpdb->postmeta,
                'column_name' => 'meta_value',
            ),
            array(
                'table_name'  => $wpdb->comments,
                'column_name' => 'comment_content',
            ),
            array(
                'table_name'  => $wpdb->comments,
                'column_name' => 'comment_author_url',
            ),
            array(
                'table_name'  => $wpdb->commentmeta,
                'column_name' => 'meta_value',
            ),
            array(
                'table_name'  => $wpdb->users,
                'column_name' => 'user_url',
            ),
            array(
                'table_name'  => $wpdb->usermeta,
                'column_name' => 'meta_value',
            ),
            array(
                'table_name'  => $wpdb->termmeta,
                'column_name' => 'meta_value',
            ),
            array(
                'table_name'  => $wpdb->term_taxonomy,
                'column_name' => 'description',
            ),
        );

        //排除
        $eliminate_table_names   = array_column($args, 'table_name');
        $eliminate_table_names[] = $wpdb->options;
        //获取其他
        $sql    = "SELECT t.TABLE_NAME as table_name ,c.COLUMN_NAME as column_name FROM information_schema.TABLES t,INFORMATION_SCHEMA.Columns c WHERE c.TABLE_NAME=t.TABLE_NAME AND c.TABLE_SCHEMA = '" . DB_NAME . "' AND c.DATA_TYPE in ('longtext','text') AND t.TABLE_NAME NOT IN ('" . implode("','", $eliminate_table_names) . "')";
        $result = $wpdb->get_results($sql);
        if ($result) {
            $args = array_merge($args, $result);
        }

        $args[] = array(
            'table_name'  => $wpdb->options, //
            'column_name' => 'option_value',
        );

        return $args;
    }

    //执行替换数据库
    public function db_replace($old, $new)
    {
        global $wpdb;
        $is_str_strlen_same = strlen($old) === strlen($new);
        $table_args         = $this->get_db_table_args();

        $data = array(
            'serialize' => array(
                'count'  => 0,
                'detail' => array(),
            ), //序列化数据
            'routine'   => array(
                'count'  => 0,
                'detail' => array(),
            ), //普通常规数据
        );

        //如果前后字串符数量不同，则先执行序列化数据替换。
        if (!$is_str_strlen_same) {
            $data['serialize'] = $this->db_replace_serialize($old, $new);

            //如果由于超时，没有全部替换完成，则先返回，等待浏览器再次发送请求
            if (!empty($data['time_over'])) {
                $data['time_over'] = true;
                return $data;
            }
        }

        //处理非序列化数据，或者前后字符串数量一致的
        foreach ($table_args as $args) {
            $args      = (array) $args;
            $s_name    = $args['table_name'];
            $s_columns = $args['column_name'];
            $sql       = "UPDATE `$s_name` SET `$s_columns` = replace(`$s_columns`,'$old','$new')";
            if (!$is_str_strlen_same) {
                $sql .= " WHERE `$s_columns` NOT LIKE '%{%' and `$s_columns` NOT LIKE '%}'";
            }

            $count = $wpdb->query($sql);

            if ($count) {
                $data['routine']['detail'][] = array(
                    'table_name'  => $s_name,
                    'column_name' => $s_columns,
                    'count'       => $count,
                    'sql'         => $sql,
                );
                $data['routine']['count'] += $count;
            }
        }

        return $data;
    }

    //替换数据库中序列化字符串
    public function db_replace_serialize($old, $new)
    {

        global $wpdb;
        $time_over_second = 10; //函数最大执行时间，超时后由前端自动重新发起请求
        $table_args       = $this->get_db_table_args();
        $data             = array(
            'count'  => 0,
            'detail' => array(),
        );

        foreach ($table_args as $args) {
            $args      = (array) $args;
            $s_name    = $args['table_name'];
            $s_columns = $args['column_name'];

            $sql     = "SELECT `$s_columns` as `s_val` FROM `$s_name` WHERE `$s_columns` like '%$old%' and `$s_columns` LIKE '%{%' and `$s_columns` LIKE '%}'";
            $results = $wpdb->get_results($sql);

            if ($results && is_array($results)) {

                $i = 0;
                foreach ($results as $result) {
                    if ($this->db_save_serialize_data($s_name, $s_columns, $result->s_val, $old, $new)) {
                        $i++;
                    }
                }

                $data['detail'][] = array(
                    'table_name'  => $s_name,
                    'column_name' => $s_columns,
                    'count'       => $i,
                    'sql'         => $sql,
                );

                $data['count'] += $i;
            }
        }

        return $data;
    }

    //执行保存序列化数据
    public function db_save_serialize_data($s_name, $s_columns, $result, $old, $new)
    {
        global $wpdb;

        if (is_serialized($result)) {
            $new_val = $this->serialize_data_replace(maybe_unserialize($result), $old, $new);
        } else {
            $new_val = str_replace($old, $new, $result);
        }

        $new_val = maybe_serialize($new_val);
        return $wpdb->update($s_name, array($s_columns => $new_val), array($s_columns => $result));

        $save_sql = "UPDATE $s_name SET $s_columns = '$new_val' WHERE $s_columns = $result";
        return $wpdb->query($save_sql);
    }

    //替换序列化数据中的内容
    public function serialize_data_replace($result, $old, $new)
    {

        if (is_array($result)) {
            foreach ($result as $k => $v) {
                $result[$k] = $this->serialize_data_replace($v, $old, $new);
            }
        } elseif (is_object($result)) {
            foreach ($result as $k => $v) {
                $result->$k = $this->serialize_data_replace($v, $old, $new);
            }
        } else {
            $result = str_replace($old, $new, $result);
        }

        return $result;
    }

    //搜索数据库，返回匹配数量明细
    public function db_select($key, $new = '')
    {
        global $wpdb;
        $is_str_strlen_same = true;
        if ($new && strlen($new) !== strlen($key)) {
            $is_str_strlen_same = false;
        }

        $table_args = $this->get_db_table_args();
        $data       = array();
        foreach ($table_args as $args) {
            $args      = (array) $args;
            $s_name    = $args['table_name'];
            $s_columns = $args['column_name'];
            $sql       = "SELECT count(*) FROM `$s_name` WHERE `$s_columns` like '%$key%'";
            $count     = $wpdb->get_var($sql);

            if ($count) {
                $serialize_count = 0;
                if (!$is_str_strlen_same) {
                    $serialize_sql   = $sql . " and `$s_columns` LIKE '%{%' and `$s_columns` LIKE '%}'";
                    $serialize_count = $wpdb->get_var($serialize_sql);
                }

                $data[] = array(
                    'table_name'      => $s_name,
                    'column_name'     => $s_columns,
                    'count'           => $count,
                    'serialize_count' => $serialize_count,
                );
            }

        }

        return $data;
    }

    //第一步的AJAX操作，先进行查询并返回
    public function ajax_next()
    {
        //执行跨域安全检查
        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'sql-replace-nonce')) {
            wp_send_json_error(__('环境异常或登录状态已失效，请刷新页面重试', SQL_REPLACE_TEXT_DOMAIN));
        }

        //执行管理员权限验证
        if (!is_super_admin()) {
            wp_send_json_error(__('权限不足或登录状态已失效，请刷新页面重试', SQL_REPLACE_TEXT_DOMAIN));
        }

        $old = esc_sql($_POST['old']);
        $new = esc_sql($_POST['new']);

        if (!$old) {
            wp_send_json_error(__('请输入需要替换的旧内容', SQL_REPLACE_TEXT_DOMAIN));
        }

        if (!$new) {
            wp_send_json_error(__('请输入需要替换的新内容', SQL_REPLACE_TEXT_DOMAIN));
        }

        if ($new === $old) {
            wp_send_json_error(__('新内容不能与旧内容相同', SQL_REPLACE_TEXT_DOMAIN));
        }

        $send_msg = '';
        if (strlen($new) < 12 || strlen($old) < 12) {
            $send_msg = __('您输入的内容字符数量较少，替换时容易出现误修改情况，请认真确认', SQL_REPLACE_TEXT_DOMAIN);
        }

        $db_select = $this->db_select($old, $new);
        if (!$db_select) {
            wp_send_json_error(__('数据库未查询到可替换的内容[' . $old . ']', SQL_REPLACE_TEXT_DOMAIN));
        }

        wp_send_json_success(['msg' => $send_msg, 'old' => $old, 'new' => $new, 'data' => $db_select, 'num_queries' => get_num_queries(), 'timer_stop' => timer_stop(0, 6) * 1000 . 'ms']);
    }

    //第二步的AJAX操作，替换数据
    public function ajax_submit()
    {
        //执行跨域安全检查
        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'sql-replace-nonce')) {
            wp_send_json_error(__('环境异常或登录状态已失效，请刷新页面重试', SQL_REPLACE_TEXT_DOMAIN));
        }

        //执行管理员权限验证
        if (!is_super_admin()) {
            wp_send_json_error(__('权限不足或登录状态已失效，请刷新页面重试', SQL_REPLACE_TEXT_DOMAIN));
        }

        $old = esc_sql($_POST['old']);
        $new = esc_sql($_POST['new']);

        if (!$old) {
            wp_send_json_error(__('请输入需要替换的旧内容', SQL_REPLACE_TEXT_DOMAIN));
        }

        if (!$new) {
            wp_send_json_error(__('请输入需要替换的新内容', SQL_REPLACE_TEXT_DOMAIN));
        }

        if ($new === $old) {
            wp_send_json_error(__('新内容不能与旧内容相同', SQL_REPLACE_TEXT_DOMAIN));
        }

        $db_result = $this->db_replace($old, $new);
        $count     = $db_result['routine']['count'] + $db_result['serialize']['count'];
        $time_over = !empty($db_result['time_over']);
        $send_msg  = $time_over ? '' : __('数据库替换完成，如您替换了网站域名，请自行跳转到新地址访问', SQL_REPLACE_TEXT_DOMAIN);

        wp_send_json_success(['msg' => $send_msg, 'time_over' => $time_over, 'num_queries' => get_num_queries(), 'timer_stop' => timer_stop(0, 6) * 1000 . 'ms', 'count' => $count, 'old' => $old, 'new' => $new, 'data' => $db_result]);
    }
}
