<?php

namespace Bitrix\Seo\BusinessSuite;


use Bitrix\Seo\Retargeting;
use Bitrix\Seo\BusinessSuite\AuthAdapter\Facebook\BusinessAuthAdapter;

class Service implements Retargeting\IService, IInternalService
{
	public const GROUP = 'business';
	public const FACEBOOK_TYPE = "facebook";
	public const INSTAGRAM_TYPE = 'instagram';

	/** @var BusinessAuthAdapter[] $authAdapterPool*/
	private static $authAdapterPool = [];

	private function __construct()
	{}

	private function __clone()
	{}

	/**
	 * @return string
	 */
	public static function getMethodPrefix() : string
	{
		return 'business';
	}

	/**
	 * @param string $type
	 *
	 * @return Config
	 */
	public function getConfig(string $type): Config
	{
		return Config::create($type)->setService($this);
	}

	/**
	 * @param string $type
	 *
	 * @return Extension
	 */
	public function getExtension(string $type) : Extension
	{
		return Extension::create($type)->setService($this);
	}

	/**
	 * @param string $type
	 *
	 * @return Conversion
	 */
	public function getConversion(string $type) : Conversion
	{
		return Conversion::create($type)->setService($this);
	}

	/**
	 * @param string $type
	 *
	 * @return Account
	 */
	public function getAccount(string $type) : Account
	{
		return Account::create($type)->setService($this);
	}

	/**
	 * @return static
	 */
	public static function getInstance(): self
	{
		static $instance;
		if (!$instance)
		{
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * @param string $type
	 *
	 * @return string
	 */
	public static function getEngineCode($type): string
	{
		return self::GROUP.'.'.$type;
	}

	/**
	 * @return array
	 */
	public static function getTypes(): array
	{
		return [self::FACEBOOK_TYPE, self::INSTAGRAM_TYPE];
	}

	/**
	 * @param string $type
	 *
	 * @return BusinessAuthAdapter
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getAuthAdapter($type) : BusinessAuthAdapter
	{
		if (!array_key_exists($type,static::$authAdapterPool))
		{
			static::$authAdapterPool[$type] = BusinessAuthAdapter::create($type)->setService(static::getInstance());
		}

		return static::$authAdapterPool[$type];
	}

	/**
	 * @inheritDoc
	 */
	public static function getTypeByEngine(string $engineCode): ?string
	{
		foreach (static::getTypes() as $type)
		{
			if($engineCode == static::getEngineCode($type))
			{

				return $type;
			}
		}

		return null;
	}

	/**
	 * @inheritDoc
	 */
	public static function canUseAsInternal(): bool
	{
		return true;
	}
}