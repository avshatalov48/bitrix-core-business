<?php

namespace Bitrix\Location\Entity;

use Bitrix\Location\Entity\Location\Converter\ArrayConverter;
use Bitrix\Location\Entity\Location\Field;
use Bitrix\Location\Entity\Location\FieldCollection;
use Bitrix\Location\Entity\Location\Type;
use Bitrix\Location\Entity\Location\Parents;
use Bitrix\Location\Service;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Web\Json;

/**
 * Class Location
 *
 * @package Bitrix\Location\Entity
 */
final class Location implements \Serializable
{
	/** @var int  */
	protected $id = 0;
	/** @var string  */
	protected $code = '';
	/** @var string  */
	protected $externalId = '';
	/** @var string  */
	protected $sourceCode = '';
	/** @var int  */
	protected $type = Type::UNKNOWN;
	/** @var string  */
	protected $name = '';
	/** @var string  */
	protected $languageId = '';
	/** @var string  */
	protected $latitude = '';
	/** @var string  */
	protected $longitude = '';

	/** @var FieldCollection */
	private $fieldCollection;

	/** @var Address Location could contain the Address*/
	protected $address = null;

	/** @var Parents Location parents */
	protected $parents = null;

	/**
	 * Location constructor.
	 */
	public function __construct()
	{
		$this->fieldCollection = new FieldCollection();
	}

	/**
	 * Check if this Location is parent of the other Location
	 *
	 * @param Location $childCandidate
	 * @return bool
	 */
	public function isParentOf(Location $childCandidate): bool
	{
		$candidateParents = $childCandidate->getParents();

		if(!$candidateParents)
		{
			return false;
		}

		return $candidateParents->isContain($this);
	}

	/**
	 * Check if this Location is parent of the other Location
	 *
	 * @param Location $parentCandidate
	 * @return bool
	 */
	public function isChildOf(Location $parentCandidate): bool
	{
		$parents = $this->getParents();

		if(!$parents)
		{
			return false;
		}

		return $parents->isContain($parentCandidate);
	}

	/**
	 * Check if this Location and the other Location are the same
	 *
	 * @param Location $location
	 * @return bool
	 */
	public function isEqualTo(Location $location): bool
	{
		if($this->getId() > 0 && $location->getId() > 0)
		{
			return $this->getId() === $location->getId();
		}

		if($this->getExternalId() !== '' || $location->getExternalId() !== ''
			|| $this->getSourceCode() !== '' || $location->getSourceCode() !== '')
		{

			if($this->getExternalId() === $location->getExternalId()
				&& $this->getSourceCode() === $location->getSourceCode())
			{
				return true;
			}

			if($this->getExternalId() !== $location->getExternalId())
			{
				return false;
			}

			if($this->getSourceCode() !== $location->getSourceCode())
			{
				return false;
			}
		}

		if($this->getType() !== $location->getType())
		{
			return false;
		}

		if($this->getLanguageId() !== $location->getLanguageId())
		{
			return false;
		}

		if($this->getName() !== $location->getName())
		{
			return false;
		}

		if($this->getLatitude() !== $location->getLatitude())
		{
			return false;
		}

		if($this->getLongitude() !== $location->getLongitude())
		{
			return false;
		}

		$thisParents = $this->getParents();
		$otherParents = $location->getParents();

		if($thisParents && $otherParents && !$thisParents->isEqualTo($otherParents))
		{
			return false;
		}

		return true;
	}

	/**
	 * @return Parents|bool
	 * @internal
	 */
	public function getParents()
	{
		$this->loadParents();
		return $this->parents;
	}

	/**
	 * Load Location parents
	 * @internal
	 */
	public function loadParents(): void
	{
		if($this->parents === null)
		{
			$this->parents = Service\LocationService::getInstance()->findParents($this, $this->languageId);
		}
	}

	/**
	 * @param int $level
	 * @return mixed
	 * @internal
	 */
	public function getParentByLevel(int $level): ?Location
	{
		$parents = $this->getParents();
		return (bool)$parents ? $parents[$level] : null;
	}

	/**
	 * @param int $type
	 * @return Location|null
	 * @internal
	 */
	public function getParentByType(int $type): ?Location
	{
		$parents = $this->getParents();
		return (bool)$parents ? $parents->getItemByType($type) : null;
	}

	/**
	 * @param Parents $parents
	 * @return $this
	 * @internal
	 */
	public function setParents(Parents $parents): self
	{
		$this->parents = $parents;
		$this->parents->setDescendant($this);
		return $this;
	}

	/**
	 * @return string
	 */
	public function getExternalId(): string
	{
		return $this->externalId;
	}

