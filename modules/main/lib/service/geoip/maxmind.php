<?php

namespace Bitrix\Main\Service\GeoIp;

use Bitrix\Main;
use Bitrix\Main\Error;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Localization\Loc;

/**
 * Class MaxMind
 * @package Bitrix\Main\Service\GeoIp
 * @link https://www.maxmind.com
 */
class MaxMind extends Base
{
	/**
	 * @return string Title of handler.
	 */
	public function getTitle()
	{
		return 'MaxMind';
	}

	/**
	 * @return string Handler description.
	 */
	public function getDescription()
	{
		return Loc::getMessage('MAIN_SRV_GEOIP_MM_DESCRIPTION');
	}

	/**
	 * @param string $ipAddress Ip address
	 * @param string $userId user identifier obtained from www.maxmind.com
	 * @param string $licenseKey
	 * @return Main\Result
	 */
	protected function sendRequest($ipAddress, $userId, $licenseKey)
	{
		$result = new Main\Result();
		$httpClient = $this->getHttpClient();
		$httpClient->setAuthorization($userId, $licenseKey);

		$httpRes = $httpClient->get($this->getEndpoint() . $ipAddress . '?pretty');

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
				$result->addError(new Error('Http status: '.$status));

			$arRes = json_decode($httpRes, true);

			if(is_array($arRes))
			{
				if ($status == 200)
				{
					$result->setData($arRes);
				}
				else
				{
					$result->addError(new Error('['.$arRes['code'].'] '.$arRes['error']));
				}
			}
			else
			{
				$result->addError(new Error('Can\'t decode json result'));
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
		return array('de', 'en', 'es', 'fr', 'ja', 'pt-BR', 'ru', 'zh-CN');
	}

	/**
	 * @param string $ip Ip address
	 * @param string $lang Language identifier
	 * @return Result | null
	 */
	public function getDataResult($ip, $lang = '')
	{
		$dataResult = new Result();
		$geoData = new Data();
		
		$geoData->lang = ($lang <> '' ? $lang : 'en');

		if($this->config['USER_ID'] == '' || $this->config['LICENSE_KEY'] == '')
		{
			$dataResult->addError(new Error(Loc::getMessage('MAIN_SRV_GEOIP_MM_SETT_EMPTY')));
			return $dataResult;
		}

		$res = $this->sendRequest($ip, $this->config['USER_ID'], $this->config['LICENSE_KEY']);

		if($res->isSuccess())
		{
			$lang = $geoData->lang;

			$data = $res->getData();

			$geoData->ipNetwork = $data['traits']['network'] ?? null;

			$geoData->continentCode = $data['continent']['code'] ?? null;
			$geoData->continentName = $data['continent']['names'][$lang] ?? $data['continent']['name'] ?? null;

			$geoData->countryCode = $data['country']['iso_code'] ?? null;
			$geoData->countryName = $data['country']['names'][$lang] ?? $data['country']['name'] ?? null;

			$geoData->regionCode = $data['subdivisions'][0]['iso_code'] ?? null;
			$geoData->regionGeonameId = $data['subdivisions'][0]['geoname_id'] ?? null;
			$geoData->regionName = $data['subdivisions'][0]['names'][$lang] ?? $data['subdivisions'][0]['name'] ?? null;

			if ($geoData->regionGeonameId)
			{
				$geoData->geonames[$geoData->regionGeonameId] = $res->getData()['subdivisions'][0]['names'] ?? [];
			}

			$geoData->subRegionCode = $data['subdivisions'][1]['iso_code'] ?? null;
			$geoData->subRegionGeonameId = $data['subdivisions'][1]['geoname_id'] ?? null;
			$geoData->subRegionName = $data['subdivisions'][1]['names'][$lang] ?? $data['subdivisions'][1]['name'] ?? null;

			if ($geoData->subRegionGeonameId)
			{
				$geoData->geonames[$geoData->subRegionGeonameId] = $res->getData()['subdivisions'][1]['names'] ?? [];
			}

			$geoData->cityName = $data['city']['names'][$lang] ?? $data['city']['name'] ?? null;
			$geoData->cityGeonameId = $data['city']['geoname_id'] ?? null;

			if ($geoData->cityGeonameId)
			{
				$geoData->geonames[$geoData->cityGeonameId] = $res->getData()['city']['names'] ?? [];
			}

			$geoData->latitude = $data['location']['latitude'] ?? null;
			$geoData->longitude = $data['location']['longitude'] ?? null;
			$geoData->timezone = $data['location']['time_zone'] ?? null;

			$geoData->zipCode = $data['postal']['code'] ?? null;

			$geoData->ispName = $data['traits']['isp'] ?? null;
			$geoData->organizationName = $data['traits']['organization'] ?? null;
			$geoData->asn = $data['traits']['autonomous_system_number'] ?? null;
			$geoData->asnOrganizationName = $data['traits']['autonomous_system_organization'] ?? null;
		}
		else
		{
			$dataResult->addErrors($res->getErrors());
		}

		$dataResult->setGeoData($geoData);
		return $dataResult;
	}

	/**
	 * Is this handler installed and ready for using.
	 * @return bool
	 */
	public function isInstalled()
	{
		return !empty($this->config['USER_ID']) && !empty($this->config['LICENSE_KEY']);
	}

	/**
	 * @param array $postFields  Admin form posted fields during saving process.
	 * @return array Field CONFIG for saving to DB in admin edit form.
	 */
	public function createConfigField(array $postFields)
	{
		return array(
			'SERVICE' => $postFields['SERVICE'] ?? 'GeoIP2City',
			'USER_ID' => $postFields['USER_ID'] ?? '',
			'LICENSE_KEY' => $postFields['LICENSE_KEY'] ?? '',
		);
	}

	/**
	 * @return array Set of fields description for administration purposes.
	 */
	public function getConfigForAdmin()
	{
		return [
			[
				'NAME' => 'SERVICE',
				'TITLE' => Loc::getMessage("main_geoip_mm_service"),
				'TYPE' => 'LIST',
				'VALUE' => ($this->config['SERVICE'] ?? 'GeoIP2City'),
				'OPTIONS' => [
					'GeoLite2Country' => 'GeoLite2 Country',
					'GeoLite2City' => 'GeoLite2 City',
					'GeoIP2Country' => 'GeoIP2 Country',
					'GeoIP2City' => 'GeoIP2 City',
					'GeoIP2Insights' => 'GeoIP2 Insights',
				],
				'REQUIRED' => true,
			],
			[
				'NAME' => 'USER_ID',
				'TITLE' => Loc::getMessage('MAIN_SRV_GEOIP_MM_F_USER_ID'),
				'TYPE' => 'TEXT',
				'VALUE' => htmlspecialcharsbx($this->config['USER_ID']),
				'REQUIRED' => true,
			],
			[
				'NAME' => 'LICENSE_KEY',
				'TITLE' => Loc::getMessage('MAIN_SRV_GEOIP_MM_F_LICENSE_KEY'),
				'TYPE' => 'TEXT',
				'VALUE' => htmlspecialcharsbx($this->config['LICENSE_KEY']),
				'REQUIRED' => true,
			]
		];
	}

	/**
	 * @return ProvidingData Geolocation information witch handler can return.
	 */
	public function getProvidingData()
	{
		$service = $this->config['SERVICE'] ?? 'GeoIP2City';

		if ($service == 'GeoLite2Country' || $service == 'GeoIP2Country')
		{
			return ProvidingData::createForCountry();
		}

		return ProvidingData::createForCity();
	}

	protected function getEndpoint(): string
	{
		static $endpoints = [
			'GeoLite2Country' => 'https://geolite.info/geoip/v2.1/country/',
			'GeoLite2City' => 'https://geolite.info/geoip/v2.1/city/',
			'GeoIP2Country' => 'https://geoip.maxmind.com/geoip/v2.1/country/',
			'GeoIP2City' => 'https://geoip.maxmind.com/geoip/v2.1/city/',
			'GeoIP2Insights' => 'https://geoip.maxmind.com/geoip/v2.1/insights/',
		];

		$service = $this->config['SERVICE'] ?? 'GeoIP2City';
		if (isset($endpoints[$service]))
		{
			return $endpoints[$service];
		}
		return $endpoints['GeoIP2City'];
	}
}
