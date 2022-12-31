<?php

namespace Bitrix\Catalog\Integration\Report\Filter;

use Bitrix\Catalog\StoreTable;
use Bitrix\Catalog\v2\IoC\ServiceContainer;
use Bitrix\Catalog\v2\Product\BaseProduct;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Report\VisualConstructor\Helper\Filter;

abstract class BaseFilter extends Filter
{
	abstract protected static function getStoreFilterContext(): string;

	abstract protected static function getProductFilterContext(): string;

	public function getFilterParameters()
	{
		return [
			'FILTER_ID' => $this->filterId,
			'COMMON_PRESETS_ID' => $this->filterId . '_presets',
			'FILTER' => static::getFieldsList(),
			'DISABLE_SEARCH' => true,
			'FILTER_PRESETS' => static::getPresetsList(),
			'ENABLE_LABEL' => true,
			'ENABLE_LIVE_SEARCH' => false,
			'RESET_TO_DEFAULT_MODE' => false,
			'VALUE_REQUIRED_MODE' => false,
		];
	}

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

		return [
			'STORES' => [
				'id' => 'STORES',
				'name' => Loc::getMessage('BASE_FILTER_STORES_TITLE'),
				'type' => 'entity_selector',
				'default' => true,
				'partial' => true,
				'params' => [
					'multiple' => 'Y',
					'dialogOptions' => [
						'hideOnSelect' => false,
						'context' => static::getStoreFilterContext(),
						'entities' => [
							[
								'id' => 'store',
								'dynamicLoad' => true,
								'dynamicSearch' => true,
							]
						],
						'dropdownMode' => true
					],
				],
			],
			'PRODUCTS' => [
				'id' => 'PRODUCTS',
				'name' => Loc::getMessage('BASE_FILTER_PRODUCTS_TITLE'),
				'type' => 'entity_selector',
				'default' => true,
				'partial' => true,
				'params' => [
					'multiple' => true,
					'showDialogOnEmptyInput' => false,
					'dialogOptions' => [
						'hideOnSelect' => false,
						'context' => static::getProductFilterContext(),
						'entities' => $productsEntities,
						'dropdownMode' => true,
						'recentTabOptions' => [
							'stub' => true,
							'stubOptions' => [
								'title' => Loc::getMessage('BASE_FILTER_DEFAULT_STORE_PRESET_TITLE'),
							],
						],
						'events' => [
							'onBeforeSearch' => 'onBeforeDialogSearch',
						]
					],
				],
			],
		];
	}

	public static function getPresetsList()
	{
		return [];
	}

	public static function prepareProductFilter(array $productIds): array
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
