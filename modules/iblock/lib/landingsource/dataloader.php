<?php
namespace Bitrix\Iblock\LandingSource;

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock;
use Bitrix\Currency;
use Bitrix\Catalog;
use	Bitrix\Landing\Source;

Loc::loadMessages(__FILE__);

if (Loader::includeModule('landing'))
{
	class DataLoader extends Source\DataLoader
	{
		protected $catalogIncluded = null;

		protected $saleIncluded = null;

		protected $iblockId = 0;

		/** @var null|array Catalog description */
		protected $catalog = null;

		protected $productFields = null;

		protected $priceTypes = null;

		/**
		 * @return void
		 */
		public function __construct()
		{
			parent::__construct();
			$this->catalogIncluded = Loader::includeModule('catalog');
			$this->saleIncluded = Loader::includeModule('sale');
		}

		/**
		 * @return array
		 */
		public function getElementListData()
		{
			$this->seo->clear();

			$this->initIblock();

			$settings = self::getInternalSettings();

			$rawSelect = $this->getPreparedSelectFields();
			if (empty($rawSelect))
				return [];

			$rawOrder = $this->getOrder();
			self::prepareOrder($settings, $rawOrder);

			$rawSelect[] = 'DETAIL_PAGE_URL';
			$this->prepareSelectFields($settings, $rawSelect);
			unset($rawSelect);

			$settings['filter'] = $this->getIblockElementListFilter();

			$settings['limit'] = $this->getLimit();

			$detailPageUrl = $this->getOptionsValue('detail_page_url');
			if (!empty($detailPageUrl))
				$settings['templates']['detailPageUrl'] = $detailPageUrl;

			$settings['loadSeo'] = false;

			$settings['mode'] = 'list';

			return array_values($this->getElementsInternal($settings));
		}

		/**
		 * @param string|int $element
		 * @return array
		 */
		public function getElementData($element)
		{
			$this->seo->clear();

			$this->initIblock();

			$result = [];
			if (!is_string($element) && !is_int($element))
				return $result;
			$element = (int)$element;
			if ($element <= 0)
				return $result;

			$rawSelect = $this->getPreparedSelectFields();
			if (empty($rawSelect))
				return $result;

			$settings = self::getInternalSettings();

			$this->prepareSelectFields($settings, $rawSelect);
			unset($rawSelect);

			$filter = [];
			if (!empty($internalFilter) && is_array($internalFilter))
				$filter = array_merge($filter, $internalFilter);
			$filter['ID'] = $element;
			$settings['filter'] = $filter;
			unset($filter);

			$settings['loadSeo'] = true;

			$settings['mode'] = 'detail';

			$result = $this->getElementsInternal($settings);;
			unset($settings);
			if (!empty($result))
			{
				$result = reset($result);
				if (!empty($result))
				{
					$this->seo->setProperties($result['SEO_PROPERTIES']);
					unset($result['SEO_PROPERTIES']);
					$result = [$result];
				}
			}

			return $result;
		}

		/**
		 * @return array
		 */
		private static function getInternalSettings()
		{
			return [
				'rawSelect' => [],
				'element' => [],
				'element_properties' => [],
				'product' => [],
				'prices' => false,
				'limit' => 0,
				'order' => [],
				'rawOrder' => [],
				'sortByPrice' => false,
				'templates' => []
			];
		}

		/**
		 * @return array
		 */
		protected function getPreparedSelectFields()
		{
			$result = parent::getPreparedSelectFields();
			if (!empty($result))
			{
				if (!in_array('ID', $result))
				{
					$result[] = 'ID';
				}
				if (!in_array('IBLOCK_ID', $result))
				{
					$result[] = 'IBLOCK_ID';
				}
				if (!in_array('NAME', $result))
				{
					$result[] = 'NAME';
				}
			}
			return $result;
		}

		/**
		 * Returns element order.
		 *
		 * @return array
		 */
		protected function getOrder()
		{
			$result = parent::getOrder();
			if (!is_array($result))
			{
				$result = [];
			}
			return $result;
		}

		/**
		 * Returns max element count for showing.
		 *
		 * @return int
		 */
		protected function getLimit()
		{
			return (int)$this->getSettingsValue('limit');
		}

		/**
		 * @return void
		 */
		protected function initIblock()
		{
			$internalFilter = $this->getInternalFilter();
			if (!empty($internalFilter) && is_array($internalFilter))
			{
				if (isset($internalFilter['IBLOCK_ID']))
					$this->iblockId = (int)$internalFilter['IBLOCK_ID'];
				if ($this->iblockId > 0 && $this->catalogIncluded)
				{
					$catalog = \CCatalogSku::GetInfoByProductIBlock($this->iblockId);
					if (!empty($catalog))
						$this->catalog = $catalog;
					unset($catalog);
				}
			}
			unset($internalFilter);
		}

		/**
		 * @return array
		 */
		protected function getIblockElementListFilter()
		{
			$result = $this->compileIblockElementListFilter(
				$this->getPreparedFilter($this->getFilterFieldsDescription())
			);
			$internalFilter = $this->getInternalFilter();
			if (!empty($internalFilter) && is_array($internalFilter))
			{
				$result = array_merge($result, $internalFilter);
			}
			unset($internalFilter);
			return $result;
		}

		/**
		 * @param array $filter
		 * @return array
		 */
		protected function compileIblockElementListFilter(array $filter)
		{
			$result = [];
			if (isset($filter['master']))
				$result = $filter['master'];
			if (!empty($this->catalog) && isset($filter['offer']))
			{
				$subFilter = $filter['offer'];
				$subFilter['IBLOCK_ID'] = $this->catalog['IBLOCK_ID'];
				$result['=ID'] = \CIBlockElement::SubQuery('PROPERTY_'.$this->catalog['SKU_PROPERTY_ID'], $subFilter);
				unset($subFilter);
			}

			if (!isset($result['ACTIVE']))
				$result['ACTIVE'] = 'Y';
			return $result;
		}

		/**
		 * @param array $settings
		 * @param array $select
		 * @return void
		 */
		private function prepareSelectFields(array &$settings, array $select)
		{
			$settings['rawSelect'] = $select;
			if (!empty($settings['order']))
			{
				foreach (array_keys($settings['order']) as $field)
				{
					if (!in_array($field, $select))
						$select[] = $field;
				}
			}

			$productFields = $this->getProductFields();
			foreach ($select as $index => $field)
			{
				if ($field == 'PRICE')
				{
					$settings['prices'] = true;
					unset($select[$index]);
				}
				elseif (isset($productFields[$field]))
				{
					$settings['product'][$field] = $productFields[$field];
					unset($select[$index]);
				}
				elseif (strncmp($field, 'PROPERTY_', 9) == 0)
				{
					$propertyId = (int)mb_substr($field, 9);
					if ($propertyId > 0)
						$settings['element_properties'][$propertyId] = $propertyId;
					unset($select[$index]);
					unset($propertyId);
				}
			}
			unset($index, $field);
			unset($productFields);

			$settings['element'] = $select;
		}

		/**
		 * @param array $settings
		 * @param array $order
		 * @return void
		 */
		private static function prepareOrder(array &$settings, array $order)
		{
			if (!isset($order['by']) || !isset($order['order']))
				return;
			$rawOrder = [$order['by'] => $order['order']];
			$settings['rawOrder'] = $rawOrder;
			if (isset($rawOrder['PRICE']))
			{
				$settings['sortByPrice'] = true;
				$settings['prices'] = true;
				unset($rawOrder['PRICE']);
			}
			$settings['order'] = $rawOrder;
		}

		/**
		 * @return array
		 */
		protected function getProductFields()
		{
			if ($this->productFields === null)
			{
				$result = [];
				if ($this->catalogIncluded)
				{
					$result = Catalog\ProductTable::getEntity()->getScalarFields();
					unset($result['ID']);
					$result = array_fill_keys(array_keys($result), true);
				}
				$this->productFields = $result;
				unset($result);
			}
			return $this->productFields;
		}

		private function getFilterFieldsDescription()
		{
			$result = [];
			$result['NAME'] = [
				'id' => 'NAME',
				'type' => 'string',
				'operators' => [
					'default' => '%',
					'quickSearch' => '?'
				],
				'quickSearch' => true
			];
			$result['ID'] = [
				'id' => 'ID',
				'type' => 'number',
				'operators' => [
					'default' => '=',
					'exact' => '=',
					'range' => '><',
					'more' => '>',
					'less' => '<'
				]
			];
			$result['SECTION_ID'] = [
				'id' => 'SECTION_ID',
				'type' => 'list',
				'operators' => [
					'default' => '='
				]
			];
			$result['INCLUDE_SUBSECTIONS'] = [
				'id' => 'INCLUDE_SUBSECTIONS',
				'type' => 'checkbox',
				'operators' => [
					'default' => ''
				]
			];
			$result['ACTIVE'] = [
				'id' => 'ACTIVE',
				'type' => 'list',
				'operators' => [
					'default' => '='
				]
			];
			$result['XML_ID'] = [
				'id' => 'XML_ID',
				'type' => 'string',
				'operators' => [
					'default' => '='
				]
			];
			$result['CODE'] = [
				'id' => 'CODE',
				'type' => 'string',
				'operators' => [
					'default' => '='
				]
			];
			$result['ACTIVE_FROM'] = [
				'id' => 'ACTIVE_FROM',
				'type' => 'date',
				'operators' => [
					'default' => '=',
					'range' => '><'
				]
			];
			$result['ACTIVE_TO'] = [
				'id' => 'ACTIVE_TO',
				'type' => 'date',
				'operators' => [
					'default' => '=',
					'range' => '><'
				]
			];

			if ($this->catalogIncluded)
			{
				$result['TYPE'] = [
					'id' => 'TYPE',
					'type' => 'list',
					'operators' => [
						'default' => '='
					],
					'params' => ['multiple' => 'Y']
				];
				$result['BUNDLE'] = [
					'id' => 'BUNDLE',
					'type' => 'list',
					'operators' => [
						'default' => '='
					]
				];
				$result['AVAILABLE'] = [
					'id' => 'AVAILABLE',
					'type' => 'list',
					'operators' => [
						'default' => '='
					]
				];
			}
			if ($this->iblockId > 0)
			{
				$properties = $this->getFilterProductPropertiesDescription();
				if (!empty($properties))
					$result = array_merge($result, $properties);
				$properties = $this->getFilterOfferPropertiesDescription();
				if (!empty($properties))
					$result = array_merge($result, $properties);
				unset($properties);
			}

			return $result;
		}

		private static function getFilterPropertiesDescription(array $filter)
		{
			$result = [];

			$operators = [
				Iblock\PropertyTable::TYPE_STRING => [
					'default' => '?',
					'quickSearch' => '?'
				],
				Iblock\PropertyTable::TYPE_NUMBER => [
					'default' => '=',
					'exact' => '=',
					'range' => '><',
					'more' => '>',
					'less' => '<'
				],
				Iblock\PropertyTable::TYPE_LIST => [
					'default' => '='
				],
				Iblock\PropertyTable::TYPE_ELEMENT => [
					'default' => '='
				],
				Iblock\PropertyTable::TYPE_SECTION => [
					'default' => '='
				]
			];

			$iterator = Iblock\PropertyTable::getList([
				'select' => [
					'ID', 'IBLOCK_ID', 'NAME', 'SORT', 'PROPERTY_TYPE',
					'MULTIPLE', 'LINK_IBLOCK_ID', 'FILTRABLE', 'VERSION',
					'USER_TYPE', 'USER_TYPE_SETTINGS_LIST'
				],
				'filter' => $filter,
				'order' => ['SORT' => 'ASC', 'NAME' => 'ASC']
			]);
			while ($row = $iterator->fetch())
			{
				$row['USER_TYPE'] = (string)$row['USER_TYPE'];
				$row['PROPERTY_USER_TYPE'] = ($row['USER_TYPE'] !== '' ? \CIBlockProperty::GetUserType($row['USER_TYPE']) : []);
				$row['USER_TYPE_SETTINGS'] = $row['USER_TYPE_SETTINGS_LIST'];
				unset($row['USER_TYPE_SETTINGS_LIST']);

				$field = null;
				$id = 'PROPERTY_'.$row['ID'];
				$row['USER_TYPE'] = (string)$row['USER_TYPE'];
				$type = $row['PROPERTY_TYPE'];
				$settings = $row['USER_TYPE_SETTINGS'];
				if (
					$row['USER_TYPE'] !== ''
					&& !empty($settings)
					&& is_array($settings)
				)
				{
					$row['PROPERTY_USER_TYPE'] = \CIBlockProperty::GetUserType($row['USER_TYPE']);
					if (
						isset($row['PROPERTY_USER_TYPE']['GetUIFilterProperty'])
						&& is_callable($row['PROPERTY_USER_TYPE']['GetUIFilterProperty'])
					)
					{
						$type = 'USER_TYPE';
					}
				}
				switch ($type)
				{
					case 'USER_TYPE':
						$field = [
							'type' => 'custom',
							'value' => ''
						];
						if (isset($operators[$row['PROPERTY_TYPE']]))
							$field['operators'] = $operators[$row['PROPERTY_TYPE']];
						call_user_func_array(
							$row['PROPERTY_USER_TYPE']['GetUIFilterProperty'],
							[
								$row,
								['VALUE' => $id, 'FORM_NAME' => 'main-ui-filter'],
								&$field
							]
						);
						break;
					case Iblock\PropertyTable::TYPE_STRING:
						$field = [
							'type' => 'string',
							'operators' => $operators[Iblock\PropertyTable::TYPE_STRING],
						];
						break;
					case Iblock\PropertyTable::TYPE_NUMBER:
						$field = [
							'type' => 'number',
							'operators' => $operators[Iblock\PropertyTable::TYPE_NUMBER]
						];
						break;
					case Iblock\PropertyTable::TYPE_LIST:
						$field = [
							'type' => 'list',
							'operators' => $operators[Iblock\PropertyTable::TYPE_LIST]
						];
						break;
					case Iblock\PropertyTable::TYPE_ELEMENT:
						$field = [
							'type' => 'list',
							'operators' => $operators[Iblock\PropertyTable::TYPE_ELEMENT]
						];
						break;
					case Iblock\PropertyTable::TYPE_SECTION:
						$field = [
							'type' => 'list',
							'operators' => $operators[Iblock\PropertyTable::TYPE_SECTION]
						];
						break;
				}

				if (!empty($field))
				{
					$field['id'] = $id;
					$result[$id] = $field;
				}
			}
			unset($row, $iterator);

			return $result;
		}

		private function getFilterProductPropertiesDescription()
		{
			return self::getFilterPropertiesDescription([
				'=IBLOCK_ID' => $this->iblockId,
				'=ACTIVE' => 'Y',
				'=FILTRABLE' => 'Y',
				'!=PROPERTY_TYPE' => Iblock\PropertyTable::TYPE_FILE
			]);
		}

		private function getFilterOfferPropertiesDescription()
		{
			$result = [];
			if (!empty($this->catalog))
			{
				$result = self::getFilterPropertiesDescription([
					'=IBLOCK_ID' => $this->catalog['IBLOCK_ID'],
					'!=ID' => $this->catalog['SKU_PROPERTY_ID'],
					'=ACTIVE' => 'Y',
					'=FILTRABLE' => 'Y',
					'!=PROPERTY_TYPE' => Iblock\PropertyTable::TYPE_FILE
				]);
				if (!empty($result))
				{
					foreach (array_keys($result) as $index)
						$result[$index]['entity'] = 'offer';
					unset($index);
				}
			}
			return $result;
		}

		/**
		 * @param array $settings
		 * @return array
		 */
		private function getElementsInternal(array $settings)
		{
			$result = [];

			$iterator = \CIBlockElement::GetList(
				$settings['order'],
				$settings['filter'],
				false,
				($settings['limit'] > 0 ? ['nTopCount' => $settings['limit']] : false),
				$settings['element']
			);
			if (!empty($settings['templates']['detailPageUrl']))
				$iterator->SetUrlTemplates($settings['templates']['detailPageUrl']);

			$needPreview = in_array('PREVIEW_PICTURE', $settings['element']);
			$needDetailPicture = in_array('DETAIL_PICTURE', $settings['element']);

			$loadSeo = ($needPreview || $needDetailPicture || $settings['loadSeo']);

			if (!empty($settings['element_properties']))
			{
				$settings['element_properties'] = $this->getAllowedPropertyId(
					$settings['filter']['IBLOCK_ID'],
					$settings['element_properties'],
					($settings['mode'] == 'list'
						? Iblock\Model\PropertyFeature::FEATURE_ID_LIST_PAGE_SHOW
						: Iblock\Model\PropertyFeature::FEATURE_ID_DETAIL_PAGE_SHOW
					)
				);
			}

			while ($row = $iterator->GetNext())
			{
				$id = (int)$row['ID'];
				if ($loadSeo)
				{
					Iblock\InheritedProperty\ElementValues::queue($row['IBLOCK_ID'], $row['ID']);
				}

				$row['PROPERTIES'] = [];

				$result[$id] = $row;
			}
			unset($row, $iterator);

			if (!empty($result))
			{
				$needDiscountCache = false;
				if ($this->catalogIncluded && $settings['prices'])
				{
					$needDiscountCache = \CIBlockPriceTools::SetCatalogDiscountCache(
						$this->getPriceTypes(),
						$this->getUserGroups()
					);
				}
				$loadProperties = (!empty($settings['element_properties']) || $needDiscountCache);
				if ($loadProperties)
				{
					$propertyFilter = [];
					if (!$needDiscountCache)
					{
						$propertyFilter = ['ID' => $settings['element_properties']];
					}

					\CIBlockElement::GetPropertyValuesArray(
						$result,
						$settings['filter']['IBLOCK_ID'],
						['ID' => array_keys($result)],
						$propertyFilter,
						['USE_PROPERTY_ID' => 'Y']
					);
					unset($propertyFilter);
				}
				if ($needDiscountCache)
				{
					$elementIds = array_keys($result);
					foreach ($elementIds as $itemId)
					{
						\CCatalogDiscount::SetProductPropertiesCache($itemId, $result[$itemId]['PROPERTIES']);
					}
					Catalog\Discount\DiscountManager::preloadPriceData($elementIds, $this->getPriceTypes());
					Catalog\Discount\DiscountManager::preloadProductDataToExtendOrder($elementIds, $this->getUserGroups());
					\CCatalogDiscount::SetProductSectionsCache($elementIds);
				}

				foreach ($result as &$row)
				{
					if ($loadSeo)
					{
						$ipropValues = new Iblock\InheritedProperty\ElementValues($row['IBLOCK_ID'], $row['ID']);
						$row['IPROPERTY_VALUES'] = $ipropValues->getValues();
					}
					if ($needPreview || $needDetailPicture)
					{
						Iblock\Component\Tools::getFieldImageData(
							$row,
							['PREVIEW_PICTURE', 'DETAIL_PICTURE'],
							Iblock\Component\Tools::IPROPERTY_ENTITY_ELEMENT,
							'IPROPERTY_VALUES'
						);
						if ($needPreview)
						{
							if (!empty($row['PREVIEW_PICTURE']))
							{
								$row['PREVIEW_PICTURE'] = [
									'src' => $row['PREVIEW_PICTURE']['SRC'],
									'alt' => $row['PREVIEW_PICTURE']['ALT'],
								];
							}
						}
						if ($needDetailPicture)
						{
							if (!empty($row['DETAIL_PICTURE']))
							{
								$row['DETAIL_PICTURE'] = [
									'src' => $row['DETAIL_PICTURE']['SRC'],
									'alt' => $row['DETAIL_PICTURE']['ALT'],
								];
							}
						}
					}

					if (!empty($settings['element_properties']))
					{
						foreach ($settings['element_properties'] as $propertyId)
						{
							$row['PROPERTY_'.$propertyId] = null;
							if (isset($row['PROPERTIES'][$propertyId]))
							{
								$row['PROPERTY_'.$propertyId] = self::getPropertyValue($row['PROPERTIES'][$propertyId]);
							}
						}
						unset($propertyId);
					}
					unset($row['PROPERTIES']);

					if ($settings['prices'])
					{
						$row['PRICE'] = null;
						$row['SORT_ORDER'] = null;
					}

					if ($settings['loadSeo'])
					{
						$this->fillElementSeo($row);
					}

					if ($loadSeo)
					{
						unset($row['IPROPERTY_VALUES']);
						unset($ipropValues);
					}
				}
				unset($row);

				if ($this->catalogIncluded)
				{
					if (!empty($settings['product']))
					{
						self::loadProduct($result, $settings['product']);
					}

					if ($settings['prices'])
					{
						$this->loadPrices($result);
						if (!empty($settings['sortByPrice']))
						{
							$sortPrice = mb_strtoupper($settings['sortByPrice']);
							$sortPrice = ($sortPrice === 'ASC' ? SORT_ASC : SORT_DESC);
							Main\Type\Collection::sortByColumn($result, ['SORT_ORDER' => $sortPrice]);
						}
						foreach ($result as &$item)
						{
							unset($item['SORT_ORDER']);
						}
					}
					if ($needDiscountCache)
					{
						\CCatalogDiscount::ClearDiscountCache(array(
							'PRODUCT' => true,
							'SECTIONS' => true,
							'PROPERTIES' => true
						));
					}
				}
			}

			return $result;
		}

		/**
		 * @param array $property
		 * @return mixed|null
		 */
		private static function getPropertyValue(array $property)
		{
			$result = null;

			$multiValue = is_array($property['VALUE']);
			if (
				($multiValue && !empty($property['VALUE'])) ||
				(!$multiValue && (string)$property['VALUE'] !== '')
			)
			{
				if ($property['PROPERTY_TYPE'] == Iblock\PropertyTable::TYPE_FILE)
				{
					if ($multiValue)
					{
						foreach ($property['VALUE'] as $value)
						{
							$file = \CFile::GetFileArray($value);
							if (!empty($file))
							{
								$result = [
									'src' => $file['SRC'],
									'alt' => ''
								];
								break;
							}
						}
					}
					else
					{
						$file = \CFile::GetFileArray($property['VALUE']);
						if (!empty($file))
						{
							$result = [
								'src' => $file['SRC'],
								'alt' => ''
							];
						}
					}
				}
				else
				{
					$result = \CIBlockFormatProperties::GetDisplayValue(['NAME' => ''], $property, '');

					if (empty($result))
					{
						$result = null;
					}
					elseif (is_array($result))
					{
						if (empty($result['DISPLAY_VALUE']))
							$result = null;
						elseif (is_array($result['DISPLAY_VALUE']))
							$result = implode(', ', $result['DISPLAY_VALUE']);
						else
							$result = $result['DISPLAY_VALUE'];
					}
				}
			}

			return $result;
		}

		private static function loadProduct(array &$result, array $fields)
		{
			$select = ['ID'];
			$descr = [];

			foreach ($fields as $index => $item)
			{
				if (is_array($item))
				{
					if (isset($item['COMPILE']))
					{
						$select = array_merge($select, $item['COMPILE']['FIELDS']);
					}
					else
					{
						$select[] = $item['ID'];
					}
					$descr[$index] = $item;
				}
				elseif (is_bool($item))
				{
					$select[] = $index;
					$descr[$index] = ['ID' => $index];
				}
			}

			$iterator = Catalog\ProductTable::getList([
				'select' => $select,
				'filter' => ['@ID' => array_keys($result)]
			]);
			while ($row = $iterator->fetch())
			{
				$id = (int)$row['ID'];
				foreach ($descr as $item)
				{
					$fieldId = $item['ID'];
					$result[$id][$fieldId] = null;
					if (isset($item['COMPILE']))
					{
						$value = [];
						foreach ($item['COMPILE']['FIELDS'] as $index)
						{
							if ($row[$index] === null)
								continue;
							$value[] = $row[$index];
						}
						if (!empty($value))
							$result[$id][$fieldId] = implode($item['COMPILE']['SEPARATOR'], $value);
					}
					else
					{
						if ($fieldId == 'AVAILABLE')
							$row[$fieldId] = ($row[$fieldId] == 'Y'
								? Loc::getMessage('PRODUCT_FIELD_STATUS_YES')
								: Loc::getMessage('PRODUCT_FIELD_STATUS_NO')
							);
						$result[$id][$fieldId] = $row[$fieldId];
					}
				}
			}
			unset($row, $iterator);
		}

		private function loadPrices(array &$result)
		{
			$priceTypes = $this->getPriceTypes();
			if (!empty($priceTypes))
			{
				$productIds = array_keys($result);
				$prices = [];
				$priceFilter = [
					'@PRODUCT_ID' => array_keys($result),
					'@CATALOG_GROUP_ID' => $priceTypes,
					[
						'LOGIC' => 'OR',
						'<=QUANTITY_FROM' => 1,
						'=QUANTITY_FROM' => null
					],
					[
						'LOGIC' => 'OR',
						'>=QUANTITY_TO' => 1,
						'=QUANTITY_TO' => null
					]
				];

				$iterator = Catalog\PriceTable::getList([
					'select' => ['ID', 'PRODUCT_ID', 'CATALOG_GROUP_ID', 'PRICE', 'CURRENCY'],
					'filter' => $priceFilter
				]);

				while ($price = $iterator->fetch())
				{
					$id = (int)$price['PRODUCT_ID'];
					$priceTypeId = (int)$price['CATALOG_GROUP_ID'];
					$prices[$id][$priceTypeId] = $price;
					unset($priceTypeId, $id);
				}
				unset($price, $iterator);

				$calculationConfig = [
					'CURRENCY' => Currency\CurrencyManager::getBaseCurrency(),
					'USE_DISCOUNTS' => true,
					'RESULT_WITH_VAT' => true,
					'RESULT_MODE' => Catalog\Product\Price\Calculation::RESULT_MODE_COMPONENT
				];

				if ($this->saleIncluded)
				{
					$saleDiscountOnly = (string)Main\Config\Option::get('sale', 'use_sale_discount_only') == 'Y';
					if ($saleDiscountOnly)
						$calculationConfig['PRECISION'] = (int)Main\Config\Option::get('sale', 'value_precision');
				}
				Catalog\Product\Price\Calculation::pushConfig();
				Catalog\Product\Price\Calculation::setConfig($calculationConfig);
				unset($calculationConfig);

				foreach ($productIds as $id)
				{
					$minimalPrice = \CCatalogProduct::GetOptimalPrice(
						$id,
						1,
						[2],
						'N',
						$prices[$id],
						false,
						[]
					);
					$result[$id]['SORT_ORDER'] = 0;
					if (!empty($minimalPrice))
					{
						$minimalPrice = $minimalPrice['RESULT_PRICE'];
						$result[$id]['PRICE'] = \CCurrencyLang::CurrencyFormat(
							$minimalPrice['DISCOUNT_PRICE'],
							$minimalPrice['CURRENCY'],
							true
						);
						if ($minimalPrice['BASE_PRICE'] > $minimalPrice['DISCOUNT_PRICE'])
						{
							$result[$id]['PRICE'] .= ' <span style="text-decoration: line-through;">'.
								\CCurrencyLang::CurrencyFormat(
									$minimalPrice['BASE_PRICE'],
									$minimalPrice['CURRENCY'],
									true
								).
								'</span>';
						}
						$result[$id]['SORT_ORDER'] = $minimalPrice['DISCOUNT_PRICE'];
					}
					unset($minimalPrice);
				}

				Catalog\Product\Price\Calculation::popConfig();
			}
			unset($priceTypes);
		}

		private function fillElementSeo(array &$row)
		{
			$iproperty = (!empty($row['IPROPERTY_VALUES']) ? $row['IPROPERTY_VALUES'] : []);
			$row['SEO_PROPERTIES'] = [
				Source\Seo::TITLE => [isset($iproperty['ELEMENT_PAGE_TITLE']) && $iproperty['ELEMENT_PAGE_TITLE'] != ''
					? $iproperty['ELEMENT_PAGE_TITLE']
					: $row['NAME']
				],
			];

			if (!empty($iproperty))
			{
				$entity = [
					Source\Seo::BROWSER_TITLE => 'ELEMENT_META_TITLE',
					Source\Seo::KEYWORDS => 'ELEMENT_META_KEYWORDS',
					Source\Seo::DESCRIPTION => 'ELEMENT_META_DESCRIPTION'
				];

				foreach ($entity as $seoItem => $meta)
				{
					if (!empty($iproperty[$meta]))
					{
						$row['SEO_PROPERTIES'][$seoItem] = $iproperty[$meta];
						if (is_array($row['SEO_PROPERTIES'][$seoItem]))
						{
							$row['SEO_PROPERTIES'][$seoItem] = implode(' ', $row['SEO_PROPERTIES'][$seoItem]);
						}
					}
				}
				unset($entity, $seoItem, $meta);
			}
			unset($iproperty);
		}

		/**
		 * @param int $iblockId
		 * @param array $propertyIds
		 * @param string $mode
		 * @return array
		 */
		private function getAllowedPropertyId($iblockId, array $propertyIds, $mode)
		{
			$list = null;
			switch ($mode)
			{
				case Iblock\Model\PropertyFeature::FEATURE_ID_LIST_PAGE_SHOW:
					$list = Iblock\Model\PropertyFeature::getListPageShowPropertyCodes($iblockId);
					break;
				case Iblock\Model\PropertyFeature::FEATURE_ID_DETAIL_PAGE_SHOW:
					$list = Iblock\Model\PropertyFeature::getDetailPageShowPropertyCodes($iblockId);
					break;
			}
			if (empty($list))
			{
				return [];
			}

			$result = array_intersect_key(
				array_fill_keys($propertyIds, true),
				array_fill_keys($list, true)
			);

			return (!empty($result) ? array_keys($result) : []);
		}

		/**
		 * Return user groups. Now worked only with current user.
		 *
		 * @return array
		 */
		protected function getUserGroups()
		{
			/** @global \CUser $USER */
			global $USER;
			$result = [2];
			if (isset($USER) && $USER instanceof \CUser)
			{
				$result = $USER->GetUserGroupArray();
				Main\Type\Collection::normalizeArrayValuesByInt($result, true);
			}
			return $result;
		}

		/**
		 * @return array
		 */
		protected function getPriceTypes()
		{
			if ($this->priceTypes === null)
			{
				$this->priceTypes = [];
				$iterator = Catalog\GroupAccessTable::getList([
					'select' => ['CATALOG_GROUP_ID'],
					'filter' => ['=GROUP_ID' => 2]
				]);
				while ($row = $iterator->fetch())
				{
					$id = (int)$row['CATALOG_GROUP_ID'];
					$this->priceTypes[$id] = $id;
				}
				unset($row, $iterator);
			}
			return $this->priceTypes;
		}
	}
}