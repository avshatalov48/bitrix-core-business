<?php

namespace Bitrix\Catalog\Grid\Panel\UI\Item;

use Bitrix\Catalog\Grid\Access\ProductRightsChecker;
use Bitrix\Catalog\Grid\Panel\UI\Item\Group\ChangePricesGroupChild;
use Bitrix\Catalog\Grid\Panel\UI\Item\Group\ConvertToProductGroupChild;
use Bitrix\Catalog\Grid\Panel\UI\Item\Group\ConvertToServiceGroupChild;
use Bitrix\Catalog\Grid\Panel\UI\Item\Group\SetParametersGroupChild;
use Bitrix\Iblock\Grid\Panel\UI\Actions\Item\ElementGroupActionsItem;

/**
 * @property ProductRightsChecker $rights
 */
class ProductGroupActionsItem extends ElementGroupActionsItem
{
	public function __construct(int $iblockId, ProductRightsChecker $rights, string $listMode)
	{
		parent::__construct($iblockId, $rights, $listMode);
	}

	protected function prepareChildItems(): array
	{
		$result = parent::prepareChildItems();

		if ($this->rights->canEditElements())
		{
			$result[] = new ConvertToServiceGroupChild($this->iblockId, $this->rights, $this->listMode);
			$result[] = new ConvertToProductGroupChild($this->iblockId, $this->rights, $this->listMode);
			$result[] = new SetParametersGroupChild($this->iblockId, $this->rights, $this->listMode);
		}

		if ($this->rights->canEditPrices())
		{
			$result[] = new ChangePricesGroupChild();
		}

		return $result;
	}
}
