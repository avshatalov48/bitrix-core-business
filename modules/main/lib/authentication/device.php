<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2024 Bitrix
 */

namespace Bitrix\Main\Authentication;

use Bitrix\Main;
use Bitrix\Main\Web;
use Bitrix\Main\Security;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Authentication\Internal\EO_UserDevice;
use Bitrix\Main\Authentication\Internal\EO_UserDeviceLogin;
use Bitrix\Main\Web\UserAgent\Browser;
use Bitrix\Main\Web\UserAgent\DeviceType;
use Bitrix\Main\Service\GeoIp;
use Bitrix\Main\Service\GeoIp\Internal\GeonameTable;
use Bitrix\Main\Mail;
use Bitrix\Main\Localization\LanguageTable;

class Device
{
	protected const COOKIE_NAME = 'UIDD';
	public const EMAIL_EVENT = 'NEW_DEVICE_LOGIN';

	public static function addLogin(Context $context, array $user): void
	{
		$device = static::findByCookie($context);

		if ($device === null)
		{
			$cookable = false;
			$device = static::findByUserAgent($context);

			if ($device === null && $context->getApplicationPasswordId())
			{
				$device = static::findByAppPasswordId($context);
			}
		}
		else
		{
			// found by a cookie = supports cookies
			$cookable = true;
		}

		if ($device === null)
		{
			// add a new device for the user
			$device = static::add($context);
			$deviceLogin = static::addDeviceLogin($device, $context);

			if (Option::get('main', 'user_device_notify', 'N') === 'Y')
			{
				// send notification to the user
				static::sendEmail($device, $deviceLogin, $user);
			}
		}
		else
		{
			// update to actual data
			static::update($device, $context, $cookable);
			static::addDeviceLogin($device, $context);
		}

		static::setCookie($device->getDeviceUid());
	}

	protected static function findByCookie(Context $context): ?EO_UserDevice
	{
		$request = Main\Context::getCurrent()->getRequest();
		$deviceUid = $request->getCookie(static::COOKIE_NAME);

		if (is_string($deviceUid) && $deviceUid != '')
		{
			return Internal\UserDeviceTable::query()
				->setSelect(['*'])
				->where('USER_ID', $context->getUserId())
				->where('DEVICE_UID', $deviceUid)
				->fetchObject()
			;
		}

		return null;
	}

	protected static function findByUserAgent(Context $context): ?EO_UserDevice
	{
		$request = Main\Context::getCurrent()->getRequest();
		$userAgent = $request->getUserAgent();

		// only user agents not supporting cookies
		$query = Internal\UserDeviceTable::query()
			->setSelect(['*'])
			->where('USER_ID', $context->getUserId())
			->where('COOKABLE', false)
			->exec()
		;

		while ($device = $query->fetchObject())
		{
			if (static::match($userAgent, $device->getUserAgent()))
			{
				return $device;
			}
		}

		return null;
	}

	protected static function findByAppPasswordId(Context $context): ?EO_UserDevice
	{
		$request = Main\Context::getCurrent()->getRequest();
		$userAgent = $request->getUserAgent();

		// only user agents with the same APP_PASSWORD_ID
		$query = Internal\UserDeviceTable::query()
			->setSelect(['*'])
			->where('USER_ID', $context->getUserId())
			->where('COOKABLE', true)
			->where('APP_PASSWORD_ID', $context->getApplicationPasswordId())
			->exec()
		;

		while ($device = $query->fetchObject())
		{
			if (static::match($userAgent, $device->getUserAgent()))
			{
				return $device;
			}
		}

		return null;
	}

	protected static function match(?string $userAgent, ?string $patternAgent): bool
	{
		$pattern = preg_replace('/\\d+/i', '\\d+', preg_quote((string)$patternAgent, '/'));

		return preg_match("/{$pattern}/i", (string)$userAgent);
	}

	protected static function add(Context $context): EO_UserDevice
	{
		$browser = Browser::detect();
		$device = Internal\UserDeviceTable::createObject();

		$device
			->setUserId($context->getUserId())
			->setDeviceUid(Security\Random::getString(32))
			->setDeviceType($browser->getDeviceType())
			->setBrowser($browser->getName())
			->setPlatform($browser->getPlatform())
			->setUserAgent($browser->getUserAgent())
			->setAppPasswordId($context->getApplicationPasswordId())
			->save()
		;

		return $device;
	}

	protected static function update(EO_UserDevice $device, Context $context, bool $cookable): void
	{
		$browser = Browser::detect();

		$device
			->setDeviceType($browser->getDeviceType())
			->setBrowser($browser->getName())
			->setPlatform($browser->getPlatform())
			->setUserAgent($browser->getUserAgent())
			->setCookable($cookable)
			->setAppPasswordId($context->getApplicationPasswordId())
			->save()
		;
	}

	protected static function setCookie(string $value): void
	{
		$cookie = new Web\Cookie(static::COOKIE_NAME, $value, time() + 60 * 60 * 24 * 30 * 12);
		Main\Context::getCurrent()->getResponse()->addCookie($cookie);
	}

