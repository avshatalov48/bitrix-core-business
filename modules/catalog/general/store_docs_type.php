<?php
use Bitrix\Main;
use Bitrix\Catalog;
use Bitrix\Sale;

IncludeModuleLangFile(__DIR__.'\\store_docs.php');

abstract class CCatalogDocsTypes
{
	/** @deprecated  */
	public const TYPE_ARRIVAL = Catalog\StoreDocumentTable::TYPE_ARRIVAL;
	/** @deprecated  */
	public const TYPE_STORE_ADJUSTMENT = Catalog\StoreDocumentTable::TYPE_STORE_ADJUSTMENT;
	/** @deprecated  */
	public const TYPE_MOVING = Catalog\StoreDocumentTable::TYPE_MOVING;
	/** @deprecated  */
	public const TYPE_RETURN = Catalog\StoreDocumentTable::TYPE_RETURN;
	/** @deprecated  */
	public const TYPE_DEDUCT = Catalog\StoreDocumentTable::TYPE_DEDUCT;
	/** @deprecated  */
	public const TYPE_UNDO_RESERVE = Catalog\StoreDocumentTable::TYPE_UNDO_RESERVE;

	protected static $clearAutoCache = array();

	public static function getFields(): array
	{
		return [];
	}

	/** The method of conducting a document, distributes products to warehouses, according to the document type.
	 * @param $documentId
	 * @param $userId
	 * @param $currency
	 * @param $contractorId
	 * @return mixed
	 */
	abstract public static function conductDocument($documentId, $userId, $currency, $contractorId);

	/** Method cancels an instrument and perform the reverse action of conducting a document.
	 * @param $documentId
	 * @param $userId
	 * @return mixed
	 */
	abstract public static function cancellationDocument($documentId, $userId);

	/** The method checks the correctness of the data warehouse. If successful, enrolling \ debits to the storage required amount of product.
	 * @param $arFields
	 * @param $userId
	 * @return array|bool
	 */
	protected static function distributeElementsToStores($arFields, $userId)
	{
		global $APPLICATION;

		$connection = Main\Application::getConnection();

		if (isset($arFields["ELEMENTS"]) && is_array($arFields["ELEMENTS"]))
		{
			$disabledStores = Catalog\StoreTable::getList([
				'select' => ['ID'],
				'filter' => ['=ACTIVE' => 'N'],
			])->fetchAll();
			$disabledStores = array_column($disabledStores, 'ID');

			$basePriceType = CCatalogGroup::GetBaseGroupId();

			foreach ($arFields["ELEMENTS"] as $elementId => $arElements)
			{
				$existPriceRanges = null;
				foreach ($arElements["POSITIONS"] as $arElement)
				{
					if (is_array($arElement))
					{
						if (isset($arElement["STORE_FROM"]))
						{
							if (in_array($arElement["STORE_FROM"], $disabledStores))
							{
								$APPLICATION->ThrowException(Main\Localization\Loc::getMessage('CAT_DOC_STORE_DEACTIVATED', ['#STORE#' => CCatalogStoreControlUtil::getStoreName($arElement["STORE_FROM"])]));
								return false;
							}
							$rsResult = CCatalogStoreProduct::GetList(array(), array("PRODUCT_ID" => $arElement["PRODUCT_ID"], "STORE_ID" => $arElement["STORE_FROM"]), false, false, array('ID', 'AMOUNT'));
							$arID = $rsResult->Fetch();
							if(($arID !== false) || ($arElements["NEGATIVE_AMOUNT_TRACE"] == 'Y'))
							{
								$amountForUpdate = doubleval($arID["AMOUNT"]) - $arElement["AMOUNT"];
								if(($amountForUpdate >= 0) || ($arElements["NEGATIVE_AMOUNT_TRACE"] == 'Y'))
								{
									if(!CCatalogStoreProduct::UpdateFromForm(array("PRODUCT_ID" => $arElement['PRODUCT_ID'], "STORE_ID" => $arElement['STORE_FROM'], "AMOUNT" => $amountForUpdate)))
										return false;
								}
								else
								{
									$storeFromName = CCatalogStoreControlUtil::getStoreName($arElement["STORE_FROM"]);
									$APPLICATION->ThrowException(GetMessage("CAT_DOC_INSUFFICIENTLY_AMOUNT_2", array("#STORE#" => '"'.$storeFromName.'"', "#PRODUCT#" => '"'.$arElements["ELEMENT_NAME"].'"')));
									return false;
								}
							}
							else
							{
								$storeFromName = CCatalogStoreControlUtil::getStoreName($arElement["STORE_FROM"]);
								$APPLICATION->ThrowException(GetMessage("CAT_DOC_INSUFFICIENTLY_AMOUNT_2", array("#STORE#" => $storeFromName, "#PRODUCT#" => $arElements["ELEMENT_NAME"])));
								return false;
							}
						}
						if (isset($arElement["STORE_TO"]))
						{
							if (in_array($arElement["STORE_TO"], $disabledStores))
							{
								$APPLICATION->ThrowException(Main\Localization\Loc::getMessage('CAT_DOC_STORE_DEACTIVATED', ['#STORE#' => CCatalogStoreControlUtil::getStoreName($arElement["STORE_TO"])]));
								return false;
							}
							if(!CCatalogStoreProduct::addToBalanceOfStore($arElement["STORE_TO"], $arElement["PRODUCT_ID"], $arElement["AMOUNT"]))
								return false;
						}

						if (isset($arElements["BARCODES"]) && is_array($arElements["BARCODES"]))
						{
							foreach($arElements["BARCODES"] as $key => $arBarCode)
							{
								$arBarCode['ELEMENT_NAME'] = $arElements['ELEMENT_NAME'];
								if(!self::applyBarCode($arBarCode, $userId))
									return false;
								else
									unset($arElements["BARCODES"][$key]);
							}
						}

						//TODO: replace to group operations
						$iterator = Catalog\Model\Product::getList([
							'select' => ['ID', 'QUANTITY_RESERVED', 'IBLOCK_ID' => 'IBLOCK_ELEMENT.IBLOCK_ID'],
							'filter' => ['=ID' => $arElement["PRODUCT_ID"]],
						]);
						$product = $iterator->fetch();
						unset($iterator);
						if (empty($product))
							return false;
						if ($product['IBLOCK_ID'] === null)
							return false;
						$product['ID'] = (int)$product['ID'];
						$product['IBLOCK_ID'] = (int)$product['IBLOCK_ID'];
						$product['QUANTITY_RESERVED'] = (float)$product['QUANTITY_RESERVED'];

						$query = 'select SUM(CSP.AMOUNT) as PRODUCT_QUANTITY, CSP.PRODUCT_ID '.
							'from b_catalog_store_product CSP inner join b_catalog_store CS on CS.ID = CSP.STORE_ID '.
							'where CSP.PRODUCT_ID = '.$product['ID'].' and CS.ACTIVE = "Y"';
						$iterator = $connection->query($query);
						$row = $iterator->fetch();
						unset($iterator);

						$arFields = [];
						if (isset($arElement["PURCHASING_INFO"]))
							$arFields = $arElement["PURCHASING_INFO"];

						if (!empty($row))
							$arFields['QUANTITY'] = (float)$row['PRODUCT_QUANTITY'] - $product['QUANTITY_RESERVED'];
						else
							$arFields['QUANTITY'] = -$product['QUANTITY_RESERVED'];
						unset($row);

						if ($basePriceType !== null && isset($arElement['BASE_PRICE_INFO']))
						{
							if ($existPriceRanges === null)
							{
								$existPriceRanges = self::isExistPriceRanges($product['ID'], $basePriceType);
							}
							if (!$existPriceRanges)
							{
								$iterator = Catalog\Model\Price::getList([
									'select' => [
										'ID',
										'PRICE',
										'CURRENCY',
									],
									'filter' => [
										'=PRODUCT_ID' => $product['ID'],
										'=CATALOG_GROUP_ID' => $basePriceType,
									],
								]);
								$priceRow = $iterator->fetch();
								unset($iterator);
								if (!empty($priceRow))
								{
									if (
										$priceRow['PRICE'] != $arElement['BASE_PRICE_INFO']['BASE_PRICE']
										|| $priceRow['CURRENCY'] != $arElement['BASE_PRICE_INFO']['BASE_PRICE_CURRENCY']
									)
									{
										Catalog\Model\Price::update(
											(int)$priceRow['ID'],
											[
												'PRICE' => $arElement['BASE_PRICE_INFO']['BASE_PRICE'],
												'CURRENCY' => $arElement['BASE_PRICE_INFO']['BASE_PRICE_CURRENCY'],
											]
										);
									}
								}
								else
								{
									Catalog\Model\Price::add([
										'PRODUCT_ID' => $product['ID'],
										'CATALOG_GROUP_ID' => $basePriceType,
										'PRICE' => $arElement['BASE_PRICE_INFO']['BASE_PRICE'],
										'CURRENCY' => $arElement['BASE_PRICE_INFO']['BASE_PRICE_CURRENCY'],
									]);
								}
							}
						}

						if (!CCatalogProduct::Update($arElement["PRODUCT_ID"], $arFields))
						{
							$APPLICATION->ThrowException(GetMessage("CAT_DOC_PURCHASING_INFO_ERROR"));
							return false;
						}
					}
				}
			}
		}

		unset($connection);

		return true;
	}

