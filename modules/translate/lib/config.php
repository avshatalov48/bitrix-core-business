<?php

namespace Bitrix\Translate;

use Bitrix\Main;
use Bitrix\Translate;
use Bitrix\Main\Localization\Loc;

final class Config
{
	const OPTION_INIT_FOLDERS = 'INIT_FOLDERS';
	const OPTION_BUTTON_LANG_FILES = 'BUTTON_LANG_FILES';
	const OPTION_BACKUP_FILES = 'BACKUP_FILES';
	const OPTION_SORT_PHRASES = 'SORT_PHRASES';
	const OPTION_DONT_SORT_LANGUAGES = 'DONT_SORT_LANGUAGES';
	const OPTION_BACKUP_FOLDER = 'BACKUP_FOLDER';
	const OPTION_EXPORT_CSV_DELIMITER = 'EXPORT_CSV_DELIMITER';

	private const CACHE_TTL = 3600;

	/**
	 * Returns an module option.
	 *
	 * @param string $optionName Name of option.
	 *
	 * @return mixed|null
	 */
	public static function getOption($optionName)
	{
		static $options;
		if (empty($options[$optionName]))
		{
			$options[$optionName] = Main\Config\Option::get(
				'translate',
				$optionName,
				Translate\Config::getModuleDefault($optionName)
			);
		}

		return $options[$optionName] ?: null;
	}

