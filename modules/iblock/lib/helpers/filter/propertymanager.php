<?php
namespace Bitrix\Iblock\Helpers\Filter;

use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Iblock;

Loc::loadMessages(__FILE__);

class PropertyManager
{
	private $iblockId;
	private $listProperty;
	private $filterFields;
	private $catalogIncluded;
	private $catalog = null;

	public function __construct($iblockId)
	{
		$this->iblockId = (int)$iblockId;
		$this->listProperty = null;
		$this->filterFields = null;
		$this->catalogIncluded = Loader::includeModule('catalog');
		if ($this->catalogIncluded)
		{
			$catalog = \CCatalogSku::GetInfoByIBlock($this->iblockId);
			if (!empty($catalog))
			{
				$this->catalog = $catalog;
			}
			unset($catalog);
		}
	}

	/**
	 * @return array
	 */
	public function getFilterFields(): array
	{
		if ($this->filterFields === null)
		{
			$offers = (!empty($this->catalog['CATALOG_TYPE']) && $this->catalog['CATALOG_TYPE'] == \CCatalogSku::TYPE_OFFERS);

			$this->filterFields = [];
			$listProperty = $this->getListProperty();
			foreach ($listProperty as $property)
			{
				$fieldId = $property['FIELD_ID'];
				$fieldName = ($offers
					? Loc::getMessage('IBLOCK_PROPERTY_FILTER_MANAGER_MESS_OFFER_TITLE', ['#NAME#' => $property['NAME']])
					: $property['NAME']
				);

				if (!empty($property['USER_TYPE']))
				{
					$field = array();
					if (isset($property["PROPERTY_USER_TYPE"]["GetUIFilterProperty"]))
					{
						$field = [
							'id' => $fieldId,
							'name' => $fieldName,
							'type' => 'custom',
							'value' => '',
							'filterable' => '',
						];
						call_user_func_array(
							$property["PROPERTY_USER_TYPE"]["GetUIFilterProperty"],
							[
								$property,
								["VALUE" => $fieldId, "FORM_NAME" => "main-ui-filter"],
								&$field,
							]
						);
					}
					elseif (isset($property["PROPERTY_USER_TYPE"]["GetPublicFilterHTML"]))
					{
						$field = [
							'id' => $fieldId,
							'name' => $fieldName,
							'type' => 'custom',
							'value' => call_user_func_array(
								$property['PROPERTY_USER_TYPE']['GetPublicFilterHTML'],
								[
									$property,
									[
										'VALUE' => $fieldId,
										'FORM_NAME' => 'main-ui-filter',
									],
								]
							),
							'filterable' => '',
						];
					}
					elseif (isset($property["PROPERTY_USER_TYPE"]["GetAdminFilterHTML"]))
					{
						$field = [
							'id' => $fieldId,
							'name' => $fieldName,
							'type' => 'custom',
							'value' => call_user_func_array(
								$property['PROPERTY_USER_TYPE']['GetAdminFilterHTML'],
								[
									$property,
									[
										'VALUE' => $fieldId,
										'FORM_NAME' => 'main-ui-filter',
									],
								]
							),
							'filterable' => '',
						];
					}
					if ($field === null)
					{
						continue;
					}
					elseif (empty($field))
					{
						$field = [
							'id' => $fieldId,
							'name' => $fieldName,
							'filterable' => '',
						];
					}
					$this->filterFields[] = $field;
				}
				else
				{
					switch ($property['PROPERTY_TYPE'])
					{
						case Iblock\PropertyTable::TYPE_STRING:
							$this->filterFields[] = [
								'id' => $fieldId,
								'name' => $fieldName,
								'filterable' => '?',
							];
							break;
						case Iblock\PropertyTable::TYPE_NUMBER:
							$this->filterFields[] = [
								'id' => $fieldId,
								'name' => $fieldName,
								'type' => 'number',
								'filterable' => '',
							];
							break;
						case Iblock\PropertyTable::TYPE_LIST:
							$items = [
								'NOT_REF' => Loc::getMessage('IBLOCK_PM_LIST_DEFAULT_OPTION'),
							];
							$enumIterator = Iblock\PropertyEnumerationTable::getList([
								'select' => [
									'ID',
									'VALUE',
									'SORT',
								],
								'filter' => [
									'=PROPERTY_ID' => $property['ID'],
								],
								'order' => [
									'SORT' => 'ASC',
									'VALUE' => 'ASC',
									'ID' => 'ASC',
								],
							]);
							while ($enumRow = $enumIterator->fetch())
							{
								$items[$enumRow['ID']] = $enumRow['VALUE'];
							}
							unset($enumRow, $enumIterator);
							$this->filterFields[] = [
								'id' => $fieldId,
								'name' => $fieldName,
								'type' => 'list',
								'items' => $items,
								'params' => [
									'multiple' => 'Y',
								],
								'filterable' => '',
							];
							break;
						case Iblock\PropertyTable::TYPE_ELEMENT:
							$this->filterFields[] = [
								'id' => $fieldId,
								'name' => $fieldName,
								'type' => 'custom_entity',
								'filterable' => '',
								'property' => $property,
								'customRender' => [
									'Bitrix\Iblock\Helpers\Filter\Property',
									'render',
								],
								'customFilter' => [
									'Bitrix\Iblock\Helpers\Filter\Property',
									'addFilter',
								],
							];
							break;
						case Iblock\PropertyTable::TYPE_SECTION:
							$items = [];
							$sectionQueryObject = \CIBlockSection::getList(
								[
									'LEFT_MARGIN' => 'ASC',
								],
								[
									'IBLOCK_ID' => $property['LINK_IBLOCK_ID'],
								],
								false,
								[
									'ID',
									'IBLOCK_ID',
									'DEPTH_LEVEL',
									'NAME',
									'LEFT_MARGIN',
								]
							);
							while ($section = $sectionQueryObject->fetch())
							{
								$margin = max((int)$section['DEPTH_LEVEL'], 1) - 1;
								$items[$section['ID']] = str_repeat('. ', $margin) . $section['NAME'];
							}
							unset($section, $sectionQueryObject);
							$this->filterFields[] = [
								'id' => $fieldId,
								'name' => $fieldName,
								'type' => 'list',
								'items' => $items,
								'params' => [
									'multiple' => 'Y',
								],
								'filterable' => '',
							];
							unset($items);
							break;
					}
				}
			}
			unset($property, $listProperty);
			unset($offers);
		}

		return $this->filterFields;
	}

