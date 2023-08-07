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
	private ?string $detailUrlTemplate = null;

	private bool $allowedDetailUrl = false;

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

	public function setAutoloadDetailUrl(bool $state): self
	{
		$this->allowedDetailUrl = $state;

		return $this;
	}

	public function checkAutoloadDetailUrl(): bool
	{
		return $this->allowedDetailUrl;
	}

	public function setDetailUrlTemplate(?string $template): self
	{
		$this->detailUrlTemplate = $template;

		$this->setAutoloadDetailUrl($template !== null);

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

		$repository->setAutoloadDetailUrl($this->checkAutoloadDetailUrl());
		$urlTemplate = $this->getDetailUrlTemplate();
		if ($urlTemplate)
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

		$repository->setAutoloadDetailUrl($this->checkAutoloadDetailUrl());
		$urlTemplate = $this->getDetailUrlTemplate();
		if ($urlTemplate)
		{
			$repository->setDetailUrlTemplate($urlTemplate);
		}

		return $repository->getEntityById($skuId);
	}
}
