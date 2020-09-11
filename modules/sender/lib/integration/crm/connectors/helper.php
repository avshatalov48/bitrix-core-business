<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\Crm\Connectors;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Entity;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Orm;
use Bitrix\Main\UI\Filter\AdditionalDateType;
use Bitrix\Main\UI\Filter\Type as UiFilterType;
use Bitrix\Sender\Connector;
use Bitrix\Sender\Integration;
use Bitrix\Sender\Recipient;

Loc::loadMessages(__FILE__);

/**
 * Class Helper
 * @package Bitrix\Sender\Integration\Crm\Connectors
 */
class Helper
{
	public static $runtimeByEntity = [];
	private const PERSONALIZE_NAMESPACE = "\\Bitrix\\Sender\\Integration\\Crm\\Connectors\\Personalize\\";

	/**
	 * Create Orm expression field for selecting multi field.
	 *
	 * @param string $entityName Entity name.
	 * @param string $multiFieldTypeId Multi-field type ID.
	 * @return Orm\Fields\ExpressionField
	 */
	public static function createExpressionMultiField($entityName, $multiFieldTypeId)
	{
		$sqlHelper = Application::getConnection()->getSqlHelper();
		return new Orm\Fields\ExpressionField(
			$multiFieldTypeId,
			'(' . $sqlHelper->getTopSql(
				"
						SELECT FM.VALUE							
						FROM b_crm_field_multi FM 
						WHERE FM.ENTITY_ID = '$entityName' 
							AND FM.ELEMENT_ID = %s 
							AND FM.TYPE_ID = '$multiFieldTypeId' 
						ORDER BY
							CASE FM.VALUE_TYPE 
								WHEN 'MAILING' THEN 0 
								WHEN 'HOME' THEN 1
								WHEN 'MOBILE' THEN 1
								ELSE 2
							END,
							FM.ID
					",
				1
			) . ')',
			'ID'
		);
	}

	/**
	 * Get personalize field list.
	 *
	 * @return array
	 */
	public static function getPersonalizeList()
	{
		return array(
			array('CODE' => 'CRM_ENTITY_TYPE'),
			array('CODE' => 'CRM_ENTITY_TYPE_ID'),
			array('CODE' => 'CRM_ENTITY_ID'),
		);
	}

	public static function buildPersonalizeList($entityType)
	{
		$result = [];
		$documentClass = self::PERSONALIZE_NAMESPACE.ucfirst($entityType);

		if(!class_exists($documentClass, 'getEntityFields'))
		{
			return $result;
		}

		$items = [];
		$fields = $documentClass::getEntityFields($entityType) ;
		$counter = 0;
		foreach ($fields as $fieldCode  => $field)
		{
			$items[$counter++] = [
				'CODE' => $entityType.'.'.$fieldCode,
				'NAME' => $field['Name'],
				'DESC' => $field['Name'],
			];
			if(isset($field['personalizeCode']) && $field['personalizeCode'])
			{
				$items[$counter - 1]['CODE'] = implode(
					"##",
					Integration\Crm\Connectors\Personalize\BasePersonalize::getMap()[$field['personalizeCode']]
				);
			}
		}

		$result[] = [
			'CODE' => $entityType,
			'NAME' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_'.$entityType),
			'ITEMS' => $items
		];

		return $result;
	}

	public static function getData($entityType, $entityIds, $fields = ['*'])
	{
		$result = [];
		$documentClass = self::PERSONALIZE_NAMESPACE.ucfirst($entityType);

		if(!class_exists($documentClass, 'getData'))
		{
			return $result;
		}

		return $documentClass::getData($entityType, $entityIds, $fields);
	}

