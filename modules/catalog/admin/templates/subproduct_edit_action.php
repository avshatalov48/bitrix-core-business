<?php

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Main;
use Bitrix\Catalog;

/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @var string $strWarning */
/** @var int $IBLOCK_ID */
/** @var int $ID */
/** @var bool $bCreateRecord */

/** @global array $arCatalogBaseGroup */
/** @global array $arCatalogBasePrices */
/** @global array $arCatalogPrices */

if ($strWarning !== '')
{
	return;
}

$IBLOCK_ID = (int)$IBLOCK_ID;
$ID = (int)$ID;
$userId = (int)$USER->GetID();

if ($IBLOCK_ID <= 0 || $ID <= 0)
{
	return;
}

$PRODUCT_ID = CIBlockElement::GetRealElement($ID);

$accessController = AccessController::getCurrent();

$iblockEditProduct = $bCreateRecord
	? CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, 'element_edit_price')
	: CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $PRODUCT_ID, 'element_edit_price')
;

$allowEdit = false;
if ($iblockEditProduct)
{
	$allowEdit = $bCreateRecord
		? $accessController->check(ActionDictionary::ACTION_PRODUCT_ADD)
		: $accessController->check(ActionDictionary::ACTION_PRODUCT_EDIT)
	;
}
$allowEditPrices = $allowEdit
	&& $accessController->check(ActionDictionary::ACTION_PRICE_EDIT)
;

if (!$allowEdit && !$allowEditPrices)
{
	return;
}

IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/catalog/templates/product_edit_action.php');

