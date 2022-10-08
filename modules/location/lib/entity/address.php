<?php

namespace Bitrix\Location\Entity;

use Bitrix\Location\Entity\Address\AddressLink;
use Bitrix\Location\Entity\Address\AddressLinkCollection;
use Bitrix\Location\Entity\Address\Converter\ArrayConverter;
use Bitrix\Location\Entity\Address\Converter\StringConverter;
use Bitrix\Location\Entity\Address\Field;
use Bitrix\Location\Entity\Address\FieldCollection;
use Bitrix\Location\Service\AddressService;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ORM\Data\DeleteResult;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\Json;

/**
 * Class Address
 * @package Bitrix\Location\Entity
 */
final class Address
{
	/** @var int  */
	private $id = 0;
	/** @var string */
	private $languageId;
	/** @var string  */
	protected $latitude = '';
	/** @var string  */
	protected $longitude = '';
	/** @var Location  */
	private $location;
	/** @var FieldCollection */
	private $fieldCollection;
	/** @var AddressLinkCollection */
	private $linkCollection = null;

	/**
	 * Address constructor.
	 * @param string $languageId
	 */
	public function __construct(string $languageId)
	{
		$this->languageId = $languageId;
		$this->fieldCollection = new FieldCollection();
		$this->linkCollection = new AddressLinkCollection();
	}

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * @param int $id
	 * @return $this
	 */
	public function setId(int $id): Address
	{
		$this->id = $id;
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getLatitude(): string
	{
		return $this->latitude;
	}

	/**
	 * @param string $latitude
	 * @return Address
	 */
	public function setLatitude(string $latitude): Address
	{
		$this->latitude = $latitude;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getLongitude(): string
	{
		return $this->longitude;
	}

	/**
	 * @param string $longitude
	 * @return Address
	 */
	public function setLongitude(string $longitude): Address
	{
		$this->longitude = $longitude;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getLanguageId(): string
	{
		return $this->languageId;
	}

	/**
	 * @return FieldCollection
	 * @internal
	 */
	public function getFieldCollection(): FieldCollection
	{
		return $this->fieldCollection;
	}

	/**
	 * @param FieldCollection $fieldCollection
	 * @return Address
	 * @internal
	 */
	public function setFieldCollection(FieldCollection $fieldCollection): Address
	{
		$this->fieldCollection = $fieldCollection;

		return $this;
	}

	/**
	 * @return Location
	 */
	public function getLocation():? Location
	{
		return $this->location;
	}

	/**
	 * @param Location|null $location
	 * @return self
	 */
	public function setLocation(?Location $location): self
	{
		$this->location = $location;
		return $this;
	}

	/**
	 * Set address field value
	 * @param int $type Field type. See \Bitrix\Location\Entity\Address\FieldType
	 * @param string $value Field value
	 * @return $this
	 * @throws SystemException
	 */
	public function setFieldValue(int $type, string $value): self
	{
		if($field = $this->getFieldCollection()->getItemByType($type))
		{
			$field->setValue($value);
		}
		else
		{
			$this->fieldCollection->addItem(
				(new Field($type))
					->setValue($value)
			);
		}

		return $this;
	}

	/**
	 * Get all fields values from the address
	 *
	 * @return array
	 */
	public function getAllFieldsValues(): array
	{
		$result = [];

		foreach ($this->getFieldCollection() as $field)
		{
			$result[$field->getType()] = $field->getValue();
		}

		return $result;
	}

	/**
	 * Get address field value
	 *
	 * @param int $type Field type. See \Bitrix\Location\Entity\Address\FieldType
	 * @return string|null
	 */
	public function getFieldValue(int $type): ?string
	{
		$result = null;

		if($field = $this->getFieldCollection()->getItemByType($type))
		{
			$result = $field->getValue();
		}

		return $result;
	}

	/**
	 * Check if field exist
	 *
	 * @param int $type Field type. See \Bitrix\Location\Entity\Address\FieldType
	 * @return bool
	 */
	public function isFieldExist(int $type): bool
	{
		return (bool)$this->getFieldCollection()->getItemByType($type);
	}

	/**
	 * Load the Address
	 *
	 * @param int $id
	 * @return Address|bool|null
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 */
	public static function load(int $id)
	{
		return AddressService::getInstance()->findById($id);
	}

	/**
	 * Save the Address
	 *
	 * @return \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\Result|\Bitrix\Main\ORM\Data\UpdateResult
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 */
	public function save()
	{
		return AddressService::getInstance()->save($this);
	}

	/**
	 * Delete the address
	 *
	 * @return \Bitrix\Main\ORM\Data\DeleteResult
	 * @throws \Exception
	 */
	public function delete(): DeleteResult
	{
		return AddressService::getInstance()->delete($this->getId());
	}

	/**
	 * Convert the Address to JSON
	 *
	 * @return string Json
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function toJson(): string
	{
		return Json::encode(ArrayConverter::convertToArray($this));
	}

	/**
	 * Convert the Address to Array
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		return ArrayConverter::convertToArray($this);
	}

	/**
	 * Create the Address from JSON
	 *
	 * @param string $jsonData
	 * @return Address
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function fromJson(string $jsonData): Address
	{
		return ArrayConverter::convertFromArray(Json::decode($jsonData));
	}

	/**
	 * Create the Address from Array
	 *
	 * @param array $arrayData
	 * @return Address
	 */
	public static function fromArray(array $arrayData): Address
	{
		return ArrayConverter::convertFromArray($arrayData);
	}

	/**
	 * @param AddressLinkCollection $collection
	 */
	public function setLinks(AddressLinkCollection $collection): void
	{
		$this->linkCollection = $collection;
	}

	/**
	 * @return AddressLinkCollection
	 */
	public function getLinks(): AddressLinkCollection
	{
		return $this->linkCollection;
	}

	/**
	 * Removes all links
	 */
	public function clearLinks(): void
	{
		$this->linkCollection->clear();
	}

	/**
	 * Link entity to address
	 *
	 * @param string $entityId
	 * @param string $entityType
	 * @throws ArgumentNullException
	 */
	public function addLink(string $entityId, string $entityType): void
	{
		if($entityId === '')
		{
			throw new ArgumentNullException('entityId');
		}

		if($entityType === '')
		{
			throw new ArgumentNullException('entityType');
		}

		$this->linkCollection->addItem(
			new AddressLink($entityId, $entityType)
		);
	}

	/**
	 * Check if Address has links or not
	 *
	 * @return bool
	 */
	public function hasLinks(): bool
	{
		return $this->linkCollection->count() > 0;
	}

	/**
	 * Convert Address to String
	 *
	 * @param Format $format Address format
	 * @param string $strategyType See StringConverter::STRATEGY_*
	 * @param string $contentType See StringConverter::CONTENT_TYPE_*
	 * @return string
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public function toString(
		Format $format,
		string $strategyType = StringConverter::STRATEGY_TYPE_TEMPLATE,
		string $contentType = StringConverter::CONTENT_TYPE_HTML
	): string
	{
		return StringConverter::convertToString($this, $format, $strategyType, $contentType);
	}
}