	public static function getPersonalizeFieldsFromConnectors($isTrigger = false)
	{
		$connectors = Connector\Manager::getConnectorList();
		$items = [];

		foreach ($connectors as $connector)
		{
			if(!($connector instanceof Connector\BaseFilter))
			{
				continue;
			}

			if(method_exists($connector, 'getPersonalizeList'))
			{
				$list = $connector->getPersonalizeList();
				if($isTrigger)
				{
					foreach ($list as $key => $data)
					{
						if(isset($data['ITEMS']) && in_array($data['CODE'], ['LEAD', 'CONTACT', 'COMPANY']))
						{
							unset($list[$key]);
						}
					}
				}
				if(!empty($list))
				{
					$items = array_merge($items, $list);
				}
			}
		}
		return $items;
	}

	/**
	 * Get filter user fields.
	 *
	 * @param integer $entityTypeId Entity type ID.
	 * @return array
	 */
	public static function getFilterUserFields($entityTypeId)
	{
		$list = array();
		$ufManager = is_object($GLOBALS['USER_FIELD_MANAGER']) ? $GLOBALS['USER_FIELD_MANAGER'] : null;
		if (!$ufManager)
		{
			return $list;
		}

		$ufEntityId = \CCrmOwnerType::resolveUserFieldEntityID($entityTypeId);
		$crmUserType = new \CCrmUserType($ufManager, $ufEntityId);
		$logicFilter = array();
		$crmUserType->prepareListFilterFields($list, $logicFilter);
		$originalList = $crmUserType->getFields();
		$restrictedTypes = ['address', 'file', 'crm', 'resourcebooking'];

		$list = array_filter(
			$list,
			function ($field) use ($originalList, $restrictedTypes)
			{
				if (empty($originalList[$field['id']]))
				{
					return false;
				}

				$type = $originalList[$field['id']]['USER_TYPE']['USER_TYPE_ID'];
				return !in_array($type, $restrictedTypes);
			}
		);

		foreach ($list as $index => $field)
		{
			if ($field['type'] === 'date')
			{
				$list[$index]['include'] = [
					AdditionalDateType::CUSTOM_DATE,
					AdditionalDateType::PREV_DAY,
					AdditionalDateType::NEXT_DAY,
					AdditionalDateType::MORE_THAN_DAYS_AGO,
					AdditionalDateType::AFTER_DAYS,
				];
				if (!isset($list[$index]['allow_years_switcher']))
				{
					$list[$index]['allow_years_switcher'] = true;
				}
			}
			if ($originalList[$field['id']]['MULTIPLE'] == 'Y')
			{
				$list[$index]['multiple_uf'] = true;
			}
		}

		return $list;
	}

	/**
	 * Prepare query select.
	 *
	 * @param Entity\Query $query Query.
	 * @param integer $dataTypeId Data type ID.
	 * @return Entity\Query
	 */
	public static function prepareQuery(Entity\Query $query, $dataTypeId = null)
	{
		$map = array(
			Recipient\Type::EMAIL => [
				'name' => 'HAS_EMAIL',
				'operator' => '=',
				'value' => 'Y'
			],
			Recipient\Type::PHONE => [
				'name' => 'HAS_PHONE',
				'operator' => '=',
				'value' => 'Y'
			],
			Recipient\Type::IM => [
				'name' => 'HAS_IMOL',
				'operator' => '=',
				'value' => 'Y'
			],
			Recipient\Type::CRM_CONTACT_ID => [
				'name' => 'CONTACT_ID',
				'operator' => null,
				'value' => null
			],
			Recipient\Type::CRM_COMPANY_ID => [
				'name' => 'COMPANY_ID',
				'operator' => null,
				'value' => null
			],
		);

		$entityName = mb_strtoupper($query->getEntity()->getName());

		if ($dataTypeId)
		{
			if (!isset($map[$dataTypeId]))
			{
				return $query;
			}

			$field = $map[$dataTypeId];

			if($field['operator'] === null)
			{
				return $query;
			}

			if ($dataTypeId == Recipient\Type::CRM_COMPANY_ID && in_array($entityName, ['CONTACT']))
			{
				$field['name'] = 'CRM_COMPANY_ID';
			}

			$query->where($field['name'], $field['operator'], $field['value']);
			if ($dataTypeId === Recipient\Type::IM)
			{
				$query->whereExists(self::getImSqlExpression($query));
			}
		}
		else if (!in_array($entityName, ['CONTACT', 'COMPANY']))
		{
			$filter = Entity\Query::filter();
			foreach ($map as $dataTypeId => $field)
			{
				if($field['operator'] === null)
				{
					continue;
				}
				if ($dataTypeId === Recipient\Type::IM)
				{
					$filter->where(
						Entity\Query::filter()
							->where($field['name'], $field['operator'], $field['value'])
							->whereExists(self::getImSqlExpression($query))
					);
				}
				else
				{
					$filter->where($field['name'], $field['operator'], $field['value']);
				}
			}

			if (count($filter->getConditions()) > 0)
			{
				$filter->logic('or');
				$query->where($filter);
			}
		}


		return $query;
	}

