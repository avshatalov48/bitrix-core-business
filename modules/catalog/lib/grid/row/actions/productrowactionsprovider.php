<?php

namespace Bitrix\Catalog\Grid\Row\Actions;

use Bitrix\Catalog\Grid\Access\ProductRightsChecker;
use Bitrix\Catalog\Grid\Row\Actions\Item\ConvertToProductItem;
use Bitrix\Catalog\Grid\Row\Actions\Item\ConvertToServiceItem;
use Bitrix\Catalog\Grid\Settings\ProductSettings;
use Bitrix\Catalog\ProductTable;
use Bitrix\Iblock\Grid\Row\Actions\ElementRowActionsProvider;
use Bitrix\Main\Loader;

Loader::requireModule('iblock');

/**
 * @property ProductSettings $settings
 * @method ProductRightsChecker getIblockRightsChecker()
 */
class ProductRowActionsProvider extends ElementRowActionsProvider
{
	public function __construct(ProductSettings $settings, ProductRightsChecker $rights)
	{
		parent::__construct($settings, $rights);
	}

	public function prepareActions(): array
	{
		$elementActions = parent::prepareActions();

		if ($this->getIblockRightsChecker()->canEditElements())
		{
			$elementActions[] = new ConvertToServiceItem($this->getIblockId(), $this->getIblockRightsChecker());
			$elementActions[] = new ConvertToProductItem($this->getIblockId(), $this->getIblockRightsChecker());
		}

		return $elementActions;
	}

	public function prepareControls(array $rawFields): array
	{
		$controls = parent::prepareControls($rawFields);

		$isSection = isset($rawFields['ROW_TYPE']) && $rawFields['ROW_TYPE'] === 'S';
		if (!$isSection)
		{
			$id = (int)($rawFields['ID'] ?? 0);
			if ($id > 0)
			{
				$additionalItems = [];

				$productType = (int)($rawFields['TYPE'] ?? $this->getProductType($id) ?? 0);
				if ($productType === ProductTable::TYPE_SERVICE)
				{
					$additionalItems[] = $this->getActionById(ConvertToProductItem::getId());
				}
				else
				{
					$additionalItems[] = $this->getActionById(ConvertToServiceItem::getId());
				}

				foreach ($additionalItems as $item)
				{
					/**
					 * @var \Bitrix\Main\Grid\Row\Action\BaseAction|null $item
					 */
					$control = isset($item) ? $item->getControl($rawFields) : null;
					if (isset($control))
					{
						$controls[] = $control;
					}
				}
			}
		}

		return $controls;
	}

	private function getProductType(int $productId): ?int
	{
		$row = ProductTable::getRow([
			'select' => [
				'TYPE'
			],
			'filter' => [
				'=ID' => $productId,
			],
		]);
		if ($row)
		{
			return (int)$row['TYPE'];
		}

		return null;
	}
}
