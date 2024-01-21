<?php

namespace Bitrix\Catalog\Grid\Panel\UI;

use Bitrix\Catalog\Grid\Access\ProductRightsChecker;
use Bitrix\Catalog\Grid\Panel\UI\Item\ChangePricesActionsItem;
use Bitrix\Catalog\Grid\Panel\UI\Item\EditActionsItem;
use Bitrix\Catalog\Grid\Panel\UI\Item\ProductGroupActionsItem;
use Bitrix\Iblock\Grid\Panel\UI\Actions\Item\ElementGroupActionsItem;
use Bitrix\Iblock\Grid\Panel\UI\ElementPanelProvider;
use Bitrix\Main\Loader;

Loader::requireModule('iblock');

/**
 * @method ProductRightsChecker getIblockRightsChecker()
 */
class ProductPanelProvider extends ElementPanelProvider
{
	public function prepareActions(): array
	{
		$elementActions = parent::prepareActions();

		$listMode = $this->getListMode();

		foreach ($elementActions as &$actionItem)
		{
			if ($actionItem instanceof ElementGroupActionsItem)
			{
				$actionItem = new ProductGroupActionsItem(
					$this->getIblockId(),
					$this->getIblockRightsChecker(),
					$listMode
				);
			}
			elseif ($actionItem instanceof \Bitrix\Iblock\Grid\Panel\UI\Actions\Item\EditActionsItem)
			{
				$actionItem = new EditActionsItem($this->getIblockId(), $this->getColumns(), $this->getIblockRightsChecker());
			}
		}

		if ($this->getIblockRightsChecker()->canEditPrices())
		{
			$elementActions[] = new ChangePricesActionsItem($this->getIblockId(), $this->getIblockRightsChecker());
		}

		return $elementActions;
	}
}
