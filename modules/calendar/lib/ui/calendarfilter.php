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
			'FROM_LIMIT',
			'TO_LIMIT',
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
			$presets['filter_calendar_meeting_status_q'] = [
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
		$connection = Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();

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
			else if ($key === 'MEETING_STATUS')
			{
				$result['fields']['MEETING_STATUS'] = $sqlHelper->forSql($value);
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
			$result[] = (int)$section['ID'];
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
		else if (!$filter['FROM_LIMIT'])
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
}