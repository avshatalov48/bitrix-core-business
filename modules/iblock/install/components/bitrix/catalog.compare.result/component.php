<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Iblock;
use Bitrix\Iblock\InheritedProperty\ElementValues;
use Bitrix\Catalog;
use Bitrix\Currency;

$this->setFrameMode(false);

if (!Loader::includeModule("iblock"))
{
	ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
	return;
}
/*************************************************************************
	Processing of received parameters
*************************************************************************/
unset($arParams['IBLOCK_TYPE']); //was used only for IBLOCK_ID setup with Editor
$arParams['IBLOCK_ID'] = (int)($arParams['IBLOCK_ID'] ?? 0);

$arParams['NAME'] = trim((string)($arParams['NAME'] ?? ''));
if ($arParams['NAME'] === '')
{
	$arParams['NAME'] = 'CATALOG_COMPARE_LIST';
}

$arParams['ELEMENT_SORT_FIELD'] = (string)($arParams['ELEMENT_SORT_FIELD'] ?? '');
if ($arParams['ELEMENT_SORT_FIELD'] === '')
{
	$arParams['ELEMENT_SORT_FIELD'] = 'sort';
}

$arParams['ELEMENT_SORT_ORDER'] = (string)($arParams['ELEMENT_SORT_ORDER'] ?? '');
if (!preg_match('/^(asc|desc|nulls)(,asc|,desc|,nulls){0,1}$/i', $arParams['ELEMENT_SORT_ORDER']))
{
	$arParams['ELEMENT_SORT_ORDER'] = 'asc';
}

$arParams['DETAIL_URL'] = trim((string)($arParams['DETAIL_URL'] ?? ''));
$arParams['BASKET_URL'] = trim((string)($arParams['BASKET_URL'] ?? ''));
if ($arParams['BASKET_URL'] === '')
{
	$arParams['BASKET_URL'] = '/personal/basket.php';
}

$arParams['ACTION_VARIABLE'] = trim((string)($arParams['ACTION_VARIABLE'] ?? ''));
if (
	$arParams['ACTION_VARIABLE'] === ''
	|| !preg_match('/^[A-Za-z_][A-Za-z01-9_]*$/', $arParams['ACTION_VARIABLE'])
)
{
	$arParams['ACTION_VARIABLE'] = 'action';
}

$arParams['PRODUCT_ID_VARIABLE'] = trim((string)($arParams['PRODUCT_ID_VARIABLE'] ?? ''));
if (
	$arParams['PRODUCT_ID_VARIABLE'] === ''
	|| !preg_match('/^[A-Za-z_][A-Za-z01-9_]*$/', $arParams['PRODUCT_ID_VARIABLE'])
)
{
	$arParams['PRODUCT_ID_VARIABLE'] = 'id';
}

$arParams['SECTION_ID_VARIABLE'] = trim((string)($arParams['SECTION_ID_VARIABLE'] ?? ''));
if (
	$arParams['SECTION_ID_VARIABLE'] === ''
	|| !preg_match('/^[A-Za-z_][A-Za-z01-9_]*$/', $arParams['SECTION_ID_VARIABLE'])
)
{
	$arParams['SECTION_ID_VARIABLE'] = 'SECTION_ID';
}

if (!isset($arParams['PROPERTY_CODE']) || !is_array($arParams['PROPERTY_CODE']))
{
	$arParams['PROPERTY_CODE'] = [];
}
$arParams['PROPERTY_CODE'] = array_filter($arParams['PROPERTY_CODE']);

if (!isset($arParams['FIELD_CODE']) || !is_array($arParams['FIELD_CODE']))
{
	$arParams['FIELD_CODE'] = [];
}
$arParams['FIELD_CODE'] = array_filter($arParams['FIELD_CODE']);
if (!in_array('NAME', $arParams['FIELD_CODE']))
{
	$arParams['FIELD_CODE'][] = 'NAME';
}

if (!isset($arParams['OFFERS_FIELD_CODE']) || !is_array($arParams['OFFERS_FIELD_CODE']))
{
	$arParams['OFFERS_FIELD_CODE'] = [];
}
$arParams['OFFERS_FIELD_CODE'] = array_filter($arParams['OFFERS_FIELD_CODE']);

if (!isset($arParams['OFFERS_PROPERTY_CODE']) || !is_array($arParams['OFFERS_PROPERTY_CODE']))
{
	$arParams['OFFERS_PROPERTY_CODE'] = [];
}
$arParams['OFFERS_PROPERTY_CODE'] = array_filter($arParams['OFFERS_PROPERTY_CODE']);

if (!isset($arParams['PRICE_CODE']) || !is_array($arParams['PRICE_CODE']))
{
	$arParams['PRICE_CODE'] = [];
}
$arParams['PRICE_CODE'] = array_filter($arParams['PRICE_CODE']);

$arParams['USE_PRICE_COUNT'] = ($arParams['USE_PRICE_COUNT'] ?? 'N') === 'Y';
$arParams['SHOW_PRICE_COUNT'] = (int)($arParams['SHOW_PRICE_COUNT'] ?? 1);
if ($arParams['SHOW_PRICE_COUNT'] <= 0)
{
	$arParams['SHOW_PRICE_COUNT'] = 1;
}

$arParams['DISPLAY_ELEMENT_SELECT_BOX'] = ($arParams['DISPLAY_ELEMENT_SELECT_BOX'] ?? 'N') === 'Y';
if (empty($arParams['ELEMENT_SORT_FIELD_BOX']))
{
	$arParams['ELEMENT_SORT_FIELD_BOX'] = 'sort';
}
if (
	!isset($arParams['ELEMENT_SORT_ORDER_BOX'])
	|| !preg_match('/^(asc|desc|nulls)(,asc|,desc|,nulls){0,1}$/i', $arParams['ELEMENT_SORT_ORDER_BOX'])
)
{
	$arParams['ELEMENT_SORT_ORDER_BOX'] = 'asc';
}
if (empty($arParams['ELEMENT_SORT_FIELD_BOX2']))
{
	$arParams['ELEMENT_SORT_FIELD_BOX2'] = 'id';
}
if (
	!isset($arParams['ELEMENT_SORT_ORDER_BOX2'])
	|| !preg_match('/^(asc|desc|nulls)(,asc|,desc|,nulls){0,1}$/i', $arParams['ELEMENT_SORT_ORDER_BOX2'])
)
{
	$arParams['ELEMENT_SORT_ORDER_BOX2'] = 'desc';
}

if (empty($arParams['HIDE_NOT_AVAILABLE']) || $arParams['HIDE_NOT_AVAILABLE'] !== 'Y')
{
	$arParams['HIDE_NOT_AVAILABLE'] = 'N';
}

$arParams['PRICE_VAT_INCLUDE'] = ($arParams['PRICE_VAT_INCLUDE'] ?? 'Y') !== 'N';

