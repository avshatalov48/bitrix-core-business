<?php

namespace Bitrix\Catalog\Grid\Settings;

use Bitrix\Iblock\Grid\Entity\ElementSettings;
use Bitrix\Main\Loader;
use CCatalogSku;

Loader::requireModule('iblock');

class ProductSettings extends ElementSettings
{
	private ?int $offersIblockId = null;
	private ?int $skuPropertyId = null;

	protected function init(): void
	{
		parent::init();

		$catalog = CCatalogSku::GetInfoByIBlock($this->getIblockId());
		if (!empty($catalog))
		{
			$type = $catalog['CATALOG_TYPE'];

			if (
				$type === CCatalogSku::TYPE_FULL
				|| $type === CCatalogSku::TYPE_PRODUCT
			)
			{
				$this->offersIblockId = $catalog['IBLOCK_ID'];
				$this->skuPropertyId = $catalog['SKU_PROPERTY_ID'];
			}
		}
	}

	public function isAllowedIblockSections(): bool
	{
		return false;
	}

	public function getOffersIblockId(): ?int
	{
		return $this->offersIblockId;
	}

	public function getSkuPropertyId(): ?int
	{
		return $this->skuPropertyId;
	}
}
