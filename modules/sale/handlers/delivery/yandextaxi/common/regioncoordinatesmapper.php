<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Common;

/**
 * Class RegionCoordinatesMapper
 * @package Sale\Handlers\Delivery\YandexTaxi\Common
 * @inernal
 */
final class RegionCoordinatesMapper
{
	/**
	 * @param string $region
	 * @return array|float[]
	 */
	public function getRegionCoordinates(string $region): array
	{
		$result = [0, 0];

		switch ($region)
		{
			case 'ru':
				$result = [37.587093, 55.733969];
				break;
			case 'kz':
				$result = [71.430411, 51.128207];
				break;
			case 'by':
				$result = [27.559665, 53.902194];
				break;
		}

		return $result;
	}
}
