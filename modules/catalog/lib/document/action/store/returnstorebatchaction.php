<?php

namespace Bitrix\Catalog\Document\Action\Store;

use Bitrix\Catalog\Document\Action;
use Bitrix\Catalog\EO_StoreDocumentElement;
use Bitrix\Catalog\Product\Store\BatchManager;
use Bitrix\Catalog\Product\Store\DistributionStrategy\DeductDocument;
use Bitrix\Catalog\StoreDocumentElementTable;
use Bitrix\Main\Result;

/**
 * Remove existed batch of products.
 */
class ReturnStoreBatchAction implements Action
{
	use WriteOffAmountValidator;

	protected int $productId;
	protected ?EO_StoreDocumentElement $storeDocumentElement;
	public function __construct(int $documentElementId)
	{
		$this->storeDocumentElement = StoreDocumentElementTable::getList([
			'filter' => [
				'=ID' => $documentElementId,
			],
			'select' => ['*', 'DOCUMENT'],
			'limit' => 1
		])
			->fetchObject()
		;

		if ($this->storeDocumentElement)
		{
			$this->productId = $this->storeDocumentElement->getElementId();
		}
	}

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
	public function execute(): Result
	{
		if (!$this->storeDocumentElement)
		{
			return new Result();
		}

		$distributor = new DeductDocument(
			new BatchManager($this->getProductId()),
			$this->storeDocumentElement
		);

		return $distributor->return();
	}
}