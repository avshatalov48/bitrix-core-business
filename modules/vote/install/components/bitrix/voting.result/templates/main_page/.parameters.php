<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arTemplateParameters = array(
	"THEME" => array(
		"NAME" => GetMessage("THEMES"),
		"TYPE" => "LIST",
		"VALUES" => array(
			"colourless" => GetMessage("V_COLOURLESS"), 
			"green" => GetMessage("V_GREEN"), 
			"blue" => GetMessage("V_BLUE")),
		"MULTIPLE" => "N",
		"DEFAULT" => "colourless", 
		"ADDITIONAL_VALUES" => "Y"),
);
?>