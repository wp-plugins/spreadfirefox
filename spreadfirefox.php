<?php
/*
Plugin Name: Spread Firefox
Plugin URI: http://www.spreadfirefox.com
Description: Help upgrade the web by showing a friendly 'Spread Firefox' banner to users who are not using firefox.
Version: 1.2
Author: Mike Hostetler <mike@amountaintop.com>, A Mountain Top LLC
Author URI: http://www.amountaintop.com
*/

define('SFX_BUTTON_URL','http://images.spreadfirefox.com/utw/buttons-36.json');

add_action('widgets_init', 'sfx_register');
add_action ('wp_head', 'sfx_dropdown');
register_activation_hook( __FILE__, 'sfx_enable');
register_deactivation_hook( __FILE__, 'sfx_disable');

function sfx_register () {
    register_sidebar_widget('spreadfirefox', 'sfx_widget');
    register_widget_control('spreadfirefox', 'sfx_control');
    wp_enqueue_script('jquery_selectbox', '/' . PLUGINDIR . '/spreadfirefox/js/jquery.selectboxes.js', array('jquery'));
    wp_enqueue_script('spreadfirefox', '/' . PLUGINDIR . '/spreadfirefox/js/spreadfirefox.js', array('thickbox'));
    wp_enqueue_style('spreadfirefox_style',  '/' . PLUGINDIR . '/spreadfirefox/css/style.css', array('thickbox'));
}

function sfx_widget ($strWidgetArgsArray) {
    $strParameterArray = get_option ("spreadfirefox");
    printf ('%s
                <div id="sfx_image_holder">
                    <a id="sfx_image_url" href="http://www.mozilla.com/en-US/firefox/upgrade.html?t=18">
                        <img border="0" id="sfx_image" alt="Spread Firefox" src="http://images.spreadfirefox.com/utw/3.6/FF36_OTHER_120x240.png" />
                    </a>
                </div>
                %s
            %s
        ',
        $strWidgetArgsArray['before_widget'],
		($strParameterArray['strButtonListArray']) ? sfx_apply_image_js() : '',
        $strWidgetArgsArray['after_widget']
    );    
}

function sfx_apply_image_js () {
	$strParameterArray = get_option ("spreadfirefox");
	return sprintf ('<script type="text/javascript">sfx_apply_image("%s","%s","%s","%s", "%s");</script>',
		$strParameterArray['strButtonSize'],
		$strParameterArray['strLocale'],
		$strParameterArray['intAffiliateId'],
		WP_PLUGIN_URL,
		$strParameterArray['strButtonListArray']
	);
}

function sfx_dropdown ($strParameterArray) {
    $strParameterArray = get_option ("spreadfirefox");
    if ($strParameterArray['strButtonListArray']) {
      printf ('
        <div id="sfx_close">[ <a href="#" title="Close this message">close</a> ]</div>
        <div id="sfx_header" onclick="window.location=\'%s\';" class="sfx_header">
            <span id="sfx_message" class="sfx_message">Help Upgrade the Web: Download Firefox 3.6</span>
        </div>',
        'http://www.mozilla.com/en-US/firefox/upgrade.html?uid=' . $strParameterArray['intAffiliateId']
      );
    }
}

function sfx_control () {

    $strParameterArray = get_option ('spreadfirefox');

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $strParameterArray = array (
          'intAffiliateId' => attribute_escape($_POST['sfx_affiliateid']),
          'strLocale' => attribute_escape ($_POST['sfx_locale']),
          'strButtonSize' => attribute_escape ($_POST['sfx_button_size']),
          'strButtonListArray' => attribute_escape($_POST['sfx_button_list'])
        );
        update_option ('spreadfirefox', $strParameterArray);
    }

    printf ('
        <label class="sfx_label">Affiliate Id: <em>(optional)</em><input name="sfx_affiliateid" type="text" class="widefat" value="%s"></label>
        <small class="sfx_label">Note: Affiliate id can be found by logging into your SFx account and clicking on My Account Info.</small>
        <label class="sfx_label"><b>Selected Locale</b>: <span id="sfx_lbl_locale" class="sfx_lbl_locale">%s</span></label>
        <label class="sfx_label"><b>Selected Size</b>: <span id="sfx_lbl_size" class="sfx_lbl_size">%s</span></label>
        <a href="#TB_inline?height=550&width=520&inlineId=sfx_option_window" title="" class="thickbox" onclick="sfx_update_buttons(\'%s\',\'%s\',\'%s\');">Edit Image Options</a>
        <p/>
        %s',
        $strParameterArray['intAffiliateId'],
        ($strParameterArray['strLocale']) ? $strParameterArray['strLocale'] : 'Not Selected',
        ($strParameterArray['strButtonSize']) ? $strParameterArray['strButtonSize'] : 'Not Selected',
        $strParameterArray['strLocale'],
        $strParameterArray['strButtonSize'],
        WP_PLUGIN_URL,
        sfx_control_thickbox($strParameterArray)        
    );

    // Register the thickbox (this helps re-assign the button on an ajax call)
    print ("
        <script type='text/javascript'>
            tb_init('a.thickbox, area.thickbox, input.thickbox');//pass where to apply thickbox
            imgLoader = new Image();// preload image
            imgLoader.src = tb_pathToImage;
        </script>\n
    ");


}

function sfx_control_thickbox ($strParameterArray) {
    
    return sprintf ('
        <div id="sfx_option_window">
            <img class="sfx_ff_logo" src="%s" alt="Firefox Logo" />
            <h3 class="sfx_image_options" >Spread Firefox: Image options</h3>
            <br style="clear:both;"/>
            <label class="sfx_label">Locale:
                <select id="sfx_locale" name="sfx_locale" class="sfx_locale_select widefat" onchange="sfx_update_images();">
                    %s
                </select>
            </label>
            <label class="sfx_label">Size:
                <select id="sfx_button_size" name="sfx_button_size" class="sfx_button_size_select widefat" onchange="sfx_update_images();">
                    %s
                </select>
            </label>
            <input type="hidden" id="sfx_button_list" name="sfx_button_list" value="%s" />
            <p/><a class="button widget-action widget-control-save edit" href="#save:spread-firefox" style="float:none;" onclick="sfx_update_labels();">Done</a>
            <p/>Preview:
            <div id="sfx_image_preview"></div>
        </div>',
        WP_PLUGIN_URL . '/spreadfirefox/images/ff_wordmark.png',
        ($strParameterArray['strLocale']) ? '<option value="' . $strParameterArray['strLocale'] . '">' . $strParameterArray['strLocale'] . '</option>' : '',
        ($strParameterArray['strButtonSize']) ? '<option value="' . $strParameterArray['strButtonSize'] . '">'.$strParameterArray['strButtonSize'].'</option>' : '',
        ($strParameterArray['strButtonListArray']) ? $strParameterArray['strButtonListArray'] : ''

    );
}

function sfx_enable () {

    $strParameterArray = array (
      'intAffiliateId' => null,
      'strLocale' => 'en-US',
      'strButtonSize' => null,
      'strButtonListArray' => null
    );

    if (!get_option('spreadfirefox', $strParameterArray))
        add_option ('spreadfirefox', $strParameterArray);
    else
        update_option ('spreadfirefox', $strParameterArray);


}

function sfx_disable () {
    delete_option ('spreadfirefox');
}

?>
