<?php
/**
 * @package Menu Button With Boards List
 * @author digger http://mysmf.ru
 * @copyright 2015
 * @license CC BY-NC-ND 4.0 http://creativecommons.org/licenses/by-nc-nd/4.0/
 * @version 1.0
 */


if (!defined('SMF')) {
    die('Hacking attempt...');
}


/**
 * Load all needed hooks
 */
function loadMenuButtonWithBoardsListHooks()
{
    add_integration_function('integrate_admin_areas', 'addMenuButtonWithBoardsListAdminArea', false);
    add_integration_function('integrate_modify_modifications', 'addMenuButtonWithBoardsListAdminAction', false);
    add_integration_function('integrate_menu_buttons', 'addMenuButtonWithBoardsList', false);
    add_integration_function('integrate_menu_buttons', 'addMenuButtonWithBoardsListCopyright', false);
}


/**
 * Add admin area
 * @param $admin_areas
 */
function addMenuButtonWithBoardsListAdminArea(&$admin_areas)
{
    global $txt;
    loadLanguage('MenuButtonWithBoardsList/');

    $admin_areas['config']['areas']['modsettings']['subsections']['menu_button_with_boards'] = array($txt['menu_button_with_boards']);
}


/**
 * Add admin area action
 * @param $subActions
 */
function addMenuButtonWithBoardsListAdminAction(&$subActions)
{
    $subActions['menu_button_with_boards'] = 'addMenuButtonWithBoardsListAdminSettings';
}


/**
 * @param bool $return_config
 * @return array config vars
 */
function addMenuButtonWithBoardsListAdminSettings($return_config = false)
{
    global $txt, $scripturl, $context;
    loadLanguage('MenuButtonWithBoardsList/');

    $context['page_title'] = $txt['menu_button_with_boards'];
    $context['post_url'] = $scripturl . '?action=admin;area=modsettings;save;sa=menu_button_with_boards';
    $context['settings_message'] = '';

    $config_vars = array(
        array('title', 'menu_button_with_boards_settings'),
        array('text', 'menu_button_with_boards_title'),
        array('text', 'menu_button_with_boards_cats', 'subtext' => $txt['menu_button_with_boards_cats_desc']),
        //array('check', 'menu_button_with_boards_new', 'desc' => $txt['menu_button_with_boards_new_desc']),
    );

    if ($return_config) {
        return $config_vars;
    }

    if (isset($_GET['save'])) {
        checkSession();
        saveDBSettings($config_vars);
        clean_cache();
        redirectexit('action=admin;area=modsettings;sa=menu_button_with_boards');
    }

    prepareDBSettingContext($config_vars);
}

/**
 * Add mod copyright to the forum credit's page
 */
function addMenuButtonWithBoardsListCopyright()
{
    global $context;

    if ($context['current_action'] == 'credits') {
        $context['copyrights']['mods'][] = '<a href="http://mysmf.ru/mods/menu-button-with-boards-list" target="_blank">Menu Button With Boards List</a> &copy; 2013-2015, digger';
    }
}

function addMenuButtonWithBoardsList(&$menu_buttons)
{
    global $txt, $sourcedir, $scripturl, $user_info, $cat_tree, $modSettings;

    $modSettings['menu_button_with_boards_after'] = 'site';
    $modSettings['menu_button_with_boards_cache'] = 60 * 60 * 24;

    if (empty($menu_buttons) || empty($modSettings['menu_button_with_boards_cats'])) {
        return;
    } // don't use in portal blocks

    $modSettings['menu_button_with_boards_after'] = 'site';
    $categories = explode(',', str_replace(' ', '', $modSettings['menu_button_with_boards_cats']));

    if (empty($categories)) {
        return;
    }

    $new_button = cache_get_data('menu_button_with_boards-' . $user_info['id']);

    if (empty($new_button)) {
        require_once($sourcedir . '/Subs-Boards.php');

        getBoardTree();
        $buttonItems = array();
        $buttonSubItems = array();

        foreach ($categories as $categoryID) {
            foreach ($cat_tree[$categoryID]['children'] as $childID => $category) {
                if (!empty($cat_tree[$categoryID]['children'][$childID]['children'])) {
                    foreach ($cat_tree[$categoryID]['children'][$childID]['children'] as $childSubID => $categorySub) {
                        $buttonSubItems[] = array(
                            'title' => $cat_tree[$categoryID]['children'][$childID]['children'][$childSubID]['node']['name'],
                            'href' => $scripturl . '?board=' . $childSubID . '.0',
                            'show' => (array_intersect($user_info['groups'],
                                    $cat_tree[$categoryID]['children'][$childID]['children'][$childSubID]['node']['member_groups']) || $user_info['is_admin']) ? true : false,
                        );
                    }
                }

                $buttonItems[] = array(
                    'title' => $cat_tree[$categoryID]['children'][$childID]['node']['name'],
                    'href' => $scripturl . '?board=' . $childID . '.0',
                    'show' => (array_intersect($user_info['groups'],
                            $cat_tree[$categoryID]['children'][$childID]['node']['member_groups']) || $user_info['is_admin']) ? true : false,
                    'sub_buttons' => $buttonSubItems,
                );
                $buttonSubItems = null;
            }
            $buttonItems[] = array(
                'title' => '<hr />',
                'href' => '',
                'show' => true,
            );
        }

        $new_button = array(
            'boards_list' =>
                array(
                    'title' => $modSettings['menu_button_with_boards_title'],
                    'href' => '',
                    'show' => true,
                    'sub_buttons' => $buttonItems,
                ),
        );

        if (!empty($new_button)) {
            cache_put_data('menu_button_with_boards-' . $user_info['id'], $new_button,
                $modSettings['menu_button_with_boards_cache']);
        }
    }

    $counter = 0;
    foreach (array_keys($menu_buttons) as $area) {
        if (++$counter && $area == $modSettings['menu_button_with_boards_after']) {
            break;
        }
    }
    $menu_buttons = array_merge(array_slice($menu_buttons, 0, $counter),
        array_merge($new_button, array_slice($menu_buttons, $counter)));
}
