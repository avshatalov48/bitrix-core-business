<?
use Bitrix\Catalog;
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @var string $strWarning */
/** @var int $IBLOCK_ID */
/** @var int $ID */

/** @global string $CAT_BASE_WEIGHT */
/** @global string $CAT_BASE_WIDTH */
/** @global string $CAT_BASE_LENGTH */
/** @global string $CAT_BASE_HEIGHT */
/** @global string $CAT_MEASURE */
/** @global string $CAT_BASE_QUANTITY */
/** @global string $CAT_PRICE_TYPE */
/** @global string $CAT_RECUR_SCHEME_TYPE */
/** @global string $CAT_RECUR_SCHEME_LENGTH */
/** @global string $CAT_TRIAL_PRICE_ID */
/** @global string $CAT_WITHOUT_ORDER */
/** @global string $CAT_MEASURE_RATIO */
/** @global string $CAT_VAT_ID */
/** @global string $CAT_VAT_INCLUDED */
/** @global array $arCatalogBaseGroup */
/** @global array $arCatalogBasePrices */
/** @global array $arCatalogPrices */

if ($USER->CanDoOperation('catalog_price'))
{
	$IBLOCK_ID = (int)$IBLOCK_ID;
	$ID = (int)$ID;
	$userId = (int)$USER->GetID();

	if (0 < $IBLOCK_ID && 0 < $ID)
	{
		$PRODUCT_ID = CIBlockElement::GetRealElement($ID);
		$bUseStoreControl = Catalog\Config\State::isUsedInventoryManagement();
		$bEnableReservation = (COption::GetOptionString('catalog', 'enable_reservation') != 'N');
		$enableQuantityRanges = Catalog\Config\Feature::isPriceQuantityRangesEnabled();

		if (CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $PRODUCT_ID, "element_edit_price"))
		{

			IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/catalog/templates/product_edit_action.php');
			if ('' == $strWarning)
			{
				if ($enableQuantityRanges)
					$bUseExtForm = (isset($_POST['price_useextform']) && 'Y' == $_POST['price_useextform']);
				else
					$bUseExtForm = false;

				$arCatalog = CCatalog::GetByID($IBLOCK_ID);

				$intBasePriceCount = count($arCatalogBasePrices);
				$dbCatGroups = CCatalogGroup::GetList(array(), array("!BASE" => "Y"));
				while ($arCatGroups = $dbCatGroups->Fetch())
				{
					$arCatalogPrice_tmp = array();

					for ($i = 0; $i < $intBasePriceCount; $i++)
					{
						${"CAT_PRICE_".$arCatGroups["ID"]."_".$arCatalogBasePrices[$i]["IND"]} = str_replace([' ', ','], ['', '.'], ${"CAT_PRICE_".$arCatGroups["ID"]."_".$arCatalogBasePrices[$i]["IND"]});
						$arCatalogPrice_tmp[$i] = array(
							"ID" => (int)(${"CAT_ID_".$arCatGroups["ID"]}[$arCatalogBasePrices[$i]["IND"]]),
							"EXTRA_ID" => ${"CAT_EXTRA_".$arCatGroups["ID"]."_".$arCatalogBasePrices[$i]["IND"]}
								? (int)(${"CAT_EXTRA_".$arCatGroups["ID"]."_".$arCatalogBasePrices[$i]["IND"]})
								: 0,
							"PRICE" => ${"CAT_PRICE_".$arCatGroups["ID"]."_".$arCatalogBasePrices[$i]["IND"]},
							"CURRENCY" => trim(${"CAT_CURRENCY_".$arCatGroups["ID"]."_".$arCatalogBasePrices[$i]["IND"]}),
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

				$arUpdatedIDs = array();
				$quantityTrace = $_POST['CAT_BASE_QUANTITY_TRACE'];
				if(!$quantityTrace || $quantityTrace == '')
					$quantityTrace = 'D';
				$useStore = $_POST['USE_STORE'];
				if(!$useStore || $useStore == '')
					$useStore = 'D';

				$barcodeMultiply = $_POST["CAT_BARCODE_MULTIPLY"];
				if(!$barcodeMultiply || $barcodeMultiply == '')
					$barcodeMultiply = 'N';
				if(isset($_REQUEST["AR_BARCODE_ID"]) && ($barcodeMultiply == 'Y'))
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
								$arBarCodeResult[] = $arBarCode;
								if ($arBarCode["STORE_ID"] != 0)
									$countBarCode++;
							}
							if (($arAmount["SUM"] + $arAmount["RESERVED"] != 0) || ($countBarCode > 0))
							{
								$strWarning .= GetMessage("C2IT_ERROR_USE_MULTIBARCODE", array("#COUNT#" => ($arAmount["SUM"] - $countBarCode)));
								$barcodeMultiply = 'N';
								unset($_REQUEST["AR_BARCODE_ID"]);
							}
							else
							{
								foreach ($arBarCodeResult as $barCode)
								{
									CCatalogStoreBarCode::Delete($barCode["ID"]);
								}
							}
						}
					}
					unset($row);
				}
				elseif(isset($_REQUEST["AR_BARCODE_ID"]) && is_array($_REQUEST["AR_BARCODE_ID"]) && $barcodeMultiply != 'Y')
				{
					$arBarCodeFieldsAdd = $arBarCodeFields = array();
					$isErrorSaveBarcode = false;
					foreach($_REQUEST["AR_BARCODE_ID"] as $barcodeId)
					{
						$barcodeId = intval($barcodeId);
						if(!isset($_REQUEST["CAT_BARCODE_$barcodeId"]) || trim($_REQUEST["CAT_BARCODE_$barcodeId"]) == '')
						{
							if(trim($_REQUEST["CAT_BARCODE_$barcodeId"]) == '')
							{
								CCatalogStoreBarCode::Delete($barcodeId);
							}
							continue;
						}

						$arBarCodeFields = array(
							"BARCODE" => trim($_REQUEST["CAT_BARCODE_$barcodeId"]),
							"PRODUCT_ID" => $PRODUCT_ID,
							"CREATED_BY" => $userId,
							"MODIFIED_BY" => $userId,
							"STORE_ID" => 0,
						);

						if($barcodeId > 0)
						{
							if(!CCatalogStoreBarCode::Update($barcodeId, $arBarCodeFields))
							{
								$isErrorSaveBarcode = true;
							}
						}
						else
						{
							if(!CCatalogStoreBarCode::Add($arBarCodeFields))
							{
								$isErrorSaveBarcode = true;
							}
						}

						if($isErrorSaveBarcode)
						{
							$strWarning .= GetMessage("C2IT_ERROR_SAVE_BARCODE");
							break;
						}
					}

					if(isset($_REQUEST["CAT_BARCODE_ADD"]) && is_array($_REQUEST["CAT_BARCODE_ADD"]))
						foreach($_REQUEST["CAT_BARCODE_ADD"] as $barcodeToAdd)
							if(trim($barcodeToAdd) != '')
								$arBarCodeFieldsAdd[] = array(
									"BARCODE" => trim($barcodeToAdd),
									"PRODUCT_ID" => $PRODUCT_ID,
									"CREATED_BY" => $userId,
									"MODIFIED_BY" => $userId,
									"STORE_ID" => 0,
								);

					if(count($arBarCodeFieldsAdd) > 0 && is_array($arBarCodeFieldsAdd))
						foreach($arBarCodeFieldsAdd as $arCodeToAdd)
							if(!CCatalogStoreBarCode::Add($arCodeToAdd))
							{
								$strWarning .= GetMessage("C2IT_ERROR_SAVE_BARCODE");
								break;
							}
				}
				$arFields = array(
					"ID" => $PRODUCT_ID,
					"QUANTITY_TRACE" => $quantityTrace,
					"WEIGHT" => $CAT_BASE_WEIGHT,
					"WIDTH" => $CAT_BASE_WIDTH,
					"LENGTH" => $CAT_BASE_LENGTH,
					"HEIGHT" => $CAT_BASE_HEIGHT,
					"VAT_ID" => $CAT_VAT_ID,
					"VAT_INCLUDED" => $CAT_VAT_INCLUDED,
					"CAN_BUY_ZERO" => $useStore,
					"PRICE_TYPE" => false,
					"RECUR_SCHEME_TYPE" => false,
					"RECUR_SCHEME_LENGTH" => false,
					"TRIAL_PRICE_ID" => false,
					"WITHOUT_ORDER" => false,
					"BARCODE_MULTI" => $barcodeMultiply,
					"MEASURE" => $CAT_MEASURE,
				);
				if ($arFields['WEIGHT'] === '' || $arFields['WEIGHT'] === null)
					unset($arFields['WEIGHT']);
				if ($USER->CanDoOperation('catalog_purchas_info') && !$bUseStoreControl)
				{
					if (
						isset($_POST['CAT_PURCHASING_PRICE'])
						&& isset($_POST['CAT_PURCHASING_CURRENCY'])
					)
					{
						$price = trim($_POST['CAT_PURCHASING_PRICE']);
						$currency = trim($_POST['CAT_PURCHASING_CURRENCY']);
						if ($price == '' || $currency == '')
						{
							$price = false;
							$currency = false;
						}
						$arFields['PURCHASING_PRICE'] = $price;
						$arFields['PURCHASING_CURRENCY'] = $currency;
						unset($currency, $price);
					}
				}

				if (isset($_POST['SUBSCRIBE']))
					$arFields['SUBSCRIBE'] = strval($_POST['SUBSCRIBE']);

				if(!$bUseStoreControl)
				{
					$arFields['QUANTITY'] = $CAT_BASE_QUANTITY;
					if ($arFields['QUANTITY'] === '' || $arFields['QUANTITY'] === null)
						unset($arFields['QUANTITY']);
					if ($bEnableReservation && isset($CAT_BASE_QUANTITY_RESERVED))
					{
						$arFields['QUANTITY_RESERVED'] = $CAT_BASE_QUANTITY_RESERVED;
						if ($arFields['QUANTITY_RESERVED'] === '' || $arFields['QUANTITY_RESERVED'] === null)
							unset($arFields['QUANTITY_RESERVED']);
					}
				}

				if ($arCatalog["SUBSCRIPTION"] == "Y")
				{
					$arFields["PRICE_TYPE"] = $CAT_PRICE_TYPE;
					$arFields["RECUR_SCHEME_TYPE"] = $CAT_RECUR_SCHEME_TYPE;
					$arFields["RECUR_SCHEME_LENGTH"] = $CAT_RECUR_SCHEME_LENGTH;
					$arFields["TRIAL_PRICE_ID"] = $CAT_TRIAL_PRICE_ID;
					$arFields["WITHOUT_ORDER"] = $CAT_WITHOUT_ORDER;
					$arFields["QUANTITY_TRACE"] = Catalog\ProductTable::STATUS_NO;
					$arFields["CAN_BUY_ZERO"] = Catalog\ProductTable::STATUS_NO;
				}

				$USER_FIELD_MANAGER->EditFormAddFields(Catalog\ProductTable::getUfId(), $arFields);

				$iterator = Catalog\Model\Product::getList(array(
					'select' => ['ID', 'TYPE'],
					'filter' => ['=ID' => $arFields['ID']]
				));
				$row = $iterator->fetch();
				unset($iterator);
				if (!empty($row))
				{
					if (
						$row['TYPE'] == Catalog\ProductTable::TYPE_EMPTY_SKU
					)
					{
						$catalogInfo = \CCatalogSku::GetInfoByIBlock($IBLOCK_ID);
						if (
							!empty($catalogInfo)
							&& (
								$catalogInfo['CATALOG_TYPE'] == \CCatalogSku::TYPE_FULL
								|| $catalogInfo['CATALOG_TYPE'] == \CCatalogSku::TYPE_CATALOG
							)
						)
						{
							$arFields['TYPE'] = Catalog\ProductTable::TYPE_PRODUCT;
						}
						unset($catalogInfo);
					}

					$productResult = CCatalogProduct::Update($arFields['ID'], $arFields);
				}
				else
				{
					if ($bUseStoreControl)
					{
						$arFields['QUANTITY'] = 0;
						$arFields['QUANTITY_RESERVED'] = 0;
					}
					$productResult = CCatalogProduct::Add($arFields, false);
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

				$ratioList = [];
				$arMeasureRatio = [
					'PRODUCT_ID' => $PRODUCT_ID,
					'RATIO' => $CAT_MEASURE_RATIO,
					'IS_DEFAULT' => 'Y'
				];
				$newRatio = true;
				$currentRatioID = 0;
				if (isset($_POST['CAT_MEASURE_RATIO_ID']))
					$currentRatioID = (int)$_POST['CAT_MEASURE_RATIO_ID'];
				$ratioFilter = ['=PRODUCT_ID' => $PRODUCT_ID, '=RATIO' => $CAT_MEASURE_RATIO];
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
					$intCountPrice = count($arCatalogPrice_tmp);
					for ($i = 0; $i < $intCountPrice; $i++)
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
				\Bitrix\Iblock\PropertyIndex\Manager::updateElementIndex($IBLOCK_ID, $PRODUCT_ID);

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
						($b = "c_sort"),
						($o = "asc"),
						array("ANONYMOUS" => "N")
					);
					while ($arGroup = $dbGroups->Fetch())
					{
						$arGroup["ID"] = intval($arGroup["ID"]);

						if ($arGroup["ID"] == 2
							|| !in_array($arGroup["ID"], $arAvailContentGroups))
						{
							if (isset($arCurProductGroups[$arGroup["ID"]]))
								CCatalogProductGroups::Delete($arCurProductGroups[$arGroup["ID"]]["ID"]);

							continue;
						}

						if (isset($arCurProductGroups[$arGroup["ID"]]))
						{
							if (isset(${"CAT_USER_GROUP_ID_".$arGroup["ID"]}) && ${"CAT_USER_GROUP_ID_".$arGroup["ID"]} == "Y")
							{
								if ((int)(${"CAT_ACCESS_LENGTH_".$arGroup["ID"]}) != (int)($arCurProductGroups[$arGroup["ID"]]["ACCESS_LENGTH"])
									|| ${"CAT_ACCESS_LENGTH_TYPE_".$arGroup["ID"]} != $arCurProductGroups[$arGroup["ID"]]["ACCESS_LENGTH_TYPE"])
								{
									$arCatalogFields = array(
										"ACCESS_LENGTH" => (int)(${"CAT_ACCESS_LENGTH_".$arGroup["ID"]}),
										"ACCESS_LENGTH_TYPE" => ${"CAT_ACCESS_LENGTH_TYPE_".$arGroup["ID"]}
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
							if (isset(${"CAT_USER_GROUP_ID_".$arGroup["ID"]}) && ${"CAT_USER_GROUP_ID_".$arGroup["ID"]} == "Y")
							{
								$arCatalogFields = array(
									"PRODUCT_ID" => $ID,
									"GROUP_ID" => $arGroup["ID"],
									"ACCESS_LENGTH" => (int)(${"CAT_ACCESS_LENGTH_".$arGroup["ID"]}),
									"ACCESS_LENGTH_TYPE" => ${"CAT_ACCESS_LENGTH_TYPE_".$arGroup["ID"]}
								);
								CCatalogProductGroups::Add($arCatalogFields);
							}
						}
					}
				}

				if (!$bUseStoreControl && $USER->CanDoOperation('catalog_store'))
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
						if (!isset($_POST['AR_AMOUNT'][$row['ID']]))
							continue;
						$amount = trim((string)$_POST['AR_AMOUNT'][$row['ID']]);
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
		}
	}
}