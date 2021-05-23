<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arTemplateParameters = array(
	"SHOW_TAGS" => array(
		"NAME" => GetMessage("F_SHOW_TAGS"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y"),
	"SMILES_COUNT" => array(
		"NAME" => GetMessage("F_SMILES_COUNT"),
		"TYPE" => "STRING",
		"DEFAULT" => "0"),
	"SEO_USE_AN_EXTERNAL_SERVICE" => Array(
		"NAME" => GetMessage("F_SEO_USE_AN_EXTERNAL_SERVICE"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y")
);
?>