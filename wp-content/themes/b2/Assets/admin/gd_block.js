(function(blocks, editor, element, components, _) {
    var __ = wp.i18n;
    var el = element.createElement;
    var RichText = editor.RichText;
    var AlignmentToolbar = editor.AlignmentToolbar;
    var PlainText = editor.PlainText;
    var BlockControls = editor.BlockControls;
    // var Fragment = element.Fragment;
    // var TextControl = wp.components.TextControl;
    // var TextareaControl = wp.components.TextareaControl;

    let shortcodedata = {
        hideStart:{
            desc:'请将需要隐藏的内容，包裹在隐藏开始和隐藏结束两个短代码之间。',
            content:'[content_hide]'
        },
        hideEnd:{
            desc:'请将需要隐藏的内容，包裹在隐藏开始和隐藏结束两个短代码之间。',
            content:'[/content_hide]'
        },
        card:{
            desc:'只支持本站的ID或链接',
            content:'[b2_insert_post id="请输入本站网址或文章ID"]'
        },
        file:{
            desc:'请按说明，将相应的参数填入短代码中',
            content:'[b2_file link="连接" name="资源名称" pass="提取码" code="解压码"]'
        },
        video:{
            desc:'支持视频文件。如果使用优酷等第三方视频源，请安装 smartideo 插件后，再使用此短代码',
            content:'[b2player src="视频地址" poster="封面地址"]'
        },
        inv:{
            desc:'请前往主题设置->邀请管理查看要显示的邀请码开始和结束ID。请注意，只有管理员生成的邀请码才能显示，用户生成的邀请码不会显示在此处',
            content:'[zrz_inv start="开始ID" end="结束ID"]'
        }
    }

    blocks.registerBlockType('b2/shortcode', {
        title: 'B2：短代码',
		category: 'common',
        icon: {
            src: 'shortcode',
            foreground: '#f85253'
        },
        description: '隐藏内容短代码',
        attributes: { 
            key: {
                type: 'string',
                default: 'hideStart',
                source: 'attribute',
                selector: '.b2-shortcode',
                attribute: 'class',
            },
            content:{
                type: 'string',
                default: '[content_hide]',
                source: 'text',
                selector: '.b2-shortcode',
            }
        },
        edit: function(props) {
            var isSelected = props.isSelected;
            
            var index = props.attributes.key.lastIndexOf(" "),
            skey  =  props.attributes.key.substring(index + 1,  props.attributes.key.length);

            function changeType(event) {
                const key = event.target.getAttribute('data-key')
                props.setAttributes({
                    key: key
                })
                props.setAttributes({
                    content: shortcodedata[key].content
                })
            }

            var outerHtml = el(
                    PlainText, {
                        style:{width:'100%'},
                        placeholder: '在此编写简码…',
                        rows:1,
                        className: "block-editor-plain-text blocks-shortcode__textarea",
                        hideLabelFromVision:true,
                        value: props.attributes.content,
                        onChange: ( value ) => {
                            props.setAttributes({
                                content: value
                            })
                        }
                    })

            var label = el('label', {
                className: 'components-placeholder__label',
            },el('i',{
                className:'dashicon dashicons dashicons-shortcode ',
            }),'B2主题短代码')

            var desc = el('p',
                    {
                        className:'b2-shortcode-desc'
                    },`${shortcodedata[skey].desc}`)

            var selector = el('div', {
                className: 'b2-shortcode-button'
            },
            [
                el('button', {'data-key':'hideStart',onClick: changeType},'隐藏开始'), 
                el('button', {'data-key':'hideEnd',onClick: changeType},'隐藏结束'), 
                el('button', {'data-key':'card',onClick: changeType},'插入卡片'),
                el('button', {'data-key':'file',onClick: changeType},'插入下载'),
                el('button', {'data-key':'video',onClick: changeType},'插入视频'),
                el('button', {'data-key':'inv',onClick: changeType},'插入邀请码'),
            ]
            );

            return el(
                'div',
                {
                    className:'components-placeholder block-editor b2-block-shortcode',
                    style:{width:'100%'}
                },
                [label, outerHtml, desc, isSelected && selector]
            );
        },
        save: function(props) {  
            return el('p',{
                className:'b2-shortcode '+props.attributes.key
            },props.attributes.content)
        },
    });

    blocks.registerBlockType('b2/tips', {
        title: 'B2：提示',
		category: 'common',
        icon: {
            src: 'warning',
            foreground: '#f85253'
        },
        attributes: {
            classStr: {
                type: 'string',
                source: 'attribute',
                selector: '.b2-tip',
                attribute: 'class',
            },
            content:{
                type:'string',
                default:'',
                source: 'html',
                selector: '.b2-tip',
            }
        },
        edit: function(props) {
            console.log(props)
            var classStr = props.attributes.classStr,
            alignment = props.attributes.alignment,
            isSelected = props.isSelected;
            function onChangeContent(newContent) {
                props.setAttributes({
                    content: newContent
                })
            }
            function changeType(event) {
                var classStr = event.target.getAttribute('data-key')
                props.setAttributes({
                    classStr: classStr
                })
            }

            var richText = el(RichText, {
                tagName: 'p',
                className:'b2-tip '+classStr,
                onChange: onChangeContent,
                value: b2unescape(props.attributes.content),
                isSelected: props.isSelected,
                placeholder: '提示内容'
            });
            var outerHtml = el('div', {},richText);

            var selector = el('div', {
                className: 'b2-shortcode-button'
            },
            [el('button', {
                className: 'b2-button-primary',
                'data-key':'b2-alert-primary',
                onClick: changeType
            },
            '警告'), el('button', {
                className: 'b2-button-warning',
                'data-key':'b2-alert-warning',
                onClick: changeType
            },
            '错误'), el('button', {
                className: 'b2-button-info',
                'data-key':'b2-alert-info',
                onClick: changeType
            },
            '信息'), el('button', {
                className: 'b2-button-success',
                'data-key':'b2-alert-success',
                onClick: changeType
            },
            '成功'), el('button', {
                className: 'b2-button-light',
                'data-key':'b2-alert-light',
                onClick: changeType
            },
            '提示'), ]);

			return el('div', {
                style: {
                    textAlign: alignment
                }
            },
            [outerHtml, isSelected && selector])
        },
        save: function(props) {
            return el('p',{
                className:'b2-tip '+props.attributes.classStr,
            },props.attributes.content)
        },
    })

})(window.wp.blocks, window.wp.editor, window.wp.element, window.wp.components, window._, );

var b2entityMap = {
    escape: {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&apos;',
    },
    unescape: {
      '&amp;': "&",
      '&apos;': "'",
      '&gt;': ">",
      '&lt;': "<",
      '&quot;': '"',
    }
};

var b2entityReg = {
    escape: RegExp('[' + Object.keys(b2entityMap.escape).join('') + ']', 'g'),
    unescape: RegExp('(' + Object.keys(b2entityMap.unescape).join('|') + ')', 'g')
}

function b2escape(html) {
    if (typeof html !== 'string') return ''
    return html.replace(b2entityReg.escape, function(match) {
        return b2entityMap.escape[match]
    })
}

function b2unescape(str) {
    if (typeof str !== 'string') return ''
    return str.replace(b2entityReg.unescape, function(match) {
        return b2entityMap.unescape[match]
    })
}