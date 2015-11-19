<?php
/**
 * @package Menu Button With Boards List
 * @author digger http://mysmf.ru
 * @copyright 2015
 * @license CC BY-NC-ND 4.0 http://creativecommons.org/licenses/by-nc-nd/4.0/
 * @version 1.0
 */

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF')) {
    require_once(dirname(__FILE__) . '/SSI.php');
} elseif (!defined('SMF')) {
    die('<b>Error:</b> Cannot install - please verify that you put this file in the same place as SMF\'s index.php and SSI.php files.');
}

if ((SMF == 'SSI') && !$user_info['is_admin']) {
    die('Admin privileges required.');
}

// List settings here in the format: setting_key => default_value.  Escape any "s. (" => \")
$mod_settings = array(
    // Settings
    'menu_button_with_boards_cats' => '',
    'menu_button_with_boards_title' => 'List',
    //'menu_button_with_boards_new' => 0,
);

// Update mod settings if applicable
foreach ($mod_settings as $new_setting => $new_value) {
    if (!isset($modSettings[$new_setting])) {
        updateSettings(array($new_setting => $new_value));
    }
}
