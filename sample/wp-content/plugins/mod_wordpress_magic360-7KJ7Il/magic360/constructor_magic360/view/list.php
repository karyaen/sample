<?php
    // function getCurrentURL() {
    //     $currentURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
    //     $currentURL .= $_SERVER["SERVER_NAME"];
    //
    //     if($_SERVER["SERVER_PORT"] != "80" && $_SERVER["SERVER_PORT"] != "443") {
    //         $currentURL .= ":".$_SERVER["SERVER_PORT"];
    //     }
    //
    //     $currentURL .= $_SERVER["REQUEST_URI"];
    //     return $currentURL;
    // }

    // $pageURL = getCurrentURL();

    $pageURL = get_admin_url(null, 'admin.php?page=WordPressMagic360-shortcodes-page');

    $magic = magictoolbox_WordPress_Magic360_get_data();
    $shortcode_img_url = preg_replace('/\/magic360\/constructor_magic360\/view\//is', '/magic360/', plugin_dir_url( __FILE__ ));
    $shortcode_img_url .= 'core/admin_graphics/icon.png';
?>

<div class="list-container">
    <div class="loader"><div class="magictoolbox-loader"></div></div>
    <h1>My spins</h1>
    <p>
        Create 360 spins below, to insert with the <img src="<?php echo $shortcode_img_url; ?>" alt="Magic 360" style="vertical-align: middle;"/> shortcut into any page or post.
    </p>
    <p style="margin-right:20px; float:right; font-size:15px;">
        Resources:
            &nbsp;<a href="<?php echo WordPressMagic360_url('http://www.magictoolbox.com/magic360/integration/',' configuration page resources settings link'); ?>" target="_blank">Documentation<span class="dashicons dashicons-share-alt2" style="text-decoration: none;line-height:1.3;margin-left:5px;"></span></a>&nbsp;|
            &nbsp;<a href="<?php echo WordPressMagic360_url('http://www.magictoolbox.com/magic360/examples/',' configuration page resources examples link'); ?>" target="_blank">Examples<span class="dashicons dashicons-share-alt2" style="text-decoration: none;line-height:1.3;margin-left:5px;"></span></a>&nbsp;|
            &nbsp;<a href="<?php echo WordPressMagic360_url('http://www.magictoolbox.com/contact/','configuration page resources support link'); ?>" target="_blank">Support<span class="dashicons dashicons-share-alt2" style="text-decoration: none;line-height:1.3;margin-left:5px;"></span></a>&nbsp;
    |&nbsp;<a href="<?php echo WordPressMagic360_url('http://www.magictoolbox.com/buy/magic360/','configuration page resources buy link'); ?>" target="_blank">Buy<span class="dashicons dashicons-share-alt2" style="text-decoration: none;line-height:1.3;margin-left:5px;"></span></a>
    </p><br/>
    <?php
        if (count($magic)) {
    ?>
    <button style="margin-right: 5px; margin-bottom: 5px; <?php if (!count($magic)) { echo 'display: none;'; } ?>" id="delete-selected" class="button">Delete selected</button>
    <?php
        }
    ?>
    <a style="margin-right: 5px; margin-bottom: 5px;" class="button button-primary" href="<?php echo $pageURL;?>&id=new">Add spin</a>
    <table class="shortcodes-list">
        <thead>
            <tr <?php if (!count($magic)) { echo 'style="display: none;"'; } ?>>
                <td class="t-cb"><input type="checkbox"></td>
                <td class="t-id">ID</td>
                <td class="t-pv" style="width:auto;">Preview</td>
                <td class="t-name" style="width:50%">Name</td>
                <td class="t-sc" style="width:50%">Shortcode</td>
            </tr>
        </thead>
        <tbody>
            <?php
                if (count($magic)) {
                    foreach($magic as $val) {
                        $start_img = $val->startimg;
                        $url = wp_get_attachment_url( $start_img );
                        $toolId = $val->id;
            ?>
                <tr id="<?php echo $toolId; ?>">
                    <td class="t-cb"><input type="checkbox"></td>
                    <td class="t-id"><?php echo $toolId; ?></td>
                    <td class="t-pv"><img src="<?php echo $url; ?>" /></td>
                    <td class="t-name"><a href="<?php echo $pageURL;?>&id=<?php echo $toolId; ?>"><?php echo $val->name; ?></a></br>
                        <a href="<?php echo $pageURL;?>&id=<?php echo $toolId; ?>">Edit</a> |
                        <a href="#" class="copy-spin" title="Copy spin">Duplicate spin</button></td>
                    <?php
                        $sc = $val->shortcode;
                        if (empty($sc)) {
                            $sc = $val->id;
                        }
                    ?>
                    <td class="t-sc">[magic360 id="<?php echo $sc; ?>"]</td>
                </tr>
            <?php
                    }
                }
            ?>
        </tbody>
    </table>
    <button style="margin-right: 5px; margin-top: 5px; <?php if (0 == count($magic)) { echo 'display: none;'; } ?>" id="delete-selected2" class="button">Delete selected</button>
    <a style="margin-right: 5px; margin-top: 5px; <?php if (0 == count($magic)) { echo 'display: none;'; } ?>" id="new2" class="button button-primary" href="<?php echo $pageURL;?>&id=new">Add spin</a>
</div>