	private static function isExistPriceRanges(int $productId, int $basePriceId): bool
	{
		return Catalog\PriceTable::getCount([
			'=PRODUCT_ID' => $productId,
			'=CATALOG_GROUP_ID' => $basePriceId,
			[
				'LOGIC' => 'OR',
				'!=QUANTITY_FROM' => null,
				'!=QUANTITY_TO' => null,
			],
		]) > 0;
	}

	/** The method works with barcodes. If necessary, check the uniqueness of multiple barcodes.
	 * @param $arFields
	 * @param $userId
	 * @return bool|int
	 */
	protected static function applyBarCode($arFields, $userId)
	{
		global $APPLICATION;

		$barCode = $arFields["BARCODE"];
		$elementId = $arFields["PRODUCT_ID"];
		$storeToId = (isset($arFields["STORE_ID"])) ? $arFields["STORE_ID"] : 0;
		$storeFromId = (isset($arFields["STORE_FROM"])) ? $arFields["STORE_FROM"] : 0;
		$newStore = 0;
		$userId = (int)$userId;
		$result = false;
		$rsProps = CCatalogStoreBarCode::GetList(array(), array("BARCODE" => $barCode), false, false, array('ID', 'STORE_ID', 'PRODUCT_ID'));
		if($arBarCode = $rsProps->Fetch())
		{
			if($storeFromId > 0) // deduct or moving
			{
				if($storeToId > 0) // moving
				{
					if($arBarCode["STORE_ID"] == $storeFromId && $arBarCode["PRODUCT_ID"] == $elementId)
						$newStore = $storeToId;
					else
					{
						$storeName = CCatalogStoreControlUtil::getStoreName($storeFromId);
						$APPLICATION->ThrowException(GetMessage("CAT_DOC_WRONG_STORE_BARCODE", array("#STORE#" => '"'.$storeName.'"', "#PRODUCT#" => '"'.$arFields["ELEMENT_NAME"].'"', "#BARCODE#" => '"'.$barCode.'"')));
						return false;
					}
				}
			}
			else
			{
				$APPLICATION->ThrowException(GetMessage("CAT_DOC_BARCODE_ALREADY_EXIST", array("#PRODUCT#" => '"'.$arFields["ELEMENT_NAME"].'"', "#BARCODE#" => '"'.$barCode.'"')));
				return false;
			}
			if($newStore > 0)
				$result = CCatalogStoreBarCode::update($arBarCode["ID"], array("STORE_ID" => $storeToId, "MODIFIED_BY" => $userId));
			else
				$result = CCatalogStoreBarCode::delete($arBarCode["ID"]);
		}
		else
		{
			if($storeFromId > 0)
			{
				$storeName = CCatalogStoreControlUtil::getStoreName($storeFromId);
				$APPLICATION->ThrowException(GetMessage("CAT_DOC_WRONG_STORE_BARCODE", array("#STORE#" => '"'.$storeName.'"', "#PRODUCT#" => '"'.$arFields["ELEMENT_NAME"].'"', "#BARCODE#" => '"'.$barCode.'"')));
				return false;
			}
			elseif($storeToId > 0)
				$result = CCatalogStoreBarCode::Add(array("PRODUCT_ID" => $elementId, "STORE_ID" => $storeToId, "BARCODE" => $barCode, "MODIFIED_BY" => $userId, "CREATED_BY" => $userId));
		}

		return $result;
	}

	/**
	 * @deprecated
	 */
	protected static function checkTotalAmount($elementId, $name = ''): array
	{
		return [];
	}

	protected static function checkAmountField($arDocElement, $name = '')
	{
		global $APPLICATION;
		$name = (string)$name;
		if(doubleval($arDocElement["AMOUNT"]) <= 0)
		{
			if ($name == '')
			{
				$dbProduct = CIBlockElement::GetList(
					array(),
					array("ID" => $arDocElement["ELEMENT_ID"]),
					false,
					false,
					array('ID', 'NAME')
				);
				if ($arProduct = $dbProduct->Fetch())
				{
					$name = $arProduct['NAME'];
				}
			}
			if ($name == '')
				$name = $arDocElement["ELEMENT_ID"];
			$APPLICATION->ThrowException(GetMessage("CAT_DOC_WRONG_AMOUNT", array("#PRODUCT#" => '"'.$name.'"')));
			return false;
		}
		return true;
	}

	protected static function checkParamsForConduction($documentId, $userId, $currency, $contractorId): bool
	{
		return true;
	}
}

class CCatalogArrivalDocs extends CCatalogDocsTypes
{
	/** The method returns an array of fields needed for this type of document.
	 * @return array
	 */
	public static function getFields(): array
	{
		return [
			'ELEMENT_ID' => ['required' => 'Y'],
			'AMOUNT' => ['required' => 'Y'],
			'NET_PRICE' => ['required' => 'Y'],
			'STORE_TO' => ['required' => 'Y'],
			'BAR_CODE' => ['required' => 'Y'],
			'CONTRACTOR' => ['required' => 'Y'],
			'CURRENCY' => ['required' => 'Y'],
			'TOTAL' => ['required' => 'Y'],
		];
	}

	protected static function  checkParamsForConduction($documentId, $userId, $currency, $contractorId): bool
	{
		global $APPLICATION;

		if ($contractorId <= 0)
		{
			$APPLICATION->ThrowException(GetMessage('CAT_DOC_WRONG_CONTRACTOR'));
			return false;
		}

		return true;
	}

