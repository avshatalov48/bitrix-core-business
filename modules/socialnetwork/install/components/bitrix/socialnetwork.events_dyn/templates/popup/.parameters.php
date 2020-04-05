<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("socialnetwork"))
	return false;

$arTemplateParameters = array(
	"PATH_TO_MESSAGES" => array(
		"NAME" => GetMessage("SONET_PATH_TO_MESSAGES"),
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"DEFAULT" => "",
		"COLS" => 25,
		"PARENT" => "URL_TEMPLATES",
    ),
);
?>