<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Editting seminar application form");
?><?$APPLICATION->IncludeFile("form/result_edit/default.php", Array(
	"RESULT_ID"		=> $_REQUEST["RESULT_ID"],// Result ID
	"EDIT_ADDITIONAL"	=> "N",		// Allow editing the auxiliary fields
	"EDIT_STATUS"		=> "Y",			// Show status change form
	"LIST_URL"		=> "result_list.php",		// Result list page
	"VIEW_URL"		=> "result_view.php",	// Result view page
	"CHAIN_ITEM_TEXT"	=> "List of seminar application forms",
	"CHAIN_ITEM_LINK"	=> "result_list.php?WEB_FORM_ID=".$_REQUEST["WEB_FORM_ID"],
	)
);?> <?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>