	/**
	 * @param $documentId
	 * @param $userId
	 * @param $currency
	 * @param $contractorId
	 * @return array|bool
	 */
	public static function conductDocument($documentId, $userId, $currency, $contractorId)
	{
		global $APPLICATION;

		$documentId = (int)$documentId;
		$userId = (int)$userId;
		$contractorId = (int)$contractorId;
		$arResult = array(
			'ELEMENTS' => array(),
		);
		$arElement = array();
		$arDocElementId = array();
		$sumAmountBarcodes = array();
		$currency = ($currency !== null) ? $currency : '';
		if (!static::checkParamsForConduction($documentId, $userId, $currency, $contractorId))
		{
			return false;
		}
		$dbDocElements = Catalog\StoreDocumentElementTable::getList([
			'select' => ['ID', 'DOC_ID', 'STORE_FROM', 'STORE_TO', 'ELEMENT_ID', 'AMOUNT', 'PURCHASING_PRICE', 'ELEMENT_NAME' => 'ELEMENT.NAME', 'BASE_PRICE'],
			'filter' => ['DOC_ID' => $documentId],
		]);
		while($arDocElement = $dbDocElements->fetch())
		{
			if(!self::checkAmountField($arDocElement, $arDocElement["ELEMENT_NAME"]))
			{
				return false;
			}
			$elementId = (int)$arDocElement["ELEMENT_ID"];

			if (!isset($arElement[$elementId]))
				$arElement[$elementId] = array();
			$arElement[$elementId][$arDocElement["ID"]] = $arDocElement;
			if (!isset($arDocElementId[$elementId]))
				$arDocElementId[$elementId] = array();
			$arDocElementId[$elementId][] = $arDocElement["ID"];
			if (!isset($sumAmountBarcodes[$elementId]))
				$sumAmountBarcodes[$elementId] = 0;
			$sumAmountBarcodes[$elementId] += $arDocElement["AMOUNT"];
			if (!isset($arResult["ELEMENTS"][$elementId]))
			{
				$arResult["ELEMENTS"][$elementId] = array(
					'POSITIONS' => array(),
				);
			}

			$position = [
				"PRODUCT_ID" => $arDocElement["ELEMENT_ID"],
				"STORE_TO" => $arDocElement["STORE_TO"],
				"AMOUNT" => $arDocElement["AMOUNT"],
				"PURCHASING_INFO" => array("PURCHASING_PRICE" => $arDocElement["PURCHASING_PRICE"], "PURCHASING_CURRENCY" => $currency),
			];
			if (isset($arDocElement['BASE_PRICE']))
			{
				$position['BASE_PRICE_INFO'] = [
					'BASE_PRICE' => $arDocElement['BASE_PRICE'],
					'BASE_PRICE_CURRENCY' => $currency,
				];
			}

			if ((int)$position['STORE_TO'] <= 0)
			{
				$row = \Bitrix\Iblock\ElementTable::getList([
					'select' => ['ID', 'NAME'],
					'filter' => ['=ID' => $arDocElement["ELEMENT_ID"]]
				])->fetch();
				$APPLICATION->ThrowException(GetMessage(
					"CAT_DOC_ERROR_STORE_TO",
					["#PRODUCT#" => !empty($row['NAME']) ? $row['NAME'] : $arDocElement["ELEMENT_ID"]]
				));
				return false;
			}

			$arResult["ELEMENTS"][$elementId]["POSITIONS"][] = $position;

		}
		if(empty($arElement))
		{
			$APPLICATION->ThrowException(GetMessage("CAT_DOC_WRONG_ELEMENT_COUNT"));
			return false;
		}

		$dbProductInfo = CCatalogProduct::GetList(
			array(),
			array('ID' => array_keys($arElement)),
			false,
			false,
			array('ID', 'NEGATIVE_AMOUNT_TRACE', 'BARCODE_MULTI', 'QUANTITY', 'QUANTITY_TRACE', 'CAN_BUY_ZERO', 'ELEMENT_NAME', 'ELEMENT_IBLOCK_ID', 'SUBSCRIBE')
		);

		while ($arProductInfo = $dbProductInfo->Fetch())
		{
			$id = $arProductInfo["ID"];

			$arResult["ELEMENTS"][$id]["NEGATIVE_AMOUNT_TRACE"] = $arProductInfo["NEGATIVE_AMOUNT_TRACE"];
			$arResult["ELEMENTS"][$id]["QUANTITY_TRACE"] = $arProductInfo["QUANTITY_TRACE"];
			$arResult["ELEMENTS"][$id]["CAN_BUY_ZERO"] = $arProductInfo["CAN_BUY_ZERO"];
			$arResult["ELEMENTS"][$id]["OLD_QUANTITY"] = $arProductInfo["QUANTITY"];
			$arResult["ELEMENTS"][$id]["IBLOCK_ID"] = $arProductInfo["ELEMENT_IBLOCK_ID"];
			$arResult["ELEMENTS"][$id]["ELEMENT_NAME"] = $arProductInfo["ELEMENT_NAME"];
			$arResult["ELEMENTS"][$id]["SUBSCRIBE"] = $arProductInfo["SUBSCRIBE"];

			if($arProductInfo["BARCODE_MULTI"] == 'Y')
			{
				$arResult["ELEMENTS"][$id]["BARCODES"] = array();
				$dbDocBarcodes = CCatalogStoreDocsBarcode::getList(array(), array("DOC_ELEMENT_ID" => $arDocElementId[$id]));
				while($arDocBarcode = $dbDocBarcodes->Fetch())
				{
					$arResult["ELEMENTS"][$id]["BARCODES"][] = array(
						"PRODUCT_ID" => $id,
						"BARCODE" => $arDocBarcode["BARCODE"],
						"STORE_ID" => $arElement[$id][$arDocBarcode["DOC_ELEMENT_ID"]]["STORE_TO"],
					);
				}
				if($sumAmountBarcodes[$id] != count($arResult["ELEMENTS"][$id]["BARCODES"]))
				{
					$APPLICATION->ThrowException(GetMessage("CAT_DOC_WRONG_COUNT", array("#PRODUCT#" => '"'.$arProductInfo["ELEMENT_NAME"].'"')));
					return false;
				}
			}
		}
		return self::distributeElementsToStores($arResult, $userId);
	}

