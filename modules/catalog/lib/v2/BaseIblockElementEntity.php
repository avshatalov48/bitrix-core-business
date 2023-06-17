<?php

namespace Bitrix\Catalog\v2;

use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\v2\Fields\TypeCasters\MapTypeCaster;
use Bitrix\Catalog\v2\Iblock\IblockInfo;
use Bitrix\Catalog\v2\Image\HasImageCollection;
use Bitrix\Catalog\v2\Image\ImageCollection;
use Bitrix\Catalog\v2\Image\ImageRepositoryContract;
use Bitrix\Catalog\v2\Property\HasPropertyCollection;
use Bitrix\Catalog\v2\Property\PropertyCollection;
use Bitrix\Catalog\v2\Property\PropertyRepositoryContract;
use Bitrix\Main\Engine\CurrentUser;
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
abstract class BaseIblockElementEntity extends BaseEntity implements HasPropertyCollection, HasImageCollection
{
	/** @var \Bitrix\Catalog\v2\Iblock\IblockInfo */
	protected $iblockInfo;
	/** @var \Bitrix\Catalog\v2\Property\PropertyRepositoryContract */
	protected $propertyRepository;
	/** @var \Bitrix\Catalog\v2\Property\PropertyCollection|\Bitrix\Catalog\v2\Property\Property[] */
	protected $propertyCollection;
	/** @var \Bitrix\Catalog\v2\Image\ImageRepositoryContract */
	protected $imageRepository;
	/** @var \Bitrix\Catalog\v2\Image\ImageCollection|\Bitrix\Catalog\v2\Image\BaseImage[] */
	protected $imageCollection;

	public function __construct(
		IblockInfo $iblockInfo,
		RepositoryContract $repository,
		PropertyRepositoryContract $propertyRepository,
		ImageRepositoryContract $imageRepository
	)
	{
		parent::__construct($repository);
		$this->iblockInfo = $iblockInfo;
		$this->propertyRepository = $propertyRepository;
		$this->imageRepository = $imageRepository;
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
	protected function loadPropertyCollection(): PropertyCollection
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
		$propertyCollection->setParent($this);

		$this->propertyCollection = $propertyCollection;

		return $this;
	}

	/**
	 * @return $this
	 *
	 * @internal
	 */
	protected function unsetPropertyCollection(): self
	{
		if ($this->propertyCollection !== null)
		{
			$this->propertyCollection->setParent(null);
			$this->propertyCollection = null;
		}

		return $this;
	}

	/**
	 * @return ImageCollection|\Bitrix\Catalog\v2\Image\BaseImage[]
	 */
	public function getImageCollection(): ImageCollection
	{
		if ($this->imageCollection === null)
		{
			$this->setImageCollection($this->loadImageCollection());
		}

		return $this->imageCollection;
	}

	/**
	 * @return ImageCollection|\Bitrix\Catalog\v2\Image\BaseImage[]
	 */
	public function getFrontImageCollection(): ImageCollection
	{
		return $this->getImageCollection();
	}

	/**
	 * @return ImageCollection|\Bitrix\Catalog\v2\Image\BaseImage[]
	 */
	protected function loadImageCollection(): ImageCollection
	{
		return $this->imageRepository->getCollectionByParent($this);
	}

	/**
	 * @param ImageCollection $imageCollection
	 * @return $this
	 *
	 * @internal
	 */
	public function setImageCollection(ImageCollection $imageCollection): self
	{
		$imageCollection->setParent($this);

		$this->imageCollection = $imageCollection;

		return $this;
	}

	/**
	 * @return $this
	 *
	 * @internal
	 */
	protected function unsetImageCollection(): self
	{
		if ($this->imageCollection !== null)
		{
			$this->imageCollection->setParent(null);
			$this->imageCollection = null;
		}

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
			$imageCollection = $this->getImageCollection();
			$image =
				$name === 'DETAIL_PICTURE'
					? $imageCollection->getDetailImage()
					: $imageCollection->getPreviewImage();

			if (is_numeric($value))
			{
				$value = \CFile::MakeFileArray($value);
			}

			if (is_array($value))
			{
				$image->setFileStructure($value);
			}

			return $this;
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
		$type = $this->getType();

		return (
			$type === ProductTable::TYPE_PRODUCT
			|| $type === ProductTable::TYPE_SERVICE
			|| $type === ProductTable::TYPE_EMPTY_SKU
		);
	}

	public function allowConvertToSku(): bool
	{
		$type = $this->getType();

		return (
			$type === ProductTable::TYPE_PRODUCT
			|| $type === ProductTable::TYPE_EMPTY_SKU
		);
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

	public function getDetailUrl(): string
	{
		return (string)$this->getField('DETAIL_PAGE_URL');
	}

	public function saveInternal(): Result
	{
		$entityChanged = $this->isChanged();
		if ($entityChanged && !$this->hasChangedFields())
		{
			$this->setField('MODIFIED_BY', CurrentUser::get()->getId());
		}

		$propertyCollectionChanged = $this->propertyCollection && $this->propertyCollection->isChanged();
		$imageCollectionChanged = $this->imageCollection && $this->imageCollection->isChanged();

		$result = parent::saveInternal();

		if ($result->isSuccess())
		{
			if ($entityChanged)
			{
				\CIBlock::clearIblockTagCache($this->getIblockId());
			}

			// ToDo reload if at least one file property changed?
			if ($propertyCollectionChanged)
			{
				// hack to re-initialize saved ids from database after files saving
				$this->unsetPropertyCollection();
			}

			if ($imageCollectionChanged)
			{
				// hack to re-initialize saved ids from database after files saving
				$this->unsetImageCollection();
			}
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
				return is_numeric($value) ? (int)$value : $value;
			},
			'DETAIL_PICTURE' => static function ($value) {
				return is_numeric($value) ? (int)$value : $value;
			},

			// ToDo make immutable
			'DETAIL_PAGE_URL' => MapTypeCaster::NOTHING,

			'QUANTITY' => MapTypeCaster::NULLABLE_FLOAT,
			'WEIGHT' => MapTypeCaster::NULLABLE_FLOAT,
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

			// TODO: change this horror
			'UF_PRODUCT_GROUP' => MapTypeCaster::NULLABLE_INT,
			'UF_PRODUCT_MAPPING' => MapTypeCaster::NULLABLE_MULTI_INT,
		];
	}
}