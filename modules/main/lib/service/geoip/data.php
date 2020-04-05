<?
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
	public $ip = Manager::INFO_NOT_AVAILABLE;

	/** @var string Storing data language. */
	public $lang = Manager::INFO_NOT_AVAILABLE;

	/** @var string Country name  */
	public $countryName = Manager::INFO_NOT_AVAILABLE;
	/** @var string Region name  */
	public $regionName = Manager::INFO_NOT_AVAILABLE;
	/** @var string Subregion name  */
	public $subRegionName = Manager::INFO_NOT_AVAILABLE;
	/** @var string City name  */
	public $cityName = Manager::INFO_NOT_AVAILABLE;

	/** @var string Country code  */
	public $countryCode = Manager::INFO_NOT_AVAILABLE;
	/** @var string Region code  */
	public $regionCode = Manager::INFO_NOT_AVAILABLE;

	/** @var string Zip or postal code  */
	public $zipCode = Manager::INFO_NOT_AVAILABLE;

	/** @var string Latitude*/
	public $latitude = Manager::INFO_NOT_AVAILABLE;
	/** @var string Longitude*/
	public $longitude = Manager::INFO_NOT_AVAILABLE;

	/** @var string Timezone*/
	public $timezone = Manager::INFO_NOT_AVAILABLE;

	/** @var string Autonomous System Numbers (ASN) */
	public $asn = Manager::INFO_NOT_AVAILABLE;
	/** @var string Internet Service Provider (ISP) name */
	public $ispName = Manager::INFO_NOT_AVAILABLE;

	/** @var string Organization name */
	public $organizationName = Manager::INFO_NOT_AVAILABLE;

	/** @var string Geolocation handler - source of information */
	public $handlerClass = Manager::INFO_NOT_AVAILABLE;
}