	/**
	 * @param $documentId
	 * @param $userId
	 * @return array|bool
	 */
	public static function cancellationDocument($documentId, $userId)
	{
		global $APPLICATION;

		$documentId = (int)$documentId;
		$userId = (int)$userId;
		$arResult = array(
			'ELEMENTS' => array(),
		);
		$arElement = array();
		$arDocElementId = array();
		$sumAmountBarcodes = array();
		$dbDocElements = CCatalogStoreDocsElement::getList(
			array(),
			array("DOC_ID" => $documentId),
			false,
			false,
			array("ID", "DOC_ID", "STORE_FROM", "STORE_TO", "ELEMENT_ID", "AMOUNT", "PURCHASING_PRICE", "ELEMENT_NAME")
		);
		while($arDocElement = $dbDocElements->Fetch())
		{
			$elementId = (int)$arDocElement["ELEMENT_ID"];

			if(!self::checkAmountField($arDocElement, $arDocElement["ELEMENT_NAME"]))
			{
				return false;
			}
			if (!isset($arElement[$elementId]))
				$arElement[$elementId] = array();
			$arElement[$elementId][$arDocElement["ID"]] = $arDocElement;
			if (!isset($arDocElementId[$elementId]))
				$arDocElementId[$elementId] = array();
			$arDocElementId[$elementId][] = $arDocElement["ID"];
			if (!isset($sumAmountBarcodes[$elementId]))
				$sumAmountBarcodes[$elementId] = 0;
			$sumAmountBarcodes[$elementId] += $arDocElement["AMOUNT"];
			if (!isset($arResult["ELEMENTS"][$elementId]))
			{
				$arResult["ELEMENTS"][$elementId] = array(
					'POSITIONS' => array(),
				);
			}
			$arResult["ELEMENTS"][$elementId]["POSITIONS"][] = array(
				"PRODUCT_ID" => $arDocElement["ELEMENT_ID"],
				"STORE_FROM" => $arDocElement["STORE_TO"],
				"AMOUNT" => $arDocElement["AMOUNT"],
			);
		}

		if(empty($arElement))
			return false;

		$dbProductInfo = CCatalogProduct::GetList(
			array(),
			array('ID' => array_keys($arElement)),
			false,
			false,
			array('ID', 'NEGATIVE_AMOUNT_TRACE', 'BARCODE_MULTI', 'QUANTITY', 'QUANTITY_TRACE', 'CAN_BUY_ZERO', 'ELEMENT_NAME', 'ELEMENT_IBLOCK_ID', 'SUBSCRIBE')
		);
		while($arProductInfo = $dbProductInfo->Fetch())
		{
			$id = $arProductInfo["ID"];
			$arResult["ELEMENTS"][$id]["NEGATIVE_AMOUNT_TRACE"] = $arProductInfo["NEGATIVE_AMOUNT_TRACE"];
			$arResult["ELEMENTS"][$id]["QUANTITY_TRACE"] = $arProductInfo["QUANTITY_TRACE"];
			$arResult["ELEMENTS"][$id]["CAN_BUY_ZERO"] = $arProductInfo["CAN_BUY_ZERO"];
			$arResult["ELEMENTS"][$id]["OLD_QUANTITY"] = $arProductInfo["QUANTITY"];
			$arResult["ELEMENTS"][$id]["IBLOCK_ID"] = $arProductInfo["ELEMENT_IBLOCK_ID"];
			$arResult["ELEMENTS"][$id]["ELEMENT_NAME"] = $arProductInfo["ELEMENT_NAME"];
			$arResult["ELEMENTS"][$id]["SUBSCRIBE"] = $arProductInfo["SUBSCRIBE"];

			if ($arProductInfo["BARCODE_MULTI"] == 'Y')
			{
				$arResult["ELEMENTS"][$id]["BARCODES"] = array();
				$dbDocBarcodes = CCatalogStoreDocsBarcode::getList(array(), array("DOC_ELEMENT_ID" => $arDocElementId[$id]));
				while($arDocBarcode = $dbDocBarcodes->Fetch())
				{
					$arResult["ELEMENTS"][$id]["BARCODES"][] = array(
						"PRODUCT_ID" => $id,
						"BARCODE" => $arDocBarcode["BARCODE"],
						"STORE_FROM" => $arElement[$id][$arDocBarcode["DOC_ELEMENT_ID"]]["STORE_TO"],
					);
				}
				if($sumAmountBarcodes[$id] != count($arResult["ELEMENTS"][$id]["BARCODES"]))
				{
					$APPLICATION->ThrowException(GetMessage("CAT_DOC_WRONG_COUNT", array("#PRODUCT#" => '"'.$arProductInfo["ELEMENT_NAME"].'"')));
					return false;
				}
			}
		}
		return self::distributeElementsToStores($arResult, $userId);
	}

}

class CCatalogStoreAdjustmentDocs extends CCatalogArrivalDocs
{
	/** The method returns an array of fields needed for this type of document.
	 * @return array
	 */
	public static function getFields(): array
	{
		return [
			'ELEMENT_ID' => ['required' => 'Y'],
			'AMOUNT' => ['required' => 'Y'],
			'NET_PRICE' => ['required' => 'Y'],
			'STORE_TO' => ['required' => 'Y'],
			'BAR_CODE' => ['required' => 'Y'],
			'CURRENCY' => ['required' => 'Y'],
			'TOTAL' => ['required' => 'Y'],
		];
	}

	protected static function checkParamsForConduction($documentId, $userId, $currency, $contractorId): bool
	{
		return true;
	}
}

class CCatalogMovingDocs extends CCatalogDocsTypes
{
	/** The method returns an array of fields needed for this type of document.
	 * @return array
	 */
	public static function getFields(): array
	{
		return [
			'ELEMENT_ID' => ['required' => 'Y'],
			'AMOUNT' => ['required' => 'Y'],
			'STORE_TO' => ['required' => 'Y'],
			'BAR_CODE' => ['required' => 'Y'],
			'STORE_FROM' => ['required' => 'Y'],
		];
	}

	/**
	 * @param $documentId
	 * @param $userId
	 * @param $currency
	 * @param $contractorId
	 * @return array|bool
	 */
	public static function conductDocument($documentId, $userId, $currency, $contractorId)
	{
		global $APPLICATION;

		$documentId = (int)$documentId;
		$userId = (int)$userId;
		$arResult = array(
			'ELEMENTS' => array(),
		);
		$arElement = array();
		$arDocElementId = array();
		$sumAmountBarcodes = array();
		if (!static::checkParamsForConduction($documentId, $userId, $currency, $contractorId))
		{
			return false;
		}
		$dbDocElements = CCatalogStoreDocsElement::getList(
			array(),
			array("DOC_ID" => $documentId),
			false,
			false,
			array("ID", "DOC_ID", "STORE_FROM", "STORE_TO", "ELEMENT_ID", "AMOUNT", "PURCHASING_PRICE", "ELEMENT_NAME")
		);
		while($arDocElement = $dbDocElements->Fetch())
		{
			$elementId = (int)$arDocElement["ELEMENT_ID"];

			if(!self::checkAmountField($arDocElement, $arDocElement["ELEMENT_NAME"]))
			{
				return false;
			}
			if (!isset($arElement[$elementId]))
				$arElement[$elementId] = array();
			$arElement[$elementId][$arDocElement["ID"]] = $arDocElement;
			if (!isset($arDocElementId[$elementId]))
				$arDocElementId[$elementId] = array();
			$arDocElementId[$elementId][] = $arDocElement["ID"];
			if (!isset($sumAmountBarcodes[$elementId]))
				$sumAmountBarcodes[$elementId] = 0;
			$sumAmountBarcodes[$elementId] += $arDocElement["AMOUNT"];
			if (!isset($arResult["ELEMENTS"][$elementId]))
			{
				$arResult["ELEMENTS"][$elementId] = array(
					'POSITIONS' => array(),
				);
			}
			$arResult["ELEMENTS"][$elementId]["POSITIONS"][] = array(
				"PRODUCT_ID" => $arDocElement["ELEMENT_ID"],
				"STORE_TO" => $arDocElement["STORE_TO"],
				"AMOUNT" => $arDocElement["AMOUNT"],
				"STORE_FROM" => $arDocElement["STORE_FROM"],
			);
		}

		if (empty($arElement))
			return false;

		$dbProductInfo = CCatalogProduct::GetList(
			array(),
			array('ID' => array_keys($arElement)),
			false,
			false,
			array('ID', 'NEGATIVE_AMOUNT_TRACE', 'BARCODE_MULTI', 'ELEMENT_NAME')
		);

		while($arProductInfo = $dbProductInfo->Fetch())
		{
			$id = $arProductInfo["ID"];

			$arResult["ELEMENTS"][$id]["NEGATIVE_AMOUNT_TRACE"] = $arProductInfo["NEGATIVE_AMOUNT_TRACE"];
			$arResult["ELEMENTS"][$id]["ELEMENT_NAME"] = $arProductInfo["ELEMENT_NAME"];

			if($arProductInfo["BARCODE_MULTI"] == 'Y')
			{
				$arResult["ELEMENTS"][$id]["BARCODES"] = array();
				$dbDocBarcodes = CCatalogStoreDocsBarcode::getList(array(), array("DOC_ELEMENT_ID" => $arDocElementId[$id]));
				while($arDocBarcode = $dbDocBarcodes->Fetch())
				{
					$arResult["ELEMENTS"][$id]["BARCODES"][] = array(
						"PRODUCT_ID" => $id,
						"BARCODE" => $arDocBarcode["BARCODE"],
						"STORE_ID" => $arElement[$id][$arDocBarcode["DOC_ELEMENT_ID"]]["STORE_TO"],
						"STORE_FROM" => $arElement[$id][$arDocBarcode["DOC_ELEMENT_ID"]]["STORE_FROM"],
					);
				}

				if($sumAmountBarcodes[$id] != count($arResult["ELEMENTS"][$id]["BARCODES"]))
				{
					$APPLICATION->ThrowException(GetMessage("CAT_DOC_WRONG_COUNT", array("#PRODUCT#" => '"'.$arProductInfo["ELEMENT_NAME"].'"')));
					return false;
				}
			}
		}
		return self::distributeElementsToStores($arResult, $userId);
	}

