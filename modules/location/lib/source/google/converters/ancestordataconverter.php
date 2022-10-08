<?php

namespace Bitrix\Location\Source\Google\Converters;

use Bitrix\Location\Entity\Address\FieldType;
use Bitrix\Location\Entity\Location\Type;

final class AncestorDataConverter
{
	public function convert(array $locationRawData, int $descendantType)
	{
		$result = [];

		if(isset($locationRawData['result']['address_components'])
			&& is_array($locationRawData['result']['address_components'])
			&& count($locationRawData['result']['address_components']) > 0
		)
		{
			$items = array_reverse($locationRawData['result']['address_components']);
			$accumulator = '';

			foreach ($items as $item)
			{
				$types =$this->convertTypes($item['types'], $descendantType);

				if(empty($types))
				{
					continue;
				}

				if($accumulator <> '')
				{
					$accumulator .= ',';
				}

				$accumulator .= $item['long_name'];

				$result[] = [
					'NAME' => $accumulator,
					'TYPES' => $types
				];
			}
		}

		return array_reverse($result);
	}

	protected function convertTypes(array $types, int $descendantType)
	{
		$result = [];

		foreach($types as $gType)
		{
			$type = PlaceTypeConverter::convert($gType);

			if($type === Type::UNKNOWN)
			{
				continue;
			}

			//Only location type
			if(!Type::isValueExist($type))
			{
				continue;
			}

			//Type::COUNTRY == 100 Type::LOCALITY == 300
			if($descendantType !== Type::UNKNOWN && $descendantType <= $type)
			{
				continue;
			}

			$result[] =  $type;
		}

		return $result;
	}
}