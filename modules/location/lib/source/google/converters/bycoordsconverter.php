<?php

namespace Bitrix\Location\Source\Google\Converters;

use Bitrix\Location\Entity\Address\FieldType;
use Bitrix\Location\Entity\Location;
use Bitrix\Location\Entity\Generic\Collection;
use Bitrix\Location\Exception\RuntimeException;
use Bitrix\Location\Source\Google;

/**
 * Convert Reverse Geocoding result to LocationCollection
 * Class ReverseGeocodingConverter
 * @package Bitrix\Location\Source\Google\Converters
 */
final class ByCoordsConverter extends BaseConverter
{
	/**
	 * @param mixed $data
	 * @return Collection|null
	 */
	public function convert(array $data)
	{
		if (isset($data['status']) && $data['status'] !== 'OK')
		{
			$errorMessage = $data['error_message'] . ' ('.$data['status'] . ')' ?? $data['status'];

			throw new RuntimeException($errorMessage, Google\ErrorCodes::CONVERTER_BYCOORDS_ERROR);
		}

		if (!isset($data['results']) || !is_array($data['results']))
		{
			return null;
		}

		$result = new Collection;

		foreach ($data['results'] as $item)
		{
			if (isset($item['types']))
			{
				$type = $this->convertTypes($item['types'], Location\Type::class);
			}
			else
			{
				$type = FieldType::UNKNOWN;
			}

			$location = (new Location())
				->setSourceCode(Google\Repository::getSourceCode())
				->setExternalId($item['place_id'])
				->setLongitude($item['geometry']['location']['lng'])
				->setLatitude($item['geometry']['location']['lat'])
				->setType($type)
				->setLanguageId($this->languageId);


			if (is_array($item['address_components']))
			{
				if ($address = $this->createAddress($item['address_components']))
				{
					$address->setLatitude($location->getLatitude());
					$address->setLongitude($location->getLongitude());
					$location->setAddress($address);

					if ($address->isFieldExist(FieldType::POSTAL_CODE))
					{
						$location->setFieldValue(
							Location\FieldType::POSTAL_CODE,
							$address->getFieldValue(FieldType::POSTAL_CODE)
						);
					}
				}

				if ($address->isFieldExist($type))
				{
					$location->setName($address->getFieldValue($type));
				}
			}

			if ($location->getName() === '')
			{
				$location->setName($item['formatted_address']);
			}

			$result->addItem($location);
		}

		return $result;
	}
}
