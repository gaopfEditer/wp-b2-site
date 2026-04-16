/*
 * @Author: 子比主题老唐-Qinver
 * @Url: zibll.com
 * @Date: 2023-10-12 12:18:41
 * @LastEditTime: 2023-10-24 13:49:04
 * @Read me : 本工具为免费开源工具，您可以自由使用和分享，如您需要分享或二次开发，请务必保留并在显眼处体现作者名，以及www.zibll.com的网站
 * @Read me : 不要用于任何商业用途，否则将依法追究相关责任，谢谢合作！
 */

(function ($, document) {
    $(document).ready(function ($) {
        /*global _sql */
        var _wrap = $('.sql-replace-wrap');
        var key_calss_text = 'sql-key';
        var lang = _sql.lang;

        //查询数据
        _wrap.on('click', '.sql-next', function () {
            var _this = $(this);
            var old_text = $('.sql-input-old').val();
            var new_text = $('.sql-input-new').val();
            var _submit_btn = $('.sql-submit');

            var data = {
                action: 'sql_replace_next',
                old: old_text,
                new: new_text,
            };

            ajax(_this, data, function (data) {
                var lists = '';
                var all_count = 0;

                if (data.data && data.data.data) {
                    $.each(data.data.data, function (i, lists_data) {
                        var lists_data_count = ~~lists_data.count;
                        if (lists_data_count) {
                            all_count += lists_data_count;
                            lists += '<div>[table_name:' + lists_data.table_name + '] => [column_name:' + lists_data.column_name + '] => count[' + lists_data.count + ']' + (~~lists_data.serialize_count ? ' || serialize_count[' + lists_data.serialize_count + ']' : '') + '</div>';
                        }
                    });
                }

                if (lists) {
                    var replace_reminder = lang.replace_reminder.replace('{old}', '<span class="' + key_calss_text + ' old">' + data.data.old + '</span>').replace('{new}', '<span class="' + key_calss_text + ' new">' + data.data.new + '</span>');
                    var replace_count_reminder = lang.replace_count_reminder.replace('{count}', all_count);

                    var notice_html = '<div class="sql-warning">' + replace_reminder + '<div>' + replace_count_reminder + '</div></div>';
                    if (data.data.msg) {
                        notice_html += '<div class="sql-warning">' + data.data.msg + '</div>';
                    }

                    notice_html += '<div><div>' + lang.query_title + '</div>' + lists + '</div>';

                    notice(notice_html);
                    _this.html(lang.requery_btn);
                    _submit_btn.show();
                } else {
                    notice(lang.not_reminder, 'error');
                    _submit_btn.hide();
                }
            });

            return false;
        });

        //执行替换
        _wrap.on('click', '.sql-submit', function () {
            var _this = $(this);
            var old_text = $('.' + key_calss_text + '.old').text();
            var new_text = $('.' + key_calss_text + '.new').text();

            if (confirm(lang.reconfirm_reminder.replace('{old}', old_text).replace('{new}', new_text))) {
                return ajax_submit(_this, old_text, new_text);
            }

            return false;
        });

        function ajax_submit(_this, old_text, new_text, auto_submit) {
            if (!old_text || !new_text) {
                return notice(lang.parameter_error, 'error');
            }

            var ajax_data = {
                action: 'sql_replace_submit',
                old: old_text,
                new: new_text,
            };

            notice('<i class="sql-loading"></i> ' + lang.loading_text, 'info', auto_submit);

            ajax(_this, ajax_data, function (n) {
                var data = n.data;
                var lists = '';

                if (data && data.count) {
                    if (data.data.routine && data.data.routine.detail) {
                        $.each(data.data.routine.detail, function (i, lists_data) {
                            if (~~lists_data.count) {
                                lists += '<div>table_name[' + lists_data.table_name + '] => column_name[' + lists_data.column_name + '] => count[' + lists_data.count + ']</div>';
                            }
                        });
                        if (lists) {
                            lists = '<div>' + lang.replace_title + '</div>' + lists;
                        }
                    }

                    if (data.data.serialize && data.data.serialize.detail && ~~data.data.serialize.count) {
                        lists += '<div>' + lang.replace_serialize_title + '</div>';
                        $.each(data.data.serialize.detail, function (i, lists_data) {
                            if (~~lists_data.count) {
                                lists += '<div>table_name[' + lists_data.table_name + '] => column_name[' + lists_data.column_name + '] => count[' + lists_data.count + ']</div>';
                            }
                        });
                    }
                }

                if (lists) {
                    var notice_html = '';
                    if (!auto_submit) {
                        var replace_success_text = lang.replace_success_text.replace('{old}', '<span class="' + key_calss_text + ' old">' + data.old + '</span>').replace('{new}', '<span class="' + key_calss_text + ' new">' + data.new + '</span>');
                        notice_html = '<div class="sql-success"><div class="sql-notice-title"><span class="dashicons dashicons-yes-alt"></span> ' + lang.replace_success_title + '</div>' + replace_success_text + '</div>';
                    }

                    if (data.msg) {
                        notice_html += '<div class="sql-warning">' + data.msg + '</div>';
                    }

                    notice_html += lists;
                    notice(notice_html, 'info', auto_submit);

                    //自动提交
                    if (data.time_over) {
                        ajax_submit(_this, old_text, new_text, true);
                    } else {
                        _this.hide();
                    }
                } else {
                    auto_submit || notice(lang.not_reminder, 'error');
                }
            });

            return false;
        }

        function ajax(_this, data, success) {
            if (_this.attr('disabled')) {
                return !1;
            }

            var _text = _this.html();
            data.nonce = _sql.nonce;
            _this.attr('disabled', true).html('<i class="sql-loading"></i>请稍候');

            $.ajax({
                type: 'POST',
                url: _sql.ajax_url,
                data: data,
                dataType: 'json',
                error: function (n) {
                    console.error('ajax_error', n);
                    _this.attr('disabled', false).html(_text);
                    $('.sql-submit').hide();
                    return notice('<div class="sql-notice-title"><span class="dashicons dashicons-dismiss"></span> Ajax Error</div><div>Error Status:' + n.status + '</div><div>Error Msg:' + (n.responseText || n.statusText) + '</div>', 'error');
                },
                success: function (n) {
                    _this.attr('disabled', false).html(_text); //完成

                    if (!n.success) {
                        $('.sql-submit').hide();
                        return notice(n.data, 'error');
                    }

                    $.isFunction(success) && success(n, _this, data);

                    if (n.reload) {
                        window.location.reload();
                    }
                },
            });
        }

        function notice($msg, type, is_add) {
            var _notice = $('.sql-notice');
            type = type || 'info';
            _notice.removeClass('error success info').addClass(type);
            if (is_add) {
                _notice.append($msg);
            } else {
                _notice.html($msg);
            }
        }

        console.log('数据库批处理工具：by:zibll-老唐');
    });
})(jQuery, document);
