<?php
namespace Bitrix\Rest;


use Bitrix\Main\Event;

class Application
{
	protected static $initialized = false;

	/**
	 * @var AuthProviderInterface
	 */
	protected static $authProvider = null;

	/**
	 * @return AuthProviderInterface
	 */
	public static function getAuthProvider()
	{
		static::initialize();

		if(static::$authProvider === null)
		{
			static::$authProvider = static::getDefaultAuthProvider();
		}

		return static::$authProvider;
	}

	/**
	 * @param AuthProviderInterface $authProvider
	 */
	public static function setAuthProvider(AuthProviderInterface $authProvider)
	{
		static::$authProvider = $authProvider;
	}

	/**
	 * @return OAuth\Provider
	 */
	protected static function getDefaultAuthProvider()
	{
		return OAuth\Provider::instance();
	}

	protected static function initialize()
	{
		if(!static::$initialized)
		{
			static::$initialized = true;

			$event = new Event('rest', 'onApplicationManagerInitialize');
			$event->send();
		}
	}
}