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

	public static function clearCache()
	{
		Rooms\Manager::createInstance()->clearCache();
	}
}
?>
