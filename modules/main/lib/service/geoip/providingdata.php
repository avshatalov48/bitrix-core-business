<?
namespace Bitrix\Main\Service\GeoIp;

/**
 * Class ProvidingData
 * @package Bitrix\Main\Service\GeoIp
 *
 * The structure witch contain information about providing data.
 */
class ProvidingData
{
	/** @var bool Country name  */
	public $countryName = false;
	/** @var bool Region name  */
	public $regionName = false;
	/** @var bool Subregion name  */
	public $subRegionName = false;
	/** @var bool City name  */
	public $cityName = false;

	/** @var bool Country code  */
	public $countryCode = false;
	/** @var bool Region code  */
	public $regionCode = false;

	/** @var bool Zip or postal code  */
	public $zipCode = false;

	/** @var bool Latitude*/
	public $latitude = false;
	/** @var bool Longitude*/
	public $longitude = false;

	/** @var bool Timezone*/
	public $timezone = false;

	/** @var bool Autonomous System Numbers (ASN) */
	public $asn = false;
	/** @var bool Internet Service Provider (ISP) name */
	public $ispName = false;

	/** @var bool Organization name */
	public $organizationName = false;
}