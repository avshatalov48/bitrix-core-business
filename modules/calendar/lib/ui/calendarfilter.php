<?php

namespace Bitrix\Calendar\Ui;

use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Filter;

class CalendarFilter
{
	protected static $filterId = '';

	/**
	 * Get available fields in filter.
	 * @return array
	 */
	protected static function getAvailableFields()
	{
		$fields = array(
			'CREATED_BY',
			'ATTENDEES',
			'IS_MEETING',
			'IS_RECURSIVE',
			'MEETING_STATUS',
			'DATE_FROM',
			'DATE_TO'
		);

		return $fields;
	}


	/**
	 * @return string
	 */
	public static function getFilterId($type, $ownerId, $userId)
	{
		if(!static::$filterId)
		{
			static::$filterId = 'calendar-filter';
			if ($type == 'user' && $ownerId == $userId)
				static::$filterId = 'calendar-filter-personal';
		}

		return static::$filterId;
	}

	/**
	 * @param $filterId
	 */
	public static function setFilterId($filterId)
	{
		static::$filterId = $filterId;
	}

	/**
	 * @return array
	 */
	public static function getPresets()
	{
		$presets = array(
			'filter_calendar_meeting_status_q' => array(
				'name' => Loc::getMessage('CALENDAR_PRESET_MEETING_STATUS_Q'),
				'default' => false,
				'fields' => array(
					'MEETING_STATUS' => 'Q',
					'IS_MEETING' => 'Y'
				)
			),
			'filter_calendar_host' => array(
				'name' => Loc::getMessage('CALENDAR_PRESET_I_AM_HOST'),
				'default' => false,
				'fields' => array(
					'IS_MEETING' => true,
					'MEETING_STATUS' => 'H'
				)
			),
			'filter_calendar_attendee' => array(
				'name' => Loc::getMessage('CALENDAR_PRESET_I_AM_ATTENDEE'),
				'default' => false,
				'fields' => array(
					'IS_MEETING' => true,
					'MEETING_STATUS' => 'Y'
				)
			)
		);

		return $presets;
	}

	/**
	 * @param string $filterId
	 *
	 * @return array
	 */
	public static function resolveFilterFields($filterId)
	{
		$filterOptions = new \Bitrix\Main\UI\Filter\Options($filterId);
		$fields = $filterOptions->getFilter();
		$result = array(
			'search' => $filterOptions->getSearchString(),
			'presetId' => $fields['PRESET_ID'],
			'fields' => array()
		);

		$fieldNames = self::getAvailableFields();
		foreach ($fields as $key => $value)
		{
			if ($key == 'DATE_from')
			{
				$result['fields']['DATE_FROM'] = $value;
			}
			elseif ($key == 'DATE_to')
			{
				$result['fields']['DATE_TO'] = $value;
			}
			elseif ($key == 'ATTENDEES' || $key == 'CREATED_BY')
			{
				$userList = array();
				foreach ($value as $code)
				{
					if(substr($code, 0, 1) == 'U')
					{
						$userList[] = intVal(substr($code, 1));
					}
				}
				$result['fields'][$key] = $userList;
			}
			elseif (in_array($key, $fieldNames))
			{
				$result['fields'][$key] = $value;
			}
		}

		return $result;
	}



	/**
	 * @return array|bool
	 */
	/*protected static function processSpecialPresetsFilter()
	{
		$arrFilter = array();
		$request = Context::getCurrent()->getRequest()->toArray();
		if(array_key_exists('F_FILTER_SWITCH_PRESET', $request)
			&& static::getFilterCtrlInstance()->checkExistsPresetById($request['F_FILTER_SWITCH_PRESET']))
		{
			$arrFilter = static::getFilterCtrlInstance()->getFilterPresetConditionById($request['F_FILTER_SWITCH_PRESET']);
		}

		return $arrFilter;
	}*/

