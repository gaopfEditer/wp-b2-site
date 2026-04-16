<?php namespace B2\Modules\Settings;
use B2\Modules\Common\CustomPay as Cpay;

class CustomPay{

    public function init(){
        add_action('cmb2_admin_init',array($this,'post_settings'));
    }

    public function post_settings(){
        $post_meta = new_cmb2_box(array(
            'id'            => 'single_pay_metabox',
            'title'         => __( '支付设置', 'b2' ),
            'object_types'  => array( 'cpay'),
            'context'       => 'normal',
            'priority'      => 'high',
            'show_names'    => true,
        ));

        // $post_meta->add_field(array(
        //     'name' => __('支付类型','b2'),
        //     'id'   => 'b2_single_pay_type',
        //     'type' => 'select',
        //     'options'=>[
        //         'money'=>__('金额','b2'),
        //         'credit'=>__('积分','b2'),
        //     ],
        //     'desc'=>'<p>'.__('请选择支付类型。','b2').'</p>'
        // ));

        $post_meta->add_field(array(
            'name' => __('支付金额','b2'),
            'id'   => 'b2_single_pay_money',
            'type' => 'textarea_small',
            'desc'=>'<p>'.__('如果是固定金额，或者需要用户选择支付金额，请将金额填到此处，每个金额占一行。请直接填写数字。','b2').'</p>
            <p>'.__('如果需要用户自己输入金额，请将此处留空。','b2').'</p>'
        ));

        $post_meta->add_field(array(
            'name' => __('是否开启金额关联','b2'),
            'id'   => 'b2_enable_related_pay_money',
            'type' => 'select',
            'options' => [
                'off' => '关闭',
                'on' => '开启',
            ],
            'default' => 'off',
            'desc'=>'<p>'.__('支付金额与某个自定义表单字段（单选）联动，可以实现类似于“月付”、“季付”、“年付”等自定义支付。','b2').'</p>
                    <p>'.__('金额的个数必须与该自定义表单字段的待选值一致。','b2').'</p>',
            'before_row'=> function() {
                add_action('admin_footer', function () {
                    ?>
                    <script type="text/javascript">
                        jQuery(function ($) {
                            let b2_enable_related_pay_money = $('#b2_enable_related_pay_money').find(':selected').val();
                            if(b2_enable_related_pay_money === 'on'){
                                $('.cmb2-id-b2-related-field').show();
                            }else{
                                $('.cmb2-id-b2-related-field').hide();
                            }
                            $('#b2_enable_related_pay_money').on('change', function() {
                                let val = $(this).find(':selected').val();
                                if(val === 'on'){
                                    $('.cmb2-id-b2-related-field').show();
                                }else{
                                    $('.cmb2-id-b2-related-field').hide();
                                }
                            })
                        });
                    </script>
                    <?php
                });
            }
        ) );

        $post_meta->add_field(array(
            'name' => __('金额关联字段','b2'),
            'id'   => 'b2_related_field',
            'type' => 'text',
            'desc'=>'<p>'.__('支付金额与某个自定义表单字段（单选）联动，可以实现类似于“月付”、“季付”、“年付”等自定义支付。','b2').'</p>
                    <p>'.__('请输入“自定义表单”的某个自定义表单key','b2').'</p>'
        ) );

        $post_meta->add_field([
            'name' => __('开放支付时间模式','b2'),
            'id'   => 'b2_active_date_type',
            'type' => 'select',
            'options'=> [
                'forever'=>__('永久有效','b2'),
                'date_area'=>__('限定时间范围','b2'),
            ],
            'default'=>'forever',
            'before_row'=> function() {
                add_action('admin_footer', function () {
                    ?>
                    <script type="text/javascript">
                        jQuery(function ($) {
                            let b2_active_date_type = $('#b2_active_date_type').find(':selected').val();
                            if(b2_active_date_type === 'forever'){
                                $('.cmb2-id-b2-start-t').hide();
                                $('.cmb2-id-b2-end-t').hide();
                                $('.cmb2-id-b2-not-start-text').hide();
                                $('.cmb2-id-b2-end-text').hide();
                            }else{
                                $('.cmb2-id-b2-start-t').show();
                                $('.cmb2-id-b2-end-t').show();
                                $('.cmb2-id-b2-not-start-text').show();
                                $('.cmb2-id-b2-end-text').show();
                            }
                            $('#b2_active_date_type').on('change', function() {
                                let val = $(this).find(':selected').val();
                                if(val === 'forever'){
                                    $('.cmb2-id-b2-start-t').hide();
                                    $('.cmb2-id-b2-end-t').hide();
                                    $('.cmb2-id-b2-not-start-text').hide();
                                    $('.cmb2-id-b2-end-text').hide();
                                }else{
                                    $('.cmb2-id-b2-start-t').show();
                                    $('.cmb2-id-b2-end-t').show();
                                    $('.cmb2-id-b2-not-start-text').show();
                                    $('.cmb2-id-b2-end-text').show();
                                }
                            })
                        });
                    </script>
                    <?php
                });
            }
        ]);

        $post_meta->add_field( [
            'name' => __('开始时间','b2'),
            'id'   => 'b2_start_t',
            'type' => 'text_datetime_timestamp',
        ]);

        $post_meta->add_field( [
            'name' => __('结束时间','b2'),
            'id'   => 'b2_end_t',
            'type' => 'text_datetime_timestamp',
        ]);

        $post_meta->add_field( [
            'name' => __('未开始的文字提示','b2'),
            'id'   => 'b2_not_start_text',
            'type' => 'text',
            'desc'=>sprintf(__('可以使用%s来代替上面选择的“开始时间”','b2'),'<code>{{start_time}}</code>'),
            'default'=>__('活动将于{{start_time}}准时开始，感谢关注！','b2')
        ]);

        $post_meta->add_field( [
            'name' => __('已结束的文字提示','b2'),
            'id'   => 'b2_end_text',
            'type' => 'text',
            'default'=>__('活动已结束，感谢关注本次活动！','b2'),
        ]);

        $post_meta->add_field(array(
            'name' => __('支付成功以后PHP的回调方法','b2'),
            'id'   => 'b2_single_pay_callback',
            'type' => 'text',
            'desc'=>sprintf(__('支付成功以后您可以使用钩子处理支付后的逻辑，也可以这里直接填写支付成功以后要执行的函数，具体方法请参考%s自定义支付使用教程%s','b2'),'<a href="https://7b2.com/document/57638.html" target="_blank">','</a>')
        ));

        $custom_code = $post_meta->add_field( array(
            'name'=>__('自定义表单','b2'),
            'id'          => 'b2_pay_custom_group',
            'type'        => 'group',
            'options'     => array(
                'group_title'       => __( '自定义表单{#}', 'b2' ),
                'add_button'        => __( '添加新的自定义表单', 'b2' ),
                'remove_button'     => __( '删除', 'b2' ),
                'sortable'          => true,
                'closed'         => true,
                'remove_confirm' => __( '确定要删除这个自定义表单吗？', 'b2' ),
            )
        ));

        $post_meta->add_group_field( $custom_code, array(
            'name' => __('自定义表单名称','b2'),
            'id'   => 'name',
            'type' => 'text',
            'desc'=>sprintf(__('提示用户要设置的是什么，比如：%s国籍%s','b2'),'<code>','</code>')
        ) );

        $post_meta->add_group_field( $custom_code, array(
            'name' => __('自定义表单key','b2'),
            'id'   => 'key',
            'type' => 'text',
            'desc'=>sprintf(__('请使用英文，%s并且唯一，不能和其他表单名称相同%s。比如%scountry%s（国籍的英文名称）','b2'),'<span class="red">','</span>','<code>','</code>')
        ) );

        $post_meta->add_group_field( $custom_code, array(
            'name' => __('描述','b2'),
            'id'   => 'desc',
            'type' => 'textarea_small',
            'desc'=>sprintf(__('向用户说明这个选项如何使用，比如：%s请选择您的国籍%s','b2'),'<code>','</code>')
        ) );

        $post_meta->add_group_field( $custom_code, array(
            'name' => __('是否必填','b2'),
            'id'   => 'required',
            'type' => 'radio',
            'options'=>[
                1=>__('必填','b2'),
                0=>__('选填','b2')
            ],
            'default'=>0
        ) );

        $post_meta->add_group_field( $custom_code, array(
            'name' => __('是否在支付结果中显示该项目','b2'),
            'id'   => 'show_list',
            'type' => 'radio',
            'options'=>[
                1=>__('显示','b2'),
                0=>__('不显示','b2')
            ],
            'default'=>1
        ) );

        $post_meta->add_group_field( $custom_code, array(
            'name' => __('表单形式','b2'),
            'id'   => 'type',
            'type' => 'select',
            'options'=>array(
                'text'=>__('单行文本','b2'),
                'textarea'=>__('多行文本','b2'),
                'radio'=>__('单选','b2'),
                'checkbox'=>__('多选','b2'),
                'select'=>__('下拉选框','b2'),
                'file'=>__('文件','b2')
            )
        ) );

        $post_meta->add_group_field( $custom_code, array(
            'name' => __('待选值','b2'),
            'id'   => 'value',
            'type' => 'textarea',
            'desc'=>sprintf(__('每组值占一行，请使用%schina=中国%s这种形式，%schina%s是存入数据库里的值（英文的值方便查找与管理），%s中国%s是显示出来给用户看的。比如：%schina=中国%samerica=美国%s'),'<code>','</code>','<code>','</code>','<code>','</code>','<br><code>','</code><br><code>','</code>')
        ) );

        $post_meta->add_group_field( $custom_code, array(
            'name' => __('允许上传的文件数量','b2'),
            'id'   => 'file_count',
            'type' => 'text',
            'default'=>1,
            'desc'=>__('允许上传多少个文件','b2')
        ) );

        $post_meta->add_group_field( $custom_code, array(
            'name' => __('文件类型','b2'),
            'id'   => 'file_type',
            'type' => 'textarea',
            'desc'=>'<p>'.__('如果表单形式为文件，请在此输入允许上传的文件类型，每种类型之间使用英文逗号隔开。','b2').'</p>
            <p>比如您要上传图片，请填写：<code>.jpg, .png, .gif, .jpeg, .ico</code></p>
            <p>'.__('以下是wp默认的允许上传的文件类型。如果你需要上传的文件类型不在里面，请使用 file upload types 插件开启某些文件类型的上传权限，再填到此处。','b2').'</p>
            <p>
            图片: .jpg, .png, .gif, .jpeg, .ico<br />
            文件: .pdf, .doc, .ppt, .odt, .xls, .psd<br />
            音频: .mp3, .m4a, .ogg, .wav<br />
            视频: .mp4, .mov, .avi, .mpg, .ogv,. .3gp, .3g2<br />
            </p>'
        ) );

        $post_meta->add_field(array(
            'name' => __('自定义html','b2'),
            'id'          => 'b2_pay_custom_html',
            'type' => 'textarea_code',
            'options' => array( 'disable_codemirror' => true ),
            'desc'=>__('此内容会显示在支付说明中，支持 Html 和 php','b2')
        ));

        $post_meta->add_field(array(
            'name' => __('提交按钮的名称','b2'),
            'id'          => 'b2_pay_button',
            'type' => 'text',
            'desc'=>__('比如‘支付’、‘提交’、‘打赏’、‘支持’、‘捐助’等字眼','b2')
        ));

        $post_meta->add_field(array(
            'name' => __('是否显示支付用户列表','b2'),
            'id'          => 'b2_pay_user_list',
            'type' => 'select',
            'options'=>[
                0=>__('始终不显示','b2'),
                1=>__('始终显示全部','b2'),
                2=>__('支付后显示全部','b2'),
                3=>__('登录用户显示全部','b2'),
                4=>__('只显示当前用户的支付结果','b2')
            ],
            'desc'=>__('管理员不受此显示，始终可以查看全部','b2'),
            'default'=>1
        ));

        $post_meta->add_field(array(
            'name' => __('切换表单的按钮名称','b2'),
            'id'   => 'b2_pay_form_name',
            'type' => 'text',
            'desc'=>__('如果要显示支付用户列表，tab选项卡中显示的文字','b2')
        ) );

        $post_meta->add_field(array(
            'name' => __('切换支付用户列表的按钮名称','b2'),
            'id'   => 'b2_pay_res_name',
            'type' => 'text',
            'desc'=>__('如果显示支付用户列表，tab选项卡中显示的文字','b2')
        ) );

        $id = isset($_REQUEST['post']) ? (int)$_REQUEST['post'] : '';

        $post_meta->add_field(array(
            'name' => __('调用的短代码','b2'),
            'id'          => 'b2_pay_short_code',
            'type' => 'text',
            'attributes' => array(
                'readonly' => 'readonly',
                'disabled' => 'disabled'
            ),
            'save_field' => false,
            'default'=>'[b2_custom_pay id="'.$id.'"]',
            'desc'=>__('您可以在文章中插入此短代码，以调用自定义支付','b2')
        ));
    }
}
