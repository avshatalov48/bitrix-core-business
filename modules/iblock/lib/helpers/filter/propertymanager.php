<?
namespace Bitrix\Iblock\Helpers\Filter;

use Bitrix\Main\Localization\Loc,
	Bitrix\Iblock;

Loc::loadMessages(__FILE__);

class PropertyManager
{
	private $iblockId = 0;
	private $listProperty = null;
	private $filterFields = null;

	public function __construct($iblockId)
	{
		$this->iblockId = (int)$iblockId;
		$this->listProperty = null;
		$this->filterFields = null;
	}

	/**
	 * @return array
	 */
	public function getFilterFields()
	{
		if ($this->filterFields === null)
		{
			$this->filterFields = [];
			$listProperty = $this->getListProperty();
			foreach ($listProperty as $property)
			{
				$fieldId = $property['FIELD_ID'];
				$fieldName = $property['NAME'];

				if (!empty($property['USER_TYPE']))
				{
					$field = array();
					$userType = $property["USER_TYPE"];
					switch ($userType)
					{
						case "directory":
							if (isset($property["PROPERTY_USER_TYPE"]["GetOptionsData"]))
							{
								$data = call_user_func_array(
									$property["PROPERTY_USER_TYPE"]["GetOptionsData"],
									array($property, array())
								);
								$field = array(
									"id" => $fieldId,
									"name" => $fieldName,
									"type" => "list",
									"items" => $data,
									"params" => array("multiple" => "Y"),
									"filterable" => ""
								);
							}
							break;
						case "employee":
						case "UserID":
							$field = array(
								"id" => $fieldId,
								"name" => $fieldName,
								"type" => "custom_entity",
								"filterable" => "",
								"selector" => array("type" => "user"),
							);
							break;
						case "ECrm":
							$field = array(
								"id" => $fieldId,
								"name" => $fieldName,
								"type" => "custom_entity",
								"filterable" => "",
							);
							break;
						default:
							if (isset($property["PROPERTY_USER_TYPE"]["GetUIFilterProperty"]))
							{
								$field = array(
									"id" => $fieldId,
									"name" => $fieldName,
									"type" => "custom",
									"value" => "",
									"filterable" => ""
								);
								call_user_func_array($property["PROPERTY_USER_TYPE"]["GetUIFilterProperty"],
									array(
										$property,
										array("VALUE" => $fieldId, "FORM_NAME" => "main-ui-filter"),
										&$field
									)
								);
							}
							elseif (isset($property["PROPERTY_USER_TYPE"]["GetPublicFilterHTML"]))
							{
								$field = array(
									"id" => $fieldId,
									"name" => $fieldName,
									"type" => "custom",
									"value" => call_user_func_array(
										$property["PROPERTY_USER_TYPE"]["GetPublicFilterHTML"],
										array(
											$property,
											array("VALUE" => $fieldId, "FORM_NAME" => "main-ui-filter")
										)
									),
									"filterable" => ""
								);
							}
							elseif (isset($property["PROPERTY_USER_TYPE"]["GetAdminFilterHTML"]))
							{
								$field = array(
									"id" => $fieldId,
									"name" => $fieldName,
									"type" => "custom",
									"value" => call_user_func_array(
										$property["PROPERTY_USER_TYPE"]["GetAdminFilterHTML"],
										array(
											$property,
											array("VALUE" => $fieldId, "FORM_NAME" => "main-ui-filter")
										)
									),
									"filterable" => ""
								);
							}
					}
					if (empty($field))
					{
						$field = array(
							"id" => $fieldId,
							"name" => $fieldName,
							"filterable" => ""
						);
					}
					$this->filterFields[] = $field;
				}
				else
				{
					switch ($property['PROPERTY_TYPE'])
					{
						case Iblock\PropertyTable::TYPE_STRING:
							$this->filterFields[] = array(
								"id" => $fieldId,
								"name" => $fieldName,
								"filterable" => "?"
							);
							break;
						case Iblock\PropertyTable::TYPE_NUMBER:
							$this->filterFields[] = array(
								"id" => $fieldId,
								"name" => $fieldName,
								"type" => "number",
								"filterable" => ""
							);
							break;
						case Iblock\PropertyTable::TYPE_LIST:
							$items = [
								'NOT_REF' => Loc::getMessage('IBLOCK_PM_LIST_DEFAULT_OPTION')
							];
							$enumIterator = Iblock\PropertyEnumerationTable::getList([
								'select' => ['ID', 'VALUE', 'SORT'],
								'filter' => ['PROPERTY_ID' => $property['ID']],
								'order' => ['SORT' => 'ASC', 'VALUE' => 'ASC', 'ID' => 'ASC']
							]);
							while ($enumRow = $enumIterator->fetch())
								$items[$enumRow['ID']] = $enumRow['VALUE'];
							unset($enumRow, $enumIterator);
							$this->filterFields[] = array(
								"id" => $fieldId,
								"name" => $fieldName,
								"type" => "list",
								"items" => $items,
								"params" => ["multiple" => "Y"],
								"filterable" => ""
							);
							break;
						case Iblock\PropertyTable::TYPE_ELEMENT:
							$this->filterFields[] = array(
								"id" => $fieldId,
								"name" => $fieldName,
								"type" => "custom_entity",
								"filterable" => "",
								"property" => $property,
								"customRender" => array("Bitrix\Iblock\Helpers\Filter\Property", "render"),
								"customFilter" => array("Bitrix\Iblock\Helpers\Filter\Property", "addFilter")
							);
							break;
						case Iblock\PropertyTable::TYPE_SECTION:
							$items = array();
							$sectionQueryObject = \CIBlockSection::getList(
								array("LEFT_MARGIN" => "ASC"),
								array("IBLOCK_ID" => $property["LINK_IBLOCK_ID"]),
								false,
								array('ID', 'IBLOCK_ID', 'DEPTH_LEVEL', 'NAME', 'LEFT_MARGIN')
							);
							while ($section = $sectionQueryObject->fetch())
							{
								$items[$section["ID"]] = str_repeat(". ", $section["DEPTH_LEVEL"] - 1).$section["NAME"];
							}
							unset($section, $sectionQueryObject);
							$this->filterFields[] = array(
								"id" => $fieldId,
								"name" => $fieldName,
								"type" => "list",
								"items" => $items,
								"params" => array("multiple" => "Y"),
								"filterable" => ""
							);
							unset($items);
							break;
					}
				}
			}
			unset($property, $listProperty);
		}

		return $this->filterFields;
	}