	protected static function getImSqlExpression(Entity\Query $query)
	{
		$codes = Integration\Im\Service::getExcludedChannelCodes();
		if (empty($codes))
		{
			$codes = array('livechat', 'network');
		}

		$entityTypeName = mb_strtoupper($query->getEntity()->getName());
		$filterImolSql = "SELECT FM.VALUE " .
			"FROM b_crm_field_multi FM " .
			"WHERE FM.ENTITY_ID = '$entityTypeName' AND FM.ELEMENT_ID = ?#.ID " .
			"AND FM.TYPE_ID = 'IM' " .
			"AND FM.VALUE NOT REGEXP '^imol\\\\|(" . implode('|', $codes) . ")' " .
			"ORDER BY FM.ID LIMIT 1";

		return new SqlExpression($filterImolSql, $query->getInitAlias());
	}

	/**
	 * Get runtime by entity.
	 *
	 * @param string $entityTypeName Entity type name.
	 * @return Entity\ExpressionField[]
	 */
	public static function getRuntimeByEntity($entityTypeName = '')
	{
		if (isset(self::$runtimeByEntity[$entityTypeName]))
		{
			return self::$runtimeByEntity[$entityTypeName];
		}

		return [];
	}

	protected static function processRuntimeFilter(array &$filter, $entityTypeName = '')
	{
		foreach ($filter as $key => $item)
		{
			if (!($item instanceof Connector\Filter\RuntimeFilter))
			{
				continue;
			}

			unset($filter[$key]);
			$filter[$item->getKey()] = $item->getValue();
			if (empty(self::$runtimeByEntity[$entityTypeName]))
			{
				self::$runtimeByEntity[$entityTypeName] = [];
			}
			self::$runtimeByEntity[$entityTypeName] = array_merge(
				self::$runtimeByEntity[$entityTypeName],
				array_map(
					function ($item) use ($entityTypeName)
					{
						$search = $entityTypeName ? $entityTypeName . '_' : '';
						$runtimeName = $entityTypeName ? $entityTypeName . '.' : '';
						$item['expression'] = str_replace(
							$search,
							$runtimeName,
							$item['expression']
						);
						$item['buildFrom'] = array_map(
							function ($from) use ($search, $runtimeName)
							{
								return str_replace($search, $runtimeName, $from);
							},
							$item['buildFrom']
						);
						return $item;
					},
					$item->getRuntime()
				)
			);
		}
	}

