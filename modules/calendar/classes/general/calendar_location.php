<?
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

class CCalendarLocation
{
	private static
		$type = 'location';

	public static function enabled()
	{
		return true;
	}

	public static function getList($params = array())
	{
		$arFilter =  array(
			'CAL_TYPE' => self::$type
		);

		$sectionList = CCalendarSect::GetList(array('arFilter' => $arFilter));

		$res = array();
		foreach($sectionList as $sect)
		{
			$res[] = array(
				'ID' => $sect['ID'],
				'NAME' => $sect['NAME'],
				'PERM' => $sect['PERM'],
				'ACCESS' => $sect['ACCESS']
			);
		}
		return $res;
	}

	public static function getById($id)
	{
		$arFilter =  array(
			'CAL_TYPE' => self::$type,
			'ID' => intval($id)
		);
		$sectionList = CCalendarSect::GetList(array('arFilter' => $arFilter));

		$res = false;
		foreach($sectionList as $sect)
		{
			$res = array(
				'ID' => $sect['ID'],
				'NAME' => $sect['NAME'],
				'PERM' => $sect['PERM'],
				'ACCESS' => $sect['ACCESS']
			);
			break;
		}
		return $res;
	}

	public static function update($params = array())
	{
		CCalendarSect::Edit(array(
			'arFields' => array(
				'CAL_TYPE' => self::$type,
				'ID' => $params['id'],
				'NAME' => $params['name'],
				'ACCESS' => array()
			)
		));
	}

	public static function getRoomAccessibility($roomId, $from, $to, $params = array())
	{
		if (!isset($params['checkPermissions']))
			$params['checkPermissions'] = true;

		$accessibility = array();

		$roomEntries = CCalendarEvent::GetList(
			array(
				'arFilter' => array(
					"FROM_LIMIT" => $from,
					"TO_LIMIT" => $to,
					"CAL_TYPE" => self::$type,
					"ACTIVE_SECTION" => "Y",
					"SECTION" => $roomId
				),
				'parseRecursion' => true,
				'fetchSection' => true,
				'setDefaultLimit' => false
			)
		);

		foreach($roomEntries as $roomEntry)
		{
//			if ($curEventId && ($roomEntry["ID"] == $curEventId || $roomEntry["PARENT_ID"] == $curEventId))
//				continue;
//			if ($roomEntry["IS_MEETING"] && ($roomEntry["MEETING_STATUS"] == "N" || $roomEntry["MEETING_STATUS"] == "Q"))
//				continue;
			$accessibility[] = array(
				"ID" => $roomEntry["ID"],
				"NAME" => $roomEntry["NAME"],
				"DATE_FROM" => $roomEntry["DATE_FROM"],
				"DATE_TO" => $roomEntry["DATE_TO"],
				"~USER_OFFSET_FROM" => $roomEntry["~USER_OFFSET_FROM"],
				"~USER_OFFSET_TO" => $roomEntry["~USER_OFFSET_TO"],
				"DT_SKIP_TIME" => $roomEntry["DT_SKIP_TIME"],
				"TZ_FROM" => $roomEntry["TZ_FROM"],
				"TZ_TO" => $roomEntry["TZ_TO"],
				"ACCESSIBILITY" => $roomEntry["ACCESSIBILITY"],
				"IMPORTANCE" => $roomEntry["IMPORTANCE"],
				"EVENT_TYPE" => $roomEntry["EVENT_TYPE"]
			);
		}

		return $accessibility;
	}


	public static function delete($id)
	{
		CCalendarSect::Delete($id);
	}

	public static function clearCache($params = array())
	{
		CCalendar::ClearCache(array('section_list'));
	}

	public static function releaseRoom($params = array())
	{
		$res = CCalendar::DeleteEvent(intVal($params['room_event_id']),
			false
			//array('recursionMode' => self::$request['rec_mode'])
		);
		return true;
	}

	public static function reserveRoom($params = array())
	{
		$roomEventId = CCalendarEvent::Edit(array(
			'arFields' => array(
				'ID' => $params['room_event_id'],
				'CAL_TYPE' => self::$type,
				'SECTIONS' => $params['room_id'],
				'DATE_FROM' => $params['dateFrom'],
				'DATE_TO' => $params['dateTo'],
				'TZ_FROM' => $params['parentParams']['arFields']['TZ_FROM'],
				'TZ_TO' => $params['parentParams']['arFields']['TZ_TO'],
				'SKIP_TIME' => $params['parentParams']['arFields']['SKIP_TIME'],
				'NAME' => Loc::getMessage('EC_EDEV_EVENT').': '.$params['parentParams']['arFields']['NAME'],
				'RRULE' => $params['parentParams']['arFields']['RRULE']
			)
		));

		return $roomEventId;
	}
}
?>