	/**
	 * @param $documentId
	 * @param $userId
	 * @return array|bool
	 */
	public static function cancellationDocument($documentId, $userId)
	{
		global $APPLICATION;

		$documentId = (int)$documentId;
		$userId = (int)$userId;
		$arResult = array(
			'ELEMENTS' => array(),
		);
		$arElement = array();
		$arDocElementId = array();
		$sumAmountBarcodes = array();
		$dbDocElements = CCatalogStoreDocsElement::getList(
			array(),
			array("DOC_ID" => $documentId),
			false,
			false,
			array("ID", "DOC_ID", "STORE_FROM", "STORE_TO", "ELEMENT_ID", "AMOUNT", "PURCHASING_PRICE", "ELEMENT_NAME")
		);
		while($arDocElement = $dbDocElements->Fetch())
		{
			$elementId = (int)$arDocElement["ELEMENT_ID"];

			if(!self::checkAmountField($arDocElement, $arDocElement['ELEMENT_NAME']))
			{
				return false;
			}
			if (!isset($arElement[$elementId]))
				$arElement[$elementId] = array();
			$arElement[$elementId][$arDocElement["ID"]] = $arDocElement;
			if (!isset($arDocElementId[$elementId]))
				$arDocElementId[$elementId] = array();
			$arDocElementId[$elementId][] = $arDocElement["ID"];
			if (!isset($sumAmountBarcodes[$elementId]))
				$sumAmountBarcodes[$elementId] = 0;
			$sumAmountBarcodes[$elementId] += $arDocElement["AMOUNT"];
			if (!isset($arResult["ELEMENTS"][$elementId]))
			{
				$arResult["ELEMENTS"][$elementId] = array(
					'POSITIONS' => array(),
				);
			}
			$arResult["ELEMENTS"][$elementId]["POSITIONS"][] = array(
				"PRODUCT_ID" => $arDocElement["ELEMENT_ID"],
				"STORE_FROM" => $arDocElement["STORE_TO"],
				"AMOUNT" => $arDocElement["AMOUNT"],
				"STORE_TO" => $arDocElement["STORE_FROM"],
			);
		}

		if(empty($arElement))
			return false;

		$dbProductInfo = CCatalogProduct::GetList(
			array(),
			array('ID' => array_keys($arElement)),
			false,
			false,
			array('ID', 'NEGATIVE_AMOUNT_TRACE', 'BARCODE_MULTI', 'ELEMENT_NAME')
		);
		while($arProductInfo = $dbProductInfo->Fetch())
		{
			$id = $arProductInfo["ID"];
			$arResult["ELEMENTS"][$id]["NEGATIVE_AMOUNT_TRACE"] = $arProductInfo["NEGATIVE_AMOUNT_TRACE"];
			$arResult["ELEMENTS"][$id]["ELEMENT_NAME"] = $arProductInfo["ELEMENT_NAME"];

			if($arProductInfo["BARCODE_MULTI"] == 'Y')
			{
				$arResult["ELEMENTS"][$id]["BARCODES"] = array();
				$dbDocBarcodes = CCatalogStoreDocsBarcode::getList(array(), array("DOC_ELEMENT_ID" => $arDocElementId[$id]));
				while($arDocBarcode = $dbDocBarcodes->Fetch())
				{
					$arResult["ELEMENTS"][$id]["BARCODES"][] = array(
						"PRODUCT_ID" => $id,
						"BARCODE" => $arDocBarcode["BARCODE"],
						"STORE_FROM" => $arElement[$id][$arDocBarcode["DOC_ELEMENT_ID"]]["STORE_TO"],
						"STORE_ID" => $arElement[$id][$arDocBarcode["DOC_ELEMENT_ID"]]["STORE_FROM"],
					);
				}
				if($sumAmountBarcodes[$id] != count($arResult["ELEMENTS"][$id]["BARCODES"]))
				{

					$APPLICATION->ThrowException(GetMessage("CAT_DOC_WRONG_COUNT", array("#PRODUCT#" => '"'.$arProductInfo["ELEMENT_NAME"].'"')));
					return false;
				}
			}
		}
		return self::distributeElementsToStores($arResult, $userId);
	}

}

class CCatalogReturnsDocs extends CCatalogDocsTypes
{
	/** The method returns an array of fields needed for this type of document.
	 * @return array
	 */
	public static function getFields(): array
	{
		return [
			'ELEMENT_ID' => ['required' => 'Y'],
			'AMOUNT' => ['required' => 'Y'],
			'STORE_TO' => ['required' => 'Y'],
			'BAR_CODE' => ['required' => 'Y'],
		];
	}

