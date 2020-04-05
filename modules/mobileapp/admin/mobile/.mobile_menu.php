<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

IncludeModuleLangFile(__FILE__);

$arMobileMenuItems = array(
	array(
		"type" => "section",
		"sort" => "1000",
		"items" =>	array(
			array(
			"text" => GetMessage("MOBILEAPP_EXIT"),
			"class" => "menu-icon-logout",
			"onclick" => "Menu.logOut();",
			"id" => "application_quit"
			)
		)
	)
);
?>
