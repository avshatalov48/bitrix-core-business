<?php

namespace Bitrix\Catalog\v2\StoreProduct;

use Bitrix\Catalog\v2\BaseEntity;
use Bitrix\Catalog\v2\Fields\TypeCasters\MapTypeCaster;
use Bitrix\Catalog\v2\HasSettingsTrait;

/**
 * Class StoreProduct
 *
 * @package Bitrix\Catalog\v2\StoreProduct
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */

class StoreProduct extends BaseEntity
{
	use HasSettingsTrait;

	public function __construct(StoreProductRepositoryContract $repository)
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

	public function setAmount(?float $amount): self
	{
		$this->setField('AMOUNT', $amount);

		return $this;
	}

	public function unsetAmount(): self
	{
		return $this->setAmount(null);
	}

	public function hasAmount(): bool
	{
		return $this->hasField('AMOUNT');
	}

	public function getAmount(): ?float
	{
		return $this->hasAmount() ? (float)$this->getField('AMOUNT') : null;
	}

	protected function getFieldsMap(): array
	{
		return [
			'ID' => MapTypeCaster::NULLABLE_INT,
			'STORE_ID' => MapTypeCaster::INT,
			'PRODUCT_ID' => MapTypeCaster::INT,
			'AMOUNT' => MapTypeCaster::NULLABLE_FLOAT,
			'QUANTITY_RESERVED' =>  MapTypeCaster::NULLABLE_FLOAT,
		];
	}
}