	/**
	 * @param string $filterId
	 * @return void
	 */
	public function renderCustomFields($filterId)
	{
		foreach ($this->getFilterFields() as $filterField)
		{
			if (isset($filterField['customRender']))
			{
				echo call_user_func_array(
					$filterField['customRender'],
					[
						$filterId,
						$filterField['property']['PROPERTY_TYPE'],
						[
							$filterField['property'],
						],
					]
				);
			}
		}
	}

	/**
	 * @param string $filterId
	 * @param array &$filter
	 * @return void
	 */
	public function AddFilter($filterId, array &$filter)
	{
		if (empty($filter))
		{
			return;
		}

		$listProperty = $this->getListProperty();
		if (empty($listProperty))
		{
			return;
		}

		$filterFields = [];
		foreach($this->getFilterFields() as $row)
		{
			$filterFields[$row['id']] = $row;
		}

		foreach (array_keys($filter) as $index)
		{
			$indexData = \CIBlock::MkOperationFilter($index);
			$propertyId = $indexData['FIELD'];

			if (!isset($listProperty[$propertyId]))
			{
				continue;
			}

			if (isset($filterFields[$propertyId]['customFilter']))
			{
				$row = $filterFields[$propertyId];
				$filtered = false;
				call_user_func_array(
					$row['customFilter'],
					[
						$row['property'],
						[
							"VALUE" => $row["id"],
							"FILTER_ID" => $filterId,
						],
						&$filter,
						&$filtered,
					]
				);
				unset($filtered);
				unset($row);
			}
			elseif (isset($listProperty[$propertyId]['PROPERTY_USER_TYPE']['AddFilterFields']))
			{
				$filtered = false;
				$row = $listProperty[$propertyId];
				call_user_func_array(
					$row['PROPERTY_USER_TYPE']['AddFilterFields'],
					[
						$row,
						[
							'VALUE' => $row['FIELD_ID'],
							'FILTER_ID' => $filterId,
						],
						&$filter,
						&$filtered,
					]
				);
				unset($filtered);
				unset($row);
			}
			else
			{
				switch ($listProperty[$propertyId]['PROPERTY_TYPE'])
				{
					case Iblock\PropertyTable::TYPE_STRING:
					case Iblock\PropertyTable::TYPE_NUMBER:
						$value = (string)$filter[$index];
						if ($value === '')
						{
							unset($filter[$index]);
						}
						unset($value);
						break;
					case Iblock\PropertyTable::TYPE_LIST:
					case Iblock\PropertyTable::TYPE_ELEMENT:
					case IBlock\PropertyTable::TYPE_SECTION:
						if (is_array($filter[$index]))
						{
							$newValues = [];
							foreach ($filter[$index] as $value)
							{
								$newValues[] = ($value === 'NOT_REF' ? false : $value);
							}
							unset($filter[$index]);
							if (!empty($newValues))
							{
								$filter['=' . $propertyId] = $newValues;
							}
							unset($newValues);
						}
						else
						{
							$value = $filter[$index];
							unset($filter[$index]);
							if ($value === 'NOT_REF')
							{
								$value = false;
							}
							$filter['='.$propertyId] = $value;
							unset($value);
						}
						break;
				}
			}
		}
		unset($filterFields);
		unset($listProperty);
	}

	/**
	 * @return array
	 */
	private function getListProperty(): array
	{
		if ($this->listProperty === null)
		{
			$this->listProperty = [];
			$iterator = Iblock\PropertyTable::getList([
				'select' => ['*'],
				'filter' => [
					'=IBLOCK_ID' => $this->iblockId,
					'=ACTIVE' => 'Y',
					'=FILTRABLE' => 'Y',
				],
				'order' => [
					'SORT' => 'ASC',
					'NAME' => 'ASC',
				],
			]);
			while ($property = $iterator->fetch())
			{
				$property['FIELD_ID'] = 'PROPERTY_'.$property['ID'];
				$property['USER_TYPE_SETTINGS'] = $property['USER_TYPE_SETTINGS_LIST'];
				unset($property['USER_TYPE_SETTINGS_LIST']);
				$property['PROPERTY_USER_TYPE'] = (!empty($property['USER_TYPE']) ?
					\CIBlockProperty::GetUserType($property['USER_TYPE']) : []);
				$this->listProperty[$property['FIELD_ID']] = $property;
			}
			unset($property, $iterator);
		}

		return $this->listProperty;
	}
}
