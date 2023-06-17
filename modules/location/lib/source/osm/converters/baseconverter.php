<?php

namespace Bitrix\Location\Source\Osm\Converters;

use Bitrix\Location\Entity\Address;
use Bitrix\Location\Entity\Address\Converter\StringConverter;
use Bitrix\Location\Entity\Address\Field;
use Bitrix\Location\Entity\Address\FieldType;
use Bitrix\Location\Entity\Format\TemplateType;
use Bitrix\Location\Entity\Location;
use Bitrix\Location\Service\FormatService;
use Bitrix\Location\Source\Osm\ExternalIdBuilder;
use Bitrix\Location\Source\Osm\Repository;

/**
 * Class BaseConverter
 * @package Bitrix\Location\Source\Osm\Converters
 * @internal
 *
 * @see https://wiki.openstreetmap.org/wiki/Tag:boundary%3Dadministrative
 * @see https://wiki.openstreetmap.org/wiki/Nominatim/Development_overview#Indexing.2Faddress_calculation
 * @see https://nominatim.org/release-docs/develop/develop/Ranking/#address-rank
 * @see https://wiki.openstreetmap.org/wiki/Map_Features#Place
 * @see https://nominatim.org/release-docs/develop/api/Faq/#1-the-address-of-my-search-results-contains-far-away-places-that-dont-belong-there
 */
abstract class BaseConverter
{
	/** @var int */
	protected const COUNTRY_ADMIN_LEVEL = 2;

	/** @var array */
	protected $details = [];

	/** @var array */
	protected $addressComponents = [];

	/**
	 * @param string $languageId
	 * @param array $details
	 * @return Location|null
	 */
	public function convert(string $languageId, array $details): ?Location
	{
		$this->details = $details;

		if (!$this->isDetailsValid())
		{
			return null;
		}

		$addressFieldCollection = $this->makeAddressFieldCollection($languageId);
		$locationTypeField = $this->getLocationTypeField($addressFieldCollection);

		if ($locationTypeField === null)
		{
			return null;
		}

		list($latitude, $longitude) = $this->getCoordinates();

		$address = (new Address($languageId))
			->setLatitude($latitude)
			->setLongitude($longitude)
			->setFieldCollection($addressFieldCollection);

		if ($addressLine1 = $this->createAddressLine1($address))
		{
			$address->setFieldValue(FieldType::ADDRESS_LINE_1, $addressLine1);
		}

		$externalId = ExternalIdBuilder::buildExternalId(
			$this->details['osm_type'],
			$this->details['osm_id']
		);

		if (!$externalId)
		{
			return null;
		}

		$location =
			(new Location())
				->setSourceCode(Repository::getSourceCode())
				->setExternalId($externalId)
				->setType($locationTypeField->getType())
				->setName($locationTypeField->getValue())
				->setLatitude($latitude)
				->setLongitude($longitude)
				->setLanguageId($languageId)
				->setAddress($address);

		if($address->isFieldExist(FieldType::POSTAL_CODE))
		{
			$location->setFieldValue(
				FieldType::POSTAL_CODE,
				$address->getFieldValue(FieldType::POSTAL_CODE)
			);
		}

		return $location;
	}

	/**
	 * @return bool
	 */
	private function isDetailsValid(): bool
	{
		if (!isset($this->details['osm_type']))
		{
			return false;
		}

		if (!isset($this->details['osm_id']))
		{
			return false;
		}

		if (!isset($this->details['address']) || !is_array($this->details['address']))
		{
			return false;
		}

		/**
		 * Remove non-address items
		 */
		$this->addressComponents = array_filter(
			$this->details['address'],
			static function (array $addressComponent)
			{
				if (!isset($addressComponent['isaddress']))
				{
					return false;
				}

				return (bool)$addressComponent['isaddress'];
			}
		);

		if (empty($this->details['address']))
		{
			return false;
		}

		if (!isset($this->details['country_code']))
		{
			return false;
		}

		if (!$this->getCountry())
		{
			return false;
		}

		if (!$this->getCoordinates())
		{
			return false;
		}

		return true;
	}

