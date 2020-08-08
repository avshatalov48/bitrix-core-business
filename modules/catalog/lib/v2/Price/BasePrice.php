<?php

namespace Bitrix\Catalog\v2\Price;

use Bitrix\Catalog\v2\BaseCollection;
use Bitrix\Catalog\v2\BaseEntity;
use Bitrix\Catalog\v2\Fields\TypeCasters\MapTypeCaster;

/**
 * Class BasePrice
 *
 * @package Bitrix\Catalog\v2\Price
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
abstract class BasePrice extends BaseEntity
{
	public function __construct(PriceRepositoryContract $priceRepository)
	{
		parent::__construct($priceRepository);
	}

	public function setParentCollection(?BaseCollection $collection): BaseEntity
	{
		parent::setParentCollection($collection);

		$parent = $this->getParent();

		if ($parent && !$parent->isNew())
		{
			$this->setProductId($parent->getId());
		}

		return $this;
	}

	public function setPrice(float $price): self
	{
		$this->setField('PRICE', $price);

		return $this;
	}

	public function getPrice(): float
	{
		return (float)$this->getField('PRICE');
	}

	public function setCurrency($currency): self
	{
		$this->setField('CURRENCY', $currency);

		return $this;
	}

	public function getCurrency(): string
	{
		return (string)$this->getField('CURRENCY');
	}

	public function isPriceBase(): bool
	{
		return $this->getField('BASE') === 'Y';
	}

	public function setPriceBase(bool $state): self
	{
		$this->setField('BASE', $state ? 'Y' : 'N');

		return $this;
	}

	public function setGroupId(int $groupId): self
	{
		$this->setField('CATALOG_GROUP_ID', $groupId);

		return $this;
	}

	public function getGroupId(): int
	{
		return (int)$this->getField('CATALOG_GROUP_ID');
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

	protected function getFieldsMap(): array
	{
		return [
			'ID' => MapTypeCaster::NULLABLE_INT,
			'PRODUCT_ID' => MapTypeCaster::INT,
			'EXTRA_ID' => MapTypeCaster::NULLABLE_INT,
			'CATALOG_GROUP_ID' => MapTypeCaster::INT,
			'PRICE' => MapTypeCaster::FLOAT,
			'CURRENCY' => MapTypeCaster::STRING,
			'TIMESTAMP_X' => MapTypeCaster::DATETIME,
			'QUANTITY_FROM' => MapTypeCaster::NULLABLE_INT,
			'QUANTITY_TO' => MapTypeCaster::NULLABLE_INT,
		];
	}
}