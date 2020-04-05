<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("bizproc"))
{
	ShowError(GetMessage("BPWC_NO_BP_MODULE"));
	return;
}
if (!CModule::IncludeModule("iblock"))
{
	ShowError(GetMessage("BPWC_NO_IB_MODULE"));
	return;
}

$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
if (strlen($arParams["IBLOCK_TYPE"]) <= 0)
{
	ShowError(GetMessage("BPWC_WC_EMPTY_TYPE"));
	return;
}

$arParams['NAME_TEMPLATE'] = empty($arParams['NAME_TEMPLATE']) ? COption::GetOptionString("bizproc", "name_template", CSite::GetNameFormat(false), SITE_ID) : str_replace(array("#NOBR#","#/NOBR#"), array("",""), $arParams["NAME_TEMPLATE"]);

$arDefaultUrlTemplates404 = array(
	"index" => "index.php",
	"new" => "new.php",
	"list" => "#block_id#/",
	"view" => "#block_id#/view-#bp_id#.php",
	"start" => "#block_id#/start.php",
	"edit" => "#block_id#/edit.php",
	"task" => "#block_id#/task-#task_id#.php",
	"bp" => "#block_id#/bp.php",
	"setvar" => "#block_id#/setvar.php",
	"log" => "#block_id#/log-#bp_id#.php",
	'instances' => 'instances.php'
);
$arDefaultUrlTemplatesN404 = array(
	"index" => "page=index",
	"new" => "page=new",
	"list" => "page=list&block_id=#block_id#",
	"view" => "page=view&block_id=#block_id#&bp_id=#bp_id#",
	"start" => "page=start&block_id=#block_id#",
	"edit" => "page=edit&block_id=#block_id#",
	"task" => "page=task&task_id=#task_id#&block_id=#block_id#",
	"bp" => "page=bp&block_id=#block_id#",
	"setvar" => "page=setvar&block_id=#block_id#",
	"log" => "page=log&block_id=#block_id#&bp_id=#bp_id#",
	'instances' => 'page=instances'
);
$arDefaultVariableAliases404 = array();
$arDefaultVariableAliases = array();

$componentPage = "";

$arComponentVariables = array("page", "task_id", "block_id", "bp_id", "sessid", "saveajax", "export_template", "import_template");

if ($_REQUEST["auth"]=="Y" && $USER->IsAuthorized())
	LocalRedirect($APPLICATION->GetCurPageParam("", array("login", "logout", "register", "forgot_password", "change_password", "backurl", "auth")));

if ($arParams["SEF_MODE"] == "Y")
{
	$arVariables = array();

	$arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);

	$componentPage = CComponentEngine::ParseComponentPath($arParams["SEF_FOLDER"], $arUrlTemplates, $arVariables);

	if (array_key_exists($arVariables["page"], $arDefaultUrlTemplates404))
		$componentPage = $arVariables["page"];

	if (empty($componentPage) || (!array_key_exists($componentPage, $arDefaultUrlTemplates404)))
		$componentPage = "index";

	CComponentEngine::InitComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);

	foreach ($arUrlTemplates as $url => $value)
		$arResult["PATH_TO_".strtoupper($url)] = $arParams["SEF_FOLDER"].$value;
}
else
{
	$arVariables = array();

	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases, $arParams["VARIABLE_ALIASES"]);
	CComponentEngine::InitComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);

	if (array_key_exists($arVariables["page"], $arDefaultUrlTemplates404))
		$componentPage = $arVariables["page"];

	if (empty($componentPage) || (!array_key_exists($componentPage, $arDefaultUrlTemplates404)))
		$componentPage = "index";

	foreach ($arDefaultUrlTemplatesN404 as $url => $value)
		$arResult["PATH_TO_".strtoupper($url)] = $GLOBALS["APPLICATION"]->GetCurPageParam($value, $arComponentVariables);
}

if ($_REQUEST["auth"] == "Y")
	$componentPage = "auth";

if ($arParams["SKIP_BLOCK"] == "Y" && $componentPage == "index")
{
	$componentPage = "list";
	$dbBlockList = CIBlock::GetList(
		array("SORT" => "ASC", "NAME" => "ASC"),
		array("ACTIVE" => "Y", "SITE_ID" => SITE_ID, "TYPE" => $arParams["IBLOCK_TYPE"])
	);
	while ($arBlock = $dbBlockList->Fetch())
		$arResult["VARIABLES"]["block_id"] = $arBlock["ID"];
}

$arResult = array_merge(
	array(
		"SEF_MODE" => $arParams["SEF_MODE"],
		"SEF_FOLDER" => $arParams["SEF_FOLDER"],
		"VARIABLES" => $arVariables,
		"ALIASES" => $arParams["SEF_MODE"] == "Y"? array(): $arVariableAliases,
		"SET_TITLE" => $arParams["SET_TITLE"],
		"SET_NAV_CHAIN" => $arParams["SET_NAV_CHAIN"],
		"ADMIN_ACCESS" => $arParams["ADMIN_ACCESS"],
		"COMPONENT_TEMPLATES" => array(),
	),
	$arResult
);

if (isset($arResult["VARIABLES"]["block_id"]))
{
	global $CACHE_MANAGER;

	$cacheTag = 'component_bizproc_wizards_templates_'.$arParams["IBLOCK_TYPE"];

	if ($CACHE_MANAGER->Read(86400, $cacheTag))
	{
		$arComponentTemplates = $CACHE_MANAGER->Get($cacheTag);
	}
	else
	{
		$arComponentTemplates = array();

		$dbIBlock = CIBlock::GetList(
			array(),
			array("TYPE" => $arParams["IBLOCK_TYPE"], "ACTIVE" => "Y", "CHECK_PERMISSIONS" => "N")
		);
		while ($arIBlock = $dbIBlock->Fetch())
		{
			if (strlen($arIBlock["DESCRIPTION"]) > 0 && substr($arIBlock["DESCRIPTION"], 0, strlen("v2:")) == "v2:")
			{
				$v1 = @unserialize(substr($arIBlock["DESCRIPTION"], 3));
				if (is_array($v1))
					$arComponentTemplates[$arIBlock["ID"]] = $v1["COMPONENT_TEMPLATES"];
			}
		}

		$CACHE_MANAGER->Set($cacheTag, $arComponentTemplates);
	}

	$arResult["COMPONENT_TEMPLATES"] = (array_key_exists($arResult["VARIABLES"]["block_id"], $arComponentTemplates) ? $arComponentTemplates[$arResult["VARIABLES"]["block_id"]] : array());
}

$arParams["ERROR_MESSAGE"] = "";
$arParams["NOTE_MESSAGE"] = "";

$this->IncludeComponentTemplate($componentPage);
?>