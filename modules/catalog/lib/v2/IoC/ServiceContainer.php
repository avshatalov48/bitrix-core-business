<?php

namespace Bitrix\Catalog\v2\IoC;

use Bitrix\Catalog\v2\Iblock\IblockInfo;
use Bitrix\Catalog\v2\Product\ProductFactory;
use Bitrix\Catalog\v2\Product\ProductRepositoryContract;
use Bitrix\Catalog\v2\Sku\SkuFactory;
use Bitrix\Catalog\v2\Sku\SkuRepositoryContract;
use Bitrix\Main\ObjectNotFoundException;

/**
 * Class ServiceContainer
 *
 * @package Bitrix\Catalog\v2\IoC
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
final class ServiceContainer
{
	/** @var \Bitrix\Catalog\v2\IoC\ContainerContract */
	private static $container;

	private function __construct()
	{
	}

	public static function getContainer(): ContainerContract
	{
		if (static::$container === null)
		{
			// ToDo make events for customization
			static::$container = ContainerBuilder::buildFromConfig();
		}

		return static::$container;
	}

	public static function get($id, array $args = [])
	{
		return static::getContainer()->get($id, $args);
	}

	/**
	 * @param int $iblockId
	 * @return \Bitrix\Catalog\v2\Iblock\IblockInfo|null
	 */
	public static function getIblockInfo(int $iblockId): ?IblockInfo
	{
		try
		{
			$iblockInfo = static::get(Dependency::IBLOCK_INFO, compact('iblockId'));
		}
		catch (ObjectNotFoundException $exception)
		{
			$iblockInfo = null;
		}

		return $iblockInfo;
	}

	/**
	 * @param int $iblockId
	 * @return \Bitrix\Catalog\v2\Product\ProductFactory|null
	 */
	public static function getProductFactory(int $iblockId): ?ProductFactory
	{
		$iblockInfo = static::getIblockInfo($iblockId);

		if ($iblockInfo)
		{
			$iblockId = $iblockInfo->getProductIblockId();

			return static::get(Dependency::PRODUCT_FACTORY, compact('iblockId'));
		}

		return null;
	}

	/**
	 * @param int $iblockId
	 * @return \Bitrix\Catalog\v2\Product\ProductRepositoryContract|null
	 */
	public static function getProductRepository(int $iblockId): ?ProductRepositoryContract
	{
		$iblockInfo = static::getIblockInfo($iblockId);

		if ($iblockInfo)
		{
			$iblockId = $iblockInfo->getProductIblockId();

			return static::get(Dependency::PRODUCT_REPOSITORY, compact('iblockId'));
		}

		return null;
	}

	/**
	 * @param int $iblockId
	 * @return \Bitrix\Catalog\v2\Sku\SkuFactory|null
	 */
	public static function getSkuFactory(int $iblockId): ?SkuFactory
	{
		$iblockInfo = static::getIblockInfo($iblockId);

		if ($iblockInfo)
		{
			$iblockId = $iblockInfo->getProductIblockId();

			return static::get(Dependency::SKU_FACTORY, compact('iblockId'));
		}

		return null;
	}

	/**
	 * @param int $iblockId
	 * @return \Bitrix\Catalog\v2\Sku\SkuRepositoryContract|null
	 */
	public static function getSkuRepository(int $iblockId): ?SkuRepositoryContract
	{
		$iblockInfo = static::getIblockInfo($iblockId);

		if ($iblockInfo)
		{
			$iblockId = $iblockInfo->getProductIblockId();

			return static::get(Dependency::SKU_REPOSITORY, compact('iblockId'));
		}

		return null;
	}
}