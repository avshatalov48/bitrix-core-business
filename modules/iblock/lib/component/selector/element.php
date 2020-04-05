<?php
namespace Bitrix\Iblock\Component\Selector;

use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Iblock,
	Bitrix\Catalog;

Loc::loadMessages(__FILE__);

class Element extends Entity
{
	protected $catalogIncluded = null;

	/** @var array Element property list */
	protected $elementProperties = null;

	/** @var array Offer property list */
	protected $offerProperties = null;

	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->useImplicitPageNavigation();
	}

	/**
	 * @return void
	 */
	public function onIncludeComponentLang()
	{
		Loc::loadMessages(__FILE__);
	}

	/**
	 * @return void
	 */
	protected function checkModules()
	{
		if ($this->catalogIncluded === null)
			$this->catalogIncluded = Loader::includeModule('catalog');
	}

	/**
	 * @return void
	 */
	protected function initEntitySettings()
	{
		parent::initEntitySettings();
		if (!$this->catalogIncluded)
			return;
		$iblockId = (int)$this->getStorageItem(self::STORAGE_ENTITY_IBLOCK, 'IBLOCK_ID');
		if ($iblockId > 0)
		{
			$description = [
				'CATALOG_TYPE' => null,
				'OFFERS_IBLOCK_ID' => null,
				'SKU_PROPERTY_ID' => null,
			];
			$catalog = \CCatalogSku::GetInfoByIBlock($iblockId);
			if (!empty($catalog))
			{
				$description['CATALOG_TYPE'] = $catalog['CATALOG_TYPE'];
				if (
					$catalog['CATALOG_TYPE'] == \CCatalogSku::TYPE_FULL
					|| $catalog['CATALOG_TYPE'] == \CCatalogSku::TYPE_PRODUCT
				)
				{
					$description['OFFERS_IBLOCK_ID'] = $catalog['IBLOCK_ID'];
					$description['SKU_PROPERTY_ID'] = $catalog['SKU_PROPERTY_ID'];
				}
				$description['FILTER_ALL'] = Loc::getMessage('IBLOCK_SELECTOR_ELEMENT_SLIDER_FILTER_ALL_PRODUCTS');

			}
			unset($catalog);
			$this->fillStorageNode(self::STORAGE_ENTITY_IBLOCK, $description);
			unset($description);
		}
		unset($iblockId);
	}

	/**
	 * @return array
	 */
	protected function getGridFilterDefinition()
	{
		$result = parent::getGridFilterDefinition();

		$sectionFilter = $this->getSectionFilterDefinition();
		if (!empty($sectionFilter))
		{
			$newResult = [];
			foreach ($result as $id => $row)
			{
				$newResult[$id] = $row;
				if ($id == 'ID')
				{
					foreach (array_keys($sectionFilter) as $index)
						$newResult[$index] = $sectionFilter[$index];
					unset($index);
				}
			}
			unset($id, $row);
			$result = $newResult;
			unset($newResult);
		}
		unset($sectionFilter);

		$result = array_merge(
			$result,
			$this->getElementFieldsFilterDefinition()
		);

		$result = array_merge(
			$result,
			$this->getProductFieldsFilterDefinition()
		);

		$result = array_merge(
			$result,
			$this->getElementPropertyFilterDefinition()
		);
		$result = array_merge(
			$result,
			$this->getOfferPropertyFilterDefinition()
		);

		return $result;
	}

	/**
	 * @return array
	 */
	protected function getGridColumnsDescription()
	{
		$result = array_merge(
			parent::getGridColumnsDescription(),
			$this->getElementFieldsDescription()
		);

		$result = array_merge(
			$result,
			$this->getProductFieldsDescription()
		);

		$properties = $this->getElementPropertiesDescription();
		if (!empty($properties))
			$result = array_merge($result, $properties);
		unset($properties);

		return $result;
	}

	/**
	 * @param array $filter
	 * @return array
	 */
	protected function compileUserFilter(array $filter)
	{
		$result = parent::compileUserFilter($filter);

		if ($this->catalogIncluded)
		{
			$iblockId = (int)$this->getStorageItem(self::STORAGE_ENTITY_IBLOCK, 'OFFERS_IBLOCK_ID');
			$skuPropertyId = (int)$this->getStorageItem(self::STORAGE_ENTITY_IBLOCK, 'SKU_PROPERTY_ID');

			if ($iblockId > 0 && $skuPropertyId > 0)
			{
				if (isset($filter['offer']))
				{
					$subFilter = $filter['offer'];
					$subFilter['IBLOCK_ID'] = $iblockId;
					$result['=ID'] = \CIBlockElement::SubQuery('PROPERTY_'.$skuPropertyId, $subFilter);
					unset($subFilter);
				}
			}
		}

		return $result;
	}

	/**
	 * @return array
	 */
	protected function getClientExtensions()
	{
		return array_merge(
			parent::getClientExtensions(),
			['date']
		);
	}

	/**
	 * @return array
	 */
	protected function getDataOrder()
	{
		$result = parent::getDataOrder();

		if (!isset($result['ID']))
		{
			if (isset($result['TYPE']))
			{
				$result['BUNDLE'] = $result['TYPE'];
			}
			$result['ID'] = 'ASC';
		}

		return $result;
	}

	/**
	 * @return void
	 */
	protected function getData()
	{
		Loader::includeModule('fileman');

		$this->rows = [];

		$listImageSize = Main\Config\Option::get('iblock', 'list_image_size');
		$minImageSize = ['W' => 1, 'H' => 1];
		$maxImageSize = ['W' => $listImageSize, 'H' => $listImageSize];
		unset($listImageSize);

		$binaryStates = $this->getBinaryDictionary();

		$allowedSets = Catalog\Config\Feature::isProductSetsEnabled();

		$this->loadElementPropertiesDescription();

		$viewedColumns = $this->getDataFields();
		$selectedFields = [];
		$selectedProperties = [];
		foreach ($viewedColumns as $columnId)
		{
			if (strncmp($columnId, 'PROPERTY_', 9) == 0)
			{
				if (isset($this->elementProperties[$columnId]))
				{
					$selectedProperties[] = $this->elementProperties[$columnId]['ID'];
				}
			}
			else
			{
				$selectedFields[] = $columnId;
				if ($columnId == 'TYPE')
					$selectedFields[] = 'BUNDLE';
			}
		}
		unset($columnId);
		if (!in_array('ID', $selectedFields))
			$selectedFields[] = 'ID';

		$viewedColumns = array_fill_keys($viewedColumns, true);

		$iblockId = (int)$this->getStorageItem(self::STORAGE_ENTITY_IBLOCK, 'IBLOCK_ID');
		$needProperties = $iblockId > 0 && !empty($selectedProperties);

		$productTypeList = [];
		if ($this->catalogIncluded)
			$productTypeList = Catalog\ProductTable::getProductTypes(true);

		$listIds = [];

		$iterator = \CIBlockElement::GetList(
			$this->getDataOrder(),
			$this->getDataFilter(),
			false,
			$this->getGridNavigationParams(),
			$selectedFields
		);
		$iterator->bShowAll = false;
		while ($row = $iterator->Fetch())
		{
			$row['ID'] = (int)$row['ID'];

			if (isset($viewedColumns['ACTIVE']))
				$row['ACTIVE'] = ($row['ACTIVE'] == 'Y' ? $binaryStates['Y'] : $binaryStates['N']);
			if (isset($viewedColumns['NAME']))
				$row['NAME'] = htmlspecialcharsEx((string)$row['NAME']);
			if (isset($viewedColumns['SORT']))
				$row['SORT'] = (int)$row['SORT'];
			if (isset($viewedColumns['CODE']))
				$row['CODE'] = htmlspecialcharsbx((string)$row['CODE']);
			if (isset($viewedColumns['XML_ID']))
				$row['XML_ID'] = htmlspecialcharsbx((string)$row['XML_ID']);
			if (isset($viewedColumns['ACTIVE_FROM']))
				$row['ACTIVE_FROM'] = htmlspecialcharsbx((string)$row['ACTIVE_FROM']);
			if (isset($viewedColumns['ACTIVE_TO']))
				$row['ACTIVE_TO'] = htmlspecialcharsbx((string)$row['ACTIVE_TO']);

			if (isset($viewedColumns['TYPE']))
			{
				if ($row['TYPE'] === null)
				{
					$row['TYPE'] = Loc::getMessage('IBLOCK_SELECTOR_ELEMENT_MESS_IS_NOT_PRODUCT');
				}
				else
				{
					$productType = (int)$row['TYPE'];
					if (isset($productTypeList[$productType]))
					{
						$row['TYPE'] = $productTypeList[$productType];
					}
					else
					{
						$row['TYPE'] = Loc::getMessage('IBLOCK_SELECTOR_ELEMENT_MESS_BAD_PRODUCT_TYPE');
					}
					if ($allowedSets && $row['BUNDLE'] == 'Y')
					{
						$row['TYPE'] = Loc::getMessage(
							'IBLOCK_SELECTOR_ELEMENT_MESS_TYPE_DESCRIPTION',
							['#TYPE#' => $row['TYPE']]
						);
					}
				}
				unset($row['BUNDLE']);
			}
			if (isset($viewedColumns['AVAILABLE']))
				$row['AVAILABLE'] = ($row['AVAILABLE'] == 'Y' ? $binaryStates['Y'] : $binaryStates['N']);

			if ($needProperties)
				$row['PROPERTIES'] = [];
			$this->rows[$row['ID']] = $row;
			$listIds[] = $row['ID'];
		}
		unset($row);

		if (!empty($this->rows) && $needProperties)
		{
			\CIBlockElement::GetPropertyValuesArray(
				$this->rows,
				$iblockId,
				['ID' => $listIds],
				['ID' => $selectedProperties],
				[
					'USE_PROPERTY_ID' => 'Y',
					'PROPERTY_FIELDS' => ['ID'],
					'GET_RAW_DATA' => 'Y'
				]
			);

			$elementCache = [];
			$sectionCache = [];

			foreach ($listIds as $id)
			{
				foreach ($selectedProperties as $propertyId)
				{
					$viewedValue = '';
					$index = 'PROPERTY_'.$propertyId;
					if (isset($this->rows[$id]['PROPERTIES'][$propertyId]))
					{
						$property = $this->elementProperties[$index];
						$value = $this->rows[$id]['PROPERTIES'][$propertyId];
						$complexValue = is_array($value['VALUE']);
						if (
							($complexValue && !empty($value['VALUE']))
							|| (!$complexValue && (string)$value['VALUE'] !== '')
						)
						{
							$direct = false;
							$assembly = [];
							$userType = $property['PROPERTY_USER_TYPE'];
							if (isset($userType['GetAdminListViewHTML']))
							{
								$rawValue = $value['VALUE'];
								if ($property['MULTIPLE'] == 'N' || !$complexValue)
									$rawValue = [$rawValue];
								foreach ($rawValue as $item)
								{
									$itemValue = (string)call_user_func_array(
										$userType['GetAdminListViewHTML'],
										[
											$property,
											[
												'VALUE' => $item,
												'DESCRIPTION' => ''
											],
											[
												'MODE' => 'iblock_element_admin',
												'FORM_NAME' => ''
											]
										]
									);
									if ($itemValue !== '')
										$assembly[] = $itemValue;
								}
							}
							else
							{
								$rawValue = $value['VALUE'];
								if (!$complexValue)
									$rawValue = [$rawValue];

								switch ($this->elementProperties[$index]['PROPERTY_TYPE'])
								{
									case Iblock\PropertyTable::TYPE_STRING:
									case Iblock\PropertyTable::TYPE_NUMBER:
									case Iblock\PropertyTable::TYPE_LIST:
										$assembly = $rawValue;
										break;
									case Iblock\PropertyTable::TYPE_ELEMENT:
										foreach ($rawValue as $item)
										{
											if (!isset($elementCache[$item]))
											{
												$elementCache[$item] = '';
												$valueIterator = Iblock\ElementTable::getList([
													'select' => ['ID', 'NAME'],
													'filter' => ['=ID' => $item]
												]);
												$valueRow = $valueIterator->fetch();
												if (!empty($valueRow))
													$elementCache[$item] = '['.$valueRow['ID'].'] '.$valueRow['NAME'];
												unset($valueRow, $valueIterator);
											}
											if ($elementCache[$item] !== '')
												$assembly[] = $elementCache[$item];
										}
										break;
									case Iblock\PropertyTable::TYPE_SECTION:
										foreach ($rawValue as $item)
										{
											if (!isset($sectionCache[$item]))
											{
												$sectionCache[$item] = '';
												$valueIterator = Iblock\SectionTable::getList([
													'select' => ['ID', 'NAME'],
													'filter' => ['=ID' => $item]
												]);
												$valueRow = $valueIterator->fetch();
												if (!empty($valueRow))
													$sectionCache[$item] = '['.$valueRow['ID'].'] '.$valueRow['NAME'];
												unset($valueRow, $valueIterator);
											}
											if ($sectionCache[$item] !== '')
												$assembly[] = $sectionCache[$item];
										}
										break;
									case Iblock\PropertyTable::TYPE_FILE:
										$direct = true;
										/*										$fileInputParams = [
																					'edit' => false,
																					'description' => true,
																					'upload' => false,
																					'medialib' => false,
																					'fileDialog' => false,
																					'cloud' => false,
																					'delete' => false,
																				];
																				if ($property['MULTIPLE'] == 'Y')
																				{
																					$fileInputParams['name'] = $index.'[#IND#]';
																				}
																				else
																				{
																					$fileInputParams['name'] = $index;
																					$fileInputParams['maxCount'] = 1;
																					$rawValue = reset($rawValue);
																				}
																				$assembly = UI\FileInput::createInstance($fileInputParams)->show(
																					$rawValue,
																					false
																				);
																				unset($fileInputParams); */
										$fileInputParams = [
											'upload' => false,
											'medialib' => false,
											'file_dialog' => false,
											'cloud' => false,
											'del' => false,
											'description' => false
										];
										$fileOptions = [
											'IMAGE' => 'Y',
											'PATH' => 'N',
											'FILE_SIZE' => 'N',
											'DIMENSIONS' => 'N',
											'IMAGE_POPUP' => 'Y',
											'MAX_SIZE' => $maxImageSize,
											'MIN_SIZE' => $minImageSize,
										];
										if ($property['MULTIPLE'] == 'Y')
										{
											$assembly = \CFileInput::ShowMultiple(
												$rawValue,
												$index.'[#IND#]',
												$fileOptions,
												false,
												$fileInputParams
											);
										}
										else
										{
											$rawValue = reset($rawValue);
											$assembly = \CFileInput::Show(
												$index,
												$rawValue,
												$fileOptions,
												$fileInputParams
											);
										}
										break;
								}
							}
							if (!empty($assembly))
							{
								$viewedValue = ($direct ? $assembly : htmlspecialcharsbx(implode(' / ', $assembly)));
							}
							unset($direct, $assembly);
						}
					}
					$this->rows[$id][$index] = $viewedValue;
					unset($viewedValue);
				}
				unset($this->rows[$id]['PROPERTIES']);
			}

			unset($sectionCache, $elementCache);
		}
		if (!empty($this->rows))
			$this->rows = array_values($this->rows);

		$this->setImplicitNavigationData($iterator);
		unset($iterator);
	}

	/**
	 * @return string
	 */
	protected function getNavigationTitle()
	{
		$title = $this->getStorageItem(self::STORAGE_ENTITY_IBLOCK, 'IBLOCK_ELEMENTS_NAME');
		if (!empty($title))
			return $title;
		return Loc::getMessage('IBLOCK_SELECTOR_ELEMENT_GRID_PAGENAVIGATION_TITLE');
	}

	/**
	 * @return array
	 */
	protected function getInternalFilter()
	{
		$filter = parent::getInternalFilter();

		$filter['CHECK_PERMISSIONS'] = 'Y';
		$filter['MIN_PERMISSION'] = 'R';

		return $filter;
	}

	/**
	 * @return array
	 */
	protected function getSectionFilterDefinition()
	{
		$result = [];

		if ($this->getStorageItem(self::STORAGE_ENTITY_IBLOCK, 'IBLOCK_SECTIONS') == 'Y')
		{
			$list = [];
			$iterator = \CIBlockSection::GetList(
				['LEFT_MARGIN' => 'ASC'],
				[
					'IBLOCK_ID' => $this->getStorageItem(self::STORAGE_ENTITY_IBLOCK, 'IBLOCK_ID'),
					'ACTIVE' => 'Y',
					'GLOBAL_ACTIVE' => 'Y',
					'CHECK_PERMISSIONS' => 'Y',
					'MIN_PERMISSION' => 'R'
				],
				false,
				['ID', 'NAME', 'IBLOCK_ID', 'DEPTH_LEVEL', 'LEFT_MARGIN']
			);
			while ($row = $iterator->Fetch())
			{
				$list[$row['ID']] = str_repeat('.', $row['DEPTH_LEVEL'] - 1).$row['NAME'];
			}
			unset($row, $iterator);
			$result = [
				'SECTION_ID' => [
					'id' => 'SECTION_ID',
					'name' => Loc::getMessage('IBLOCK_SELECTOR_ELEMENT_FILTER_FIELD_SECTION_ID'),
					'type' => 'list',
					'items' => $list,
					'operators' => [
						'default' => '='
					],
					'default' => true
				],
				'INCLUDE_SUBSECTIONS' => [
					'id' => 'INCLUDE_SUBSECTIONS',
					'name' => Loc::getMessage('IBLOCK_SELECTOR_ELEMENT_FILTER_FIELD_INCLUDE_SUBSECTIONS'),
					'type' => 'checkbox',
					'operators' => [
						'default' => ''
					],
					'default' => true
				]
			];
			unset($list);
		}

		return $result;
	}

	/**
	 * @return array
	 */
	protected function getElementFieldsFilterDefinition()
	{
		$result = [];

		$result['ACTIVE_FROM'] = [
			'id' => 'ACTIVE_FROM',
			'name' => Loc::getMessage('IBLOCK_SELECTOR_ELEMENT_GRID_COLUMN_ACTIVE_FROM'),
			'type' => 'date',
			'operators' => [
				'default' => '=',
				'range' => '><'
			]
		];
		$result['ACTIVE_TO'] = [
			'id' => 'ACTIVE_TO',
			'name' => Loc::getMessage('IBLOCK_SELECTOR_ELEMENT_GRID_COLUMN_ACTIVE_TO'),
			'type' => 'date',
			'operators' => [
				'default' => '=',
				'range' => '><'
			]
		];

		return $result;
	}

	/**
	 * @return array
	 */
	protected function getProductFieldsFilterDefinition()
	{
		$result = [];

		$binaryStates = $this->getBinaryDictionary();

		if (
			$this->catalogIncluded
			&& (string)$this->getStorageItem(self::STORAGE_ENTITY_IBLOCK, 'CATALOG_TYPE') !== ''
		)
		{
			$result['TYPE'] = [
				'id' => 'TYPE',
				'name' => Loc::getMessage('IBLOCK_SELECTOR_ELEMENT_GRID_COLUMN_TYPE'),
				'type' => 'list',
				'items' => \CCatalogAdminTools::getIblockProductTypeList(
					$this->getStorageItem(self::STORAGE_ENTITY_IBLOCK, 'IBLOCK_ID'),
					true
				),
				'params' => ['multiple' => 'Y'],
				'operators' => [
					'default' => '=',
					'exact' => '=',
					'enum' => '@'
				]
			];
			if (Catalog\Config\Feature::isProductSetsEnabled())
			{
				$result['BUNDLE'] = [
					'id' => 'BUNDLE',
					'name' => Loc::getMessage('IBLOCK_SELECTOR_ELEMENT_FILTER_FIELD_BUNDLE'),
					'type' => 'list',
					'items' => $binaryStates,
					'operators' => [
						'default' => '='
					]
				];
			}
			$result['AVAILABLE'] = [
				'id' => 'AVAILABLE',
				'name' => Loc::getMessage('IBLOCK_SELECTOR_ELEMENT_GRID_COLUMN_AVAILABLE'),
				'type' => 'list',
				'items' => $binaryStates,
				'operators' => [
					'default' => '='
				]
			];
		}

		return $result;
	}

	/**
	 * @return array
	 */
	protected function getElementPropertyFilterDefinition()
	{
		$result = [];

		$this->loadElementPropertiesDescription();
		if (!empty($this->elementProperties))
			$result = $this->compileFilterProperties($this->elementProperties);

		return $result;
	}

	/**
	 * @return array
	 */
	protected function getOfferPropertyFilterDefinition()
	{
		$result = [];

		$this->loadOfferPropertiesDescription();
		if (!empty($this->offerProperties))
		{
			$result = $this->compileFilterProperties($this->offerProperties);
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
	 * @param array $list
	 * @return array
	 */
	protected function compileFilterProperties(array $list)
	{
		$result = [];
		if (empty($list))
			return $result;

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
				'default' => '=',
				'exact' => '='
			],
			Iblock\PropertyTable::TYPE_ELEMENT => [
				'default' => '=',
				'exact' => '='
			],
			Iblock\PropertyTable::TYPE_SECTION => [
				'default' => '=',
				'exact' => '='
			]
		];

		$list = array_filter($list, [__CLASS__, 'isFilterableProperty']);
		foreach ($list as $id => $row)
		{
			$field = null;
			$type = $row['PROPERTY_TYPE'];
			if (
				$row['USER_TYPE'] !== ''
				&& isset($row['PROPERTY_USER_TYPE']['GetUIFilterProperty'])
				&& is_callable($row['PROPERTY_USER_TYPE']['GetUIFilterProperty'])
			)
			{
				$type = 'USER_TYPE';
			}
			switch ($type)
			{
				case 'USER_TYPE':
					$field = [
						'id' => $id,
						'name' => $row['NAME'],
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
						'id' => $id,
						'name' => $row['NAME'],
						'type' => 'string',
						'operators' => $operators[Iblock\PropertyTable::TYPE_STRING],
					];
					break;
				case Iblock\PropertyTable::TYPE_NUMBER:
					$field = [
						'id' => $id,
						'name' => $row['NAME'],
						'type' => 'number',
						'operators' => $operators[Iblock\PropertyTable::TYPE_NUMBER]
					];
					break;
				case Iblock\PropertyTable::TYPE_LIST:
					$list = [];
					$valueIterator = Iblock\PropertyEnumerationTable::getList([
						'select' => ['ID', 'VALUE', 'SORT'],
						'filter' => ['=PROPERTY_ID' => $row['ID']],
						'order' => ['SORT' => 'ASC', 'VALUE' => 'ASC']
					]);
					while ($value = $valueIterator->fetch())
						$list[$value['ID']] = $value['VALUE'];
					unset($value, $valueIterator);
					if (!empty($list))
					{
						$field = [
							'id' => $id,
							'name' => $row['NAME'],
							'type' => 'list',
							'items' => $list,
							'params' => ['multiple' => 'Y'],
							'operators' => $operators[Iblock\PropertyTable::TYPE_LIST]
						];
					}
					unset($list);
					break;
				case Iblock\PropertyTable::TYPE_ELEMENT:
					$row['LINK_IBLOCK_ID'] = (int)$row['LINK_IBLOCK_ID'];
					if ($row['LINK_IBLOCK_ID'] > 0)
					{
						$list = [];
						$valueIterator = \CIBlockElement::GetList(
							['SORT' => 'ASC', 'NAME' => 'ASC'],
							['IBLOCK_ID' => $row['LINK_IBLOCK_ID'], 'CHECK_PERMISSIONS' => 'Y', 'MIN_PERMISSION' => 'R'],
							false,
							false,
							['ID', 'IBLOCK_ID', 'NAME', 'SORT']
						);
						while ($value = $valueIterator->Fetch())
							$list[$value['ID']] = '['.$value['ID'].'] '.$value['NAME'];
						unset($value, $valueIterator);
						if (!empty($list))
						{
							$field = [
								'id' => $id,
								'name' => $row['NAME'],
								'type' => 'list',
								'items' => $list,
								'params' => ['multiple' => 'Y'],
								'operators' => $operators[Iblock\PropertyTable::TYPE_ELEMENT]
							];
						}
						unset($list);
					}
					break;
				case Iblock\PropertyTable::TYPE_SECTION:
					$row['LINK_IBLOCK_ID'] = (int)$row['LINK_IBLOCK_ID'];
					if ($row['LINK_IBLOCK_ID'] > 0)
					{
						$list = [];
						$valueIterator = \CIBlockSection::GetList(
							['LEFT_MARGIN' => 'ASC'],
							['IBLOCK_ID' => $row['LINK_IBLOCK_ID'], 'CHECK_PERMISSIONS' => 'Y', 'MIN_PERMISSION' => 'R'],
							false,
							['ID', 'IBLOCK_ID', 'DEPTH_LEVEL', 'NAME', 'LEFT_MARGIN']
						);
						while ($value = $valueIterator->Fetch())
							$list[$value['ID']] = str_repeat('. ', $value['DEPTH_LEVEL'] - 1).'['.$value['ID'].'] '.$value['NAME'];
						unset($value, $valueIterator);
						if (!empty($list))
						{
							$field = [
								'id' => $id,
								'name' => $row['NAME'],
								'type' => 'list',
								'items' => $list,
								'operators' => $operators[Iblock\PropertyTable::TYPE_SECTION]
							];
						}
						unset($list);
					}
					break;
			}
			if (!empty($field))
				$result[$id] = $field;
		}
		unset($id, $row, $iterator);
		unset($operators);

		return $result;
	}

	/**
	 * @return array
	 */
	protected function getElementFieldsDescription()
	{
		$result = [];

		$result['ACTIVE_FROM'] = [
			'id' => 'ACTIVE_FROM',
			'name' => Loc::getMessage('IBLOCK_SELECTOR_ELEMENT_GRID_COLUMN_ACTIVE_FROM'),
			'sort' => 'ACTIVE_FROM',
			'default' => false
		];
		$result['ACTIVE_TO'] = [
			'id' => 'ACTIVE_TO',
			'name' => Loc::getMessage('IBLOCK_SELECTOR_ELEMENT_GRID_COLUMN_ACTIVE_TO'),
			'sort' => 'ACTIVE_TO',
			'default' => false
		];

		if (
			$this->catalogIncluded
			&& (string)$this->getStorageItem(self::STORAGE_ENTITY_IBLOCK, 'CATALOG_TYPE') !== ''
		)
		{
			$result['TYPE'] = [
				'id' => 'TYPE',
				'name' => Loc::getMessage('IBLOCK_SELECTOR_ELEMENT_GRID_COLUMN_TYPE'),
				'sort' => 'TYPE',
				'default' => false
			];
			$result['AVAILABLE'] = [
				'id' => 'AVAILABLE',
				'name' => Loc::getMessage('IBLOCK_SELECTOR_ELEMENT_GRID_COLUMN_AVAILABLE'),
				'sort' => 'AVAILABLE',
				'default' => false
			];
		}

		return $result;
	}

	/**
	 * @return array
	 */
	protected function getProductFieldsDescription()
	{
		$result = [];

		if (
			$this->catalogIncluded
			&& (string)$this->getStorageItem(self::STORAGE_ENTITY_IBLOCK, 'CATALOG_TYPE') !== ''
		)
		{
			$result['TYPE'] = [
				'id' => 'TYPE',
				'name' => Loc::getMessage('IBLOCK_SELECTOR_ELEMENT_GRID_COLUMN_TYPE'),
				'sort' => 'TYPE',
				'default' => false
			];
			$result['AVAILABLE'] = [
				'id' => 'AVAILABLE',
				'name' => Loc::getMessage('IBLOCK_SELECTOR_ELEMENT_GRID_COLUMN_AVAILABLE'),
				'sort' => 'AVAILABLE',
				'default' => false
			];
		}

		return $result;
	}

	/**
	 * @return array|null
	 */
	protected function getElementPropertiesDescription()
	{
		$result = null;

		$iblockId = (int)$this->getStorageItem(self::STORAGE_ENTITY_IBLOCK, 'IBLOCK_ID');
		if ($iblockId > 0)
		{
			$this->loadElementPropertiesDescription();
			if (!empty($this->elementProperties))
			{
				$result = [];
				foreach ($this->elementProperties as $id => $row)
				{
					$result[$id] = [
						'id' => $id,
						'name' => $row['NAME'],
						'sort' => ($row['MULTIPLE'] == 'N'),
						'default' => false
					];
				}
			}
		}

		return $result;
	}

	/**
	 * @return void
	 */
	protected function loadElementPropertiesDescription()
	{
		if ($this->elementProperties !== null)
			return;

		$iblockId = (int)$this->getStorageItem(self::STORAGE_ENTITY_IBLOCK, 'IBLOCK_ID');
		if ($iblockId > 0)
		{
			$this->elementProperties = $this->loadPropertiesDescription([
				'=IBLOCK_ID' => $iblockId, '=ACTIVE' => 'Y'
			]);
		}
		unset($iblockId);
	}

	/**
	 * @return void
	 */
	protected function loadOfferPropertiesDescription()
	{
		if ($this->offerProperties !== null)
			return;

		if (!$this->catalogIncluded)
			return;

		$iblockId = (int)$this->getStorageItem(self::STORAGE_ENTITY_IBLOCK, 'OFFERS_IBLOCK_ID');
		$skuPropertyId = (int)$this->getStorageItem(self::STORAGE_ENTITY_IBLOCK, 'SKU_PROPERTY_ID');

		if ($iblockId > 0 && $skuPropertyId > 0)
		{
			$this->offerProperties = $this->loadPropertiesDescription([
				'=IBLOCK_ID' => $iblockId, '!=ID' => $skuPropertyId, '=ACTIVE' => 'Y'
			]);
		}
		unset($skuPropertyId, $iblockId);
	}

	/**
	 * @param array $filter
	 * @return array
	 */
	protected function loadPropertiesDescription(array $filter)
	{
		$result = [];
		if (empty($filter))
			return $result;

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
			$result['PROPERTY_'.$row['ID']] = $row;
		}
		unset($row, $iterator);

		return $result;
	}

	/**
	 * @param array $row
	 * @return bool
	 */
	protected static function isFilterableProperty(array $row)
	{
		return ($row['FILTRABLE'] == 'Y' && $row['PROPERTY_TYPE'] != Iblock\PropertyTable::TYPE_FILE);
	}
}