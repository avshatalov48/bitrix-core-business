<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("blog"))
	return false;

$arTemplateParameters = array(
	"SEO_USER" => array(
			"NAME" => GetMessage("B_SEO_USER"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
	)
);
?>