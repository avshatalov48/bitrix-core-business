<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$site = ($_REQUEST["site"] <> ''? $_REQUEST["site"] : ($_REQUEST["src_site"] <> ''? $_REQUEST["src_site"] : false));
$arFilter = Array("TYPE_ID" => "INFOPORTAL_ADD_ELEMENT", "ACTIVE" => "Y");
if($site !== false)
	$arFilter["LID"] = $site;

$arEvent = Array();
$dbType = CEventMessage::GetList($by="ID", $order="DESC", $arFilter);
while($arType = $dbType->GetNext())
	$arEvent[$arType["ID"]] = "[".$arType["ID"]."] ".$arType["SUBJECT"];

$arTemplateParameters = array(
	"SEND_EMAIL" => Array(
		"NAME" => GetMessage("MFP_SEND_EMAIL"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
	),
	"EMAIL_TO" => Array(
		"NAME" => GetMessage("MFP_EMAIL_TO"), 
		"TYPE" => "STRING",
		"DEFAULT" => htmlspecialcharsbx(COption::GetOptionString("main", "email_from")),
	),
	"SUBJECT" => Array(
		"NAME" => GetMessage("MFP_EMAIL_SUBJECT"), 
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("MFP_EMAIL_SUBJECT_DESC"), 
	),
	"EVENT_MESSAGE_ID" => Array(
			"NAME" => GetMessage("MFP_EMAIL_TEMPLATES"), 
			"TYPE"=>"LIST", 
			"VALUES" => $arEvent,
			"DEFAULT"=>"", 
			"MULTIPLE"=>"Y", 
			"COLS"=>25, 
			"PARENT" => "BASE",
	),
);

?>
