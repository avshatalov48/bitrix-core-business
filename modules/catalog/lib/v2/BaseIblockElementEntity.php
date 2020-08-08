<?php

namespace Bitrix\Catalog\v2;

use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\v2\Fields\TypeCasters\MapTypeCaster;
use Bitrix\Catalog\v2\Iblock\IblockInfo;
use Bitrix\Catalog\v2\Property\HasPropertyCollection;
use Bitrix\Catalog\v2\Property\PropertyCollection;
use Bitrix\Catalog\v2\Property\PropertyRepositoryContract;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\Result;

/**
 * Class BaseIblockElementEntity
 *
 * @package Bitrix\Catalog\v2
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
abstract class BaseIblockElementEntity extends BaseEntity implements HasPropertyCollection
{
	/** @var \Bitrix\Catalog\v2\Iblock\IblockInfo */
	protected $iblockInfo;
	/** @var \Bitrix\Catalog\v2\Property\PropertyRepositoryContract */
	protected $propertyRepository;
	/** @var \Bitrix\Catalog\v2\Property\PropertyCollection|\Bitrix\Catalog\v2\Property\Property[] */
	protected $propertyCollection;

	public function __construct(
		IblockInfo $iblockInfo,
		RepositoryContract $repository,
		PropertyRepositoryContract $propertyRepository
	)
	{
		parent::__construct($repository);
		$this->iblockInfo = $iblockInfo;
		$this->propertyRepository = $propertyRepository;
	}

	/**
	 * @return \Bitrix\Catalog\v2\Iblock\IblockInfo
	 */
	public function getIblockInfo(): IblockInfo
	{
		return $this->iblockInfo;
	}

	/**
	 * @return \Bitrix\Catalog\v2\Property\PropertyCollection|\Bitrix\Catalog\v2\Property\Property[]
	 */
	public function getPropertyCollection(): PropertyCollection
	{
		if ($this->propertyCollection === null)
		{
			// ToDo make lazy load like sku collection with iterator callback?
			$this->setPropertyCollection($this->loadPropertyCollection());
		}

		return $this->propertyCollection;
	}

	/**
	 * @return \Bitrix\Catalog\v2\Property\PropertyCollection|\Bitrix\Catalog\v2\Property\Property[]
	 */
	protected function loadPropertyCollection(): BaseCollection
	{
		return $this->propertyRepository->getCollectionByParent($this);
	}

	/**
	 * @param \Bitrix\Catalog\v2\Property\PropertyCollection $propertyCollection
	 * @return $this
	 *
	 * @internal
	 */
	public function setPropertyCollection(PropertyCollection $propertyCollection): self
	{
		$this->propertyCollection = $propertyCollection;

		return $this;
	}

	public function getIblockId(): ?int
	{
		return (int)$this->getField('IBLOCK_ID') ?: null;
	}

	public function setIblockId(int $iblockId): BaseEntity
	{
		return $this->setField('IBLOCK_ID', $iblockId);
	}

	public function setField(string $name, $value): BaseEntity
	{
		if ($name === 'IBLOCK_ID')
		{
			$iblockId = $this->getIblockId();

			// ToDo make immutable field type in type caster
			if ($iblockId !== null && $iblockId != $value)
			{
				throw new NotSupportedException('Iblock id field has been already initialized.');
			}
		}
		elseif ($name === 'DETAIL_PICTURE' || $name === 'PREVIEW_PICTURE')
		{
			if (is_numeric($value))
			{
				$value = \CFile::MakeFileArray($value);
			}
		}

		return parent::setField($name, $value);
	}

	// ToDo make tests coverage for TYPEs
	public function setType(int $type): BaseEntity
	{
		return $this->setField('TYPE', $type);
	}

	public function getType(): int
	{
		return (int)$this->getField('TYPE');
	}

	public function isSimple(): bool
	{
		return $this->getType() === ProductTable::TYPE_PRODUCT;
	}

	public function setActive(bool $active): BaseEntity
	{
		return $this->setField('ACTIVE', $active ? 'Y' : 'N');
	}

	public function isActive(): bool
	{
		return $this->getField('ACTIVE') === 'Y';
	}

	public function setName($name): BaseEntity
	{
		return $this->setField('NAME', $name);
	}

	public function getName()
	{
		return $this->getField('NAME');
	}

	public function hasName(): bool
	{
		return $this->getName() !== null && $this->getName() !== '';
	}

	public function saveInternal(): Result
	{
		$propertyCollectionChanged = $this->propertyCollection && $this->propertyCollection->isChanged();

		$result = parent::saveInternal();

		// ToDo reload if at least one file property changed?
		if ($propertyCollectionChanged && $result->isSuccess())
		{
			// re-initialize saved ids from database after file is saved
			$this->setPropertyCollection($this->loadPropertyCollection());
		}

		return $result;
	}

	protected function getFieldsMap(): array
	{
		return [
			'ID' => MapTypeCaster::NULLABLE_INT,
			'IBLOCK_ID' => MapTypeCaster::INT,
			'NAME' => MapTypeCaster::NULLABLE_STRING,
			'CODE' => MapTypeCaster::NULLABLE_STRING,
			'XML_ID' => MapTypeCaster::NULLABLE_STRING,
			'TIMESTAMP_X' => MapTypeCaster::DATETIME,
			'MODIFIED_BY' => MapTypeCaster::NULLABLE_INT,
			'DATE_CREATE' => MapTypeCaster::DATETIME,
			'CREATED_BY' => MapTypeCaster::NULLABLE_INT,
			'IBLOCK_SECTION_ID' => MapTypeCaster::NULLABLE_INT,
			'ACTIVE' => MapTypeCaster::Y_OR_N,
			'ACTIVE_FROM' => MapTypeCaster::DATETIME,
			'ACTIVE_TO' => MapTypeCaster::DATETIME,
			'SORT' => MapTypeCaster::NULLABLE_INT,
			'PREVIEW_TEXT' => MapTypeCaster::NULLABLE_STRING,
			'PREVIEW_TEXT_TYPE' => MapTypeCaster::NULLABLE_STRING,
			'DETAIL_TEXT' => MapTypeCaster::NULLABLE_STRING,
			'DETAIL_TEXT_TYPE' => MapTypeCaster::NULLABLE_STRING,

			'PREVIEW_PICTURE' => static function ($value) {
				if (is_numeric($value))
				{
					return (int)$value;
				}

				return $value;
			},
			'DETAIL_PICTURE' => static function ($value) {
				if (is_numeric($value))
				{
					return (int)$value;
				}

				return $value;
			},

			'QUANTITY' => MapTypeCaster::NULLABLE_FLOAT,
			'WEIGHT' => MapTypeCaster::NULLABLE_INT,
			'VAT_ID' => MapTypeCaster::NULLABLE_INT,
			'VAT_INCLUDED' => MapTypeCaster::Y_OR_N,
			'PURCHASING_PRICE' => MapTypeCaster::NULLABLE_FLOAT,
			'PURCHASING_CURRENCY' => MapTypeCaster::NULLABLE_STRING,
			'BARCODE_MULTI' => MapTypeCaster::Y_OR_N,
			'QUANTITY_RESERVED' => MapTypeCaster::NULLABLE_FLOAT,
			'WIDTH' => MapTypeCaster::NULLABLE_FLOAT,
			'LENGTH' => MapTypeCaster::NULLABLE_FLOAT,
			'HEIGHT' => MapTypeCaster::NULLABLE_FLOAT,
			'MEASURE' => MapTypeCaster::NULLABLE_INT,
			'TYPE' => MapTypeCaster::NULLABLE_INT,
			'AVAILABLE' => MapTypeCaster::Y_OR_N,
			'BUNDLE' => MapTypeCaster::Y_OR_N,

			'QUANTITY_TRACE' => MapTypeCaster::Y_OR_N_OR_D,
			'CAN_BUY_ZERO' => MapTypeCaster::Y_OR_N_OR_D,
			'SUBSCRIBE' => MapTypeCaster::Y_OR_N_OR_D,
		];
	}
}