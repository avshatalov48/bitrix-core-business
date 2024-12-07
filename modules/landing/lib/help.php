<?php
namespace Bitrix\Landing;

use Bitrix\Main\Loader;
use Bitrix\UI\Util;

class Help
{
	public const DEFAULT_ZONE_ID = 'ru';

	/**
	 * @var array B24 domains.
	 */
	protected static array $domains = [
		'ru' => 'bitrix24.ru',
		'by' => 'bitrix24.by',
		'kz' => 'bitrix24.kz',
		'ua' => 'bitrix24.ua',
		'en' => 'bitrix24.com',
		'de' => 'bitrix24.de',
		'es' => 'bitrix24.es',
		'br' => 'bitrix24.com.br',
		'pl' => 'bitrix24.pl',
		'fr' => 'bitrix24.fr',
		'cn' => 'bitrix24.cn',
		'in' => 'bitrix24.in',
		'eu' => 'bitrix24.eu',
		'tr' => 'bitrix24.com.tr',
		'it' => 'bitrix24.it',
		'id' => 'bitrix24.id',
		'vn' => 'bitrix24.vn',
		'jp' => 'bitrix24.jp'
	];

	/**
	 * @var array Help set url ids.
	 */
	protected static array $helpUrl = [
		'SITE_LIMIT_REACHED' => [
			'ru' => '6519197',
		],
		'LANDING_EDIT' => [
			'ru' => 's105667',
		],
		'DOMAIN_EDIT' => [
			'ru' => '6624333',
		],
		'DOMAIN_BITRIX24' => [
			'ru' => '11341354'
		],
		'COOKIES_EDIT' => [
			'ru' => '12297162',
		],
		'DOMAIN_FREE' => [
			'ru' => '11341378',
		],
		'GMAP_EDIT' => [
			'ru' => '8203739',
		],
		'PIXEL' => [
			'ru' => '9022893',
		],
		'GTM' => [
			'ru' => '9488927',
		],
		'GACOUNTER' => [
			'ru' => '13063040',
		],
		'META_GOOGLE_VERIFICATION' => [
			'ru' => '7908779',
		],
		'DYNAMIC_BLOCKS' => [
			'ru' => '10104989',
		],
		'YACOUNTER' => [
			'ru' => '9494147'
		],
		'META_YANDEX_VERIFICATION' => [
			'ru' => '7919271'
		],
		'SPEED' => [
			'ru' => '11565144',
		],
		'FORM_EDIT' => [
			'ru' => '12619286',
		],
		'FORM_GENERAL' => [
			'ru' => '6875449',
		],
		'WIDGET_GENERAL' => [
			'ru' => '6986667',
		],
		'FREE_MESSAGES' => [
			'ru' => '13655934'
		],
		'FIRST_ORDER_REQUIREMENTS' => [
			'ru' => '15732254'
		],
		'KNOWLEDGE_EXTENSION' => [
			'ru' => '11409302',
		],
		'B24BUTTON' => [
			'ru' => '17013614',
		],
		'SHOP1C' => [
			'ru' => '19613828',
		],
		'WIDGET_LIVEFEED' => [
			'ru' => '21379432',
		],
	];

	/**
	 * Gets domain's array.
	 * @return array
	 */
	public static function getDomains(): array
	{
		return self::$domains;
	}

	/**
	 * Gets help id and help zone by code.
	 * @param string $code Help code.
	 * @param string|null $zone Help code zone (force mode).
	 * @return array
	 */
	public static function getHelpData(string $code, ?string $zone = null): array
	{
		static $myZone = null;
		static $defaultZone = self::DEFAULT_ZONE_ID;

		if ($zone && isset(self::$helpUrl[$code][$zone]))
		{
			return [self::$helpUrl[$code][$zone], $zone];
		}

		if ($myZone === null)
		{
			$myZone = Manager::getZone();
		}

		if ($myZone === 'by' || $myZone === 'kz')
		{
			$myZone = 'ru';
		}

		$helpId = 0;
		$helpZone = '';

		if (isset(self::$helpUrl[$code]))
		{
			if (isset(self::$helpUrl[$code][$myZone]))
			{
				$helpId = self::$helpUrl[$code][$myZone];
				$helpZone = $myZone;
			}
			elseif (isset(self::$helpUrl[$code][$defaultZone]))
			{
				$helpId = self::$helpUrl[$code][$defaultZone];
				$helpZone = $defaultZone;
			}
		}

		return [$helpId, $helpZone];
	}

	/**
	 * Gets url to help article by code.
	 * @param string $code Help code.
	 * @return string
	 */
	public static function getHelpUrl(string $code): string
	{
		if (isset(self::$helpUrl[$code]['ru']))
		{
			$url = self::getHelpArticleUrl(self::$helpUrl[$code]['ru']);
			if ($url !== '')
			{
				return $url;
			}
		}

		return '';
	}

	/**
	 *  Gets url to help article by code from ui\util.
	 * @param string $code .code
	 * @return string
	 */
	public static function getHelpArticleUrl(string $code): string
	{
		if (Loader::includeModule('ui'))
		{
			return Util::getArticleUrlByCode($code);
		}

		return '';
	}

	/**
	 * Replaces in content all help links by format #HELP_LINK_*CODE*#.
	 * @param string $content Some content.
	 * @return string
	 * @deprecated since 24.0.0
	 */
	public static function replaceHelpUrl(string $content): string
	{
		return preg_replace_callback(
			'/#HELP_LINK_([\w]+)#/',
			function($match)
			{
				return \Bitrix\Landing\Help::getHelpUrl($match[1]);
			},
			$content
		);
	}
}