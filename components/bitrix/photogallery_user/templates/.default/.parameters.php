<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arTemplateParameters = array(
	"INDEX_PAGE_TOP_ELEMENTS_COUNT" => array(
		"NAME" => GetMessage("P_INDEX_PAGE_TOP_ELEMENTS_COUNT"),
		"TYPE" => "STRING",
		"DEFAULT" => "45")
	);

if ($arCurrentValues["ANALIZE_SOCNET_PERMISSION"] == "Y")
{
	$arTemplateParameters = $arTemplateParameters + array(
		"SHOW_ONLY_PUBLIC" => array(
			"NAME" => GetMessage("P_SHOW_ONLY_PUBLIC"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"HIDDEN" => "Y"
		)
	);
	$arCurrentValues["SHOW_ONLY_PUBLIC"] = "Y";
}
else
{
	$arTemplateParameters = $arTemplateParameters + array(
		"SHOW_ONLY_PUBLIC" => array(
			"NAME" => GetMessage("P_SHOW_ONLY_PUBLIC"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y")
	);
}

if ($arCurrentValues["SHOW_ONLY_PUBLIC"] != "N")
{
	if ($arCurrentValues["ANALIZE_SOCNET_PERMISSION"] != "Y")
	{
		$arTemplateParameters["PUBLIC_BY_DEFAULT"] = array(
				"NAME" => GetMessage("P_PUBLIC_BY_DEFAULT"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "N");
	}
	$arTemplateParameters["MODERATE"] = array(
		"NAME" => GetMessage("P_MODERATE"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N");
}

$arTemplateParameters["SHOW_CONTROLS_BUTTONS"] = array(
	"NAME" => GetMessage("P_SHOW_CONTROLS_BUTTONS"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "Y"
);


?>