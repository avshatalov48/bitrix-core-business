<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\Crm\Connectors;

use Bitrix\Main\Config\Option;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Entity;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI\Filter\Type as UiFilterType;
use Bitrix\Main\UI\Filter\AdditionalDateType;

use Bitrix\Crm\CompanyTable;
use Bitrix\Crm\LeadTable;
use Bitrix\Crm\ContactTable;

use Bitrix\Sender\Recipient;
use Bitrix\Sender\Connector;
use Bitrix\Sender\Integration;

Loc::loadMessages(__FILE__);

/**
 * Class Helper
 * @package Bitrix\Sender\Integration\Crm\Connectors
 */
class Helper
{
	protected static $runtimeByEntity = [];

	/**
	 * Get personalize field list.
	 *
	 * @return array
	 */
	public static function getPersonalizeList()
	{
		return array(
			array('CODE' => 'CRM_ENTITY_TYPE_ID'),
			array('CODE' => 'CRM_ENTITY_ID'),
		);
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

		foreach ($list as $index => $field)
		{
			if ($field['type'] !== 'date')
			{
				continue;
			}

			$list[$index]['include'] = [AdditionalDateType::CUSTOM_DATE];
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
				'operator' => '>',
				'value' => 0
			],
			Recipient\Type::CRM_COMPANY_ID => [
				'name' => 'COMPANY_ID',
				'operator' => '>',
				'value' => 0
			],
		);

		$entityName = strtoupper($query->getEntity()->getName());

		if ($dataTypeId)
		{
			if (!isset($map[$dataTypeId]))
			{
				return $query;
			}

			$field = $map[$dataTypeId];

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

		$entityTypeName = strtoupper($query->getEntity()->getName());
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
				if (strpos($id, $search) !== 0)
				{
					continue;
				}

				foreach ($entityTypes as $entityTypeName)
				{
					$filterKey = "$entityTypeName." . substr($id, strlen($search));
					if (!self::isFieldTypeFilter($field['type']))
					{
						$filterKey = "=$filterKey";
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

		if (!isset($values[$id]) || !$values[$id])
		{
			return false;
		}

		return true;
	}

	public static function getFilterByFields(array $fields = array(), array $values = array(), $entityTypeName = '')
	{
		self::$runtimeByEntity = [];
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
			$value = $isMultiple && !is_array($value) ? array($value) : $value;
			$field['value'] = $value;

			if (strpos($field['id'], 'COMMUNICATION_TYPE') !== false)
			{
				self::getCommunicationTypeFilter($value, $filter);
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
			UiFilterType::NUMBER
		);
		return in_array(strtoupper($type), $types);
	}

	protected static function setFieldTypeFilter($filterKey, array $fieldData, &$filter)
	{
		$fieldData['filter-key'] = $filterKey;
		switch (strtoupper($fieldData['type']))
		{
			case UiFilterType::DATE:
				Connector\Filter\DateField::create($fieldData)->applyFilter($filter);
				break;
			case UiFilterType::NUMBER:
				Connector\Filter\NumberField::create($fieldData)->applyFilter($filter);
				break;
		}
	}

	protected static function getCommunicationTypeFilter(array $commTypes, &$filter)
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

	public static function getFilterFieldUserSelector(array $userSelector, $filterID)
	{
		if(empty($userSelector))
		{
			return '';
		}

		$userSelectors = array($userSelector);
		Asset::getInstance()->addJs('/bitrix/js/crm/common.js');
		ob_start();
		$componentName = "{$filterID}_FILTER_USER";
		$GLOBALS['APPLICATION']->includeComponent(
			'bitrix:intranet.user.selector.new',
			'',
			array(
				'MULTIPLE' => 'N',
				'NAME' => $componentName,
				'INPUT_NAME' => strtolower($componentName),
				'SHOW_EXTRANET_USERS' => 'NONE',
				'POPUP' => 'Y',
				'SITE_ID' => SITE_DIR,
				//'NAME_TEMPLATE' => $nameTemplate
			),
			null,
			array('HIDE_ICONS' => 'Y')
		);
		?><script type="text/javascript"><?
		foreach($userSelectors as $userSelector)
		{
			$selectorID = $userSelector['ID'];
			$fieldID = $userSelector['FIELD_ID'];
			?>
			BX.ready(
				function()
				{
					BX.FilterUserSelector.create(
						"<?=\CUtil::JSEscape($selectorID)?>",
						{
							fieldId: "<?=\CUtil::JSEscape($fieldID)?>",
							componentName: "<?=\CUtil::JSEscape($componentName)?>"
						}
					);
				}
			);
			<?
		}
		?></script><?

		return ob_get_clean();
	}

	/**
	 * Callback on draw of result view.
	 *
	 * @param array $row Row.
	 * @return void
	 */
	public function onResultViewDraw(array &$row)
	{
		switch ($row['CRM_ENTITY_TYPE_ID'])
		{
			case \CCrmOwnerType::Company:
				$crmRow = CompanyTable::getRowById($row['CRM_ENTITY_ID']);
				$row['~NAME'] = self::getResultViewTitle(
					$row['CRM_ENTITY_TYPE_ID'],
					$row['CRM_ENTITY_ID'],
					$row['NAME'],
					\CCrmOwnerType::GetDescription($row['CRM_ENTITY_TYPE_ID']),
					self::getCrmStatusName('COMPANY_TYPE', $crmRow['COMPANY_TYPE'])
				);
				break;
			case \CCrmOwnerType::Contact:
				$crmRow = ContactTable::getRowById($row['CRM_ENTITY_ID']);
				 $row['~NAME'] = self::getResultViewTitle(
					$row['CRM_ENTITY_TYPE_ID'],
					$row['CRM_ENTITY_ID'],
					$row['NAME'],
					 \CCrmOwnerType::GetDescription($row['CRM_ENTITY_TYPE_ID']),
					 self::getCrmStatusName('SOURCE', $crmRow['SOURCE_ID'])
				);
				break;
			case \CCrmOwnerType::Lead:
				$crmRow = LeadTable::getRowById($row['CRM_ENTITY_ID']);
				$row['CRM_LEAD'] = $row['~CRM_LEAD'] = self::getResultViewTitle(
					$row['CRM_ENTITY_TYPE_ID'],
					$row['CRM_ENTITY_ID'],
					$crmRow['TITLE'],
					$crmRow['IS_RETURN_CUSTOMER'] === 'Y' ? Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_LEAD_FIELD_RC_LEAD') : null,
					self::getCrmStatusName('SOURCE', $crmRow['SOURCE_ID'])
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
}
