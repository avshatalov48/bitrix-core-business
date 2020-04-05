<?
namespace Bitrix\Sale\Location;

use Bitrix\Main\Service\GeoIp\Result,
	Bitrix\Main\Service\GeoIp\Manager;

class GeoIp
{
	/**
	 * @param string $ip Ip address.
	 * @param string $lang Language identifier.
	 * @return int Location id.
	 */
	public static function getLocationId($ip = '', $lang = LANGUAGE_ID)
	{
		$fields = array();
		$geoData = self::getData($ip, $lang);

		if($geoData)
			$fields = self::getLocationFields($geoData);

		return intval($fields['ID']) > 0  ? intval($fields['ID']) : 0;
	}

	/**
	 * @param string $ip Ip address.
	 * @param string $lang Language identifier.
	 * @return string Location code.
	 */
	public static function getLocationCode($ip = '', $lang = LANGUAGE_ID)
	{
		$fields = array();
		$geoData = self::getData($ip, $lang);

		if($geoData)
			$fields = self::getLocationFields($geoData);

		return strlen($fields['CODE']) > 0 ? $fields['CODE'] : '';
	}

	/**
	 * @param string $ip Ip address.
	 * @param string $lang Language.
	 * @return string Zip (postal) code.
	 */
	public static function getZipCode($ip, $lang = LANGUAGE_ID)
	{
		$data = self::getData($ip, $lang);

		if(!$data)
			$result = '';
		else
			$result = strlen($data->getGeoData()->zipCode) > 0 ? $data->getGeoData()->zipCode : '';

		return $result;
	}

	/**
	 * @param string $ip Ip address.
	 * @param string $lang Language identifier.
	 * @return Result.
	 */
	protected static function getData($ip, $lang)
	{
		Manager::useCookieToStoreInfo(true);
		return Manager::getDataResult($ip, $lang, array('cityName'));
	}

	/**
	 * @param Result $geoIpData.
	 * @return array Location fields.
	 */
	protected static function getLocationFields(Result $geoIpData)
	{
		$cityName = $geoIpData->getGeoData()->cityName;
		$result = array();

		$res = LocationTable::getList(array(
			'filter' => array(
				'=NAME.NAME_UPPER' => ToUpper($cityName),
				'=NAME.LANGUAGE_ID' => $geoIpData->getGeoData()->lang
			),
			'select' => array('ID', 'CODE')
		));

		if($loc = $res->fetch())
			$result = $loc;

		return $result;
	}
}