	/**
	 * Get filter by entity.
	 *
	 * @param array $fields Fields.
	 * @param array $values Values.
	 * @param array $entityTypeNames Entity type names.
	 * @return array
	 */
	public static function getFilterByEntity(array $fields = array(), array $values = array(), array $entityTypeNames = array())
	{
		$map = array();
		foreach ($entityTypeNames as $entityTypeName)
		{
			$map[$entityTypeName] = array($entityTypeName);
		}
		$map['CLIENT'] = array(\CCrmOwnerType::CompanyName, \CCrmOwnerType::ContactName);
		$map[\CCrmOwnerType::CompanyName] = array(\CCrmOwnerType::CompanyName);
		$map[\CCrmOwnerType::ContactName] = array(\CCrmOwnerType::ContactName);

		$result = array();
		foreach ($fields as $field)
		{
			if (!self::isFieldFilterable($field, $values))
			{
				continue;
			}

			$id = $field['id'];
			foreach ($map as $prefix => $entityTypes)
			{
				$search = $prefix . '_';
				if (mb_strpos($id, $search) !== 0)
				{
					continue;
				}

				foreach ($entityTypes as $entityTypeName)
				{
					$filterKey = "$entityTypeName.".mb_substr($id, mb_strlen($search));
					if (!self::isFieldTypeFilter($field['type']))
					{
						$filterKey = "=$filterKey";
					}
					if ($field['multiple_uf'])
					{
						$filterKey .= "_SINGLE";
					}

					$field['sender_segment_filter'] = $filterKey;
					if (!isset($result[$entityTypeName]))
					{
						$result[$entityTypeName] = array();
					}

					$result[$entityTypeName][] = $field;
				}

				break;
			}
		}

		self::$runtimeByEntity = [];
		foreach ($result as $entityTypeName => $fields)
		{
			$items = self::getFilterByFields($fields, $values, $entityTypeName);

			$result[$entityTypeName] = $items;
		}

		return $result;
	}

	private static function isFieldFilterable(array $field = array(), array $values = array())
	{
		$id = $field['id'];
		$codeKey = 'sender_segment_filter';
		if (isset($field[$codeKey]) && $field[$codeKey] === false)
		{
			return false;
		}

		if (!isset($values[$id]) || (!$values[$id] && !is_numeric($values[$id])))
		{
			return false;
		}

		return true;
	}

	/**
	 * Get filter by fields.
	 *
	 * @param array $fields Fields.
	 * @param array $values Values.
	 * @param string $entityTypeName Entity type name.
	 * @return array
	 */
	public static function getFilterByFields(array $fields = array(), array $values = array(), $entityTypeName = '')
	{
		if ($entityTypeName)
		{
			if (!empty(self::$runtimeByEntity[$entityTypeName]))
			{
				self::$runtimeByEntity[$entityTypeName] = [];
			}
		}
		else
		{
			self::$runtimeByEntity = [];
		}

		$filter = array();
		foreach ($fields as $field)
		{
			if (!self::isFieldFilterable($field, $values))
			{
				continue;
			}

			$isMultiple = false;
			if (isset($field['params']) && is_array($field['params']))
			{
				if (isset($field['params']['multiple']) && $field['params']['multiple'])
				{
					$isMultiple = true;
				}
			}

			$id = $field['id'];
			$value = $values[$id];
			$value = $isMultiple && !is_array($value) ? array($value) :
				($value === "" ? null:$value);

			if($isMultiple)
			{
				foreach($value as &$val)
				{
					if($val === "")
					{
						$val = null;
					}
				}
			}

			$field['value'] = $value;

			if ($field['filter_callback'])
			{
				$extraCallbackParams = [
					'FIELD' => $field,
					'ENTITY_TYPE_NAME' => $entityTypeName
				];
				call_user_func_array($field['filter_callback'], [$value, &$filter, $extraCallbackParams]);
				continue;
			}

			$filterKey = self::getFilterFieldKey($field);
			if (is_array($filterKey))
			{
				foreach ($filterKey as $fieldValue => $fieldFilter)
				{
					if ($value !== $fieldValue)
					{
						continue;
					}

					$filter[$fieldFilter[0]] = $fieldFilter[1];
				}
			}
			elseif (self::isFieldTypeFilter($field['type']))
			{
				self::setFieldTypeFilter($filterKey, $field, $filter);
				self::processRuntimeFilter($filter, $entityTypeName);
			}
			else
			{
				$filter[$filterKey] = $value;
			}
		}

		return $filter;
	}

