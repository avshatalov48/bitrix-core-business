<?php

namespace Bitrix\Catalog\Grid;

use Bitrix\Catalog\Filter\Factory\ProductFilterFactory;
use Bitrix\Catalog\Grid\Access\ProductRightsChecker;
use Bitrix\Catalog\Grid\Column\Factory\ProductColumnsFactory;
use Bitrix\Catalog\Grid\Column\PriceProvider;
use Bitrix\Catalog\Grid\Panel\UI\ProductPanel;
use Bitrix\Catalog\Grid\Panel\UI\ProductPanelProvider;
use Bitrix\Catalog\Grid\Row\Actions\ProductRowActionsProvider;
use Bitrix\Catalog\Grid\Row\ProductRowAssembler;
use Bitrix\Catalog\Grid\Settings\ProductSettings;
use Bitrix\Catalog\GroupTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Filter\Filter;
use Bitrix\Main\Grid\Column\Column;
use Bitrix\Main\Grid\Column\Columns;
use Bitrix\Main\Grid\Grid;
use Bitrix\Main\Grid\Row\Rows;
use Bitrix\Main\Loader;

Loader::requireModule('iblock');

/**
 * @method ProductSettings getSettings()
 * @method ProductPanel getPanel()
 */
class ProductGrid extends Grid
{
	private bool $useFilter = false;
	private ProductRightsChecker $rights;

	public function __construct(ProductSettings $settings)
	{
		parent::__construct($settings);
	}

	public function setUseFilter(bool $value = true): self
	{
		$this->useFilter = $value;

		return $this;
	}

	private function getProductRightsChecker(): ProductRightsChecker
	{
		$this->rights ??= new ProductRightsChecker($this->getIblockId());

		return $this->rights;
	}

	private function getIblockId(): int
	{
		return $this->getSettings()->getIblockId();
	}

	public function getOrmFilter(): ?array
	{
		$result = parent::getOrmFilter();

		if (isset($result))
		{
			$result['IBLOCK_ID'] = $this->getSettings()->getIblockId();
		}

		return $result;
	}

	public function prepareColumns(): array
	{
		$columns = parent::prepareColumns();

		return $this->prepareSortingColumns($columns);
	}

	private function getBasePriceColumnId(): string
	{
		return PriceProvider::getPriceTypeColumnId(
			(int)GroupTable::getBasePriceTypeId()
		);
	}

	private function prepareDefaultColumns(Columns $columns): void
	{
		$defaultColumns = array_fill_keys([
			'PRODUCT',
			'MORE_PHOTO',
			'SECTIONS',
			'QUANTITY',
			'MEASURE',
			$this->getBasePriceColumnId(),
		], true);

		foreach ($columns as $column)
		{
			$id = $column->getId();
			$column->setDefault(
				isset($defaultColumns[$id])
			);
		}
	}

	/**
	 * @param Column[] $columns
	 *
	 * @return Column[]
	 */
	private function prepareSortingColumns(array $columns): array
	{
		$map = [];
		foreach ($columns as $column)
		{
			$map[$column->getId()] = $column;
		}

		$primaryColumns = [
			'ID',
			'ACTIVE',
			'PRODUCT',
			'MORE_PHOTO',
			'QUANTITY',
			'MEASURE',
			$this->getBasePriceColumnId(),
			'SHOW_COUNTER',
		];

		$result = [];
		foreach ($primaryColumns as $columnId)
		{
			if (isset($map[$columnId]))
			{
				$result[] = $map[$columnId];
				unset($map[$columnId]);
			}
		}

		array_push($result, ... array_values($map));

		return $result;
	}

	#region creation methods

	protected function createColumns(): Columns
	{
		$columns = (new ProductColumnsFactory)->create($this->getSettings());

		$this->prepareDefaultColumns($columns);

		return $columns;
	}

	protected function createRows(): Rows
	{
		$assembler = new ProductRowAssembler(
			$this->getVisibleColumnsIds(),
			$this->getSettings(),
			$this->getColumns()
		);

		$assembler->setUseCatalogTab(
			Option::get('catalog', 'show_catalog_tab_with_offers') === 'Y'
		);
		$assembler->setUseSkuSelector(
			$this->getSettings()->isSkuSelectorEnabled()
		);

		return new Rows(
			$assembler,
			new ProductRowActionsProvider(
				$this->getSettings(),
				$this->getProductRightsChecker()
			)
		);
	}

	protected function createPanel(): ProductPanel
	{
		return new ProductPanel(
			new ProductPanelProvider(
				$this->getSettings(),
				$this->getColumns(),
				$this->getProductRightsChecker()
			),
		);
	}

	protected function createFilter(): ?Filter
	{
		if (!$this->useFilter)
		{
			return null;
		}

		$settings = new \Bitrix\Catalog\Filter\DataProvider\Settings\ProductSettings([
			'ID' => $this->getId(),
			'IBLOCK_ID' => $this->getSettings()->getIblockId(),
			'VARIATION_IBLOCK_ID' => $this->getSettings()->getOffersIblockId(),
			'LINK_PROPERTY_ID' => $this->getSettings()->getSkuPropertyId(),
			//'SHOW_SECTIONS' => true,
			//'SHOW_XML_ID' => true,
		]);

		return (new ProductFilterFactory)->createBySettings($settings);
	}

	#endregion creation methods
}
