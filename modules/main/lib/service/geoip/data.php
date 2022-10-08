<?php

namespace Bitrix\Main\Service\GeoIp;

/**
 * Class Data
 * @package Bitrix\Main\Service\GeoIp
 *
 * Structure for transferring geolocation data.
 */
class Data
{
	/** @var string Ip address. */
	public $ip;
	/** @var string Ip network. */
	public $ipNetwork;

	/** @var string Storing data language. */
	public $lang;

	/** @var string Continent name  */
	public $continentName;
	/** @var string Country name  */
	public $countryName;
	/** @var string Region name  */
	public $regionName;
	/** @var string Subregion name  */
	public $subRegionName;
	/** @var string City name  */
	public $cityName;

	/** @var string Continent code  */
	public $continentCode;
	/** @var string Country code  */
	public $countryCode;
	/** @var string Region code  */
	public $regionCode;
	/** @var string Subregion code  */
	public $subRegionCode;

	/** @var string A unique identifier for the location as specified by GeoNames  */
	public $cityGeonameId;
	/** @var string A unique identifier for the location as specified by GeoNames  */
	public $regionGeonameId;
	/** @var string A unique identifier for the location as specified by GeoNames  */
	public $subRegionGeonameId;

	/** @var array Names for geonameIds, always in UTF-8 */
	public $geonames = [];

	/** @var string Zip or postal code  */
	public $zipCode;

	/** @var string Latitude*/
	public $latitude;
	/** @var string Longitude*/
	public $longitude;
	/** @var string Timezone*/
	public $timezone;

	/** @var string Internet Service Provider (ISP) name */
	public $ispName;
	/** @var string Organization name */
	public $organizationName;

	/** @var string Autonomous System Numbers (ASN) */
	public $asn;
	/** @var string ASN organization */
	public $asnOrganizationName;

	/** @var string Geolocation handler - source of information */
	public $handlerClass;
}
