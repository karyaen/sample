(function($) {
    'use strict';
    var delButton,
        delButton2,
        newButton2,
        allSelected,
        loader,
        timer,
        selectArr;


    function allSelectedChange (e) {
        var state = $(e.target).prop('checked');
        selectArr.each(function(i, el) {
            $(el).children().prop('checked', state);
        });
    }

    function createNode(oldNode, newId) {
        var clone = oldNode.clone(true);

        clone.attr('id', newId);
        clone.find('.t-id').html(newId);
        clone.find('.t-sc').html('[magic360 id="' + newId + '"]');

        setCopySpin();

        $('tbody').append(clone);
        selectArr = $('tbody').find('td.t-cb');
        if (selectArr.length) {
            delButton2.css('display', 'inline-block');
            newButton2.css('display', 'inline-block');
        }
    }

    function copySpin(node, button) {
        button.prop('disabled', true);
        clearTimeout(timer);
        timer = setTimeout(function() {
            loader.css('display', 'block');
        }, 200);
        $.post(magictoolbox_WordPress_Magic360_admin_modal_object.ajax, {
            action: "WordPress_Magic360_copy_spins",
            nonce : magictoolbox_WordPress_Magic360_admin_modal_object.nonce,
            id    : node.attr('id')
        })
        .success(function(_data) {
            _data = JSON.parse(_data);
            // button.prop('disabled', false);
            // if (_data.id) {
            //     createNode(node, _data.id);
            // }
            // clearTimeout(timer);
            // loader.css('display', 'none');
            var href = window.location.href;
            href = href.split('?')[0];
            href = href.split('wp-admin')[0];
            href = href + 'wp-admin/admin.php?page=WordPressMagic360-shortcodes-page&id='+_data.id;
            window.location.href = href;
        })
        .error(function() {
            button.prop('disabled', false);
            clearTimeout(timer);
            loader.css('display', 'none');
        });
    }

    function removeSpins(e) {
        var arr = [], spins = [], b = $(this);

        function remSp() {
            var l;
            $.each(arr, function(i, el) {
                $(el).parent().remove();
            });

            l = $('tbody').find('td.t-cb').length;

            if (!l) {
                delButton2.css('display', 'none');
                newButton2.css('display', 'none');
            }

            if (!$('tbody').find('td.t-cb').length) {
                $('table').remove();
                delButton.remove();
            }

            allSelected.prop('checked', false);
            selectArr = $('tbody').find('td.t-cb');
        }

        selectArr.each(function(i, el) {
            el = $(el);
            if (el.children().prop('checked')) {
                arr.push(el);
                spins.push(parseInt(el.parent().attr('id')));
            }
        });

        if (spins.length) {
            var popWindow = $('<div>');
            var popCurtain = $('<div>');
            var popWrapper = $('<div>');
            var popMessage = $('<div>');
            var popControls = $('<div>');
            var ok = $('<button>');
            var cansel = $('<button>');

            popWindow.addClass('pop-window');
            popCurtain.addClass('pop-curtain');
            popWindow.append(popCurtain[0]);
            popWrapper.addClass('pop-wrapper');
            popWindow.append(popWrapper[0]);
            popMessage.addClass('pop-message');
            popMessage.html('<span>Are you sure you want to delete ' + spins.length + ' spin' + (spins.length > 1 ? 's' : '') + '?</span>');
            popControls.addClass('pop-controls');
            ok.addClass('button');
            ok.html('OK');
            cansel.addClass('button');
            cansel.html('Cancel');
            popControls.append(cansel[0]);
            popControls.append(ok[0]);

            popWrapper.append(popMessage[0]);
            popWrapper.append(popControls[0]);
            $(document.body).append(popWindow[0]);

            cansel.on('click', function() {
                popWindow.remove();
                return false;
            });

            ok.on('click', function() {
                popWindow.remove();
                b.prop('disabled', true);
                clearTimeout(timer);
                timer = setTimeout(function() {
                    loader.css('display', 'block');
                }, 200);
                $.post(magictoolbox_WordPress_Magic360_admin_modal_object.ajax, {
                    action: "WordPress_Magic360_remove_spins",
                    nonce: magictoolbox_WordPress_Magic360_admin_modal_object.nonce,
                    ids: spins
                })
                .success(function(_data) {
                    _data = JSON.parse(_data);
                    b.prop('disabled', false);
                    remSp();
                    clearTimeout(timer);
                    loader.css('display', 'none');
                })
                .error(function() {
                    b.prop('disabled', false);
                    remSp();
                    clearTimeout(timer);
                    loader.css('display', 'none');
                });
                return false;
            });
        }
    }

    function setCopySpin() {
        $('.copy-spin').off('click');
        $('.copy-spin').on('click', function(e) {
            copySpin($(this).parent().parent(), $(this));
        });
    }

    $(document).ready(function() {
        delButton = $('#delete-selected');
        delButton2 = $('#delete-selected2');
        newButton2 = $('#new2');
        allSelected = $('thead').find('td.t-cb').children();
        selectArr = $('tbody').find('td.t-cb');
        loader = $('.loader');

        allSelected.on('change', allSelectedChange);
        delButton.on('click', removeSpins);
        delButton2.on('click', removeSpins);

        setCopySpin();
    });
})(jQuery);
