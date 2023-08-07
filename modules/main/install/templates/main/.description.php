<?
IncludeTemplateLangFile(__FILE__);

$sSectionName = GetMessage("MAIN_SECTION_NAME");
/*
$arTemplateDescription["map/.separator"] = array(
	"NAME"			=> GetMessage("MAIN_MAP_NAME"),
	"SEPARATOR"		=> "Y",
	);
*/
/**************************************************************************************
					Компоненты для вывода карты сайта
**************************************************************************************/

$arTemplateDescription["map/default.php"] = array(
	"NAME" => GetMessage("MAIN_MAP_TEMPLATE_NAME"),
	"DESCRIPTION" => GetMessage("MAIN_MAP_TEMPLATE_DESCRIPTION"),
	"ICON"	=> "/bitrix/images/main/components/map.gif",
	);
$arTemplateDescription["profile.php"] = array(
	"NAME" => GetMessage("T_MAIN_PROFILE"),
	"DESCRIPTION" => GetMessage("T_MAIN_PROFILE_DESCRIPTION"),
	"ICON"	=> "/bitrix/images/main/components/user_profile.gif",
	);
?>
