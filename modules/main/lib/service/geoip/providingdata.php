<?php

namespace Bitrix\Main\Service\GeoIp;

/**
 * Class ProvidingData
 * @package Bitrix\Main\Service\GeoIp
 *
 * The structure witch contain information about providing data.
 */
class ProvidingData extends Data
{
	public static function createForCountry(): ProvidingData
	{
		$data = new static();
		$data->continentName = true;
		$data->continentCode = true;
		$data->countryName = true;
		$data->countryCode = true;

		return $data;
	}

	public static function createForCity(): ProvidingData
	{
		$data = static::createForCountry();

		$data->regionName = true;
		$data->regionCode = true;
		$data->subRegionName = true;
		$data->subRegionCode = true;
		$data->cityName = true;
		$data->cityGeonameId = true;
		$data->latitude = true;
		$data->longitude = true;
		$data->timezone = true;
		$data->zipCode = true;
		$data->ispName = true;
		$data->organizationName = true;
		$data->asn = true;
		$data->asnOrganizationName = true;

		return $data;
	}
}
