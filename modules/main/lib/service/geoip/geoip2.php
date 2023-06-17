<?php

namespace Bitrix\Main\Service\GeoIp;

use Bitrix\Main\Error;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Localization\Loc;
use GeoIp2\Exception;
use GeoIp2\Database;
use MaxMind\Db\Reader;

/**
 * Class GeoIP2
 * @package Bitrix\Main\Service\GeoIp
 * @link https://www.maxmind.com
 */
class GeoIP2 extends Base
{
	/** @var Database\Reader */
	protected static $reader;

	/**
	 * @return string Title of handler.
	 */
	public function getTitle()
	{
		return 'GeoIP2';
	}

	/**
	 * @return string Handler description.
	 */
	public function getDescription()
	{
		return Loc::getMessage("geoip_geoip2_desc");
	}

	/**
	 * Languages supported by handler ISO 639-1
	 * @return array
	 */
	public function getSupportedLanguages()
	{
		return ['de', 'en', 'es', 'fr', 'ja', 'pt-BR', 'ru', 'zh-CN'];
	}

	/**
	 * @param string $ip Ip address
	 * @param string $lang Language identifier
	 * @return Result | null
	 */
	public function getDataResult($ip, $lang = '')
	{
		$dataResult = $this->initReader();
		if (!$dataResult->isSuccess())
		{
			return $dataResult;
		}

		$geoData = new Data();
		$geoData->lang = ($lang != '' ? $lang : 'en');

		try
		{
			$type = $this->config['TYPE'] ?? 'city';

			if ($type == 'city')
			{
				$record = static::$reader->city($ip);
			}
			else
			{
				$record = static::$reader->country($ip);
			}

			$geoData->ipNetwork = $record->traits->network;

			$geoData->continentCode = $record->continent->code;
			$geoData->continentName = Encoding::convertEncodingToCurrent(($record->continent->names[$geoData->lang] ?? $record->continent->name));

			$geoData->countryCode = $record->country->isoCode;
			$geoData->countryName = Encoding::convertEncodingToCurrent(($record->country->names[$geoData->lang] ?? $record->country->name));

			if ($record instanceof \GeoIp2\Model\City)
			{
				if (isset($record->subdivisions[0]))
				{
					/** @var \GeoIp2\Record\Subdivision $subdivision */
					$subdivision = $record->subdivisions[0];
					$geoData->regionCode = $subdivision->isoCode;
					$geoData->regionName = Encoding::convertEncodingToCurrent(($subdivision->names[$geoData->lang] ?? $subdivision->name));
					$geoData->regionGeonameId = $subdivision->geonameId;

					if ($subdivision->geonameId)
					{
						$geoData->geonames[$subdivision->geonameId] = $subdivision->names;
					}
				}

				if (isset($record->subdivisions[1]))
				{
					/** @var \GeoIp2\Record\Subdivision $subdivision */
					$subdivision = $record->subdivisions[1];
					$geoData->subRegionCode = $subdivision->isoCode;
					$geoData->subRegionName = Encoding::convertEncodingToCurrent(($subdivision->names[$geoData->lang] ?? $subdivision->name));
					$geoData->subRegionGeonameId = $subdivision->geonameId;

					if ($subdivision->geonameId)
					{
						$geoData->geonames[$subdivision->geonameId] = $subdivision->names;
					}
				}

				$geoData->cityName = Encoding::convertEncodingToCurrent(($record->city->names[$geoData->lang] ?? $record->city->name));
				$geoData->cityGeonameId = $record->city->geonameId;

				if ($record->city->geonameId)
				{
					$geoData->geonames[$record->city->geonameId] = $record->city->names;
				}

				$geoData->latitude = $record->location->latitude;
				$geoData->longitude = $record->location->longitude;
				$geoData->timezone = $record->location->timeZone;

				$geoData->zipCode = $record->postal->code;

				$geoData->ispName = $record->traits->isp;
				$geoData->organizationName = $record->traits->organization;
				$geoData->asn = $record->traits->autonomousSystemNumber;
				$geoData->asnOrganizationName = $record->traits->autonomousSystemOrganization;
			}

			$dataResult->setGeoData($geoData);
		}
		catch(Exception\AddressNotFoundException $e)
		{
			// is it an error?
		}
		catch(Reader\InvalidDatabaseException $e)
		{
			$dataResult->addError(new Error(Loc::getMessage("geoip_geoip2_err_reading")));
		}

		return $dataResult;
	}

	/**
	 * Is this handler installed and ready for using.
	 * @return bool
	 */
	public function isInstalled()
	{
		return (isset($this->config['FILE']) && $this->config['FILE'] !== '' && file_exists($this->config['FILE']));
	}

	/**
	 * @param array $postFields  Admin form posted fields during saving process.
	 * @return array Field CONFIG for saving to DB in admin edit form.
	 */
	public function createConfigField(array $postFields)
	{
		return [
			'TYPE' => $postFields['TYPE'] ?? 'city',
			'FILE' => $postFields['FILE'] ?? '',
		];
	}

	/**
	 * @return array Set of fields description for administration purposes.
	 */
	public function getConfigForAdmin()
	{
		return [
			[
				'NAME' => 'TYPE',
				'TITLE' => Loc::getMessage("geoip_geoip2_type"),
				'TYPE' => 'LIST',
				'VALUE' => ($this->config['TYPE'] ?? 'city'),
				'OPTIONS' => [
					'city' => 'GeoIP2/GeoLite2 City',
					'country' => 'GeoIP2/GeoLite2 Country',
				],
				'REQUIRED' => true,
			],
			[
				'NAME' => 'FILE',
				'TITLE' => Loc::getMessage("geoip_geoip2_file"),
				'TYPE' => 'TEXT',
				'VALUE' => htmlspecialcharsbx($this->config['FILE'] ?? ''),
				'REQUIRED' => true,
			],
		];
	}

	/**
	 * @return ProvidingData Geolocation information witch handler can return.
	 */
	public function getProvidingData()
	{
		$type = $this->config['TYPE'] ?? 'city';

		if ($type == 'city')
		{
			return ProvidingData::createForCity();
		}
		return ProvidingData::createForCountry();
	}

	/**
	 * @return Result
	 */
	protected function initReader(): Result
	{
		$dataResult = new Result();

		if (static::$reader === null)
		{
			if ($this->config['FILE'] == '')
			{
				$dataResult->addError(new Error(Loc::getMessage("geoip_geoip2_no_file")));
				return $dataResult;
			}

			if (!file_exists($this->config['FILE']))
			{
				$dataResult->addError(new Error(Loc::getMessage("geoip_geoip2_file_not_found")));
				return $dataResult;
			}

			try
			{
				static::$reader = new Database\Reader($this->config['FILE']);
			}
			catch(Reader\InvalidDatabaseException $e)
			{
				$dataResult->addError(new Error(Loc::getMessage("geoip_geoip2_err_reading")));
			}
		}

		return $dataResult;
	}
}
