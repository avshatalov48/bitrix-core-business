<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("lists"))
	return;

$arTypes = array();
$rsTypes = CLists::GetIBlockTypes();
while($ar = $rsTypes->Fetch())
	$arTypes[$ar["IBLOCK_TYPE_ID"]] = "[".$ar["IBLOCK_TYPE_ID"]."] ".$ar["NAME"];

$arComponentParameters = array(
	"GROUPS" => array(
	),
	"PARAMETERS" => array(
		"IBLOCK_TYPE_ID" => Array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CP_BLM_IBLOCK_TYPE_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arTypes,
			"DEFAULT" => "lists",
		),
		"IS_SEF" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("CP_BLM_IS_SEF"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y",
		),
	),
);

if($arCurrentValues["IS_SEF"] === "Y")
{
	$arComponentParameters["PARAMETERS"]["SEF_BASE_URL"] = array(
		"PARENT" => "URL_TEMPLATES",
		"NAME" => GetMessage("CP_BLM_SEF_BASE_URL"),
		"TYPE" => "STRING",
		"DEFAULT" => "/personal/lists/",
	);
	$arComponentParameters["PARAMETERS"]["SEF_LIST_BASE_URL"] = array(
		"PARENT" => "URL_TEMPLATES",
		"NAME" => GetMessage("CP_BLM_SEF_LIST_BASE_URL"),
		"TYPE"=>"STRING",
		"DEFAULT" => "#list_id#/",
	);
	$arComponentParameters["PARAMETERS"]["SEF_LIST_URL"] = array(
		"PARENT" => "URL_TEMPLATES",
		"NAME" => GetMessage("CP_BLM_SEF_LIST_URL"),
		"TYPE"=>"STRING",
		"DEFAULT" => "#list_id#/view/#section_id#/",
	);
}
else
{
	$arComponentParameters["PARAMETERS"]["LIST_URL"] = CListsParameters::GetPathTemplateParam(
		"LIST",
		"SEF_LIST_URL",
		GetMessage("CP_BLM_LIST_URL"),
		"lists.list.php?list_id=#list_id#",
		"URL_TEMPLATES"
	);
	$arComponentParameters["PARAMETERS"]["LIST_ID"] = array(
		"PARENT" => "URL_TEMPLATES",
		"NAME"=>GetMessage("CP_BLM_LIST_ID"),
		"TYPE"=>"STRING",
		"DEFAULT"=>'={$_REQUEST["list_id"]}',
	);
}

$arComponentParameters["PARAMETERS"]["CACHE_TIME"] = array("DEFAULT"=>3600);
?>
