<?
use Bitrix\Calendar\Rooms;

class CCalendarLocation
{
	const TYPE = 'location';

	public static function enabled()
	{
		return true;
	}
	
	public static function getList()
	{
		return Rooms\Manager::getRoomsList();
	}

	public static function getById($id)
	{
		return Rooms\Manager::getRoomById($id);
	}

	public static function getRoomAccessibility($roomId, $from, $to)
	{
		return Rooms\AccessibilityManager::getRoomAccessibility([$roomId], $from, $to);
	}

	public static function clearCache()
	{
		Rooms\Manager::createInstance()->clearCache();
	}

	public static function releaseRoom($params = array())
	{
		return Rooms\Manager::releaseRoom($params);
	}

	public static function reserveRoom($params = array())
	{
		return Rooms\Manager::reserveRoom($params);
	}

	public static function checkAccessibility($location = '', $params = [])
	{
		return Rooms\AccessibilityManager::checkAccessibility($location, $params);
	}
}
?>