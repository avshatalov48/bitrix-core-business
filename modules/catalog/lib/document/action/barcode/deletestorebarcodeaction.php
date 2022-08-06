<?php

namespace Bitrix\Catalog\Document\Action\Barcode;

use Bitrix\Catalog\Document\Action;
use Bitrix\Main\Result;
use CCatalogStoreBarCode;

/**
 * Delete barcode from store.
 */
class DeleteStoreBarcodeAction implements Action
{
	use BaseStoreBarcodeAction;

	/**
	 * @inheritDoc
	 */
	public function canExecute(): Result
	{
		return new Result();
	}

	/**
	 * @inheritDoc
	 *
	 * @return Result
	 */
	public function execute(): Result
	{
		$result = new Result();

		$row = $this->getBarcodeRow();
		if ($row)
		{
			CCatalogStoreBarCode::delete($row['ID']);
		}

		return $result;
	}
}