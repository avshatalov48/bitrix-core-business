<?php

namespace Bitrix\Main\Service\GeoIp;

use Bitrix\Main;
use Bitrix\Main\Error;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Localization\Loc;

/**
 * Class SypexGeo
 * @package Bitrix\Main\Service\GeoIp
 * @link http://sypexgeo.net
 */
final class SypexGeo extends Base
{
	/**
	 * @return string Title of handler.
	 */
	public function getTitle()
	{
		return Loc::getMessage('MAIN_SRV_GEOIP_SG_TITLE');
	}

	/**
	 * @return string Handler description.
	 */
	public function getDescription()
	{
		return Loc::getMessage('MAIN_SRV_GEOIP_SG_DESCRIPTION');
	}

	/**
	 * @param string $ip Ip address
	 * @param string $key license key.
	 * @return Main\Result
	 */
	protected function sendRequest($ip, $key)
	{
		$result = new Main\Result();
		$httpClient = $this->getHttpClient();
		$url = 'http://api.sypexgeo.net/';

		if($key <> '')
			$url .= $key.'/';

		$url .= "json/".$ip;

		$httpRes = $httpClient->get($url);
		$errors = $httpClient->getError();

		if (!$httpRes && !empty($errors))
		{
			$strError = "";

			foreach($errors as $errorCode => $errMes)
				$strError .= $errorCode.": ".$errMes;

			$result->addError(new Error($strError));
		}
		else
		{
			$status = $httpClient->getStatus();

			if ($status != 200)
			{
				$result->addError(new Error('Sypexgeo.net http status: '.$status));
			}
			else
			{
				$arRes = json_decode($httpRes, true);

				if(is_array($arRes))
				{
					$result->setData($arRes);
				}
				else
				{
					$result->addError(new Error('Can\'t decode json result'));
				}
			}
		}

		return $result;
	}

	/**
	 * @return HttpClient
	 */
	protected static function getHttpClient()
	{
		return new HttpClient(array(
			"version" => "1.1",
			"socketTimeout" => 5,
			"streamTimeout" => 5,
			"redirect" => true,
			"redirectMax" => 5,
		));
	}

	/**
	 * Languages supported by handler ISO 639-1
	 * @return array
	 */
	public function getSupportedLanguages()
	{
		return array('en', 'ru');
	}

	/**
	 * @param string $ip Ip address
	 * @param string $lang Language identifier
	 * @return Result | null
	 */
	public function getDataResult($ip, $lang = '')
	{
		$dataResult = new Result;
		$geoData = new Data();

		$geoData->lang = $lang = $lang <> '' ? $lang : 'en';

		$key = !empty($this->config['KEY']) ? $this->config['KEY'] : '';
		$res = $this->sendRequest($ip, $key);

		if($res->isSuccess())
		{
			$data = $res->getData();

			$geoData->countryName = $data['country']['name_'.$lang];
			$geoData->countryCode = $data['country']['iso'];
			$geoData->regionName = $data['region']['name_'.$lang];
			$geoData->regionGeonameId = $data['region']['id'];
			$geoData->regionCode = $data['region']['iso'];
			$geoData->cityName = $data['city']['name_'.$lang];
			$geoData->cityGeonameId = $data['city']['id'];
			$geoData->latitude = $data['city']['lat'];
			$geoData->longitude = $data['city']['lon'];
			$geoData->timezone = $data['region']['timezone'];

			if ($geoData->regionGeonameId && $geoData->regionName)
			{
				$geoData->geonames[$geoData->regionGeonameId][$lang] = $geoData->regionName;
			}

			if ($geoData->cityGeonameId && $geoData->cityName)
			{
				$geoData->geonames[$geoData->cityGeonameId][$lang] = $geoData->cityName;
			}
		}
		else
		{
			$dataResult->addErrors($res->getErrors());
		}

		$dataResult->setGeoData($geoData);
		return $dataResult;
	}

	/**
	 * @param array $postFields $_POST
	 * @return array Field CONFIG for saving to DB in admin edit form.
	 */
	public function createConfigField(array $postFields)
	{
		return array(
			'KEY' => $postFields['KEY'] ?? ''
		);
	}

	/**
	 * @return array Set of fields description for administration purposes.
	 */
	public function getConfigForAdmin()
	{
		return array(
			array(
				'NAME' => 'KEY',
				'TITLE' => Loc::getMessage('MAIN_SRV_GEOIP_SG_KEY'),
				'TYPE' => 'TEXT',
				'VALUE' => htmlspecialcharsbx($this->config['KEY'])
			)
		);
	}
	
	/**
	 * @return ProvidingData Geolocation information witch handler can return.
	 */
	public function getProvidingData()
	{
		$result = new ProvidingData();
		$result->countryName = true;
		$result->countryCode = true;
		$result->regionName = true;
		$result->regionGeonameId = true;
		$result->regionCode = true;
		$result->cityName = true;
		$result->cityGeonameId = true;
		$result->latitude = true;
		$result->longitude = true;
		$result->timezone = true;
		return $result;
	}
}
