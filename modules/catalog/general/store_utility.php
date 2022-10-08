<?php
IncludeModuleLangFile(__DIR__.'\\store_docs.php');

class CCatalogStoreControlUtil
{
	protected static $storeNames = array();

	/** By store ID, returns its title and\or address.
	 * @param $storeId
	 * @return string
	 */
	public static function getStoreName($storeId): string
	{
		$storeId = (int)$storeId;
		if ($storeId <= 0)
			return '';

		if (!isset(self::$storeNames[$storeId]))
		{
			$storeIterator = CCatalogStore::GetList(
				array(),
				array('ID' => $storeId),
				false,
				false,
				array('ID', 'ADDRESS', 'TITLE')
			);
			$storeName = '';
			if ($store = $storeIterator->Fetch())
			{
				$store['ID'] = (int)$store['ID'];
				$store['ADDRESS'] = (string)$store['ADDRESS'];
				$store['TITLE'] = (string)$store['TITLE'];
				$storeName = ($store['TITLE'] !== '' ? $store['TITLE'].' ('.$store['ADDRESS'].')' : $store['ADDRESS']);
			}
			unset($store, $storeIterator);
			self::$storeNames[$storeId] = $storeName;
		}
		else
		{
			$storeName = self::$storeNames[$storeId];
		}

		return $storeName;
	}

	/** Returns an array, containing information about the product block on its ID.
	 * @param $elementId
	 * @return array|string
	 */
	public static function getProductInfo($elementId)
	{
		$elementId = (int)$elementId;
		if($elementId <= 0)
			return '';

		$dbProduct = CIBlockElement::GetList(
			array(),
			array("ID" => $elementId),
			false,
			false,
			array('ID', 'IBLOCK_ID', 'IBLOCK_SECTION_ID', 'DETAIL_PICTURE', 'PREVIEW_PICTURE', 'NAME', 'XML_ID')
		);
		$arProduct = $dbProduct->GetNext();
		unset($dbProduct);
		if (empty($arProduct))
		{
			return '';
		}

		if($arProduct["IBLOCK_ID"] > 0)
		{
			$arProduct["EDIT_PAGE_URL"] = CIBlock::GetAdminElementEditLink(
				$arProduct["IBLOCK_ID"],
				$elementId,
				["find_section_section" => $arProduct["IBLOCK_SECTION_ID"]]
			);
		}
		$arProduct["DETAIL_PAGE_URL"] = htmlspecialcharsex($arProduct["DETAIL_PAGE_URL"]);

		$imgCode = "";
		if($arProduct["DETAIL_PICTURE"] > 0)
		{
			$imgCode = $arProduct["DETAIL_PICTURE"];
		}
		elseif($arProduct["PREVIEW_PICTURE"] > 0)
		{
			$imgCode = $arProduct["PREVIEW_PICTURE"];
		}

		if ($imgCode > 0)
		{
			$arFile = CFile::GetFileArray($imgCode);
			$arImgProduct = CFile::ResizeImageGet($arFile, array('width' => 80, 'height' => 80), BX_RESIZE_IMAGE_PROPORTIONAL, false, false);
			$arProduct["IMG_URL"] = $arImgProduct['src'];
		}

		return $arProduct;
	}

	/** Checks whether the same method in the class, describing the transmitted document type. Calls this method and returns a set of fields for this type of document.
	 * @param string $docType
	 * @return array|null
	 */
	public static function getFields(string $docType): ?array
	{
		if($docType !== '' && isset(CCatalogDocs::$types[$docType]))
		{
			/** @var CCatalogDocsTypes $documentClass */
			$documentClass = CCatalogDocs::$types[$docType];
			return $documentClass::getFields();
		}
		return null;
	}

	public static function getTypeFields(string $docType): ?array
	{
		if ($docType !== '' && isset(CCatalogDocs::$types[$docType]))
		{
			/** @var CCatalogDocsTypes $documentClass */
			$documentClass = CCatalogDocs::$types[$docType];

			return [
				'DOCUMENT' => $documentClass::getDocumentFields(),
				'ELEMENT' => $documentClass::getElementFields(),
			];
		}

		return null;
	}

	/** Generate a list of products on which did not match the total number and amount of all warehouses.
	 * @param $arProduct
	 * @param int $numberDisplayedElements
	 * @return string
	 */
	public static function showErrorProduct($arProduct, $numberDisplayedElements = 10): string
	{
		$strError = '';
		$numberDisplayedElements = intval($numberDisplayedElements);
		if($numberDisplayedElements < 1)
			$numberDisplayedElements = 1;
		if(is_array($arProduct))
		{
			foreach($arProduct as $key => $product)
			{
				$strError .= "\n- ".$product;
				if($key >= ($numberDisplayedElements - 1))
				{
					$strError .= "\n...".GetMessage("CAT_DOC_AND_MORE", array("#COUNT#" => (count($arProduct) - $numberDisplayedElements)));
					break;
				}
			}
		}
		return $strError;
	}

	public static function getQuantityInformation($productId)
	{
		global $DB;

		return $DB->Query("SELECT SUM(SP.AMOUNT) as SUM, CP.QUANTITY_RESERVED as RESERVED FROM b_catalog_store_product SP INNER JOIN b_catalog_product CP ON SP.PRODUCT_ID = CP.ID INNER JOIN b_catalog_store CS ON SP.STORE_ID = CS.ID WHERE SP.PRODUCT_ID = ".$productId."  AND CS.ACTIVE = 'Y' GROUP BY QUANTITY_RESERVED ", true);
	}

	public static function clearStoreName($storeId)
	{
		$storeId = (int)$storeId;
		if ($storeId > 0)
		{
			if (isset(self::$storeNames[$storeId]))
				unset(self::$storeNames[$storeId]);
		}
	}

