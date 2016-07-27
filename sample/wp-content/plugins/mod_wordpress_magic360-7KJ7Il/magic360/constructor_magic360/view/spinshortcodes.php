<?php
    $spinId = null;
    $spin = null;
    $spinName = '';
    $spinShortcode = '';
    $spinMultiRow = false;
    $opt = array();
    $addOpt = array();
    $defOpt = array();
    $defOptStr = '';
    $defOptFlag = true;
    if (array_key_exists('id', $_GET)) {
        if ('new' != $_GET['id']) {
            $spinId = $_GET['id'];
	    $spin = magictoolbox_WordPress_Magic360_get_data("id", $spinId);
            $spin = $spin[0];

            $spinName = $spin->name;
            $spinShortcode = $spin->shortcode;

            $o = explode(';', $spin->options);
            array_pop($o);

            foreach($o as $i) {
                $tmp = explode(':', $i);
                $opt[$tmp[0]] = isset($tmp[1]) ? $tmp[1] : null;
            }

            $spinMultiRow = ($opt['multiRow'] == 'true');
            $defOptFlag = ($opt['useDefOpt'] == 'true');

            $o = explode(';', $spin->additional_options);
            array_pop($o);
            foreach($o as $i) {
                $tmp = explode(':', $i);
                $addOpt[$tmp[0]] = isset($tmp[1]) ? $tmp[1] : '';
            }
        }
    }

    $sett = get_option("WordPressMagic360CoreSettings");
    foreach($sett['default'] as $key => $value) {
        if (empty($value['value'])) {
            $val = $value['default'];
        } else {
            $val = $value['value'];
        }

        if ('Yes' == $val) {
            $val = 'true';
        } else if ('No' == $val) {
            $val = 'false';
        }

        $defOpt[$key] = $val;
    }

    if (!count($addOpt)) {
        foreach($defOpt as $key => $value) {
            if ("watermark-max-width"  == $key ||
                "watermark-max-height" == $key ||
                "watermark-opacity"    == $key ||
                "watermark-position"   == $key ||
                "watermark-offset-x"   == $key ||
                "watermark-offset-y"   == $key) {
                if (empty($opt[$key])) {
                    $opt[$key] = $value;
                }
            } else {
                $addOpt[$key] = $value;
            }
        }

        if (empty($opt["resize-image"])) {
            $opt["resize-image"] = 'medium';
            $opt["watermark"] = '';
            $opt["watermark-to-thumbnail"] = 'true';
            $opt["thumb-max-width"] = 400;
            $opt["thumb-max-height"] = 400;
        }
    }

    foreach ($defOpt as $key => $value) {
        $defOptStr .= ($key.':'.$value.';');
    }

    $_imageSizes = get_intermediate_image_sizes();
    $_imageSizes[] = 'custom';

    //  temp
    if (empty($addOpt["right-click"])) {
        $addOpt["right-click"] = 'false';
    }
?>