	/**
	 * @param string $externalId
	 * @return $this
	 */
	public function setExternalId(string $externalId): self
	{
		$this->externalId = $externalId;
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
	 * @param string $languageId
	 * @return $this
	 */
	public function setLanguageId(string $languageId): self
	{
		$this->languageId = $languageId;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Return Location name imploded with parents names
	 *
	 * @return string
	 * @todo: customize delimiter and the names order
	 */
	public function getNameWithParents(): string
	{
		$result = $this->getName();

		if($parents = $this->getParents())
		{
			foreach ($parents as $parent)
			{
				$result = $parent->getName().', '.$this->getName();
			}
		}

		return $result;
	}

	/**
	 * @param string $name
	 * @return $this
	 */
	public function setName(string $name): self
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * @return Address
	 */
	public function getAddress(): ?Address
	{
		if($this->address === null)
		{
			$this->address = Location\Converter\AddressConverter::convertToAddress($this);
		}

		return $this->address;
	}

	/**
	 * @param Address $address
	 * @return $this
	 */
	public function setAddress(Address $address): self
	{
		$this->address = $address;
		return $this;
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
	public function setId(int $id): self
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getCode(): string
	{
		return $this->code;
	}

	/**
	 * @param string $code
	 * @return $this
	 */
	public function setCode(string $code): self
	{
		$this->code = $code;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSourceCode(): string
	{
		return $this->sourceCode;
	}

	/**
	 * @param string $sourceCode
	 * @return $this
	 */
	public function setSourceCode($sourceCode): self
	{
		$this->sourceCode = $sourceCode;
		return $this;
	}

	/**
	 * @return int
	 * @see \Bitrix\Location\Entity\Location\Type
	 */
	public function getType(): int
	{
		return $this->type;
	}

	/**
	 * @param int $type
	 * @return $this
	 * @see \Bitrix\Location\Entity\Location\Types
	 * @throws ArgumentOutOfRangeException
	 */
	public function setType(int $type): self
	{
		if(!Type::isValueExist($type))
		{
			throw new ArgumentOutOfRangeException('Wrong location type');
		}

		$this->type = $type;
		return $this;
	}

	/**
	 * Copy data from other Location
	 *
	 * @param Location $otherLocation
	 * @internal
	 */
	public function copyDataFrom(Location $otherLocation): void
	{
		$this->setId($otherLocation->getId())
			->setCode($otherLocation->getCode())
			->setExternalId($otherLocation->getExternalId())
			->setSourceCode($otherLocation->getSourceCode())
			->setType($otherLocation->getType())
			->setName($otherLocation->getName())
			->setLanguageId($otherLocation->getLanguageId())
			->setLatitude($otherLocation->getLatitude())
			->setLongitude($otherLocation->getLongitude());

			if($address = $otherLocation->getAddress())
			{
				$this->setAddress($address);
			}
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
	 * @return self
	 */
	public function setLatitude(string $latitude): self
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
	 * @return self
	 */
	public function setLongitude(string $longitude): self
	{
		$this->longitude = $longitude;
		return $this;
	}

	/**
	 * Save Location
	 *
	 * @return \Bitrix\Main\Result
	 */
	public function save(): \Bitrix\Main\Result
	{
		return Service\LocationService::getInstance()->save($this);
	}

	/**
	 * Load the Location
	 *
	 * @param int $id
	 * @param string $languageId
	 * @return Location|bool|null
	 */
	public static function load(int $id, string $languageId = LANGUAGE_ID)
	{
		return Service\LocationService::getInstance()->findById($id, $languageId);
	}

	/**
	 * Delete the Location
	 *
	 * @return \Bitrix\Main\Result
	 */
	public function delete(): \Bitrix\Main\Result
	{
		return Service\LocationService::getInstance()->delete($this);
	}

	/**
	 * Serialize the Location
	 *
	 * @return string
	 */
	public function serialize()
	{
		return serialize(
			\Bitrix\Location\Entity\Location\Converter\ArrayConverter::convertToArray($this)
		);
	}

	public function __serialize(): array
	{
		return \Bitrix\Location\Entity\Location\Converter\ArrayConverter::convertToArray($this);
	}

	/**
	 * Unserialize the Location
	 *
	 * @param string $serialized
	 */
	public function unserialize($serialized)
	{
		$this->copyDataFrom(
			Location::fromArray(
				unserialize($serialized, ['allowed_classes' => false])
			)
		);
	}

	public function __unserialize(array $data): void
	{
		$this->copyDataFrom(
			Location::fromArray(
				$data
			)
		);
	}

	/**
	 * Convert the Location to an Array
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		return ArrayConverter::convertToArray($this);
	}

	public function toJson(): string
	{
		return Json::encode($this->toArray());
	}

	/**
	 * Create Location from the Array
	 *
	 * @param array $location
	 * @return Location
	 */
	public static function fromArray(array $location): Location
	{
		return ArrayConverter::convertFromArray($location);
	}

	/**
	 * Set the Location field value
	 *
	 * @param int $type Location type. See \Bitrix\Location\Entity\Location\Type
	 * @param string $value
	 * @return $this
	 * @throws \Bitrix\Main\SystemException
	 */
	public function setFieldValue(int $type, string $value): self
	{
		if($field = $this->fieldCollection->getItemByType($type))
		{
			$field->setValue($value);
		}
		else
		{
			$this->fieldCollection->addItem(
				new Field($type, $value)
			);
		}

		return $this;
	}

	/**
	 * Return all fields values
	 *
	 * @return array
	 */
	public function getAllFieldsValues(): array
	{
		$result = [];

		foreach ($this->fieldCollection as $field)
		{
			$result[$field->getType()] = $field->getValue();
		}

		return $result;
	}

	/**
	 * @param int $type Location type. See \Bitrix\Location\Entity\Location\Type
	 * @return string|null
	 */
	public function getFieldValue(int $type): ?string
	{
		$result = null;

		if($field = $this->fieldCollection->getItemByType($type))
		{
			$result = $field->getValue();
		}

		return $result;
	}

	/**
	 * Check if the Location field exists
	 *
	 * @param int $type Location type. See \Bitrix\Location\Entity\Location\Type
	 * @return bool
	 */
	public function isFieldExist(int $type): bool
	{
		return (bool)$this->fieldCollection->getItemByType($type);
	}

	/**
	 * @return FieldCollection
	 * @internal
	 */
	public function getFieldCollection(): FieldCollection
	{
		return $this->fieldCollection;
	}
}