	public static function clearAllStoreNames()
	{
		self::$storeNames = array();
	}

	public static function loadAllStoreNames($active = true)
	{
		$active = ($active === true);
		self::$storeNames = array();
		$filter = ($active ? array('ACTIVE' => 'Y') : array());
		$storeIterator = CCatalogStore::GetList(
			array(),
			$filter,
			false,
			false,
			array('ID', 'ADDRESS', 'TITLE')
		);
		while ($store = $storeIterator->Fetch())
		{
			$store['ID'] = (int)$store['ID'];
			$store['ADDRESS'] = (string)$store['ADDRESS'];
			$store['TITLE'] = (string)$store['TITLE'];
			self::$storeNames[$store['ID']] = ($store['TITLE'] !== '' ? $store['TITLE'].' ('.$store['ADDRESS'].')' : $store['ADDRESS']);
		}
		unset($store, $storeIterator, $filter);
	}

	/**
	 * Returns multiple files from \Bitrix\Main\UI\FileInput control.
	 *
	 * @param \Bitrix\Main\HttpRequest $request
	 * @param array $fieldList
	 * @return array|null
	 */
	public static function getMultipleFilesFromPost(\Bitrix\Main\HttpRequest $request, array $fieldList): ?array
	{
		if (empty($fieldList))
		{
			return null;
		}

		$result = [];
		$requestFields = $request->getPostList();
		foreach ($fieldList as $fieldId)
		{
			if (!is_string($fieldId) || $fieldId === '')
			{
				continue;
			}
			if (isset($requestFields[$fieldId]) && is_array($requestFields[$fieldId]))
			{
				foreach ($requestFields[$fieldId] as $rowId => $value)
				{
					$fileRow = [];
					$parsed = [];
					if (preg_match('/^[0-9]+$/', $rowId, $parsed))
					{
						$fileRow = [
							'ID' => (int)$rowId,
						];
					}
					elseif (preg_match('/^n[0-9]+$/', $rowId, $parsed))
					{
						$fileRow = [
							'ID' => null,
						];
					}

					if (empty($fileRow))
					{
						continue;
					}

					if (is_array($value))
					{
						$fileRow['FILE_UPLOAD'] = \CIBlock::makeFileArray(
							$value
						);
					}
					elseif (is_string($value))
					{
						if (preg_match('/^[0-9]+$/', $value, $parsed))
						{
							$fileRow['FILE_ID'] = (int)$value;
						}
						else
						{
							continue;
						}
					}
					else
					{
						continue;
					}

					if (!isset($result[$fieldId]))
					{
						$result[$fieldId] = [];
					}
					$result[$fieldId][$rowId] = $fileRow;
				}
			}

			$deleteFieldId = $fieldId . '_del';
			if (isset($requestFields[$deleteFieldId]) && is_array($requestFields[$deleteFieldId]))
			{
				foreach ($requestFields[$deleteFieldId] as $rowId => $value)
				{
					if ($value !== 'Y')
					{
						continue;
					}
					$parsed = [];
					if (!preg_match('/^[0-9]+$/', $rowId, $parsed))
					{
						continue;
					}
					$rowId = (int)$rowId;
					if (!isset($result[$fieldId]))
					{
						$result[$fieldId] = [];
					}
					if (
						isset($result[$fieldId][$rowId])
						&& isset($result[$fieldId][$rowId]['FILE_ID'])
					)
					{
						$result[$fieldId][$rowId]['DEL'] = $value;
					}
					else
					{
						$result[$fieldId][$rowId] = [
							'DEL' => $value,
						];
					}
				}
			}
		}

		return (!empty($result) ? $result : null);
	}

	/**
	 * Returns single files from \Bitrix\Main\UI\FileInput control.
	 *
	 * @param \Bitrix\Main\HttpRequest $request
	 * @param array $fieldList
	 * @return array|null
	 */
	public static function getFilesFromPost(\Bitrix\Main\HttpRequest $request, array $fieldList): ?array
	{
		if (empty($fieldList))
		{
			return null;
		}

		$result = [];
		$requestFields = $request->getPostList();
		foreach ($fieldList as $fieldId)
		{
			if (!is_string($fieldId) || $fieldId === '')
			{
				continue;
			}
			if (isset($requestFields[$fieldId]))
			{
				$value = $requestFields[$fieldId];

				$fileRow = [];
				if (is_array($value))
				{
					$fileRow['FILE_UPLOAD'] = \CIBlock::makeFileArray(
						$value
					);
				}
				elseif (is_string($value))
				{
					$parsed = [];
					if (preg_match('/^[0-9]+$/', $value, $parsed))
					{
						$fileRow['FILE_ID'] = (int)$value;
					}
				}

				if (!empty($fileRow))
				{
					$result[$fieldId] = $fileRow;
				}
			}

			$deleteFieldId = $fieldId . '_del';
			if (isset($requestFields[$deleteFieldId]) && $requestFields[$deleteFieldId] === 'Y')
			{
				if (isset($result[$fieldId]) && isset($result[$fieldId]['FILE_ID']))
				{
					$result[$fieldId]['DEL'] = 'Y';
				}
				else
				{
					$result[$fieldId] = [
						'DEL' => 'Y',
					];
				}
			}
		}

		return (!empty($result) ? $result : null);
	}

	public static function isAllowShowShippingCenter(): bool
	{
		if (\Bitrix\Main\ModuleManager::isModuleInstalled('crm'))
		{
			return false;
		}

		return \Bitrix\Main\Config\Option::get('catalog', 'show_store_shipping_center') === 'Y';
	}
}
