<?php

namespace Bitrix\Location\Entity\Location;

use Bitrix\Location\Entity\Location;

/**
 * Calculate distance between two Locations
 * Class DistanceCalculator
 * @package Bitrix\Location\Entity\Location
 */
class DistanceCalculator
{
	private const EARTH_RADIUS = 6371;

	/**
	 * @param Location $location1
	 * @param Location $location2
	 * @return bool|float
	 */
	public function calculate(Location $location1, Location $location2)
	{
		if(
			empty($location1->getLatitude())
			|| empty($location1->getLongitude())
			|| empty($location2->getLatitude())
			|| empty($location2->getLongitude())
		)
		{
			return false;
		}

		$lat1 = $location1->getLatitude() / 180 * M_PI;
		$lat2 = $location2->getLatitude() / 180 * M_PI;
		$lon1 = $location1->getLongitude() / 180 * M_PI;
		$lon2 = $location2->getLongitude() / 180 * M_PI;

		return (float)acos(sin($lat1)*sin($lat2)
			+ cos($lat1)*cos($lat2)
			* cos($lon2-$lon1))
			* self::EARTH_RADIUS;
	}
}