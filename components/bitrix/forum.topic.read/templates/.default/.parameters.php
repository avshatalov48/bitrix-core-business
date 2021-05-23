<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("forum"))
	return;
$arTemplateParameters = array(
	"ATTACH_MODE" => array(
		"NAME" => GetMessage("F_ATTACH_MODE"),
		"TYPE" => "LIST",
		"VALUES" => array(
			"THUMB" => GetMessage("F_ATTACH_MODE_THUMB"),
			"NAME" => GetMessage("F_ATTACH_MODE_NAME")
		),
		"MULTIPLE" => "Y",
		"DEFAULT" => array("NAME")
	),
	"ATTACH_SIZE" => Array(
		"NAME" => GetMessage("F_ATTACH_SIZE"),
		"TYPE" => "STRING",
		"DEFAULT" => "90"),

	"SEND_MAIL" => CForumParameters::GetSendMessageRights(GetMessage("F_SEND_MAIL"), "BASE", "E"),
	
/*	"HIDE_USER_ACTION" => array(
		"NAME" => GetMessage("F_HIDE_USER_ACTION"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N"),
*/
	"SHOW_RSS" => array(
		"NAME" => GetMessage("F_SHOW_RSS"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y"),
	"SHOW_NAME_LINK" => array(
		"NAME" => GetMessage("F_SHOW_NAME_LINK"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y"),
	"SEO_USER" => array(
		"NAME" => GetMessage("F_SEO_USER"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y")
);
if (IsModuleInstalled("vote"))
{
	$arTemplateParameters["SHOW_VOTE"] = array(
		"NAME" => GetMessage("F_SHOW_VOTE"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N");
	$arTemplateParameters["VOTE_TEMPLATE"] = array(
		"NAME" => GetMessage("F_VOTE_TEMPLATE"),
		"TYPE" => "STRING",
		"DEFAULT" => "light");
}
?>