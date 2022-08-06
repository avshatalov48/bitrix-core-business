<?php

namespace Bitrix\Catalog\Document\Action\Barcode;

use Bitrix\Catalog\StoreBarcodeTable;

/**
 * Barcodes base trait.
 */
trait BaseStoreBarcodeAction
{
	/**
	 * @var int|null
	 */
	protected $storeId;

	/**
	 * @var int
	 */
	protected $productId;

	/**
	 * @var string
	 */
	protected $barcode;

	/**
	 * @var int|null
	 */
	protected $userId;

	/**
	 * @param int|null $storeId
	 * @param int $productId
	 * @param string $barcode
	 * @param int $userId
	 */
	public function __construct(
		?int $storeId,
		int $productId,
		string $barcode,
		int $userId
	)
	{
		$this->storeId = $storeId;
		$this->productId = $productId;
		$this->barcode = $barcode;
		$this->userId = $userId;
	}

	/**
	 * Barcode row.
	 *
	 * @return array|null
	 */
	protected function getBarcodeRow(): ?array
	{
		return StoreBarcodeTable::getRow([
			'select' => [
				'ID',
				'STORE_ID',
				'PRODUCT_ID',
				'BARCODE',
			],
			'filter' => [
				'=BARCODE' => $this->barcode,
			],
		]) ?: null;
	}
}