	/**
	 * @param $documentId
	 * @param $userId
	 * @param $currency
	 * @param $contractorId
	 * @return array|bool
	 */
	public static function conductDocument($documentId, $userId, $currency, $contractorId)
	{
		global $APPLICATION;

		$documentId = (int)$documentId;
		$userId = (int)$userId;
		$arResult = array(
			'ELEMENTS' => array(),
		);
		$arElement = array();
		$arDocElementId = array();
		$sumAmountBarcodes = array();
		if (!static::checkParamsForConduction($documentId, $userId, $currency, $contractorId))
		{
			return false;
		}
		$dbDocElements = CCatalogStoreDocsElement::getList(
			array(),
			array("DOC_ID" => $documentId),
			false,
			false,
			array("ID", "DOC_ID", "STORE_FROM", "STORE_TO", "ELEMENT_ID", "AMOUNT", "PURCHASING_PRICE", "ELEMENT_NAME")
		);
		while($arDocElement = $dbDocElements->Fetch())
		{
			$elementId = (int)$arDocElement["ELEMENT_ID"];

			if(!self::checkAmountField($arDocElement, $arDocElement["ELEMENT_NAME"]))
			{
				return false;
			}
			if (!isset($arElement[$elementId]))
				$arElement[$elementId] = array();
			$arElement[$elementId][$arDocElement["ID"]] = $arDocElement;
			if (!isset($arDocElementId[$elementId]))
				$arDocElementId[$elementId] = array();
			$arDocElementId[$elementId][] = $arDocElement["ID"];
			if (!isset($sumAmountBarcodes[$elementId]))
				$sumAmountBarcodes[$elementId] = 0;
			$sumAmountBarcodes[$elementId] += $arDocElement["AMOUNT"];
			if (!isset($arResult["ELEMENTS"][$elementId]))
			{
				$arResult["ELEMENTS"][$elementId] = array(
					'POSITIONS' => array(),
				);
			}
			$arResult["ELEMENTS"][$elementId]["POSITIONS"][] = array(
				"PRODUCT_ID" => $arDocElement["ELEMENT_ID"],
				"AMOUNT" => $arDocElement["AMOUNT"],
				"STORE_TO" => $arDocElement["STORE_TO"],
			);
		}

		if(empty($arElement))
			return false;

		$dbProductInfo = CCatalogProduct::GetList(
			array(),
			array('ID' => array_keys($arElement)),
			false,
			false,
			array('ID', 'NEGATIVE_AMOUNT_TRACE', 'BARCODE_MULTI', 'QUANTITY', 'QUANTITY_TRACE', 'CAN_BUY_ZERO', 'ELEMENT_NAME', 'ELEMENT_IBLOCK_ID')
		);

		while($arProductInfo = $dbProductInfo->Fetch())
		{
			$id = $arProductInfo["ID"];

			$arResult["ELEMENTS"][$id]["NEGATIVE_AMOUNT_TRACE"] = $arProductInfo["NEGATIVE_AMOUNT_TRACE"];
			$arResult["ELEMENTS"][$id]["QUANTITY_TRACE"] = $arProductInfo["QUANTITY_TRACE"];
			$arResult["ELEMENTS"][$id]["CAN_BUY_ZERO"] = $arProductInfo["CAN_BUY_ZERO"];
			$arResult["ELEMENTS"][$id]["OLD_QUANTITY"] = $arProductInfo["QUANTITY"];
			$arResult["ELEMENTS"][$id]["IBLOCK_ID"] = $arProductInfo["ELEMENT_IBLOCK_ID"];
			$arResult["ELEMENTS"][$id]["ELEMENT_NAME"] = $arProductInfo["ELEMENT_NAME"];

			if($arProductInfo["BARCODE_MULTI"] == 'Y')
			{
				$arResult["ELEMENTS"][$id]["BARCODES"] = array();
				$dbDocBarcodes = CCatalogStoreDocsBarcode::getList(array(), array("DOC_ELEMENT_ID" => $arDocElementId[$id]));
				while($arDocBarcode = $dbDocBarcodes->Fetch())
				{
					$arResult["ELEMENTS"][$id]["BARCODES"][] = array(
						"PRODUCT_ID" => $id,
						"BARCODE" => $arDocBarcode["BARCODE"],
						"STORE_ID" => $arElement[$id][$arDocBarcode["DOC_ELEMENT_ID"]]["STORE_TO"],
					);
				}

				if($sumAmountBarcodes[$id] != count($arResult["ELEMENTS"][$id]["BARCODES"]))
				{
					$APPLICATION->ThrowException(GetMessage("CAT_DOC_WRONG_COUNT", array("#PRODUCT#" => '"'.$arProductInfo["ELEMENT_NAME"].'"')));
					return false;
				}
			}
		}
		return self::distributeElementsToStores($arResult, $userId);
	}

	/**
	 * @param $documentId
	 * @param $userId
	 * @return array|bool
	 */
	public static function cancellationDocument($documentId, $userId)
	{
		global $APPLICATION;

		$documentId = (int)$documentId;
		$userId = (int)$userId;
		$arResult = array(
			'ELEMENTS' => array(),
		);
		$arElement = array();
		$arDocElementId = array();
		$sumAmountBarcodes = array();
		$dbDocElements = CCatalogStoreDocsElement::getList(
			array(),
			array("DOC_ID" => $documentId),
			false,
			false,
			array("ID", "DOC_ID", "STORE_FROM", "STORE_TO", "ELEMENT_ID", "AMOUNT", "PURCHASING_PRICE", "ELEMENT_NAME")
		);
		while($arDocElement = $dbDocElements->Fetch())
		{
			$elementId = (int)$arDocElement["ELEMENT_ID"];

			if(!self::checkAmountField($arDocElement, $arDocElement["ELEMENT_NAME"]))
			{
				return false;
			}
			if (!isset($arElement[$elementId]))
				$arElement[$elementId] = array();
			$arElement[$elementId][$arDocElement["ID"]] = $arDocElement;
			if (!isset($arDocElementId[$elementId]))
				$arDocElementId[$elementId] = array();
			$arDocElementId[$elementId][] = $arDocElement["ID"];
			if (!isset($sumAmountBarcodes[$elementId]))
				$sumAmountBarcodes[$elementId] = 0;
			$sumAmountBarcodes[$elementId] += $arDocElement["AMOUNT"];
			if (!isset($arResult["ELEMENTS"][$elementId]))
			{
				$arResult["ELEMENTS"][$elementId] = array(
					'POSITIONS' => array(),
				);
			}
			$arResult["ELEMENTS"][$elementId]["POSITIONS"][] = array(
				"PRODUCT_ID" => $arDocElement["ELEMENT_ID"],
				"AMOUNT" => $arDocElement["AMOUNT"],
				"STORE_FROM" => $arDocElement["STORE_TO"],
			);
		}

		if(empty($arElement))
			return false;

		$dbProductInfo = CCatalogProduct::GetList(
			array(),
			array('ID' => array_keys($arElement)),
			false,
			false,
			array('ID', 'NEGATIVE_AMOUNT_TRACE', 'BARCODE_MULTI', 'QUANTITY', 'QUANTITY_TRACE', 'CAN_BUY_ZERO', 'ELEMENT_NAME', 'ELEMENT_IBLOCK_ID')
		);
		while($arProductInfo = $dbProductInfo->Fetch())
		{
			$id = $arProductInfo["ID"];
			$arResult["ELEMENTS"][$id]["NEGATIVE_AMOUNT_TRACE"] = $arProductInfo["NEGATIVE_AMOUNT_TRACE"];
			$arResult["ELEMENTS"][$id]["QUANTITY_TRACE"] = $arProductInfo["QUANTITY_TRACE"];
			$arResult["ELEMENTS"][$id]["CAN_BUY_ZERO"] = $arProductInfo["CAN_BUY_ZERO"];
			$arResult["ELEMENTS"][$id]["OLD_QUANTITY"] = $arProductInfo["QUANTITY"];
			$arResult["ELEMENTS"][$id]["IBLOCK_ID"] = $arProductInfo["ELEMENT_IBLOCK_ID"];
			$arResult["ELEMENTS"][$id]["ELEMENT_NAME"] = $arProductInfo["ELEMENT_NAME"];

			if($arProductInfo["BARCODE_MULTI"] == 'Y')
			{
				$arResult["ELEMENTS"][$id]["BARCODES"] = array();
				$dbDocBarcodes = CCatalogStoreDocsBarcode::getList(array(), array("DOC_ELEMENT_ID" => $arDocElementId[$id]));
				while($arDocBarcode = $dbDocBarcodes->Fetch())
				{
					$arResult["ELEMENTS"][$id]["BARCODES"][] = array(
						"PRODUCT_ID" => $id,
						"BARCODE" => $arDocBarcode["BARCODE"],
						"STORE_FROM" => $arElement[$id][$arDocBarcode["DOC_ELEMENT_ID"]]["STORE_TO"],
					);
				}
				if($sumAmountBarcodes[$id] != count($arResult["ELEMENTS"][$id]["BARCODES"]))
				{
					$APPLICATION->ThrowException(GetMessage("CAT_DOC_WRONG_COUNT", array("#PRODUCT#" => '"'.$arProductInfo["ELEMENT_NAME"].'"')));
					return false;
				}
			}
		}
		return self::distributeElementsToStores($arResult, $userId);
	}

}