// region Save product data
if ($allowEdit)
{
	$bUseStoreControl = Catalog\Config\State::isUsedInventoryManagement();
	$bEnableReservation = Main\Config\Option::get('catalog', 'enable_reservation') === 'Y';

	$arCatalog = CCatalog::GetByID($IBLOCK_ID);

	// region Check form fields
	$quantityTrace = ($_POST['SUBCAT_BASE_QUANTITY_TRACE'] ?? null);
	if (
		$quantityTrace !== Catalog\ProductTable::STATUS_DEFAULT
		&& $quantityTrace !== Catalog\ProductTable::STATUS_YES
		&& $quantityTrace !== Catalog\ProductTable::STATUS_NO
	)
	{
		$quantityTrace = null;
	}
	$canBuyZero = ($_POST['SUBUSE_STORE'] ?? null);
	if (
		$canBuyZero !== Catalog\ProductTable::STATUS_DEFAULT
		&& $canBuyZero !== Catalog\ProductTable::STATUS_YES
		&& $canBuyZero !== Catalog\ProductTable::STATUS_NO
	)
	{
		$canBuyZero = null;
	}
	$weight = ($_POST['SUBCAT_BASE_WEIGHT'] ?? null);
	if ($weight === '')
	{
		$weight = 0;
	}
	$subscribe = ($_POST['SUBSUBSCRIBE'] ?? null);
	if (
		$subscribe !== Catalog\ProductTable::STATUS_DEFAULT
		&& $subscribe !== Catalog\ProductTable::STATUS_YES
		&& $subscribe !== Catalog\ProductTable::STATUS_NO
	)
	{
		$subscribe = null;
	}
	// endregion

	$barcodeMultiply = $_POST["SUBCAT_BARCODE_MULTIPLY"] ?? '';
	if(!$barcodeMultiply || $barcodeMultiply == '')
		$barcodeMultiply = 'N';

	if(isset($_REQUEST["SUBCAT_BARCODE"]) && ($barcodeMultiply == 'Y'))
	{
		$row = Catalog\ProductTable::getRowById($PRODUCT_ID);
		if (!empty($row) && $row['BARCODE_MULTI'] == 'N')
		{
			$countBarCode = 0;
			$arBarCodeResult = array();
			$dbAmount = CCatalogStoreControlUtil::getQuantityInformation($PRODUCT_ID);
			if (is_object($dbAmount) && ($arAmount = $dbAmount->Fetch()))
			{
				$dbBarCode = CCatalogStoreBarCode::GetList(array(), array("PRODUCT_ID" => $PRODUCT_ID), false, false, array("ID", "BARCODE", "PRODUCT_ID", "STORE_ID"));
				while ($arBarCode = $dbBarCode->Fetch())
				{
					$arBarCodeResult = $arBarCode;
					$countBarCode++;
				}
				if ((!empty($arBarCodeResult)) && ($countBarCode == 1) && (intval($arBarCodeResult["STORE_ID"]) == 0))
				{
					if (CCatalogStoreBarCode::Delete($arBarCode["ID"]))
						$countBarCode--;
				}
				if ($arAmount["SUM"] != $countBarCode)
				{
					$strWarning .= GetMessage("C2IT_ERROR_USE_MULTIBARCODE", array("#COUNT#" => ($arAmount["SUM"] - $countBarCode)));
					$barcodeMultiply = 'N';
					unset($_REQUEST["SUBCAT_BARCODE"]);
				}
			}
		}
		unset($row);
	}
	elseif(isset($_REQUEST["SUBCAT_BARCODE"]) && $barcodeMultiply != 'Y')
	{
		$arId = array();
		$arBarCodeFields = array(
			"BARCODE" => $_REQUEST["SUBCAT_BARCODE"],
			"PRODUCT_ID" => $PRODUCT_ID,
			"CREATED_BY" => $userId,
			"MODIFIED_BY" => $userId,
			"STORE_ID" => 0,
		);
		$dbBarCode = CCatalogStoreBarCode::GetList(array(), array("PRODUCT_ID" => $PRODUCT_ID), false, false, array("ID", "BARCODE", "PRODUCT_ID"));
		while($arBarCode = $dbBarCode->Fetch())
		{
			$arId[] = $arBarCode["ID"];
		}
		if(count($arId) == 1)
		{
			if (isset($_REQUEST['SUBCAT_BARCODE_EDIT']) && 'Y' == $_REQUEST['SUBCAT_BARCODE_EDIT'])
			{
				if ('' != trim($_REQUEST["SUBCAT_BARCODE"]))
				{
					if(!CCatalogStoreBarCode::Update($arId[0], array("MODIFIED_BY" => $userId, "STORE_ID" => 0, 'BARCODE' => trim($_REQUEST["SUBCAT_BARCODE"]))))
						$strWarning .= GetMessage("C2IT_ERROR_SAVE_BARCODE");
				}
				else
				{
					if (!CCatalogStoreBarCode::Delete($arId[0]))
						$strWarning .= GetMessage("C2IT_ERROR_SAVE_BARCODE");
				}
			}
			else
			{
				if(!CCatalogStoreBarCode::Update($arId[0], array("MODIFIED_BY" => $userId, "STORE_ID" => 0)))
					$strWarning .= GetMessage("C2IT_ERROR_SAVE_BARCODE");
			}
		}
		elseif(count($arId) == 0)
		{
			if(trim($_REQUEST["SUBCAT_BARCODE"]) != '')
			{
				if(!CCatalogStoreBarCode::Add($arBarCodeFields))
					$strWarning .= GetMessage("C2IT_ERROR_SAVE_BARCODE");
			}
		}
		else
		{
			$strWarning .= GetMessage("C2IT_ERROR_SAVE_MULTIBARCODE");
			$barcodeMultiply = 'Y';
		}
	}

	$vatIncluded = $_POST['SUBCAT_VAT_INCLUDED'] ?? null;
	if ($vatIncluded !== 'Y')
	{
		$vatIncluded = 'N';
	}
	$productFields = [
		'WIDTH' => $_POST['SUBCAT_BASE_WIDTH'] ?? null,
		'LENGTH' => $_POST['SUBCAT_BASE_LENGTH'] ?? null,
		'HEIGHT' => $_POST['SUBCAT_BASE_HEIGHT'] ?? null,
		'VAT_ID' => $_POST['SUBCAT_VAT_ID'] ?? null,
		'VAT_INCLUDED' => $vatIncluded,
		'PRICE_TYPE' => false,
		'RECUR_SCHEME_TYPE' => false,
		'RECUR_SCHEME_LENGTH' => false,
		'TRIAL_PRICE_ID' => false,
		'WITHOUT_ORDER' => false,
		'BARCODE_MULTI' => $barcodeMultiply,
		'MEASURE' => $_POST['SUBCAT_MEASURE'] ?? null,
		'TYPE' => Catalog\ProductTable::TYPE_OFFER,
	];
	if ($quantityTrace !== null)
	{
		$productFields['QUANTITY_TRACE'] = $quantityTrace;
	}
	if ($canBuyZero !== null)
	{
		$productFields['CAN_BUY_ZERO'] = $canBuyZero;
	}
	if ($subscribe !== null)
	{
		$productFields['SUBSCRIBE'] = $subscribe;
	}
	if ($weight !== null)
	{
		$productFields['WEIGHT'] = $weight;
	}

	if (AccessController::getCurrent()->check(ActionDictionary::ACTION_PRODUCT_PURCHASE_INFO_VIEW) && !$bUseStoreControl)
	{
		if (
			isset($_POST['SUBCAT_PURCHASING_PRICE'])
			&& isset($_POST['SUBCAT_PURCHASING_CURRENCY'])
		)
		{
			$price = trim($_POST['SUBCAT_PURCHASING_PRICE']);
			$currency = trim($_POST['SUBCAT_PURCHASING_CURRENCY']);
			if ($price == '' || $currency == '')
			{
				$price = false;
				$currency = false;
			}
			$productFields['PURCHASING_PRICE'] = $price;
			$productFields['PURCHASING_CURRENCY'] = $currency;
			unset($currency, $price);
		}
	}

	if(!$bUseStoreControl)
	{
		$productFields['QUANTITY'] = $_POST['SUBCAT_BASE_QUANTITY'] ?? null;
		if ($productFields['QUANTITY'] === '' || $productFields['QUANTITY'] === null)
		{
			unset($productFields['QUANTITY']);
		}
		else
		{
			if (is_string($productFields['QUANTITY']))
			{
				$productFields['QUANTITY'] = str_replace(',', '.', $productFields['QUANTITY']);
			}
		}
		if ($bEnableReservation && isset($_POST['SUBCAT_BASE_QUANTITY_RESERVED']))
		{
			$productFields['QUANTITY_RESERVED'] = $_POST['SUBCAT_BASE_QUANTITY_RESERVED'];
			if ($productFields['QUANTITY_RESERVED'] === '')
			{
				unset($productFields['QUANTITY_RESERVED']);
			}
			else
			{
				if (is_string($productFields['QUANTITY_RESERVED']))
				{
					$productFields['QUANTITY_RESERVED'] = str_replace(',', '.', $productFields['QUANTITY_RESERVED']);
				}
			}
		}
	}

	if ($arCatalog["SUBSCRIPTION"] == "Y")
	{
		$productFields["PRICE_TYPE"] = $_POST['SUBCAT_PRICE_TYPE'] ?? null;
		$productFields["RECUR_SCHEME_TYPE"] = $_POST['SUBCAT_RECUR_SCHEME_TYPE'] ?? null;
		$productFields["RECUR_SCHEME_LENGTH"] = $_POST['SUBCAT_RECUR_SCHEME_LENGTH'] ?? null;
		$productFields["TRIAL_PRICE_ID"] = $_POST['SUBCAT_TRIAL_PRICE_ID'] ?? null;
		$productFields["WITHOUT_ORDER"] = $_POST['SUBCAT_WITHOUT_ORDER'] ?? null;
		$productFields["QUANTITY_TRACE"] = Catalog\ProductTable::STATUS_NO;
		$productFields["CAN_BUY_ZERO"] = Catalog\ProductTable::STATUS_NO;
	}

	$userFieldManager = Main\UserField\Internal\UserFieldHelper::getInstance()->getManager();
	$userFieldManager->EditFormAddFields(Catalog\ProductTable::getUfId(), $productFields);
	unset($userFieldManager);

	$iterator = Catalog\Model\Product::getList(array(
		'select' => ['ID'],
		'filter' => ['=ID' => $PRODUCT_ID]
	));
	$row = $iterator->fetch();
	unset($iterator);
	if (!empty($row))
	{
		$productResult = CCatalogProduct::Update($PRODUCT_ID, $productFields);
	}
	else
	{
		if ($bUseStoreControl)
		{
			$productFields['QUANTITY'] = 0;
			$productFields['QUANTITY_RESERVED'] = 0;
		}
		$productFields['ID'] = $PRODUCT_ID;
		$productResult = CCatalogProduct::Add($productFields, false);
	}
	unset($row);

	if (!$productResult)
	{
		if ($ex = $APPLICATION->GetException())
			$strWarning .= GetMessage(
					'C2IT_ERROR_PRODUCT_SAVE_ERROR',
					array('#ERROR#' => $ex->GetString())
				).'<br>';
		else
			$strWarning .= GetMessage('C2IT_ERROR_PRODUCT_SAVE_UNKNOWN_ERROR').'<br>';
		unset($ex);
		return;
	}
	unset($productResult);

	$arMeasureRatio = [
		'PRODUCT_ID' => $PRODUCT_ID,
		'RATIO' => $_POST['SUBCAT_MEASURE_RATIO'] ?? 1,
		'IS_DEFAULT' => 'Y',
	];
	$newRatio = true;
	$currentRatioID = 0;
	if (isset($_POST['SUBCAT_MEASURE_RATIO_ID']))
		$currentRatioID = (int)$_POST['SUBCAT_MEASURE_RATIO_ID'];
	$ratioFilter = ['=PRODUCT_ID' => $PRODUCT_ID, '=RATIO' => $arMeasureRatio['RATIO']];
	$ratioIterator = Catalog\MeasureRatioTable::getList([
		'select' => ['*'],
		'filter' => $ratioFilter
	]);
	$currentRatio = $ratioIterator->fetch();
	if (empty($currentRatio) && $currentRatioID > 0)
	{
		$ratioFilter = ['=PRODUCT_ID' => $PRODUCT_ID, '=ID' => $currentRatioID];
		$ratioIterator = Catalog\MeasureRatioTable::getList([
			'select' => ['*'],
			'filter' => $ratioFilter
		]);
		$currentRatio = $ratioIterator->fetch();
	}
	unset($ratioIterator, $ratioFilter);
	if (!empty($currentRatio))
	{
		$currentRatioID = $currentRatio['ID'];
		$newRatio = false;
	}
	unset($currentRatio);
	if ($newRatio)
		$currentRatioID = (int)CCatalogMeasureRatio::add($arMeasureRatio);
	else
		$currentRatioID = CCatalogMeasureRatio::update($currentRatioID, $arMeasureRatio);
	unset($newRatio, $arMeasureRatio);

	if ($currentRatioID > 0)
	{
		$iterator = CCatalogMeasureRatio::getList(
			[],
			['PRODUCT_ID' => $PRODUCT_ID],
			false,
			false,
			['ID']
		);
		while ($row = $iterator->Fetch())
		{
			if ($row['ID'] == $currentRatioID)
				continue;
			CCatalogMeasureRatio::delete($row['ID']);
		}
		unset($row, $iterator);
	}
	unset($currentRatioID);

	if ($arCatalog["SUBSCRIPTION"] == "Y")
	{
		$arCurProductGroups = array();

		$dbProductGroups = CCatalogProductGroups::GetList(
			array(),
			array("PRODUCT_ID" => $ID),
			false,
			false,
			array("ID", "GROUP_ID", "ACCESS_LENGTH", "ACCESS_LENGTH_TYPE")
		);
		while ($arProductGroup = $dbProductGroups->Fetch())
		{
			$arCurProductGroups[(int)$arProductGroup["GROUP_ID"]] = $arProductGroup;
		}

		$arAvailContentGroups = array();
		$availContentGroups = COption::GetOptionString("catalog", "avail_content_groups");
		if ($availContentGroups <> '')
			$arAvailContentGroups = explode(",", $availContentGroups);

		$dbGroups = CGroup::GetList(
			"c_sort",
			"asc",
			array("ANONYMOUS" => "N")
		);
		while ($arGroup = $dbGroups->Fetch())
		{
			$arGroup["ID"] = (int)$arGroup["ID"];

			if ($arGroup["ID"] == 2
				|| !in_array($arGroup["ID"], $arAvailContentGroups))
			{
				if (isset($arCurProductGroups[$arGroup["ID"]]))
					CCatalogProductGroups::Delete($arCurProductGroups[$arGroup["ID"]]["ID"]);

				continue;
			}

			if (isset($arCurProductGroups[$arGroup["ID"]]))
			{
				if (isset(${"SUBCAT_USER_GROUP_ID_".$arGroup["ID"]}) && ${"SUBCAT_USER_GROUP_ID_".$arGroup["ID"]} == "Y")
				{
					if ((int)(${"SUBCAT_ACCESS_LENGTH_".$arGroup["ID"]}) != (int)($arCurProductGroups[$arGroup["ID"]]["ACCESS_LENGTH"])
						|| ${"SUBCAT_ACCESS_LENGTH_TYPE_".$arGroup["ID"]} != $arCurProductGroups[$arGroup["ID"]]["ACCESS_LENGTH_TYPE"])
					{
						$arCatalogFields = array(
							"ACCESS_LENGTH" => (int)(${"SUBCAT_ACCESS_LENGTH_".$arGroup["ID"]}),
							"ACCESS_LENGTH_TYPE" => ${"SUBCAT_ACCESS_LENGTH_TYPE_".$arGroup["ID"]}
						);
						CCatalogProductGroups::Update($arCurProductGroups[$arGroup["ID"]]["ID"], $arCatalogFields);
					}
				}
				else
				{
					CCatalogProductGroups::Delete($arCurProductGroups[$arGroup["ID"]]["ID"]);
				}
			}
			else
			{
				if (isset(${"SUBCAT_USER_GROUP_ID_".$arGroup["ID"]}) && ${"SUBCAT_USER_GROUP_ID_".$arGroup["ID"]} == "Y")
				{
					$arCatalogFields = array(
						"PRODUCT_ID" => $ID,
						"GROUP_ID" => $arGroup["ID"],
						"ACCESS_LENGTH" => (int)(${"SUBCAT_ACCESS_LENGTH_".$arGroup["ID"]}),
						"ACCESS_LENGTH_TYPE" => ${"SUBCAT_ACCESS_LENGTH_TYPE_".$arGroup["ID"]}
					);
					CCatalogProductGroups::Add($arCatalogFields);
				}
			}
		}
	}

	if (!$bUseStoreControl && AccessController::getCurrent()->check(ActionDictionary::ACTION_STORE_VIEW))
	{
		$storeProducts = array();
		$iterator = Catalog\StoreProductTable::getList(array(
			'select' => array('ID', 'STORE_ID', 'AMOUNT'),
			'filter' => array('=PRODUCT_ID' => $PRODUCT_ID, '=STORE.ACTIVE' => 'Y')
		));
		while ($row = $iterator->fetch())
			$storeProducts[$row['STORE_ID']] = $row;
		unset($row, $iterator);
		$iterator = Catalog\StoreTable::getList(array(
			'select' => array('ID'),
			'filter' => array('=ACTIVE' => 'Y')
		));
		while ($row = $iterator->fetch())
		{
			if (!isset($_POST['SUBAR_AMOUNT'][$row['ID']]))
				continue;
			$amount = trim((string)$_POST['SUBAR_AMOUNT'][$row['ID']]);
			if ($amount === '' && !isset($storeProducts[$row['ID']]))
				continue;
			if ($amount === '')
			{
				$storeRes = CCatalogStoreProduct::Delete($storeProducts[$row['ID']]['ID']);
			}
			else
			{
				$fields = array(
					'PRODUCT_ID' => $PRODUCT_ID,
					'STORE_ID' => $row['ID'],
					'AMOUNT' => $amount
				);
				if (isset($storeProducts[$row['ID']]))
					$storeRes = CCatalogStoreProduct::Update($storeProducts[$row['ID']]['ID'], $fields);
				else
					$storeRes = CCatalogStoreProduct::Add($fields);
			}
			if (!$storeRes)
			{
				$bVarsFromForm = true;
				break;
			}
		}
		unset($fields, $row, $iterator);
	}
}
// endregion

