<?php

use Bitrix\Main;
use Bitrix\Sale;

/** @global CMain $APPLICATION */
/** @global CUser $USER */

/** @global CAdminPage $adminPage */
global $adminPage;
/** @global CAdminSidePanelHelper $adminSidePanelHelper */
global $adminSidePanelHelper;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$publicMode = $adminPage->publicMode;
$selfFolderUrl = $adminPage->getSelfFolderUrl();

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

Main\Loader::includeModule('sale');

if(!CBXFeatures::IsFeatureEnabled('SaleAccounts'))
{
	require($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_admin_after.php");

	ShowError(GetMessage("SALE_FEATURE_NOT_ALLOW"));

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

// functions
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/admin_tool.php");

IncludeModuleLangFile(__FILE__);

$request = Main\Context::getCurrent()->getRequest();

$sTableID = "tbl_sale_basket";

$oSort = new CAdminUiSorting($sTableID, "DATE_UPDATE_MAX", "DESC");
$lAdmin = new CAdminUiList($sTableID, $oSort);

$siteName = Array();
$serverName = Array();
$listSite = array();
$dbSite = CSite::GetList();
while ($arSite = $dbSite->Fetch())
{
	$serverName[$arSite["LID"]] = $arSite["SERVER_NAME"];
	$siteName[$arSite["LID"]] = $arSite["NAME"];
	$listSite[$arSite["LID"]] = $arSite["NAME"]." [".$arSite["LID"]."]";
	if ($serverName[$arSite["LID"]] == '')
	{
		if (defined("SITE_SERVER_NAME") && SITE_SERVER_NAME <> '')
			$serverName[$arSite["LID"]] = SITE_SERVER_NAME;
		else
			$serverName[$arSite["LID"]] = COption::GetOptionString("main", "server_name");
	}
}
$arAccessibleSites = array();
$dbAccessibleSites = CSaleGroupAccessToSite::GetList(
		array(),
		array("GROUP_ID" => $GLOBALS["USER"]->GetUserGroupArray()),
		false,
		false,
		array("SITE_ID")
	);
while ($arAccessibleSite = $dbAccessibleSites->Fetch())
{
	if (!in_array($arAccessibleSite["SITE_ID"], $arAccessibleSites))
		$arAccessibleSites[] = $arAccessibleSite["SITE_ID"];
}

$listGroup = array();
$groupQueryObject = CGroup::getDropDownList("AND ID!=2");
while ($group = $groupQueryObject->fetch())
{
	$listGroup[$group["REFERENCE_ID"]] = $group["REFERENCE"];
}
$listCurrency = array();
$currencyList = Bitrix\Currency\CurrencyManager::getCurrencyList();
foreach ($currencyList as $currencyId => $currencyName)
{
	$listCurrency[$currencyId] = $currencyName;
}

$filterFields = array(
	array(
		"id" => "NAME_SEARCH",
		"name" => GetMessage('SB_UNIVERSAL'),
		"filterable" => "%",
		"quickSearch" => "%",
		"default" => true
	),
	array(
		"id" => "USER_ID",
		"name" => GetMessage('SB_USER_ID'),
		"type" => "custom_entity",
		"selector" => array("type" => "user"),
		"filterable" => ""
	),
	array(
		"id" => "FUSER_ID",
		"name" => GetMessage('SB_FUSER_ID'),
		"type" => "number",
		"filterable" => ""
	),
	array(
		"id" => "USER_LOGIN",
		"name" => GetMessage("SB_USER_LOGIN"),
		"filterable" => "",
		"default" => true
	),
	array(
		"id" => "PRICE_ALL",
		"name" => GetMessage("SB_PRICE_ALL"),
		"type" => "number",
		"filterable" => ""
	),
	array(
		"id" => "QUANTITY_ALL",
		"name" => GetMessage("SB_QUANTITY_ALL"),
		"type" => "number",
		"filterable" => ""
	),
	array(
		"id" => "PR_COUNT",
		"name" => GetMessage("SB_CNT"),
		"type" => "number",
		"filterable" => ""
	),
	array(
		"id" => "BASKET_TYPE",
		"name" => GetMessage("SB_BASKET_TYPE"),
		"type" => "list",
		"items" => array(
			"CAN_BUY" => GetMessage("SB_TYPE_CAN_BUY"),
			"DELAY" => GetMessage("SB_TYPE_DELAY"),
			"SUBSCRIBE" => GetMessage("SB_TYPE_SUBCRIBE"),
		),
		"filterable" => ""
	),
	array(
		"id" => "DATE_INSERT",
		"name" => GetMessage("SB_DATE_INSERT"),
		"type" => "date",
		"filterable" => ""
	),
	array(
		"id" => "DATE_UPDATE",
		"name" => GetMessage("SB_DATE_UPDATE"),
		"type" => "date",
		"filterable" => ""
	),
	array(
		"id" => "PRODUCT_ID",
		"name" => GetMessage("SB_QUANTITY_ALL"),
		"type" => "custom_entity",
		"selector" => array("type" => "product"),
		"filterable" => ""
	),
	array(
		"id" => "CURRENCY",
		"name" => GetMessage("SB_CURRENCY"),
		"type" => "list",
		"items" => $listCurrency,
		"filterable" => ""
	),
	array(
		"id" => "USER_GROUP_ID",
		"name" => GetMessage("SB_USER_GROUP_ID"),
		"type" => "list",
		"items" => $listGroup,
		"params" => array("multiple" => "Y"),
		"filterable" => ""
	),
	array(
		"id" => "LID",
		"name" => GetMessage("SB_LID"),
		"type" => "list",
		"items" => $listSite,
		"filterable" => ""
	),
);
$filterPresets = array(
	"find_1" => array(
		"name" => GetMessage("SB_FILTER_WEEK")
	),
	"find_2" => array(
		"name" => GetMessage("SB_FILTER_ALL")
	),
	"find_3" => array(
		"name" => GetMessage("SB_FILTER_PRD")
	)
);
$lAdmin->setFilterPresets($filterPresets);

$arFilter = array("ORDER_ID" => false);

$lAdmin->AddFilter($filterFields, $arFilter);

if (isset($arFilter["BASKET_TYPE"]))
{
	switch ($arFilter["BASKET_TYPE"])
	{
		case "CAN_BUY":
			$arFilter["CAN_BUY"] = "Y";
			break;
		case "DELAY":
			$arFilter["DELAY"] = "Y";
			break;
		case "SUBSCRIBE":
			$arFilter["SUBSCRIBE"] = "Y";
			break;
	}
	unset($arFilter["BASKET_TYPE"]);
}

if(!$USER->IsAdmin() && !empty($arAccessibleSites) && count($arAccessibleSites) != count($siteName))
{
	if(empty($arFilter["LID"]))
		$arFilter["LID"] = $arAccessibleSites;
}

if (isset($_REQUEST['action']))
{
	if($_REQUEST['action'] == "order_basket")
	{
		$fuserID = intval($_REQUEST["FUSER_ID"]);
		if($fuserID > 0)
		{
			$userID = intval($_REQUEST["USER_ID"]);
			$siteID = $_REQUEST["SITE_ID"];
			if ($publicMode)
			{
				$url = "/shop/orders/details/0/?lang=" . LANGUAGE_ID . "&SITE_ID=".$siteID."&USER_ID=".$userID."&FUSER_ID=".$fuserID."&ABANDONED=Y";
			}
			else
			{
				$url = $selfFolderUrl."sale_order_create.php?lang=" . LANGUAGE_ID . "&SITE_ID=".$siteID."&USER_ID=".$userID."&FUSER_ID=".$fuserID."&ABANDONED=Y";
			}

			$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

			/** @var Sale\Basket $basketClass */
			$basketClass = $registry->getBasketClassName();

			$basketData = $basketClass::getList([
				'filter' => [
					"=FUSER_ID" => $fuserID,
					"=LID" => $siteID,
					"=ORDER_ID" => false,
					"CAN_BUY" => "Y",
					"DELAY" => "N",
				],
				'limit' => 1
			]);
			if ($basketData->fetch())
			{
				LocalRedirect($url);
			}
		}
	}
}

$by = mb_strtoupper($oSort->getField());
$order = mb_strtoupper($oSort->getOrder());

$dbResultList = CSaleBasket::GetLeave(
	array($by => $order),
	$arFilter,
	false,
	false,
	['DATE_UPDATE_MAX', 'USER_ID', 'PRICE_ALL', 'QUANTITY_ALL', 'PR_COUNT', 'LID', 'DATE_INSERT_MIN', 'FUSER_ID']
);

$dbResultList = new CAdminUiResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->SetNavigationParams($dbResultList, array("BASE_LINK" => $selfFolderUrl."sale_basket.php"));

$lAdmin->AddHeaders(array(
	array("id" => "DATE_UPDATE_MAX", "content" => GetMessage("SB_DATE_UPDATE"), "sort" => "DATE_UPDATE_MAX", "default" => true),
	array("id" => "USER_ID","content" => GetMessage("SB_USER"), "sort" => "user_id", "default" => true),
	array("id" => "PRICE_ALL", "content" => GetMessage("SB_PRICE_ALL"), "sort" => "PRICE_ALL", "default" => true, "align" => "right"),
	array("id" => "QUANTITY_ALL", "content" => GetMessage('SB_QUANTITY_ALL'), "sort" => "QUANTITY_ALL", "default" => false, "align" => "right"),
	array("id" => "PR_COUNT", "content" => GetMessage("SB_CNT"), "sort" => "PR_COUNT", "default" => true, "align" => "right"),
	array("id" => "LID", "content" => GetMessage("SB_LID"),  "sort" => "LID", "default" => count($siteName) !== 1),
	array("id" => "BASKET", "content" => GetMessage("SB_BASKET"), "sort" => "", "default" => true),
	array("id" => "BASKET_NAME", "content" => GetMessage("SB_BASKET_NAME"), "sort" => "", "default" => false),
	array("id" => "BASKET_QUANTITY", "content" => GetMessage("SB_BASKET_QUANTITY"),  "sort" => "", "default" => false, "align" => "right"),
	array("id" => "BASKET_PRICE", "content" => GetMessage("SB_BASKET_PRICE"), "sort" => "", "default" => false, "align" => "right"),
	array("id" => "DATE_INSERT_MIN", "content" => GetMessage("SB_DATE_INSERT"), "sort" => "DATE_INSERT_MIN", "default" => true),
	array("id" => "FUSER_ID", "content" => GetMessage("SB_FUSER_ID"), "sort" => "FUSER_ID", "default" => false),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

$nameFormat = CSite::GetNameFormat();
$usersCache = [];
$userLinkTitle = htmlspecialcharsbx(
	$publicMode
		? GetMessage('SB_FUSER_INFO')
		: GetMessage('SB_USER_INFO')
);
$mailToTitle = htmlspecialcharsbx(GetMessage('SB_MAILTO'));

while ($arBasket = $dbResultList->Fetch())
{
	$row =& $lAdmin->AddRow(false, $arBasket);

	$fieldValue = GetMessage("SB_NOT_AUTH");
	$userId = (int)$arBasket['USER_ID'];
	if ($userId > 0)
	{
		$userEditUrl = $selfFolderUrl."user_edit.php?ID=".$userId."&lang=".LANGUAGE_ID;
		if ($publicMode)
		{
			$userEditUrl = $selfFolderUrl."sale_buyers_profile.php?USER_ID=".$userId."&lang=".LANGUAGE_ID;
			$userEditUrl = $adminSidePanelHelper->editUrlToPublicPage($userEditUrl);
		}
		$fieldValue = "[<a href=".$userEditUrl." title=\"".GetMessage("SB_USER_INFO")."\">".$userId."</a>] ";
		if (!isset($usersCache[$userId]))
		{
			$usersCache[$userId] = false;
			$userData = Main\UserTable::getRow([
				'select' => [
					'ID',
					'LOGIN',
					'NAME',
					'LAST_NAME',
					'SECOND_NAME',
					'EMAIL',
					'TITLE',
				],
				'filter' => [
					'=ID' => $userId,
				],
			]);
			if ($userData !== null)
			{
				$usersCache[$userId] = [
					'ID' => (int)$userData['ID'],
					'NAME' => CUser::FormatName($nameFormat, $userData, true),
					'EMAIL' => (string)$userData['EMAIL'],
				];
			}
		}
		if (!empty($usersCache[$userId]))
		{
			$userEditUrl = $selfFolderUrl."user_edit.php?ID=".$userId."&lang=".LANGUAGE_ID;
			if ($publicMode)
			{
				$userEditUrl = $selfFolderUrl."sale_buyers_profile.php?USER_ID=".$userId."&lang=".LANGUAGE_ID;
				$userEditUrl = $adminSidePanelHelper->editUrlToPublicPage($userEditUrl);
			}
			$fieldValue = '[<a href="' . $userEditUrl . '" title="' . $userLinkTitle . '">' . $userId . '</a>] ';
			$fieldValue .= '<a href="' . $userEditUrl . '" title="' . $userLinkTitle . '">' . $usersCache[$userId]['NAME'] . '</a><br>';
			if ($usersCache[$userId]['EMAIL'] !== '')
			{
				$fieldValue .= '<a href="mailto:' . htmlspecialcharsEx($usersCache[$userId]['EMAIL'])
					. '" title="' . $mailToTitle . '">' . htmlspecialcharsEx($usersCache[$userId]['EMAIL'])
					. '</a>'
				;
			}
		}
	}
	$row->AddField("USER_ID", $fieldValue);
	$row->AddField("LID", "[".htmlspecialcharsbx($arBasket["LID"])."] ".htmlspecialcharsbx($siteName[$arBasket["LID"]] ?? ''));

	$row->AddField(
		"PRICE_ALL",
		SaleFormatCurrency(
			$arBasket["PRICE_ALL"],
			Sale\Internals\SiteCurrencyTable::getSiteCurrency($arBasket["LID"])
		)
	);


	$fieldValue = "";
	$productId = "";
	$arFilterBasket = Array("ORDER_ID" => false, "FUSER_ID" => $arBasket["FUSER_ID"], "LID" => $arBasket["LID"]);
	if(isset($arFilter["CAN_BUY"]))
		$arFilterBasket["CAN_BUY"] = $arFilter["CAN_BUY"];
	if(isset($arFilter["DELAY"]))
		$arFilterBasket["DELAY"] = $arFilter["DELAY"];
	if(isset($arFilter["SUBCRIBE"]))
		$arFilterBasket["SUBCRIBE"] = $arFilter["SUBCRIBE"];

	$bNeedLine = false;
	$basket = "";
	$basketName = "";
	$basketPrice = "";
	$basketQuantity = "";
	$basketAvaible = "";
	$arBasketItems = array();

	$dbB = CSaleBasket::GetList(
		array("ID" => "ASC"),
		$arFilterBasket,
		false,
		false,
		array("ID", "PRODUCT_ID", "NAME", "QUANTITY", "PRICE", "CURRENCY", "DETAIL_PAGE_URL", "LID", "SET_PARENT_ID", "TYPE")
	);
	while($arB = $dbB->Fetch())
	{
		$arBasketItems[] = $arB;
	}

	$arBasketItems = getMeasures($arBasketItems);
	foreach ($arBasketItems as $arB)
	{
		if (CSaleBasketHelper::isSetItem($arB))
			continue;

		$productId .= "&product[]=".$arB["PRODUCT_ID"];
		if ($bNeedLine)
		{
			$basketName .= "<br />";
			$basketPrice .= "<br />";
			$basketQuantity .= "<br />";
			$basketAvaible .= "<br />";
		}
		$bNeedLine = true;

		if($arB["DETAIL_PAGE_URL"] <> '')
		{
			$url = $arB["DETAIL_PAGE_URL"];

			if ($publicMode)
			{
				$elementQueryObject = CIBlockElement::getList(array(), array(
					"ID" => $arB["PRODUCT_ID"]), false, false, array("IBLOCK_ID", "IBLOCK_TYPE_ID"));
				if ($elementData = $elementQueryObject->fetch())
				{
					$url = $selfFolderUrl."cat_product_edit.php?IBLOCK_ID=".$elementData["IBLOCK_ID"].
						"&type=".$elementData["IBLOCK_TYPE_ID"]."&ID=".$arB["PRODUCT_ID"]."&lang=".LANGUAGE_ID."&WF=Y";
					$url = $adminSidePanelHelper->editUrlToPublicPage($url);
				}
			}

			$basketName .= "<nobr><a href=\"".$url."\">";
			$basket .= "<nobr><a href=\"".$url."\">";
		}
		$basket .= htmlspecialcharsbx($arB["NAME"]);
		$basketName .= htmlspecialcharsbx($arB["NAME"]);
		if($arB["DETAIL_PAGE_URL"] <> '')
		{
			$basketName .= "</a></nobr>";
			$basket .= "</a></nobr>";
		}

		$measure = (isset($arB["MEASURE_TEXT"])) ? htmlspecialcharsbx($arB["MEASURE_TEXT"]) : GetMessage("SB_SHT");

		$basket .= " (".$arB["QUANTITY"]." ".$measure.") - "."<nobr>".SaleFormatCurrency($arB["PRICE"], $arB["CURRENCY"])."</nobr><br>";
		$dbProp = CSaleBasket::GetPropsList(Array("SORT" => "ASC", "ID" => "ASC"), Array("BASKET_ID" => $arB["ID"], "!CODE" => array("CATALOG.XML_ID", "PRODUCT.XML_ID")));
		while($arProp = $dbProp -> GetNext())
		{
			$basket .= "<div><small>".$arProp["NAME"].": ".$arProp["VALUE"]."</small></div>";
		}

		$basketPrice .= "<nobr>".SaleFormatCurrency($arB["PRICE"], $arB["CURRENCY"])."</nobr>";
		$basketQuantity .= $arB["QUANTITY"];

	}
	$row->AddField("BASKET", $basket);
	$row->AddField("BASKET_NAME", $basketName);
	$row->AddField("BASKET_PRICE", $basketPrice);
	$row->AddField("BASKET_QUANTITY", $basketQuantity);

	$arActions = Array();
	$orderAction = array(
		"ICON" => "",
		"TEXT" => GetMessage("SB_CREATE_ORDER"),
		"ACTION" => $lAdmin->ActionRedirect("sale_basket.php?FUSER_ID=".$arBasket["FUSER_ID"]."&SITE_ID=".
			$arBasket["LID"]."&USER_ID=".$arBasket["USER_ID"]."&action=order_basket&lang=".LANGUAGE_ID),
		"DEFAULT" => true
	);
	if ($publicMode)
	{
		$orderAction["ACTION"] = "top.BX.adminSidePanel.onOpenPage('/shop/orders/details/0/?FUSER_ID=".
			CUtil::JSEscape($arBasket["FUSER_ID"])."&lang=".LANGUAGE_ID."&SITE_ID=".CUtil::JSEscape($arBasket["LID"]).
			"&USER_ID=".$arBasket["USER_ID"]."');";
	}
	$arActions[] = $orderAction;

	if ((int)$arBasket["USER_ID"] > 0)
	{
		$profileLink = $selfFolderUrl."sale_buyers_profile.php?USER_ID=".$arBasket["USER_ID"]."&lang=".LANGUAGE_ID;
		$profileLink = $adminSidePanelHelper->editUrlToPublicPage($profileLink);
		$arActions[] = array(
			"TEXT" => GetMessage("SB_FUSER_INFO"),
			"LINK" => $profileLink
		);
	}

	$row->AddActions($arActions);
}

$aContext = array();
$lAdmin->setContextSettings(array("pagePath" => $selfFolderUrl."sale_basket.php"));
$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$APPLICATION->SetTitle(GetMessage("SB_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
if (!$publicMode && Sale\Update\CrmEntityCreatorStepper::isNeedStub())
{
	$APPLICATION->IncludeComponent("bitrix:sale.admin.page.stub", ".default");
}
else
{
	$lAdmin->DisplayFilter($filterFields);
	$lAdmin->DisplayList();
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
