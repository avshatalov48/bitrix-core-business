<?php

namespace Bitrix\Catalog\Document\Action\Store;

use Bitrix\Catalog\EO_StoreDocumentElement;
use Bitrix\Catalog\Product\Store\BatchManager;
use Bitrix\Catalog\StoreDocumentElementTable;
use Bitrix\Main\Result;
use Bitrix\Catalog\Document\Action;
use Bitrix\Catalog\Product\Store\DistributionStrategy\DeductDocument;

/**
 * Write off products from stores.
 */
class WriteOffStoreBatchAction implements Action
{
	use WriteOffAmountValidator;
	private ?EO_StoreDocumentElement $storeDocumentElement;
	private int $productId;

	private int $storeId;
	private float $amount;
	public function __construct(
		int $documentElementId,
		int $productId,
		float $amount,
	)
	{
		$this->productId = $productId;
		$this->amount = $amount;

		$this->storeDocumentElement = StoreDocumentElementTable::getList([
				'filter' => [
					'=ID' => $documentElementId,
				],
				'limit' => 1
			])
			->fetchObject()
		;
	}

	public function canExecute(): Result
	{
		$this->productId = $this->storeDocumentElement->getElementId();
		$this->storeId = $this->storeDocumentElement->getStoreFrom();

		return $this->checkStoreAmount($this->storeDocumentElement);
	}

	/**
	 * @inheritDoc
	 */
	public function execute(): Result
	{
		$distributor = new DeductDocument(
			new BatchManager($this->productId),
			$this->storeDocumentElement
		);

		return $distributor->writeOff($this->amount);
	}
}