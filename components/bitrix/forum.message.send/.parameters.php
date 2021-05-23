<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("forum"))
	return;
$arComponentParameters = Array(
	"GROUPS" => array(
		"URL_TEMPLATES" => array(
			"NAME" => GetMessage("F_URL_TEMPLATES"),
		),
	),
	
	"PARAMETERS" => Array(
	
		"SET_TITLE" => Array(),
		
		"SET_NAVIGATION" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_SET_NAVIGATION"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"DEFAULT" => "Y"
		),
		
		"URL_TEMPLATES_PROFILE_VIEW" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_PROFILE_VIEW_TEMPLATE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "profile_view.php?UID=#UID#",
			"COLS" => 25
		),

		"UID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_DEFAULT_UID"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => '={$_REQUEST["UID"]}',
			"COLS" => 25
		),
		"SEND_MAIL" => CForumParameters::GetSendMessageRights(GetMessage("F_SEND_MAIL"), "BASE", "E"),
		"SEND_ICQ" => CForumParameters::GetSendMessageRights(GetMessage("F_SEND_ICQ"), "BASE", "A", "ICQ"),
	)
);
?>