	/**
	 * @param string $languageId
	 * @return Address\FieldCollection
	 */
	private function makeAddressFieldCollection(string $languageId): Address\FieldCollection
	{
		$result = new Address\FieldCollection();

		$postalCode = $this->getPostalCode();
		if ($postalCode)
		{
			$result->addItem(
				(new Field(FieldType::POSTAL_CODE))->setValue($postalCode)
			);
		}

		$country = $this->getCountry();
		if ($country && isset($country['localname']))
		{
			$result->addItem(
				(new Field(FieldType::COUNTRY))->setValue($country['localname'])
			);
		}

		$adminLevel1 = $this->getAdminLevel1();
		if ($adminLevel1 && isset($adminLevel1['localname']))
		{
			$result->addItem(
				(new Field(FieldType::ADM_LEVEL_1))->setValue($adminLevel1['localname'])
			);
		}

		$adminLevel2 = $this->getAdminLevel2();
		if ($adminLevel2 && isset($adminLevel2['localname']))
		{
			$result->addItem(
				(new Field(FieldType::ADM_LEVEL_2))->setValue($adminLevel2['localname'])
			);
		}

		$adminLevel3 = $this->getAdminLevel3();
		if ($adminLevel3 && isset($adminLevel3['localname']))
		{
			$result->addItem(
				(new Field(FieldType::ADM_LEVEL_3))->setValue($adminLevel3['localname'])
			);
		}

		$adminLevel4 = $this->getAdminLevel4();
		if ($adminLevel4 && isset($adminLevel4['localname']))
		{
			$result->addItem(
				(new Field(FieldType::ADM_LEVEL_4))->setValue($adminLevel4['localname'])
			);
		}

		$locality = $this->getLocality();
		if ($locality && isset($locality['localname']))
		{
			$result->addItem(
				(new Field(FieldType::LOCALITY))->setValue($locality['localname'])
			);
		}

		$subLocality = $this->getSubLocality();
		if ($subLocality && isset($subLocality['localname']))
		{
			$result->addItem(
				(new Field(FieldType::SUB_LOCALITY))->setValue($subLocality['localname'])
			);
		}

		$subLocalityLevel1 = $this->getSubLocalityLevel1();
		if ($subLocalityLevel1 && isset($subLocalityLevel1['localname']))
		{
			$result->addItem(
				(new Field(FieldType::SUB_LOCALITY_LEVEL_1))->setValue($subLocalityLevel1['localname'])
			);
		}

		$subLocalityLevel2 = $this->getSubLocalityLevel2();
		if ($subLocalityLevel2 && isset($subLocalityLevel2['localname']))
		{
			$result->addItem(
				(new Field(FieldType::SUB_LOCALITY_LEVEL_2))->setValue($subLocalityLevel2['localname'])
			);
		}

		$street = $this->getStreet();
		if ($street && isset($street['localname']))
		{
			$result->addItem(
				(new Field(FieldType::STREET))->setValue($street['localname'])
			);
		}

		$house = $this->getHouse();
		if ($house && isset($house['localname']))
		{
			$result->addItem(
				(new Field(FieldType::BUILDING))->setValue($house['localname'])
			);
		}

		$addressLine2 = $this->getAddressLine2();
		if ($addressLine2 && isset($addressLine2['localname']))
		{
			$result->addItem(
				(new Field(FieldType::ADDRESS_LINE_2))->setValue($addressLine2['localname'])
			);
		}

		return $result;
	}

	/**
	 * @param Address $address
	 * @return string|null
	 */
	private function createAddressLine1(Address $address): ?string
	{
		$format = FormatService::getInstance()->findDefault($address->getLanguageId());

		return StringConverter::convertToStringTemplate(
			$address,
			$format->getTemplate(TemplateType::ADDRESS_LINE_1),
			StringConverter::STRATEGY_TYPE_TEMPLATE,
			StringConverter::CONTENT_TYPE_TEXT
		);
	}