	/**
	 * @return array
	 */
	public static function getFilters()
	{
		static $filters = array();

		if (empty($filters))
		{
			$filters['CREATED_BY'] = array(
				'id' => 'CREATED_BY',
				'name' => Loc::getMessage('CALENDAR_FILTER_CREATED_BY'),
				'params' => array('multiple' => 'Y'),
				'type' => 'custom_entity',
				'selector' => array(
					'TYPE' => 'user',
					'DATA' => array(
						'ID' => 'user',
						'FIELD_ID' => 'CREATED_BY'
					)
				)
			);

			$filters['ATTENDEES'] = array(
				'id' => 'ATTENDEES',
				'name' => Loc::getMessage('CALENDAR_FILTER_ATTENDEES'),
				'params' => array('multiple' => 'Y'),
				'type' => 'custom_entity',
				'selector' => array(
					'TYPE' => 'user',
					'DATA' => array(
						'ID' => 'user',
						'FIELD_ID' => 'ATTENDEES'
					)
				)
			);

			$filters['IS_MEETING'] = array(
				'id' => 'IS_MEETING',
				'name' => Loc::getMessage('CALENDAR_FILTER_IS_MEETING'),
				'type' => 'checkbox',
				'default' => true,
			);

			$filters['MEETING_STATUS'] = array(
				'id' => 'MEETING_STATUS',
				'name' => Loc::getMessage('CALENDAR_FILTER_MEETING_STATUS'),
				'type' => 'list',
				'params' => array(
					'multiple' => 'N'
				),
				'items' => array(
					'H' => Loc::getMessage('CALENDAR_FILTER_MEETING_STATUS_H'),
					'Q' => Loc::getMessage('CALENDAR_FILTER_MEETING_STATUS_Q'),
					'Y' => Loc::getMessage('CALENDAR_FILTER_MEETING_STATUS_Y'),
					'N' => Loc::getMessage('CALENDAR_FILTER_MEETING_STATUS_N')
					//'I' => Loc::getMessage('CALENDAR_FILTER_MEETING_STATUS_I'),
				)
			);

			$filters['DATE'] = array(
				'id' => 'DATE',
				'name' => Loc::getMessage('CALENDAR_FILTER_DATE'),
				'type' => 'date'
			);
		}

		return $filters;
	}


