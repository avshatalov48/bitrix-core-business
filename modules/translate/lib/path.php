<?php
namespace Bitrix\Translate;

use Bitrix\Main;


class Path
	extends Main\IO\Path
{
	/**
	 * Normalizes path splitter symbols.
	 *
	 * @param string $path Path to check.
	 *
	 * @return string
	 */
	public static function tidy($path)
	{
		return preg_replace("#[\\\\\\/]+#", '/', $path);
	}

	/**
	 * Checks if it is translation folder.
	 *
	 * @param string $path Path to check.
	 * @param bool $additionalCheck Preforms additional check.
	 *
	 * @return bool
	 */
	public static function isLangDir($path, $additionalCheck = false)
	{
		if (preg_match("#/lang/([^/]*?)(/|\$)#", $path, $match))
		{
			if (strpos($path, '/lang/'.$match[1].'/exec/') !== false)
			{
				return false;
			}
			if ($additionalCheck)
			{
				$arr = explode('/', $path);
				$langKey = array_search('lang', $arr) + 1;

				return array_key_exists($langKey, $arr) && strlen($arr[$langKey]) > 0;
			}

			return true;
		}

		return false;
	}

	/**
	 * Detects and returns language code from a path.
	 *
	 * @param string $path Path to check.
	 *
	 * @return string
	 */
	public static function extractLangId($path)
	{
		$arr = explode('/', $path);
		if (in_array('lang', $arr))
		{
			$langKey = array_search('lang', $arr) + 1;
			return $arr[$langKey];
		}
		return false;
	}

	/**
	 * Replaces language code in the path.
	 *
	 * @param string $path Path to check.
	 * @param string $langId Language code to add.
	 *
	 * @return string
	 */
	public static function replaceLangId($path, $langId)
	{
		return preg_replace("#^(.*?/lang/)([^/]*?)(/|$)#", "\\1$langId\\3", $path);
	}


	/**
	 * Removes language folder and code from path.
	 *
	 * @param string $path Path to check.
	 * @param string[] $langs Languages list.
	 *
	 * @return string
	 */
	public static function removeLangId($path, $langs = null)
	{
		static $defLangs = array();
		if (empty($langs))
		{
			if (empty($defLangs))
			{
				$defLangs = array_unique(array_merge(
					\Bitrix\Translate\Translation::getDefaultLanguages(),
					\Bitrix\Translate\Translation::getEnabledLanguages()
				));
			}
			$langs = $defLangs;
		}
		$arr = explode('/', $path);
		if (in_array('lang', $arr))
		{
			$langKey = array_search('lang', $arr) + 1;
			if (in_array($arr[$langKey], $langs))
			{
				unset($arr[$langKey]);
			}
			$path = implode('/', $arr);
		}

		return $path;
	}

	/**
	 * Adds language folder and code into path.
	 *
	 * @param string $path Path to check.
	 * @param string $langId Language code to add.
	 * @param string[] $langs Languages list.
	 *
	 * @return string
	 */
	public static function addLangId($path, $langId, $langs = null)
	{
		$pathTemp = self::removeLangId($path, $langs);

		$arr = explode('/', $pathTemp);
		if (in_array('lang', $arr))
		{
			$arr1 = array();
			foreach($arr as $d)
			{
				$arr1[] = $d;
				if ($d == 'lang')
				{
					$arr1[] = $langId;
				}
			}
			$path = implode('/', $arr1);
		}

		return $path;
	}
}