class CCatalogDeductDocs extends CCatalogDocsTypes
{
	/** The method returns an array of fields needed for this type of document.
	 * @return array
	 */
	public static function getFields(): array
	{
		return [
			'ELEMENT_ID' => ['required' => 'Y'],
			'AMOUNT' => ['required' => 'Y'],
			'BAR_CODE' => ['required' => 'Y'],
			'STORE_FROM' => ['required' => 'Y'],
		];
	}

	/**
	 * @param $documentId
	 * @param $userId
	 * @param $currency
	 * @param $contractorId
	 * @return array|bool
	 */
	public static function conductDocument($documentId, $userId, $currency, $contractorId)
	{
		global $APPLICATION;

		$documentId = (int)$documentId;
		$userId = (int)$userId;
		$arResult = array(
			'ELEMENTS' => array(),
		);
		$arElement = array();
		$arDocElementId = array();
		$sumAmountBarcodes = array();
		if (!static::checkParamsForConduction($documentId, $userId, $currency, $contractorId))
		{
			return false;
		}
		$dbDocElements = CCatalogStoreDocsElement::getList(
			array(),
			array("DOC_ID" => $documentId),
			false,
			false,
			array("ID", "DOC_ID", "STORE_FROM", "STORE_TO", "ELEMENT_ID", "AMOUNT", "PURCHASING_PRICE", "ELEMENT_NAME")
		);
		while($arDocElement = $dbDocElements->Fetch())
		{
			$elementId = (int)$arDocElement["ELEMENT_ID"];

			if(!self::checkAmountField($arDocElement, $arDocElement["ELEMENT_NAME"]))
			{
				return false;
			}
			if (!isset($arElement[$elementId]))
				$arElement[$elementId] = array();
			$arElement[$elementId][$arDocElement["ID"]] = $arDocElement;
			if (!isset($arDocElementId[$elementId]))
				$arDocElementId[$elementId] = array();
			$arDocElementId[$elementId][] = $arDocElement["ID"];
			if (!isset($sumAmountBarcodes[$elementId]))
				$sumAmountBarcodes[$elementId] = 0;
			$sumAmountBarcodes[$elementId] += $arDocElement["AMOUNT"];
			if (!isset($arResult["ELEMENTS"][$elementId]))
			{
				$arResult["ELEMENTS"][$elementId] = array(
					'POSITIONS' => array(),
				);
			}
			$arResult["ELEMENTS"][$elementId]["POSITIONS"][] = array(
				"PRODUCT_ID" => $arDocElement["ELEMENT_ID"],
				"AMOUNT" => $arDocElement["AMOUNT"],
				"STORE_FROM" => $arDocElement["STORE_FROM"],
			);
		}

		if(empty($arElement))
			return false;

		$dbProductInfo = CCatalogProduct::GetList(
			array(),
			array('ID' => array_keys($arElement)),
			false,
			false,
			array('ID', 'NEGATIVE_AMOUNT_TRACE', 'BARCODE_MULTI', 'QUANTITY', 'QUANTITY_TRACE', 'CAN_BUY_ZERO', 'ELEMENT_NAME', 'ELEMENT_IBLOCK_ID', 'SUBSCRIBE')
		);

		while($arProductInfo = $dbProductInfo->Fetch())
		{
			$id = $arProductInfo["ID"];

			$arResult["ELEMENTS"][$id]["NEGATIVE_AMOUNT_TRACE"] = $arProductInfo["NEGATIVE_AMOUNT_TRACE"];
			$arResult["ELEMENTS"][$id]["QUANTITY_TRACE"] = $arProductInfo["QUANTITY_TRACE"];
			$arResult["ELEMENTS"][$id]["CAN_BUY_ZERO"] = $arProductInfo["CAN_BUY_ZERO"];
			$arResult["ELEMENTS"][$id]["OLD_QUANTITY"] = $arProductInfo["QUANTITY"];
			$arResult["ELEMENTS"][$id]["IBLOCK_ID"] = $arProductInfo["ELEMENT_IBLOCK_ID"];
			$arResult["ELEMENTS"][$id]["ELEMENT_NAME"] = $arProductInfo["ELEMENT_NAME"];
			$arResult["ELEMENTS"][$id]["SUBSCRIBE"] = $arProductInfo["SUBSCRIBE"];

			if($arProductInfo["BARCODE_MULTI"] == 'Y')
			{
				$arResult["ELEMENTS"][$id]["BARCODES"] = array();
				$dbDocBarcodes = CCatalogStoreDocsBarcode::getList(array(), array("DOC_ELEMENT_ID" => $arDocElementId[$id]));
				while($arDocBarcode = $dbDocBarcodes->Fetch())
				{
					$arResult["ELEMENTS"][$id]["BARCODES"][] = array(
						"PRODUCT_ID" => $id,
						"BARCODE" => $arDocBarcode["BARCODE"],
						"STORE_FROM" => $arElement[$id][$arDocBarcode["DOC_ELEMENT_ID"]]["STORE_FROM"],
					);
				}

				if($sumAmountBarcodes[$id] != count($arResult["ELEMENTS"][$id]["BARCODES"]))
				{
					$APPLICATION->ThrowException(GetMessage("CAT_DOC_WRONG_COUNT", array("#PRODUCT#" => '"'.$arProductInfo["ELEMENT_NAME"].'"')));
					return false;
				}
			}
		}
		return self::distributeElementsToStores($arResult, $userId);
	}