	protected static function addDeviceLogin(EO_UserDevice $device, Context $context): EO_UserDeviceLogin
	{
		$ip = GeoIp\Manager::getRealIp();

		$login = Internal\UserDeviceLoginTable::createObject();

		$login
			->setDeviceId($device->getId())
			->setLoginDate(new Main\Type\DateTime())
			->setIp($ip)
			->setAppPasswordId($context->getApplicationPasswordId())
			->setStoredAuthId($context->getStoredAuthId())
			->setHitAuthId($context->getHitAuthId())
		;

		if (Option::get('main', 'user_device_geodata', 'N') === 'Y')
		{
			$ipData = GeoIp\Manager::getDataResult($ip, '', ['cityGeonameId']);

			if ($ipData && $ipData->isSuccess())
			{
				$data = $ipData->getGeoData();

				$login
					->setCityGeoid($data->cityGeonameId)
					->setRegionGeoid($data->subRegionGeonameId ?? $data->regionGeonameId)
					->setCountryIsoCode($data->countryCode)
				;
			}
		}

		$login->save();

		return $login;
	}

	protected static function sendEmail(EO_UserDevice $device, EO_UserDeviceLogin $deviceLogin, array $user): void
	{
		$site = $user['LID'];
		if (!$site)
		{
			$site = Main\Context::getCurrent()->getSite();
			if (!$site)
			{
				$site = \CSite::GetDefSite();
			}
		}

		$currentLang = Main\Context::getCurrent()->getLanguage();
		$lang = $user['LANGUAGE_ID'] != '' ? $user['LANGUAGE_ID'] : $currentLang;

		// Devices
		$deviceTypes = DeviceType::getDescription($lang);

		// City and Region names
		$geoids = array_filter([$deviceLogin->getCityGeoid(), $deviceLogin->getRegionGeoid()]);
		$geonames = GeonameTable::get($geoids);

		$city = '';
		$region = '';
		if (!empty($geonames))
		{
			$langCode = '';
			if ($user['LANGUAGE_ID'] != '' && $user['LANGUAGE_ID'] != $currentLang)
			{
				$language = LanguageTable::getList([
					'filter' => ['=LID' => $user['LANGUAGE_ID'], '=ACTIVE' => 'Y'],
					'cache' => ['ttl' => 86400],
				])->fetchObject();

				if ($language)
				{
					$langCode = $language->getCode();
				}
			}
			if ($langCode == '')
			{
				$langCode = Main\Context::getCurrent()->getLanguageObject()->getCode();
			}

			if (($cityCode = $deviceLogin->getCityGeoid()) > 0)
			{
				$city = $geonames[$cityCode][$langCode] ?? $geonames[$cityCode]['en'] ?? '';
			}
			if (($regionCode = $deviceLogin->getRegionGeoid()) > 0)
			{
				$region = $geonames[$regionCode][$langCode] ?? $geonames[$regionCode]['en'] ?? '';
			}
		}

		// Country name
		$country = '';
		if (($countryCode = $deviceLogin->getCountryIsoCode()) != '')
		{
			$countries = \GetCountries($lang);
			$country = $countries[$countryCode]['NAME'];
		}

		// Combined location
		$location = implode(', ', array_filter([$city, $region, $country]));

		$fields = [
			'EMAIL' => $user['EMAIL'],
			'LOGIN' => $user['LOGIN'],
			'NAME' => $user['NAME'],
			'LAST_NAME' => $user['LAST_NAME'],
			'DEVICE' => $deviceTypes[$device->getDeviceType()],
			'BROWSER' => $device->getBrowser(),
			'PLATFORM' => $device->getPlatform(),
			'USER_AGENT' => $device->getUserAgent(),
			'IP' => $deviceLogin->getIp(),
			'DATE' => $deviceLogin->getLoginDate(),
			'COUNTRY' => $country,
			'REGION' => $region,
			'CITY' => $city,
			'LOCATION' => $location,
		];

		Mail\Event::send([
			'EVENT_NAME' => self::EMAIL_EVENT,
			'C_FIELDS' => $fields,
			'LID' => $site,
			'LANGUAGE_ID' => $lang,
		]);
	}

	/**
	 * @internal
	 * @param int $lastId
	 * @return string
	 */
	public static function deleteDuplicatesAgent(int $lastId = 0)
	{
		$connection = Main\Application::getConnection();

		$users = $connection->query("
			select USER_ID 
			from b_user_device 
			where USER_ID > {$lastId} 
			group by USER_ID 
			order by USER_ID 
			limit 100
		");

		$userId = null;
		while ($user = $users->fetch())
		{
			$userId = $user['USER_ID'];

			$devices = $connection->query("
				select * 
				from b_user_device 
				where USER_ID = {$userId}
					and COOKABLE = 'Y'
					and APP_PASSWORD_ID is not null 
				order by ID 
			")->fetchAll();

			$deleted = [];
			for ($i = 0, $count = count($devices); $i < $count; $i++)
			{
				$device = $devices[$i];

				if (isset($deleted[$device['ID']]))
				{
					continue;
				}

				for ($j = $i + 1; $j < $count; $j++)
				{
					$deviceDouble = $devices[$j];

					if ($deviceDouble['APP_PASSWORD_ID'] == $device['APP_PASSWORD_ID'] && static::match($deviceDouble['USER_AGENT'], $device['USER_AGENT']))
					{
						$connection->query("
							update b_user_device_login 
							set DEVICE_ID = {$device['ID']}
							where DEVICE_ID = {$deviceDouble['ID']} 
						");

						$connection->query("
							delete from b_user_device 
							where ID = {$deviceDouble['ID']} 
						");

						$deleted[$deviceDouble['ID']] = 1;
					}
				}
			}
		}

		if ($userId !== null)
		{
			return "\\Bitrix\\Main\\Authentication\\Device::deleteDuplicatesAgent({$userId});";
		}

		return '';
	}
}