	protected static function getFilterFieldKey(array $field)
	{
		$codeKey = 'sender_segment_filter';

		$id = $field['id'];
		if (isset($field[$codeKey]) && $field[$codeKey])
		{
			return $field[$codeKey];
		}

		if (self::isFieldTypeFilter($field['type']))
		{
			return "$id";
		}

		return "=$id";
	}

	protected static function isFieldTypeFilter($type)
	{
		$types = array(
			UiFilterType::DATE,
			UiFilterType::NUMBER,
			UiFilterType::DEST_SELECTOR
		);
		return in_array(mb_strtoupper($type), $types);
	}

	protected static function setFieldTypeFilter($filterKey, array $fieldData, &$filter)
	{
		$fieldData['filter-key'] = $filterKey;
		switch(mb_strtoupper($fieldData['type']))
		{
			case UiFilterType::DATE:
				Connector\Filter\DateField::create($fieldData)->applyFilter($filter);
				break;
			case UiFilterType::NUMBER:
				Connector\Filter\NumberField::create($fieldData)->applyFilter($filter);
				break;
			case UiFilterType::DEST_SELECTOR:
				Connector\Filter\DestSelectorField::create($fieldData)->applyFilter($filter);
				break;
		}
	}

	protected static function getCommunicationTypeFilter(array $commTypes, &$filter, $extraCallbackParams = [])
	{
		if (in_array(\CCrmFieldMulti::PHONE, $commTypes))
		{
			$filter['=HAS_PHONE'] = 'Y';
		}
		if (in_array(\CCrmFieldMulti::EMAIL, $commTypes))
		{
			$filter['=HAS_EMAIL'] = 'Y';
		}
		if (in_array(\CCrmFieldMulti::IM, $commTypes))
		{
			$filter['=HAS_IMOL'] = 'Y';
		}
	}

	protected static function getNoPurchasesFilter($value, &$filter, $extraCallbackParams = [])
	{
		$entityTypeName = $extraCallbackParams['ENTITY_TYPE_NAME'];
		$field = $extraCallbackParams['FIELD'];
		if (!$entityTypeName)
			return;

		if ($value[$field['id'] . '_datesel'] != 'NONE')
		{
			$filter['NO_PURCHASES'] = [];
			self::setFieldTypeFilter('%PURCHASE_DATE%', $field, $filter['NO_PURCHASES']);
			self::processRuntimeFilter($filter['NO_PURCHASES'], $entityTypeName);
		}
	}

	protected static function productSourceFilter($value, &$filter, $extraCallbackParams = [])
	{
		if ($filter['NO_PURCHASES'] || $filter['=COMPANY.PRODUCT_ID'] || $filter['=CONTACT.PRODUCT_ID'])
		{
			if (!is_array($value) || in_array("", $value)) // if PRODUCT_SOURCE wasn't set or has "everywhere" value
			{
				$value = [];
			}
			$filter['PRODUCT_SOURCE'] = $value;
		}
	}

	protected static function getDealCategoryFilter($values, &$filter, $extraCallbackParams = [])
	{
		$filter['DEAL'] = [];
		foreach ($values as $k => $value)
		{
			if(is_null($value))
			{
				$filter['DEAL'][] = ['SGT_DEAL.ID', '=', $value];
				$filter['DEAL']['JOIN_TYPE'] = 'LEFT';
				unset($values[$k]);
			}
		}
		if(!empty($values))
		{
			$filter['DEAL'][] = ['SGT_DEAL.CATEGORY_ID', 'in', $values];
		}
	}

