<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
if (!\Bitrix\Main\Loader::includeModule("catalog"))
{
	ShowError(GetMessage("CATALOG_MODULE_NOT_INSTALL"));
	return;
}

$arDefaultUrlTemplates404 = array(
	"liststores" => "index.php",
	"element" => "#store_id#",
);
$arDefaultUrlTemplatesN404 = array(
	"liststores" => "",
	"element" => "store_id=#store_id#",
);

$arDefaultVariableAliases404 = array();
$arDefaultVariableAliases = array();
$arComponentVariables = array("store_id");

$sefFolder = "/store/";
$arUrlTemplates = array();

if ($arParams["SEF_MODE"] == "Y")
{
	$arVariables = array();

	$arUrlTemplates =
		CComponentEngine::makeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);
	$arVariableAliases = CComponentEngine::makeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);

	$componentPage = CComponentEngine::parseComponentPath($arParams["SEF_FOLDER"], $arUrlTemplates, $arVariables);

	if ($componentPage == '')
		$componentPage = "liststores";

	CComponentEngine::initComponentVariables($componentPage, $arComponentVariables,	$arVariableAliases,	$arVariables);

	foreach ($arUrlTemplates as $url => $value)
		$arResult["PATH_TO_".mb_strtoupper($url)] = $arParams["SEF_FOLDER"].$value;

	$sefFolder = $arParams["SEF_FOLDER"];
}
else
{
	$arVariables = array();
	$arVariableAliases = CComponentEngine::makeComponentVariableAliases($arDefaultVariableAliases, $arParams["VARIABLE_ALIASES"]);
	CComponentEngine::initComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);

	foreach ($arDefaultUrlTemplatesN404 as $url => $value)
		$arResult["PATH_TO_".mb_strtoupper($url)] = $GLOBALS["APPLICATION"]->GetCurPageParam($value, $arComponentVariables);

	$componentPage = "";
	if ((int)$arVariables["store_id"] > 0)
		$componentPage = "element";
	else
		$componentPage = "liststores";
}
$arResult = array_merge(
	array(
		"FOLDER" => $sefFolder,
		"URL_TEMPLATES" => $arUrlTemplates,
		"STORE" => $arVariables["store_id"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
	), $arResult);

$this->IncludeComponentTemplate($componentPage);