	/**
	 * Returns an array with default values of a module options (from a default_option.php file).
	 *
	 * @param string $optionName Name of option.
	 *
	 * @return mixed|null
	 */
	public static function getModuleDefault($optionName)
	{
		static $defs;
		if (empty($defs))
		{
			$defs = Main\Config\Option::getDefaults('translate');
		}

		return $defs[$optionName] ?: null;
	}

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
		static $flag;
		if (empty($flag))
		{
			$flag = Main\Application::isUtfMode() || defined('BX_UTF');
		}
		return $flag;
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
			$iterator = Main\Localization\CultureTable::getList([
				'select' => ['ID', 'CODE', 'CHARSET'],
				'cache' => ['ttl' => self::CACHE_TTL],
			]);
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
			$iterator = Main\Localization\LanguageTable::getList([
				'select' => ['ID', 'SORT'],
				'order' => ['SORT' => 'ASC'],
				'cache' => ['ttl' => self::CACHE_TTL],
			]);
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
			$iterator = Main\Localization\LanguageTable::getList([
				'select' => ['ID', 'SORT'],
				'filter' => ['=ACTIVE' => 'Y'],
				'order' => ['SORT' => 'ASC'],
				'cache' => ['ttl' => self::CACHE_TTL],
			]);
			while ($row = $iterator->fetch())
			{
				$langs[] = $row['ID'];
			}
		}

		return $langs;
	}

	/**
	 * Returns list of language names from the site settings.
	 *
	 * @param string[] $languageIds Languages list to get name.
	 *
	 * @return array
	 */
	public static function getLanguagesTitle($languageIds)
	{
		static $cache = array();
		$cacheId = implode('-', $languageIds);
		if (!isset($cache[$cacheId]))
		{
			$cache[$cacheId] = array();

			$iterator = Main\Localization\LanguageTable::getList([
				'select' => ['ID', 'NAME'],
				'filter' => [
					'ID' => $languageIds,
					'=ACTIVE' => 'Y'
				],
				'order' => ['SORT' => 'ASC'],
				'cache' => ['ttl' => self::CACHE_TTL],
			]);
			while ($row = $iterator->fetch())
			{
				$cache[$cacheId][$row['ID']] = $row['NAME'];
			}
		}

		return $cache[$cacheId];
	}

	/**
	 * Returns list of available in system translation languages.
	 *
	 * @return string[]
	 */
	public static function getAvailableLanguages()
	{
		static $languages = [];
		if (empty($languages))
		{
			$languages = array_unique(array_merge(
				self::getAvailableDefaultLanguages(),
				self::getTranslationRepositoryLanguages()
			));
		}

		return $languages;
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

	/**
	 * Folders list allowed to translation edit.
	 *
	 * @return string[]
	 */
	public static function getInitPath()
	{
		/** @var string[]  */
		static $initFolders;
		if (empty($initFolders))
		{
			$initFolders = array();
			$folders = trim((string)Main\Config\Option::get('translate', self::OPTION_INIT_FOLDERS, self::getDefaultPath()));
			$folders = explode(',', $folders);
			foreach ($folders as $oneFolder)
			{
				$oneFolder = Translate\IO\Path::normalize($oneFolder);

				$initFolders[] = '/'. ltrim($oneFolder, '/');
			}
		}

		return $initFolders;
	}

	/**
	 * Returns the default translation folder path.
	 *
	 * @return string
	 */
	public static function getDefaultPath()
	{
		return self::getModuleDefault(self::OPTION_INIT_FOLDERS);
	}

	/**
	 * Allows calculate difference.
	 *
	 * @return bool
	 */
	public static function allowCalculateDifference()
	{
		static $autoCalculateDifference;
		if (empty($autoCalculateDifference))
		{
			$autoCalculateDifference = (string)Main\Config\Option::get('translate', 'AUTO_CALCULATE', 'N') === 'Y';
		}

		return $autoCalculateDifference;
	}

	/**
	 * Returns setting of necessity to backup language files.
	 *
	 * @return bool
	 */
	public static function needToBackUpFiles()
	{
		static $needToBackUpFiles;
		if (empty($needToBackUpFiles))
		{
			$def = self::getModuleDefault(self::OPTION_BACKUP_FILES);
			$needToBackUpFiles = (bool)(Main\Config\Option::get('translate', self::OPTION_BACKUP_FILES, $def) === 'Y');
		}

		return $needToBackUpFiles;
	}

	/**
	 * Returns disposition of backup folder for backuped language files.
	 *
	 * @return string
	 */
	public static function getBackupFolder()
	{
		static $backupFolder;
		if (empty($backupFolder))
		{
			$confOption = Main\Config\Option::get('translate', self::OPTION_BACKUP_FOLDER, '');
			if (!empty($confOption))
			{
				if (strpos($confOption, '/') === 0)
				{
					$backupFolder = $confOption;
				}
				elseif (strncasecmp(PHP_OS, 'WIN', 3) == 0 && preg_match("#^[a-z]{1}:/#i", $confOption))
				{
					$backupFolder = $confOption;
				}
				else
				{
					$backupFolder = Main\Application::getDocumentRoot(). '/'. $confOption;
				}
			}
			else
			{
				$defOption = self::getModuleDefault(self::OPTION_BACKUP_FOLDER);
				if (empty($defOption))
				{
					$defOption = 'bitrix/backup/translate/';
				}
				$backupFolder = Main\Application::getDocumentRoot(). '/'. $defOption;
			}
		}

		return $backupFolder;
	}

	/**
	 * Returns setting of necessity to sort phrases in language files.
	 *
	 * @return bool
	 */
	public static function needToSortPhrases()
	{
		static $needToSortPhrases;
		if (empty($needToSortPhrases))
		{
			$def = self::getModuleDefault(self::OPTION_SORT_PHRASES);
			$needToSortPhrases = (bool)(Main\Config\Option::get('translate', self::OPTION_SORT_PHRASES, $def) === 'Y');
		}

		return $needToSortPhrases;
	}

	/**
	 * Returns list of languages in that is unnessary to sort phrases.
	 *
	 * @return string[]
	 */
	public static function getNonSortPhraseLanguages()
	{
		static $nonSortPhraseLanguages = array();
		if (empty($nonSortPhraseLanguages))
		{
			$def = self::getModuleDefault(self::OPTION_DONT_SORT_LANGUAGES);
			$nonSortPhraseLanguages = Main\Config\Option::get('translate', self::OPTION_DONT_SORT_LANGUAGES, $def);
			if (!is_array($nonSortPhraseLanguages))
			{
				$nonSortPhraseLanguages = explode(',', $nonSortPhraseLanguages);
			}
		}

		return $nonSortPhraseLanguages;
	}
}
