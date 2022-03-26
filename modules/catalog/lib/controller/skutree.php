<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Catalog\v2\IoC\ServiceContainer;
use Bitrix\Catalog\v2\Sku\BaseSku;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\JsonController;

class SkuTree extends JsonController
{
	protected function getDefaultPreFilters()
	{
		return array_merge(
			parent::getDefaultPreFilters(),
			[
				new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
				new ActionFilter\Scope(ActionFilter\Scope::AJAX),
			]
		);
	}

	public function getIblockPropertiesAction(int $iblockId): array
	{
		if ($iblockId <= 0)
		{
			return [];
		}

		$skuTree = ServiceContainer::make('sku.tree', ['iblockId' => $iblockId]);
		if ($skuTree)
		{
			return $skuTree->getTreeProperties();
		}

		return [];
	}

	public function getSkuAction(int $skuId): array
	{
		$iterator = \CIBlockElement::GetList(
			[],
			[
				'ID' => $skuId,
				'ACTIVE' => 'Y',
				'ACTIVE_DATE' => 'Y',
				'CHECK_PERMISSIONS' => 'Y',
				'MIN_PERMISSION' => 'R',
			],
			false,
			false,
			['ID', 'IBLOCK_ID']
		);
		$element = $iterator->Fetch();
		if (!$element)
		{
			return [];
		}
		unset($iterator);

		$skuRepository = ServiceContainer::getSkuRepository($element['IBLOCK_ID']);
		if (!$skuRepository)
		{
			return [];
		}

		/** @var BaseSku $sku */
		$sku = $skuRepository->getEntityById($skuId);

		if (!$sku || $sku->isSimple())
		{
			return [];
		}

		$parentProduct = $sku->getParent();
		if (!$parentProduct)
		{
			return [];
		}

		/** @var \Bitrix\Catalog\Component\SkuTree $skuTree */
		$skuTree = ServiceContainer::make('sku.tree', [
			'iblockId' => $parentProduct->getIblockId(),
		]);

		if (!$skuTree)
		{
			return [];
		}

		$productId = $parentProduct->getId();

		$offers = $skuTree->loadWithSelectedOffers([
			$productId => $skuId,
		]);

		if ($offers[$productId][$skuId] && is_array($offers[$productId][$skuId]['OFFERS']))
		{
			foreach ($offers[$productId][$skuId]['OFFERS'] as $offer)
			{
				if ((int)$offer['ID'] === $skuId)
				{
					return $offer;
				}
			}
		}

		return [];
	}
}