	/**
	 * @param $documentId
	 * @param $userId
	 * @return array|bool
	 */
	public static function cancellationDocument($documentId, $userId)
	{
		global $APPLICATION;

		$documentId = (int)$documentId;
		$userId = (int)$userId;
		$arResult = array(
			'ELEMENTS' => array(),
		);
		$arElement = array();
		$arDocElementId = array();
		$sumAmountBarcodes = array();
		$dbDocElements = CCatalogStoreDocsElement::getList(
			array(),
			array("DOC_ID" => $documentId),
			false,
			false,
			array("ID", "DOC_ID", "STORE_FROM", "STORE_TO", "ELEMENT_ID", "AMOUNT", "PURCHASING_PRICE", "ELEMENT_NAME")
		);
		while($arDocElement = $dbDocElements->Fetch())
		{
			$elementId = (int)$arDocElement["ELEMENT_ID"];

			if(!self::checkAmountField($arDocElement, $arDocElement["ELEMENT_NAME"]))
			{
				return false;
			}
			if (!isset($arElement[$elementId]))
				$arElement[$elementId] = array();
			$arElement[$elementId][$arDocElement["ID"]] = $arDocElement;
			if (!isset($arDocElementId[$elementId]))
				$arDocElementId[$elementId] = array();
			$arDocElementId[$elementId][] = $arDocElement["ID"];
			if (!isset($sumAmountBarcodes[$elementId]))
				$sumAmountBarcodes[$elementId] = 0;
			$sumAmountBarcodes[$elementId] += $arDocElement["AMOUNT"];
			if (!isset($arResult["ELEMENTS"][$elementId]))
			{
				$arResult["ELEMENTS"][$elementId] = array(
					'POSITIONS' => array(),
				);
			}
			$arResult["ELEMENTS"][$elementId]["POSITIONS"][] = array(
				"PRODUCT_ID" => $arDocElement["ELEMENT_ID"],
				"AMOUNT" => $arDocElement["AMOUNT"],
				"STORE_TO" => $arDocElement["STORE_FROM"],
			);
		}

		if(empty($arElement))
			return false;

		$dbProductInfo = CCatalogProduct::GetList(
			array(),
			array('ID' => array_keys($arElement)),
			false,
			false,
			array('ID', 'NEGATIVE_AMOUNT_TRACE', 'BARCODE_MULTI', 'QUANTITY', 'QUANTITY_TRACE', 'CAN_BUY_ZERO', 'ELEMENT_NAME', 'ELEMENT_IBLOCK_ID', 'SUBSCRIBE')
		);
		while($arProductInfo = $dbProductInfo->Fetch())
		{
			$id = $arProductInfo["ID"];
			$arResult["ELEMENTS"][$id]["NEGATIVE_AMOUNT_TRACE"] = $arProductInfo["NEGATIVE_AMOUNT_TRACE"];
			$arResult["ELEMENTS"][$id]["QUANTITY_TRACE"] = $arProductInfo["QUANTITY_TRACE"];
			$arResult["ELEMENTS"][$id]["CAN_BUY_ZERO"] = $arProductInfo["CAN_BUY_ZERO"];
			$arResult["ELEMENTS"][$id]["OLD_QUANTITY"] = $arProductInfo["QUANTITY"];
			$arResult["ELEMENTS"][$id]["IBLOCK_ID"] = $arProductInfo["ELEMENT_IBLOCK_ID"];
			$arResult["ELEMENTS"][$id]["ELEMENT_NAME"] = $arProductInfo["ELEMENT_NAME"];
			$arResult["ELEMENTS"][$id]["SUBSCRIBE"] = $arProductInfo["SUBSCRIBE"];

			if($arProductInfo["BARCODE_MULTI"] == 'Y')
			{
				$arResult["ELEMENTS"][$id]["BARCODES"] = array();
				$dbDocBarcodes = CCatalogStoreDocsBarcode::getList(array(), array("DOC_ELEMENT_ID" => $arDocElementId[$id]));
				while($arDocBarcode = $dbDocBarcodes->Fetch())
				{
					$arResult["ELEMENTS"][$id]["BARCODES"][] = array(
						"PRODUCT_ID" => $id,
						"BARCODE" => $arDocBarcode["BARCODE"],
						"STORE_ID" => $arElement[$id][$arDocBarcode["DOC_ELEMENT_ID"]]["STORE_FROM"],
					);
				}
				if($sumAmountBarcodes[$id] != count($arResult["ELEMENTS"][$id]["BARCODES"]))
				{
					$APPLICATION->ThrowException(GetMessage("CAT_DOC_WRONG_COUNT", array("#PRODUCT#" => '"'.$arProductInfo["ELEMENT_NAME"].'"')));
					return false;
				}
			}
		}
		return self::distributeElementsToStores($arResult, $userId);
	}

}

class CCatalogUnReservedDocs extends CCatalogDocsTypes
{
	/** The method returns an array of fields needed for this type of document.
	 * @return array
	 */
	public static function getFields(): array
	{
		return [
			'ELEMENT_ID' => ['required' => 'Y'],
			'AMOUNT' => ['required' => 'Y'],
			'RESERVED' => ['required' => 'Y'],
		];
	}

	/**
	 * @param $documentId
	 * @param $userId
	 * @param $currency
	 * @param $contractorId
	 * @return bool
	 */
	public static function conductDocument($documentId, $userId, $currency, $contractorId)
	{
		global $DB, $APPLICATION;

		$documentId = (int)$documentId;
		$i = 0;
		if (!static::checkParamsForConduction($documentId, $userId, $currency, $contractorId))
		{
			return false;
		}
		$dbDocElements = CCatalogStoreDocsElement::getList(
			array(),
			array("DOC_ID" => $documentId),
			false,
			false,
			array("ID", "DOC_ID", "STORE_FROM", "STORE_TO", "ELEMENT_ID", "AMOUNT", "PURCHASING_PRICE", "ELEMENT_NAME")
		);
		while($arDocElement = $dbDocElements->Fetch())
		{
			if (!self::checkAmountField($arDocElement, $arDocElement["ELEMENT_NAME"]))
			{
				return false;
			}

			$arProductInfo = CCatalogProduct::GetByID($arDocElement["ELEMENT_ID"]);
			$newReserved = $arProductInfo["QUANTITY_RESERVED"] - $arDocElement["AMOUNT"];
			if($newReserved >= 0)
			{
				if(!CCatalogProduct::Update($arDocElement["ELEMENT_ID"], array("QUANTITY_RESERVED" => $newReserved)))
					return false;
				$dbAmount = $DB->Query("SELECT SUM(SP.AMOUNT) as SUM, CP.QUANTITY_RESERVED as RESERVED FROM b_catalog_store_product SP INNER JOIN b_catalog_product CP ON SP.PRODUCT_ID = CP.ID INNER JOIN b_catalog_store CS ON SP.STORE_ID = CS.ID WHERE SP.PRODUCT_ID = ".$arDocElement["ELEMENT_ID"]."  AND CS.ACTIVE = 'Y' GROUP BY CP.QUANTITY_RESERVED ", true);
				if($arAmount = $dbAmount->Fetch())
				{
					$arFields = array(
						"QUANTITY" => doubleval($arAmount["SUM"] - $arAmount["RESERVED"]),
					);
					if(!CCatalogProduct::Update($arDocElement["ELEMENT_ID"], $arFields))
					{
						$APPLICATION->ThrowException(GetMessage("CAT_DOC_PURCHASING_INFO_ERROR"));
						return false;
					}
				}
			}
			else
			{
				$name = '';
				$dbProduct = CIBlockElement::GetList(
					array(),
					array("ID" => $arDocElement["ELEMENT_ID"]),
					false,
					false,
					array('ID', 'NAME')
				);
				if ($arProduct = $dbProduct->Fetch())
				{
					$name = $arProduct['NAME'];
				}
				if ($name == '')
					$name = $arDocElement["ELEMENT_ID"];
				$APPLICATION->ThrowException(GetMessage("CAT_DOC_WRONG_RESERVED_AMOUNT", array("#PRODUCT#" => '"'.$name.'"')));
				return false;
			}
			$i++;
		}
		return ($i > 0);
	}

	/**
	 * @param $documentId
	 * @param $userId
	 * @return bool
	 */
	public static function cancellationDocument($documentId, $userId)
	{
		global $DB, $APPLICATION;

		$documentId = (int)$documentId;
		$i = 0;
		$dbDocElements = CCatalogStoreDocsElement::getList(array(), array("DOC_ID" => $documentId));
		while($arDocElement = $dbDocElements->Fetch())
		{
			$arResult = array();
			$arProductInfo = CCatalogProduct::GetByID($arDocElement["ELEMENT_ID"]);
			$newReserved = $arProductInfo["QUANTITY_RESERVED"] + $arDocElement["AMOUNT"];
			$arResult["QUANTITY_RESERVED"] = $newReserved;

			$dbAmount = $DB->Query("SELECT SUM(SP.AMOUNT) as SUM, CP.QUANTITY_RESERVED as RESERVED FROM b_catalog_store_product SP INNER JOIN b_catalog_product CP ON SP.PRODUCT_ID = CP.ID INNER JOIN b_catalog_store CS ON SP.STORE_ID = CS.ID WHERE SP.PRODUCT_ID = ".$arDocElement["ELEMENT_ID"]."  AND CS.ACTIVE = 'Y' GROUP BY CP.QUANTITY_RESERVED ", true);
			if($arAmount = $dbAmount->Fetch())
			{
				$arResult["QUANTITY"] = doubleval($arAmount["SUM"] - $newReserved);
				if(!CCatalogProduct::Update($arDocElement["ELEMENT_ID"], $arResult))
				{
					$APPLICATION->ThrowException(GetMessage("CAT_DOC_PURCHASING_INFO_ERROR"));
					return false;
				}
			}
			$i++;
		}
		return ($i > 0);
	}
}
