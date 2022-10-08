<?php

namespace Bitrix\Location\Source\Google\Converters;

use Bitrix\Location\Entity\Address;
use Bitrix\Location\Entity\Location;
use Bitrix\Location\Entity\Generic\Collection;
use Bitrix\Location\Exception\RuntimeException;
use Bitrix\Location\Source\Google;

/**
 * Convert Reverse Geocoding result to LocationCollection
 * Class ReverseGeocodingConverter
 * @package Bitrix\Location\Source\Google\Converters
 */
final class ByQueryConverter extends BaseConverter
{
	/**
	 * @param mixed $data
	 * @return Collection|null
	 */
	public function convert(array $data)
	{
		if(isset($data['status']) && $data['status'] != 'OK')
		{
			$errorMessage = $data['error_message'].' ('.$data['status'].')' ?? $data['status'];
			throw new RuntimeException($errorMessage, Google\ErrorCodes::CONVERTER_BYQUERY_ERROR);
		}

		if(!isset($data['results']) || !is_array($data['results']))
		{
			return null;
		}

		$result = new Collection;

		foreach($data['results'] as $item)
		{
			if(isset($item['types']))
			{
				$type = $this->convertTypes($item['types'], Location\Type::class);
			}
			else
			{
				$type = Address\FieldType::UNKNOWN;
			}

			$location = (new Location())
				->setSourceCode(Google\Repository::getSourceCode())
				->setExternalId($item['place_id'])
				->setName($item['name'])
				->setLongitude($item['geometry']['location']['lng'])
				->setLatitude($item['geometry']['location']['lat'])
				->setType($type)
				->setLanguageId($this->languageId);

			$result->addItem($location);
		}

		return $result;
	}
}

