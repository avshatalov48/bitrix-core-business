<?php

namespace Bitrix\Location\Source\Osm\Converters;

/**
 * Class Factory
 * @package Bitrix\Location\Source\Osm\Converters
 * @internal
 */
final class Factory
{
	/**
	 * @param array $details
	 * @return BaseConverter
	 */
	public static function make(array $details): BaseConverter
	{
		$className = null;

		if (isset($details['country_code']))
		{
			switch ($details['country_code'])
			{
				case 'ru':
					$className = RuConverter::class;
					break;
				case 'us':
					$className = UsConverter::class;
					break;
				case 'de':
					$className = DeConverter::class;
					break;
				case 'br':
					$className = BrConverter::class;
					break;
			}
		}

		$className = $className ?? GenericConverter::class;

		return new $className();
	}
}
