<?php
namespace Bitrix\Socialnetwork\Livefeed;

final class LogComment extends Provider
{
	const PROVIDER_ID = 'SONET_COMMENT';
	const TYPE = 'comment';
	const CONTENT_TYPE_ID = 'LOG_COMMENT';

	public static function getId()
	{
		return static::PROVIDER_ID;
	}

	public function getEventId()
	{
		return array('data_comment');
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