	/**
	 * Callback on draw of result view.
	 *
	 * @param array &$row Row.
	 * @return void
	 */
	public function onResultViewDraw(array &$row)
	{
		switch ($row['CRM_ENTITY_TYPE_ID'])
		{
			case \CCrmOwnerType::Company:
				$crmRow = \Bitrix\Crm\CompanyTable::getRowById($row['CRM_ENTITY_ID']);
				$row['~NAME'] = self::getResultViewTitle(
					$row['CRM_ENTITY_TYPE_ID'],
					$row['CRM_ENTITY_ID'],
					$row['NAME'],
					\CCrmOwnerType::GetDescription($row['CRM_ENTITY_TYPE_ID']),
					self::getCrmStatusName('COMPANY_TYPE', $crmRow['COMPANY_TYPE'])
				);
				break;
			case \CCrmOwnerType::Contact:
				$crmRow = \Bitrix\Crm\ContactTable::getRowById($row['CRM_ENTITY_ID']);
				$row['~NAME'] = self::getResultViewTitle(
					$row['CRM_ENTITY_TYPE_ID'],
					$row['CRM_ENTITY_ID'],
					$row['NAME'],
					\CCrmOwnerType::GetDescription($row['CRM_ENTITY_TYPE_ID']),
					self::getCrmStatusName('SOURCE', $crmRow['SOURCE_ID'])
				);
				break;
			case \CCrmOwnerType::Lead:
				$crmRow = \Bitrix\Crm\LeadTable::getRowById($row['CRM_ENTITY_ID']);
				$row['CRM_LEAD'] = $row['~CRM_LEAD'] = self::getResultViewTitle(
					$row['CRM_ENTITY_TYPE_ID'],
					$row['CRM_ENTITY_ID'],
					$crmRow['TITLE'],
					self::getCrmStatusName('SOURCE', $crmRow['SOURCE_ID']),
					$crmRow['IS_RETURN_CUSTOMER'] === 'Y' ? Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_LEAD_FIELD_RC_LEAD') : null
				);
				break;
		}
	}

	protected function getCrmStatusName($statusType, $statusId)
	{
		if (!$statusId)
		{
			return null;
		}

		$sources = \CCrmStatus::GetStatus($statusType);
		if (empty($sources[$statusId]))
		{
			return $statusId;
		}

		return $sources[$statusId]['NAME'];
	}

	protected function getResultViewTitle($entityTypeId, $entityId, $title, $secondTitle = null, $thirdTitle = null)
	{
		$url = self::getPathToDetail($entityTypeId, $entityId);
		$title = htmlspecialcharsbx($title);
		if ($url && \CCrmAuthorizationHelper::checkReadPermission($entityTypeId, $entityId))
		{
			$title = '<a href="' . $url . '">' . $title . '</a>';
		}

		if ($secondTitle)
		{
			$title .= "<br><span style=\"color:grey; font-size: 12px;\">";
			$title .= htmlspecialcharsbx($secondTitle);
			$title .= "</span>";
		}

		if ($thirdTitle)
		{
			$title .= "<br><span style=\"color:grey; font-size: 12px;\">";
			$title .= htmlspecialcharsbx($thirdTitle);
			$title .= "</span>";
		}

		return $title;
	}

	protected function getPathToDetail($entityTypeId, $entityId)
	{
		switch ($entityTypeId)
		{
			case \CCrmOwnerType::Company:
				$optionName = 'path_to_company_details';
				break;
			case \CCrmOwnerType::Contact:
				$optionName = 'path_to_contact_details';
				break;
			case \CCrmOwnerType::Lead:
				$optionName = 'path_to_lead_details';
				break;
			default:
				$optionName = null;
		}

		$url = $optionName ? Option::get('crm', $optionName) : null;
		if ($url)
		{
			$url = str_replace(
				['#company_id#', '#contact_id#', '#lead_id#'],
				$entityId,
				$url
			);
		}

		return $url;
	}

	public static function isCrmSaleEnabled()
	{
		return Loader::includeModule("sale") && (Option::get("crm", "crm_shop_enabled", "N") != 'N');
	}
}
