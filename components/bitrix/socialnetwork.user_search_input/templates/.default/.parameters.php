<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arTemplateParameters = array(
	"PAGE_ELEMENTS" => array(
		"NAME" => GetMessage("SEARCH_PAGE_ELEMENTS"),
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"DEFAULT" => "10"
	),
	"TEXT" => array(
		"NAME" => GetMessage("SEARCH_TEXT"),
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"DEFAULT" => ""
	),
	"TMPL_IFRAME" => array(
		"NAME" => GetMessage("SEARCH_SHOW_IFRAME"),
		"TYPE" => "CHECKBOX",
		"MULTIPLE" => "N",
		"DEFAULT" => "Y"
	),
);
?>