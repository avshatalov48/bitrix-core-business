<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arTemplateParameters = array(
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

if (IsModuleInstalled("video"))
{
	$arTemplateParameters["PATH_TO_VIDEO_CALL"] = array(
		"NAME" => GetMessage("BC_PATH_TO_VIDEO_CALL"),
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"DEFAULT" => "/company/personal/video/#user_id#/",
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
?>