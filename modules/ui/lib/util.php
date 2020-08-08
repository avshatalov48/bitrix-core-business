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
				$helpdeskUrl = "https://helpdesk.bitrix24.ru";
				break;

			case "de":
				$helpdeskUrl = "https://helpdesk.bitrix24.de";
				break;

			case "ua":
				$helpdeskUrl = "https://helpdesk.bitrix24.ua";
				break;

			case "br":
				$helpdeskUrl = "https://helpdesk.bitrix24.com.br";
				break;

			case "fr":
				$helpdeskUrl = "https://helpdesk.bitrix24.fr";
				break;

			case "la":
				$helpdeskUrl = "https://helpdesk.bitrix24.es";
				break;

			case "pl":
				$helpdeskUrl = "https://helpdesk.bitrix24.pl";
				break;

			default:
				$helpdeskUrl = "https://helpdesk.bitrix24.com";
		}

		return $helpdeskUrl;
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

