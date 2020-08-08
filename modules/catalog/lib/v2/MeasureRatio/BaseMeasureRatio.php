<?php

namespace Bitrix\Catalog\v2\MeasureRatio;

use Bitrix\Catalog\v2\BaseCollection;
use Bitrix\Catalog\v2\BaseEntity;
use Bitrix\Catalog\v2\Fields\TypeCasters\MapTypeCaster;

/**
 * Class BaseMeasureRatio
 *
 * @package Bitrix\Catalog\v2\MeasureRatio
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
abstract class BaseMeasureRatio extends BaseEntity
{
	public function __construct(MeasureRatioRepositoryContract $measureRatioRepository)
	{
		parent::__construct($measureRatioRepository);
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

	public function setRatio(float $ratio): self
	{
		$this->setField('RATIO', $ratio);

		return $this;
	}

	public function getRatio(): string
	{
		return $this->getField('RATIO');
	}

	public function isDefault(): bool
	{
		return $this->getField('IS_DEFAULT') === 'Y';
	}

	public function setDefault(bool $state = true): self
	{
		$this->setField('IS_DEFAULT', $state ? 'Y' : 'N');

		return $this;
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
			'RATIO' => MapTypeCaster::FLOAT,
			'IS_DEFAULT' => MapTypeCaster::Y_OR_N,
		];
	}
}