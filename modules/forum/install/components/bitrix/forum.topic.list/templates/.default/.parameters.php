<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arTemplateParameters = array(
	"TMPLT_SHOW_ADDITIONAL_MARKER" => array(
		"NAME" => GetMessage("F_TMPLT_SHOW_ADDITIONAL_MARKER"),
		"TYPE" => "STRING",
		"DEFAULT" => ""),
	"SHOW_RSS" => array(
		"NAME" => GetMessage("F_SHOW_RSS"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y"),
	"SEO_USER" => array(
		"NAME" => GetMessage("F_SEO_USER"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y"),
);
?>