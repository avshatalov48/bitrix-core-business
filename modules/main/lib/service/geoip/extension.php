<?
namespace Bitrix\Main\Service\GeoIp;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class Extension
 * @package Bitrix\Main\Service\GeoIp
 * Uses standard php GeoIP extension
 * @link http://php.net/manual/ru/book.geoip.php
 */
class Extension extends Base
{
	/**
	 * @return string Class title
	 */
	public function getTitle()
	{
		return Loc::getMessage('MAIN_SRV_GEOIP_EXT_TITLE');
	}

	/**
	 * @return string Class description
	 */
	public function getDescription()
	{
		return Loc::getMessage('MAIN_SRV_GEOIP_EXT_DESCRIPTION');
	}

	/**
	 * Languages supported by handler ISO 639-1
	 * @return array
	 */
	public function getSupportedLanguages()
	{
		return array('en');
	}
	
	/**
	 * @param string $ip Ip address
	 * @param string $lang Language identifier
	 * @return Result
	 */
	public function getDataResult($ip, $lang = '')
	{
		$dataResult = new Result;
		$geoData = new Data();

		$geoData->lang = 'en';

		if (self::isAvailableBaseCountry())
		{
			$geoData->countryCode = geoip_country_code_by_name($ip);
			$geoData->countryName = geoip_country_name_by_name($ip);
		}

		if (self::isAvailableBaseCity())
		{
			$recordByName = geoip_record_by_name($ip);

			if (isset($recordByName['country_code']) && isset($recordByName['region']))
			{
				$geoData->timezone = geoip_time_zone_by_country_and_region(
					$recordByName['country_code'],
					$recordByName['region']
				);
			}

			$geoData->countryCode = $recordByName['country_code'];
			$geoData->countryName = $recordByName['country_name'];
			$geoData->regionCode = $recordByName['region'];
			$geoData->cityName = $recordByName['city'];
			$geoData->zipCode = $recordByName['postal_code'];
			$geoData->latitude = $recordByName['latitude'];
			$geoData->longitude = $recordByName['longitude'];
		}

		if (self::isAvailableBaseOrganization())
		{
			$geoData->organizationName = geoip_org_by_name($ip);
		}

		if (self::isAvailableBaseIsp())
		{
			$geoData->ispName = geoip_isp_by_name($ip);
		}

		if (self::isAvailableBaseAsn())
		{
			$geoData->asn = geoip_asnum_by_name($ip);
		}

		$dataResult->setGeoData($geoData);
		return $dataResult;
	}

	/**
	 * Determine if GeoIP Database is available.
	 *
	 * @return bool
	 */
	protected static function isAvailable()
	{
		return function_exists('geoip_db_avail');
	}

	/**
	 * Determine if GeoIP Country Database is available (GEOIP_COUNTRY_EDITION).
	 *
	 * @return bool
	 */
	protected static function isAvailableBaseCountry()
	{
		return self::isAvailable() && geoip_db_avail(GEOIP_COUNTRY_EDITION);
	}

	/**
	 * Determine if GeoIP City Database is available (GEOIP_CITY_EDITION_REV0).
	 *
	 * @return bool
	 */
	protected static function isAvailableBaseCity()
	{
		return self::isAvailable() && geoip_db_avail(GEOIP_CITY_EDITION_REV0);
	}

	/**
	 * Determine if GeoIP Organization Database is available (GEOIP_ORG_EDITION).
	 *
	 * @return bool
	 */
	protected static function isAvailableBaseOrganization()
	{
		return self::isAvailable() && geoip_db_avail(GEOIP_ORG_EDITION);
	}

	/**
	 * Determine if GeoIP ISP Database is available (GEOIP_ISP_EDITION).
	 *
	 * @return bool
	 */
	protected static function isAvailableBaseIsp()
	{
		return self::isAvailable() && geoip_db_avail(GEOIP_ISP_EDITION) && function_exists('geoip_isp_by_name');
	}

	/**
	 * Determine if GeoIP ASN Database is available (GEOIP_ASNUM_EDITION).
	 *
	 * @return bool
	 */
	protected static function isAvailableBaseAsn()
	{
		return self::isAvailable() && geoip_db_avail(GEOIP_ASNUM_EDITION) && function_exists('geoip_asnum_by_name');
	}

	/**
	 * Is this handler installed and ready for using.
	 * @return bool
	 */
	public function isInstalled()
	{
		return self::isAvailable();
	}

	/**
	 * @return array Set of fields description for administration purposes.
	 */
	public function getConfigForAdmin()
	{
		return array(
			array(
				'TITLE' => Loc::getMessage('MAIN_SRV_GEOIP_EXT_NOT_REQ'),
				'TYPE' => 'COLSPAN2'
			),
			array(
				'TITLE' => Loc::getMessage('MAIN_SRV_GEOIP_EXT_DB_AVIALABLE'),
				'TYPE' => 'COLSPAN2',
				'HEADING' => true
			),
			array(
				'TITLE' => Loc::getMessage('MAIN_SRV_GEOIP_EXT_DB_COUNTRY'),
				'TYPE' => 'CHECKBOX',
				'CHECKED' => self::isAvailableBaseCountry(),
				'DISABLED' => true
			),
			array(
				'TITLE' => Loc::getMessage('MAIN_SRV_GEOIP_EXT_DB_CITY'),
				'TYPE' => 'CHECKBOX',
				'CHECKED' => self::isAvailableBaseCity(),
				'DISABLED' => true
			),
			array(
				'TITLE' => Loc::getMessage('MAIN_SRV_GEOIP_EXT_DB_ORG'),
				'TYPE' => 'CHECKBOX',
				'CHECKED' => self::isAvailableBaseOrganization(),
				'DISABLED' => true
			),
			array(
				'TITLE' => Loc::getMessage('MAIN_SRV_GEOIP_EXT_DB_ISP'),
				'TYPE' => 'CHECKBOX',
				'CHECKED' => self::isAvailableBaseIsp(),
				'DISABLED' => true
			),
			array(
				'TITLE' => Loc::getMessage('MAIN_SRV_GEOIP_EXT_DB_ASN'),
				'TYPE' => 'CHECKBOX',
				'CHECKED' => self::isAvailableBaseAsn(),
				'DISABLED' => true
			)
		);
	}
	
	/**
	 * @return ProvidingData Geolocation information witch handler can return.
	 */
	public function getProvidingData()
	{
		$result = new ProvidingData();

		if (self::isAvailableBaseCountry())
		{
			$result->countryCode = true;
			$result->countryName = true;
		}

		if (self::isAvailableBaseCity())
		{
			$result->timezone = true;
			$result->countryCode = true;
			$result->countryName = true;
			$result->regionCode = true;
			$result->cityName = true;
			$result->zipCode = true;
			$result->latitude = true;
			$result->longitude = true;
		}

		if (self::isAvailableBaseOrganization())
		{
			$result->organizationName = true;
		}

		if (self::isAvailableBaseIsp())
		{
			$result->ispName = true;
		}

		if (self::isAvailableBaseAsn())
		{
			$result->asn = true;
		}

		return $result;
	}
}