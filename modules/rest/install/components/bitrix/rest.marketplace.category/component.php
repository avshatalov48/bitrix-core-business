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

$arParams['CATEGORY'] = !empty($arParams['CATEGORY']) ? $arParams['CATEGORY'] : 'all';
$arParams['SET_TITLE'] = isset($arParams['SET_TITLE']) ? $arParams['SET_TITLE'] : 'Y';

if(!CModule::IncludeModule("rest"))
{
	return;
}

/*$curPage = false;
if (isset($_GET["PAGEN_1"]) && intval($_GET["PAGEN_1"]))
{
	$curPage = intval($_GET["PAGEN_1"]);
}*/

$nav = new \Bitrix\Main\UI\PageNavigation("nav-apps");
$nav->allowAllRecords(false)
	->setPageSize(20)
	->initFromUri();

$curPage = $nav->getCurrentPage();

if (isset($_GET["q"]) && trim($_GET['q']))
{
	$q = trim($_GET['q']);

	if($_GET['dynamic'] == 1)
	{
		\CUtil::decodeURIComponent($q);
	}

	$arCategory = \Bitrix\Rest\Marketplace\Client::searchApp($q, $curPage);

	if($_GET['dynamic'] == 1)
	{
		$APPLICATION->RestartBuffer();

		if($arCategory)
		{
			$arResult["SEARCH_ITEMS"] = $arCategory["ITEMS"];

			if(count($arResult["SEARCH_ITEMS"]) > 0)
			{
				$this->IncludeComponentTemplate('ajax');
			}
		}

		CMain::FinalActions();
		die();
	}

	if($arParams['SET_TITLE'] !== 'N')
	{
		$APPLICATION->SetTitle(GetMessage("MARKETPLACE_CAT_SEARCH"));
	}

	$arResult["CAT_NAME"] = GetMessage("MARKETPLACE_CAT_SEARCH");
}
else
{
	if(isset($_GET['placement']))
	{
		$tag = \Bitrix\Rest\Marketplace\Client::getTagByPlacement($_GET['placement']);

		if(count($tag) > 0)
		{
			$arResult["CAT_NAME"] = GetMessage('MARKETPLACE_CAT_PLACEMENT');
			$arCategory = \Bitrix\Rest\Marketplace\Client::getByTag($tag, $curPage);
		}
		else
		{
			$arCategory = array();
		}
	}
	else
	{
		if(isset($arParams['TAG']) && is_array($arParams['TAG']))
		{
			$arCategory = \Bitrix\Rest\Marketplace\Client::getByTag($arParams['TAG'], $curPage);
			$arResult["CAT_NAME"] = GetMessage('MARKETPLACE_CAT_PLACEMENT');
		}
		else
		{
			$arCategory = \Bitrix\Rest\Marketplace\Client::getCategory($arParams['CATEGORY'], $curPage);
			$arResult["CAT_NAME"] = $arParams['CATEGORY'] == "all"
				? GetMessage("MARKETPLACE_ALL_APPS")
				: $arCategory["CAT_NAME"];
		}

		if($arParams['SET_TITLE'] !== 'N')
		{
			$APPLICATION->SetTitle(htmlspecialcharsbx($arResult["CAT_NAME"]));
		}
	}
}

if($arCategory)
{
	$arResult["ITEMS"] = $arCategory["ITEMS"];

	$listAppCode = array();
	if(is_array($arResult['ITEMS']))
	{
		foreach($arResult['ITEMS'] as $item)
		{
			$listAppCode[] = $item['CODE'];
		}
	}

	$arResult['ITEMS_INSTALLED'] = array();
	if(count($listAppCode) > 0)
	{
		$dbRes = \Bitrix\Rest\AppTable::getList(array(
			'filter' => array(
				'@CODE' => $listAppCode,
				'=ACTIVE' => \Bitrix\Rest\AppTable::ACTIVE
			),
			'select' => array('CODE')
		));
		while($installedApp = $dbRes->fetch())
		{
			$arResult['ITEMS_INSTALLED'][] = $installedApp['CODE'];
		}
	}

	$nav->setRecordCount(intval($arCategory["PAGES"]) * 20);

	$arResult['NAV'] = $nav;
}

CJSCore::Init(array("marketplace"));

$this->IncludeComponentTemplate();
