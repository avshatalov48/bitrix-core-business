<?php
namespace Bitrix\Catalog\Product;

use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Iblock,
	Bitrix\Catalog,
	Bitrix\Sale;

Loc::loadMessages(__FILE__);

/**
 * Class Basket
 * @package Bitrix\Catalog\Product
 */
class Basket
{
	private static $statisticIncluded = null;
	private static $saleIncluded = null;

	/**
	 * Add to basket from public components.
	 *
	 * @param array $product				Product data (with properties).
	 * @param array $basketFields			Basket fields (if used).
	 * @param array $options				Execute options.
	 *
	 * @return Main\Result
	 *
	 * @throws Main\LoaderException
	 * @throws Main\ObjectNotFoundException
	 */
	public static function addProduct(array $product, array $basketFields = [], array $options = [])
	{
		$result = new Main\Result();

		if (empty($product['PRODUCT_ID']))
		{
			$result->addError(new Main\Error(Loc::getMessage('BX_CATALOG_PRODUCT_BASKET_ERR_NO_PRODUCT')));
			return $result;
		}
		$productId = (int)$product['PRODUCT_ID'];
		if ($productId <= 0)
		{
			$result->addError(new Main\Error(Main\Localization\Loc::getMessage('BX_CATALOG_PRODUCT_BASKET_ERR_NO_PRODUCT')));
			return $result;
		}

		$product['MODULE'] = 'catalog';
		$product['PRODUCT_PROVIDER_CLASS'] = self::getDefaultProviderName();

		if (!empty($basketFields))
			$product = array_merge($product, $basketFields);

		if (self::$saleIncluded === null)
			self::$saleIncluded = Loader::includeModule('sale');

		if (!self::$saleIncluded)
		{
			$result->addError(new Main\Error(Loc::getMessage('BX_CATALOG_PRODUCT_BASKET_ERR_NO_SALE')));
			return $result;
		}

		$siteId = SITE_ID;
		if (!empty($basketFields['LID']))
			$siteId = $basketFields['LID'];

		$context = array(
			'SITE_ID' => $siteId,
		);

		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

		/** @var Sale\Basket $basketClass */
		$basketClass = $registry->getBasketClassName();

		$basket = $basketClass::loadItemsForFUser(Sale\Fuser::getId(), $siteId);

		$options['CHECK_PERMISSIONS'] = 'Y';
		$options['USE_MERGE'] = (isset($options['USE_MERGE']) && $options['USE_MERGE'] == 'N' ? 'N' : 'Y');
		$options['CHECK_CRAWLERS'] = 'Y';

		$result = static::add($basket, $product, $context, $options);

		if ($result->isSuccess())
		{
			$saveResult = $basket->save();
			if ($saveResult->isSuccess())
			{
				$resultData = $result->getData();
				if (!empty($resultData['BASKET_ITEM']))
				{
					$item = $resultData['BASKET_ITEM'];
					if ($item instanceof Sale\BasketItemBase)
					{
						if (self::$statisticIncluded === null)
							self::$statisticIncluded = Main\Loader::includeModule('statistic');

						if (self::$statisticIncluded)
						{
							\CStatistic::Set_Event(
								'sale2basket', 'catalog', $item->getField('DETAIL_PAGE_URL')
							);
						}
						$result->setData(array(
							'ID' => $item->getId()
						));
					}
					else
					{
						$result->addError(new Main\Error(Loc::getMessage('BX_CATALOG_PRODUCT_BASKET_ERR_UNKNOWN')));
					}
					unset($item);
				}
				else
				{
					$result->addError(new Main\Error(Loc::getMessage('BX_CATALOG_PRODUCT_BASKET_ERR_UNKNOWN')));
				}
				unset($resultData);
			}
			else
			{
				$result->addErrors($saveResult->getErrors());
			}
			unset($saveResult);
		}
		unset($basket, $context, $siteId);

		return $result;
	}

	/**
	 * Proxy method of adding a item to the basket.
	 *
	 * @param Sale\BasketBase $basket		Working basket.
	 * @param array $fields					Basket item fields for add.
	 * @param array $context				Working context (site, user).
	 * @param array $options				Execute options.
	 *
	 * @return Main\Result
	 *
	 * @throws Main\LoaderException
	 * @throws Main\ObjectNotFoundException
	 */
	public static function addProductToBasket(Sale\BasketBase $basket, array $fields, array $context, array $options = [])
	{
		$options['CHECK_CRAWLERS'] = 'N';
		return static::add($basket, $fields, $context, $options);
	}

