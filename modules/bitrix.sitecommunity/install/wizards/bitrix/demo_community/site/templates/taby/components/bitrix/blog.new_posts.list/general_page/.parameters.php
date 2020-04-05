<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("blog"))
	return false;

$arTemplateParameters = array(
	"SEO_USER" => array(
	        "NAME" => GetMessage("B_SEO_USER"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N", 
    ),

);

if (CModule::IncludeModule("socialnetwork"))
{
	$arTemplateParameters["PATH_TO_MESSAGES_CHAT"] = array(
		"NAME" => GetMessage("B_PATH_TO_MESSAGES_CHAT"),
		"DEFAULT" => "/people/messages/chat/#user_id#/",
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"COLS" => 25,
	);
}

if (IsModuleInstalled("video"))
{
	$arTemplateParameters["PATH_TO_VIDEO_CALL"] = array(
		"NAME" => GetMessage("B_PATH_TO_VIDEO_CALL"),
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"DEFAULT" => "/people/video/#user_id#/",
		"COLS" => 25,
	); 
}
?>