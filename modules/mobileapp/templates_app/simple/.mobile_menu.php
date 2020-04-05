<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/mobileapp/public/.mobile_menu.php");

$arMobileMenuItems = array(
	array(
		"type" => "section",
		"text" => GetMessage("MOBILE_MENU_HEADER"),
		"sort" => "100",
		"items" => array(
			array(
				"text" => GetMessage("MOBILE_MENU_MAIN"),
				"data-url" => SITE_DIR . "#folder#/index.php",
				"class" => "menu-item",
				"id" => "main",

			),
			array(
				"text" => GetMessage("MOBILE_MENU_ITEM", array("#number#" => 1)),
				"data-url" => SITE_DIR . "#folder#/index.php",
				"class" => "menu-item",
				"id" => "point1",

			),
			array(
				"text" => GetMessage("MOBILE_MENU_ITEM", array("#number#" => 2)),
				"data-url" => SITE_DIR . "#folder#/index.php",
				"class" => "menu-item",
				"id" => "point2",

			),
			array(
				"text" => GetMessage("MOBILE_MENU_ITEM", array("#number#" => 3)),
				"data-url" => SITE_DIR . "#folder#/index.php",
				"class" => "menu-item",
				"id" => "point3",

			),
			array(
				"text" => GetMessage("MOBILE_MENU_ABOUT"),
				"data-url" => SITE_DIR . "#folder#/index.php",
				"class" => "menu-item",
				"id" => "point4",

			)
		)
	)
);
?>