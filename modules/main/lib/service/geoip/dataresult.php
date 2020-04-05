<?
namespace Bitrix\Main\Service\GeoIp;

use Bitrix\Main;

/**
 * Class DataResult
 * @package Bitrix\Main\Service\GeoIp
 * Class-structure for transferring geolocation data
 * @deprecated
 */
class DataResult extends Main\Result
{
	public $ip = Manager::INFO_NOT_AVAILABLE;

	public $lang = Manager::INFO_NOT_AVAILABLE;

	public $countryName = Manager::INFO_NOT_AVAILABLE;
	public $regionName = Manager::INFO_NOT_AVAILABLE;
	public $subRegionName = Manager::INFO_NOT_AVAILABLE;
	public $cityName = Manager::INFO_NOT_AVAILABLE;

	public $countryCode = Manager::INFO_NOT_AVAILABLE;
	public $regionCode = Manager::INFO_NOT_AVAILABLE;

	public $zipCode = Manager::INFO_NOT_AVAILABLE;

	public $latitude = Manager::INFO_NOT_AVAILABLE;
	public $longitude = Manager::INFO_NOT_AVAILABLE;

	public $timezone = Manager::INFO_NOT_AVAILABLE;

	/** @var string Autonomous System Numbers (ASN) */
	public $asn = Manager::INFO_NOT_AVAILABLE;
	/** @var string Internet Service Provider (ISP) name */
	public $ispName = Manager::INFO_NOT_AVAILABLE;

	public $organizationName = Manager::INFO_NOT_AVAILABLE;
	public $handlerClass = Manager::INFO_NOT_AVAILABLE;
}