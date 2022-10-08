<?
namespace Bitrix\UI;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class Util
 * @package Bitrix\UI
 */
class Util
{
	private const HELPDESK_DOMAIN = [
		'en' => 'https://helpdesk.bitrix24.com',
		'br' => 'https://helpdesk.bitrix24.com.br',
		'de' => 'https://helpdesk.bitrix24.de',
		'es' => 'https://helpdesk.bitrix24.es',
		'fr' => 'https://helpdesk.bitrix24.fr',
		'it' => 'https://helpdesk.bitrix24.it',
		'pl' => 'https://helpdesk.bitrix24.pl',
		'ru' => 'https://helpdesk.bitrix24.ru',
		'ua' => 'https://helpdesk.bitrix24.ua',
	];

	public static function getHelpdeskUrl($byLang = false)
	{
		$lang = LANGUAGE_ID;
		if (Loader::includeModule('bitrix24'))
		{
			$licensePrefix = \CBitrix24::getLicensePrefix();
			if(!$byLang || $licensePrefix === 'ua')
			{
				$lang = $licensePrefix;
			}
		}

		switch ($lang)
		{
			case "ru":
			case "by":
			case "kz":
				$helpdeskUrl = static::HELPDESK_DOMAIN['ru'];
				break;

			case "de":
				$helpdeskUrl = static::HELPDESK_DOMAIN['de'];
				break;

			case "ua":
				$helpdeskUrl = static::HELPDESK_DOMAIN['ua'];
				break;

			case "br":
				$helpdeskUrl = static::HELPDESK_DOMAIN['br'];
				break;

			case "fr":
				$helpdeskUrl = static::HELPDESK_DOMAIN['fr'];
				break;

			case "la":
				$helpdeskUrl = static::HELPDESK_DOMAIN['es'];
				break;

			case "pl":
				$helpdeskUrl = static::HELPDESK_DOMAIN['pl'];
				break;

			case "it":
				$helpdeskUrl = static::HELPDESK_DOMAIN['it'];
				break;

			default:
				$helpdeskUrl = static::HELPDESK_DOMAIN['en'];
		}

		return $helpdeskUrl;
	}

	/**
	 * Returns used domains.
	 *
	 * @return string[]
	 */
	public static function listDomain()
	{
		return array_values(static::HELPDESK_DOMAIN);
	}

	/**
	 * @param string $code article code.
	 * @return string
	 */
	public static function getArticleUrlByCode(string $code): ?string
	{
		if (preg_match('/([\w]+)/', $code, $matches))
		{
			$articleUrl = self::getHelpdeskUrl();
			$articleUrl .= '/open/code_' . $code . '/';

			return $articleUrl;
		}

		return null;
	}
}

