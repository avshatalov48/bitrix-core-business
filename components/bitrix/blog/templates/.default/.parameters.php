<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("blog"))
	return false;

$arGroupList = Array();
$dbGroup = CBlogGroup::GetList(Array("SITE_ID" => "ASC", "NAME" => "ASC"));
while($arGroup = $dbGroup->Fetch())
{
	$arGroupList[$arGroup["ID"]] = "(".$arGroup["SITE_ID"].") [".$arGroup["ID"]."] ".$arGroup["NAME"];
}

$arThemesMessages = array(
	"blue" => GetMessage("BLG_THEME_BLUE"), 
	"green" => GetMessage("BLG_THEME_GREEN"), 
	"red" => GetMessage("BLG_THEME_RED"), 
	"red2" => GetMessage("BLG_THEME_RED2"), 
	"orange" => GetMessage("BLG_THEME_ORANGE"), 
	);
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


$arTemplateParameters = array(
	"THEME" => array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
		"NAME" => GetMessage("BLG_THEME"),
		"TYPE" => "LIST",
		"VALUES" => $arThemes,
		"MULTIPLE" => "N",
		"DEFAULT" => "blue"),

	"GROUP_ID"=>array(
		"NAME" => GetMessage("GENERAL_PAGE_GROUP_ID"),
		"TYPE" => "LIST",
		"VALUES" => $arGroupList,
		"MULTIPLE" => "Y",
		"DEFAULT" => "",	
		"ADDITIONAL_VALUES" => "Y",
	),
	"SHOW_NAVIGATION" => Array(
	  	"NAME" => GetMessage("B_SHOW_NAVIGATION"),
		"TYPE" => "CHECKBOX",
		"MULTIPLE" => "N",
		"VALUE" => "Y",
		"DEFAULT" =>"Y",
		),
	"USER_PROPERTY_NAME"=>array(
		"NAME" => GetMessage("USER_PROPERTY_NAME"),
		"TYPE" => "STRING",
		"DEFAULT" => "",	
	),
	"PERIOD_NEW_TAGS" => array(
		"NAME" => GetMessage("SEARCH_PERIOD_NEW_TAGS"),
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"DEFAULT" => ""
	),
	"PERIOD" => array(
		"NAME" => GetMessage("SEARCH_PERIOD"),
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"DEFAULT" => ""
	),
	"COLOR_TYPE" => array(
		"NAME" => GetMessage("SEARCH_COLOR_TYPE"),
		"TYPE" => "LIST",
		"TYPE" => "CHECKBOX",
		"MULTIPLE" => "N",
		"DEFAULT" => "Y",
	),
	"WIDTH" => array(
		"NAME" => GetMessage("SEARCH_WIDTH"),
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"DEFAULT" => "100%"
	),
	"SEO_USER" => array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
	        "NAME" => GetMessage("B_SEO_USER"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N", 
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

if (CModule::IncludeModule("socialnetwork"))
{
	$arTemplateParameters["PATH_TO_SONET_USER_PROFILE"] = array(
		"NAME" => GetMessage("BC_PATH_TO_SONET_USER_PROFILE"),
		"DEFAULT" => (IsModuleInstalled("intranet") ? "/company/personal" : "/club")."/user/#user_id#/",
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"COLS" => 25,
	);

	$arTemplateParameters["PATH_TO_MESSAGES_CHAT"] = array(
		"NAME" => GetMessage("BC_PATH_TO_MESSAGES_CHAT"),
		"DEFAULT" => (IsModuleInstalled("intranet") ? "/company/personal" : "/club")."/messages/chat/#user_id#/",
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"COLS" => 25,
	);
}

if (IsModuleInstalled("intranet"))
{
	$arTemplateParameters["PATH_TO_CONPANY_DEPARTMENT"] = array(
		"NAME" => GetMessage("BC_PATH_TO_CONPANY_DEPARTMENT"),
		"DEFAULT" => "/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#",
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"COLS" => 25,
	);
}

$arTemplateParameters["ALLOW_POST_MOVE"] = Array(
		"NAME" => GetMessage("BPE_ALLOW_POST_MOVE"),
		"TYPE" => "CHECKBOX",
		"MULTIPLE" => "N",
		"VALUE" => "Y",
		"DEFAULT" =>"N",
		"REFRESH" => "Y",
	);

if ($arCurrentValues["ALLOW_POST_MOVE"] == "Y")
{
	if(CModule::IncludeModule("socialnetwork"))
	{
		$arTemplateParameters["PATH_TO_USER_POST"] = array(
			"NAME" => GetMessage("BPE_PATH_TO_USER_POST"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		);	
		$arTemplateParameters["PATH_TO_USER_POST_EDIT"] = array(
			"NAME" => GetMessage("BPE_PATH_TO_USER_POST_EDIT"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		);	
		$arTemplateParameters["PATH_TO_USER_DRAFT"] = array(
			"NAME" => GetMessage("BPE_PATH_TO_USER_DRAFT"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		);	
		$arTemplateParameters["PATH_TO_USER_BLOG"] = array(
			"NAME" => GetMessage("BPE_PATH_TO_USER_BLOG"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		);
		
		$arTemplateParameters["PATH_TO_GROUP_POST"] = array(
			"NAME" => GetMessage("BPE_PATH_TO_GROUP_POST"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		);	
		$arTemplateParameters["PATH_TO_GROUP_POST_EDIT"] = array(
			"NAME" => GetMessage("BPE_PATH_TO_GROUP_POST_EDIT"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		);	
		$arTemplateParameters["PATH_TO_GROUP_DRAFT"] = array(
			"NAME" => GetMessage("BPE_PATH_TO_GROUP_DRAFT"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		);	
		$arTemplateParameters["PATH_TO_GROUP_BLOG"] = array(
			"NAME" => GetMessage("BPE_PATH_TO_GROUP_BLOG"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		);
	}
}
?>
