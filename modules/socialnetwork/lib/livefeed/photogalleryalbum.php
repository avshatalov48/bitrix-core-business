<?php
namespace Bitrix\Socialnetwork\Livefeed;

final class PhotogalleryAlbum extends Provider
{
	const PROVIDER_ID = 'PHOTO_ALBUM';
	const TYPE = 'entry';
	const CONTENT_TYPE_ID = 'PHOTO_ALBUM';

	public static function getId()
	{
		return static::PROVIDER_ID;
	}

	public function getEventId()
	{
		return array('photo');
	}

	public function getType()
	{
		return static::TYPE;
	}
}