<?php

namespace Bitrix\Catalog\Component;

use Bitrix\Iblock\Url\AdminPage\BuilderManager;
use Bitrix\Main;
use Bitrix\Catalog\v2\IoC\ServiceContainer;

abstract class ProductList extends \CBitrixComponent
{
	protected $measures = [];

	protected function loadCatalog(array $skuIds): array
	{
		Main\Type\Collection::normalizeArrayValuesByInt($skuIds, true);

		$repositoryFacade = ServiceContainer::getRepositoryFacade();
		if (!$repositoryFacade)
		{
			return [];
		}

		$productInfo = [];
		$productSkuIblockMap = [];
		foreach ($skuIds as $skuId)
		{
			$sku = $repositoryFacade->loadVariation($skuId);
			if (!$sku)
			{
				continue;
			}

			/** @var \Bitrix\Catalog\v2\Product\BaseProduct $product */
			$product = $sku->getParent();

			$fields = $sku->getFields();
			$fields['PRODUCT_ID'] = $product->getId();
			$fields['SKU_ID'] = $skuId;
			$fields['OFFERS_IBLOCK_ID'] = 0;
			$fields['SKU_TREE'] = [];
			$fields['DETAIL_URL'] = $this->getElementDetailUrl($product->getIblockId(), $product->getId());
			$fields['TYPE'] = (int)$fields['TYPE'];

			$measure = $this->measures[$sku->getField('MEASURE')] ?? null;
			if (!$measure)
			{
				$measure = $this->getDefaultMeasure();
			}

			$fields['MEASURE_CODE'] = $measure['CODE'];
			$fields['MEASURE_NAME'] = $measure['SYMBOL'];

			if (!$product->isSimple())
			{
				$fields['OFFERS_IBLOCK_ID'] = $fields['IBLOCK_ID'];
				$fields['IBLOCK_ID'] = $product->getIblockId();
				$productSkuIblockMap[$product->getIblockId()] = $productSkuIblockMap[$product->getIblockId()] ?? [];
				$productSkuIblockMap[$product->getIblockId()][$product->getId()][] = $sku->getId();
			}

			$productInfo[$skuId] = [
				'SKU' => $sku,
				'FIELDS' => $fields,
			];
		}

		if ($productSkuIblockMap)
		{
			foreach ($productSkuIblockMap as $iblockId => $productMap)
			{
				$skuTree = ServiceContainer::make('sku.tree', ['iblockId' => $iblockId]);
				if ($skuTree)
				{
					$skuTreeItems = $skuTree->loadJsonOffers($productMap);
					foreach ($skuTreeItems as $offers)
					{
						foreach ($offers as $skuId => $skuTreeItem)
						{
							if (isset($productInfo[$skuId]['FIELDS']))
							{
								$productInfo[$skuId]['FIELDS']['SKU_TREE'] = $skuTreeItem;
							}
						}
					}
				}
			}
		}

		return $productInfo;
	}

	protected function getElementDetailUrl(int $iblockId, int $skuId = 0): string
	{
		$urlBuilder = BuilderManager::getInstance()->getBuilder($this->arParams['BUILDER_CONTEXT']);
		if (!$urlBuilder)
		{
			return '';
		}

		$urlBuilder->setIblockId($iblockId);
		return $urlBuilder->getElementDetailUrl($skuId);
	}

	protected function loadMeasures(): void
	{
		$measureResult = \CCatalogMeasure::getList(
			['CODE' => 'ASC'],
			[],
			false,
			[],
			['CODE', 'SYMBOL_RUS', 'SYMBOL_INTL', 'IS_DEFAULT', 'ID']
		);

		$this->measures = [];
		while ($measureFields = $measureResult->Fetch())
		{
			$measureItem = [
				'ID' => $measureFields['ID'],
				'CODE' => $measureFields['CODE'],
				'IS_DEFAULT' => $measureFields['IS_DEFAULT'],
				'SYMBOL' => $measureFields['SYMBOL_RUS'] ?? $measureFields['SYMBOL_INTL'],
			];

			$this->measures[$measureFields['ID']] = $measureItem;
		}
	}

	/**
	 * @return string
	 */
	protected function getDefaultMeasure(): array
	{
		return \CCatalogMeasure::getDefaultMeasure(true);
	}
}
