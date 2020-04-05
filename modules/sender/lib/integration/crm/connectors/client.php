<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Integration\Crm\Connectors;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Entity;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Type\Date;
use Bitrix\Main\UI\Filter\AdditionalDateType;

use Bitrix\Sender\Connector\BaseFilter as ConnectorBaseFilter;
use Bitrix\Sender\Connector\ResultView;
use Bitrix\Sender\Integration\Sender\Holiday;
use Bitrix\Crm\ContactTable as CrmContactTable;
use Bitrix\Crm\CompanyTable as CrmCompanyTable;
use Bitrix\Crm\PhaseSemantics;
use Bitrix\Crm\Category\DealCategory;

Loc::loadMessages(__FILE__);

/**
 * Class Client
 * @package Bitrix\Sender\Integration\Crm\Connectors
 */
class Client extends ConnectorBaseFilter
{
	private $crmEntityFilter = null;

	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_NAME');
	}

	/**
	 * Get code.
	 *
	 * @return string
	 */
	public function getCode()
	{
		return "crm_client";
	}

	/**
	 * Get queries.
	 *
	 * @return Entity\Query[]
	 */
	public function getQueries()
	{
		$queries = array();
		$clientType = $this->getFieldValue('CLIENT_TYPE');

		if (!$clientType || $clientType === \CCrmOwnerType::ContactName)
		{
			$query = CrmContactTable::query();
			$query->setFilter($this->getCrmEntityFilter(\CCrmOwnerType::ContactName));
			$this->addCrmEntityReferences($query);
			$query->registerRuntimeField(new Entity\ExpressionField('CRM_ENTITY_TYPE_ID', \CCrmOwnerType::Contact));
			$query->registerRuntimeField(new Entity\ExpressionField('CRM_COMPANY_ID', 0));
			$query->registerRuntimeField(new Entity\ExpressionField('CONTACT_ID', '%s', ['ID']));
			$query->setSelect([
				'NAME', 'CRM_ENTITY_ID' => 'ID', 'CRM_ENTITY_TYPE_ID',
				'CRM_CONTACT_ID' => 'CONTACT_ID', 'CRM_COMPANY_ID',
			]);
			$queries[] = $query;
		}
		if (!$clientType || $clientType === \CCrmOwnerType::CompanyName)
		{
			$query = CrmCompanyTable::query();
			$query->setFilter($this->getCrmEntityFilter(\CCrmOwnerType::CompanyName));
			$this->addCrmEntityReferences($query);
			$query->registerRuntimeField(new Entity\ExpressionField('CRM_ENTITY_TYPE_ID', \CCrmOwnerType::Company));
			$query->registerRuntimeField(new Entity\ExpressionField('CONTACT_ID', 0));
			$query->registerRuntimeField(new Entity\ExpressionField('COMPANY_ID', '%s', ['ID']));
			$query->setSelect([
				'NAME' => 'TITLE', 'CRM_ENTITY_ID' => 'ID', 'CRM_ENTITY_TYPE_ID',
				'CRM_CONTACT_ID' => 'CONTACT_ID', 'CRM_COMPANY_ID' => 'COMPANY_ID',
			]);
			$queries[] = $query;
		}

		/*
		ob_start();
		foreach ($queries as $query)
		{
			echo "<pre>";
			echo $query->getQuery();
			echo "\n\n\n";
		}
		$s = ob_get_clean();
		die($s);
		*/

		return $queries;
	}

	protected function addCrmEntityReferences(Entity\Query $query)
	{
		$docTypes = array();
		$docType = $this->getFieldValue('DOC_TYPE');
		if ($docType)
		{
			$docTypes[] = $docType;
		}
		else
		{
			foreach (array_keys(self::getCrmDocumentTypes()) as $entityTypeName)
			{
				$filter = $this->getCrmReferencedEntityFilter($entityTypeName);
				if (count($filter) === 0)
				{
					continue;
				}

				$docTypes[] = $entityTypeName;
			}
		}

		foreach ($docTypes as $docType)
		{
			$refClassName = "\\Bitrix\\Crm\\" . ucfirst(strtolower($docType)) . "Table";
			if (!class_exists($refClassName))
			{
				continue;
			}

			if ($query->getEntity()->getName() === 'Contact')
			{
				$ref = array('=this.ID' => 'ref.CONTACT_ID');
			}
			elseif ($query->getEntity()->getName() === 'Company')
			{
				$ref = array('=this.ID' => 'ref.COMPANY_ID');
			}
			else
			{
				continue;
			}

			$runtimeFieldName = "SGT_$docType";
			$query->registerRuntimeField(null, new Entity\ReferenceField(
				$runtimeFieldName,
				$refClassName,
				$ref,
				array('join_type' => 'INNER')
			));

			$filter = $this->getCrmReferencedEntityFilter($docType);
			foreach ($filter as $key => $value)
			{
				$pattern = "/^[\W]{0,2}$docType\./";
				if (preg_match($pattern, $key))
				{
					$key = str_replace("$docType.", "$runtimeFieldName.", $key);
				}

				$query->addFilter($key, $value);
			}

			$runtime = Helper::getRuntimeByEntity($docType);
			foreach ($runtime as $item)
			{
				$item = new Entity\ExpressionField(
					$item['name'],
					str_replace("$docType.", "$runtimeFieldName.", $item['expression']),
					array_map(
						function ($from) use ($docType, $runtimeFieldName)
						{
							return str_replace("$docType.", "$runtimeFieldName.", $from);
						},
						$item['buildFrom']
					)
				);
				$query->registerRuntimeField($item);
			}
		}

		$entityTypeName = strtoupper($query->getEntity()->getName());
		$runtime = Helper::getRuntimeByEntity($entityTypeName);
		foreach ($runtime as $item)
		{
			$item = new Entity\ExpressionField(
				$item['name'],
				$item['expression'],
				array_map(
					function ($from) use ($entityTypeName)
					{
						return str_replace("$entityTypeName.", "", $from);
					},
					$item['buildFrom']
				)
			);
			$query->registerRuntimeField($item);
		}
	}

	protected function getCrmReferencedEntityFilter($entityTypeName)
	{
		return $this->getCrmEntityFilter($entityTypeName, true);
	}

	protected function getCrmEntityFilter($entityTypeName, $isReferenced = false)
	{
		if ($this->crmEntityFilter === null)
		{
			$this->crmEntityFilter = Helper::getFilterByEntity(
				self::getUiFilterFields(),
				$this->getFieldValues(),
				array_keys(self::getCrmDocumentTypes())
			);
		}

		if (isset($this->crmEntityFilter[$entityTypeName]))
		{
			$filter = $this->crmEntityFilter[$entityTypeName];
		}
		else
		{
			$filter = array();
		}

		if ($isReferenced && count($filter) === 0)
		{
			return $filter;
		}

		$commonNames = ['ASSIGNED_BY_ID', 'EMAIL', 'PHONE', 'NAME'];
		foreach ($commonNames as $commonName)
		{
			$value = $this->getFieldValue($commonName);
			if (!$value)
			{
				continue;
			}

			if (in_array($commonName, ['EMAIL', 'PHONE', 'NAME']))
			{
				$commonName = "%$entityTypeName.$commonName";
			}
			else
			{
				$commonName = "=$entityTypeName.$commonName";
			}
			$filter[$commonName] = $value;
		}

		if ($isReferenced)
		{
			return $filter;
		}

		foreach ($filter as $key => $value)
		{
			$pattern = "/^([\W]{0,2})$entityTypeName\./";
			if (!preg_match($pattern, $key))
			{
				continue;
			}

			unset($filter[$key]);
			$key = preg_replace($pattern, '$1', $key);
			$filter[$key] = $value;
		}

		return $filter;
	}

	protected static function getCrmDocumentTypes()
	{
		$types = array(\CCrmOwnerType::Deal);

		$list = array();
		foreach ($types as $typeId)
		{
			$typeName = \CCrmOwnerType::resolveName($typeId);
			$typeCaption = \CCrmOwnerType::getDescription($typeId);
			$list[$typeName] = $typeCaption;
		}

		return $list;
	}

	/**
	 * Get data count by type.
	 *
	 * @return array
	 */
	protected function getDataCountByType()
	{
		if (!$this->hasFieldValues())
		{
			return array();
		}

		return QueryCount::getUnionizedCount($this->getQueries(), $this->getDataTypeId());
	}

	/**
	 * Get data.
	 *
	 * @return array|\Bitrix\Main\DB\Result
	 */
	public function getData()
	{
		if (!$this->hasFieldValues())
		{
			return array();
		}

		$query = QueryData::getUnionizedQuery(
			$this->getQueries(),
			$this->getDataTypeId(),
			$this->getResultView()->getNav()
		);

		return QueryData::getUnionizedData($query);
	}

	/**
	 * Get personalize field list.
	 *
	 * @return array
	 */
	public static function getPersonalizeList()
	{
		return Helper::getPersonalizeList();
	}

	/**
	 * Get filter fields.
	 *
	 * @return array
	 */
	public static function getUiFilterFields()
	{
		$list = [
			[
				'id' => 'EMAIL',
				'type' => 'string',
				'sender_segment_filter' => '%EMAIL',
				'sender_internal' => true
			],
			[
				'id' => 'PHONE',
				'type' => 'string',
				'sender_segment_filter' => '%PHONE',
				'sender_internal' => true
			],
			[
				'id' => 'NAME',
				'type' => 'string',
				'sender_segment_filter' => '%NAME',
				'sender_internal' => true
			],
		];

		$list[] = array(
			"id" => "DOC_TYPE",
			"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_DOC_TYPE'),
			"type" => "list",
			"items" => self::getCrmDocumentTypes(),
			"sender_segment_filter" => false,
			"default" => true,
		);

		$list[] = array(
			"id" => "CLIENT_TYPE",
			"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_CLIENT_TYPE'),
			"type" => "list",
			"items" => array(
				"" => Loc::getMessage(
					'SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_CLIENT_TYPE_NOT_SET',
					[
						'%default%' => \CCrmOwnerType::getDescription(\CCrmOwnerType::Contact) . ", " . \CCrmOwnerType::getDescription(\CCrmOwnerType::Company),
					]
				),
				\CCrmOwnerType::ContactName => \CCrmOwnerType::getDescription(\CCrmOwnerType::Contact),
				\CCrmOwnerType::CompanyName => \CCrmOwnerType::getDescription(\CCrmOwnerType::Company),
			),
			"sender_segment_filter" => false,
			"default" => true
		);

		$list[] = array(
			"id" => "DEAL_DATE_CREATE",
			"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_DEAL_DATE_CREATE'),
			"type" => "date",
			"include" => [
				AdditionalDateType::CUSTOM_DATE,
				AdditionalDateType::PREV_DAY,
				AdditionalDateType::NEXT_DAY,
				AdditionalDateType::MORE_THAN_DAYS_AGO,
				AdditionalDateType::AFTER_DAYS,
			],
			"allow_years_switcher" => true,
			"default" => true
		);

		$list[] = array(
			"id" => "DEAL_STAGE_ID",
			"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_DEAL_STATUS_ID'),
			"type" => "list",
			'params' => array('multiple' => 'Y'),
			"items" => DealCategory::getFullStageList(),
			"default" => true
		);

		$list[] = array(
			"id" => "CONTACT_SOURCE_ID",
			"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_CONTACT_SOURCE_ID'),
			"type" => "list",
			'params' => array('multiple' => 'Y'),
			"items" => \CCrmStatus::GetStatusList('SOURCE'),
			"default" => true
		);

		$list[] = array(
			'id' => 'CLIENT_COMMUNICATION_TYPE',
			"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_COMMUNICATION_TYPE'),
			'params' => array('multiple' => 'Y'),
			'default' => true,
			'type' => 'list',
			'items' => \CCrmFieldMulti::PrepareListItems(array(
				\CCrmFieldMulti::PHONE,
				\CCrmFieldMulti::EMAIL,
				\CCrmFieldMulti::IM
			))
		);

		$list[] = PhaseSemantics::getListFilterInfo(
			\CCrmOwnerType::Deal,
			array(
				'id' => 'DEAL_STAGE_SEMANTIC_ID',
				"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_DEAL_STATUS_SEMANTIC_ID'),
				'default' => true,
				'params' => array('multiple' => 'Y')
			),
			true
		);

		$list[] = array(
			"id" => "ASSIGNED_BY_ID",
			"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_ASSIGNED_BY_ID'),
			'type' => 'custom_entity',
			'params' => array('multiple' => 'Y'),
			'selector' => array(
				'TYPE' => 'user',
				'DATA' => array('ID' => 'assigned_by', 'FIELD_ID' => 'ASSIGNED_BY_ID'),
			),
			'sender_segment_callback' => function ($field)
			{
				return Helper::getFilterFieldUserSelector($field['selector']['DATA'], 'crm_segment_client');
			},
			"sender_segment_filter" => false,
			"default" => false
		);

		foreach ([\CCrmOwnerType::Company, \CCrmOwnerType::Contact, \CCrmOwnerType::Deal] as $entityTypeId)
		{
			$entityTypeCaption = \CCrmOwnerType::getDescription($entityTypeId);
			$entityTypeName = \CCrmOwnerType::resolveName($entityTypeId);
			$fieldId = "{$entityTypeName}_ASSIGNED_BY_ID";
			$list[] = array(
				"id" => $fieldId,
				"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_ASSIGNED_BY_ID') . " ($entityTypeCaption)",
				'type' => 'custom_entity',
				'params' => array('multiple' => 'Y'),
				'selector' => array(
					'TYPE' => 'user',
					'DATA' => array('ID' => strtolower($fieldId), 'FIELD_ID' => $fieldId),
				),
				'sender_segment_callback' => function ($field)
				{
					return Helper::getFilterFieldUserSelector($field['selector']['DATA'], 'crm_segment_client');
				},
				//"sender_segment_filter" => false,
				"default" => false
			);
		}

		$list[] = array(
			"id" => "CONTACT_BIRTHDATE",
			"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_CONTACT_BIRTHDATE'),
			'type' => 'date',
			"include" => [
				AdditionalDateType::CUSTOM_DATE,
				AdditionalDateType::PREV_DAY,
				AdditionalDateType::NEXT_DAY,
				AdditionalDateType::MORE_THAN_DAYS_AGO,
				AdditionalDateType::AFTER_DAYS,
			],
			"allow_years_switcher" => true,
			"default" => false,
		);

		$list[] = array(
			'id' => 'DEAL_CATEGORY_ID',
			'name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_DEAL_CATEGORY_ID'),
			'params' => array('multiple' => 'Y'),
			'default' => false,
			'type' => 'list',
			'items' => DealCategory::getSelectListItems(true)
		);

		$list[] = array(
			"id" => "DEAL_TYPE_ID",
			"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_DEAL_TYPE_ID'),
			"type" => "list",
			'params' => array('multiple' => 'Y'),
			"items" => \CCrmStatus::GetStatusList('DEAL_TYPE'),
			"default" => false
		);

		$list[] = array(
			"id" => "DEAL_OPPORTUNITY",
			"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_DEAL_OPPORTUNITY'),
			"type" => "number",
			"default" => false
		);

		$list[] = array(
			"id" => "DEAL_CLOSEDATE",
			"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_DEAL_CLOSEDATE'),
			"type" => "date",
			"include" => [
				AdditionalDateType::CUSTOM_DATE,
				AdditionalDateType::PREV_DAY,
				AdditionalDateType::NEXT_DAY,
				AdditionalDateType::MORE_THAN_DAYS_AGO,
				AdditionalDateType::AFTER_DAYS,
			],
			"allow_years_switcher" => true,
			"default" => false
		);

		$list[] = array(
			'id' => 'COMPANY_COMPANY_TYPE',
			'name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_COMPANY_TYPE'),
			'params' => array('multiple' => 'Y'),
			'default' => false,
			'type' => 'list',
			'items' => \CCrmStatus::GetStatusList('COMPANY_TYPE'),
		);

		$list[] = array(
			'id' => 'CONTACT_TYPE_ID',
			'name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_CONTACT_TYPE'),
			'params' => array('multiple' => 'Y'),
			'default' => false,
			'type' => 'list',
			'items' => \CCrmStatus::GetStatusList('CONTACT_TYPE'),
		);

		$list[] = array(
			'id' => 'COMPANY_INDUSTRY',
			'name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_FIELD_COMPANY_INDUSTRY'),
			'params' => array('multiple' => 'Y'),
			'default' => false,
			'type' => 'list',
			'items' => \CCrmStatus::GetStatusList('INDUSTRY'),
		);


		$entityTypes = array_merge(
			array(
				\CCrmOwnerType::ContactName,
				\CCrmOwnerType::CompanyName,
			),
			array_keys(self::getCrmDocumentTypes())
		);
		foreach ($entityTypes as $entityTypeName)
		{
			$entityTypeId = \CCrmOwnerType::resolveId($entityTypeName);
			$entityTypeCaption = \CCrmOwnerType::getDescription($entityTypeId);
			$ufList = Helper::getFilterUserFields($entityTypeId);
			foreach ($ufList as $item)
			{
				if (isset($item['name']))
				{
					$item['name'] .= " ($entityTypeCaption)";
				}
				elseif (isset($item['NAME']))
				{
					$item['NAME'] .= " ($entityTypeCaption)";
				}

				if (isset($item['id']))
				{
					$item['id'] = $entityTypeName . "_" . $item['id'];
				}
				elseif (isset($item['ID']))
				{
					$item['ID'] = $entityTypeName . "_" . $item['ID'];
				}

				$list[] = $item;
			}
		}

		return $list;
	}

	/**
	 * Get filter presets.
	 *
	 * @return array
	 */
	public static function getUiFilterPresets()
	{
		$list = array(
			'crm_client_all' => array(
				'name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_PRESET_ALL'),
				'sender_segment_name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_PRESET_SEGMENT_ALL'),
				'fields' => array(
					self::FIELD_FOR_PRESET_ALL => 'Y',
				)
			),
			'crm_client_deal_in_work' => array(
				'name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_PRESET_DEAL_INW'),
				'sender_segment_name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_PRESET_SEGMENT_DEAL_INW'),
				'fields' => array(
					'DEAL_STAGE_SEMANTIC_ID' => array(PhaseSemantics::PROCESS),
				)
			),
			'crm_client_deal_won' => array(
				'name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_PRESET_DEAL_WON'),
				'sender_segment_name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_PRESET_SEGMENT_DEAL_WON'),
				'fields' => array(
					'DEAL_STAGE_SEMANTIC_ID' => array(PhaseSemantics::SUCCESS),
				)
			),
			'crm_client_deal_loose' => array(
				'name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_PRESET_DEAL_LOOSE'),
				'sender_segment_name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_PRESET_SEGMENT_DEAL_LOOSE'),
				'fields' => array(
					'DEAL_STAGE_SEMANTIC_ID' => array(PhaseSemantics::FAILURE),
				)
			),
			'crm_client_birthday' => array(
				'name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_PRESET_BIRTH'),
				'sender_segment_name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_PRESET_SEGMENT_BIRTH'),
				'sender_segment_business_case' => true,
				'fields' => array(
					'CONTACT_BIRTHDATE_datesel' => 'NEXT_DAY',
					'CONTACT_BIRTHDATE_days' => '5',
					'CONTACT_BIRTHDATE_allow_year' => '0',
					'CLIENT_TYPE' => \CCrmOwnerType::ContactName
				)
			),
			'crm_client_aft_deal_clo' => array(
				'name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_PRESET_AFTER_CLOSE_DEAL'),
				'sender_segment_name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_PRESET_SEGMENT_AFTER_CLOSE_DEAL'),
				'sender_segment_business_case' => true,
				'fields' => array(
					'DEAL_CLOSEDATE_datesel' => 'PREV_DAY',
					'DEAL_CLOSEDATE_days' => "30",
					'DEAL_CLOSEDATE_allow_year' => '1',
				)
			),
		);

		foreach (Holiday::getList() as $holiday)
		{
			$code = $holiday->getCode();
			$name = $holiday->getName(
				Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_CLIENT_PRESET_HOLIDAY'),
				'%holiday_name%'
			);

			$list["crm_client_$code"] = [
				'name' => $name,
				'sender_segment_name' => $name,
				'sender_segment_business_case' => true,
				'fields' => [
					'DEAL_DATE_CREATE_datesel' => 'RANGE',
					'DEAL_DATE_CREATE_from' => $holiday->getDateFrom()->toString(),
					'DEAL_DATE_CREATE_to' => $holiday->getDateTo()->toString(),
					'CONTACT_BIRTHDATE_allow_year' => '0',
				]
			];
		}

		return $list;
	}

	/**
	 * Return true if support view of result.
	 *
	 * @return bool
	 */
	public function isResultViewable()
	{
		return true;
	}

	protected function onInitResultView()
	{
		$this->getResultView()
			->setCallback(
				ResultView::ColumnModifier,
				function ()
				{
					Asset::getInstance()->addJs('/bitrix/js/crm/common.js');
				}
			)
			->setCallback(
				ResultView::Draw,
				[__NAMESPACE__ . '\Helper', 'onResultViewDraw']
			);
	}
}
