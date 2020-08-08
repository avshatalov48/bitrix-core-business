<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

$this->setFramemode(false);

if (!CBXFeatures::IsFeatureEnabled('SaleCCards'))
	return;

$arDefaultUrlTemplates404 = array(
	"list" => "cc_list.php",
	"detail" => "cc_detail.php?ID=#ID#",
);

$arDefaultVariableAliases404 = array();
$arDefaultVariableAliases = array();
$arComponentVariables = array("del_id", "ID");
$componentPage = "";
$arVariables = array();

if ($arParams["SEF_MODE"] == "Y")
{
	$arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);

	$componentPage = CComponentEngine::ParseComponentPath(
		$arParams["SEF_FOLDER"],
		$arUrlTemplates,
		$arVariables
	);

	CComponentEngine::InitComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);

	foreach ($arUrlTemplates as $url => $value)
		$arResult["PATH_TO_".ToUpper($url)] = $arParams["SEF_FOLDER"].$value;

	if ($componentPage != "detail")
		$componentPage = "list";

	$arResult = array_merge(
			Array(
				"SEF_FOLDER" => $arParams["SEF_FOLDER"], 
				"URL_TEMPLATES" => $arUrlTemplates, 
				"VARIABLES" => $arVariables, 
				"ALIASES" => $arVariableAliases,
			),
			$arResult
		);
}
else
{
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);
	CComponentEngine::InitComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);

	if ($_REQUEST["ID"] <> '')
		$componentPage = "detail";
	else
		$componentPage = "list";
	$arResult = array(
			"VARIABLES" => $arVariables, 
			"ALIASES" => $arVariableAliases
		);
}
$this->IncludeComponentTemplate($componentPage);
?>