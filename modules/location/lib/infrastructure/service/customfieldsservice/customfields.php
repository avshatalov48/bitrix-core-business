<?php

namespace Bitrix\Location\Infrastructure\Service\CustomFieldsService;

use Bitrix\Location\Entity\Location;
use Bitrix\Location\Infrastructure\Service\CurrentRegionFinderService;
use Bitrix\Location\Entity\Location\Type;
use Bitrix\Location\Entity;
use Bitrix\Location\Entity\Address;

abstract class CustomFields
{
	/** @var string */
	protected $currentRegion;

	public function __construct()
	{
		$this->currentRegion = CurrentRegionFinderService::getInstance()->getRegion();
	}

	/**
	 * @param Location $location
	 */
	public function adjustLocation(Location $location): void
	{
		$info = $this->getInfo();
		if (!$info)
		{
			return;
		}

		/**
		 * Location
		 */
		/** @var Location\FieldCollection $locationFieldCollection */
		$locationFieldCollection = $location->getFieldCollection();

		if (isset($info['country']))
		{
			$this->adjustLocationField($locationFieldCollection, Type::COUNTRY, $info['country']);
		}
		if (isset($info['state']))
		{
			$this->adjustLocationField($locationFieldCollection, Type::ADM_LEVEL_1, $info['state']);
		}

		/**
		 * Address
		 */
		$address = $location->getAddress();
		if ($address)
		{
			/** @var Address\FieldCollection $addressFieldCollection */
			$addressFieldCollection = $address->getFieldCollection();

			if (isset($info['country']))
			{
				$this->adjustAddressField(
					$addressFieldCollection,
					Entity\Address\FieldType::COUNTRY,
					$info['country']
				);
			}

			if (isset($info['state']))
			{
				$this->adjustAddressField(
					$addressFieldCollection,
					Entity\Address\FieldType::ADM_LEVEL_1,
					$info['state']
				);
			}

			/** @var Entity\Address\Field $item */
			$item = $addressFieldCollection->getItemByType(Entity\Address\FieldType::POSTAL_CODE);
			if ($item)
			{
				$item->setValue('');
			}
		}
	}

	/**
	 * @param array $item
	 */
	public function adjustAutocompleteItem(array &$item): void
	{
		$info = $this->getInfo();
		if (!$info)
		{
			return;
		}

		unset($item['countrycode']);
		unset($item['postcode']);

		if (isset($info['country']))
		{
			$item['country'] = $info['country'];
		}

		if (isset($info['state']))
		{
			$item['state'] = $info['state'];
		}
	}

	/** @return array [
	 *     'country' => 'Russia',
	 *     'state' => 'Republic of Crimea',
	 * ] */
	abstract protected function getInfo(): array;

	/**
	 * @param Location\FieldCollection $fieldCollection
	 * @param int $type
	 * @param $value
	 */
	private function adjustLocationField(Entity\Location\FieldCollection $fieldCollection, int $type, $value)
	{
		$item = $fieldCollection->getItemByType($type);
		if ($item)
		{
			$item->setValue($value);
		}
		else
		{
			$fieldCollection->addItem(new Entity\Location\Field($type, $value));
		}
	}

	/**
	 * @param Address\FieldCollection $fieldCollection
	 * @param int $type
	 * @param $value
	 */
	private function adjustAddressField(Entity\Address\FieldCollection $fieldCollection, int $type, $value)
	{
		$item = $fieldCollection->getItemByType($type);
		if ($item)
		{
			$item->setValue($value);
		}
		else
		{
			$fieldCollection->addItem(new Entity\Address\Field($type, $value));
		}
	}
}
