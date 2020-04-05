<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("New seminar application form");
?><?$APPLICATION->IncludeFile("form/result_new/default.php", Array(
	"WEB_FORM_ID"		=> "9",		// Web form ID
	"LIST_URL"		=> "result_list.php",	// Result list page
	"EDIT_URL"		=> "result_edit.php",// Result editing page
	"CHAIN_ITEM_TEXT"	=> "",
	"CHAIN_ITEM_LINK"	=> "result_list.php?WEB_FORM_ID=".$_REQUEST["WEB_FORM_ID"],
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>