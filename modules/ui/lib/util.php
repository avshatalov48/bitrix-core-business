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
		if (Loader::includeModule('bitrix24') && !$byLang)
		{
			$lang = \CBitrix24::getLicensePrefix();
		}
		else
		{
			$lang = LANGUAGE_ID;
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

