<?php

namespace Bitrix\Catalog\v2\Barcode;

use Bitrix\Catalog\v2\BaseEntity;
use Bitrix\Catalog\v2\Fields\TypeCasters\MapTypeCaster;
use Bitrix\Catalog\v2\HasSettingsTrait;

/**
 * Class Barcode
 *
 * @package Bitrix\Catalog\v2\Barcode
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */

class Barcode extends BaseEntity
{
	use HasSettingsTrait;

	public function __construct(BarcodeRepositoryContract $repository)
	{
		parent::__construct($repository);
	}

	public function setStoreId(int $storeId): self
	{
		$this->setField('STORE_ID', $storeId);

		return $this;
	}

	public function getStoreId(): string
	{
		return $this->getField('STORE_ID');
	}

	public function setProductId(int $productId): self
	{
		$this->setField('PRODUCT_ID', $productId);

		return $this;
	}

	public function getProductId(): int
	{
		return (int)$this->getField('PRODUCT_ID');
	}

	public function setBarcode(?string $barcode): self
	{
		$this->setField('BARCODE', $barcode);

		return $this;
	}

	public function unsetBarcode(): self
	{
		return $this->setBarcode(null);
	}

	public function hasBarcode(): bool
	{
		return $this->hasField('BARCODE');
	}

	public function getBarcode(): ?string
	{
		return $this->hasBarcode() ? (string)$this->getField('BARCODE') : null;
	}

	protected function getFieldsMap(): array
	{
		return [
			'ID' => MapTypeCaster::NULLABLE_INT,
			'PRODUCT_ID' => MapTypeCaster::INT,
			'STORE_ID' => MapTypeCaster::INT,
			'BARCODE' =>MapTypeCaster::NULLABLE_STRING,
		];
	}
}
