<?php
namespace Bitrix\Landing;

use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Context;
use \Bitrix\Main\Web\Uri;

class Domain extends \Bitrix\Landing\Internals\BaseTable
{
	/**
	 * Bitrix24 domains.
	 * @see \Bitrix\Landing\Agent::removeBadDomains
	 */
	const B24_DOMAINS = [
		'bitrix24.site',
		'bitrix24.shop',
		'bitrix24site.by',
		'bitrix24shop.by',
		'bitrix24site.ua',
		'bitrix24site.ru',
		'bitrix24shop.ru',
		'b24site.online',
		'b24shop.online',
	];

	/**
	 * Internal class.
	 * @var string
	 */
	public static $internalClass = 'DomainTable';

	/**
	 * Gets domain name.
	 * @return string.
	 */
	protected static function getDomainName()
	{
		static $domain = null;

		if (!$domain)
		{
			$context = \Bitrix\Main\Application::getInstance()->getContext();
			$server = $context->getServer();
			$domain = $server->getServerName();
			if (!$domain)
			{
				$domain = $server->getHttpHost();
			}
		}

		return $domain;
	}

	/**
	 * Returns Bitrix24 sub domain name from full domain name.
	 *
	 * @param string $domainName Full domain name.
	 * @param string|null &$baseUrl If specified will be set to base url from full domain.
	 * @return string|null Null, if $domainName isn't Bitrix24's subdomain.
	 */
	public static function getBitrix24Subdomain(string $domainName, ?string &$baseUrl = null): ?string
	{
		$re = '/^([^\.]+)\.(' . implode('|', self::B24_DOMAINS) . ')$/i';
		if (preg_match($re, $domainName, $matches))
		{
			$baseUrl = ".{$matches[2]}";
			return $matches[1];
		}

		return null;
	}

	/**
	 * Returns postfix for bitrix24.site.
	 *
	 * @use self::getBitrix24Subdomain
	 * @param string $type Site type.
	 * @return string
	 */
	public static function getBitrix24Postfix(string $type): string
	{
		$zone = Manager::getZone();
		$postfix = ($type === 'STORE') ? '.bitrix24.shop' : '.bitrix24.site';
		$type = mb_strtoupper($type);

		// local domain
		if (in_array($zone, ['ru']))
		{
			$postfix = '.';
			$postfix .= ($type === 'STORE') ? 'bitrix24shop' : 'bitrix24site';
			$postfix .= '.' . $zone;
		}
		if (in_array($zone, ['by']))
		{
			if ($type === 'STORE')
			{
				$postfix = '.b24shop.online';
			}
			else
			{
				$postfix = '.b24site.online';
			}
		}

		return $postfix;
	}

	/**
	 * Returns true if remote service os available.
	 * @return bool
	 */
	public static function canRegisterInBitrix24(): bool
	{
		try
		{
			Manager::getExternalSiteController()::isDomainExists('repo.bitrix24.site');
		}
		catch (\Bitrix\Main\SystemException $ex)
		{
			return false;
		}

		return true;
	}

	/**
	 * Create current domain and return new id..
	 * @return int
	 */
	public static function createDefault()
	{
		$res = self::add(array(
			'ACTIVE' => 'Y',
			'DOMAIN' => self::getDomainName()
		));
		if ($res->isSuccess())
		{
			return $res->getId();
		}

		return false;
	}

	/**
	 * Get current domain id.
	 * @return int
	 */
	public static function getCurrentId()
	{
		$res = self::getList(array(
			'filter' => array(
				'=ACTIVE' => 'Y',
				'=DOMAIN' => self::getDomainName()
			)
		));
		if ($row = $res->fetch())
		{
			return $row['ID'];
		}
		else
		{
			return self::createDefault();
		}
	}

	/**
	 * Get available protocol list.
	 * @return array
	 */
	public static function getProtocolList()
	{
		return \Bitrix\Landing\Internals\DomainTable::getProtocolList();
	}

	/**
	 * Gets current host url.
	 * @return string
	 */
	public static function getHostUrl()
	{
		static $hostUrl = null;

		if ($hostUrl !== null)
		{
			return $hostUrl;
		}

		$request = Context::getCurrent()->getRequest();
		$protocol = ($request->isHttps() ? 'https://' : 'http://');

		if (defined('SITE_SERVER_NAME') && SITE_SERVER_NAME)
		{
			$host = SITE_SERVER_NAME;
		}
		else
		{
			$host = Option::get('main', 'server_name', $request->getHttpHost());
		}

		$hostUrl = rtrim($protocol . $host, '/');

		return $hostUrl;
	}

	/**
	 * Returns top level domain by domain name.
	 * @param string $domainName Domain name.
	 * @return string
	 */
	public static function getTLD(string $domainName): string
	{
		$domainName = mb_strtolower(trim($domainName));
		$domainNameParts = explode('.', $domainName);
		$domainNameTld = $domainNameParts[count($domainNameParts) - 1];

		if ($domainNameParts[count($domainNameParts) - 2] == 'com')
		{
			$domainNameTld = 'com.' . $domainNameTld;
		}

		return $domainNameTld;
	}
}
