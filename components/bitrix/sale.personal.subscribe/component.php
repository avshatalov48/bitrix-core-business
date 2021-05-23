<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

$arDefaultUrlTemplates404 = array(
	"list" => "subscribe_list.php",
	"cancel" => "subscribe_cancel.php?ID=#ID#",
);

$arDefaultVariableAliases404 = array();
$arDefaultVariableAliases = array();
$arComponentVariables = array("ID");
$componentPage = "";
$arVariables = array();
$this->setFramemode(false);

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

	if ($componentPage != "cancel")
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

	if (intval($_REQUEST["ID"]) > 0)
		$componentPage = "cancel";
	else
		$componentPage = "list";

	$arResult = array(
				"VARIABLES" => $arVariables, 
				"ALIASES" => $arVariableAliases
			);

}
$this->IncludeComponentTemplate($componentPage);
?>