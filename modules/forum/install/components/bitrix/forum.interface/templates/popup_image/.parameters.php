<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arTemplateParameters = array(
	"URL" => array(
		"NAME" => GetMessage("F_URL"),
		"TYPE" => "STRING",
		"DEFAULT" => ""),
	"WIDTH" => array(
		"NAME" => GetMessage("F_WIDTH"),
		"TYPE" => "STRING",
		"DEFAULT" => "300"),
	"HEIGHT" => array(
		"NAME" => GetMessage("F_HEIGHT"),
		"TYPE" => "STRING",
		"DEFAULT" => "300"),
	"FAMILY" => array(
		"NAME" => GetMessage("F_FAMILY"),
		"TYPE" => "STRING",
		"DEFAULT" => ""),
	"CONVERT" => array(
		"NAME" => GetMessage("F_CONVERT"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N"),
/*	"SINGLE" => array(
		"NAME" => GetMessage("F_SINGLE"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y"),
*/
	"RETURN" => array(
		"NAME" => GetMessage("F_RETURN"),
		"TYPE" => "STRING",
		"DEFAULT" => "N"),
);
?>