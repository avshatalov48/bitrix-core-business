<?php

namespace Bitrix\Catalog\Filter\DataProvider\Settings;

use Bitrix\Iblock\Filter\DataProvider\Settings\ElementSettings;
use Bitrix\Main\Loader;

Loader::requireModule('iblock');

class ProductSettings extends ElementSettings
{
	private ?int $variationIblockId = null;
	private ?int $linkPropertyId = null;

	public function __construct(array $params)
	{
		parent::__construct($params);

		$this->variationIblockId = $params['VARIATION_IBLOCK_ID'] ?? null;
		$this->linkPropertyId = $params['LINK_PROPERTY_ID'] ?? null;
		if ($this->variationIblockId === null || $this->linkPropertyId === null)
		{
			$this->variationIblockId = null;
			$this->linkPropertyId = null;
		}
	}

	public function getVariationIblockId(): ?int
	{
		return $this->variationIblockId;
	}

	public function getLinkPropertyId(): ?int
	{
		return $this->linkPropertyId;
	}

	public function getCatalogIblockId(): int
	{
		return $this->getIblockId();
	}

	public function isWithVariations(): bool
	{
		return $this->getVariationIblockId() !== null;
	}
}
