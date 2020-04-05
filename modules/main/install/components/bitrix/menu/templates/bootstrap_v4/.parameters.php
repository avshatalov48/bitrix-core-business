<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arThemes = array();

$arThemesMessages = array(
	"site" => GetMessage("F_THEME_SITE"),
	"blue" => GetMessage("F_THEME_BLUE"),
	"yellow" => GetMessage("F_THEME_YELLOW"),
	"green" => GetMessage("F_THEME_GREEN"),
	"white" => GetMessage("F_THEME_LIGHT"),
	"red" => GetMessage("F_THEME_RED")
);


$arTemplateParameters = array(
	"MENU_THEME"=>array(
		"NAME" => GetMessage("MENU_THEME"),
		"TYPE" => "LIST",
		"VALUES" => $arThemesMessages,
		"PARENT" => "BASE",
	)
);
?>
