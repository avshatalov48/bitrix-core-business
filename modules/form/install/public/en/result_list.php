<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Seminar application form");
?><?$APPLICATION->IncludeFile("form/result_list/default.php", Array(
	"WEB_FORM_ID"		=> "9",		// Web form ID
	"VIEW_URL"		=> "result_view.php",// Result view page
	"EDIT_URL"		=> "result_edit.php",// Result editing page
	"NEW_URL"		=> "result_new.php",	// New result creation page
	"SHOW_ADDITIONAL"	=> "N",	// Show auxiliary web form fields in a table of results
	"SHOW_ANSWER_VALUE"	=> "N",	// Show value ANSWER_VALUE in a table of results
	"SHOW_STATUS"		=> "Y",		// Show status for each result in a table of results
	"NOT_SHOW_FILTER"	=> "",	// Codes of fields that are not allowed to show in the filter (comma-separated)
	"NOT_SHOW_TABLE"	=> "",		// Codes of fields that are not allowed to show in the table (comma-separated))
	)
);?><?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>