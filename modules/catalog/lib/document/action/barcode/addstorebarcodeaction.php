<?php

namespace Bitrix\Catalog\Document\Action\Barcode;

use Bitrix\Catalog\Document\Action;
use Bitrix\Catalog\Document\Action\ProductAndStoreInfo;
use Bitrix\Catalog\ProductTable;
use Bitrix\Main\Application;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use CCatalogStoreBarCode;

Loc::loadMessages(
	Application::getDocumentRoot() .'/bitrix/modules/catalog/general/store_docs_type.php'
);

/**
 * Add barcode to store.
 */
class AddStoreBarcodeAction implements Action
{
	use ProductAndStoreInfo;
	use BaseStoreBarcodeAction;

	/**
	 * @inheritDoc
	 */
	public function canExecute(): Result
	{
		$result = new Result();

		$row = $this->getBarcodeRow();
		if ($row && $this->productId !== (int)$row['PRODUCT_ID'])
		{
			$message = Loc::getMessage('CATALOG_STORE_DOCS_ERR_BARCODE_ALREADY_EXIST', [
				'#PRODUCT#' => $this->getProductName(),
				'#BARCODE#' => $this->barcode,
			]);
			$result->addError(new Error($message));
		}

		return $result;
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
			if ($this->storeId === (int)$row['STORE_ID'])
			{
				// no changes - no action
				return $result;
			}

			$id = CCatalogStoreBarCode::Update($row['ID'], [
				'STORE_ID' => $this->storeId,
				'MODIFIED_BY' => $this->userId,
			]);
		}
		else
		{
			$id = CCatalogStoreBarCode::add([
				'PRODUCT_ID' => $this->productId,
				'STORE_ID' => $this->storeId,
				'BARCODE' => $this->barcode,
				'MODIFIED_BY' => $this->userId,
				'CREATED_BY' => $this->userId,
			]);
		}

		if (!$id)
		{
			$result->addError(new Error('Can\'t save barcode'));
		}

		return $result;
	}
}