// region Save prices
if ($allowEditPrices)
{
	$enableQuantityRanges = Catalog\Config\Feature::isPriceQuantityRangesEnabled();

	if ($enableQuantityRanges)
	{
		$bUseExtForm = (isset($_POST['subprice_useextform']) && $_POST['subprice_useextform'] === 'Y');
	}
	else
	{
		$bUseExtForm = false;
	}

	$intBasePriceCount = count($arCatalogBasePrices);
	$dbCatGroups = CCatalogGroup::GetList(array(), array("!BASE" => "Y"));
	while ($arCatGroups = $dbCatGroups->Fetch())
	{
		$arCatalogPrice_tmp = array();

		for ($i = 0; $i < $intBasePriceCount; $i++)
		{
			${"SUBCAT_PRICE_".$arCatGroups["ID"]."_".$arCatalogBasePrices[$i]["IND"]} = str_replace([' ', ','], ['', '.'], ${"SUBCAT_PRICE_".$arCatGroups["ID"]."_".$arCatalogBasePrices[$i]["IND"]});
			$arCatalogPrice_tmp[$i] = array(
				"ID" => (int)(${"SUBCAT_ID_".$arCatGroups["ID"]}[$arCatalogBasePrices[$i]["IND"]] ?? 0),
				"EXTRA_ID" => ${"SUBCAT_EXTRA_".$arCatGroups["ID"]."_".$arCatalogBasePrices[$i]["IND"]}
					? (int)(${"SUBCAT_EXTRA_".$arCatGroups["ID"]."_".$arCatalogBasePrices[$i]["IND"]})
					: 0,
				"PRICE" => ${"SUBCAT_PRICE_".$arCatGroups["ID"]."_".$arCatalogBasePrices[$i]["IND"]},
				"CURRENCY" => trim(${"SUBCAT_CURRENCY_".$arCatGroups["ID"]."_".$arCatalogBasePrices[$i]["IND"]}),
				"QUANTITY_FROM" => $arCatalogBasePrices[$i]["QUANTITY_FROM"],
				"QUANTITY_TO" => $arCatalogBasePrices[$i]["QUANTITY_TO"]
			);

			if ($arCatalogPrice_tmp[$i]["CURRENCY"] == '')
			{
				$arCatalogPrice_tmp[$i]["CURRENCY"] = $arCatalogBasePrices[$i]["CURRENCY"];
			}

			if ($arCatalogPrice_tmp[$i]["EXTRA_ID"] > 0)
			{
				if (0 < doubleval($arCatalogBasePrices[$i]["PRICE"]))
				{
					$arCatalogPrice_tmp[$i]["CURRENCY"] = $arCatalogBasePrices[$i]["CURRENCY"];
					$arCatalogExtra = CExtra::GetByID($arCatalogPrice_tmp[$i]["EXTRA_ID"]);
					$arCatalogPrice_tmp[$i]["PRICE"] = roundEx($arCatalogBasePrices[$i]["PRICE"] * (1 + (float)$arCatalogExtra["PERCENTAGE"] / 100), CATALOG_VALUE_PRECISION);
				}
				else
				{
					$arCatalogPrice_tmp[$i]["EXTRA_ID"] = 0;
				}
			}
		}

		$arCatalogPrices[$arCatGroups["ID"]] = $arCatalogPrice_tmp;
		unset($arCatalogPrice_tmp);
	}

	$arUpdatedIDs = [];

	$intCountBasePrice = count($arCatalogBasePrices);
	for ($i = 0; $i < $intCountBasePrice; $i++)
	{
		if ($arCatalogBasePrices[$i]["PRICE"] <> '')
		{
			$arCatalogFields = array(
				"EXTRA_ID" => false,
				"PRODUCT_ID" => $PRODUCT_ID,
				"CATALOG_GROUP_ID" => $arCatalogBaseGroup["ID"],
				"PRICE" => (float)$arCatalogBasePrices[$i]["PRICE"],
				"CURRENCY" => $arCatalogBasePrices[$i]["CURRENCY"],
				"QUANTITY_FROM" => ($arCatalogBasePrices[$i]["QUANTITY_FROM"] > 0 ? $arCatalogBasePrices[$i]["QUANTITY_FROM"] : false),
				"QUANTITY_TO" => ($arCatalogBasePrices[$i]["QUANTITY_TO"] > 0 ? $arCatalogBasePrices[$i]["QUANTITY_TO"] : false)
			);

			if ($arCatalogBasePrices[$i]["ID"] > 0)
			{
				$arCatalogPrice = CPrice::GetByID($arCatalogBasePrices[$i]["ID"]);
				if ($arCatalogPrice && $arCatalogPrice["PRODUCT_ID"] == $PRODUCT_ID)
				{
					$arUpdatedIDs[] = $arCatalogBasePrices[$i]["ID"];
					if (!CPrice::Update($arCatalogBasePrices[$i]["ID"], $arCatalogFields))
						$strWarning .= str_replace("#ID#", $arCatalogBasePrices[$i]["ID"], GetMessage("C2IT_ERROR_PRPARAMS"))."<br>";
				}
				else
				{
					$ID_tmp = CPrice::Add($arCatalogFields);
					$arUpdatedIDs[] = $ID_tmp;
					if (!$ID_tmp)
						$strWarning .= str_replace("#PRICE#", $arCatalogFields["PRICE"], GetMessage("C2IT_ERROR_SAVEPRICE"))."<br>";
				}
			}
			else
			{
				$ID_tmp = CPrice::Add($arCatalogFields);
				$arUpdatedIDs[] = $ID_tmp;
				if (!$ID_tmp)
					$strWarning .= str_replace("#PRICE#", $arCatalogFields["PRICE"], GetMessage("C2IT_ERROR_SAVEPRICE"))."<br>";
			}
		}
	}

	foreach ($arCatalogPrices as $catalogGroupID => $arCatalogPrice_tmp)
	{
		$intCountPrices = count($arCatalogPrice_tmp);
		for ($i = 0; $i < $intCountPrices; $i++)
		{
			if ($arCatalogPrice_tmp[$i]["PRICE"] <> '')
			{
				$arCatalogFields = array(
					"EXTRA_ID" => ($arCatalogPrice_tmp[$i]["EXTRA_ID"] > 0 ? $arCatalogPrice_tmp[$i]["EXTRA_ID"] : false),
					"PRODUCT_ID" => $PRODUCT_ID,
					"CATALOG_GROUP_ID" => $catalogGroupID,
					"PRICE" => (float)$arCatalogPrice_tmp[$i]["PRICE"],
					"CURRENCY" => $arCatalogPrice_tmp[$i]["CURRENCY"],
					"QUANTITY_FROM" => ($arCatalogPrice_tmp[$i]["QUANTITY_FROM"] > 0 ? $arCatalogPrice_tmp[$i]["QUANTITY_FROM"] : false),
					"QUANTITY_TO" => ($arCatalogPrice_tmp[$i]["QUANTITY_TO"] > 0 ? $arCatalogPrice_tmp[$i]["QUANTITY_TO"] : false)
				);

				if ($arCatalogPrice_tmp[$i]["ID"] > 0)
				{
					$arCatalogPrice = CPrice::GetByID($arCatalogPrice_tmp[$i]["ID"]);
					if ($arCatalogPrice && $arCatalogPrice["PRODUCT_ID"] == $PRODUCT_ID)
					{
						$arUpdatedIDs[] = $arCatalogPrice_tmp[$i]["ID"];
						if (!CPrice::Update($arCatalogPrice_tmp[$i]["ID"], $arCatalogFields))
							$strWarning .= str_replace("#ID#", $arCatalogPrice_tmp[$i]["ID"], GetMessage("C2IT_ERROR_PRPARAMS"))."<br>";
					}
					else
					{
						$ID_tmp = CPrice::Add($arCatalogFields);
						$arUpdatedIDs[] = $ID_tmp;
						if (!$ID_tmp)
							$strWarning .= str_replace("#PRICE#", $arCatalogFields["PRICE"], GetMessage("C2IT_ERROR_SAVEPRICE"))."<br>";
					}
				}
				else
				{
					$ID_tmp = CPrice::Add($arCatalogFields);
					$arUpdatedIDs[] = $ID_tmp;
					if (!$ID_tmp)
						$strWarning .= str_replace("#PRICE#", $arCatalogFields["PRICE"], GetMessage("C2IT_ERROR_SAVEPRICE"))."<br>";
				}
			}
		}
	}

	CPrice::DeleteByProduct($PRODUCT_ID, $arUpdatedIDs);
}
// endregion

if ($strWarning === '')
{
	\Bitrix\Iblock\PropertyIndex\Manager::updateElementIndex($IBLOCK_ID, $PRODUCT_ID);
}
