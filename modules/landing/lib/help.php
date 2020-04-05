<?php
namespace Bitrix\Landing;

class Help
{
	const DEFAULT_ZONE_ID = 'en';

	/**
	 * @var array B24 domains.
	 */
	protected static $domains = array(
		'ru' => 'bitrix24.ru',
		'ua' => 'bitrix24.ua',
		'by' => 'bitrix24.by',
		'kz' => 'bitrix24.kz',
		'pl' => 'bitrix24.pl',
		'en' => 'bitrix24.com',
		'de' => 'bitrix24.de',
		'es' => 'bitrix24.es',
		'br' => 'bitrix24.com.br',
		'fr' => 'bitrix24.fr',
		'cn' => 'bitrix24.cn',
		'in' => 'bitrix24.in',
		'eu' => 'bitrix24.eu',
		'tr' => 'bitrix24.com.tr'
	);

	/**
	 * @var array Help url's ids.
	 */
	protected static $helpUrl = array(
		'SITE_LIMIT_REACHED' => array(
			'ru' => '6519197',
			'en' => '6588287',
			'es' => '6529315',
			'de' => '6630821',
			'br' => '7014601',
			'ua' => '6524403'
		),
		'LANDING_EDIT' => array(
			'ru' => 's93291'
		)
	);

	/**
	 * Gets url to help article by code.
	 * @param string $code Help code.
	 * @return string
	 */
	public static function getHelpUrl($code)
	{
		static $myZone = null;
		static $defaultZone = self::DEFAULT_ZONE_ID;

		if ($myZone === null)
		{
			$myZone = Manager::getZone();
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

		if ($helpId && $helpZone)
		{
			return 'https://helpdesk.' . self::$domains[$helpZone] .
					(
						(substr($helpId, 0, 1) == 's')
						? ('/#section' . substr($helpId, 1))
						: ('/open/' . $helpId . '/')
					);
		}

		return '';
	}

	/**
	 * Relace in content all help links by format #HELP_LINK_*CODE*#.
	 * @param string $content Some content.
	 * @return string
	 */
	public static function replaceHelpUrl($content)
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