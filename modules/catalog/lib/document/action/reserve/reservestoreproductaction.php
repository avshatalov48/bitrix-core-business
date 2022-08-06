<?php

namespace Bitrix\Catalog\Document\Action\Reserve;

use Bitrix\Catalog\Document\Action;
use Bitrix\Main\Result;

/**
 * Reserve product.
 */
class ReserveStoreProductAction implements Action
{
	use BaseReserveStoreProductAction;

	/**
	 * @inheritDoc
	 */
	public function canExecute(): Result
	{
		return new Result();
	}

	/**
	 * @inheritDoc
	 */
	protected function getNewProductQuantity(): float
	{
		return $this->getProductTotalQuantity() - $this->amount;
	}

	/**
	 * @inheritDoc
	 */
	protected function getNewProductReservedQuantity(): float
	{
		return $this->getProductTotalReservedQuantity() + $this->amount;
	}

	/**
	 * @inheritDoc
	 */
	protected function getNewStoreReservedQuantity(): float
	{
		return $this->getStoreReservedQuantity() + $this->amount;
	}
}