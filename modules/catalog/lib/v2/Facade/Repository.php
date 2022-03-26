<?php

namespace Bitrix\Catalog\v2\Facade;

use Bitrix\Catalog\v2\IoC\ServiceContainer;
use Bitrix\Catalog\v2\Product\BaseProduct;
use Bitrix\Catalog\v2\Sku\BaseSku;

/**
 * Class Repository
 *
 * @package Bitrix\Catalog\v2\Facade
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
final class Repository
{
	/** @var string|null */
	private $detailUrlTemplate;

	public function loadProduct(int $productId): ?BaseProduct
	{
		$iblockId = (int)\CIBlockElement::GetIBlockByID($productId);
		if (!$iblockId)
		{
			return null;
		}

		try
		{
			return $this->loadFromProductRepository($iblockId, $productId);
		}
		catch (\Bitrix\Main\SystemException $e)
		{}

		return null;
	}

	public function loadVariation(int $skuId): ?BaseSku
	{
		$iblockId = (int)\CIBlockElement::GetIBlockByID($skuId);
		if (!$iblockId)
		{
			return null;
		}

		$iblockInfo = ServiceContainer::getIblockInfo($iblockId);
		if (!$iblockInfo)
		{
			return null;
		}

		try
		{
			if ($iblockInfo->getProductIblockId() === $iblockId)
			{
				$product = $this->loadFromProductRepository($iblockId, $skuId);
				if ($product)
				{
					return $product->getSkuCollection()->getFirst();
				}
			}
			else
			{
				return $this->loadFromSkuRepository($iblockId, $skuId);
			}
		}
		catch (\Bitrix\Main\SystemException $e)
		{}

		return null;
	}

	public function setDetailUrlTemplate(?string $template): self
	{
		$this->detailUrlTemplate = $template;

		return $this;
	}

	public function getDetailUrlTemplate(): ?string
	{
		return $this->detailUrlTemplate;
	}

	private function loadFromProductRepository(int $iblockId, int $productId): ?BaseProduct
	{
		static $repository = null;

		if ($repository === null)
		{
			$repository = ServiceContainer::getProductRepository($iblockId);
			if (!$repository)
			{
				return null;
			}
		}

		if ($urlTemplate = $this->getDetailUrlTemplate())
		{
			$repository->setDetailUrlTemplate($urlTemplate);
		}

		return $repository->getEntityById($productId);
	}

	private function loadFromSkuRepository(int $iblockId, int $skuId): ?BaseSku
	{
		static $repository = null;

		if ($repository === null)
		{
			$repository = ServiceContainer::getSkuRepository($iblockId);
			if (!$repository)
			{
				return null;
			}
		}

		if ($urlTemplate = $this->getDetailUrlTemplate())
		{
			$repository->setDetailUrlTemplate($urlTemplate);
		}

		return $repository->getEntityById($skuId);
	}
}