	/**
	 * @return array|null
	 */
	private function getCountry(): ?array
	{
		/**
		 * Case #1 (country itself)
		 * @see https://github.com/osm-search/Nominatim/issues/1806
		 * @see https://nominatim.openstreetmap.org/ui/details.html?osmtype=R&osmid=60189
		 * @see https://nominatim.openstreetmap.org/details?osmtype=R&osmid=60189&addressdetails=1&linkedplaces=0&format=json
		 *
		 */
		foreach ($this->addressComponents as $addressComponent)
		{
			if ($addressComponent['class'] === 'boundary'
				&& $addressComponent['type'] === 'administrative'
				&& $addressComponent['admin_level'] === static::COUNTRY_ADMIN_LEVEL
			)
			{
				return $addressComponent;
			}
		}

		/**
		 * Case #2 (item within a country)
		 * @see https://github.com/osm-search/Nominatim/issues/1806
		 * @see https://nominatim.openstreetmap.org/ui/details.html?osmtype=R&osmid=1674442
		 * @see https://nominatim.openstreetmap.org/details?osmtype=R&osmid=1674442&addressdetails=1&linkedplaces=0&format=json
		 *
		 */
		foreach ($this->addressComponents as $addressComponent)
		{
			if ($addressComponent['class'] === 'place' && $addressComponent['type'] === 'country')
			{
				return $addressComponent;
			}
		}

		return null;
	}

	/**
	 * @return array|null
	 */
	abstract protected function getAdminLevel1(): ?array;

	/**
	 * @return array|null
	 */
	protected function getAdminLevel2(): ?array
	{
		return null;
	}

	/**
	 * @return array|null
	 */
	protected function getAdminLevel3(): ?array
	{
		return null;
	}

	/**
	 * @return array|null
	 */
	protected function getAdminLevel4(): ?array
	{
		return null;
	}

	/**
	 * @return array|null
	 */
	protected function getLocality(): ?array
	{
		/**
		 * Itself
		 */
		$settlementTypePriorityList = $this->getSettlementTypes();
		foreach ($this->addressComponents as $addressComponent)
		{
			$componentPlaceType = $this->getAddressComponentPlaceType($addressComponent);

			$isItself = (
				$this->details['osm_type'] === $addressComponent['osm_type']
				&& $this->details['osm_id'] === $addressComponent['osm_id']
			);

			if ($isItself && in_array($componentPlaceType, $settlementTypePriorityList, true))
			{
				return $addressComponent;
			}
		}

		$addressComponent = $this->getLocalityConcrete();
		if ($addressComponent)
		{
			return $addressComponent;
		}

		if ($this->isCityState())
		{
			return $this->getAdminLevel1();
		}

		return null;
	}

	/**
	 * @return bool
	 */
	protected function isCityState(): bool
	{
		$adminLevel1 = $this->getAdminLevel1();

		return (
			$adminLevel1['osm_type'] === 'R'
			&& in_array($adminLevel1['osm_id'], $this->getCityStateRelationIds(), true)
		);
	}

	/**
	 * @return array|null
	 */
	protected function getLocalityConcrete(): ?array
	{
		$addressComponent = $this->getLocalityByTypes(['R', 'W']);
		if ($addressComponent)
		{
			return $addressComponent;
		}

		$addressComponent = $this->getLocalityByTypes(['N']);
		if ($addressComponent)
		{
			return $addressComponent;
		}

		return null;
	}

	/**
	 * @return array
	 */
	protected function getCityStateRelationIds(): array
	{
		return [];
	}

	/**
	 * @return array|null
	 */
	protected function getSubLocality(): ?array
	{
		return null;
	}

	/**
	 * @return array|null
	 */
	protected function getSubLocalityLevel1(): ?array
	{
		return null;
	}

	/**
	 * @return array|null
	 */
	protected function getSubLocalityLevel2(): ?array
	{
		return null;
	}

	/**
	 * @return array|null
	 *
	 * @see https://wiki.openstreetmap.org/wiki/Nominatim/Development_overview#Indexing.2Faddress_calculation
	 * @see https://nominatim.org/release-docs/develop/develop/Ranking/#address-rank
	 */
	protected function getStreet(): ?array
	{
		foreach ($this->addressComponents as $addressComponent)
		{
			if (in_array($addressComponent['rank_address'], [26, 27], true))
			{
				return $addressComponent;
			}
		}

		if (!empty($this->details['addresstags']['street']))
		{
			return [
				'localname' => $this->details['addresstags']['street'],
			];
		}

		return null;
	}

