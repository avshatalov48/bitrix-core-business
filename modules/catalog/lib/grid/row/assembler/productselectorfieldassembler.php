<?php

namespace Bitrix\Catalog\Grid\Row\Assembler;

use Bitrix\Catalog\Grid\Settings\ProductSettings;
use Bitrix\Catalog\v2\IoC\ServiceContainer;
use Bitrix\Iblock\Grid\RowType;
use Bitrix\Main\Grid\Row\FieldAssembler;
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;

Loader::requireModule('iblock');

/**
 * @method ProductSettings getSettings()
 */
class ProductSelectorFieldAssembler extends FieldAssembler
{
	/**
	 * @var array in format `[productId => skuId]`
	 */
	private array $skuProducts;
	private array $skuTree;

	/**
	 * @param string $columnsId support for only one column in grid
	 * @param ProductSettings $settings
	 */
	public function __construct(
		string $columnsId,
		ProductSettings $settings
	)
	{
		parent::__construct([ $columnsId ], $settings);

		$this->skuProducts = $settings->getSelectedProductOfferIds() ?? [];
		$this->initSkuTree();
		$this->preloadResources();
	}

	public function prepareRows(array $rowList): array
	{
		foreach ($rowList as $index => $rowItem)
		{
			$type = $rowItem['data']['ROW_TYPE'] ?? null;
			if (!isset($type))
			{
				continue;
			}

			foreach ($this->getColumnIds() as $columnId)
			{
				if ($type !== RowType::ELEMENT || $this->getSettings()->isExcelMode())
				{
					$rowList[$index]['columns'][$columnId] = $rowItem['columns']['NAME'] ?? $rowItem['data']['NAME'];
				}
				else
				{
					$rowList[$index]['columns'][$columnId] = $this->getProductSelectorHtml($rowList[$index]['data']);
				}
			}
		}

		return $rowList;
	}

	private function initSkuTree(): void
	{
		$this->skuTree = [];

		if (empty($this->skuProducts))
		{
			return;
		}

		/** @var \Bitrix\Catalog\Component\SkuTree $skuTree */
		$skuTree = ServiceContainer::make('sku.tree', [
			'iblockId' => $this->getSettings()->getIblockId(),
		]);
		if (!$skuTree)
		{
			return;
		}

		$this->skuTree = $skuTree->loadJsonOffers($this->skuProducts);
	}

	/**
	 * Preload resources.
	 *
	 * It is always called, even if empty list of products.
	 * It is necessary for correct display in case of filtering.
	 *
	 * Example, an empty list with a filter, the filter was reset - the products appeared, but the resources were not loaded.
	 *
	 * @return void
	 */
	private function preloadResources(): void
	{
		Asset::getInstance()->addJs('/bitrix/components/bitrix/catalog.grid.product.field/templates/.default/script.js');
		Asset::getInstance()->addCss('/bitrix/components/bitrix/catalog.grid.product.field/templates/.default/style.css');
	}

	private function getProductSelectorHtml(array $row): string
	{
		global $APPLICATION;

		/**
		 * @var CMain $APPLICATION
		 */

		$productId = (int)$row['ID'];
		$skuId = $this->getProductSkuId($productId);

		$productFields = array_merge($row, [
			'IBLOCK_ID' => $this->getSettings()->getIblockId(),
			'SKU_IBLOCK_ID' => $this->getSettings()->getOffersIblockId(),
			'SKU_ID' => $skuId,
		]);

		$urlBuilder = $this->getSettings()->getUrlBuilder();

		ob_start();
		$APPLICATION->IncludeComponent(
			'bitrix:catalog.grid.product.field',
			'',
			[
				'GRID_ID' => $this->getSettings()->getId(),
				'COLUMN_NAME' => current($this->getColumnIds()),
				'ROW_ID' => RowType::ELEMENT . $productId,
				'ROW_ID_MASK' => 'E#ID#',
				'PRODUCT_FIELDS' => $productFields,
				'ENABLE_IMAGE_INPUT' => false,
				'ENABLE_CHANGES_RENDERING' => false,
				'USE_SKU_TREE' => true,
				'BUILDER_CONTEXT' => isset($urlBuilder) ? $urlBuilder->getId() : null,
				'SKU_TREE' => $this->skuTree[$productId][$skuId] ?? [],
			]
		);

		return ob_get_clean();
	}

	private function getProductSkuId(int $productId): ?int
	{
		return $this->skuProducts[$productId] ?? null;
	}
}
