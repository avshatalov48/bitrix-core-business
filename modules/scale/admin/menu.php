<?

use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * @global CUser $USER
 * @global CDatabase $DB
 */

if ($USER->IsAdmin()
	&& getenv('BITRIX_VA_VER')
	&& mb_stristr(php_uname('s'), 'linux')
	&& $DB->type == 'MYSQL'
	)
{
	$menu = array(
		"parent_menu" => "global_menu_settings",
		"section" => "scale",
		"sort" => 1645,
		"text" => Loc::getMessage("SCALE_MENU_ITEM"),
		"icon" => "scale_menu_icon",
		"page_icon" => "scale_page_icon",
		"items_id" => "menu_scale",
		"items" => array(),
	);

	$menu["items"][] = array(
		"text" => Loc::getMessage("SCALE_MENU_PANEL_ITEM"),
		"url" => "/bitrix/admin/scale_panel.php?lang=".LANGUAGE_ID,
		"more_url" => array(
			"/bitrix/admin/scale_panel.php"
		)
	);

	$menu["items"][] = array(
		"text" => Loc::getMessage("SCALE_MENU_GRAPH_ITEM"),
		"url" => "/bitrix/admin/scale_graph.php?lang=".LANGUAGE_ID,
		"more_url" => array(
			"/bitrix/admin/scale_graph.php"
		)
	);

	$menu["items"][] = array(
		"text" => Loc::getMessage("SCALE_MENU_ORDER_ITEM"),
		"url" => "/bitrix/admin/scale_order.php?lang=".LANGUAGE_ID,
		"more_url" => array(
			"/bitrix/admin/scale_order.php"
		)
	);

	return $menu;
}
else
{
	return false;
}
?>