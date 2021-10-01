<?php

namespace Bitrix\Catalog\v2\Iblock;

use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\Type\Dictionary;

/**
 * Class IblockInfo
 *
 * @package Bitrix\Catalog\v2\Iblock
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class IblockInfo
{
	private $iblock;

	public function __construct(int $iblockId)
	{
		$iblockInfo = \CCatalogSku::GetInfoByIBlock($iblockId);

		if (!$iblockInfo || !is_array($iblockInfo))
		{
			throw new ObjectNotFoundException("Can not find catalog iblock {{$iblockId}}.");
		}

		$this->iblock = new Dictionary($iblockInfo);
	}

	public function toArray(): array
	{
		return $this->iblock->toArray();
	}

	public function getCatalogType()
	{
		return $this->iblock->get('CATALOG_TYPE');
	}

	// ToDo all these wrappers
	public function getProductIblockId(): int
	{
		return
			$this->canHaveSku()
				? (int)$this->iblock->get('PRODUCT_IBLOCK_ID')
				: (int)$this->iblock->get('IBLOCK_ID');
	}

	public function hasSubscription(): bool
	{
		return $this->iblock->get('SUBSCRIPTION') === 'Y';
	}

	public function canHaveSku(): bool
	{
		return (
			$this->getCatalogType() === \CCatalogSku::TYPE_OFFERS
			|| $this->getCatalogType() === \CCatalogSku::TYPE_FULL
			|| $this->getCatalogType() === \CCatalogSku::TYPE_PRODUCT
		);
	}

	public function getSkuIblockId(): ?int
	{
		return $this->canHaveSku() ? (int)$this->iblock->get('IBLOCK_ID') : null;
	}

	public function getSkuPropertyId(): ?int
	{
		return $this->canHaveSku() ? (int)$this->iblock->get('SKU_PROPERTY_ID') : null;
	}

	public function getVatId(): ?int
	{
		return (int)$this->iblock->get('VAT_ID') ?: null;
	}
}