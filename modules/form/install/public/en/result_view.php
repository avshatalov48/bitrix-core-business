<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Seminar application form");
?><?$APPLICATION->IncludeFile("form/result_view/default.php", Array(
	"RESULT_ID"		=>	$_REQUEST["RESULT_ID"],// Result ID
	"SHOW_ADDITIONAL"	=>	"N",		// Show auxiliary web form fields
	"SHOW_ANSWER_VALUE"	=>	"N",		// Show the value of ANSWER_VALUE parameter
	"SHOW_STATUS"		=>	"Y",			// Show current result status
	"EDIT_URL"		=>	"result_edit.php",	// Result editing page
	"CHAIN_ITEM_TEXT"	=> "List of seminar application forms",
	"CHAIN_ITEM_LINK"	=> "result_list.php?WEB_FORM_ID=".$_REQUEST["WEB_FORM_ID"],
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>