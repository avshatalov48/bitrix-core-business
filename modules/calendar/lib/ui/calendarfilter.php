<?php

namespace Bitrix\Calendar\Ui;

use Bitrix\Calendar\UserSettings;
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;

class CalendarFilter
{
	protected static $filterId = '';

	/**
	 * Get available fields in filter.
	 * @return array
	 */
	protected static function getAvailableFields(): array
	{
		return [
			'CREATED_BY',
			'ATTENDEES',
			'IS_MEETING',
			'IS_RECURSIVE',
			'MEETING_STATUS',
			'MEETING_HOST',
			'DATE_FROM',
			'DATE_TO',
			'SECTION_ID',
		];
	}


	/**
	 * @return string
	 */
	public static function getFilterId($type, $ownerId, $userId): string
	{
		static::$filterId = 'calendar-filter';
		if ($type === 'user' && (int)$ownerId === (int)$userId)
		{
			static::$filterId = 'calendar-filter-personal';
		}
		else if (
			$type === 'company_calendar'
			|| $type === 'calendar_company'
			|| $type === 'company'
		)
		{
			static::$filterId = 'calendar-filter-company';
		}
		else if ($type === 'group')
		{
			static::$filterId = 'calendar-filter-group';
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
	public static function getPresets($type): array
	{
		$presets = [];
		if ($type === 'user')
		{
			$presets ['filter_calendar_meeting_status_q'] = [
				'name' => Loc::getMessage('CALENDAR_PRESET_MEETING_STATUS_Q'),
				'default' => false,
				'fields' => [
					'IS_MEETING' => 'Y',
					'MEETING_STATUS' => 'Q'
				]
			];
		}
		
		$presets['filter_calendar_host'] = [
				'name' => Loc::getMessage('CALENDAR_PRESET_I_AM_HOST'),
				'default' => false,
				'fields' => [
					'IS_MEETING' => 'Y',
					'MEETING_STATUS' => 'H',
				]
			];
		
		$presets['filter_calendar_attendee'] = [
			'name' => Loc::getMessage('CALENDAR_PRESET_I_AM_ATTENDEE'),
			'default' => false,
			'fields' => [
				'IS_MEETING' => 'Y',
				'MEETING_STATUS' => 'Y'
			]
		];
		

		return $presets;
	}

	/**
	 * @param string $filterId
	 *
	 * @return array
	 */
	public static function resolveFilterFields(string $filterId): array
	{
		$filterOptions = new \Bitrix\Main\UI\Filter\Options($filterId);
		$fields = $filterOptions->getFilter();
		$result = [
			'search' => $filterOptions->getSearchString(),
			'presetId' => $fields['PRESET_ID'],
			'fields' => []
		];

		$fieldNames = self::getAvailableFields();
		foreach ($fields as $key => $value)
		{
			if ($key === 'DATE_from')
			{
				$result['fields']['DATE_FROM'] = $value;
			}
			else if ($key === 'DATE_to')
			{
				$result['fields']['DATE_TO'] = $value;
			}
			else if ($key === 'ATTENDEES' || $key === 'CREATED_BY' || $key === 'SECTION_ID')
			{
				$valueList = [];
				foreach ($value as $code)
				{
					$valueList[] = (int)$code;
				}
				$result['fields'][$key] = $valueList;
			}
			else if (in_array($key, $fieldNames))
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
	public static function getFilters(): array
	{
		static $filters = [];

		if (empty($filters))
		{
			$filters['CREATED_BY'] = [
				'id' => 'CREATED_BY',
				'name' => Loc::getMessage('CALENDAR_FILTER_CREATED_BY'),
				'type' => 'entity_selector',
				'partial' => true,
				'params' => [
					'multiple' => 'Y',
					'dialogOptions' => [
						'height' => 240,
						'context' => 'filter',
						'entities' => [
							[
								'id' => 'user',
								'options' => [
									'inviteEmployeeLink' => false
								],
							]
						]
					],
				],
			];

			$filters['ATTENDEES'] = [
				'id' => 'ATTENDEES',
				'name' => Loc::getMessage('CALENDAR_FILTER_ATTENDEES'),
				'type' => 'entity_selector',
				'partial' => true,
				'params' => [
					'multiple' => 'Y',
					'dialogOptions' => [
						'height' => 270,
						'context' => 'filter',
						'entities' => [
							[
								'id' => 'user',
								'options' => [
									'inviteEmployeeLink' => false
								],
							],
						]
					],
				],
			];

			// $filters['IS_MEETING'] = [
			// 	'id' => 'IS_MEETING',
			// 	'name' => Loc::getMessage('CALENDAR_FILTER_IS_MEETING'),
			// 	'type' => 'checkbox',
			// 	'default' => true,
			// ];

			$filters['MEETING_STATUS'] = [
				'id' => 'MEETING_STATUS',
				'name' => Loc::getMessage('CALENDAR_FILTER_MEETING_STATUS_ME'),
				'type' => 'list',
				'params' => [
					'multiple' => 'N'
				],
				'items' => [
					'H' => Loc::getMessage('CALENDAR_FILTER_MEETING_STATUS_H'),
					'Q' => Loc::getMessage('CALENDAR_FILTER_MEETING_STATUS_Q'),
					'Y' => Loc::getMessage('CALENDAR_FILTER_MEETING_STATUS_Y'),
					'N' => Loc::getMessage('CALENDAR_FILTER_MEETING_STATUS_N')
					//'I' => Loc::getMessage('CALENDAR_FILTER_MEETING_STATUS_I'),
				]
			];
			
			$filters['DATE'] = [
				'id' => 'DATE',
				'name' => Loc::getMessage('CALENDAR_FILTER_DATE'),
				'type' => 'date'
			];
		}

		return $filters;
	}
	
	private static function getSectionsForFilter(string $type, ?int $ownerId, ?int $userId): array
	{
		$result = [];
		
		$sectionList = \CCalendar::getSectionList([
			'CAL_TYPE' => $type,
			'OWNER_ID' => $ownerId,
			'checkPermissions' => true,
			'getPermissions' => true,
		]);
		$isPersonalCalendarContext = ($type === 'user' && $userId === $ownerId);
		
		$hiddenSections = UserSettings::getHiddenSections(
			$userId,
			[
				'type' => $type,
				'ownerId' => $ownerId,
				'isPersonalCalendarContext' => $isPersonalCalendarContext,
			]
		);
		
		foreach ($sectionList as $section)
		{
			if (in_array($section['ID'], $hiddenSections))
			{
				continue;
			}
			$result[] = $section['ID'];
		}
		
		return $result;
	}
	
	public static function getFilterData(array $params): array
	{
		$connection = Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();
		$userId = (int)$params['userId'];
		$ownerId = (int)$params['ownerId'];
		$type = $sqlHelper->forSql($params['type']);
		
		$fields = self::resolveFilterFields(
			self::getFilterId($type, $ownerId, $userId)
		);
		$fields['fields']['SECTION_ID'] = self::getSectionsForFilter(
			$params['type'],
			$params['ownerId'],
			$params['userId']
		);
		
		if (
			$type === 'company_calendar'
			|| $type === 'calendar_company'
			|| $type === 'company'
			|| $type === 'group'
		)
		{
			return self::getFilterCompanyData($type, $userId, $ownerId, $fields);
		}
		else
		{
			return self::getFilterUserData($type, $userId, $ownerId, $fields);
		}
	}
	
	private static function getFilterUserData(string $type, int $userId, int $ownerId, $fields): array
	{
		global $DB;
		$counters = false;
		$entries = [];
		$filter = [
			'OWNER_ID' => $ownerId,
			'CAL_TYPE' => $type
		];
		
		if (isset($fields['fields']['IS_MEETING']))
		{
			$filter['IS_MEETING'] = $fields['fields']['IS_MEETING'] === 'Y';
		}
		
		if (isset($fields['fields']['MEETING_STATUS']))
		{
			$filter['MEETING_STATUS'] = $fields['fields']['MEETING_STATUS'];
			if ($filter['MEETING_STATUS'] === 'H')
			{
				unset($filter['MEETING_STATUS']);
				$filter['MEETING_HOST'] = $userId;
			}
			else
			{
				$filter['IS_MEETING'] = true;
			}
			
			if ($fields['presetId'] == 'filter_calendar_meeting_status_q')
			{
				$filter['FROM_LIMIT'] = \CCalendar::Date(time(), false);
				$filter['TO_LIMIT'] = \CCalendar::Date(time() + \CCalendar::DAY_LENGTH * 90, false);
				\CCalendar::UpdateCounter([$ownerId]);
				$counters = CountersManager::getValues((int)$filter['OWNER_ID']);
			}
		}
		
		if (isset($fields['fields']['CREATED_BY']))
		{
			unset($filter['OWNER_ID'], $filter['CAL_TYPE']);
			$filter['MEETING_HOST'] = $fields['fields']['CREATED_BY'];
			// mantis: 93743
			$filter['CREATED_BY'] = $userId;
			// $filter['IS_MEETING'] = true;
		}
		
		if (isset($fields['fields']['SECTION_ID']) && !empty($fields['fields']['SECTION_ID']))
		{
			$filter['SECTION'] = $fields['fields']['SECTION_ID'];
		}
		else
		{
			return [
				'result' => true,
				'entries' => $entries,
				'counters' => $counters
			];
		}
		
		if (isset($fields['fields']['ATTENDEES']))
		{
			// unset($filter['CREATED_BY']);
			$queryStr = "SELECT EV.ID FROM b_calendar_event AS EV
				LEFT JOIN b_calendar_event AS SEC ON EV.PARENT_ID = SEC.PARENT_ID
				WHERE EV.DELETED = 'N'
				AND SEC.DELETED = 'N'
				AND EV.CAL_TYPE = '" . $type . "'
				AND EV.CREATED_BY = " . $userId . " ";
			
			$queryStr .= 'AND SEC.CREATED_BY IN (\''.implode('\',\'', $fields['fields']['ATTENDEES']).'\');';
			$events = $DB->Query($queryStr);
			while ($event = $events->Fetch())
			{
				$filter['ID'][] = (int)$event['ID'];
			}
			$filter['ID'] = array_unique($filter['ID']);
			
			// $filter['OWNER_ID'] = $fields['fields']['ATTENDEES'];
			$filter['IS_MEETING'] = true;
		}
		
		[$filter, $parseRecursion] = self::filterByDate($fields, $filter);
		
		if (isset($fields['search']) && $fields['search'])
		{
			$filter[(\CCalendarEvent::isFullTextIndexEnabled() ? '*' : '*%').'SEARCHABLE_CONTENT'] = \CCalendarEvent::prepareToken($fields['search']);
		}
		
		$entries = \CCalendarEvent::GetList(
			[
				'arFilter' => $filter,
				'fetchAttendees' => true,
				'parseRecursion' => $parseRecursion,
				'maxInstanceCount' => 50,
				'preciseLimits' => $parseRecursion,
				'userId' => $userId,
				'fetchMeetings' => true,
				'fetchSection' => true,
				'setDefaultLimit' => false
			]
		);
		
		return [
			'result' => true,
			'entries' => $entries,
			'counters' => $counters
		];
	}
	
	private static function getFilterCompanyData(string $type, int $userId, int $ownerId, $fields): array
	{
		global $DB;
		$filter = [
			// 'OWNER_ID' => $ownerId,
			'CAL_TYPE' => $type
		];
		$entries = [];
		$createdBy = '';
		
		$selectString = "SELECT EV.PARENT_ID FROM b_calendar_event AS EV
			LEFT JOIN b_calendar_event AS SEC ON EV.ID = SEC.PARENT_ID ";
		
		$whereString = "WHERE EV.CAL_TYPE = '" . $type . "'
			AND SEC.DELETED = 'N'
			AND EV.DELETED = 'N' ";
		
		if (isset($fields['fields']['IS_MEETING']) && $fields['fields']['IS_MEETING'])
		{
			$filter['IS_MEETING'] = $fields['fields']['IS_MEETING'] === 'Y';
		}
		
		if (isset($fields['fields']['MEETING_STATUS']) && $fields['fields']['MEETING_STATUS'])
		{
			$createdBy = 'AND SEC.CREATED_BY = ' . $userId . ' ' ;
			if (
				$fields['fields']['MEETING_STATUS'] === 'H'
				&& !isset($fields['fields']['CREATED_BY'])
			)
			{
				unset($filter['IS_MEETING']);
				$whereString .= "AND SEC.MEETING_HOST = '" . $userId . "' ";
			}
			else
			{
				$whereString .= "AND SEC.MEETING_STATUS = '" . $fields['fields']['MEETING_STATUS'] . "' ";
				$filter['IS_MEETING'] = true;
			}
		}
		
		if (isset($fields['fields']['CREATED_BY']) && is_array($fields['fields']['CREATED_BY']))
		{
			$whereString .= 'AND SEC.MEETING_HOST IN (\''.implode('\',\'', $fields['fields']['CREATED_BY']).'\') ';
		}
		
		if (isset($fields['fields']['SECTION_ID']) && is_array($fields['fields']['SECTION_ID']))
		{
			$whereString .= 'AND EV.SECTION_ID IN (\''.implode('\',\'', $fields['fields']['SECTION_ID']).'\') ';
		}
		
		if (isset($fields['fields']['ATTENDEES']) && is_array($fields['fields']['ATTENDEES']))
		{
			if (isset($fields['fields']['MEETING_STATUS']))
			{
				$selectString .= 'LEFT JOIN b_calendar_event AS TRI ON EV.ID = TRI.PARENT_ID ';
				$whereString .= 'AND TRI.CREATED_BY IN (\''.implode('\',\'', $fields['fields']['ATTENDEES']).'\') ';
			}
			else
			{
				$createdBy = 'AND SEC.CREATED_BY IN (\''.implode('\',\'', $fields['fields']['ATTENDEES']).'\') ';
			}
			$filter['IS_MEETING'] = true;
		}
		
		if ($createdBy)
		{
			$whereString .= $createdBy;
		}
		
		if (isset($fields['search']) && $fields['search'])
		{
			$filter[(\CCalendarEvent::isFullTextIndexEnabled() ? '*' : '*%').'SEARCHABLE_CONTENT'] = \CCalendarEvent::prepareToken($fields['search']);
		}
		
		[$filter, $parseRecursion] = self::filterByDate($fields, $filter);
		
		$whereString .= ';';
		$queryStr = $selectString . $whereString;
		
		$events = $DB->Query($queryStr);
		while ($event = $events->Fetch())
		{
			$filter['ID'][] = (int)$event['PARENT_ID'];
		}
		
		if (isset($filter['ID']))
		{
			$filter['ID'] = array_unique($filter['ID']);
			
			$entries = \CCalendarEvent::GetList(
				[
					'arFilter' => $filter,
					'fetchAttendees' => true,
					'parseRecursion' => $parseRecursion,
					'maxInstanceCount' => 50,
					'preciseLimits' => $parseRecursion,
					'userId' => $userId,
					'fetchMeetings' => true,
					'fetchSection' => true,
					'setDefaultLimit' => false
				]
			);
		}
		$entries = self::applyAccessRestrictions($entries);
		
		return [
			'result' => true,
			'entries' => $entries,
			'counters' => false
		];
	}
	
	/**
	 * @param $fields
	 * @param array $filter
	 * @return array
	 */
	private static function filterByDate($fields, array $filter): array
	{
		$parseRecursion = false;
		$fromTs = 0;
		$toTs = 0;
		if (isset($fields['fields']['DATE_FROM']))
		{
			$fromTs = \CCalendar::Timestamp($fields['fields']['DATE_FROM'], true, false);
			$filter['FROM_LIMIT'] = \CCalendar::Date($fromTs, false);
		}
		else
		{
			$filter['FROM_LIMIT'] = \CCalendar::Date(time() - 31 * 12 * 24 * 3600, false);
		}
		if (isset($fields['fields']['DATE_TO']))
		{
			$toTs = \CCalendar::Timestamp($fields['fields']['DATE_TO'], true, false);
			$filter['TO_LIMIT'] = \CCalendar::Date($toTs, false);
			if ($fromTs && $toTs < $fromTs)
			{
				$filter['TO_LIMIT'] = $filter['FROM_LIMIT'];
			}
		}
		
		if ($fromTs && $toTs && $fromTs <= $toTs)
		{
			$parseRecursion = true;
		}
		
		return [
			$filter,
			$parseRecursion
		];
	}

	/**
	 * @param array $events
	 * @return array
	 */
	private static function applyAccessRestrictions(array $events): array
	{
		$eventsLength = count($events);
		for ($i = 0; $i < $eventsLength; $i++)
		{
			if (
				isset($events[$i]['IS_ACCESSIBLE_TO_USER'])
				&& $events[$i]['IS_ACCESSIBLE_TO_USER'] === false
			)
			{
				unset($events[$i]);
			}
		}

		return array_values($events);
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