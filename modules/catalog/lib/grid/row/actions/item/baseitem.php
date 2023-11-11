<?php

namespace Bitrix\Catalog\Grid\Row\Actions\Item;

use Bitrix\Catalog\Grid\Access\ProductRightsChecker;

abstract class BaseItem extends \Bitrix\Main\Grid\Row\Action\BaseAction
{
	private int $iblockId;
	private ProductRightsChecker $rights;

	public function __construct(int $iblockId, ProductRightsChecker $rights)
	{
		$this->iblockId = $iblockId;
		$this->rights = $rights;
	}

	protected function getIblockId(): int
	{
		return $this->iblockId;
	}

	protected function getProductRightsChecker(): ProductRightsChecker
	{
		return $this->rights;
	}
}
