<?
namespace Bitrix\Calendar\Update;

use \Bitrix\Main\Update\Stepper;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Calendar\Integration;
use \Bitrix\Socialnetwork\Item\LogIndex;
use \Bitrix\Socialnetwork\LogTable;
use \Bitrix\Socialnetwork\LogIndexTable;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

final class LivefeedIndexCalendar extends Stepper
{
	protected static $moduleId = "calendar";

	public function execute(array &$result)
	{
		if (!(
			Loader::includeModule("calendar")
			&& Loader::includeModule("socialnetwork")
			&& Option::get('calendar', 'needLivefeedIndex', 'Y') == 'Y'
		))
		{
			return false;
		}

		$return = false;

		$result["title"] = Loc::getMessage("FUPD_LF_CALENDAR_EVENT_INDEX_TITLE");
		$result["progress"] = 1;
		$result["steps"] = "";
		$result["count"] = "";

		$params = Option::get("calendar", "livefeedindexcalendar", "");

		$params = ($params !== "" ? @unserialize($params) : array());
		$params = (is_array($params) ? $params : array());
		if (empty($params))
		{
			$params = array(
				"lastId" => 0,
				"number" => 0,
				"count" => LogTable::getCount(
					array(
						'@EVENT_ID' => Integration\Socialnetwork\Log::getEventIdList(),
						'!SOURCE_ID' => false
					)
				)
			);
			if ($params["count"] > 0)
			{
				Option::set("calendar", "livefeedindexcalendar", serialize($params));
			}
			else
			{
				return $return;
			}
		}

		if (
			isset($params["finished"])
			&& $params["finished"] === true
		)
		{
			Option::delete("calendar", array("name" => "livefeedindexcalendar"));
			Option::set('calendar', 'needLivefeedIndex', 'N');
			return false;
		}
		else
		{
			$return = true;
		}

		$result["progress"] = intval($params["number"] * 100/ $params["count"]);
		$result["steps"] = $params["number"];
		$result["count"] = $params["count"];

		if ($params["count"] > 0)
		{
			$tmpUser = false;
			if (!isset($GLOBALS["USER"]) || !is_object($GLOBALS["USER"]))
			{
				$tmpUser = True;
				$GLOBALS["USER"] = new \CUser;
			}

			\Bitrix\Calendar\Update\LivefeedIndexCalendar::run();

			if ($tmpUser)
			{
				unset($GLOBALS["USER"]);
			}
		}

		return $return;
	}

	public static function run()
	{
		$params = Option::get("calendar", "livefeedindexcalendar", false);
		$params = ($params !== "" ? @unserialize($params) : array());

		$found = false;

		if (
			is_array($params)
			&& intval($params["lastId"]) >= 0
		)
		{
			$res = LogTable::getList(array(
				'order' => array('ID' => 'ASC'),
				'filter' => array(
					'>ID' => $params["lastId"],
					'@EVENT_ID' => Integration\Socialnetwork\Log::getEventIdList(),
					'!SOURCE_ID' => false
				),
				'select' => array('ID', 'EVENT_ID', 'SOURCE_ID'),
				'offset' => 0,
				'limit' => 100
			));

			while ($record = $res->fetch())
			{
				LogIndex::setIndex(array(
					'itemType' => LogIndexTable::ITEM_TYPE_LOG,
					'itemId' => $record['ID'],
					'fields' => $record
				));

				$params["lastId"] = $record['ID'];
				$params["number"]++;
				$found = true;
			}
		}

		if (!$found)
		{
			$params["finished"] = true;
		}

		Option::set("calendar", "livefeedindexcalendar", serialize($params));
	}
}
?>