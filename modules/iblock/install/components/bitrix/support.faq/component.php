<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */


if(!CModule::IncludeModule("iblock"))
	return;

$arDefaultUrlTemplates404 = array(
	"faq" => "",
	"element" => "#SECTION_ID#/#ELEMENT_ID#/",
	"section" => "#SECTION_ID#/",
);

$arDefaultVariableAliases404 = Array(
	"faq"=>array(),
	"section"=>array("SECTION_ID" => "SECTION_ID"),
	"element"=>array("SECTION_ID" => "SECTION_ID", "ELEMENT_ID" => "ELEMENT_ID"),
);

$arComponentVariables = Array(
	"SECTION_ID",
	"ELEMENT_ID",
	"q",
);

$arDefaultVariableAliases = Array(
	"SECTION_ID"=>"SECTION_ID",
	"ELEMENT_ID"=>"ELEMENT_ID",
	"q"=>"q",
);

$arVariables = array();
if($arParams["SEF_MODE"] == "Y")
{
	$arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);

	$componentPage = CComponentEngine::ParseComponentPath(
		$arParams["SEF_FOLDER"],
		$arUrlTemplates,
		$arVariables
	);

	if(!$componentPage)
		$componentPage = "faq";

	CComponentEngine::InitComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);
	$arResult = array(
			"FOLDER" => $arParams["SEF_FOLDER"],
			"URL_TEMPLATES" => $arUrlTemplates,
			"VARIABLES" => $arVariables,
			"ALIASES" => $arVariableAliases
		);
}
else
{
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases, $arParams["VARIABLE_ALIASES"]);
	CComponentEngine::InitComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);

	$componentPage = "";

	if(isset($arVariables["ELEMENT_ID"]) && intval($arVariables["ELEMENT_ID"]) > 0 && isset($arVariables["SECTION_ID"]) && intval($arVariables["SECTION_ID"]) > 0)
		$componentPage = "element";
	elseif(isset($arVariables["SECTION_ID"]) && intval($arVariables["SECTION_ID"]) > 0)
		$componentPage = "section";
	else
		$componentPage = "faq";

	$arResult = array(
			"FOLDER" => "",
			"URL_TEMPLATES" => Array(
				"news" => htmlspecialcharsbx($APPLICATION->GetCurPage()),
				"section" => htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arVariableAliases["SECTION_ID"]."=#SECTION_ID#"),
				"detail" => htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arVariableAliases["ELEMENT_ID"]."=#ELEMENT_ID#"),
			),
			"VARIABLES" => $arVariables,
			"ALIASES" => $arVariableAliases
		);
}

$this->IncludeComponentTemplate($componentPage);
?>