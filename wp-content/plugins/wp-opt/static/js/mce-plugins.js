(function () {
    tinymce.PluginManager.add('wpopt_btn', function (editor, url) {
        editor.addButton('wpopt_btn', {
            image: wpopt.img_url + '/wpopt.svg',
            title: 'WPOPT编辑器增强',
            onclick: () => {
                window.open_wpopt_dialog = true;
            }
        });
    });
    tinymce.activeEditor.on('paste', function (e) {
        window.tinymce_editor_data = e;
        window.wpopt_paste_img = true;
    });
})();