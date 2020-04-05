<?
namespace Bitrix\Calendar\Update;

/**
 * Class GoogleDublicatesCleaner
 * @package Bitrix\Calendar\Update
 */
final class GoogleDublicatesCleaner
{
	public static function doCheckAndClean()
	{
		global $DB;

		$query = $DB->Query("select GAPI_CALENDAR_ID, CAL_DAV_CON, COUNT(1) AS CNT from b_calendar_section where CAL_DAV_CON > 0 and GAPI_CALENDAR_ID <>
'' group by CAL_DAV_CON, GAPI_CALENDAR_ID ORDER BY CNT DESC");

		$gapiList = array();
		$gapi = '';

		while($row = $query->Fetch())
		{
			if($row['CNT'] > 1 && $row['GAPI_CALENDAR_ID'])
			{
				$gapi = $row['GAPI_CALENDAR_ID'];
				$gapiList[] = $row['GAPI_CALENDAR_ID'];
				break;
			}
		}

		if($gapi)
		{
			$query = $DB->Query("select ID, CAL_DAV_CON, GAPI_CALENDAR_ID from b_calendar_section where GAPI_CALENDAR_ID='".$DB->ForSql($gapi)."' order by ID desc limit 20");

			$sectionIndex = array();
			while($section = $query->Fetch())
			{
				$key = $section['GAPI_CALENDAR_ID'].'|'.$section['CAL_DAV_CON'];
				if(!isset($sectionIndex[$key]))
				{
					$sectionIndex[$key] = true;
				}
				else
				{
					if (\Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24') || COption::GetOptionString('calendar', 'sync_by_push', false))
					{
						\Bitrix\Calendar\PushTable::delete(array('ENTITY_TYPE' => 'SECTION', 'ENTITY_ID' => $section["ID"]));
					}

					$DB->Query("DELETE FROM b_calendar_event_sect WHERE SECT_ID=".intval($section["ID"]), false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
					$DB->Query("DELETE FROM b_calendar_section WHERE ID=".intval($section["ID"]), false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

					\CCalendarEvent::DeleteEmpty();
					\CCalendarSect::CleanAccessTable();
				}
			}
			return "Bitrix\\Calendar\\Update\\GoogleDublicatesCleaner::doCheckAndClean();";
		}

		if(empty($gapiList))
		{
			\CCalendarEvent::DeleteEmpty();
			\CCalendarSect::CleanAccessTable();
			return '';
		}
	}
}