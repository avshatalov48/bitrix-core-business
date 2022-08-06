<?php

namespace Bitrix\Catalog\Document\Action\Store;

use Bitrix\Catalog\Document\Action;
use Bitrix\Main\Result;

/**
 * Increase store availability quantity of product.
 */
class IncreaseStoreQuantityAction  implements Action
{
	use BaseStoreQuantityAction;

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
	protected function getProductAmountNew(): float
	{
		return $this->getStoreProductAmount() + $this->amount;
	}
}