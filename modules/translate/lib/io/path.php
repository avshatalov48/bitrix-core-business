<?php
namespace Bitrix\Translate\IO;

use Bitrix\Main;
use Bitrix\Translate;

class Path extends Main\IO\Path
{
	/**
	 * Normalizes path splitter symbols.
	 *
	 * @param string $path Path to check.
	 *
	 * @return string
	 */
	public static function tidy(string $path): string
	{
		$modifier = Translate\Config::isUtfMode() ? 'u' : '';

		return \preg_replace("#[\\\\\\/]+#".$modifier, self::DIRECTORY_SEPARATOR, $path);
	}

	/**
	 * Removes unsecured path parts.
	 *
	 * @param string $path Path to check.
	 *
	 * @return string
	 */
	public static function secure(string $path): string
	{
		$modifier = Translate\Config::isUtfMode() ? 'u' : '';

		return \preg_replace("#\.\.+[\/\\\]+#i".$modifier, '', $path);
	}

	/**
	 * Checks if it is translation folder.
	 *
	 * @param string $path Path to check.
	 * @param bool $additionalCheck Preforms additional check.
	 *
	 * @return bool
	 */
	public static function isLangDir(string $path, bool $additionalCheck = false): bool
	{
		$modifier = Translate\Config::isUtfMode() ? 'u' : '';
		if (\preg_match("#/lang/([^/]*?)(/|\$)#".$modifier, $path, $match))
		{
			foreach (Translate\IGNORE_LANG_NAMES as $check)
			{
				if (\mb_strpos($path, '/lang/'.$match[1].'/'.$check.'/') !== false)
				{
					return false;
				}
			}
			if ($additionalCheck)
			{
				$arr = \explode(self::DIRECTORY_SEPARATOR, $path);
				$langKey = \array_search('lang', $arr) + 1;

				return \array_key_exists($langKey, $arr) && $arr[$langKey] <> '';
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
	 * @return string|null
	 */
	public static function extractLangId(string $path): ?string
	{
		$arr = \explode(self::DIRECTORY_SEPARATOR, $path);
		$pos = \array_search('lang', $arr);
		if ($pos !== false && !empty($arr[$pos + 1]))
		{
			return $arr[$pos + 1];
		}

		return null;
	}

	/**
	 * Replaces language code in the path.
	 *
	 * @param string $path Path to check.
	 * @param string $langId Language code to add.
	 *
	 * @return string
	 */
	public static function replaceLangId(string $path, string $langId): string
	{
		$modifier = Translate\Config::isUtfMode() ? 'u' : '';

		return \preg_replace("#^(.*?/lang/)([^/]*?)(/|$)#".$modifier, "\\1$langId\\3", $path);
	}


	/**
	 * Removes language folder and code from path.
	 *
	 * @param string $path Path to check.
	 * @param string[] $langs Languages list.
	 *
	 * @return string
	 */
	public static function removeLangId(string $path, ?array $langs = null): string
	{
		static $defLangs = [];
		if (empty($langs))
		{
			if (empty($defLangs))
			{
				$defLangs = \array_unique(\array_merge(
					Translate\Config::getDefaultLanguages(),
					Translate\Config::getEnabledLanguages()
				));
			}
			$langs = $defLangs;
		}
		$arr = \explode(self::DIRECTORY_SEPARATOR, $path);
		if (\in_array('lang', $arr))
		{
			$langKey = \array_search('lang', $arr) + 1;
			if (\in_array($arr[$langKey], $langs))
			{
				unset($arr[$langKey]);
			}
			$path = \implode(self::DIRECTORY_SEPARATOR, $arr);
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
	public static function addLangId(string $path, string $langId, ?array $langs = null): string
	{
		$pathTemp = self::removeLangId($path, $langs);

		$arr = \explode('/', $pathTemp);
		if (\in_array('lang', $arr))
		{
			$arr1 = array();
			foreach($arr as $d)
			{
				$arr1[] = $d;
				if ($d === 'lang')
				{
					$arr1[] = $langId;
				}
			}
			$path = \implode('/', $arr1);
		}

		return $path;
	}

	/**
	 * Checks existence or creates of the directory path.
	 *
	 * @param string $path Path to check.
	 *
	 * @return bool
	 */
	public static function checkCreatePath(string $path): bool
	{
		$path = self::normalize($path);
		$path = \rtrim($path, self::DIRECTORY_SEPARATOR);

		if($path == '')
		{
			//current folder always exists
			return true;
		}

		if (!\file_exists($path))
		{
			return \mkdir($path, \BX_DIR_PERMISSIONS, true);
		}

		return \is_dir($path);
	}

	/**
	 * Checks if it is php file.
	 *
	 * @param string $path Path to check.
	 * @param bool $cechExistence
	 * @return bool
	 */
	public static function isPhpFile(string $path, bool $checkExistence = false): bool
	{
		return $checkExistence
			? (\mb_substr($path, -4) === '.php') && \is_file($path)
			: (\mb_substr($path, -4) === '.php');
	}
}
