<?php

namespace Bitrix\Catalog\v2\Property;

use Bitrix\Catalog\v2\BaseEntity;
use Bitrix\Catalog\v2\Fields\FieldStorage;
use Bitrix\Catalog\v2\PropertyValue\HasPropertyValueCollection;
use Bitrix\Catalog\v2\PropertyValue\PropertyValueCollection;
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
	/** @var \Bitrix\Catalog\v2\Fields\FieldStorage */
	protected $settings;
	/** @var \Bitrix\Catalog\v2\PropertyValue\PropertyValueCollection */
	protected $propertyValueCollection;

	public function __construct(PropertyRepositoryContract $productRepository)
	{
		parent::__construct($productRepository);
		$this->settings = new FieldStorage();
	}

	public function getPropertyValueCollection(): PropertyValueCollection
	{
		return $this->propertyValueCollection;
	}

	public function setPropertyValueCollection(PropertyValueCollection $propertyValueCollection): void
	{
		$this->propertyValueCollection = $propertyValueCollection;
	}

	/**
	 * @param array $settings
	 * @return \Bitrix\Catalog\v2\Property\Property
	 */
	public function setSettings(array $settings): Property
	{
		$this->settings->initFields($settings);

		return $this;
	}

	public function getSettings(): array
	{
		return $this->settings->toArray();
	}

	public function getSetting(string $name)
	{
		return $this->settings->getField($name);
	}

	public function getId()
	{
		return $this->getSetting('ID');
	}

	public function setId(int $id): BaseEntity
	{
		throw new NotSupportedException('Property ID can\'t be modified.');
	}

	public function getIndex()
	{
		return $this->getSetting('CODE') ?: $this->getSetting('ID');
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
				? unserialize($defaultValue)
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