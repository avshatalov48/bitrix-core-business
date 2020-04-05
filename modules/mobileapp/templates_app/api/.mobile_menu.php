<?
use \Bitrix\Main\Localization\Loc;
Loc::loadLanguageFile(__FILE__);
$appDir = "/#folder#/";
$items = array(
	array(
		"text" => GetMessage("DEMO_MENU_LOCAL_NOTIF"),
		"id"=>"notifications",
	),
	array(
		"text" => GetMessage("DEMO_MENU_LISTS"),
		"id" => "tables",
	),
	array(
		"text" => "Action Sheets",
		"id" => "action_sheet",
	),
	array(
		"text" => GetMessage("DEMO_MENU_TEXT_PANEL"),
		"id" => "text_panel",
	),
	array(
		"text" => GetMessage("DEMO_MENU_NAV_BAR"),
		"id" => "topbar",
	),
//	array(
//		"text" => GetMessage("DEMO_MENU_REFRESH"),
//		"id" => "refresh"
//	),
	array(
		"text" => GetMessage("DEMO_MENU_BUTTONS"),
		"id" => "buttons"
	),
	array(
		"text" => GetMessage("DEMO_MENU_ALERT"),
		"id" => "confirm"
	),
	array(
		"text" => GetMessage("DEMO_MENU_API"),
		"id" => "checkver"
	),
	array(
		"text" => GetMessage("DEMO_MENU_LOADING"),
		"id" => "loading_indicator"
	),
	array(
		"text" => GetMessage("DEMO_MENU_PICKERS"),
		"id" => "pickers"
	),
	array(
		"text" => GetMessage("DEMO_MENU_CONTEXT_MENU"),
		"id" => "context_menu"
	),
	array(
		"text" => GetMessage("DEMO_MENU_SLIDING_PANEL"),
		"id" => "sliding_panel"
	),
	array(
		"text" => GetMessage("DEMO_MENU_QR"),
		"id" => "barcode"
	),
	array(
		"text" => GetMessage("DEMO_MENU_PHOTOGALLERY"),
		"id" => "photo"
	)
//	array(
//		"text" => GetMessage("DEMO_MENU_DOCS"),
//		"id" => "docs"
//	),
//	array(
//		"text" => GetMessage("DEMO_MENU_CHOSE_PHOTO"),
//		"id" => "take_photo"
//	)
);


$arMobileMenuItems = array(
	array(
		"type" => "section",
		"text" => GetMessage("DEMO_MENU_SECTION_MAIN"),
		"sort" => "100",
		"items" =>	array(
			array(
				"text" => GetMessage("DEMO_MENU_MAIN"),
				"data-url" => $appDir."index.php",
				"class" => "menu-item",
				"id" => "main",
			),
		)
	)
);


foreach ($items as $item)
{
	$arMobileMenuItems[0]["items"][] = array(
		"text" => $item["text"],
		"data-url" => $appDir."index.php?page=".$item["id"],
		"class" => "menu-item",
		"id" => "main",
	);
}
?>