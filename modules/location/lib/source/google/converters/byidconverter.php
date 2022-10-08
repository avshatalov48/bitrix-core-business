<?php

namespace Bitrix\Location\Source\Google\Converters;

use Bitrix\Location\Entity\Address;
use Bitrix\Location\Entity\Address\FieldType;
use Bitrix\Location\Entity\Location;
use Bitrix\Location\Exception\RuntimeException;
use Bitrix\Location\Source\Google;

/**
 * Converts Place Details Results to Location
 * https://developers.google.com/places/web-service/details#PlaceDetailsResults
 * Class PlaceDetailsConverter
 * @package Bitrix\Location\Source\Google\Converters
 */
class ByIdConverter extends BaseConverter
{
	/**
	 * @param array $data Place Details Results
	 * @return Location|null
	 */
	public function convert(array $data)
	{
		if(isset($data['status']) && $data['status'] !== 'OK')
		{
			$errorMessage = $data['error_message'].' ('.$data['status'].')' ?? $data['status'];
			throw new RuntimeException($errorMessage, Google\ErrorCodes::CONVERTER_BYID_ERROR);
		}

		if(!isset($data['result']))
		{
			return null;
		}

		$data = $data['result'];

		if(isset($data['types']) && is_array($data['types']))
		{
			$type = $this->convertTypes($data['types'], Location\Type::class);
		}
		else
		{
			$type = Location\Type::UNKNOWN;
		}

		$result = (new Location())
			->setSourceCode(Google\Repository::getSourceCode())
			->setExternalId((string)$data['place_id'])
			->setName((string)$data['name'])
			->setLongitude((string)$data['geometry']['location']['lng'])
			->setLatitude((string)$data['geometry']['location']['lat'])
			->setType($type)
			->setLanguageId($this->languageId);

		if(is_array($data['address_components']))
		{
			if ($address = $this->createAddress($data['address_components']))
			{
				$address->setLatitude($result->getLatitude());
				$address->setLongitude($result->getLongitude());
				$result->setAddress($address);

				if($address->isFieldExist(FieldType::POSTAL_CODE))
				{
					$result->setFieldValue(
						FieldType::POSTAL_CODE,
						$address->getFieldValue(FieldType::POSTAL_CODE)
					);
				}

				if(!$address->isFieldExist(FieldType::ADDRESS_LINE_2) && $type === Location\Type::UNKNOWN)
				{
					$address->setFieldValue(FieldType::ADDRESS_LINE_2, (string)$data['name']);
				}
			}
		}

		return $result;
	}
}

