<?php
namespace Bitrix\Socialnetwork\Livefeed;

final class ListsItem extends Provider
{
	const PROVIDER_ID = 'LISTS_NEW_ELEMENT';
	const TYPE = 'entry';
	const CONTENT_TYPE_ID = 'LISTS_NEW_ELEMENT';

	public static function getId()
	{
		return static::PROVIDER_ID;
	}

	public function getEventId()
	{
		return array('lists_new_element');
	}

	public function getType()
	{
		return static::TYPE;
	}
}