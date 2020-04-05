<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

$arEventTypes = Array();
$langs = CLanguage::GetList(($b=""), ($o=""));
while($language = $langs->Fetch())
{
	$lid = $language["LID"];
	IncludeModuleLangFile(__FILE__, $lid);

	$arEventTypes[] = Array(
		"LID" => $lid,
		"EVENT_NAME" => "INFOPORTAL_ADD_ELEMENT",
		"NAME" => GetMessage("MF_EVENT_NAME"),
		"DESCRIPTION" => GetMessage("MF_EVENT_DESCRIPTION"),
		"SORT" => 200
	);
}

$type = new CEventType;
foreach ($arEventTypes as $arEventType)
	$type->Add($arEventType);

IncludeModuleLangFile(__FILE__);

if(COption::GetOptionString("main", "wizard_first" . substr($wizard->GetID(), 7)  . "_" . $wizard->GetVar("siteID"), false, $wizard->GetVar("siteID")) != "Y"){
	$arMessage = Array(
		"EVENT_NAME" => "INFOPORTAL_ADD_ELEMENT",
		"LID" => WIZARD_SITE_ID,
		"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
		"EMAIL_TO" => "#EMAIL_TO#",
		"SUBJECT" => GetMessage("MF_EVENT_SUBJECT"),
		"MESSAGE" => GetMessage("MF_EVENT_MESSAGE")
	);
	$message = new CEventMessage;
	$message->Add($arMessage);
}
?>