	public function renderCustomFields($filterId)
	{
		foreach ($this->getFilterFields() as $filterField)
		{
			if (isset($filterField["customRender"]))
			{
				echo call_user_func_array($filterField["customRender"], array(
					$filterId,
					$filterField["property"]["PROPERTY_TYPE"],
					array($filterField["property"]),
				));
			}
		}
	}

	public function AddFilter($filterId, array &$filter)
	{
		$listProperty = $this->getListProperty();
		if (!empty($listProperty))
		{
			$filterKeys = (!empty($filter) ? array_fill_keys(array_keys($filter), true) : []);
			foreach ($listProperty as $property)
			{
				if (isset($property["PROPERTY_USER_TYPE"]["AddFilterFields"]))
				{
					$filtered = false;
					call_user_func_array($property["PROPERTY_USER_TYPE"]["AddFilterFields"], array(
						$property,
						array("VALUE" => $property["FIELD_ID"], "FILTER_ID" => $filterId),
						&$filter,
						&$filtered,
					));
				}
				else
				{
					if (isset($filterKeys[$property["FIELD_ID"]]))
					{
						if ($filter[$property["FIELD_ID"]] === "NOT_REF")
						{
							unset($filter[$property["FIELD_ID"]]);
							$filter["?".$property["FIELD_ID"]] = false;
						}
					}
				}
			}
			unset($filterKeys);

			foreach($this->getFilterFields() as $filterField)
			{
				if (isset($filterField["customFilter"]))
				{
					$filtered = false;
					call_user_func_array($filterField["customFilter"], array(
						$filterField["property"],
						array(
							"VALUE" => $filterField["id"],
							"FILTER_ID" => $filterId,
						),
						&$filter,
						&$filtered,
					));
				}
			}
		}
		unset($listProperty);
	}

	/**
	 * @return array
	 */
	private function getListProperty()
	{
		if ($this->listProperty === null)
		{
			$this->listProperty = [];
			$iterator = Iblock\PropertyTable::getList([
				'select' => ['*'],
				'filter' => ['=IBLOCK_ID' => $this->iblockId, '=ACTIVE' => 'Y', '=FILTRABLE' => 'Y'],
				'order' => ['SORT' => 'ASC', 'NAME' => 'ASC']
			]);
			while ($property = $iterator->fetch())
			{
				$property['FIELD_ID'] = 'PROPERTY_'.$property['ID'];
				$property['USER_TYPE_SETTINGS'] = $property['USER_TYPE_SETTINGS_LIST'];
				unset($property['USER_TYPE_SETTINGS_LIST']);
				$property['PROPERTY_USER_TYPE'] = (!empty($property['USER_TYPE']) ?
					\CIBlockProperty::GetUserType($property['USER_TYPE']) : []);
				$this->listProperty[$property['ID']] = $property;
			}
			unset($property, $iterator);
		}
		return $this->listProperty;
	}
}