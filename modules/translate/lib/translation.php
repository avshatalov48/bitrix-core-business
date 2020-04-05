<?php
namespace Bitrix\Translate;

use Bitrix\Main;
use Bitrix\Translate;
use Bitrix\Main\Localization\Loc;


class Translation
	extends Main\Localization\Translation
{
	/**
	 * Returns the default translation language list.
	 *
	 * @return string[]
	 */
	public static function getDefaultLanguages()
	{
		return array('ru', 'en', 'de', 'ua');
	}

	/**
	 * Returns true id server is in utf-8 mode. False - otherwise.
	 *
	 * @return boolean
	 */
	public static function isUtfMode()
	{
		return Main\Application::isUtfMode() || defined('BX_UTF');
	}

	/**
	 * Returns list of allowed to operate charset encodings.
	 *
	 * @return string[]
	 */
	public static function getAllowedEncodings()
	{
		return Translate\ENCODINGS;
	}

	/**
	 * Returns list of allowed to operate charset encodings.
	 *
	 * @param string $encoding Encoding to check.
	 *
	 * @return string
	 */
	public static function getEncodingName($encoding)
	{
		static $mess;
		if (empty($mess))
		{
			$mess = Loc::loadLanguageFile(__FILE__);
			if (empty($mess))
			{
				$mess = Loc::loadLanguageFile(__FILE__, 'en');
			}
		}
		$code = 'TRANSLATE_ENCODING_'. strtoupper(str_replace('-', '_', $encoding));
		$encTitle = Loc::getMessage($code);
		if (!empty($encTitle))
		{
			$encTitle .= " ($encoding)";
		}
		else
		{
			$encTitle = $encoding;
			$encAlias = self::getAliasEncoding($encoding);
			if ($encAlias)
			{
				$encTitle .= " ($encAlias)";
			}
		}

		return $encTitle;
	}

	/**
	 * Returns alias of encoding.
	 *
	 * @param string $encoding Encoding to check.
	 *
	 * @return string|null
	 */
	public static function getAliasEncoding($encoding)
	{
		static $aliasEncoding = array(
			'windows-1250' => 'iso-8859-2',
			'windows-1252' => 'iso-8859-1',
		);
		if(isset($aliasEncoding[$encoding]))
		{
			return $aliasEncoding[$encoding];
		}

		$alias = array_search($encoding, $aliasEncoding);
		if ($alias !== false)
		{
			return $alias;
		}

		return null;
	}

	/**
	 * Returns language encoding from site settings.
	 *
	 * @param string $languageId Language id to check.
	 *
	 * @return string|null
	 */
	public static function getCultureEncoding($languageId)
	{
		static $cultureEncoding = array();
		if (empty($cultureEncoding))
		{
			$iterator = Main\Localization\CultureTable::getList(array(
				'select' => array('ID', 'CODE', 'CHARSET')
			));
			while ($row = $iterator->fetch())
			{
				$cultureEncoding[strtolower($row['CODE'])] = strtolower($row['CHARSET']);
			}
		}

		return $cultureEncoding[$languageId] ?: null;
	}

	/**
	 * Returns list of all languages in the site settings.
	 *
	 * @return string[]
	 */
	public static function getLanguages()
	{
		static $langs = [];
		if (empty($langs))
		{
			$iterator = Main\Localization\LanguageTable::getList(array(
				'select' => array('ID', 'SORT'),
				'order' => array('SORT' => 'ASC'),
			));
			while ($row = $iterator->fetch())
			{
				$langs[] = $row['ID'];
			}
		}

		return $langs;
	}

	/**
	 * Returns list of enabled languages in the site settings.
	 *
	 * @return string[]
	 */
	public static function getEnabledLanguages()
	{
		static $langs = [];
		if (empty($langs))
		{
			$iterator = Main\Localization\LanguageTable::getList(array(
				'select' => array('ID', 'SORT'),
				'filter' => array('=ACTIVE' => 'Y'),
				'order' => array('SORT' => 'ASC'),
			));
			while ($row = $iterator->fetch())
			{
				$langs[] = $row['ID'];
			}
		}

		return $langs;
	}


	/**
	 * Returns list of available in system translation languages.
	 *
	 * @return string[]
	 */
	public static function getAvailableLanguages()
	{
		static $langs = [];
		if (empty($langs))
		{
			$langs = array_unique(array_merge(
				self::getAvailableDefaultLanguages(),
				self::getTranslationRepositoryLanguages()
			));
		}

		return $langs;
	}

	/**
	 * Returns list of available default translation languages.
	 *
	 * @return string[]
	 */
	public static function getAvailableDefaultLanguages()
	{
		static $langs = [];
		if (empty($langs))
		{
			$langDirList = new Main\IO\Directory(Main\Application::getDocumentRoot(). '/bitrix/modules/main/lang/');
			foreach ($langDirList->getChildren() as $langDir)
			{
				$langId = $langDir->getName();
				if (in_array($langId, Translate\IGNORE_FS_NAMES) || !$langDir->isDirectory())
				{
					continue;
				}
				$langs[] = $langId;
			}
		}

		return $langs;
	}

	/**
	 * Returns list of available in system translation languages.
	 *
	 * @return string[]
	 */
	public static function getTranslationRepositoryLanguages()
	{
		static $langs = [];
		if (empty($langs))
		{
			if (Main\Localization\Translation::useTranslationRepository())
			{
				$langDirList = new Main\IO\Directory(Main\Localization\Translation::getTranslationRepositoryPath());
				foreach ($langDirList->getChildren() as $langDir)
				{
					$langId = $langDir->getName();
					if (in_array($langId, Translate\IGNORE_FS_NAMES) || !$langDir->isDirectory())
					{
						continue;
					}
					$langs[] = $langId;
				}
			}
		}

		return $langs;
	}
}
