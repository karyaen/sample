(function($) {
    var data,
        q = $.ajax({
            type: 'POST',
            url: magictoolbox_WordPress_Magic360_admin_modal_object.ajax,
            data: {'action': 'WordPress_Magic360_tiny_mce_data', 'nonce': magictoolbox_WordPress_Magic360_admin_modal_object.nonce},
            async: false,
            dataType: 'json'
        });

    data = q.responseJSON;

    if (!data.error || 'empty' === data.error) {
        tinymce.PluginManager.add('magictoolbox_WordPress_Magic360_shortcode', function( editor, url ) {
            var i, arr = [], ss_menu = [];

            for (i = 0; i < data.length; i++) {
                var id = ('null' === data[i].shortcode) ? data[i].id : data[i].shortcode;
                arr.push({
                    name: data[i].name,
                    code: '[magic360 id="' + id + '"]'
                });
            }

            ss_menu.push({
                text: "Create new spin",
                classes: "separator",
                onclick: function() {
                    var href = window.location.href;
                    href = href.split('?')[0];
                    href = href.split('wp-admin')[0];
                    href = href + 'wp-admin/admin.php?page=WordPressMagic360-shortcodes-page';
                    window.location.href = href;
                }
            });

            if (!arr.length) {
                ss_menu.push({
                    text: "No spins",
                    classes: 'focuse-disable',
                    disabled: true,
                    active: false,
                    focuse: false
                });
            }

            for (i = 0; i < arr.length; i++) {
                ss_menu.push({
                    text: arr[i].name,
                    value: arr[i].code,
                    onclick: function() {
                        editor.insertContent(this.value());
                    }
                });
            }

            // if (arr.length > 0) {
                editor.addButton( 'magictoolbox_WordPress_Magic360_shortcode', {
                    title: 'Insert spin (Magic 360)',
                    type: 'menubutton',
                    icon: 'icon magic360-icon',
                    menu: ss_menu
                });
            // }
        });
    }
})(jQuery);
