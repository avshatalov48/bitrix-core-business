<?php

namespace Bitrix\Catalog\v2\Integration\UI\ViewedProducts;

use Bitrix\Catalog;
use Bitrix\Main\Loader;

/**
 * Class Repository
 *
 * @package Bitrix\Catalog\v2\Integration\UI\ViewedProducts
 *
 * * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
final class Repository
{
	/** @var Repository */
	private static $instance;

	public const DEFAULT_GET_LIST_LIMIT = 10;

	/**
	 * @return Repository
	 */
	public static function getInstance(): Repository
	{
		if (is_null(static::$instance))
		{
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * @param array $options
	 * @return Catalog\v2\Sku\BaseSku[]
	 */
	public function getList(array $options = []): array
	{
		$result = [];

		if (!Loader::includeModule('sale'))
		{
			return $result;
		}

		$limit = $options['limit'] ?? self::DEFAULT_GET_LIST_LIMIT;

		$viewedProductsList = Catalog\CatalogViewedProductTable::getList(
			[
				'filter' => [
					'=FUSER_ID' => (int)\CSaleBasket::GetBasketUserID(
						!Catalog\Product\Basket::isNotCrawler()
					),
					'=SITE_ID' => SITE_ID,
				],
				'select' => [
					'ELEMENT_ID',
					'PRODUCT_ID',
				],
				'order' => [
					'DATE_VISIT' => 'DESC',
				],
				'limit' => $limit,
			]
		);

		while ($viewedProduct = $viewedProductsList->fetch())
		{
			$sku =
				Catalog\v2\IoC\ServiceContainer::getRepositoryFacade()
					->loadVariation((int)$viewedProduct['PRODUCT_ID'])
			;

			if (!$sku)
			{
				continue;
			}

			$result[] = $sku;
		}

		return $result;
	}
}
