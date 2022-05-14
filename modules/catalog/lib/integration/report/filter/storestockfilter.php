<?php

namespace Bitrix\Catalog\Integration\Report\Filter;

use Bitrix\Catalog\StoreTable;
use Bitrix\Catalog\v2\IoC\ServiceContainer;
use Bitrix\Catalog\v2\Product\BaseProduct;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Filter\DateType;

class StoreStockFilter extends BaseFilter
{
	public static function getFieldsList()
	{
		$productsEntities = [];
		if (Loader::includeModule('crm'))
		{
			$productsEntities[] = [
				'id' => 'product_variation',
				'options' => [
					'iblockId' => \Bitrix\Crm\Product\Catalog::getDefaultId(),
					'basePriceId' => \Bitrix\Crm\Product\Price::getBaseId(),
					'showPriceInCaption' => false,
				],
			];
		}

		$fields = [
			'STORES' => [
				'id' => 'STORES',
				'name' => Loc::getMessage('STOCK_FILTER_STORES_TITLE'),
				'type' => 'entity_selector',
				'default' => true,
				'partial' => true,
				'params' => [
					'multiple' => 'Y',
					'dialogOptions' => [
						'hideOnSelect' => false,
						'context' => 'report_store_stock_filter_stores',
						'entities' => [
							[
								'id' => 'store',
								'dynamicLoad' => true,
								'dynamicSearch' => true,
							]
						],
						'dropdownMode' => true,
					],
				],
			],
			'PRODUCTS' => [
				'id' => 'PRODUCTS',
				'name' => Loc::getMessage('STOCK_FILTER_PRODUCTS_TITLE'),
				'type' => 'entity_selector',
				'default' => true,
				'partial' => true,
				'params' => [
					'multiple' => true,
					'showDialogOnEmptyInput' => false,
					'dialogOptions' => [
						'hideOnSelect' => false,
						'context' => 'report_store_stock_filter_products',
						'entities' => $productsEntities,
						'dropdownMode' => true,
						'recentTabOptions' => [
							'stub' => true,
							'stubOptions' => [
								'title' => Loc::getMessage('STOCK_FILTER_PRODUCTS_STUB'),
							],
						],
						'events' => [
							'onBeforeSearch' => 'onBeforeDialogSearch',
						]
					],
				],
			],
			'REPORT_INTERVAL' => [
				'id' => 'REPORT_INTERVAL',
				'name' => Loc::getMessage('STOCK_FILTER_REPORT_INTERVAL_TITLE'),
				'default' => true,
				'type' => 'date',
				'required' => true,
				'valueRequired' => true,
				'exclude' => [
					DateType::NONE,
					DateType::CURRENT_DAY,
					DateType::CURRENT_WEEK,
					DateType::YESTERDAY,
					DateType::TOMORROW,
					DateType::PREV_DAYS,
					DateType::NEXT_DAYS,
					DateType::NEXT_WEEK,
					DateType::NEXT_MONTH,
					DateType::LAST_MONTH,
					DateType::LAST_WEEK,
					DateType::EXACT,
					DateType::RANGE,
					DateType::MONTH,
					DateType::QUARTER,
					DateType::YEAR
				],
			],
		];

		return $fields;
	}

	public static function getPresetsList()
	{
		$presets = [];

		$defaultStoreId = StoreTable::getDefaultStoreId();
		$defaultStoreTitle = StoreTable::getList([
			'select' => ['TITLE'],
			'filter' => ['=ID' => $defaultStoreId],
			'limit' => 1
		])->fetch()['TITLE'];
		if ($defaultStoreId)
		{
			$presets['filter_default_store'] = [
				'name' => Loc::getMessage('STOCK_FILTER_DEFAULT_STORE_PRESET_TITLE', ['#STORE_TITLE#' => $defaultStoreTitle]),
				'fields' => [
					'STORES' => [$defaultStoreId],
				],
				'default' => true,
			];
		}

		return $presets;
	}

	public static function prepareProductFilter(array $productIds)
	{
		$preparedFilter = [];
		$repositoryFacade = ServiceContainer::getRepositoryFacade();
		if (!$repositoryFacade)
		{
			return $preparedFilter;
		}

		\Bitrix\Main\Type\Collection::normalizeArrayValuesByInt($productIds);

		foreach ($productIds as $productId)
		{
			$product = $repositoryFacade->loadProduct($productId);
			if (!$product)
			{
				$preparedFilter[] = $productId;
			}
			else
			{
				array_push($preparedFilter, ...self::getProductVariations($product));
			}
		}

		return $preparedFilter;
	}

	private static function getProductVariations(BaseProduct $product): array
	{
		$variationIds = [];

		foreach ($product->getSkuCollection() as $variation)
		{
			$variationIds[] = $variation->getId();
		}

		return $variationIds;
	}
}
