<?php

namespace Bitrix\Catalog\Grid\Row;

use Bitrix\Catalog\Grid\Row\Assembler\Factory\PriceFieldAssemblerFactory;
use Bitrix\Catalog\Grid\Row\Assembler\MeasureFieldAssembler;
use Bitrix\Catalog\Grid\Row\Assembler\MorePhotoAssembler;
use Bitrix\Catalog\Grid\Row\Assembler\ProductSelectorFieldAssembler;
use Bitrix\Catalog\Grid\Row\Assembler\ProductTypeFieldAssembler;
use Bitrix\Catalog\Grid\Row\Assembler\LockedFieldAssembler;
use Bitrix\Catalog\Grid\Row\Assembler\ProductNameFieldAssembler;
use Bitrix\Catalog\Grid\Row\Assembler\PurchasingPriceFieldAssembler;
use Bitrix\Catalog\Grid\Row\Assembler\VatFieldAssembler;
use Bitrix\Catalog\Grid\Settings\ProductSettings;
use Bitrix\Catalog\ProductTable;
use Bitrix\Iblock\Grid\Row\Assembler\SectionNameFieldAssembler;
use Bitrix\Iblock\Grid\Row\ElementRowAssembler;
use Bitrix\Main\Loader;

Loader::requireModule('iblock');

/**
 * @property ProductSettings $settings
 */
class ProductRowAssembler extends ElementRowAssembler
{
	private bool $isUseCatalogTab = false;
	private bool $isUseSkuSelector = false;

	public function setUseCatalogTab(bool $value = true): void
	{
		$this->isUseCatalogTab = $value;
	}

	public function setUseSkuSelector(bool $value = true): void
	{
		$this->isUseSkuSelector = $value;
	}

	protected function prepareFieldAssemblers(): array
	{
		$result = parent::prepareFieldAssemblers();

		if ($this->isUseSkuSelector)
		{
			$result[] = new ProductSelectorFieldAssembler(
				'PRODUCT', $this->settings
			);
		}
		else
		{
			$result[] = new ProductNameFieldAssembler(
				['PRODUCT'],
				$this->settings->getUrlBuilder()
			);
		}

		$result[] = new SectionNameFieldAssembler(
			['PRODUCT'],
			$this->settings->getUrlBuilder()
		);

		$result[] = new MeasureFieldAssembler(['MEASURE']);

		$result[] = new ProductTypeFieldAssembler(['TYPE']);

		$result[] = new VatFieldAssembler(['VAT_ID']);

		$result[] = new LockedFieldAssembler($this->settings);

		$result[] = (new PriceFieldAssemblerFactory)->createForCatalogPrices();

		$result[] = new PurchasingPriceFieldAssembler();

		$result[] = new MorePhotoAssembler(
			['MORE_PHOTO'],
			$this->settings
		);

		return $result;
	}

	private function getClearedProductFields(): array
	{
		$result = array_fill_keys(ProductTable::getProductTypes(false), []);

		$baseClearSkuFields = [
			'QUANTITY',
			'QUANTITY_RESERVED',
			'QUANTITY_TRACE',
			'CAN_BUY_ZERO',
			'PURCHASING_PRICE',
			'PURCHASING_CURRENCY',
			'MEASURE',
			'VAT_INCLUDED',
			'VAT_ID',
			'WEIGHT',
			'WIDTH',
			'LENGTH',
			'HEIGHT',
		];

		if (!$this->isUseSkuSelector && !$this->isUseCatalogTab)
		{
			$result[ProductTable::TYPE_SKU] = $baseClearSkuFields;
		}

		if (!$this->isUseCatalogTab)
		{
			$result[ProductTable::TYPE_EMPTY_SKU] = $baseClearSkuFields;
		}

		$result[ProductTable::TYPE_SET] = [
			'QUANTITY_RESERVED',
		];

		$result[ProductTable::TYPE_SERVICE] = [
			'QUANTITY',
			'QUANTITY_RESERVED',
			'QUANTITY_TRACE',
			'CAN_BUY_ZERO',
			'WEIGHT',
			'WIDTH',
			'LENGTH',
			'HEIGHT',
		];

		return $result;
	}

	private function clearProductFields(array $rowsList): array
	{
		$clearedFields = $this->getClearedProductFields();

		foreach ($rowsList as $index => $rowItem)
		{
			$productType = (int)($rowList[$index]['data']['TYPE'] ?? 0);
			if (isset($clearedFields[$productType]))
			{
				foreach ($clearedFields[$productType] as $fieldName)
				{
					if (isset($rowList[$index]['data'][$fieldName]))
					{
						$rowList[$index]['data'][$fieldName] = '';
					}
				}
			}
		}

		return $rowsList;
	}

	public function prepareRows(array $rowsList): array
	{
		$rowsList = $this->clearProductFields($rowsList);

		return parent::prepareRows($rowsList);
	}
}