	/**
	 * Proxy method of adding a item to the basket. Already check iblock permissions.
	 *
	 * @param Sale\BasketBase $basket		Working basket.
	 * @param array $fields					Basket item fields for add.
	 * @param array $context				Working context (site, user).
	 * @param bool|array $options			Execute options (by default - search existing row in basket before add options value).
	 *
	 * @return Main\Result
	 *
	 * @throws Main\LoaderException
	 * @throws Main\ObjectNotFoundException
	 */
	public static function addProductToBasketWithPermissions(Sale\BasketBase $basket, array $fields, array $context, $options = true)
	{
		if (!is_array($options))
			$options = ['USE_MERGE' => ($options ? 'Y' : 'N')];
		$options['CHECK_PERMISSIONS'] = 'Y';
		$options['CHECK_CRAWLERS'] = 'Y';
		return static::add($basket, $fields, $context, $options);
	}

	/**
	 * The main method of adding a item to the basket. Does not save the result in the database.
	 * @internal
	 *
	 * @param Sale\BasketBase $basket		Working basket.
	 * @param array $fields					Basket item fields for add.
	 * @param array $context				Working context (site, user).
	 * @param array $options				Options (check permiisons, search existing row, etc).
	 *
	 * @return Main\Result
	 *
	 * @throws Main\LoaderException
	 * @throws Main\ObjectNotFoundException
	 */
	private static function add(Sale\BasketBase $basket, array $fields, array $context, array $options = [])
	{
		$result = new Main\Result();

		if (!isset($options['CHECK_CRAWLERS']) || $options['CHECK_CRAWLERS'] == 'Y')
		{
			$validBuyer = static::checkCurrentUser();
			if (!$validBuyer->isSuccess())
			{
				$result->addErrors($validBuyer->getErrors());
				return $result;
			}
			unset($validBuyer);
		}

		if (empty($fields['PRODUCT_ID']))
		{
			$result->addError(new Main\Error(Loc::getMessage('BX_CATALOG_PRODUCT_BASKET_ERR_NO_PRODUCT')));
			return $result;
		}
		$productId = (int)$fields['PRODUCT_ID'];
		if ($productId <= 0)
		{
			$result->addError(new Main\Error(Main\Localization\Loc::getMessage('BX_CATALOG_PRODUCT_BASKET_ERR_NO_PRODUCT')));
			return $result;
		}
		unset($fields['PRODUCT_ID']);

		if (empty($fields['QUANTITY']))
		{
			$result->addError(new Main\Error(Loc::getMessage('BX_CATALOG_PRODUCT_BASKET_ERR_EMPTY_QUANTITY')));
			return $result;
		}
		$quantity = (float)$fields['QUANTITY'];
		if ($quantity <= 0)
		{
			$result->addError(new Main\Error(Loc::getMessage('BX_CATALOG_PRODUCT_BASKET_ERR_EMPTY_QUANTITY')));
			return $result;
		}

		if (self::$saleIncluded === null)
			self::$saleIncluded = Loader::includeModule('sale');

		if (!self::$saleIncluded)
		{
			$result->addError(new Main\Error(Loc::getMessage('BX_CATALOG_PRODUCT_BASKET_ERR_NO_SALE')));
			return $result;
		}

		$module = 'catalog';

		$presets = ['PRODUCT_ID' => $productId];

		if (array_key_exists('MODULE', $fields))
		{
			$module = $fields['MODULE'];
			unset($fields['MODULE']);
		}

		$transferFields = [
			'PRODUCT_PROVIDER_CLASS' => true,
			'CALLBACK_FUNC' => true,
			'PAY_CALLBACK_FUNC' => true,
			'SUBSCRIBE' => true
		];
		$presets = array_merge($presets, array_intersect_key($fields, $transferFields));
		$fields = array_diff_key($fields, $transferFields);
		unset($transferFields);

		$propertyList = (!empty($fields['PROPS']) && is_array($fields['PROPS']) ? $fields['PROPS'] : []);
		if (array_key_exists('PROPS', $fields))
			unset($fields['PROPS']);

		if ($module == 'catalog')
		{
			$elementFilter = array(
				'ID' => $productId,
				'ACTIVE' => 'Y',
				'ACTIVE_DATE' => 'Y',
				'CHECK_PERMISSIONS' => 'N'
			);

			if (!empty($options['CHECK_PERMISSIONS']) && $options['CHECK_PERMISSIONS'] == "Y")
			{
				$elementFilter['CHECK_PERMISSIONS'] = 'Y';
				$elementFilter['MIN_PERMISSION'] = 'R';
				if (isset($context['USER_ID']))
					$elementFilter['PERMISSIONS_BY'] = $context['USER_ID'];
			}

			$iterator = \CIBlockElement::GetList(
				array(),
				$elementFilter,
				false,
				false,
				array(
					"ID",
					"IBLOCK_ID",
					"XML_ID",
					"NAME",
					"DETAIL_PAGE_URL",
				)
			);
			if (!($elementFields = $iterator->GetNext()))
			{
				$result->addError(new Main\Error(Loc::getMessage('BX_CATALOG_PRODUCT_BASKET_ERR_NO_IBLOCK_ELEMENT')));
				return $result;
			}

			$iterator = Catalog\ProductTable::getList(array(
				'select' => array(
					'ID', 'TYPE', 'AVAILABLE', 'CAN_BUY_ZERO', 'QUANTITY_TRACE', 'QUANTITY',
					'WEIGHT', 'WIDTH', 'HEIGHT', 'LENGTH',
					'MEASURE', 'BARCODE_MULTI'
				),
				'filter' => array('=ID' => $productId)
			));
			$productFields = $iterator->fetch();
			unset($iterator);
			if (empty($productFields))
			{
				$result->addError(new Main\Error(Loc::getMessage('BX_CATALOG_PRODUCT_BASKET_ERR_NO_PRODUCT')));
				return $result;
			}

			if (
				($productFields['TYPE'] == Catalog\ProductTable::TYPE_SKU || $productFields['TYPE'] == Catalog\ProductTable::TYPE_EMPTY_SKU)
				&& (string)Main\Config\Option::get('catalog', 'show_catalog_tab_with_offers') != 'Y'
			)
			{
				$result->addError(new Main\Error(Loc::getMessage('BX_CATALOG_PRODUCT_BASKET_ERR_CANNOT_ADD_SKU')));
				return $result;
			}
			if ($productFields['AVAILABLE'] != Catalog\ProductTable::STATUS_YES)
			{
				$result->addError(new Main\Error(Loc::getMessage('BX_CATALOG_PRODUCT_BASKET_ERR_PRODUCT_RUN_OUT')));
				return $result;
			}
			if ($productFields['TYPE'] == Catalog\ProductTable::TYPE_OFFER)
			{
				$skuInfo = \CCatalogSku::GetProductInfo($productId, $elementFields['IBLOCK_ID']);
				if (empty($skuInfo))
				{
					$result->addError(new Main\Error(Loc::getMessage('BX_CATALOG_PRODUCT_BASKET_ERR_PRODUCT_BAD_TYPE')));
					return $result;
				}
				else
				{
					$parentIterator = \CIBlockElement::GetList(
						array(),
						array(
							'ID' => $skuInfo['ID'],
							'IBLOCK_ID' => $skuInfo['IBLOCK_ID'],
							'ACTIVE' => 'Y',
							'ACTIVE_DATE' => 'Y',
							'CHECK_PERMISSIONS' => 'N'
						),
						false,
						false,
						array('ID', 'IBLOCK_ID', 'XML_ID')
					);
					$parent = $parentIterator->Fetch();
					if (empty($parent))
					{
						$result->addError(new Main\Error(Loc::getMessage('BX_CATALOG_PRODUCT_BASKET_ERR_NO_PRODUCT')));
						return $result;
					}
					elseif (mb_strpos($elementFields["~XML_ID"], '#') === false)
					{
						$elementFields["~XML_ID"] = $parent['XML_ID'].'#'.$elementFields["~XML_ID"];
					}
					unset($parent, $parentIterator);
				}
			}

			if ($productFields['TYPE'] == Catalog\ProductTable::TYPE_SET)
			{
				$allSets = \CCatalogProductSet::getAllSetsByProduct($productId, \CCatalogProductSet::TYPE_SET);
				if (empty($allSets))
				{
					$result->addError(new Main\Error(Loc::getMessage('BX_CATALOG_PRODUCT_BASKET_ERR_NO_PRODUCT_SET')));
					return $result;
				}
				$set = current($allSets);
				unset($allSets);
				$itemIds = array();
				foreach ($set['ITEMS'] as $item)
				{
					if ($item['ITEM_ID'] != $item['OWNER_ID'])
						$itemIds[$item['ITEM_ID']] = $item['ITEM_ID'];
				}
				if (empty($itemIds))
				{
					$result->addError(new Main\Error(Loc::getMessage('BX_CATALOG_PRODUCT_BASKET_ERR_NO_PRODUCT_SET')));
					return $result;
				}

				$setFilter = array(
					'ID' => $itemIds,
					'ACTIVE' => 'Y',
					'ACTIVE_DATE' => 'Y',
					'CHECK_PERMISSIONS' => 'N'
				);
				if (!empty($options['CHECK_PERMISSIONS']) && $options['CHECK_PERMISSIONS'] == "Y")
				{
					$setFilter['CHECK_PERMISSIONS'] = 'Y';
					$setFilter['MIN_PERMISSION'] = 'R';
					if (isset($context['USER_ID']))
						$setFilter['PERMISSIONS_BY'] = $context['USER_ID'];
				}

				$iterator = \CIBlockElement::GetList(
					array(),
					$setFilter,
					false,
					false,
					array('ID', 'IBLOCK_ID')
				);
				while ($row = $iterator->Fetch())
				{
					if (isset($itemIds[$row['ID']]))
						unset($itemIds[$row['ID']]);
				}
				unset($row, $iterator);
				if (!empty($itemIds))
				{
					$result->addError(new Main\Error(Loc::getMessage('BX_CATALOG_PRODUCT_BASKET_ERR_NO_PRODUCT_SET_ITEMS')));
					return $result;
				}
			}

			$propertyIndex = self::getPropertyIndex('CATALOG.XML_ID', $propertyList);
			if (!isset($fields['CATALOG_XML_ID']) || $propertyIndex === null)
			{
				$iBlockXmlID = (string)\CIBlock::GetArrayByID($elementFields['IBLOCK_ID'], 'XML_ID');
				if ($iBlockXmlID !== '')
				{
					$fields['CATALOG_XML_ID'] = $iBlockXmlID;
					$propertyData = array(
						'NAME' => 'Catalog XML_ID',
						'CODE' => 'CATALOG.XML_ID',
						'VALUE' => $iBlockXmlID
					);
					if ($propertyIndex !== null)
						$propertyList[$propertyIndex] = $propertyData;
					else
						$propertyList[] = $propertyData;
					unset($propertyData);
				}
				unset($iBlockXmlID);
			}

			$propertyIndex = self::getPropertyIndex('PRODUCT.XML_ID', $propertyList);
			if (!isset($fields['PRODUCT_XML_ID']) || $propertyIndex === null)
			{
				$fields['PRODUCT_XML_ID'] = $elementFields['~XML_ID'];
				$propertyData = array(
					'NAME' => 'Product XML_ID',
					'CODE' => 'PRODUCT.XML_ID',
					'VALUE' => $elementFields['~XML_ID']
				);
				if ($propertyIndex !== null)
					$propertyList[$propertyIndex] = $propertyData;
				else
					$propertyList[] = $propertyData;
				unset($propertyData);
			}
			unset($propertyIndex);

			//TODO: change to d7 measure class
			$productFields['MEASURE'] = (int)$productFields['MEASURE'];
			$productFields['MEASURE_NAME'] = '';
			$productFields['MEASURE_CODE'] = 0;
			if ($productFields['MEASURE'] <= 0)
			{
				$measure = \CCatalogMeasure::getDefaultMeasure(true, true);
				$productFields['MEASURE_NAME'] = $measure['~SYMBOL_RUS'];
				$productFields['MEASURE_CODE'] = $measure['CODE'];
				unset($measure);
			}
			else
			{
				$measureIterator = \CCatalogMeasure::getList(
					[],
					['ID' => $productFields['MEASURE']],
					false,
					false,
					['ID', 'SYMBOL_RUS', 'CODE']
				);
				$measure = $measureIterator->Fetch();
				unset($measureIterator);
				if (!empty($measure))
				{
					$productFields['MEASURE_NAME'] = $measure['SYMBOL_RUS'];
					$productFields['MEASURE_CODE'] = $measure['CODE'];
				}
				unset($measure);
			}

			if (isset($options['FILL_PRODUCT_PROPERTIES']) && $options['FILL_PRODUCT_PROPERTIES'] === 'Y')
			{
				if ($productFields['TYPE'] == Catalog\ProductTable::TYPE_OFFER)
				{
					self::fillOfferProperties($propertyList, $productId, $elementFields['IBLOCK_ID']);
				}
			}

			$fields = $fields +
				[
					'DETAIL_PAGE_URL' => $elementFields['~DETAIL_PAGE_URL'],
					'BARCODE_MULTI' => $productFields['BARCODE_MULTI'],
					'WEIGHT' => (float)$productFields['WEIGHT'],
					'DIMENSIONS' => [
						'WIDTH' => $productFields['WIDTH'],
						'HEIGHT' => $productFields['HEIGHT'],
						'LENGTH' => $productFields['LENGTH']
					],
					'TYPE' => ($productFields['TYPE'] == Catalog\ProductTable::TYPE_SET ? \CCatalogProductSet::TYPE_SET : null),
					'MEASURE_ID' => $productFields['MEASURE'],
					'MEASURE_NAME' => $productFields['MEASURE_NAME'],
					'MEASURE_CODE' => $productFields['MEASURE_CODE']
				];

			unset($productFields);
		}

		if (static::isCompatibilityEventAvailable())
		{
			$eventFields = array_merge($presets, $fields);
			$eventFields['MODULE'] = $module;
			$eventFields['PROPS'] = $propertyList;

			$eventResult = static::runCompatibilityEvent($eventFields);
			if ($eventResult === false)
			{
				return $result;
			}

			foreach ($eventResult as $key => $value)
			{
				if (isset($presets[$key]))
				{
					if ($presets[$key] !== $value)
					{
						$presets[$key] = $value;
					}
				}
				elseif (!isset($fields[$key]) || $fields[$key] !== $value)
				{
					$fields[$key] = $value;
				}
			}
			unset($key, $value);

			$propertyList = $eventResult['PROPS'];
			unset($eventResult);
		}

		$basketItem = null;
		// using merge by default
		if (!isset($options['USE_MERGE']) || $options['USE_MERGE'] === 'Y')
		{
			$basketItem = $basket->getExistsItem($module, $productId, $propertyList);
		}

		if ($basketItem)
		{
			$fields['QUANTITY'] = $basketItem->isDelay() ? $quantity : $basketItem->getQuantity() + $quantity;
		}
		else
		{
			$fields['QUANTITY'] = $quantity;
			$basketCode = !empty($fields['BASKET_CODE']) ? $fields['BASKET_CODE'] : null;
			$basketItem = $basket->createItem($module, $productId, $basketCode);
		}

		if (!$basketItem)
		{
			throw new Main\ObjectNotFoundException('BasketItem');
		}

		/** @var Sale\BasketPropertiesCollection $propertyCollection */
		$propertyCollection = $basketItem->getPropertyCollection();
		if ($propertyCollection)
		{
			$propertyCollection->redefine($propertyList);
		}

		$r = $basketItem->setFields($presets);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
			return $result;
		}