	/**
	 * @return array
	 */
	/*
	protected static function getFilterRaw()
	{
		$fields = static::getAvailableFields();
		$filter = array();

		if (in_array('CREATED_BY', $fields))
		{
			$filter['CREATED_BY'] = array(
				'id' => 'CREATED_BY',
				'name' => Loc::getMessage('TASKS_HELPER_FLT_CREATED_BY'),
				'params' => array('multiple' => 'Y'),
				'type' => 'custom_entity',
				'selector' => array(
					'TYPE' => 'user',
					'DATA' => array(
						'ID' => 'user',
						'FIELD_ID' => 'CREATED_BY'
					)
				)
			);
		}

		if (in_array('RESPONSIBLE_ID', $fields))
		{
			$filter['RESPONSIBLE_ID'] = array(
				'id' => 'RESPONSIBLE_ID',
				'name' => Loc::getMessage('TASKS_HELPER_FLT_RESPONSIBLE_ID'),
				'params' => array('multiple' => 'Y'),
				'type' => 'custom_entity',
				'selector' => array(
					'TYPE' => 'user',
					'DATA' => array(
						'ID' => 'user',
						'FIELD_ID' => 'RESPONSIBLE_ID'
					)
				)
			);
		}

		if (in_array('STATUS', $fields))
		{
			$filter['STATUS'] = array(
				'id' => 'STATUS',
				'name' => Loc::getMessage('TASKS_FILTER_STATUS'),
				'type' => 'list',
				'params' => array(
					'multiple' => 'Y'
				),
				'items' => array(
					//					\CTasks::METASTATE_VIRGIN_NEW => Loc::getMessage('TASKS_STATUS_1'),
					\CTasks::STATE_PENDING => Loc::getMessage('TASKS_STATUS_2'),
					\CTasks::STATE_IN_PROGRESS => Loc::getMessage('TASKS_STATUS_3'),
					\CTasks::STATE_SUPPOSEDLY_COMPLETED => Loc::getMessage('TASKS_STATUS_4'),
					\CTasks::STATE_COMPLETED => Loc::getMessage('TASKS_STATUS_5')
				)
			);
		}

		if (in_array('DEADLINE', $fields))
		{
			$filter['DEADLINE'] = array(
				'id' => 'DEADLINE',
				'name' => Loc::getMessage('TASKS_FILTER_DEADLINE'),
				'type' => 'date'
			);
		}

		if (in_array('GROUP_ID', $fields))
		{
			$filter['GROUP_ID'] = array(
				'id' => 'GROUP_ID',
				'name' => Loc::getMessage('TASKS_HELPER_FLT_GROUP'),
				'params' => array('multiple' => 'Y'),
				'type' => 'custom_entity',
				'selector' => array(
					'TYPE' => 'group',
					'DATA' => array(
						'ID' => 'group',
						'FIELD_ID' => 'GROUP_ID'
					)
				)
			);
		}

		if (in_array('PROBLEM', $fields))
		{
			$filter['PROBLEM'] = array(
				'id' => 'PROBLEM',
				'name' => Loc::getMessage('TASKS_FILTER_PROBLEM'),
				'type' => 'list',
				'items' => self::getAllowedTaskCategories()
			);
		}

		if (in_array('PARAMS', $fields))
		{
			$filter['PARAMS'] = array(
				'id' => 'PARAMS',
				'name' => Loc::getMessage('TASKS_FILTER_PARAMS'),
				'type' => 'list',
				'params' => array(
					'multiple' => 'Y'
				),
				'items' => array(
					'MARKED'=>Loc::getMessage('TASKS_FILTER_PARAMS_MARKED'),
					'IN_REPORT'=>Loc::getMessage('TASKS_FILTER_PARAMS_IN_REPORT'),
					'OVERDUED'=>Loc::getMessage('TASKS_FILTER_PARAMS_OVERDUED'),
//					'SUBORDINATE'=>Loc::getMessage('TASKS_FILTER_PARAMS_SUBORDINATE'),
					'ANY_TASK'=>Loc::getMessage('TASKS_FILTER_PARAMS_ANY_TASK')
				)
			);
		}

		if (in_array('ID', $fields))
		{
			$filter['ID'] = array(
				'id' => 'ID',
				'name' => Loc::getMessage('TASKS_FILTER_ID'),
				'type' => 'number'
			);
		}
		if (in_array('TITLE', $fields))
		{
			$filter['TITLE'] = array(
				'id' => 'TITLE',
				'name' => Loc::getMessage('TASKS_FILTER_TITLE'),
				'type' => 'string'
			);
		}
		if (in_array('PRIORITY', $fields))
		{
			$filter['PRIORITY'] = array(
				'id' => 'PRIORITY',
				'name' => Loc::getMessage('TASKS_PRIORITY'),
				'type' => 'list',
				'items' => array(
					1 => Loc::getMessage('TASKS_PRIORITY_1'),
					2 => Loc::getMessage('TASKS_PRIORITY_2'),
				)
			);
		}
		if (in_array('MARK', $fields))
		{
			$filter['MARK'] = array(
				'id' => 'MARK',
				'name' => Loc::getMessage('TASKS_FILTER_MARK'),
				'type' => 'list',
				'items' => array(
					'P' => Loc::getMessage('TASKS_MARK_P'),
					'N' => Loc::getMessage('TASKS_MARK_N')
				)
			);
		}
		if (in_array('ALLOW_TIME_TRACKING', $fields))
		{
			$filter['ALLOW_TIME_TRACKING'] = array(
				'id' => 'ALLOW_TIME_TRACKING',
				'name' => Loc::getMessage('TASKS_FILTER_ALLOW_TIME_TRACKING'),
				'type' => 'list',
				'items' => array(
					'Y' => Loc::getMessage('TASKS_ALLOW_TIME_TRACKING_Y'),
					'N' => Loc::getMessage('TASKS_ALLOW_TIME_TRACKING_N'),
				)
			);
		}
		if (in_array('CREATED_DATE', $fields))
		{
			$filter['CREATED_DATE'] = array(
				'id' => 'CREATED_DATE',
				'name' => Loc::getMessage('TASKS_FILTER_CREATED_DATE'),
				'type' => 'date'
			);
		}
		if (in_array('CLOSED_DATE', $fields))
		{
			$filter['CLOSED_DATE'] = array(
				'id' => 'CLOSED_DATE',
				'name' => Loc::getMessage('TASKS_FILTER_CLOSED_DATE'),
				'type' => 'date'
			);
		}
		if (in_array('DATE_START', $fields))
		{
			$filter['DATE_START'] = array(
				'id' => 'DATE_START',
				'name' => Loc::getMessage('TASKS_FILTER_DATE_START'),
				'type' => 'date'
			);
		}
		if (in_array('START_DATE_PLAN', $fields))
		{
			$filter['START_DATE_PLAN'] = array(
				'id' => 'START_DATE_PLAN',
				'name' => Loc::getMessage('TASKS_FILTER_START_DATE_PLAN'),
				'type' => 'date'
			);
		}
		if (in_array('END_DATE_PLAN', $fields))
		{
			$filter['END_DATE_PLAN'] = array(
				'id' => 'END_DATE_PLAN',
				'name' => Loc::getMessage('TASKS_FILTER_END_DATE_PLAN'),
				'type' => 'date'
			);
		}

		if (in_array('ACTIVE', $fields))
		{
			$filter['ACTIVE'] = array(
				'id' => 'ACTIVE',
				'name' => Loc::getMessage('TASKS_FILTER_ACTIVE'),
				'type' => 'date'
			);
		}

		if (in_array('ACCOMPLICE', $fields))
		{
			$filter['ACCOMPLICE'] = array(
				'id' => 'ACCOMPLICE',
				'name' => Loc::getMessage('TASKS_HELPER_FLT_ACCOMPLICES'),
				'params' => array('multiple' => 'Y'),
				'type' => 'custom_entity',
				'selector' => array(
					'TYPE' => 'user',
					'DATA' => array(
						'ID' => 'user',
						'FIELD_ID' => 'ACCOMPLICE'
					)
				)
			);
		}
		if (in_array('AUDITOR', $fields))
		{
			$filter['AUDITOR'] = array(
				'id' => 'AUDITOR',
				'name' => Loc::getMessage('TASKS_HELPER_FLT_AUDITOR'),
				'params' => array('multiple' => 'Y'),
				'type' => 'custom_entity',
				'selector' => array(
					'TYPE' => 'user',
					'DATA' => array(
						'ID' => 'user',
						'FIELD_ID' => 'AUDITOR'
					)
				)
			);
		}

		if (in_array('TAG', $fields))
		{
			$filter['TAG'] = array(
				'id' => 'TAG',
				'name' => Loc::getMessage('TASKS_FILTER_TAG'),
				'type' => 'string'
			);
		}

		if (in_array('ROLEID', $fields))
		{
			$roles = \CTaskListState::getKnownRoles();
			foreach($roles as $roleId)
			{
				$roleCodeName = strtolower(\CTaskListState::resolveConstantCodename($roleId));
				$items[ $roleCodeName ] = \CTaskListState::getRoleNameById($roleId);
			}
			$filter['ROLEID'] = array(
				'id' => 'ROLEID',
				'name' => Loc::getMessage('TASKS_FILTER_ROLEID'),
				'type' => 'list',
				'default'=>true,
				'items'=> $items
			);
		}

		return $filter;
	}*/
}