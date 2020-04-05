<?php
namespace Bitrix\Socialnetwork\Livefeed;

final class LogEvent extends Provider
{
	const PROVIDER_ID = 'SONET_LOG';
	const TYPE = 'entry';
	const CONTENT_TYPE_ID = 'LOG_ENTRY';

	public static function getId()
	{
		return static::PROVIDER_ID;
	}

	public function getEventId()
	{
		return array('data');
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