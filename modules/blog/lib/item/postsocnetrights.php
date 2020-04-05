<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage blog
 * @copyright 2001-2018 Bitrix
 */
namespace Bitrix\Blog\Item;

class PostSocnetRights
{
	private static $cache = array();

	public static function set($groupId, $value)
	{
		self::$cache[$groupId] = $value;
	}

	public static function get($groupId)
	{
		return self::$cache[$groupId];
	}
}
