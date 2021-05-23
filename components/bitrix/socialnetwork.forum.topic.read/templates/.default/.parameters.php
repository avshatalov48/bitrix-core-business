<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("forum"))
	return;
$arTemplateParameters = array(
/*	"SEND_MAIL" => CForumParameters::GetSendMessageRights(GetMessage("F_SEND_MAIL"), "BASE", "E"),
    "SHOW_RSS" => array(
        "NAME" => GetMessage("F_SHOW_RSS"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y"),
*/
    "SHOW_NAME_LINK" => array(
        "NAME" => GetMessage("F_SHOW_NAME_LINK"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y"),
);
?>