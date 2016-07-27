(function($) {
    'use strict';
    var save = false,
        close = false,
        mainContainer,
        addImagesButton,
        removeAllImgsButton,
        imagesContainer,
        spinName,
        spinNameError,
        shortcodeName,
        singleRow,
        multiRow,
        numberOfImages,
        defOpt,
        cusOpt,
        optionsContainer,
        spinId,
        images = [],
        options = {},
        resizeOptions = {},
        imgIDs = [],
        toolContainer,
        saveButton,
        saveButton2,
        saveAndCloseButton,
        saveAndCloseButton2,
        saveLoader,
        currentSpin,
        watermarkImg,
        imgLoader,
        imgError,
        charTimer,
        multiRowError,
        columns = 36,
        rows = 1,
        defOptions;

    function loadImgs(srcArr, callback) {
        var l = srcArr.length

        function _check() {
            if (!l) {
                imgLoader.css('display', 'none');
                unLock(addImagesButton);
                unLock(removeAllImgsButton);
                unLock(singleRow);
                unLock(multiRow);
                callback();
            }
        }
        _check();
        if (!l) { return }

        imgLoader.css('display', 'inline-block');
        lock(addImagesButton);
        lock(removeAllImgsButton);
        lock(singleRow);
        lock(multiRow);

        $.each(srcArr, function(i, v) {
            var img = $('<img>');

            img.attr('src', v);

            img.css({
                'top': '-100000px',
                'left': '-100000px',
                'position': 'absolute'
            });

            img.load(function(e) {
                $(this).remove();
                --l;
                _check();
            });

            $(document.body).append(img[0]);
        });
    }

    function lock(node) {
        node.prop('disabled', true);
    }

    function unLock(node) {
        node.prop('disabled', false);
    }

    function toString(obj) {
        var str = '';
        $.each(obj, function(key, value) {
            if ('Yes' === value || 'No' === value) {
                value = 'Yes' === value ? 'true' : 'false';
            }
            str += (key + ':' + value + ';');
        });

        return str;
    }

    function fromString(str) {
        var opt = {};

        str = str.split(';');
        $.each(str, function(i, value) {
            var v = $.trim(value).split(':');
            if (!!v[0]) {
                opt[$.trim(v[0])] = $.trim(v[1]);
            }
        });

        return opt;
    }

    function canSave() {
        var result = true;

        if ('' === $.trim(spinName.val())) {
            spinNameError.css('display', 'inline');
            result = false;
        }

        if (!shortcodeName.isGood) {
            shortcodeName.check();
            result = shortcodeName.isGood;
        }

        if (images.length < 3) {
            imgError.css('display', 'block');
            result = false;
        }

        if (multiRow.prop('checked')) {
            if (!calcColRow()) {
                multiRowError.css('display', 'block');
                result = false;
            }
        }

        return result;
    }

    function saveSpin(e) {
        var
            opt,
            addOpt,
            _id,
            _name,
            _shortcode,
            _startimg,
            _images,
            _options,
            _additional_options;

        if (!canSave()) { return; }

        opt = getOptionsValues();
        addOpt = getAdditionalOptionsValues();

        lock(saveButton);
        lock(saveButton2);
        lock(saveAndCloseButton);
        lock(saveAndCloseButton2);

        saveLoader.css('display', 'inline-block');

        if (!imgIDs.length) {
            $.each(images, function(i, el) {
                imgIDs.push(el.attr('data-img-id'));
            });
        }

        _id = spinId;
        _name = spinName.attr('value');
        _shortcode = shortcodeName.getShortcode();

        _startimg = imgIDs[getStartImgIndex(addOpt['start-column'], addOpt['start-row'], opt.numberOfImages)];

        _images = imgIDs.join(',');
        _options = toString(opt);

        if (defOpt.prop('checked')) {
            _additional_options = 'default';
        } else {
            _additional_options = toString(addOpt);
        }

        save = true;

        $.post(magictoolbox_WordPress_Magic360_admin_modal_object.ajax, {
            action: "WordPress_Magic360_save",
            nonce: magictoolbox_WordPress_Magic360_admin_modal_object.nonce,
            id: _id,
            name: _name,
            shortcode: _shortcode,
            startimg : _startimg,
            images: _images,
            options: _options,
            additional_options: _additional_options
        })
        .success(function(_data) {
            _data = JSON.parse(_data);

            if (!_data.error) {
                if (_data.id) {
                    mainContainer.attr('data-spin-id', _data.id);
                    spinId = mainContainer.attr('data-spin-id');
                }

                $('h1').remove();
                var h = $('<h1>');
                h.html('Edit spin ID #' + spinId + ', \'' + $.trim(spinName.val()) + '\'');
                mainContainer.prepend(h);
            }

            if (close) {
                window.location.href = window.location.origin + window.location.pathname + window.location.search.split('&')[0];
            } else {
                unLock(saveButton);
                unLock(saveButton2);
                unLock(saveAndCloseButton);
                unLock(saveAndCloseButton2);
                saveLoader.css('display', 'none');
                save = false;
            }
        })
        .error(function() {
            unLock(saveButton);
            unLock(saveButton2);
            unLock(saveAndCloseButton);
            unLock(saveAndCloseButton2);
            saveLoader.css('display', 'none');
            close = false;
            save = false;
        });
    }

    function getOptionsValues() {
        var
            mr = multiRow.prop('checked'),
            ni = numberOfImages.val(),
            udo = defOpt.prop('checked');

        return $.extend({}, {
            columns: columns,
            rows: rows,
            multiRow: mr,
            numberOfImages: ni,
            useDefOpt: udo
        }, getAdditionalOptionsValues(true));
    }

    function getAdditionalOptionsValues(inside) {
        var opt = {};

        function getV(obj) {
            var r;
            switch(obj.type) {
                case 'checkbox':
                    r = obj.node.prop('checked');
                    break;
                case 'input':
                case 'select':
                    r = obj.node.val();
                    break;
                case 'div':
                    r = obj.node.val();
                    break;
            }
            return r;
        }

        $.each(inside ? resizeOptions : options, function(key, value) {
            opt[key] = getV(value)
        });

        return opt;
    }

    function changeOpt(type, node) {
        switch (type) {
            case 'input':
                // node.on('keypress', function(e) {
                node.on('keyup', function(e) {
                    var k = e.keyCode;
                    // if (/[a-zA-Z0-9\s]/.test(String.fromCharCode(e.charCode))) {
                    if (k >= 46 && k <= 57 || k >= 65 && k <= 90 || k === 8 || k === 32 || k === 13) {
                        clearTimeout(charTimer);
                        charTimer = setTimeout(function() {
                            setSpin();
                        }, 300);
                    }
                });
            case 'select':
            case 'checkbox':
                node.on('change', function(e) {
                    clearTimeout(charTimer);
                    charTimer = setTimeout(function() {
                        setSpin();
                    }, 300);
                });
                break;
        }
    }

    function getOptions(inside) {
        var opt = {}, fieldset = $('fieldset');

        if (inside) {
            fieldset = fieldset.filter(function(i, n) {
                return $(n).hasClass('inside-options');
            });
        } else {
            fieldset = fieldset.not('.inside-options');
        }

        function setOpt(node) {
            var el = $($(node).children()[0]),
                name = el.attr('name'), t;

            switch(el[0].tagName.toLowerCase()) {
                case 'input':
                    if ('checkbox' === el.attr('type')) {
                        t = 'checkbox';
                    } else {
                        t = 'input';
                    }
                    break;
                case 'select':
                    t = 'select';
                    break;
                case 'div':
                    if ('watermark' === el.attr('name')) {
                        el = watermarkImg;
                    }
                    t = 'div';
                    break;
            }

            !inside && changeOpt(t, el);

            opt[name] = {
                type: t,
                node: el
            };
        }

        $.each(fieldset, function(i, el) {
            $.each($(el).find('tr'), function(_i, tr) {
                setOpt($(tr).find('td').last());
            });
        });

        return opt;
    }

    function createImg(img) {
        var node, b, s;

        node = $('<div>');
        b = $('<button>');
        s = $('<span>');

        node.addClass('img-container');
        node.attr('data-img-id', img.id);
        node.attr('data-url', img.url);
        node.attr('data-name', img.title);
        node.css('background-image', 'url(' + img.url + ')');

        s.addClass('img-name');
        s.html(img.title);

        b.addClass('magic-button');
        b.attr('title', "Remove");

        node.append(b[0]);
        node.append(s[0]);

        return node;
    }

    function removeImg(node) {
        var i, imgName;
        if (node) {
            imgName = getImgName(node);
            for (i = 0; i < images.length; i++) {
                if (imgName === getImgName(images[i])) {
                    images.splice(i, 1);
                    break;
                }
            }
            $(node.parent()).remove();
        } else {
            imagesContainer.html('');
            images = [];
        }

        clearTimeout(charTimer);
        charTimer = setTimeout(function() {
            setSpin();
        }, 300);
    }

    function getImgName(imgNode) {
        return $(imgNode).find('span').html();
    }

    function imgSorting(arr) {
        var i, _arr = [], newArr = [];

        $.each(arr, function(i, el) {
            _arr.push(getImgName(el));
        });

        _arr = _arr.sort();

        $.each(_arr, function(i, name) {
            for (i = 0; i < arr.length; i++) {
                if (name === getImgName(arr[i])) {
                    newArr.push(arr[i]);
                    break;
                }
            }
        });

        return newArr;
    }

    function addToPage(arr) {
        imgIDs = [];
        $.each(arr, function(i, el) {
            imgIDs.push(el.attr('data-img-id'));
            imagesContainer.append(el);
            var b = el.find('button');
            b.off('click');
            b.on('click', function(e) {
                removeImg($(e.target));
                return false;
            });
        });
    }

    function addImgs(e) {
        var urls = [],
            wp_media = wp.media({
                title: 'Images for Magic360',
                library: {type: 'image'},
                multiple: true,
                button: {text: 'Add'}
            });

        wp_media.on('select', function() {
            var i, imgs = wp_media.state().get('selection').toJSON();

            $.each(imgs, function(i, el) {
                var _img = createImg(el);
                urls.push(el.url);
                images.push(_img);
            });
            imgError.css('display', 'none');
            loadImgs(urls, function() {
                images = imgSorting(images);
                if (!multiRow.prop('checked')) {
                    numberOfImages.val(images.length);
                }
                addToPage(images);
                setSpin();
            });
        });
        wp_media.open();
    }


    function ShortCodeString(node) {
        var self = this;
        this._m = new Message($('.msg-container'));
        this.node = node;
        this.timer = null;
        this.value = '';
        this.isGood = true;
        this.xhr;

        if ('' !== $.trim(spinId)) {
            this._m.show('good-msg');
        } else {
            this.value = this.node.val();
        }

        this.node.on('keyup', function(e) {
            self.value = $(this).val();
            self.check();
        });
    }

    ShortCodeString.prototype.getShortcode = function() {
        return $.trim(this.value);
    };

    ShortCodeString.prototype.checkString = function() {
        var result = /^[A-Za-z0-9_-]+$/.test(this.value);

        if (result) {
            result = !/^[0-9]+$/.test(this.value);
        }

        if (result) {
            this.isGood = true;
            this._m.allHide();
        } else {
            this.isGood = false;
            this._m.show('incorrect');
        }

        return result;
    };

    ShortCodeString.prototype.showMsg = function(msg) {
        this._m.show(msg);
    };

    ShortCodeString.prototype.hideMsg = function() {
        this._m.allHide();
    };

    ShortCodeString.prototype.check = function() {
        var self = this,
            _id, _shortcode;

        if ('' === this.value) {
            this.isGood = true;
            this._m.allHide();
        } else if (this.checkString()) {
            this.isGood = false;
            clearTimeout(this.timer);
            this.timer = setTimeout(function() {
                lock(saveButton);
                lock(saveButton2);
                lock(saveAndCloseButton);
                lock(saveAndCloseButton2);
                self._m.show('small-loader');
            }, 200);
            if (self.xhr) { self.xhr.abort(); }
            if ('' === spinId) {
                _id = 'null';
            } else {
                _id = spinId;
            }
            _shortcode = self.value;

            self.xhr = $.post(magictoolbox_WordPress_Magic360_admin_modal_object.ajax, {
                action: "WordPress_Magic360_check_shortcode",
                nonce: magictoolbox_WordPress_Magic360_admin_modal_object.nonce,
                id: _id,
                shortcode: _shortcode
            })
            .success(function(_data) {
                _data = JSON.parse(_data);
                clearTimeout(self.timer);
                if ('verification failed' === _data.error) {
                    self._m.show('ver-failed');
                } else if ('not unique' === _data.error) {
                    self._m.show('not-unique');
                } else {
                    self.isGood = true;
                    self._m.show('good-msg');
                }
                self.xhr = null;

                if (!save) {
                    unLock(saveButton);
                    unLock(saveButton2);
                    unLock(saveAndCloseButton);
                    unLock(saveAndCloseButton2);
                }
            })
            .error(function(e) {
                clearTimeout(self.timer);
                if ('abort' !== e.statusText) {
                    self.showMsg('ajax-error');
                }
                self.xhr = null;
                if (!save) {
                    unLock(saveButton);
                    unLock(saveButton2);
                    unLock(saveAndCloseButton);
                    unLock(saveAndCloseButton2);
                }
            });
        }
    };

    function Message(containerForFind) {
        this.nodes = {
            'small-loader': containerForFind.find('.small-loader'),
            'good-msg': containerForFind.find('.good-msg'),
            'not-unique': containerForFind.find('.not-unique'),
            'incorrect': containerForFind.find('.incorrect'),
            'ver-failed': containerForFind.find('.ver-failed'),
            'ajax-error': containerForFind.find('.ajax-error')
        };
    }

    Message.prototype.show = function(_msg) {
        this.allHide(_msg);
        this.nodes[_msg].css('display','inline-block');
    }

    Message.prototype.allHide = function(except) {
        for (var i in this.nodes) {
            if (except !== i) {
                this.nodes[i].css('display', 'none');
            }
        }
    }

    function Watermark(node) {
        var self = this;
        this.node = node;

        $(this.node.children()[0]).on('click', function(e) {
            self.node.css('background-image', '');
            self.node.addClass('img-is-missing');
            self.node.removeAttr('data-url');
            self.node.removeAttr('data-id');
            self.node.removeAttr('title');
            return false;
        });

        this.node.on('click', function(e) {
            if ('custom' === $('#resize-image-param').val()) {
                var wp_media = wp.media({
                    title: 'Watermark image',
                    library: {type: 'image'},
                    multiple: false,
                    button: {text: 'Add'}
                });

                wp_media.on('select', function() {
                    var img = wp_media.state().get('selection').toJSON()[0];
                    self.node.css('background-image', 'url(' + img.url + ')');
                    self.node.attr('data-url', img.url);
                    self.node.attr('data-id', img.id);
                    self.node.attr('title', img.title);
                    self.node.removeClass('img-is-missing');
                });
                wp_media.open();
            }

            return false;
        });
    }

    Watermark.prototype.val = function() {
        return this.node.attr('data-id') || '';
    };

    function calcColRow() {
        var
            result = true,
            _row = 1,
            _col = parseInt(numberOfImages.val()) || 0,
            multi = multiRow.prop('checked');

        if (multi) {
            if (0 !== images.length % _col) {
                result = false;
                _row = 1;
                _col = 0;
            } else {
                _row = Math.floor(images.length / _col);
                // _col = _col / _row;
            }
        } else {
            _col = images.length;
        }

        columns = _col;
        rows = _row;

        if (!multi) {
            numberOfImages.val(columns);
        }

        return result;
    }

    function getStartImgIndex(column, row, maxColumns) {
        var index = 0;

        column = parseInt(column) || 1;

        if (multiRow.prop('checked')) {
            row = parseInt(row) || 1;
            maxColumns = parseInt(maxColumns) || 1;
            index = maxColumns * row - (maxColumns - column) - 1;
        } else {
            index = column;
        }

        if (index > images.length - 1 || index < 0) {
            index = 0;
        }

        return index;
    }

    function setSpin() {
        var i, img, imgs = '', opt = '', o, o2, _startimg;

        if (!images.length || !calcColRow()) {
            return;
        }

        if (currentSpin) {
            window.Magic360.stop();
            // currentSpin.remove();
            // currentSpin = null;
        }

        for (i = 0; i < images.length; i++) {
            imgs += (' ' + images[i].attr('data-url'));
        }

        o = getAdditionalOptionsValues();
        o2 = getOptionsValues();

        o['rows'] = rows;
        o['columns'] = columns;

        opt += toString(o);
        opt += (' images:' + imgs + ';');
        opt += (' large-images:' + imgs + ';');

        if (!currentSpin) {
            currentSpin = $('<a class="Magic360">');
            img = $('<img>');
            currentSpin.append(img[0]);
            toolContainer.append(currentSpin[0]);
        } else {
            img = currentSpin.find('img');
        }

        _startimg = getStartImgIndex(o['start-column'], o['start-row'], o2.numberOfImages);
        img.attr('src', images[_startimg].attr('data-url'));
        currentSpin.attr('href', images[_startimg].attr('data-url'));
        currentSpin.attr('data-magic360-options', opt);
        window.Magic360.start();
    }

    $(document).ready(function() {
        var urls = [];
        mainContainer = $('.shortcode-container');
        imagesContainer = $('.images-container');
        addImagesButton = $('#add-images');
        removeAllImgsButton = $('#remove-images');
        spinName = $('#spin-name');
        spinNameError = $('#spin-name-error');
        shortcodeName = new ShortCodeString($('#shortcode-name'));
        multiRow = $('#multi');
        singleRow = $('#single');
        numberOfImages = $('#axis');
        optionsContainer = $('div.options-container');
        toolContainer = $('div.spin-tool');
        saveButton = $('#save-button');
        saveButton2 = $('#save-button2');
        saveAndCloseButton = $('#save-and-close-button'),
        saveAndCloseButton2 = $('#save-and-close-button2'),
        saveLoader = $('.save-loader');
        defOpt = $('#use-def-opt');
        cusOpt = $('#use-cus-opt');
        watermarkImg = new Watermark($('#watermark-image'));
        defOptions = fromString(mainContainer.attr('data-default-options'));
        imgLoader = $('div.image-loader');
        imgError = $('.img-error');
        multiRowError = $('.multi-row-error');

        spinId = mainContainer.attr('data-spin-id');

        spinName.on('keyup', function(e) {
            if ('' !== $.trim($(this).val())) {
                spinNameError.css('display', 'none');
            }
        });

        singleRow.on('change', function(e) {
            numberOfImages.parent().css('display', 'none');
            multiRowError.html('');
            multiRowError.css('display', 'none');
            clearTimeout(charTimer);
            charTimer = setTimeout(function() {
                setSpin();
            }, 300);
        });

        multiRow.on('change', function(e) {
            numberOfImages.parent().css('display', 'block');
            clearTimeout(charTimer);
            charTimer = setTimeout(function() {
                setSpin();
            }, 300);
        });

        defOpt.on('change', function(e) {
            optionsContainer.css('display', 'none');
        });

        cusOpt.on('change', function(e) {
            optionsContainer.css('display', 'inline-block');
        });

        imagesContainer.children().each(function(i, el) {
            el = $(el);
            images.push(el);
            urls.push(el.attr('data-url'));
        });

        $.each(images, function(i, el) {
            $(el).find('button').on('click', function(e) {
                removeImg($(e.target));
                return false;
            });
        });

        loadImgs(urls, function() {
            $('#resize-image-param').on('change', function(e) {
                $('.wm-opt').prop('disabled', 'custom' !== $(this).val());
                if ('custom' !== $(this).val()) {
                    $(this).closest('table').find('.wm-opt').closest('tr').hide();
                    $('.wm-opt,#watermark-image').closest('tr').addClass('disabled');
                } else {
                    $(this).closest('table').find('.wm-opt').closest('tr').show();
                    $('.wm-opt,#watermark-image').closest('tr').removeClass('disabled');
                }

            });

            options = getOptions();
            resizeOptions = getOptions(true);

            addImagesButton.on('click', addImgs);
            removeAllImgsButton.on('click', function(e) {
                removeImg();
                return false;
            });

            saveButton.on('click', saveSpin);
            saveButton2.on('click', saveSpin);
            saveAndCloseButton.on('click', function (e) {
                close = true;
                saveSpin(e);
                return false;
            });
            saveAndCloseButton2.on('click', function (e) {
                close = true;
                saveSpin(e);
                return false;
            });

            setSpin();

            numberOfImages.on('change', function(e) {
                multiRowError.html('');
                multiRowError.css('display', 'none');
                clearTimeout(charTimer);
                charTimer = setTimeout(function() {
                    setSpin();
                }, 300);
            });
        });
    });
})(jQuery);