		//If error happend while setting quantity field we will know the name of product.
		if(!empty($elementFields['~NAME']))
		{
			$basketItem->setField('NAME', $elementFields['~NAME']);
		}

		$r = $basketItem->setField('QUANTITY', $fields['QUANTITY']);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
			return $result;
		}
		unset($fields['QUANTITY']);

		$settableFields = array_fill_keys($basketItem::getSettableFields(), true);
		$basketFields = array_intersect_key($fields, $settableFields);

		if (!empty($basketFields))
		{
			$r = $basketItem->setFields($basketFields);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
				return $result;
			}
		}

		$result->setData(['BASKET_ITEM' => $basketItem]);

		return $result;
	}

	/**
	 * @return bool
	 */
	private static function isCompatibilityEventAvailable()
	{
		return Main\Config\Option::get('sale', 'expiration_processing_events', 'N') === 'Y';
	}

	/**
	 * @param array $fields
	 * @return array|false
	 */
	private static function runCompatibilityEvent(array $fields)
	{
		foreach (GetModuleEvents("sale", "OnBeforeBasketAdd", true) as $event)
		{
			if (ExecuteModuleEventEx($event, array(&$fields)) === false)
				return false;
		}

		return $fields;
	}

	/**
	 * @param array &$propertyList
	 * @param int $id
	 * @param int $iblockId
	 * @return void
	 */
	private static function fillOfferProperties(array &$propertyList, $id, $iblockId)
	{
		static $properties = [];

		$skuInfo = \CCatalogSku::GetInfoByOfferIBlock($iblockId);
		if (empty($skuInfo))
			return;
		$skuPropertyId = $skuInfo['SKU_PROPERTY_ID'];
		$parentIblockId = $skuInfo['PRODUCT_IBLOCK_ID'];
		unset($skuInfo);

		if (!isset($properties[$iblockId]))
		{
			$properties[$iblockId] = [];
			$iterator = Iblock\PropertyTable::getList([
				'select' => ['ID'],
				'filter' => [
					'=IBLOCK_ID' => $iblockId,
					'=ACTIVE' => 'Y',
					'=PROPERTY_TYPE' => [
						Iblock\PropertyTable::TYPE_ELEMENT,
						Iblock\PropertyTable::TYPE_LIST,
						Iblock\PropertyTable::TYPE_STRING
					],
					'=MULTIPLE' => 'N'
				],
				'order' => ['ID' => 'ASC']
			]);
			while ($row = $iterator->fetch())
			{
				$row['ID'] = (int)$row['ID'];
				if ($row['ID'] == $skuPropertyId)
					continue;
				$properties[$iblockId][] = $row['ID'];
			}
			unset($row, $iterator);
		}
		if (empty($properties[$iblockId]))
			return;

		$offerProperties = \CIBlockPriceTools::GetOfferProperties(
			$id,
			$parentIblockId,
			$properties[$iblockId]
		);
		unset($parentIblockId, $skuPropertyId);

		if (empty($offerProperties))
			return;

		$codeMap = [];
		if (!empty($propertyList))
		{
			foreach ($propertyList as $row)
			{
				if (!isset($row['CODE']))
					continue;
				$index = (string)$row['CODE'];
				if ($index === '')
					continue;
				$codeMap[$index] = true;
			}
			unset($index, $row);
		}
		foreach ($offerProperties as $row)
		{
			$index = (string)$row['CODE'];
			if (isset($codeMap[$index]))
				continue;
			$codeMap[$index] = true;
			$propertyList[] = $row;
		}
		unset($index, $row, $codeMap, $offerProperties);
	}

	/**
	 * Search basket property.
	 *
	 * @param string $code				Property code.
	 * @param array $propertyList		Basket properties.
	 * @return int|null
	 */
	private static function getPropertyIndex($code, array $propertyList = array())
	{
		$propertyIndex = null;
		if (empty($propertyList))
			return $propertyIndex;

		foreach ($propertyList as $index => $propertyData)
		{
			if (!empty($propertyData['CODE']) && $code == $propertyData['CODE'])
			{
				$propertyIndex = $index;
				break;
			}
		}
		unset($index, $propertyData);

		return $propertyIndex;
	}

	/**
	 * @return string
	 */
	public static function getDefaultProviderName()
	{
		return "\Bitrix\Catalog\Product\CatalogProvider";
	}

	/**
	 * Returns the result of checking that the current user is not a search robot.
	 *
	 * @return bool
	 */
	public static function isNotCrawler()
	{
		$result = static::checkCurrentUser();
		return $result->isSuccess();

	}

	/**
	 * Checking that the current user is not a search robot.
	 *
	 * @return Main\Result
	 */
	private static function checkCurrentUser()
	{
		$result = new Main\Result();

		if (self::$statisticIncluded === null)
			self::$statisticIncluded = Main\Loader::includeModule('statistic');

		if (!self::$statisticIncluded)
			return $result;

		if (isset($_SESSION['SESS_SEARCHER_ID']) && (int)$_SESSION['SESS_SEARCHER_ID'] > 0)
			$result->addError(new Main\Error(Loc::getMessage('BX_CATALOG_PRODUCT_BASKET_ERR_SEARCHER')));

		return $result;
	}
}