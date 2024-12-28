<?php
namespace Bitrix\Landing\Domain;

use \Bitrix\Landing\Manager;
use \Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;

class Register
{
	/**
	 * B24 service for detect IP for current lang zone.
	 */
	const B24_SERVICE_DETECT_IP = 'https://ip.bitrix24.site/getipforzone/?bx24_zone=';

	/**
	 * B24 service for detect that domain is available.
	 */
	const B24_SERVICE_DETECT_DOMAIN = 'https://ip.bitrix24.site/getdomainstatus/?bx24_site_domain=';

	/**
	 * Default IP for DNS.
	 */
	const B24_DEFAULT_DNS_IP = '52.59.124.117';

	/**
	 * Default CNAME for DNS.
	 */
	const B24_DEFAULT_DNS_CNAME = 'lb.bitrix24.site.';

	/**
	 * Finds registrator instance and returns it.
	 * @return Provider|null
	 */
	public static function getInstance(): ?Provider
	{
		return new Provider\Bitrix24();
	}

	/**
	 * Returns IP for A record DNS.
	 * @param string|null $tld Top level domain.
	 * @return string
	 */
	protected static function getINA(?string $tld = null): string
	{
		$http = new HttpClient;
		$zone = ($tld == 'kz') ? 'kz' : Manager::getZone();
		$ip = $http->get(self::B24_SERVICE_DETECT_IP . $zone);

		try
		{
			$ip = Json::decode($ip);
		}
		catch (\Exception $e)
		{
			return self::B24_DEFAULT_DNS_IP;
		}

		return $ip['IP'] ?? self::B24_DEFAULT_DNS_IP;
	}

	/**
	 * Returns INA && CNAME records value for domain registration.
	 * @param string|null $tld Top level domain.
	 * @return array
	 */
	public static function getDNSRecords(?string $tld = null): array
	{
		static $result = null;

		if ($result !== null)
		{
			return $result;
		}

		$result = [
			'INA' => self::getINA($tld),
			'CNAME' => self::B24_DEFAULT_DNS_CNAME
		];

		return $result;
	}

	/**
	 * Returns true if domain is enabled and active now.
	 * @param string $domainName Domain name.
	 * @return bool
	 */
	public static function isDomainActive(string $domainName): bool
	{
		$http = new HttpClient;
		$status = $http->get(self::B24_SERVICE_DETECT_DOMAIN . $domainName);
		try
		{
			$status = Json::decode($status);
			// protect from bad answer
			if (!isset($status['status']))
			{
				return true;
			}
			return $status['status'] === 'ready';
		}
		catch (\Exception $e)
		{
			return false;
		}
	}
}