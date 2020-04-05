<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\Crm\Connectors;

use Bitrix\Main\DB\Result;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Entity;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI\Filter\AdditionalDateType;

use Bitrix\Sender\Connector\BaseFilter as ConnectorBaseFilter;
use Bitrix\Sender\Connector\ResultView;

use Bitrix\Crm\LeadTable;
use Bitrix\Crm\PhaseSemantics;

Loc::loadMessages(__FILE__);

/**
 * Class Lead
 * @package Bitrix\Sender\Integration\Crm\Connectors
 */
class Lead extends ConnectorBaseFilter
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
	public function getQuery()
	{
		$filter = Helper::getFilterByFields(
			self::getUiFilterFields(),
			$this->getFieldValues()
		);
		$runtime = Helper::getRuntimeByEntity(null);

		$nameExprField = new Entity\ExpressionField(
			'`NAME`',
			'CASE %2$s WHEN \'Y\' ' .
			'THEN if (%3$s>0, %4$s, if (%5$s>0, %6$s, %1$s)) ELSE %1$s END',
			[
				'NAME', 'IS_RETURN_CUSTOMER',
				'CONTACT_ID', 'CONTACT.NAME',
				'COMPANY_ID', 'COMPANY.TITLE'
			]
		);

		$query = new Entity\Query(LeadTable::getEntity());
		$query->setFilter($filter);
		$query->setSelect([
			$nameExprField, 'CRM_ENTITY_ID' => 'ID', 'CRM_ENTITY_TYPE_ID',
			'CRM_CONTACT_ID' => 'CONTACT_ID', 'CRM_COMPANY_ID' => 'COMPANY_ID',
		]);
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
			"id" => "DATE_CREATE",
			"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_LEAD_FIELD_DATE_CREATE'),
			"type" => "date",
			"include" => [AdditionalDateType::CUSTOM_DATE],
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
			))
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
			'type' => 'custom_entity',
			'selector' => array(
				'TYPE' => 'user',
				'DATA' => array('ID' => 'assigned_by', 'FIELD_ID' => 'ASSIGNED_BY_ID')
			),
			"default" => false,
		);

		$list[] = array(
			"id" => "BIRTHDATE",
			"name" => Loc::getMessage('SENDER_INTEGRATION_CRM_CONNECTOR_LEAD_FIELD_BIRTHDATE'),
			'type' => 'date',
			"include" => [AdditionalDateType::CUSTOM_DATE],
			"default" => false,
		);

		$list = array_merge($list, Helper::getFilterUserFields(\CCrmOwnerType::Lead));

		return $list;
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
				[__NAMESPACE__ . '\Helper', 'onResultViewDraw']
			);
	}
}
