<?php
namespace Bitrix\UI\Fonts;

use Bitrix\Main;
use CBitrix24;

/**
 * Class Proxy
 * @package Bitrix\UI\Fonts
 */
class Proxy
{
	private static array $sourceDomains = [
		'fonts.googleapis.com',
	];

	private static array $map = [
		'en' => '.bitrix24.com',
		'de' => '.bitrix24.de',
		'fr' => '.bitrix24.fr',
		'it' => '.bitrix24.it',
		'pl' => '.bitrix24.pl',
		'uk' => '.bitrix24.uk',
		'eu' => '.bitrix24.eu',
		'ua' => '.bitrix24.ua',
		'tr' => '.bitrix24.com.tr',
		'br' => '.bitrix24.com.br',
		'ru' => '.bitrix24.ru',
		'la' => '.bitrix24.es',
		'kz' => '.bitrix24.kz',
		'by' => '.bitrix24.by',
		'jp' => '.bitrix24.jp',
		'cn' => '.bitrix24.cn',
	];

	/**
	 * Get font proxy uri by real uri.
	 *
	 * @param string $fontUri Font uri.
	 * @param string|null $region Region.
	 * @return string
	 * @throws Main\LoaderException
	 */
	public static function makeUri(string $fontUri, ?string $region = null): string
	{
		$uri = new Main\Web\Uri($fontUri);
		$domain = self::getMap($region)[$uri->getHost()] ?? null;
		if (!$domain)
		{
			return $fontUri;
		}

		return 'https://' . $domain . $uri->getPathQuery();
	}

	/**
	 * Get domain of fonts proxy-server.
	 *
	 * @param string|null $region Region.
	 * @return string
	 * @throws Main\LoaderException
	 */
	public static function resolveDomain(?string $region = null): string
	{
		$domain = null;
		
		$region = $region ?: Main\Application::getInstance()->getLicense()->getRegion();
		if (Main\Loader::includeModule('bitrix24'))
		{
			$domain = CBitrix24::getAreaConfig($region)['DEFAULT_DOMAIN'] ?? null;
		}
		
		if (!$domain)
		{
			$domain = self::$map[$region] ?? self::$map['en'];
		}

		return "fonts{$domain}";
	}

	/**
	 * Get map of domains.
	 * [Original domain => Proxy domain]
	 *
	 * @param string|null $region Region.
	 * @return array
	 * @throws Main\LoaderException
	 */
	public static function getMap(?string $region = null): array
	{
		$map = [];
		$targetDomain = self::resolveDomain($region);
		foreach (self::$sourceDomains as $sourceDomain)
		{
			$map[$sourceDomain] = $targetDomain;
		}

		return $map;
	}
}

