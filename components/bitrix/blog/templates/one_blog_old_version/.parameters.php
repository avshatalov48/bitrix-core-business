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
);

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

?>