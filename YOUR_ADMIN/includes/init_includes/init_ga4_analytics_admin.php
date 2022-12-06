<?php
// -----
// Part of the "GA4 Analytics" plugin, created by lat9 (https://vinosdefrutastropicales.com)
// Copyright (c) 2022, Vinos de Frutas Tropicales.
//
define('GA4_ANALYTICS_CURRENT_VERSION', '1.0.0-beta1');

// -----
// Wait until an admin is logged in before installing or updating ...
//
if (!isset($_SESSION['admin_id'])) {
    return;
}

// -----
// Determine the configuration-group id to use for the plugin's settings, creating that
// group if it's not currently present.
//
$configurationGroupTitle = 'GA4 Analytics';
$configuration = $db->Execute(
    "SELECT configuration_group_id
       FROM " . TABLE_CONFIGURATION_GROUP . "
      WHERE configuration_group_title = '$configurationGroupTitle'
      LIMIT 1"
);
if (!$configuration->EOF) {
    $cgi = $configuration->fields['configuration_group_id'];
} else {
    $db->Execute(
        "INSERT INTO " . TABLE_CONFIGURATION_GROUP . " 
            (configuration_group_title, configuration_group_description, sort_order, visible)
         VALUES
            ('$configurationGroupTitle', '$configurationGroupTitle', 1, 1)"
    );
    $cgi = $db->Insert_ID();
    $db->Execute(
        "UPDATE " . TABLE_CONFIGURATION_GROUP . "
            SET sort_order = $cgi
          WHERE configuration_group_id = $cgi
          LIMIT 1"
    );
}

// -----
// If the plugin's configuration settings aren't present, add them now.
//
if (!defined('GA4_ANALYTICS_VERSION')) {
    $db->Execute(
        "INSERT INTO " . TABLE_CONFIGURATION . "
            (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, date_added, sort_order, use_function, set_function)
         VALUES
            ('Plugin Version', 'GA4_ANALYTICS_VERSION', '0.0.0', 'The <em>GA4 Analytics</em> installed version.', $cgi, now(), 1, NULL, 'zen_cfg_read_only('),

            ('GA4 Analytics Measuring ID', 'GA4_ANALYTICS_TRACKING_ID', '', 'Enter the GA4 Analytics <em>Measuring ID</em> provided to you when you registered your site with google.  That ID will start with <code>G-</code>.  Set this value to an empty string (the default) to disable the <em>GA4 Analytics</em> plugin.', $cgi, now(), 5, NULL, NULL)"
    );

    // -----
    // Register the plugin's configuration page for the admin menus.
    //
    zen_register_admin_page('configGA4Analytics', 'BOX_GA4_ANALYTICS_NAME', 'FILENAME_CONFIGURATION', "gID=$cgi", 'configuration', 'Y');

    // -----
    // Let the logged-in admin know that the plugin's been installed.
    //
    define('GA4_ANALYTICS_VERSION', '0.0.0');
    $messageStack->add_session(sprintf(GA4_ANALYTICS_INSTALL_SUCCESS, GA4_ANALYTICS_CURRENT_VERSION), 'success');
}

// -----
// Update the plugin's version, if the version has changed.
//
if (GA4_ANALYTICS_VERSION !== GA4_ANALYTICS_CURRENT_VERSION) {
    $db->Execute(
        "UPDATE " . TABLE_CONFIGURATION . "
            SET configuration_value = '" . GA4_ANALYTICS_CURRENT_VERSION . "',
                last_modified = now()
          WHERE configuration_key = 'GA4_ANALYTICS_VERSION'
          LIMIT 1"
    );
    if (GA4_ANALYTICS_VERSION !== '0.0.0') {
        $messageStack->add_session(sprintf(GA4_ANALYTICS_UPDATE_SUCCESS, GA4_ANALYTICS_VERSION, GA4_ANALYTICS_CURRENT_VERSION), 'success');
    }
}
