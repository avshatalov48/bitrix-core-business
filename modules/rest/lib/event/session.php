<?php
namespace Bitrix\Rest\Event;

/**
 * Class Session
 *
 * Session restriction for REST events
 *
 * @package Bitrix\Rest
 **/
class Session
{
	const PARAM_SESSION = 'EVENT_SESSION';

	private static $TTL = null;

	private static $ttlDecreased = false;
	private static $set = false;

	public static function get()
	{
		if(!self::$set)
		{
			self::$TTL = \CRestUtil::HANDLER_SESSION_TTL;
			self::$ttlDecreased = true;
		}
		else
		{
			if(!self::$ttlDecreased)
			{
				self::$TTL--;
				self::$ttlDecreased = true;
			}
		}

		return self::$TTL <= 0 ? false : self::$TTL;
	}

	public static function set($session)
	{
		self::$TTL = is_array($session) ? $session['TTL'] : $session;

		self::$ttlDecreased = false;
		self::$set = true;
	}
}