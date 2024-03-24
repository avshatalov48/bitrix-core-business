<?php

namespace Bitrix\Rest;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main;

class Lang
{
	private const DEFAULT_LANG = 'en';
	private static $languageList;
	private const KEY_LANG_ALL = 'LANG_ALL';

	/**
	 * Return list of languages
	 * @return array
	 * @throws Main\LoaderException
	 */
	public static function listLanguage(): array
	{
		if (static::$languageList === null)
		{
			static::$languageList = [
				LANGUAGE_ID,
				Loc::getDefaultLang(LANGUAGE_ID),
			];
			$licenseLang = null;
			if (Main\Loader::includeModule('bitrix24'))
			{
				$licenseLang = \CBitrix24::getLicensePrefix();
			}
			else
			{
				$dbSites = \CSite::getList(
					'sort',
					'asc',
					[
						'DEFAULT' => 'Y',
						'ACTIVE' => 'Y'
					]
				);
				if ($site = $dbSites->fetch() && !empty($site['LANGUAGE_ID']))
				{
					$licenseLang = $site['LANGUAGE_ID'];
				}
			}

			if($licenseLang === null)
			{
				$licenseLang = static::DEFAULT_LANG;
			}

			static::$languageList[] = $licenseLang;
		}

		return static::$languageList;
	}

	public static function mergeFromLangAll($data)
	{
		$result = [];
		if (!empty($data[static::KEY_LANG_ALL]))
		{
			$useLang = false;
			$langList = static::listLanguage();
			foreach ($langList as $lang)
			{
				if (is_array($data[static::KEY_LANG_ALL][$lang]))
				{
					$useLang = $lang;
					break;
				}
			}

			if ($useLang !== false)
			{
				$result = $data[static::KEY_LANG_ALL][$useLang];
			}
			else
			{
				$item = reset($data[static::KEY_LANG_ALL]);
				if (is_array($item))
				{
					$result = $item;
				}
			}
		}

		return array_merge($data, $result);
	}

	/**
	 * @param array $param
	 * @param string[] $fieldList
	 * @param array $defaultValues
	 *
	 * @return array
	 * @throws Main\LoaderException
	 */
	public static function fillCompatibility(array $param, array $fieldList, array $defaultValues = []) : array
	{
		$result = [];

		$langList = static::listLanguage();
		$langDefault = reset($langList);

		if (empty($param[static::KEY_LANG_ALL]))
		{
			foreach ($fieldList as $key)
			{
				$value = trim($param[$key] ?? '');
				if ($value !== '')
				{
					$result[static::KEY_LANG_ALL][$langDefault][$key] = $value;
					$result[$key] = trim($param[$key]);
				}
				elseif (isset($defaultValues[$key]) && $defaultValues[$key])
				{
					$result[static::KEY_LANG_ALL][$langDefault][$key] = $defaultValues[$key];
					$result[$key] = $defaultValues[$key];
				}
			}
		}
		else
		{
			foreach ($param[static::KEY_LANG_ALL] as $langCode => $langItem)
			{
				foreach ($fieldList as $field)
				{
					$value = trim($langItem[$field] ?? '');
					if ($value !== '')
					{
						$result[static::KEY_LANG_ALL][$langCode][$field] = $value;
					}
					elseif ($defaultValues[$field])
					{
						$result[static::KEY_LANG_ALL][$langDefault][$field] = $defaultValues[$field];
					}
				}
			}
			$result = static::mergeFromLangAll($result);
		}

		if (empty($result[static::KEY_LANG_ALL]) && !empty($defaultValues))
		{
			$result[static::KEY_LANG_ALL][$langDefault] = $defaultValues;
		}

		return $result;
	}
}