$arParams['CONVERT_CURRENCY'] = (isset($arParams['CONVERT_CURRENCY']) && $arParams['CONVERT_CURRENCY'] === 'Y' ? 'Y' : 'N');
$arParams['CURRENCY_ID'] = trim((string)($arParams['CURRENCY_ID'] ?? ''));
if ($arParams['CURRENCY_ID'] === '')
{
	$arParams['CONVERT_CURRENCY'] = 'N';
}
elseif ($arParams['CONVERT_CURRENCY'] === 'N')
{
	$arParams['CURRENCY_ID'] = '';
}

$arResult = [];

if (!isset($_SESSION[$arParams['NAME']]))
{
	$_SESSION[$arParams['NAME']] = [];
}
if (!isset($_SESSION[$arParams['NAME']][$arParams['IBLOCK_ID']]))
{
	$_SESSION[$arParams['NAME']][$arParams['IBLOCK_ID']] = [];
}

/*************************************************************************
			Handling the Compare button
*************************************************************************/
if (isset($_REQUEST[$arParams['ACTION_VARIABLE']]))
{
	switch (ToUpper($_REQUEST[$arParams['ACTION_VARIABLE']]))
	{
		case "ADD_TO_COMPARE_LIST":
		case "ADD_TO_COMPARE_RESULT":
			if (isset($_REQUEST[$arParams['PRODUCT_ID_VARIABLE']]))
			{
				$successfulAction = true;
				$errorMessage = '';
				$actionByAjax = (isset($_REQUEST['ajax_action']) && $_REQUEST['ajax_action'] == 'Y');
				if (!isset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["ITEMS"]))
					$_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["ITEMS"] = array();
				$productID = (int)$_REQUEST[$arParams['PRODUCT_ID_VARIABLE']];
				if ($productID > 0 && !isset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["ITEMS"][$productID]))
				{
					$found = true;
					$arOffers = CIBlockPriceTools::GetOffersIBlock($arParams["IBLOCK_ID"]);
					$OFFERS_IBLOCK_ID = $arOffers ? $arOffers["OFFERS_IBLOCK_ID"]: 0;

					$arSelect = array(
						"ID",
						"IBLOCK_ID",
						"IBLOCK_SECTION_ID",
						"NAME",
						"DETAIL_PAGE_URL",
					);
					$arFilter = array(
						"ID" => $productID,
						"IBLOCK_LID" => SITE_ID,
						"IBLOCK_ACTIVE" => "Y",
						"ACTIVE_DATE" => "Y",
						"ACTIVE" => "Y",
						"CHECK_PERMISSIONS" => "Y",
						"MIN_PERMISSION" => "R"
					);
					$arFilter["IBLOCK_ID"] = ($OFFERS_IBLOCK_ID > 0 ? array($arParams["IBLOCK_ID"], $OFFERS_IBLOCK_ID) : $arParams["IBLOCK_ID"]);

					$rsElement = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
					$arElement = $rsElement->GetNext();
					unset($rsElement);
					if (empty($arElement))
						$found = false;

					if ($found)
					{
						if ($arElement['IBLOCK_ID'] == $OFFERS_IBLOCK_ID)
						{
							$rsMasterProperty = CIBlockElement::GetProperty($arElement["IBLOCK_ID"], $arElement["ID"], array(), array("ID" => $arOffers["OFFERS_PROPERTY_ID"], "EMPTY" => "N"));
							$arMasterProperty = $rsMasterProperty->Fetch();
							unset($rsMasterProperty);
							if (empty($arMasterProperty))
								$found = false;

							if ($found)
							{
								$arMasterProperty['VALUE'] = (int)$arMasterProperty['VALUE'];
								if ($arMasterProperty['VALUE'] <= 0)
									$found = false;
							}
							if ($found)
							{
								$rsMaster = CIBlockElement::GetList(
									array(),
									array(
										'ID' => $arMasterProperty['VALUE'],
										'IBLOCK_ID' => $arMasterProperty['LINK_IBLOCK_ID'],
										'ACTIVE' => 'Y',
									),
									false,
									false,
									$arSelect
								);
								$rsMaster->SetUrlTemplates($arParams['DETAIL_URL']);
								$arMaster = $rsMaster->GetNext();
								unset($rsMaster);
								if (empty($arMaster))
								{
									$found = false;
								}
								else
								{
									$arMaster['NAME'] = $arElement['NAME'];
									$arElement = $arMaster;
								}
								unset($arMaster);
							}
						}
					}
					if ($found)
					{
						$sectionsList = array();
						$sectionsIterator = Iblock\SectionElementTable::getList(array(
							'select' => array('IBLOCK_SECTION_ID'),
							'filter' => array('=IBLOCK_ELEMENT_ID' => $arElement['ID'], '=ADDITIONAL_PROPERTY_ID' => null)
						));
						while ($section = $sectionsIterator->fetch())
						{
							$sectionId = (int)$section['IBLOCK_SECTION_ID'];
							$sectionsList[$sectionId] = $sectionId;
						}
						unset($section, $sectionsIterator);
						$_SESSION[$arParams['NAME']][$arParams['IBLOCK_ID']]['ITEMS'][$productID] = array(
							'ID' => $arElement['ID'],
							'~ID' => $arElement['~ID'],
							'IBLOCK_ID' => $arElement['IBLOCK_ID'],
							'~IBLOCK_ID' => $arElement['~IBLOCK_ID'],
							'IBLOCK_SECTION_ID' => $arElement['IBLOCK_SECTION_ID'],
							'~IBLOCK_SECTION_ID' => $arElement['~IBLOCK_SECTION_ID'],
							'NAME' => $arElement['NAME'],
							'~NAME' => $arElement['~NAME'],
							'DETAIL_PAGE_URL' => $arElement['DETAIL_PAGE_URL'],
							'~DETAIL_PAGE_URL' => $arElement['~DETAIL_PAGE_URL'],
							'SECTIONS_LIST' => $sectionsList,
							'PARENT_ID' => $productID,
							'DELETE_URL' => htmlspecialcharsbx($APPLICATION->GetCurPageParam(
								$arParams['ACTION_VARIABLE']."=DELETE_FROM_COMPARE_RESULT&".$arParams['PRODUCT_ID_VARIABLE']."=".$productID,
								array($arParams['ACTION_VARIABLE'], $arParams['PRODUCT_ID_VARIABLE'])
							))
						);
						unset($sectionsList, $arElement);
						$resultCount = count($_SESSION[$arParams['NAME']][$arParams['IBLOCK_ID']]['ITEMS']);
					}
					else
					{
						$successfulAction = false;
						$errorMessage = GetMessage('CP_BCCR_ERR_MESS_PRODUCT_NOT_FOUND');
					}
				}
				if ($actionByAjax)
				{
					if ($successfulAction)
						$addResult = array('STATUS' => 'OK', 'MESSAGE' => GetMessage('CP_BCCR_MESS_SUCCESSFUL_ADD_TO_COMPARE'));
					else
						$addResult = array('STATUS' => 'ERROR', 'MESSAGE' => $errorMessage);
					$APPLICATION->RestartBuffer();
					header('Content-Type: application/json');
					echo Main\Web\Json::encode($addResult);
					die();
				}
			}
			break;
		case "DELETE_FROM_COMPARE_LIST":
		case "DELETE_FROM_COMPARE_RESULT":
			$arID = array();

			if (isset($_REQUEST[$arParams['PRODUCT_ID_VARIABLE']]))
			{
				$arID = $_REQUEST[$arParams['PRODUCT_ID_VARIABLE']];
			}
			elseif (isset($_REQUEST["ID"]))
			{
				$arID = $_REQUEST["ID"];
			}

			if (!is_array($arID))
			{
				$arID = array($arID);
			}

			if (!empty($arID))
			{
				foreach($arID as $ID)
				{
					if (isset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["ITEMS"][$ID]))
					{
						unset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["ITEMS"][$ID]);
					}
				}
				unset($ID);
			}
			unset($arID);
			break;
		case "ADD_FEATURE":
			$arPF = array();
			$arPR = array();
			$arOF = array();
			$arOP = array();
			if (isset($_REQUEST['pf_code']))
			{
				$arPF = $_REQUEST['pf_code'];
				if (!is_array($arPF))
					$arPF = array($arPF);
			}
			if (isset($_REQUEST["pr_code"]))
			{
				$arPR = $_REQUEST["pr_code"];
				if (!is_array($arPR))
					$arPR = array($arPR);
			}
			if (isset($_REQUEST["of_code"]))
			{
				$arOF = $_REQUEST["of_code"];
				if (!is_array($arOF))
					$arOF = array($arOF);
			}
			if (isset($_REQUEST["op_code"]))
			{
				$arOP = $_REQUEST["op_code"];
				if (!is_array($arOP))
					$arOP = array($arOP);
			}

			if (!empty($arPF))
			{
				foreach($arPF as $ID)
				{
					if (isset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_FIELD"][$ID]))
						unset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_FIELD"][$ID]);
				}
				unset($ID);
			}
			if (!empty($arPR))
			{
				foreach($arPR as $ID)
				{
					if (isset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_PROP"][$ID]))
						unset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_PROP"][$ID]);
				}
				unset($ID);
			}
			if (!empty($arOF))
			{
				foreach($arOF as $ID)
				{
					if (isset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_OFFER_FIELD"][$ID]))
						unset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_OFFER_FIELD"][$ID]);
				}
				unset($ID);
			}
			if (!empty($arOP))
			{
				foreach($arOP as $ID)
				{
					if (isset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_OFFER_PROP"][$ID]))
						unset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_OFFER_PROP"][$ID]);
				}
				unset($ID);
			}
			unset($arOP, $arOF, $arPR, $arPF);
			break;
		case "DELETE_FEATURE":
			$arPF = array();
			$arPR = array();
			$arOF = array();
			$arOP = array();
			if (isset($_REQUEST['pf_code']))
			{
				$arPF = $_REQUEST['pf_code'];
				if (!is_array($arPF))
					$arPF = array($arPF);
			}
			if (isset($_REQUEST["pr_code"]))
			{
				$arPR = $_REQUEST["pr_code"];
				if (!is_array($arPR))
					$arPR = array($arPR);
			}
			if (isset($_REQUEST["of_code"]))
			{
				$arOF = $_REQUEST["of_code"];
				if (!is_array($arOF))
					$arOF = array($arOF);
			}
			if (isset($_REQUEST["op_code"]))
			{
				$arOP = $_REQUEST["op_code"];
				if (!is_array($arOP))
					$arOP = array($arOP);
			}

			if (!empty($arPF))
			{
				if (!isset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_FIELD"]))
					$_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_FIELD"] = array();
				foreach($arPF as $ID)
					$_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_FIELD"][$ID] = true;
			}
			if (!empty($arPR))
			{
				if (!isset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_PROP"]))
					$_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_PROP"] = array();
				foreach($arPR as $ID)
					$_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_PROP"][$ID] = true;
			}
			if (!empty($arOF))
			{
				if (!isset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_OFFER_FIELD"]))
					$_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_OFFER_FIELD"] = array();
				foreach($arOF as $ID)
					$_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_OFFER_FIELD"][$ID] = true;
			}
			if (!empty($arOP))
			{
				if (!isset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_OFFER_PROP"]))
					$_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_OFFER_PROP"] = array();
				foreach($arOP as $ID)
					$_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_OFFER_PROP"][$ID] = true;
			}

			unset($arOP, $arOF, $arPR, $arPF);
			break;
	}
}

if (!isset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DIFFERENT"]))
	$_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DIFFERENT"] = false;
if (isset($_REQUEST["DIFFERENT"]))
	$_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DIFFERENT"] = $_REQUEST["DIFFERENT"]=="Y";
$arResult["DIFFERENT"] = $_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DIFFERENT"];

/*************************************************************************
Processing of the Buy link
 *************************************************************************/
$strError = "";
if (isset($_REQUEST[$arParams["ACTION_VARIABLE"]]) && isset($_REQUEST[$arParams["PRODUCT_ID_VARIABLE"]]))
{
	$action = mb_strtoupper($_REQUEST[$arParams["ACTION_VARIABLE"]]);
	$productID = (int)$_REQUEST[$arParams["PRODUCT_ID_VARIABLE"]];
	if (($action == "COMPARE_ADD2BASKET" || $action == "COMPARE_BUY") && $productID > 0)
	{
		if (Loader::includeModule("sale") && Loader::includeModule("catalog"))
		{
			$QUANTITY = 1;
			$product_properties = array();
			if (is_array($arParams["OFFERS_CART_PROPERTIES"]))
			{
				foreach($arParams["OFFERS_CART_PROPERTIES"] as $i => $pid)
					if ($pid === "")
						unset($arParams["OFFERS_CART_PROPERTIES"][$i]);

				if (!empty($arParams["OFFERS_CART_PROPERTIES"]))
				{
					$product_properties = CIBlockPriceTools::GetOfferProperties(
						$productID,
						$arParams["IBLOCK_ID"],
						$arParams["OFFERS_CART_PROPERTIES"]
					);
				}
			}

			if (Add2BasketByProductID($productID, $QUANTITY, $product_properties))
			{
				if ($action == "COMPARE_BUY")
					LocalRedirect($arParams["BASKET_URL"], true);
				else
					LocalRedirect($APPLICATION->GetCurPageParam("", array($arParams["PRODUCT_ID_VARIABLE"], $arParams["ACTION_VARIABLE"])));
			}
			else
			{
				if ($ex = $APPLICATION->GetException())
					$strError = $ex->GetString();
				else
					$strError = GetMessage("CATALOG_ERROR2BASKET").".";
			}
		}
	}
}
if ($strError <> '')
{
	ShowError($strError);
	return;
}

$arCompare = array();
if (isset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["ITEMS"]))
	$arCompare = $_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["ITEMS"];

if (!empty($arCompare) && is_array($arCompare))
{
	$fieldsRequired = array(
		'NAME' => true
	);
	$fieldsHidden = array(
		'IBLOCK_TYPE_ID' => true,
		'IBLOCK_CODE' => true,
		'IBLOCK_NAME' => true,
		'IBLOCK_EXTERNAL_ID' => true,
		'SECTION_ID' => true,
		'IBLOCK_SECTION_ID' => true
	);
	$sessionFields = array(
		'DELETE_FIELD',
		'DELETE_PROP',
		'DELETE_OFFER_FIELD',
		'DELETE_OFFER_PROP'
	);
	foreach ($sessionFields as &$fieldName)
	{
		if (
			!isset($_SESSION[$arParams['NAME']][$arParams['IBLOCK_ID']][$fieldName])
			|| !is_array($_SESSION[$arParams['NAME']][$arParams['IBLOCK_ID']][$fieldName])
		)
			$_SESSION[$arParams['NAME']][$arParams['IBLOCK_ID']][$fieldName] = array();
	}
	unset($fieldName, $sessionFields);

	$catalogIncluded = Loader::includeModule('catalog');
	$arResult['PRICES'] = CIBlockPriceTools::GetCatalogPrices($arParams['IBLOCK_ID'], $arParams['PRICE_CODE']);
	$arResult['PRICES_ALLOW'] = CIBlockPriceTools::GetAllowCatalogPrices($arResult['PRICES']);

	$arConvertParams = array();
	$basePrice = '';
	if ($arParams['CONVERT_CURRENCY'] == 'Y')
	{
		$correct = false;
		if (Loader::includeModule('currency'))
		{
			$correct = Currency\CurrencyManager::isCurrencyExist($arParams['CURRENCY_ID']);
			$basePrice = Currency\CurrencyManager::getBaseCurrency();
		}

		if ($correct)
		{
			$arConvertParams['CURRENCY_ID'] = $arParams['CURRENCY_ID'];
		}
		else
		{
			$arParams['CONVERT_CURRENCY'] = 'N';
			$arParams['CURRENCY_ID'] = '';
		}
		unset($correct);
	}

	$arResult['CONVERT_CURRENCY'] = $arConvertParams;

	$arResult['OFFERS_IBLOCK_ID'] = 0;
	$arResult['OFFERS_PROPERTY_ID'] = 0;
	$arOffers = CIBlockPriceTools::GetOffersIBlock($arParams["IBLOCK_ID"]);
	if (!empty($arOffers))
	{
		$arResult["OFFERS_IBLOCK_ID"] = $arOffers["OFFERS_IBLOCK_ID"];
		$arResult["OFFERS_PROPERTY_ID"] = $arOffers["OFFERS_PROPERTY_ID"];
	}
	unset($arOffers);

	$usePropertyFeatures = Iblock\Model\PropertyFeature::isEnabledFeatures();
	if ($usePropertyFeatures)
	{
		$properties = [];
		$list = Iblock\Model\PropertyFeature::getListPageShowPropertyCodes(
			$arParams['IBLOCK_ID'],
			['CODE' => 'Y']
		);
		if (!empty($list))
		{
			$properties = $list;
		}
		$list = Iblock\Model\PropertyFeature::getDetailPageShowPropertyCodes(
			$arParams['IBLOCK_ID'],
			['CODE' => 'Y']
		);
		if (!empty($list))
		{
			$properties = array_merge($properties, $list);
		}
		if (!empty($properties))
		{
			$properties = array_unique($properties);
			$selectProperties = array_fill_keys($properties, true);
			$properties = [];
			$propertyIterator = Iblock\PropertyTable::getList([
				'select' => [
					'ID',
					'CODE',
					'SORT',
				],
				'filter' => [
					'=IBLOCK_ID' => $arParams['IBLOCK_ID'],
					'=ACTIVE' => 'Y'
				],
				'order' => [
					'SORT' => 'ASC',
					'ID' => 'ASC'
				],
			]);
			while ($property = $propertyIterator->fetch())
			{
				$code = $property['CODE'] ?? $property['ID'];
				if (isset($selectProperties[$code]))
				{
					$properties[] = $code;
				}
			}
			unset($code, $property, $propertyIterator);
			unset($selectProperties);
		}
		$arParams['PROPERTY_CODE'] = $properties;
		if ($catalogIncluded && $arResult['OFFERS_IBLOCK_ID'] > 0)
		{
			$properties = [];
			$list = Iblock\Model\PropertyFeature::getListPageShowPropertyCodes(
				$arResult['OFFERS_IBLOCK_ID'],
				['CODE' => 'Y']
			);
			if (!empty($list))
			{
				$properties = $list;
			}
			$list = Iblock\Model\PropertyFeature::getDetailPageShowPropertyCodes(
				$arResult['OFFERS_IBLOCK_ID'],
				['CODE' => 'Y']
			);
			if (!empty($list))
			{
				$properties = array_merge($properties, $list);
			}
			$list = Catalog\Product\PropertyCatalogFeature::getOfferTreePropertyCodes(
				$arResult['OFFERS_IBLOCK_ID'],
				['CODE' => 'Y']
			);
			if (!empty($list))
			{
				$properties = array_merge($properties, $list);
			}
			if (!empty($properties))
			{
				$properties = array_unique($properties);
				$selectProperties = array_fill_keys($properties, true);
				$properties = [];
				$propertyIterator = Iblock\PropertyTable::getList([
					'select' => [
						'ID',
						'CODE',
						'SORT',
					],
					'filter' => [
						'=IBLOCK_ID' => $arResult['OFFERS_IBLOCK_ID'],
						'=ACTIVE' => 'Y'
					],
					'order' => [
						'SORT' => 'ASC',
						'ID' => 'ASC'
					],
				]);
				while ($property = $propertyIterator->fetch())
				{
					$code = $property['CODE'] ?? $property['ID'];
					if (isset($selectProperties[$code]))
					{
						$properties[] = $code;
					}
				}
				unset($code, $property, $propertyIterator);
				unset($selectProperties);
			}
			$arParams['OFFERS_PROPERTY_CODE'] = $properties;
		}
		unset($list, $properties);
	}

	$arSelect = array(
		"ID",
		"IBLOCK_ID",
		"IBLOCK_SECTION_ID",
		"DETAIL_PAGE_URL",
		"PROPERTY_*",
	);
	$arFilter = array(
		"ID" => array_keys($arCompare),
		"IBLOCK_LID" => SITE_ID,
		"IBLOCK_ACTIVE" => "Y",
		"ACTIVE_DATE" => "Y",
		"ACTIVE" => "Y",
		"CHECK_PERMISSIONS" => "Y",
	);
	$arFilter["IBLOCK_ID"] = (
		$arResult["OFFERS_IBLOCK_ID"] > 0
		? array($arParams["IBLOCK_ID"], $arResult["OFFERS_IBLOCK_ID"])
		: $arParams["IBLOCK_ID"]
	);

	$arPriceTypeID = array();
	if (!$arParams["USE_PRICE_COUNT"])
	{
		foreach($arResult["PRICES"] as &$value)
		{
			if (!$value['CAN_VIEW'] && !$value['CAN_BUY'])
				continue;
			$arSelect[] = $value["SELECT"];
			$arFilter["CATALOG_SHOP_QUANTITY_".$value["ID"]] = $arParams["SHOW_PRICE_COUNT"];
		}
		if (isset($value))
			unset($value);
	}
	else
	{
		foreach($arResult["PRICES"] as &$value)
		{
			if (!$value['CAN_VIEW'] && !$value['CAN_BUY'])
				continue;
			$arPriceTypeID[] = $value["ID"];
		}
		if (isset($value))
			unset($value);
	}
	if (!empty($arParams["FIELD_CODE"]))
		$arSelect = array_merge($arSelect, $arParams["FIELD_CODE"]);
	if (!empty($arParams['OFFERS_FIELD_CODE']))
		$arSelect = array_merge($arSelect, $arParams["OFFERS_FIELD_CODE"]);
	$arSelect = array_unique($arSelect);

	$arSort = array(
		$arParams["ELEMENT_SORT_FIELD"] => $arParams["ELEMENT_SORT_ORDER"],
		"ID" => "DESC",
	);

	$currentPath = CHTTP::urlDeleteParams(
		$APPLICATION->GetCurPageParam(),
		array(
			$arParams['PRODUCT_ID_VARIABLE'], $arParams['ACTION_VARIABLE'],
			'DIFFERENT', 'ID',
			'op_code', 'of_code', 'pr_code', 'pf_code',
			'ajax_action'
		),
		array("delete_system_params" => true)
	);

	$arResult['~COMPARE_URL_TEMPLATE'] = $currentPath.(mb_stripos($currentPath, '?') === false ? '?' : '&');
	$arResult['COMPARE_URL_TEMPLATE'] = htmlspecialcharsbx($arResult['~COMPARE_URL_TEMPLATE']);
	$rawCompareTemplateWithAction = $arResult['~COMPARE_URL_TEMPLATE'].$arParams['ACTION_VARIABLE'];
	$compareTemplateWithAction = $arResult['COMPARE_URL_TEMPLATE'].$arParams['ACTION_VARIABLE'];

	$arResult['~DELETE_FROM_COMPARE_URL_TEMPLATE'] = $rawCompareTemplateWithAction.'=DELETE_FROM_COMPARE_RESULT&ID=#ID#';
	$arResult['BUY_URL_TEMPLATE'] = $compareTemplateWithAction.'=COMPARE_BUY&'.$arParams['PRODUCT_ID_VARIABLE'].'=#ID#';
	$arResult['ADD_URL_TEMPLATE'] = $compareTemplateWithAction.'=COMPARE_ADD2BASKET&'.$arParams['PRODUCT_ID_VARIABLE'].'=#ID#';
	$arResult['~DELETE_FEATURE_FIELD_TEMPLATE'] = $rawCompareTemplateWithAction.'=DELETE_FEATURE&pf_code=#CODE#';
	$arResult['~ADD_FEATURE_FIELD_TEMPLATE'] = $rawCompareTemplateWithAction.'=ADD_FEATURE&pf_code=#CODE#';
	$arResult['~DELETE_FEATURE_PROPERTY_TEMPLATE'] = $rawCompareTemplateWithAction.'=DELETE_FEATURE&pr_code=#CODE#';
	$arResult['~ADD_FEATURE_PROPERTY_TEMPLATE'] = $rawCompareTemplateWithAction.'=ADD_FEATURE&pr_code=#CODE#';
	$arResult['~DELETE_FEATURE_OF_FIELD_TEMPLATE'] = $rawCompareTemplateWithAction.'=DELETE_FEATURE&of_code=#CODE#';
	$arResult['~ADD_FEATURE_OF_FIELD_TEMPLATE'] = $rawCompareTemplateWithAction.'=ADD_FEATURE&of_code=#CODE#';
	$arResult['~DELETE_FEATURE_OF_PROPERTY_TEMPLATE'] = $rawCompareTemplateWithAction.'=DELETE_FEATURE&op_code=#CODE#';
	$arResult['~ADD_FEATURE_OF_PROPERTY_TEMPLATE'] = $rawCompareTemplateWithAction.'=ADD_FEATURE&op_code=#CODE#';
	unset($rawCompareTemplateWithAction, $compareTemplateWithAction, $currentPath);

	$arResult['DELETED_FIELDS'] = array();
	$arResult['SHOW_FIELDS'] = array();
	$arResult['DELETED_PROPERTIES'] = array();
	$arResult['SHOW_PROPERTIES'] = array();
	$arResult['DELETED_OFFER_FIELDS'] = array();
	$arResult['SHOW_OFFER_FIELDS'] = array();
	$arResult['DELETED_OFFER_PROPERTIES'] = array();
	$arResult['SHOW_OFFER_PROPERTIES'] = array();
	$arResult['EMPTY_FIELDS'] = array();
	$arResult['EMPTY_PROPERTIES'] = array();
	$arResult['EMPTY_OFFER_FIELDS'] = array();
	$arResult['EMPTY_OFFER_PROPERTIES'] = array();

	//EXECUTE
	$arResult['ITEMS'] = array();
	$rsElements = CIBlockElement::GetList($arSort, $arFilter, false, false, $arSelect);
	$rsElements->SetUrlTemplates($arParams['DETAIL_URL']);
	while($obElement = $rsElements->GetNextElement())
	{
		$arItem = $obElement->GetFields();
		$arOffer = false;
		if ($arItem["IBLOCK_ID"] == $arResult["OFFERS_IBLOCK_ID"])
		{
			if (!empty($arParams["OFFERS_PROPERTY_CODE"]))
				$arItem["PROPERTIES"] = $obElement->GetProperties();

			$rsMasterProperty = CIBlockElement::GetProperty($arItem["IBLOCK_ID"], $arItem["ID"], array(), array("ID" => $arResult["OFFERS_PROPERTY_ID"], "EMPTY" => "N"));
			if ($arMasterProperty = $rsMasterProperty->Fetch())
			{
				$rsMaster = CIBlockElement::GetList(
					array(),
					array(
						"ID" => $arMasterProperty["VALUE"],
						"IBLOCK_ID" => $arMasterProperty["LINK_IBLOCK_ID"],
						"ACTIVE" => "Y",
					),
					false,
					false,
					$arSelect
				);
				$rsMaster->SetUrlTemplates($arParams["DETAIL_URL"]);
				$obElement = $rsMaster->GetNextElement();
				if (!is_object($obElement))
					continue;
			}
			else
			{
				continue;
			}

			Iblock\Component\Tools::getFieldImageData(
				$arItem,
				array('PREVIEW_PICTURE', 'DETAIL_PICTURE'),
				Iblock\Component\Tools::IPROPERTY_ENTITY_ELEMENT,
				'IPROPERTY_VALUES'
			);
			$arOffer = $arItem;
			$arItem = $obElement->GetFields();
		}

		$ipropValues = new ElementValues($arItem["IBLOCK_ID"], $arItem["ID"]);
		$arItem["IPROPERTY_VALUES"] = $ipropValues->getValues();

		Iblock\Component\Tools::getFieldImageData(
			$arItem,
			array('PREVIEW_PICTURE', 'DETAIL_PICTURE'),
			Iblock\Component\Tools::IPROPERTY_ENTITY_ELEMENT,
			'IPROPERTY_VALUES'
		);

		$arItem["FIELDS"] = array();
		if (!empty($arParams["FIELD_CODE"]))
		{
			foreach($arParams["FIELD_CODE"] as &$code)
			{
				if (isset($fieldsHidden[$code]))
					continue;
				if (!isset($arResult['EMPTY_FIELDS'][$code]))
					$arResult['EMPTY_FIELDS'][$code] = true;

				if (array_key_exists($code, $arItem))
				{
					if (isset($fieldsRequired[$code]) || !isset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]['DELETE_FIELD'][$code]))
					{
						$arItem["FIELDS"][$code] = $arItem[$code];
						if ($arItem["FIELDS"][$code] === null)
							$arItem["FIELDS"][$code] = '';
						if ($arItem["FIELDS"][$code] != '')
							$arResult['EMPTY_FIELDS'][$code] = false;
					}
					if (isset($fieldsRequired[$code]) || !isset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_FIELD"][$code]))
					{
						$arResult["SHOW_FIELDS"][$code] = $code;
					}
					else
					{
						$arResult["DELETED_FIELDS"][$code] = $code;
						$arResult['EMPTY_FIELDS'][$code] = false;
					}
				}
			}
			unset($code);
		}

		$arItem["OFFER_FIELDS"] = array();
		$arItem["OFFER_PROPERTIES"] = array();
		$arItem["OFFER_DISPLAY_PROPERTIES"] = array();
		if ($arOffer)
		{
			if (!empty($arParams["OFFERS_FIELD_CODE"]))
			{
				foreach ($arParams["OFFERS_FIELD_CODE"] as &$code)
				{
					if (isset($fieldsHidden[$code]))
						continue;
					if (!isset($arResult['EMPTY_OFFER_FIELDS'][$code]))
						$arResult['EMPTY_OFFER_FIELDS'][$code] = true;

					if (array_key_exists($code, $arOffer))
					{
						if (!isset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_OFFER_FIELD"][$code]))
						{
							$arItem["OFFER_FIELDS"][$code] = $arOffer[$code];
							if ($arItem["OFFER_FIELDS"][$code] === null)
								$arItem["OFFER_FIELDS"][$code] = '';
							if ($arItem["OFFER_FIELDS"][$code] != '')
								$arResult['EMPTY_OFFER_FIELDS'][$code] = false;
						}

						if (isset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_OFFER_FIELD"][$code]))
						{
							$arResult["DELETED_OFFER_FIELDS"][$code] = $code;
							$arResult['EMPTY_OFFER_FIELDS'][$code] = false;
						}
						else
						{
							$arResult["SHOW_OFFER_FIELDS"][$code] = $code;
						}
					}
				}
				unset($code);
			}

			$arItem["OFFER_PROPERTIES"] = $arOffer["PROPERTIES"];
			if (!empty($arParams["OFFERS_PROPERTY_CODE"]))
			{
				foreach ($arParams["OFFERS_PROPERTY_CODE"] as &$pid)
				{
					if (!isset($arOffer['PROPERTIES'][$pid]))
						continue;

					if (!isset($arResult['EMPTY_OFFER_PROPERTIES'][$pid]))
						$arResult['EMPTY_OFFER_PROPERTIES'][$pid] = true;

					if (!isset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_OFFER_PROP"][$pid]))
					{
						$prop = &$arOffer['PROPERTIES'][$pid];
						$boolArr = is_array($prop['VALUE']);
						if (
							($boolArr && !empty($prop["VALUE"]))
							|| (!$boolArr && (string)$prop["VALUE"] !== '')
							|| \Bitrix\Iblock\Component\Tools::isCheckboxProperty($prop)
						)
						{
							$arItem['OFFER_DISPLAY_PROPERTIES'][$pid] = CIBlockFormatProperties::GetDisplayValue($arOffer, $prop);
							if ($arItem['OFFER_DISPLAY_PROPERTIES'][$pid]['DISPLAY_VALUE'] !== false)
							{
								$arResult['EMPTY_OFFER_PROPERTIES'][$pid] = false;
							}
						}
						unset($prop);
					}

					if (isset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_OFFER_PROP"][$pid]))
					{
						$arResult["DELETED_OFFER_PROPERTIES"][$pid] = $arOffer["PROPERTIES"][$pid];
						$arResult['EMPTY_OFFER_PROPERTIES'][$pid] = false;
					}
					else
					{
						$arResult["SHOW_OFFER_PROPERTIES"][$pid] = $arOffer["PROPERTIES"][$pid];
					}
				}
				unset($pid);
			}
		}

		$arItem["PROPERTIES"] = array();
		$arItem["DISPLAY_PROPERTIES"] = array();

		if (!empty($arParams["PROPERTY_CODE"]))
		{
			$arItem["PROPERTIES"] = $obElement->GetProperties();
			foreach ($arParams["PROPERTY_CODE"] as &$pid)
			{
				if (!isset($arItem['PROPERTIES'][$pid]))
					continue;

				if (!isset($arResult['EMPTY_PROPERTIES'][$pid]))
					$arResult['EMPTY_PROPERTIES'][$pid] = true;

				if (!isset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_PROP"][$pid]))
				{
					$prop = &$arItem['PROPERTIES'][$pid];
					$boolArr = is_array($prop['VALUE']);
					if (
						($boolArr && !empty($prop["VALUE"]))
						|| (!$boolArr && (string)$prop["VALUE"] !== '')
						|| \Bitrix\Iblock\Component\Tools::isCheckboxProperty($prop)
					)
					{
						$arItem['DISPLAY_PROPERTIES'][$pid] = CIBlockFormatProperties::GetDisplayValue($arItem, $prop);
						if ($arItem['DISPLAY_PROPERTIES'][$pid]['DISPLAY_VALUE'] !== false)
						{
							$arResult['EMPTY_PROPERTIES'][$pid] = false;
						}
					}
				}

				if (isset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_PROP"][$pid]))
				{
					$arResult["DELETED_PROPERTIES"][$pid] = $arItem["PROPERTIES"][$pid];
					$arResult['EMPTY_PROPERTIES'][$pid] = false;
				}
				else
				{
					$arResult["SHOW_PROPERTIES"][$pid] = $arItem["PROPERTIES"][$pid];
				}
			}
			unset($pid);
		}

		$arItem['PARENT_ID'] = $arItem['ID'];
		$arItem['PRICES'] = array();
		$arItem['PRICE_MATRIX'] = false;
		$arItem['MIN_PRICE'] = false;
		if ($arOffer)
		{
			if ($arParams["USE_PRICE_COUNT"])
			{
				if ($catalogIncluded)
				{
					$arItem["PRICE_MATRIX"] = CatalogGetPriceTableEx($arOffer["ID"], 0, $arPriceTypeID, 'Y', $arConvertParams);
					if (isset($arItem["PRICE_MATRIX"]["COLS"]) && is_array($arItem["PRICE_MATRIX"]["COLS"]))
					{
						$arItem['PRICE_MATRIX']['MIN_PRICES'] = array();
						$rows = $arItem['PRICE_MATRIX']['ROWS'];
						$matrix = $arItem['PRICE_MATRIX']['MATRIX'];

						foreach(array_keys($rows) as $keyColumn)
							$arItem["PRICE_MATRIX"]["COLS"][$keyColumn]["NAME_LANG"] = htmlspecialcharsbx($arItem["PRICE_MATRIX"]["COLS"][$keyColumn]["NAME_LANG"]);

						foreach (array_keys($rows) as $index)
						{
							$minPrice = null;
							foreach (array_keys($matrix) as $priceType)
							{
								if (empty($matrix[$priceType][$index]))
									continue;
								if ($arParams['CONVERT_CURRENCY'] == 'Y')
								{
									if ($minPrice === null || $minPrice['PRICE_SCALE'] > $matrix[$priceType][$index]['PRICE'])
									{
										$minPrice = array(
											'PRICE_SCALE' => $matrix[$priceType][$index]['PRICE'],
											'PRICE' => $matrix[$priceType][$index]['PRICE'],
											'CURRENCY' => $matrix[$priceType][$index]['CURRENCY']
										);
									}
								}
								else
								{
									$priceScale = ($matrix[$priceType][$index]['CURRENCY'] == $basePrice
										? $matrix[$priceType][$index]['PRICE']
										: \CCurrencyRates::ConvertCurrency(
											$matrix[$priceType][$index]['PRICE'],
											$matrix[$priceType][$index]['CURRENCY'],
											$basePrice
										)
									);
									if ($minPrice === null || $minPrice['PRICE_SCALE'] > $priceScale)
									{
										$minPrice = array(
											'PRICE_SCALE' => $priceScale,
											'PRICE' => $matrix[$priceType][$index]['PRICE'],
											'CURRENCY' => $matrix[$priceType][$index]['CURRENCY']
										);
									}
								}
							}
							unset($priceType);
							if (is_array($minPrice))
							{
								unset($minPrice['PRICE_SCALE']);
								$arItem['PRICE_MATRIX']['MIN_PRICES'][$index] = $minPrice;
							}
							unset($minPrice);
						}
						unset($index);
						unset($matrix, $rows);
					}
				}
			}
			else
			{
				$arItem["PRICES"] = CIBlockPriceTools::GetItemPrices(
					$arOffer["IBLOCK_ID"],
					$arResult["PRICES"],
					$arOffer,
					$arParams["PRICE_VAT_INCLUDE"],
					$arConvertParams
				);
			}
			$arItem["CAN_BUY"] = CIBlockPriceTools::CanBuy($arParams["IBLOCK_ID"], $arResult["PRICES"], $arOffer);
			$arItem['ID'] = $arOffer['ID'];
		}
		else
		{
			if ($arParams["USE_PRICE_COUNT"])
			{
				if ($catalogIncluded)
				{
					$arItem["PRICE_MATRIX"] = CatalogGetPriceTableEx($arItem["ID"], 0, $arPriceTypeID, 'Y', $arConvertParams);
					if (isset($arItem["PRICE_MATRIX"]["COLS"]) && is_array($arItem["PRICE_MATRIX"]["COLS"]))
					{
						$arItem['PRICE_MATRIX']['MIN_PRICES'] = array();
						$rows = $arItem['PRICE_MATRIX']['ROWS'];
						$matrix = $arItem['PRICE_MATRIX']['MATRIX'];

						foreach(array_keys($rows) as $keyColumn)
							$arItem["PRICE_MATRIX"]["COLS"][$keyColumn]["NAME_LANG"] = htmlspecialcharsbx($arItem["PRICE_MATRIX"]["COLS"][$keyColumn]["NAME_LANG"]);

						foreach (array_keys($rows) as $index)
						{
							$minPrice = null;
							foreach (array_keys($matrix) as $priceType)
							{
								if (empty($matrix[$priceType][$index]))
									continue;
								if ($arParams['CONVERT_CURRENCY'] == 'Y')
								{
									if ($minPrice === null || $minPrice['PRICE_SCALE'] > $matrix[$priceType][$index]['PRICE'])
									{
										$minPrice = array(
											'PRICE_SCALE' => $matrix[$priceType][$index]['PRICE'],
											'PRICE' => $matrix[$priceType][$index]['PRICE'],
											'CURRENCY' => $matrix[$priceType][$index]['CURRENCY']
										);
									}
								}
								else
								{
									$priceScale = ($matrix[$priceType][$index]['CURRENCY'] == $basePrice
										? $matrix[$priceType][$index]['PRICE']
										: \CCurrencyRates::ConvertCurrency(
											$matrix[$priceType][$index]['PRICE'],
											$matrix[$priceType][$index]['CURRENCY'],
											$basePrice
										)
									);
									if ($minPrice === null || $minPrice['PRICE_SCALE'] > $priceScale)
									{
										$minPrice = array(
											'PRICE_SCALE' => $priceScale,
											'PRICE' => $matrix[$priceType][$index]['PRICE'],
											'CURRENCY' => $matrix[$priceType][$index]['CURRENCY']
										);
									}
								}
							}
							unset($priceType);
							if (is_array($minPrice))
							{
								unset($minPrice['PRICE_SCALE']);
								$arItem['PRICE_MATRIX']['MIN_PRICES'][$index] = $minPrice;
							}
							unset($minPrice);
						}
						unset($index);
						unset($matrix, $rows);
					}
				}
			}
			else
			{
				$arItem["PRICES"] = CIBlockPriceTools::GetItemPrices(
					$arItem["IBLOCK_ID"],
					$arResult["PRICES"],
					$arItem,
					$arParams["PRICE_VAT_INCLUDE"],
					$arConvertParams
				);
			}
			$arItem["CAN_BUY"] = CIBlockPriceTools::CanBuy($arParams["IBLOCK_ID"], $arResult["PRICES"], $arItem);
		}
		if (!$arParams['USE_PRICE_COUNT'] && !empty($arItem['PRICES']))
		{
			foreach ($arItem['PRICES'] as &$arOnePrice)
			{
				if ($arOnePrice['MIN_PRICE'] == 'Y')
				{
					$arItem['MIN_PRICE'] = $arOnePrice;
					break;
				}
			}
			unset($arOnePrice);
		}

		$arItem['BUY_URL'] = str_replace('#ID#', $arItem['ID'], $arResult['BUY_URL_TEMPLATE']);
		$arItem['ADD_URL'] = str_replace('#ID#', $arItem['ID'], $arResult['ADD_URL_TEMPLATE']);
		$arItem['~DELETE_URL'] = str_replace('#ID#', $arItem['ID'], $arResult['~DELETE_FROM_COMPARE_URL_TEMPLATE']);

		$arResult["ITEMS"][] = $arItem;
	}
	\CIBlockFormatProperties::clearCache();

	if (!empty($arResult['EMPTY_FIELDS']))
	{
		$arResult['EMPTY_FIELDS'] = array_filter($arResult['EMPTY_FIELDS']);
		if (!empty($arResult['EMPTY_FIELDS']))
		{
			foreach ($arResult['EMPTY_FIELDS'] as $code => $isEmpty)
			{
				if (isset($arResult['SHOW_FIELDS'][$code]))
					unset($arResult['SHOW_FIELDS'][$code]);
				if (isset($arResult['DELETED_FIELDS'][$code]))
					unset($arResult['DELETED_FIELDS'][$code]);
			}
			unset($code, $isEmpty);
		}
	}
	if (!empty($arResult['EMPTY_OFFER_FIELDS']))
	{
		$arResult['EMPTY_OFFER_FIELDS'] = array_filter($arResult['EMPTY_OFFER_FIELDS']);
		if (!empty($arResult['EMPTY_OFFER_FIELDS']))
		{
			foreach ($arResult['EMPTY_OFFER_FIELDS'] as $code => $isEmpty)
			{
				if (isset($arResult['SHOW_OFFER_FIELDS'][$code]))
					unset($arResult['SHOW_OFFER_FIELDS'][$code]);
				if (isset($arResult['DELETED_OFFER_FIELDS'][$code]))
					unset($arResult['DELETED_OFFER_FIELDS'][$code]);
			}
			unset($code, $isEmpty);
		}
	}

	if (!empty($arResult['EMPTY_OFFER_PROPERTIES']))
	{
		$arResult['EMPTY_OFFER_PROPERTIES'] = array_filter($arResult['EMPTY_OFFER_PROPERTIES']);
		if (!empty($arResult['EMPTY_OFFER_PROPERTIES']))
		{
			foreach ($arResult['EMPTY_OFFER_PROPERTIES'] as $code => $isEmpty)
			{
				if (isset($arResult['SHOW_OFFER_PROPERTIES'][$code]))
					unset($arResult['SHOW_OFFER_PROPERTIES'][$code]);
				if (isset($arResult['DELETED_OFFER_PROPERTIES'][$code]))
					unset($arResult['DELETED_OFFER_PROPERTIES'][$code]);
			}
			unset($code, $isEmpty);
		}
	}

	if (!empty($arResult['EMPTY_PROPERTIES']))
	{
		$arResult['EMPTY_PROPERTIES'] = array_filter($arResult['EMPTY_PROPERTIES']);
		if (!empty($arResult['EMPTY_PROPERTIES']))
		{
			foreach ($arResult['EMPTY_PROPERTIES'] as $code => $isEmpty)
			{
				if (isset($arResult['SHOW_PROPERTIES'][$code]))
					unset($arResult['SHOW_PROPERTIES'][$code]);
				if (isset($arResult['DELETED_PROPERTIES'][$code]))
					unset($arResult['DELETED_PROPERTIES'][$code]);
			}
			unset($code, $isEmpty);
		}
	}

	$arResult['FIELDS_REQUIRED'] = $fieldsRequired;
	$arResult['FIELDS_SORT'] = array(
		'ID' => 100,
		'IBLOCK_ID' => 150,
		'CODE' => 200,
		'XML_ID' => 300,
		'NAME' => 400,
		'TAGS' => 500,
		'SORT' => 600,
		'DESCRIPTION' => 700,
		'DESCRIPTION_TYPE' => 800,
		'PICTURE' => 900,
		'PREVIEW_TEXT' => 1000,
		'PREVIEW_TEXT_TYPE' => 1100,
		'PREVIEW_PICTURE' => 1200,
		'DETAIL_TEXT' => 1300,
		'DETAIL_TEXT_TYPE' => 1400,
		'DETAIL_PICTURE' => 1500,
		'DATE_ACTIVE_FROM' => 1600,
		'ACTIVE_FROM' => 1700,
		'DATE_ACTIVE_TO' => 1800,
		'ACTIVE_TO' => 1900,
		'SHOW_COUNTER' => 2000,
		'SHOW_COUNTER_START' => 2100,
		'STATUS' => 2200,
		'IBLOCK_TYPE_ID' => 2300,
		'IBLOCK_CODE' => 2400,
		'IBLOCK_NAME' => 2500,
		'IBLOCK_EXTERNAL_ID' => 2600,
		'DATE_CREATE' => 2700,
		'CREATED_BY' => 2800,
		'CREATED_USER_NAME' => 2900,
		'TIMESTAMP_X' => 3000,
		'MODIFIED_BY' => 3100,
		'USER_NAME' => 3200,
		'SECTION_ID' => 3300,
		'ACTIVE' => 3400,
		'BP_PUBLISHED' => 3500,
		'SECTIONS' => 3600,
		'IBLOCK_SECTION' => 3700,
		'ACTIVE_PERIOD_FROM' => 3800,
		'ACTIVE_PERIOD_TO' => 3900
	);
	$arResult["ITEMS_TO_ADD"] = array();
	if ($arParams["DISPLAY_ELEMENT_SELECT_BOX"])
	{
		$bIBlockCatalog = false;
		$arCatalog = false;
		if ($catalogIncluded)
		{
			$arCatalog = CCatalog::GetByID($arParams["IBLOCK_ID"]);
			if (!empty($arCatalog) && is_array($arCatalog))
				$bIBlockCatalog = true;
		}
		$arResult['CATALOG'] = $arCatalog;

		$arSelect = array(
			"ID",
			"NAME",
		);
		$arFilter = array(
			"!ID" => array_keys($arCompare),
			"IBLOCK_LID" => SITE_ID,
			"IBLOCK_ACTIVE" => "Y",
			"ACTIVE_DATE" => "Y",
			"ACTIVE" => "Y",
			"CHECK_PERMISSIONS" => "Y",
		);
		if ($bIBlockCatalog && 'Y' == $arParams['HIDE_NOT_AVAILABLE'])
			$arFilter['CATALOG_AVAILABLE'] = 'Y';

		if ($arResult["OFFERS_IBLOCK_ID"] > 0)
		{
			$arFilter["IBLOCK_ID"] = array($arParams["IBLOCK_ID"], $arResult["OFFERS_IBLOCK_ID"]);
			$arFilter["!=ID"] = CIBlockElement::SubQuery("PROPERTY_".$arResult["OFFERS_PROPERTY_ID"], array(
				"IBLOCK_ID" => $arResult["OFFERS_IBLOCK_ID"]
			));
		}
		else
		{
			$arFilter["IBLOCK_ID"] = $arParams["IBLOCK_ID"];
		}

		$arSort = array(
			$arParams["ELEMENT_SORT_FIELD_BOX"] => $arParams["ELEMENT_SORT_ORDER_BOX"],
			$arParams["ELEMENT_SORT_FIELD_BOX2"] => $arParams["ELEMENT_SORT_ORDER_BOX2"],
		);
		$rsElements = CIBlockElement::GetList($arSort, $arFilter, false, false, $arSelect);
		while ($arElement = $rsElements->GetNext())
		{
			$arResult["ITEMS_TO_ADD"][$arElement["ID"]]=$arElement["NAME"];
		}
	}
	$this->includeComponentTemplate();
}
else
{
	$actionByAjax = (
		(isset($_REQUEST['ajax_action']) && $_REQUEST['ajax_action'] == 'Y')
		|| (isset($_REQUEST['compare_result_reload']) && $_REQUEST['compare_result_reload'] == 'Y')
	);
	if ($actionByAjax)
	{
		$APPLICATION->RestartBuffer();
		ShowNote(GetMessage("CATALOG_COMPARE_LIST_EMPTY"));
		die();
	}
	ShowNote(GetMessage("CATALOG_COMPARE_LIST_EMPTY"));
}