<div class="shortcode-container" data-spin-id="<?php echo $spinId; ?>" data-default-options="<?php echo $defOptStr;?>">
    <?php if (null != $spinId) { ?>
    <h1>Edit spin ID #<?php echo $spinId; ?>, '<?php echo $spinName; ?>'</h1>
    <?php } else { ?>
    <h1>Add new spin</h1>
    <?php } ?>
    <div class="save-container">
        <button class="button" id="save-button">Save</button>
        <button class="button button-primary" id="save-and-close-button">Save and close</button>
        <span class="save-loader"><div class="magictoolbox-loader"></div></span>
    </div>
    <div class="tr">
        <div class="td" style="width: 20%;">Name (short title to describe spin)<span style="color: red;">*</span></div>
        <div class="td" style="width: 79%;">
            <input id="spin-name" placeholder="e.g. iPhone 6S" type="text" value="<?php echo $spinName; ?>">
            <span id="spin-name-error" style="color: red;">Please enter shortcode name.</span>
        </div>
    </div>

    <div class="tr">
        <div class="td" style="width: 20%;">Custom shortcode string</div>
        <div class="td" style="width: 79%;">
            <input id="shortcode-name" type="text" value="<?php echo $spinShortcode; ?>">
            <div class="msg-container">
                <span class="small-loader"><div class="magictoolbox-loader"></div></span>
                <span class="good-msg">OK</span>
                <span class="error-msg not-unique">Is not unique.</span>
                <span class="error-msg incorrect">Is not correct. Valid characters 'A-Z', 'a-z'.</span>
                <span class="error-msg ver-failed">Verification failed.</span>
                <span class="error-msg ajax-error">Ajax error.</span>
            </div>

            <br>
            (optional, if you want to use a custom name instead of a number)
        </div>
    </div>

    <div class="tr">
        <div class="td" style="width: 50%; vertical-align: top;">
            <h2>Images</h2>
            <?php
                $loader = null != $spinId;
                if ($spinMultiRow) {
                    $checked = 'checked="checked"';
                    $display = 'style="display: block;"';
                    $singleRow = '';
                } else {
                    $checked = '';
                    $singleRow = 'checked="checked"';
                    $display = 'style="display: none;"';
                }
            ?>

            <div>
                <label>
                    <input type="radio" id="single" name="rows" <?php echo $loader ? 'disabled' : '';?> <?php echo $singleRow; ?>>
                    Single row spin
                </label>
                <label>
                    <input type="radio" id="multi" name="rows" <?php echo $loader ? 'disabled' : '';?> <?php echo $checked; ?>>
                    Multi-row spin
                </label>
            </div>
            <div class="number-of-images"<?php echo $display;?>>Number of images on each row <input id="axis" type="number" size="4" min="0" max="9999" value="<?php echo null != $spinId ? $opt['numberOfImages'] : 0;?>"></div>
            <div class="img-error" style="color: red;">Please choose spin images</div>
            <div class="multi-row-error" style="color: red;"></div>
            <div class="controls" style="margin-top: 15px;">
                <button id="add-images" class="button button-primary" <?php echo $loader ? 'disabled' : '';?>>Add images</button>
                <button id="remove-images" class="button" <?php echo $loader ? 'disabled' : '';?>>Remove all images</button>
            </div>
            <div class="display-container">
                <div class="image-loader" <?php echo $loader ? 'style="display: inline-block;"' : '';?>>
                    <div class="wrapper-box">
                        <div class="magictoolbox-loader"></div>
                        <div>
                            <div class="mtbl-title">Uploading images</div>
                            <div class="mtbl-description"> This could take a minute...</div>
                        </div>
                    </div>
                </div>
                <div class="images-container">
                    <?php
                        if (null != $spinId) {
                            $images = $spin->images;
                            $images = explode(',', $images);

                            $names = array();
                            $urls = array();
                            foreach ($images as $imgId) {
                                $url = wp_get_attachment_url( $imgId );

                                $name = explode('/', $url);
                                $name = $name[count($name) - 1];
                                $name = explode('.', $name);
                                $name = $name[0];

                                $names[] = $name;
                                $urls[$name] = array('url' => $url, 'id' => $imgId);
                            }

                            sort($names);
                            reset($names);

                            foreach ($names as $n) {
                                $url = $urls[$n]['url'];
                                $iid = $urls[$n]['id'];
                    ?>
                                <div class="img-container"
                                    data-img-id="<?php echo $iid; ?>"
                                    data-url="<?php echo $url; ?>"
                                    data-name="<?php echo $n; ?>"
                                    style="background-image: url(<?php echo $url; ?>)">
                                    <button class="magic-button" title="Remove"></button>
                                    <span class="img-name"><?php echo $n; ?></span>
                                </div>
                    <?php
                            }
                        }
                    ?>
                </div>
            </div>
        </div>

        <?php $customResize = ('custom' == $opt["resize-image"]) ? '' : 'disabled'; ?>

        <div class="td" style="width: 45%; padding-right: 5px;">
            <div>
                <fieldset class="inside-options">
                    <legend>Spin size</legend>
                    <table class="table table-condensed">
                        <tbody>
                            <tr>
                                <td><div title="resize-image">Spin image size</div></td>
                                <td>
                                    <select id="resize-image-param" class="form-control input-sm" name="resize-image">
                                        <?php foreach($_imageSizes as $sel) { ?>
                                        <option <?php echo $sel == $opt["resize-image"] ? 'selected' : ''; ?> value="<?php echo $sel; ?>"><?php echo $sel; ?></option>
                                        <?php } ?>
                                    </select>
                                </td>
                            </tr>
                            <tr <?=($opt["resize-image"]!='custom')?'style="display:none"':''?> class="<?php echo $customResize; ?>">
                                <td><div title="thumb-max-width">Spin max width (px)</div></td>
                                <td><input type="text" class="wm-opt form-control input-sm" name="thumb-max-width" <?php echo $customResize; ?> value="<?php echo $opt["thumb-max-width"];?>"></td>
                            </tr>
                            <tr <?=($opt["resize-image"]!='custom')?'style="display:none"':''?> class="<?php echo $customResize; ?>">
                                <td><div title="thumb-max-height">Spin max height (px)</div></td>
                                <td><input type="text" class="wm-opt form-control input-sm" name="thumb-max-height" <?php echo $customResize; ?> value="<?php echo $opt["thumb-max-height"];?>"></td>
                            </tr>
                        </tbody>
                    </table>
                </fieldset>
            </div>

            <div>
                <fieldset class="inside-options">
                    <legend>Watermark</legend>
                    <div style="padding-bottom:10px;">To add a watermark, set the 'Spin image size' to 'custom'.</div>
                    <div class="">
                        <table class="table table-condensed">
                            <tbody>
                                <tr class="<?php echo $customResize; ?>">
                                    <td><div title="watermark">Watermark image</div></td>
                                    <td>
                                        <div id="watermark-image"
                                            class="<?php echo empty($opt["watermark"]) ? 'img-is-missing' : ''; ?>"
                                            name="watermark"
                                            style="<?php echo empty($opt["watermark"]) ? '' : 'background-image:url('.wp_get_attachment_url($opt["watermark"]).');';?>"
                                            data-url="<?php echo empty($opt["watermark"]) ? '' : wp_get_attachment_url($opt["watermark"]);?>"
                                            data-id="<?php echo empty($opt["watermark"]) ? '' : $opt["watermark"];?>">
                                            <button class="magic-button" title="Remove"></button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="<?php echo $customResize; ?>">
                                    <td><div title="watermark-to-thumbnail">Watermark on thumbnail</div></td>
                                    <td>
                                        <select class="wm-opt form-control input-sm" name="watermark-to-thumbnail" <?php echo $customResize; ?>>
                                            <option <?php echo 'true' == $opt["watermark-to-thumbnail"] ? 'selected' : ''; ?> value="true">On</option>
                                            <option <?php echo 'false' == $opt["watermark-to-thumbnail"] ? 'selected' : ''; ?> value="false">Off</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr class="<?php echo $customResize; ?>">
                                    <td><div title="watermark-max-width">Maximum width of watermark image</div></td>
                                    <td><input type="text" class="wm-opt form-control input-sm" name="watermark-max-width" <?php echo $customResize; ?> value="<?php echo $opt["watermark-max-width"];?>"></td>
                                </tr>
                                <tr class="<?php echo $customResize; ?>">
                                    <td><div title="watermark-max-height">Maximum height of watermark image</div></td>
                                    <td><input type="text" class="wm-opt form-control input-sm" name="watermark-max-height" <?php echo $customResize; ?> value="<?php echo $opt["watermark-max-height"];?>"></td>
                                </tr>
                                <tr class="<?php echo $customResize; ?>">
                                    <td><div title="watermark-opacity">Watermark image opacity (1-100)</div></td>
                                    <td><input type="text" class="wm-opt form-control input-sm" name="watermark-opacity" <?php echo $customResize; ?> value="<?php echo $opt["watermark-opacity"];?>"></td>
                                </tr>
                                <tr class="<?php echo $customResize; ?>">
                                    <td><div title="watermark-position">Watermark position</div></td>
                                    <td>
                                        <select class="wm-opt form-control input-sm" name="watermark-position" <?php echo $customResize; ?>>
                                            <?php foreach(array('top', 'right', 'bottom', 'left', 'top-left', 'bottom-left', 'top-right', 'bottom-right', 'center', 'stretch') as $sel) { ?>
                                            <option <?php echo $sel == $opt["watermark-position"] ? 'selected' : ''; ?> value="<?php echo $sel; ?>"><?php echo $sel; ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr class="<?php echo $customResize; ?>">
                                    <td><div title="watermark-offset-x">Watermark horizontal offset</div></td>
                                    <td><input type="text" class="wm-opt form-control input-sm" name="watermark-offset-x" <?php echo $customResize; ?> value="<?php echo empty($opt["watermark-offset-x"]) ? 0 : $opt["watermark-offset-x"]; ?>"></td>
                                </tr>
                                <tr class="<?php echo $customResize; ?>">
                                    <td><div title="watermark-offset-y">Watermark vertical offset</div></td>
                                    <td><input type="text" class="wm-opt form-control input-sm" name="watermark-offset-y" <?php echo $customResize; ?> value="<?php echo empty($opt["watermark-offset-y"]) ? 0 : $opt["watermark-offset-y"]; ?>"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>

    <div class="tr">
        <div class="td" style="width: 65%; vertical-align: top;">
            <h2>Settings</h2>
            <div>
                <?php
                    if ($defOptFlag) {
                        $checked = 'checked="checked"';
                        $customSett = '';
                        $display = 'style="display: none;"';
                    } else {
                        $checked = '';
                        $customSett = 'checked="checked"';
                        $display = 'style="display: inline-block;"';
                    }
                ?>

                <label>
                    <input type="radio" id="use-def-opt" name="settings" <?php echo $checked; ?>>Use default settings
                </label>
                <label>
                    <input type="radio" id="use-cus-opt" name="settings" <?php echo $customSett; ?>>Use custom settings &gt;
                </label>

                <!-- <input id="use-def-opt" type="checkbox"  -->
                <?php //echo $checked;?>
                <!-- >Use default options -->
            </div>
            <div class="options-container" <?php echo $display;?>>
                <div class="left-col">
                    <fieldset>
                        <legend>Common settings</legend>
                        <table class="table table-condensed">
                            <tbody>
                                <tr>
                                    <td><div title="start-column">Start column</div></td>
                                    <td><input type="text" class="form-control input-sm" name="start-column" value="<?php echo $addOpt["start-column"];?>"></td>
                                </tr>
                                <tr>
                                    <td><div title="start-row">Start row</div></td>
                                    <td><input type="text" class="form-control input-sm" name="start-row" value="<?php echo $addOpt["start-row"];?>"></td>
                                </tr>
                                <tr>
                                    <td><div title="reverse-column">Rotate spin in opposite direction on X-axis</div></td>
                                    <td>
                                        <select class="form-control input-sm" name="reverse-column">
                                            <option <?php echo 'true' == $addOpt["reverse-column"] ? 'selected' : ''; ?> value="true">On</option>
                                            <option <?php echo 'false' == $addOpt["reverse-column"] ? 'selected' : ''; ?> value="false">Off</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><div title="reverse-row">Rotate spin in opposite direction on Y-axis</div></td>
                                    <td>
                                        <select class="form-control input-sm" name="reverse-row">
                                            <option <?php echo 'true' == $addOpt["reverse-row"] ? 'selected' : ''; ?> value="true">On</option>
                                            <option <?php echo 'false' == $addOpt["reverse-row"] ? 'selected' : ''; ?> value="false">Off</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><div title="initialize-on">When to download the images</div></td>
                                    <td>
                                        <select class="form-control input-sm" name="initialize-on">
                                            <?php foreach(array('load', 'click', 'hover') as $sel) { ?>
                                            <option <?php echo $sel == $addOpt["initialize-on"] ? 'selected' : ''; ?> value="<?php echo $sel; ?>"><?php echo $sel; ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </fieldset>
                    <fieldset>
                        <legend>Autopsin</legend>
                        <table class="table table-condensed">
                            <tbody>
                                <tr>
                                    <td><div title="autospin">Automatically spin the image</div></td>
                                    <td>
                                        <select class="form-control input-sm" name="autospin">
                                            <?php foreach(array('once', 'twice', 'infinite', 'off') as $sel) { ?>
                                            <option <?php echo $sel == $addOpt["autospin"] ? 'selected' : ''; ?> value="<?php echo $sel; ?>"><?php echo $sel; ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><div title="autospin-speed">Speed of auto-spin</div></td>
                                    <td><input type="text" class="form-control input-sm" name="autospin-speed" value="<?php echo $addOpt["autospin-speed"];?>"></td>
                                </tr>
                                <tr>
                                    <td><div title="autospin-direction">Direction of autospin</div></td>
                                    <td>
                                        <select class="form-control input-sm" name="autospin-direction">
                                            <?php foreach(array('clockwise', 'anticlockwise', 'alternate-clockwise', 'alternate-anticlockwise') as $sel) { ?>
                                            <option <?php echo $sel == $addOpt["autospin-direction"] ? 'selected' : ''; ?> value="<?php echo $sel; ?>"><?php echo $sel; ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><div title="autospin-start">Start autospin on page load, click or hover</div></td>
                                    <td>
                                        <select class="form-control input-sm" name="autospin-start">
                                            <?php foreach(array('load', 'hover', 'click') as $sel) { ?>
                                            <option <?php echo $sel == $addOpt["autospin-start"] ? 'selected' : ''; ?> value="<?php echo $sel; ?>"><?php echo $sel; ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><div title="autospin-stop">Stop autospin on click, hover or never</div></td>
                                    <td>
                                        <select class="form-control input-sm" name="autospin-stop">
                                            <?php foreach(array('click', 'hover', 'never') as $sel) { ?>
                                            <option <?php echo $sel == $addOpt["autospin-stop"] ? 'selected' : ''; ?> value="<?php echo $sel; ?>"><?php echo $sel; ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </fieldset>
                    <fieldset>
                        <legend>Full-screen &amp; Magnifier</legend>
                        <table class="table table-condensed">
                            <tbody>
                                <tr>
                                    <td><div title="fullscreen">Enable full-screen spin</div></td>
                                    <td>
                                        <select class="form-control input-sm" name="fullscreen">
                                            <option <?php echo 'true' == $addOpt["fullscreen"] ? 'selected' : ''; ?> value="true">On</option>
                                            <option <?php echo 'false' == $addOpt["fullscreen"] ? 'selected' : ''; ?> value="false">Off</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><div title="magnify">Enable magnifier</div></td>
                                    <td>
                                        <select class="form-control input-sm" name="magnify">
                                            <option <?php echo 'true' == $addOpt["magnify"] ? 'selected' : ''; ?> value="true">On</option>
                                            <option <?php echo 'false' == $addOpt["magnify"] ? 'selected' : ''; ?> value="false">Off</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><div title="magnifier-shape">Shape of magnifying glass</div></td>
                                    <td>
                                        <select class="form-control input-sm" name="magnifier-shape">
                                            <?php foreach(array('inner', 'circle', 'square') as $sel) { ?>
                                            <option <?php echo $sel == $addOpt["magnifier-shape"] ? 'selected' : ''; ?> value="<?php echo $sel; ?>"><?php echo $sel; ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><div title="magnifier-width">Width of magnifying glass (if circle or square)</div></td>
                                    <td><input type="text" class="form-control input-sm" name="magnifier-width" value="<?php echo $addOpt["magnifier-width"];?>"></td>
                                </tr>
                            </tbody>
                        </table>
                    </fieldset>
                </div>
                <div class="right-col">
                    <fieldset>
                        <legend>Hint</legend>
                        <table class="table table-condensed">
                            <tbody>
                                <tr>
                                    <td><div title="hint">Show hint message &amp; arrows</div></td>
                                    <td>
                                        <select class="form-control input-sm" name="hint">
                                            <option <?php echo 'true' == $addOpt["hint"] ? 'selected' : ''; ?> value="true">On</option>
                                            <option <?php echo 'false' == $addOpt["hint"] ? 'selected' : ''; ?> value="false">Off</option>
                                        </select>
                                    </td>
                                </tr>
                                <!-- <tr>
                                    <td><div title="hint-text">Text shown on image (for computers)</div></td>
                                    <td><input type="text" class="form-control input-sm" name="hint-text" value="<?php echo $addOpt["hint-text"];?>"></td>
                                </tr>
                                <tr>
                                    <td><div title="mobile-hint-text">Text shown on image (for mobile devices)</div></td>
                                    <td><input type="text" class="form-control input-sm" name="mobile-hint-text" value="<?php echo $addOpt["mobile-hint-text"];?>"></td>
                                </tr> -->
                            </tbody>
                        </table>
                    </fieldset>
                    <fieldset>
                        <legend>Other settings</legend>
                        <table class="table table-condensed">
                            <tbody>
                                <tr>
                                    <td><div title="loop-column">Continue spin after the last image on X-axis</div></td>
                                    <td>
                                        <select class="form-control input-sm" name="loop-column">
                                            <option <?php echo 'true' == $addOpt["loop-column"] ? 'selected' : ''; ?> value="true">On</option>
                                            <option <?php echo 'false' == $addOpt["loop-column"] ? 'selected' : ''; ?> value="false">Off</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><div title="loop-row">Continue spin after the last image on Y-axis</div></td>
                                    <td>
                                        <select class="form-control input-sm" name="loop-row">
                                            <option <?php echo 'true' == $addOpt["loop-row"] ? 'selected' : ''; ?> value="true">On</option>
                                            <option <?php echo 'false' == $addOpt["loop-row"] ? 'selected' : ''; ?> value="false">Off</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><div title="speed">Sensitivity on X-axis:</div></td>
                                    <td><input type="text" class="form-control input-sm" name="sensitivityX" value="<?php echo $addOpt["sensitivityX"];?>"></td>
                                </tr>
                                <tr>
                                    <td><div title="speed">Sensitivity on Y-axis:</div></td>
                                    <td><input type="text" class="form-control input-sm" name="sensitivityY" value="<?php echo $addOpt["sensitivityY"];?>"></td>
                                </tr>
                                <tr>
                                    <td><div title="spin">Method for spinning the image</div></td>
                                    <td>
                                        <select class="form-control input-sm" name="spin">
                                            <?php foreach(array('drag', 'hover', 'none') as $sel) { ?>
                                            <option <?php echo $sel == $addOpt["spin"] ? 'selected' : ''; ?> value="<?php echo $sel; ?>"><?php echo $sel; ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><div title="right-click">Show right-click menu on the spin</div></td>
                                    <td>
                                        <select class="form-control input-sm" name="right-click">
                                            <option <?php echo 'true' == $addOpt["right-click"] ? 'selected' : ''; ?> value="true">On</option>
                                            <option <?php echo 'false' == $addOpt["right-click"] ? 'selected' : ''; ?> value="false">Off</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><div title="mousewheel-step">Number of frames to spin on mousewheel (0 - turn off rotation on mousewheel)</div></td>
                                    <td><input type="text" class="form-control input-sm" name="mousewheel-step" value="<?php echo $addOpt["mousewheel-step"];?>"></td>
                                </tr>
                                <tr>
                                    <td><div title="smoothing">Smoothly stop the image spinning</div></td>
                                    <td>
                                        <select class="form-control input-sm" name="smoothing">
                                            <option <?php echo 'true' == $addOpt["smoothing"] ? 'selected' : ''; ?> value="true">On</option>
                                            <option <?php echo 'false' == $addOpt["smoothing"] ? 'selected' : ''; ?> value="false">Off</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><div title="column-increment">Load only every second (2) or third (3) column so that spins load faster</div></td>
                                    <td><input type="text" class="form-control input-sm" name="column-increment" value="<?php echo $addOpt["column-increment"];?>"></td>
                                </tr>
                                <tr>
                                    <td><div title="row-increment">Load only every second (2) or third (3) row so that spins load faster</div></td>
                                    <td><input type="text" class="form-control input-sm" name="row-increment" value="<?php echo $addOpt["row-increment"];?>"></td>
                                </tr>
                            </tbody>
                        </table>
                    </fieldset>
                </div>
            </div>
        </div>
        <div class="td" style="width: 34%; vertical-align: top;">
            <h2>Preview</h2>
            <div class="spin-tool"></div>
        </div>
    </div>
    <div class="save-container">
        <button class="button" id="save-button2">Save</button>
        <button class="button button-primary" id="save-and-close-button2">Save and close</button>
        <span class="save-loader"><div class="magictoolbox-loader"></div></span>
    </div>
</div>