	/**
	 * @return array|null
	 *
	 * @see https://wiki.openstreetmap.org/wiki/Nominatim/Development_overview#Indexing.2Faddress_calculation
	 * @see https://nominatim.org/release-docs/develop/develop/Ranking/#address-rank
	 */
	protected function getHouse(): ?array
	{
		foreach ($this->addressComponents as $addressComponent)
		{
			if ($addressComponent['rank_address'] == 28)
			{
				return $addressComponent;
			}
		}

		if (!empty($this->details['addresstags']['housenumber']))
		{
			return [
				'localname' => $this->details['addresstags']['housenumber'],
			];
		}

		return null;
	}

	/**
	 * @return array|null
	 *
	 * @see https://wiki.openstreetmap.org/wiki/Nominatim/Development_overview#Indexing.2Faddress_calculation
	 * @see https://nominatim.org/release-docs/develop/develop/Ranking/#address-rank
	 */
	protected function getAddressLine2(): ?array
	{
		foreach ($this->addressComponents as $addressComponent)
		{
			if ($addressComponent['rank_address'] >= 29)
			{
				return $addressComponent;
			}
		}

		return null;
	}

	/**
	 * @return string|null
	 */
	private function getPostalCode(): ?string
	{
		return $this->details['calculated_postcode'] ?? null;
	}

	/**
	 * @param Address\FieldCollection $addressFieldCollection
	 * @return Field|null
	 */
	private function getLocationTypeField(Address\FieldCollection $addressFieldCollection): ?Field
	{
		/** @var Field[] $items */
		$items = array_reverse($addressFieldCollection->getSortedItems());

		foreach ($items as $item)
		{
			if (!Location\Type::isValueExist($item->getType()))
			{
				continue;
			}

			return $item;
		}

		return null;
	}

	/**
	 * @return float[]|null
	 */
	private function getCoordinates(): ?array
	{
		if (!isset($this->details['centroid'])
			|| !isset($this->details['centroid']['type'])
			|| $this->details['centroid']['type'] !== 'Point'
			|| !isset($this->details['centroid']['coordinates'])
			|| !is_array($this->details['centroid']['coordinates'])
			|| count($this->details['centroid']['coordinates']) != 2
		)
		{
			return null;
		}

		return [
			(float)$this->details['centroid']['coordinates'][1],
			(float)$this->details['centroid']['coordinates'][0],
		];
	}

	/**
	 * @param $addressComponent
	 * @return bool
	 */
	protected function isAdministrativeBoundary($addressComponent): bool
	{
		return (
			$addressComponent['class'] === 'boundary'
			&& $addressComponent['type'] === 'administrative'
		);
	}

	/**
	 * @param int $level
	 * @return array|null
	 */
	protected function getBoundaryAdministrativeByLevel(int $level): ?array
	{
		foreach ($this->addressComponents as $addressComponent)
		{
			if ($this->isAdministrativeBoundary($addressComponent)
				&& $addressComponent['admin_level'] == $level
			)
			{
				return $addressComponent;
			}
		}

		return null;
	}

	/**
	 * Returns populated settlement types sorted by its priority
	 * @return array
	 *
	 * @see https://wiki.openstreetmap.org/wiki/Map_Features#Place
	 */
	protected function getSettlementTypes(): array
	{
		return [
			'city',
			'town',
			'village',
			'hamlet',
			'isolated_dwelling',
			'farm',
			'allotments',
		];
	}

	/**
	 * @param array $addressComponent
	 * @return mixed|null
	 */
	protected function getAddressComponentPlaceType(array $addressComponent)
	{
		if (isset($addressComponent['class']) && $addressComponent['class'] === 'place')
		{
			return $addressComponent['type'];
		}

		if (isset($addressComponent['place_type']))
		{
			return $addressComponent['place_type'];
		}

		return null;
	}

	/**
	 * @param array $types
	 * @return array|null
	 */
	protected function getLocalityByTypes(array $types): ?array
	{
		$settlementTypePriorityList = $this->getSettlementTypes();

		foreach ($settlementTypePriorityList as $settlementType)
		{
			foreach ($this->addressComponents as $addressComponent)
			{
				if (!in_array($addressComponent['osm_type'], $types, true))
				{
					continue;
				}

				$componentPlaceType = $this->getAddressComponentPlaceType($addressComponent);
				if (!$componentPlaceType)
				{
					continue;
				}

				if ($componentPlaceType === $settlementType)
				{
					return $addressComponent;
				}
			}
		}

		return null;
	}
}
