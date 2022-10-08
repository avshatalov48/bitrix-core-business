<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("forum"))
	return;
/********************************************************************
				Input params
********************************************************************/
$arThemesMessages = array(
	"beige" => GetMessage("F_THEME_BEIGE"), 
	"blue" => GetMessage("F_THEME_BLUE"), 
	"fluxbb" => GetMessage("F_THEME_FLUXBB"), 
	"gray" => GetMessage("F_THEME_GRAY"), 
	"green" => GetMessage("F_THEME_GREEN"), 
	"orange" => GetMessage("F_THEME_ORANGE"), 
	"red" => GetMessage("F_THEME_RED"), 
	"white" => GetMessage("F_THEME_WHITE"));
$arThemes = array();
$dir = trim(preg_replace("'[\\\\/]+'", "/", __DIR__."/themes/"));
if (is_dir($dir) && $directory = opendir($dir)):
	
	while (($file = readdir($directory)) !== false)
	{
		if ($file != "." && $file != ".." && is_dir($dir.$file))
			$arThemes[$file] = (!empty($arThemesMessages[$file]) ? $arThemesMessages[$file] : mb_strtoupper(mb_substr($file, 0, 1)).mb_strtolower(mb_substr($file, 1)));
	}
	closedir($directory);
endif;
$hidden = (!is_set($arCurrentValues, "USE_LIGHT_VIEW") || $arCurrentValues["USE_LIGHT_VIEW"] == "Y" ? "Y" : "N");
/********************************************************************
				/Input params
********************************************************************/

$arTemplateParameters = array(
	"THEME" => array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
		"NAME" => GetMessage("F_THEMES"),
		"TYPE" => "LIST",
		"VALUES" => $arThemes,
		"MULTIPLE" => "N",
		"DEFAULT" => "blue",
		"ADDITIONAL_VALUES" => "Y"),
	"SHOW_TAGS" => array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
		"NAME" => GetMessage("F_SHOW_TAGS"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y"),
	"SEO_USER" => array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
		"NAME" => GetMessage("F_SEO_USER_1"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"VALUES" => array(
			"Y" => GetMessage("F_SEO_USER_2"),
			"N" => GetMessage("F_SEO_USER_3"),
			"TEXT" => GetMessage("F_SEO_USER_4")
		),
		"DEFAULT" => "Y",
		"HIDDEN" => $hidden),
	"SEO_USE_AN_EXTERNAL_SERVICE" => Array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
		"NAME" => GetMessage("F_SEO_USE_AN_EXTERNAL_SERVICE"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
		"HIDDEN" => $hidden),
	"SHOW_FORUM_USERS" => Array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
		"NAME" => GetMessage("F_SHOW_FORUM_USERS"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"HIDDEN" => $hidden),
	"SHOW_SUBSCRIBE_LINK" => array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
		"NAME" => GetMessage("F_SHOW_SUBSCRIBE_LINK"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"HIDDEN" => $hidden),
	"SHOW_AUTH_FORM" => array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
		"NAME" => GetMessage("F_SHOW_AUTH"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y"),
	"SHOW_NAVIGATION" => array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
		"NAME" => GetMessage("F_SHOW_NAVIGATION"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
		"HIDDEN" => $hidden),
	"SHOW_LEGEND" => array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
		"NAME" => GetMessage("F_SHOW_LEGEND"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
		"HIDDEN" => $hidden),
	"SHOW_STATISTIC_BLOCK" => array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
		"NAME" => GetMessage("F_SHOW_STATISTIC_BLOCK"),
		"TYPE" => "LIST",
		"VALUES" => array(
			"STATISTIC" => GetMessage('F_STATISTIC_BLOCK_STATISTIC'),
			"BIRTHDAY" => GetMessage('F_STATISTIC_BLOCK_BIRTHDAY'),
			"USERS_ONLINE" => GetMessage('F_STATISTIC_BLOCK_ONLINE')
		),
		"MULTIPLE" => "Y",
		"DEFAULT" => (is_set($arCurrentValues, "SHOW_STATISTIC") && $arCurrentValues["SHOW_STATISTIC"] == "N" ? array() : array("STATISTIC", "BIRTHDAY", "USERS_ONLINE")),
		"HIDDEN" => $hidden),
	"SHOW_FORUMS" => array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
		"NAME" => GetMessage("F_SHOW_FORUMS"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
		"HIDDEN" => $hidden),
	"SHOW_FIRST_POST" => array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
		"NAME" => GetMessage("F_SHOW_FIRST_POST"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"HIDDEN" => $hidden),
	"SHOW_AUTHOR_COLUMN" => array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
		"NAME" => GetMessage("F_SHOW_AUTHOR_COLUMN"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"HIDDEN" => $hidden),
	"TMPLT_SHOW_ADDITIONAL_MARKER" => Array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
		"NAME" => GetMessage("F_SHOW_ADDITIONAL_MARKER"),
		"TYPE" => "STRING",
		"DEFAULT" => ""),
/*	"SMILES_COUNT" => array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
		"NAME" => GetMessage("F_SMILES_COUNT"),
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"DEFAULT" => "100"),*/
	"PAGE_NAVIGATION_TEMPLATE" => Array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
		"NAME" => GetMessage("F_PAGE_NAVIGATION_TEMPLATE"),
		"TYPE" => "STRING",
		"DEFAULT" => "forum",
		"HIDDEN" => $hidden),
	"PAGE_NAVIGATION_WINDOW" => Array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
		"NAME" => GetMessage("F_PAGE_NAVIGATION_WINDOW"),
		"TYPE" => "STRING",
		"DEFAULT" => "5",
		"HIDDEN" => $hidden),
	"AJAX_POST" => array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
		"NAME" => GetMessage("F_AJAX_POST"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"HIDDEN" => $hidden),
	"WORD_WRAP_CUT" => CForumParameters::GetWordWrapCut(false, "TEMPLATE_TEMPLATES_SETTINGS") + array("HIDDEN" => $hidden),
	"WORD_LENGTH" => CForumParameters::GetWordLength(false, "TEMPLATE_TEMPLATES_SETTINGS") + array("HIDDEN" => $hidden),
);
?>