<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("blog"))
	return false;

$arThemesMessages = array(
	"blue" => GetMessage("BLG_THEME_BLUE"), 
	"green" => GetMessage("BLG_THEME_GREEN"), 
	"red" => GetMessage("BLG_THEME_RED"), 
	"red2" => GetMessage("BLG_THEME_RED2"), 
	"orange" => GetMessage("BLG_THEME_ORANGE"), 
	);
$arThemes = array();
$dir = trim(preg_replace("'[\\\\/]+'", "/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix/blog/templates/.default/themes/"));
if (is_dir($dir) && $directory = opendir($dir)):
	
	while (($file = readdir($directory)) !== false)
	{
		if ($file != "." && $file != ".." && is_dir($dir.$file))
			$arThemes[$file] = (!empty($arThemesMessages[$file]) ? $arThemesMessages[$file] : mb_strtoupper(mb_substr($file, 0, 1)).mb_strtolower(mb_substr($file, 1)));
	}
	closedir($directory);
endif;
$arTemplateParameters = array(
	"THEME" => array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
		"NAME" => GetMessage("BLG_THEME"),
		"TYPE" => "LIST",
		"VALUES" => $arThemes,
		"MULTIPLE" => "N",
		"DEFAULT" => "blue"),
	"USER_PROPERTY_NAME"=>array(
		"NAME" => GetMessage("USER_PROPERTY_NAME"),
		"TYPE" => "STRING",
		"DEFAULT" => "",	
	),
	"BLOG_URL"=>array(
		"NAME" => GetMessage("ONE_BLOG_BLOG_URL"),
		"TYPE" => "STRING",
		"DEFAULT" => "",	
		"PARENT" => "BASE",
	),
	"NAME_TEMPLATE" => array(
		"TYPE" => "LIST",
		"NAME" => GetMessage("BC_NAME_TEMPLATE"),
		"VALUES" => CComponentUtil::GetDefaultNameTemplates(),
		"MULTIPLE" => "N",
		"ADDITIONAL_VALUES" => "Y",
		"DEFAULT" => "",
	),
	"SHOW_LOGIN" => Array(
		"NAME" => GetMessage("BC_SHOW_LOGIN"),
		"TYPE" => "CHECKBOX",
		"MULTIPLE" => "N",
		"VALUE" => "Y",
		"DEFAULT" =>"Y",
	),			
	"DO_NOT_SHOW_SIDEBAR" => Array(
		"NAME" => GetMessage("BC_DO_NOT_SHOW_SIDEBAR"),
		"TYPE" => "CHECKBOX",
		"MULTIPLE" => "N",
		"VALUE" => "Y",
		"DEFAULT" =>"N",
	),			
	"DO_NOT_SHOW_MENU" => Array(
		"NAME" => GetMessage("BC_DO_NOT_SHOW_MENU"),
		"TYPE" => "CHECKBOX",
		"MULTIPLE" => "N",
		"VALUE" => "Y",
		"DEFAULT" =>"N",
	),
	"USE_SHARE" => Array(
		"NAME" => GetMessage("BC_USE_SHARE"),
		"TYPE" => "CHECKBOX",
		"MULTIPLE" => "N",
		"VALUE" => "Y",
		"DEFAULT" =>"N",
		"REFRESH"=> "Y",
	),
);

if ($arCurrentValues["USE_SHARE"] == "Y")
{
	$arTemplateParameters["SHARE_HIDE"] = array(
		"NAME" => GetMessage("BC_SHARE_HIDE"),
		"TYPE" => "CHECKBOX",
		"VALUE" => "Y",
		"DEFAULT" => "N",
	);
	
	$arTemplateParameters["SHARE_TEMPLATE"] = array(
		"NAME" => GetMessage("BC_SHARE_TEMPLATE"),
		"DEFAULT" => "",
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"COLS" => 25,
		"REFRESH"=> "Y",
	);
	
	if (trim($arCurrentValues["SHARE_TEMPLATE"]) == '')
		$shareComponentTemlate = false;
	else
		$shareComponentTemlate = trim($arCurrentValues["SHARE_TEMPLATE"]);

	include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix/main.share/util.php");

	$arHandlers = __bx_share_get_handlers($shareComponentTemlate);

	$arTemplateParameters["SHARE_HANDLERS"] = array(
		"NAME" => GetMessage("BC_SHARE_SYSTEM"),
		"TYPE" => "LIST",
		"MULTIPLE" => "Y",
		"VALUES" => $arHandlers["HANDLERS"],
		"DEFAULT" => $arHandlers["HANDLERS_DEFAULT"],
	);

	$arTemplateParameters["SHARE_SHORTEN_URL_LOGIN"] = array(
		"NAME" => GetMessage("BC_SHARE_SHORTEN_URL_LOGIN"),
		"TYPE" => "STRING",
		"DEFAULT" => "",
	);
	
	$arTemplateParameters["SHARE_SHORTEN_URL_KEY"] = array(
		"NAME" => GetMessage("BC_SHARE_SHORTEN_URL_KEY"),
		"TYPE" => "STRING",
		"DEFAULT" => "",
	);
}
?>