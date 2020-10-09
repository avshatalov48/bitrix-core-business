<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

$arDefaultUrlTemplates404 = array(
	"category" => "category/#category#/",
	"detail" => "detail/#app#/",
	"placement_view" => "view/#APP#/",
	"placement" => "placement/#PLACEMENT_ID#/",
	"search" => "search/",
	"buy" => "buy/",
	"updates" => "updates/",
	"installed" => "installed/",
	"booklet" => "booklet/#CODE#/",
);

$arDefaultVariableAliases404 = array();

$arDefaultVariableAliases = array(
	'category' => 'category',
	'app' => 'app'
);

$arComponentVariables = array("category", "app");

$SEF_FOLDER = "";
$arUrlTemplates = array();

if($arParams["SEF_MODE"] == "Y")
{
	$arVariables = array();

	$arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);

	$componentPage = CComponentEngine::ParseComponentPath(
		$arParams["SEF_FOLDER"],
		$arUrlTemplates,
		$arVariables
	);

	if($componentPage == '')
	{
		$componentPage = "category";
	}

	CComponentEngine::InitComponentVariables($componentPage,
		$arComponentVariables,
		$arVariableAliases,
		$arVariables
	);

	$SEF_FOLDER = $arParams["SEF_FOLDER"];

	$arParams["TOP_URL"] = CComponentEngine::MakePathFromTemplate($arParams["SEF_FOLDER"].$arParams["SEF_URL_TEMPLATES"]["top"], $arVariables);
	$arParams["CATEGORY_URL"] = CComponentEngine::MakePathFromTemplate($arParams["SEF_FOLDER"].$arParams["SEF_URL_TEMPLATES"]["category"], $arVariables);
	$arParams["DETAIL_URL"] = CComponentEngine::MakePathFromTemplate($arParams["SEF_FOLDER"].$arParams["SEF_URL_TEMPLATES"]["detail"], $arVariables);
	$arParams["SEARCH_URL"] = CComponentEngine::MakePathFromTemplate($arParams["SEF_FOLDER"].$arParams["SEF_URL_TEMPLATES"]["search"], $arVariables);
	$arParams["BUY_URL"] = CComponentEngine::MakePathFromTemplate($arParams["SEF_FOLDER"].$arParams["SEF_URL_TEMPLATES"]["buy"], $arVariables);
	$arParams["UPDATES_URL"] = CComponentEngine::MakePathFromTemplate($arParams["SEF_FOLDER"].$arParams["SEF_URL_TEMPLATES"]["updates"], $arVariables);
	$arParams["INSTALLED_URL"] = CComponentEngine::MakePathFromTemplate($arParams["SEF_FOLDER"].$arParams["SEF_URL_TEMPLATES"]["installed"], $arVariables);
	$arParams["PLACEMENT_VIEW"] = CComponentEngine::MakePathFromTemplate($arParams["SEF_FOLDER"].$arParams["SEF_URL_TEMPLATES"]["placement_view"], $arVariables);
	$arParams["PLACEMENT"] = CComponentEngine::MakePathFromTemplate($arParams["SEF_FOLDER"].$arParams["SEF_URL_TEMPLATES"]["placement"], $arVariables);
	$arParams["BOOKLET"] = CComponentEngine::MakePathFromTemplate($arParams["SEF_FOLDER"].$arParams["SEF_URL_TEMPLATES"]["booklet"], $arVariables);

	$arParams["CATEGORY_URL_TPL"] = $arParams["SEF_FOLDER"].$arParams["SEF_URL_TEMPLATES"]["category"];
	$arParams["DETAIL_URL_TPL"] = $arParams["SEF_FOLDER"].$arParams["SEF_URL_TEMPLATES"]["detail"];
	if (\CRestUtil::isSlider())
	{
		$request = \Bitrix\Main\Context::getCurrent()->getRequest();
		$arParams["DETAIL_URL_TPL"] .= "?".(new \Bitrix\Main\Web\Uri($arParams["DETAIL_URL_TPL"]))
				->addParams(["IFRAME" => $request->get("IFRAME"), "IFRAME_TYPE" => $request->get("IFRAME_TYPE")])
				->getQuery();
	}
}
else
{
	$arVariables = array();
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases, $arParams["VARIABLE_ALIASES"]);
	CComponentEngine::InitComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);

	$componentPage = "";
	if($arVariables["app"] <> '')
	{
		$componentPage = "detail";
	}
	elseif($arVariables["category"] <> '')
	{
		$componentPage = "category";
	}
	else
	{
		$componentPage = "top";
	}

	if (\CRestUtil::isSlider())
		$arParams['DETAIL_URL_TPL'] = $APPLICATION->GetCurPageParam('app=#app#');
	else
		$arParams['DETAIL_URL_TPL'] = $APPLICATION->GetCurPageParam('app=#app#', array('IFRAME', 'IFRAME_TYPE'));
}


if (!empty($this->request->get('from')))
{
	$from = htmlspecialcharsbx($this->request->get('from'));
	if (mb_strpos($arParams["DETAIL_URL_TPL"], '?') === false)
	{
		$arParams["DETAIL_URL_TPL"] .= '?from=' . $from;
	}
	else
	{
		$arParams["DETAIL_URL_TPL"] .= '&from=' . $from;
	}

	if (mb_strpos($arParams["CATEGORY_URL_TPL"], '?') === false)
	{
		$arParams["CATEGORY_URL_TPL"] .= '?from=' . $from;
	}
	else
	{
		$arParams["CATEGORY_URL_TPL"] .= '&from=' . $from;
	}
}

if($componentPage == 'placement_view')
{
	$componentPage = 'placement';
}
$arResult = array(
	"FOLDER" => $SEF_FOLDER,
	"URL_TEMPLATES" => $arUrlTemplates,
	"VARIABLES" => $arVariables,
	"ALIASES" => $arVariableAliases
);

$arParams["COMPONENT_PAGE"] = $componentPage;

$arResult['SLIDER'] = \CRestUtil::isSlider();

\CJSCore::Init(array('marketplace'));

$this->IncludeComponentTemplate($componentPage);