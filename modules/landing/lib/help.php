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
		'tr' => 'bitrix24.com.tr'
	);

	/**
	 * @var array Help url's ids.
	 */
	protected static $helpUrl = array(
		'SITE_LIMIT_REACHED' => array(
			'ru' => '6519197',
			'ua' => '6524403',
			'en' => '6588287',
			'de' => '6630821',
			'es' => '6529315',
			'br' => '7014601'
		),
		'LANDING_EDIT' => array(
			'ru' => 's93291',
			'ua' => 's94173',
			'en' => 's95157',
			'de' => 's95161',
			'es' => 's95265',
			'br' => 's99169'
		),
		'DOMAIN_EDIT' => array(
			'ru' => '6624333',
			'ua' => '6626953',
			'en' => '7389089',
			'de' => '6637101',
			'es' => '8479199',
			'br' => '8513557',
		),
		'GMAP_EDIT' => array(
			'ru' => '8203739',
			'ua' => '8223491',
			'en' => '8218073',
			'de' => '8208835',
			'es' => '8210537',
			'br' => '8234081',
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