<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arDefaultUrlTemplates404 = array(
	"ticket_list" => "index.php",
	"ticket_edit" => "#ID#.php",
);

$arDefaultVariableAliases = Array(
	"ID" => "ID"
);

$arDefaultVariableAliases404 = Array(
);

$arComponentVariables = Array("ID");

if ($arParams["SEF_MODE"] == "Y")
{
	$arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);

	$componentPage = CComponentEngine::ParseComponentPath(
		$arParams["SEF_FOLDER"],
		$arUrlTemplates,
		$arVariables
	);

	if ($componentPage == "ticket_edit")
		$componentPage = "ticket_edit";
	elseif($_SERVER["REQUEST_METHOD"]=="POST" && ($_REQUEST["save"] <> '' || $_REQUEST["apply"] <> ''))
		$componentPage = "ticket_edit";
	else
		$componentPage = "ticket_list";

	CComponentEngine::InitComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);

	$arResult = Array(
		"FOLDER" => $arParams["SEF_FOLDER"],
		"URL_TEMPLATES" => $arUrlTemplates, 
		"VARIABLES" => $arVariables, 
		"ALIASES" => $arVariableAliases
	);

	$this->IncludeComponentTemplate($componentPage);
}
else
{
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases, $arParams["VARIABLE_ALIASES"]);
	CComponentEngine::InitComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);

	$componentPage = "";

	if (isset($_REQUEST['edit']))
		$componentPage = "ticket_edit";
	else
		$componentPage = "ticket_list";


	$arResult = array(
		"FOLDER" => "",
		"URL_TEMPLATES" => Array(
			"ticket_edit" => htmlspecialcharsbx($APPLICATION->GetCurPage())."?".$arVariableAliases["ID"]."=#ID#&edit=1",
			"ticket_list" => htmlspecialcharsbx($APPLICATION->GetCurPage()),
		),
		"VARIABLES" => $arVariables, 
		"ALIASES" => $arVariableAliases
	);

	$this->IncludeComponentTemplate($componentPage);
}


?>