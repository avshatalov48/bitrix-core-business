<?php

namespace Bitrix\Catalog\v2\Property;

use Bitrix\Catalog\v2\BaseEntity;
use Bitrix\Catalog\v2\Fields\FieldStorage;
use Bitrix\Catalog\v2\HasSettingsTrait;
use Bitrix\Catalog\v2\PropertyValue\HasPropertyValueCollection;
use Bitrix\Catalog\v2\PropertyValue\PropertyValueCollection;
use Bitrix\Catalog\v2\PropertyFeature\PropertyFeatureRepositoryContract;
use Bitrix\Catalog\v2\PropertyFeature\PropertyFeatureCollection;
use Bitrix\Catalog\v2\PropertyFeature\PropertyFeature;
use Bitrix\Iblock;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\Result;

/**
 * Class Property
 *
 * @package Bitrix\Catalog\v2\Property
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class Property extends BaseEntity implements HasPropertyValueCollection
{
	use HasSettingsTrait;

	/** @var \Bitrix\Catalog\v2\PropertyValue\PropertyValueCollection */
	protected $propertyValueCollection;
	/** @var PropertyFeatureRepositoryContract */
	protected $propertyFeatureRepository;
	/** @var PropertyFeatureCollection */
	protected $propertyFeatureCollection;

	public function __construct(
		PropertyRepositoryContract $productRepository,
		PropertyFeatureRepositoryContract $propertyFeatureRepository
	)
	{
		parent::__construct($productRepository);
		$this->settings = new FieldStorage();
		$this->propertyFeatureRepository = $propertyFeatureRepository;
	}

	public function getPropertyValueCollection(): PropertyValueCollection
	{
		return $this->propertyValueCollection;
	}

	public function setPropertyValueCollection(PropertyValueCollection $propertyValueCollection): self
	{
		$propertyValueCollection->setParent($this);

		$this->propertyValueCollection = $propertyValueCollection;

		return $this;
	}

	/**
	 * @return PropertyFeatureCollection|PropertyFeature[]
	 */
	public function getPropertyFeatureCollection(): PropertyFeatureCollection
	{
		if ($this->propertyFeatureCollection === null)
		{
			// ToDo make lazy load like sku collection with iterator callback?
			$this->setPropertyFeatureCollection($this->loadPropertyFeatureCollection());
		}

		return $this->propertyFeatureCollection;
	}

	/**
	 * @return PropertyFeatureCollection|PropertyFeature[]
	 */
	protected function loadPropertyFeatureCollection(): PropertyFeatureCollection
	{
		return $this->propertyFeatureRepository->getCollectionByParent($this);
	}

	public function setPropertyFeatureCollection(PropertyFeatureCollection $propertyFeatureCollection): self
	{
		$propertyFeatureCollection->setParent($this);

		$this->propertyFeatureCollection = $propertyFeatureCollection;

		return $this;
	}

	public function getId(): ?int
	{
		return (int)$this->getSetting('ID') ?: null;
	}

	public function setId(int $id): BaseEntity
	{
		throw new NotSupportedException('Property ID can\'t be modified.');
	}

	public function getCode(): string
	{
		return (string)$this->getSetting('CODE');
	}

	public function getName()
	{
		return $this->getSetting('NAME');
	}

	public function getDefaultValue()
	{
		$defaultValue = $this->getSetting('DEFAULT_VALUE');

		if (
			!empty($defaultValue)
			&& $this->getPropertyType() === 'S'
			&& $this->getUserType() === 'HTML'
		)
		{
			$defaultValue = CheckSerializedData($defaultValue)
				? unserialize($defaultValue, ['allowed_classes' => false])
				: null;
		}

		return $defaultValue;
	}

	public function getPropertyType()
	{
		return $this->getSetting('PROPERTY_TYPE');
	}

	public function getUserType()
	{
		return $this->getSetting('USER_TYPE');
	}

	public function getListType()
	{
		return $this->getSetting('LIST_TYPE');
	}

	public function isRequired(): bool
	{
		return $this->getSetting('IS_REQUIRED') === 'Y';
	}

	public function isMultiple(): bool
	{
		return $this->getSetting('MULTIPLE') === 'Y';
	}

	public function isActive(): bool
	{
		return $this->getSetting('ACTIVE') === 'Y';
	}

	public function isPublic(): bool
	{
		$featureCollection = $this->getPropertyFeatureCollection();
		$detailFeature = $featureCollection->findByFeatureId(Iblock\Model\PropertyFeature::FEATURE_ID_DETAIL_PAGE_SHOW);
		if (!$detailFeature || !$detailFeature->isEnabled())
		{
			return false;
		}

		$listFeature = $featureCollection->findByFeatureId(Iblock\Model\PropertyFeature::FEATURE_ID_LIST_PAGE_SHOW);
		if (!$listFeature || !$listFeature->isEnabled())
		{
			return false;
		}

		return true;
	}

	public function isFileType(): bool
	{
		return (
			$this->getPropertyType() === 'F'
			|| $this->getUserType() === 'FileMan'
			|| $this->getUserType() === 'DiskFile'
		);
	}

	public function saveInternal(): Result
	{
		return new Result();
	}

	public function deleteInternal(): Result
	{
		return new Result();
	}

	// ToDo rethink PropertyValueCollection saving and clearing
	public function clearChangedFields(): BaseEntity
	{
		parent::clearChangedFields();

		$this->getPropertyValueCollection()->clearChanged();

		return $this;
	}
}