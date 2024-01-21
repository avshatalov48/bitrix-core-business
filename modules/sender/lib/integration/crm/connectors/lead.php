<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\Crm\Connectors;

use Bitrix\Crm\LeadTable;
use Bitrix\Crm\PhaseSemantics;
use Bitrix\Crm\Service\Container;
use Bitrix\Main\DB\Result;
use Bitrix\Main\Entity;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI\Filter\AdditionalDateType;
use Bitrix\Sender\Connector;
use Bitrix\Sender\Connector\BaseFilter as ConnectorBaseFilter;
use Bitrix\Sender\Connector\ResultView;

Loc::loadMessages(__FILE__);

/**
 * Class Lead
 * @package Bitrix\Sender\Integration\Crm\Connectors
 */
class Lead extends ConnectorBaseFilter implements Connector\IncrementallyConnector
{
	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_LEAD_NAME');
	}

	/**
	 * Get code.
	 *
	 * @return string
	 */
	public function getCode()
	{
		return "crm_lead";
	}

	/**
	 * Get query.
	 *
	 * @return null|Entity\Query
	 */
	public function getQuery($selectList = [])
	{
		$filter = Helper::getFilterByFields(
			self::getUiFilterFields(),
			$this->getFieldValues()
		);
		$runtime = Helper::getRuntimeByEntity(null);

		$sqlHelper = \Bitrix\Main\Application::getConnection()->getSqlHelper();

		$nameExprField = new Entity\ExpressionField(
			$sqlHelper->quote('NAME'),
			'CASE 
			WHEN %2$s=\'Y\' AND %3$s>0 THEN %4$s
			WHEN %2$s=\'Y\' AND %5$s>0 THEN %6$s
			ELSE %1$s
			END',
			[
				'NAME', 'IS_RETURN_CUSTOMER',
				'CONTACT_ID', 'CONTACT.NAME',
				'COMPANY_ID', 'COMPANY.TITLE'
			]
		);

		$query = new Entity\Query(LeadTable::getEntity());
		$query->setFilter($filter);
		$query->setSelect(array_merge($selectList, [
			$nameExprField,
			'CRM_ENTITY_ID' => 'ID',
			'CRM_ENTITY_TYPE_ID',
			'CRM_ENTITY_TYPE',
			'CRM_CONTACT_ID' => 'CONTACT_ID',
			'CRM_COMPANY_ID' => 'COMPANY_ID',
		]));

		$query->registerRuntimeField(new Entity\ExpressionField('CRM_ENTITY_TYPE', '\''.\CCrmOwnerType::LeadName.'\''));

		$query->registerRuntimeField(Helper::createExpressionMultiField(
			\CCrmOwnerType::LeadName,
			'EMAIL'
		));
		$query->registerRuntimeField(Helper::createExpressionMultiField(
			\CCrmOwnerType::LeadName,
			'PHONE'
		));
		$query->registerRuntimeField(new Entity\ExpressionField('CRM_ENTITY_TYPE_ID', \CCrmOwnerType::Lead));
		$query->registerRuntimeField(null, new Entity\ReferenceField(
			'CONTACT',
			"\\Bitrix\\Crm\\ContactTable",
			['=this.CONTACT_ID' => 'ref.ID'],
			['join_type' => 'LEFT']
		));
		$query->registerRuntimeField(null, new Entity\ReferenceField(
			'COMPANY',
			"\\Bitrix\\Crm\\CompanyTable",
			['=this.COMPANY_ID' => 'ref.ID'],
			['join_type' => 'LEFT']
		));
		foreach ($runtime as $item)
		{
			$item = new Entity\ExpressionField(
				$item['name'],
				$item['expression'],
				$item['buildFrom']
			);
			$query->registerRuntimeField($item);
		}

		//echo "<pre>" . $query->getQuery(); die();

		return $query;
	}

	/**
	 * Get data count by type.
	 *
	 * @return null|array
	 */
	protected function getDataCountByType()
	{
		if (!$this->hasFieldValues())
		{
			return array();
		}

		return QueryCount::getCount($this->getQuery(), $this->getDataTypeId());
	}

	/**
	 * Get data.
	 *
	 * @return array|Result
	 */
	public function getData()
	{
		if (!$this->hasFieldValues())
		{
			return array();
		}

		$query = $this->getQuery();
		if ($this->getResultView()->hasNav())
		{
			$query->setOffset($this->getResultView()->getNav()->getOffset());
			$query->setLimit($this->getResultView()->getNav()->getLimit());
		}

		return QueryData::getData($query, $this->getDataTypeId());
	}

	/**
	 * Get personalize field list.
	 *
	 * @return array
	 */
	public static function getPersonalizeList()
	{
		return Loader::includeModule('crm') ? array_merge(
			Helper::getPersonalizeList(),
			Helper::buildPersonalizeList(\CCrmOwnerType::LeadName)
		) : Helper::getPersonalizeList();
	}

	/**
	 * Get filter fields.
	 *
	 * @param bool $checkAccessRights
	 *
	 * @return array
	 * @throws \Bitrix\Main\NotSupportedException
	 */
	public static function getUiFilterFields(bool $checkAccessRights = true): array
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
			"id" => "DATE_CREATE",
			"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_LEAD_FIELD_DATE_CREATE'),
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
			"id" => "STATUS_ID",
			"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_LEAD_FIELD_STATUS_ID'),
			"type" => "list",
			"items" => \CCrmStatus::GetStatusList('STATUS'),
			"default" => true,
			'params' => array('multiple' => 'Y'),
		);

		$list[] = array(
			"id" => "SOURCE_ID",
			"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_LEAD_FIELD_SOURCE_ID'),
			"type" => "list",
			"items" => \CCrmStatus::GetStatusList('SOURCE'),
			"default" => true,
			'params' => array('multiple' => 'Y'),
		);

		$list[] = array(
			'id' => 'COMMUNICATION_TYPE',
			"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_LEAD_FIELD_COMMUNICATION_TYPE'),
			'params' => array('multiple' => 'Y'),
			'default' => true,
			'type' => 'list',
			'items' => \CCrmFieldMulti::PrepareListItems(array(
				\CCrmFieldMulti::PHONE,
				\CCrmFieldMulti::EMAIL,
				\CCrmFieldMulti::IM
			)),
			'filter_callback' => ['\Bitrix\Sender\Integration\Crm\Connectors\Helper', 'getCommunicationTypeFilter']
		);

		$list[] = PhaseSemantics::getListFilterInfo(
			\CCrmOwnerType::Lead,
			array(
				'id' => 'STATUS_SEMANTIC_ID',
				"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_LEAD_FIELD_STATUS_SEMANTIC_ID'),
				'default' => true,
				'params' => array('multiple' => 'Y'),
			),
			true
		);

		$list[] = array(
			'id' => 'PRODUCT_ROW.PRODUCT_ID',
			"name" => Loc::getMessage("SENDER_INTEGRATION_CRM_CONNECTOR_LEAD_FIELD_PRODUCT_ID"),
			'default' => true,
			'type' => 'dest_selector',
			'partial' => true,
			'params' => array(
				'multiple' => 'Y',
				'apiVersion' => 3,
				'context' => 'CRM_LEAD_FILTER_PRODUCT_ID',
				'contextCode' => 'CRM',
				'useClientDatabase' => 'N',
				'enableAll' => 'N',
				'enableDepartments' => 'N',
				'enableUsers' => 'N',
				'enableSonetgroups' => 'N',
				'allowEmailInvitation' => 'N',
				'allowSearchEmailUsers' => 'N',
				'departmentSelectDisable' => 'Y',
				'addTabCrmProducts' => 'Y',
				'enableCrm' => 'Y',
				'enableCrmProducts' => 'Y',
				'convertJson' => 'Y'
			)
		);

		$list[] = array(
			"id" => "STATUS_CONVERTED",
			"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_LEAD_FIELD_STATUS_CONVERTED'),
			'type' => 'checkbox',
			'sender_segment_filter' => array(
				'N' => array('=STATUS_SEMANTIC_ID', PhaseSemantics::PROCESS),
				'Y' => array('!=STATUS_SEMANTIC_ID', PhaseSemantics::PROCESS),
			),
			"default" => false,
		);

		$list[] = array(
			"id" => "ASSIGNED_BY_ID",
			"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_LEAD_FIELD_ASSIGNED_BY_ID'),
			'type' => 'dest_selector',
			'params' => array(
				'context' => 'SENDER_LEAD_FILTER_ASSIGNED_BY_ID',
				'multiple' => 'Y',
				'contextCode' => 'U',
				'enableAll' => 'N',
				'enableSonetgroups' => 'N',
				'allowEmailInvitation' => 'N',
				'allowSearchEmailUsers' => 'N',
				'departmentSelectDisable' => 'Y',
				'isNumeric' => 'Y',
				'prefix' => 'U'
			),
			"default" => false,
		);

		$list[] = array(
			"id" => "POST",
			'type' => 'string',
			"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_LEAD_FIELD_POST'),
			'params' => array('multiple' => 'Y'),
			"default" => false
		);

		$list[] = array(
			"id" => "BIRTHDATE",
			"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_LEAD_FIELD_BIRTHDATE'),
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
			'id' => 'HONORIFIC',
			'name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_LEAD_FIELD_HONORIFIC'),
			'params' => array('multiple' => 'Y'),
			'default' => false,
			'type' => 'list',
			'items' => \CCrmStatus::GetStatusList('HONORIFIC'),
		);

		return array_merge($list, Helper::getFilterUserFields(\CCrmOwnerType::Lead, $checkAccessRights));
	}

	/**
	 * Get filter presets.
	 *
	 * @return array
	 */
	public static function getUiFilterPresets()
	{
		return array(
			'crm_lead_all' => array(
				'name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_LEAD_PRESET_ALL'),
				'sender_segment_name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_LEAD_PRESET_SEGMENT_ALL'),
				'fields' => array(
					self::FIELD_FOR_PRESET_ALL => 'Y',
				)
			),
			'crm_lead_converted' => array(
				'name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_LEAD_PRESET_CONV'),
				'sender_segment_name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_LEAD_PRESET_SEGMENT_CONV'),
				'fields' => array(
					'STATUS_SEMANTIC_ID' => array(PhaseSemantics::SUCCESS),
				)
			),
			'crm_lead_in_work' => array(
				'name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_LEAD_PRESET_INW'),
				'sender_segment_name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_LEAD_PRESET_SEGMENT_INW'),
				'fields' => array(
					'STATUS_SEMANTIC_ID' => array(PhaseSemantics::PROCESS),
				)
			),
			'crm_lead_birthday' => array(
				'name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_LEAD_PRESET_BIRTH'),
				'sender_segment_name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_LEAD_PRESET_SEGMENT_BIRTH'),
				'sender_segment_business_case' => true,
				'fields' => array(
					'BIRTHDATE_datesel' => 'NEXT_DAY',
					'BIRTHDATE_days' => '5',
					'BIRTHDATE_allow_year' => '0',
				)
			),
		);
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
				function (array &$columns)
				{
					Asset::getInstance()->addJs('/bitrix/js/crm/common.js');
					$columns = array_merge(
						[[
							'id' => 'CRM_LEAD',
							'name' => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_LEAD_FIELD_LEAD'),
							'default' => true
						]],
						$columns
					);

					return $columns;
				}
			)
			->setCallback(
				ResultView::Draw,
				function(array &$row)
				{
					(new Helper())->onResultViewDraw($row);
				}
			);
	}

	public function getUiFilterId()
	{
		$code = str_replace('_', '', $this->getCode());
		return $this->getId()   . '_--filter--'.$code.'--';
	}

	/**
	 * Get fields for statistic
	 * @return array
	 */
	public function getStatFields()
	{
		return ['PRODUCT_ROW.PRODUCT_ID'];
	}

	/**
	 * @param int $offset
	 * @param int $limit
	 * @param string|null $excludeType
	 *
	 * @return array|Entity\Query[]|null[]
	 */
	public function getLimitedQueries(int $offset, int $limit, string $excludeType = null): array
	{
		$query = $this->getQuery();

		$query->whereBetween('ID', $offset, $limit);
		return [
			$query
		];
	}

	/**
	 * @return array
	 */
	public function getEntityLimitInfo(): array
	{
		$lastLead = \CCrmLead::GetListEx(
			['ID' => 'DESC'],
			['CHECK_PERMISSIONS' => 'N'],
			false,
			['nTopCount' => '1'],
			['ID']
		)->Fetch();
		$lastLeadId = $lastLead['ID'] ?? 0;

		return [
			'lastContactId' => 0,
			'lastCompanyId' => 0,
			'lastId' => $lastLeadId,
		];
	}

	/**
	 * @param int $offset
	 * @param int $limit
	 *
	 * @return Result
	 */
	public function getLimitedData(int $offset, int $limit): \Bitrix\Main\DB\Result
	{
		$query = $this->getLimitedQueries($offset, $limit)[0];
		return QueryData::getData($query, $this->getDataTypeId());
	}

	public function getContactDataLead(int $leadId): ?array
	{
		$query = LeadTable::query()
			->setSelect(['NAME', 'LAST_NAME', 'POST', 'HONORIFIC', 'BIRTHDATE', 'HAS_EMAIL', 'HAS_IMOL', 'HAS_PHONE'])
			->where('ID', $leadId);

		// $leadDb = LeadTable::getById($leadId);
		if ($lead = $query->fetch())
		{
			$contactsFields = [];

			if (
				($lead['HAS_EMAIL'] === 'Y')
				|| ($lead['HAS_IMOL'] === 'Y')
				|| ($lead['HAS_PHONE'] === 'Y')
			)
			{
				$leadMultiFields = Container::getInstance()
					->getMultifieldStorage()
					->get(new \Bitrix\Crm\ItemIdentifier(\CCrmOwnerType::Lead, $leadId));
				$contactsFields = $leadMultiFields->toArray();
			}

			return [
				'NAME' => $lead['NAME'],
				'LAST_NAME' => $lead['LAST_NAME'],
				'POST' => $lead['POST'],
				'HONORIFIC' => $lead['HONORIFIC'],
				'BIRTHDATE' => $lead['BIRTHDATE'],
				'FM' => $contactsFields,
			];
		}

		return null;
	}
}
