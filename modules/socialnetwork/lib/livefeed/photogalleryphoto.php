<?php
namespace Bitrix\Socialnetwork\Livefeed;

final class PhotogalleryPhoto extends Provider
{
	const PROVIDER_ID = 'PHOTO_PHOTO';
	const TYPE = 'entry';
	const CONTENT_TYPE_ID = 'PHOTO_PHOTO';

	public static function getId()
	{
		return static::PROVIDER_ID;
	}

	public function getEventId()
	{
		return array('photo_photo');
	}

	public function getType()
	{
		return static::TYPE;
	}
}