<?php
namespace Bitrix\Catalog\Product;

use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Iblock;

Loc::loadMessages(__FILE__);

if (Loader::includeModule('iblock'))
{
	class PropertyCatalogFeature extends Iblock\Model\PropertyFeature
	{
		const FEATURE_ID_BASKET_PROPERTY = 'IN_BASKET'; // property added to basket
		const FEATURE_ID_OFFER_TREE_PROPERTY = 'OFFER_TREE'; // property is used to select offers

		/**
		 * Event handler. Returns catalog feature list for iblock.
		 *
		 * @param Main\Event $event		Event data.
		 * @return Main\EventResult
		 */
		public static function handlerPropertyFeatureBuildList(Main\Event $event)
		{
			$list = [];

			$property = $event->getParameter('property');
			$description = $event->getParameter('description');

			if (self::checkBasketProperty($property, $description))
			{
				$list[] = [
					'MODULE_ID' => 'catalog',
					'FEATURE_ID' => self::FEATURE_ID_BASKET_PROPERTY,
					'FEATURE_NAME' => Loc::getMessage('PROPERTY_CATALOG_FEATURE_NAME_BASKET_PROPERTY')
				];
			}
			if (self::checkOfferTreeProperty($property, $description))
			{
				$list[] = [
					'MODULE_ID' => 'catalog',
					'FEATURE_ID' => self::FEATURE_ID_OFFER_TREE_PROPERTY,
					'FEATURE_NAME' => Loc::getMessage('PROPERTY_CATALOG_FEATURE_NAME_SKU_TREE_PROPERTY')
				];
			}
			unset($description, $property);

			return new Main\EventResult(Main\EventResult::SUCCESS, $list);
		}

		/**
		 * Unified method to get properties added to basket.
		 *
		 * @param int $iblockId			Iblock identifier.
		 * @param array $parameters		Options
		 * 	keys are case sensitive:
		 *		<ul>
		 * 		<li>CODE	Return symbolic code as identifier (Y/N, default N).
		 *		</ul>
		 * @return array|null
		 * @throws Main\ArgumentException
		 * @throws Main\ArgumentNullException
		 * @throws Main\ArgumentOutOfRangeException
		 * @throws Main\ObjectPropertyException
		 * @throws Main\SystemException
		 */
		public static function getBasketPropertyCodes($iblockId, array $parameters = [])
		{
			$iblockId = (int)$iblockId;
			if ($iblockId <= 0)
				return null;

			$catalog = \CCatalogSku::GetInfoByIBlock($iblockId);
			if (empty($catalog))
				return null;

			if (!self::isEnabledFeatures())
				return self::getBasketPropertyByTypes($catalog, $parameters);

			$filter = null;
			switch ($catalog['CATALOG_TYPE'])
			{
				case \CCatalogSku::TYPE_CATALOG:
				case \CCatalogSku::TYPE_PRODUCT:
				case \CCatalogSku::TYPE_FULL:
					$filter = [
						[
							'LOGIC' => 'OR',
							[
								'=PROPERTY.MULTIPLE' => 'Y',
								'@PROPERTY.PROPERTY_TYPE' => [
									Iblock\PropertyTable::TYPE_ELEMENT,
									Iblock\PropertyTable::TYPE_SECTION,
									Iblock\PropertyTable::TYPE_LIST,
									Iblock\PropertyTable::TYPE_NUMBER,
									Iblock\PropertyTable::TYPE_STRING
								]
							],
							[
								'=PROPERTY.MULTIPLE' => 'N',
								'@PROPERTY.PROPERTY_TYPE' => [
									Iblock\PropertyTable::TYPE_ELEMENT,
									Iblock\PropertyTable::TYPE_LIST
								]
							]
						]
					];
					break;
				case \CCatalogSku::TYPE_OFFERS:
					$filter = [
						'!=PROPERTY.PROPERTY_TYPE' => Iblock\PropertyTable::TYPE_FILE,
						'!=ID' => $catalog['SKU_PROPERTY_ID']
					];
					break;
			}
			unset($catalog);

			if ($filter === null)
				return null;

			$filter['=MODULE_ID'] = 'catalog';
			$filter['=FEATURE_ID'] = self::FEATURE_ID_BASKET_PROPERTY;

			return self::getFilteredPropertyCodes($iblockId, $filter, $parameters);
		}

		/**
		 * Unified method for obtaining properties used to select offers.
		 *
		 * @param int $iblockId			Iblock identifier.
		 * @param array $parameters		Options
		 * 	keys are case sensitive:
		 *		<ul>
		 * 		<li>CODE	Return symbolic code as identifier (Y/N, default N).
		 *		</ul>
		 * @return array|null
		 * @throws Main\ArgumentException
		 * @throws Main\ArgumentNullException
		 * @throws Main\ArgumentOutOfRangeException
		 * @throws Main\ObjectPropertyException
		 * @throws Main\SystemException
		 */
		public static function getOfferTreePropertyCodes($iblockId, array $parameters = [])
		{
			$iblockId = (int)$iblockId;
			if ($iblockId <= 0)
				return null;

			$catalog = \CCatalogSku::GetInfoByOfferIBlock($iblockId);
			if (empty($catalog))
				return null;

			if (!self::isEnabledFeatures())
				return self::getOfferTreePropertyByTypes($catalog, $parameters);

			$filter = [
				'=MODULE_ID' => 'catalog',
				'=FEATURE_ID' => self::FEATURE_ID_OFFER_TREE_PROPERTY,
				'=PROPERTY.MULTIPLE' => 'N',
				'!=PROPERTY.ID' => $catalog['SKU_PROPERTY_ID'],
				[
					'LOGIC' => 'OR',
					[
						'@PROPERTY.PROPERTY_TYPE' => [
							Iblock\PropertyTable::TYPE_ELEMENT,
							Iblock\PropertyTable::TYPE_LIST
						]
					],
					[
						'=PROPERTY.PROPERTY_TYPE' => Iblock\PropertyTable::TYPE_STRING,
						'=PROPERTY.USER_TYPE' => 'directory'
					]
				]
			];

			return self::getFilteredPropertyCodes($iblockId, $filter, $parameters);
		}

		/**
		 * Getting properties added to basket when feature engine is off.
		 * Internal method.
		 *
		 * @param array $catalog		Catalog description.
		 * @param array $parameters		Options
		 * 	keys are case sensitive:
		 *		<ul>
		 * 		<li>CODE	Return symbolic code as identifier (Y/N, default N).
		 *		</ul>
		 * @return array|null
		 * @throws Main\ArgumentException
		 * @throws Main\ObjectPropertyException
		 * @throws Main\SystemException
		 */
		private static function getBasketPropertyByTypes(array $catalog, array $parameters = [])
		{
			$result = [];

			$getCode = (isset($parameters['CODE']) && $parameters['CODE'] == 'Y');
			$filter = [];
			switch ($catalog['CATALOG_TYPE'])
			{
				case \CCatalogSku::TYPE_CATALOG:
					$filter = [
						'=IBLOCK_ID' => $catalog['IBLOCK_ID'],
						[
							'LOGIC' => 'OR',
							[
								'=MULTIPLE' => 'Y',
								'@PROPERTY_TYPE' => [
									Iblock\PropertyTable::TYPE_ELEMENT,
									Iblock\PropertyTable::TYPE_SECTION,
									Iblock\PropertyTable::TYPE_LIST,
									Iblock\PropertyTable::TYPE_NUMBER,
									Iblock\PropertyTable::TYPE_STRING
								]
							],
							[
								'=MULTIPLE' => 'N',
								'@PROPERTY_TYPE' => [
									Iblock\PropertyTable::TYPE_ELEMENT,
									Iblock\PropertyTable::TYPE_LIST
								]
							]
						]
					];
					break;
				case \CCatalogSku::TYPE_PRODUCT:
				case \CCatalogSku::TYPE_FULL:
					$filter = [
						'=IBLOCK_ID' => $catalog['PRODUCT_IBLOCK_ID'],
						[
							'LOGIC' => 'OR',
							[
								'=MULTIPLE' => 'Y',
								'@PROPERTY_TYPE' => [
									Iblock\PropertyTable::TYPE_ELEMENT,
									Iblock\PropertyTable::TYPE_SECTION,
									Iblock\PropertyTable::TYPE_LIST,
									Iblock\PropertyTable::TYPE_NUMBER,
									Iblock\PropertyTable::TYPE_STRING
								]
							],
							[
								'=MULTIPLE' => 'N',
								'@PROPERTY_TYPE' => [
									Iblock\PropertyTable::TYPE_ELEMENT,
									Iblock\PropertyTable::TYPE_LIST
								]
							]
						]
					];
					break;
				case \CCatalogSku::TYPE_OFFERS:
					$filter = [
						'=IBLOCK_ID' => $catalog['IBLOCK_ID'],
						'!=PROPERTY_TYPE' => Iblock\PropertyTable::TYPE_FILE,
						'!=ID' => $catalog['SKU_PROPERTY_ID']
					];
					break;
			}
			$filter['=ACTIVE'] = 'Y';

			$iterator = Iblock\PropertyTable::getList([
				'select' => ['ID', 'CODE', 'SORT'],
				'filter' => $filter,
				'order' => ['SORT' => 'ASC', 'ID' => 'ASC']
			]);
			while ($row = $iterator->fetch())
				$result[(int)$row['ID']] = self::getPropertyCode($row, $getCode);
			unset($row, $iterator);
			unset($filter, $getCode);

			return (!empty($result) ? array_values($result) : null);
		}

		/**
		 * Getting the properties used to select offers when feature engine is off.
		 * Internal method.
		 *
		 * @param array $catalog		Catalog description.
		 * @param array $parameters		Options
		 * 	keys are case sensitive:
		 *		<ul>
		 * 		<li>CODE	Return symbolic code as identifier (Y/N, default N).
		 *		</ul>
		 * @return array|null
		 * @throws Main\ArgumentException
		 * @throws Main\ObjectPropertyException
		 * @throws Main\SystemException
		 */
		private static function getOfferTreePropertyByTypes(array $catalog, array $parameters = [])
		{
			$result = [];

			$getCode = (isset($parameters['CODE']) && $parameters['CODE'] == 'Y');
			$filter = [
				'=IBLOCK_ID' => $catalog['IBLOCK_ID'],
				'!=ID' => $catalog['SKU_PROPERTY_ID'],
				'=ACTIVE' => 'Y',
				'=MULTIPLE' => 'N',
				[
					'LOGIC' => 'OR',
					[
						'@PROPERTY_TYPE' => [
							Iblock\PropertyTable::TYPE_ELEMENT,
							Iblock\PropertyTable::TYPE_LIST
						]
					],
					[
						'=PROPERTY_TYPE' => Iblock\PropertyTable::TYPE_STRING,
						'=USER_TYPE' => 'directory'
					]
				]
			];
			$iterator = Iblock\PropertyTable::getList([
				'select' => ['ID', 'CODE', 'SORT'],
				'filter' => $filter,
				'order' => ['SORT' => 'ASC', 'ID' => 'ASC']
			]);
			while ($row = $iterator->fetch())
				$result[(int)$row['ID']] = self::getPropertyCode($row, $getCode);
			unset($row, $iterator);
			unset($filter, $getCode);

			return (!empty($result) ? array_values($result) : null);
		}

		/**
		 * Check - can the property be added to the basket.
		 * Internal method.
		 *
		 * @param array $property		Property description.
		 * @param array $description	Additional description.
		 * @return bool
		 */
		private static function checkBasketProperty(array $property, array $description)
		{
			if (!isset($property['IBLOCK_ID']))
				return false;
			$catalogType = null;
			if ((int)$property['IBLOCK_ID'] > 0)
			{
				$catalog = \CCatalogSku::GetInfoByIBlock($property['IBLOCK_ID']);
				if (empty($catalog))
					return false;
				$catalogType = $catalog['CATALOG_TYPE'];
			}
			else
			{
				return false;
			}
			if ($catalogType === null)
				return false;
			if (!isset($property['PROPERTY_TYPE']))
				return false;
			if (!isset($property['MULTIPLE']))
				return false;

			switch ($catalogType)
			{
				case \CCatalogSku::TYPE_PRODUCT:
				case \CCatalogSku::TYPE_CATALOG:
				case \CCatalogSku::TYPE_FULL:
					if ($property['MULTIPLE'] == 'Y')
					{
						if (
							$property['PROPERTY_TYPE'] !== Iblock\PropertyTable::TYPE_ELEMENT
							&& $property['PROPERTY_TYPE'] !== Iblock\PropertyTable::TYPE_SECTION
							&& $property['PROPERTY_TYPE'] !== Iblock\PropertyTable::TYPE_LIST
							&& $property['PROPERTY_TYPE'] !== Iblock\PropertyTable::TYPE_NUMBER
							&& $property['PROPERTY_TYPE'] !== Iblock\PropertyTable::TYPE_STRING
						)
							return false;
					}
					elseif ($property['MULTIPLE'] == 'N')
					{
						if (
							$property['PROPERTY_TYPE'] !== Iblock\PropertyTable::TYPE_ELEMENT
							&& $property['PROPERTY_TYPE'] !== Iblock\PropertyTable::TYPE_LIST
						)
							return false;
					}
					else
					{
						return false;
					}
					break;
				case \CCatalogSku::TYPE_OFFERS:
					if ($property['PROPERTY_TYPE'] == Iblock\PropertyTable::TYPE_FILE)
						return false;
					break;
			}

			return true;
		}

		/**
		 * Check - can the property be used to select trade offers.
		 * Internal method.
		 *
		 * @param array $property		Property description.
		 * @param array $description	Additional description.
		 * @return bool
		 */
		private static function checkOfferTreeProperty(array $property, array $description)
		{
			if (!isset($property['IBLOCK_ID']))
				return false;
			if ((int)$property['IBLOCK_ID'] > 0)
			{
				$catalog = \CCatalogSku::GetInfoByOfferIBlock($property['IBLOCK_ID']);
				if (empty($catalog))
					return false;
			}
			else
			{
				return false;
			}

			if (
				!isset($property['PROPERTY_TYPE'])
				|| (
					$property['PROPERTY_TYPE'] != Iblock\PropertyTable::TYPE_ELEMENT
					&& $property['PROPERTY_TYPE'] != Iblock\PropertyTable::TYPE_LIST
					&& $property['PROPERTY_TYPE'] != Iblock\PropertyTable::TYPE_STRING
				)
			)
				return false;
			if ($property['PROPERTY_TYPE'] == Iblock\PropertyTable::TYPE_ELEMENT)
			{
				if (isset($property['USER_TYPE']) && $property['USER_TYPE'] == \CIBlockPropertySKU::USER_TYPE)
					return false;
			}
			if ($property['PROPERTY_TYPE'] == Iblock\PropertyTable::TYPE_STRING)
			{
				if (!isset($property['USER_TYPE']) || $property['USER_TYPE'] != 'directory')
					return false;
			}
			if (!isset($property['MULTIPLE']) || $property['MULTIPLE'] != 'N')
				return false;

			return true;
		}
	}
}