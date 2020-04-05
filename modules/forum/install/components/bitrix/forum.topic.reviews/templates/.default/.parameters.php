<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arTemplateParameters = array(
	"SHOW_LINK_TO_FORUM" => array(
		"NAME" => GetMessage("F_SHOW_LINK_TO_FORUM"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y"),
	"FILES_COUNT" => array(
		"NAME" => GetMessage("F_FILES_COUNT"),
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"DEFAULT" => "2"),
	"AJAX_POST" => array(
		"NAME" => GetMessage("F_AJAX_POST"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y"),
);
?>