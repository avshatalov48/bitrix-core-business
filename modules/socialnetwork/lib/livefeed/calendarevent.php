<?php
namespace Bitrix\Socialnetwork\Livefeed;

final class CalendarEvent extends Provider
{
	const PROVIDER_ID = 'CALENDAR';
	const TYPE = 'entry';
	const CONTENT_TYPE_ID = 'CALENDAR_EVENT';

	public static function getId()
	{
		return static::PROVIDER_ID;
	}

	public function getEventId()
	{
		return array('calendar');
	}

	public function getType()
	{
		return static::TYPE;
	}


	public static function canRead($params)
	{
		return true;
	}

	protected function getPermissions(array $post)
	{
		$result = self::PERMISSION_READ;

		return $result;
	}
}