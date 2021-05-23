<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arTemplateParameters = array(
	"THUMBNAIL_SIZE" => array(
		"NAME" => GetMessage("P_THUMBS_SIZE"),
		"TYPE" => "STRING",
		"DEFAULT" => "300")
);
/*
if(IsModuleInstalled("search"))
{
	$arTemplateParameters["SHOW_TAGS"] = array(
	        "NAME" => GetMessage("P_SHOW_TAGS"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"DEFAULT" => "N",
	    );
}
*/
?>