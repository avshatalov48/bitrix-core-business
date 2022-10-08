<?php

namespace Bitrix\Seo\Catalog;

use Bitrix\Seo\BusinessSuite\IInternalService;
use Bitrix\Seo\Retargeting\AuthAdapter;
use Bitrix\Seo\Retargeting\IService;

final class Service implements IService, IInternalService
{
	/** @var AuthAdapter[] $authAdapterPool*/
	private static $authAdapterPool = [];

	public const GROUP = 'catalog';

	public const TYPE_FACEBOOK = 'facebook';


	private function __construct()
	{}

	private function __clone()
	{}

	/**
	 * @return static
	 */
	public static function getInstance(): Service
	{
		static $instance;
		return $instance = $instance ?? new Service;
	}

	/**
	 * @inheritDoc
	 */
	public static function getTypeByEngine(string $engineCode): ?string
	{
		foreach (self::getTypes() as $type)
		{
			if($engineCode === self::getEngineCode($type))
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

	/**
	 * @inheritDoc
	 */
	public static function getMethodPrefix(): string
	{
		return self::GROUP;
	}

	/**
	 * @inheritDoc
	 */
	public static function getEngineCode($type) : string
	{
		return self::GROUP . '.' . $type;
	}

	/**
	 * @inheritDoc
	 */
	public static function getTypes() : array
	{
		return [ self::TYPE_FACEBOOK ];
	}

	/**
	 * @inheritDoc
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getAuthAdapter($type) : AuthAdapter
	{
		if (!array_key_exists( $type, self::$authAdapterPool ))
		{
			return self::$authAdapterPool[$type] = AuthAdapter::create($type)->setService(self::getInstance());
		}
		return self::$authAdapterPool[$type];
	}

	/**
	 * @param string $type
	 *
	 * @return Catalog
	 */
	public function getCatalog(string $type): Catalog
	{
		return Catalog::create($type)->setService($this);
	}
}