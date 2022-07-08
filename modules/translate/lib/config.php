<?php declare(strict_types = 1);

namespace Bitrix\Translate;

use Bitrix\Main;
use Bitrix\Translate;
use Bitrix\Main\Localization\Loc;

final class Config
{
	public const OPTION_INIT_FOLDERS = 'INIT_FOLDERS';
	public const OPTION_BUTTON_LANG_FILES = 'BUTTON_LANG_FILES';
	public const OPTION_BACKUP_FILES = 'BACKUP_FILES';
	public const OPTION_SORT_PHRASES = 'SORT_PHRASES';
	public const OPTION_DONT_SORT_LANGUAGES = 'DONT_SORT_LANGUAGES';
	public const OPTION_BACKUP_FOLDER = 'BACKUP_FOLDER';
	public const OPTION_EXPORT_CSV_DELIMITER = 'EXPORT_CSV_DELIMITER';
	public const OPTION_EXPORT_FOLDER = 'EXPORT_FOLDER';

	private const CACHE_TTL = 3600;

	/**
	 * Returns an module option.
	 *
	 * @param string $optionName Name of option.
	 *
	 * @return string|null
	 */
	public static function getOption(string $optionName): ?string
	{
		static $options = [];
		if (!isset($options[$optionName]))
		{
			$options[$optionName] = (string)Main\Config\Option::get(
				'translate',
				$optionName,
				self::getModuleDefault($optionName)
			);
		}

		return $options[$optionName] ?: null;
	}

	/**
	 * Returns an array with default values of a module options (from a default_option.php file).
	 *
	 * @param string $optionName Name of option.
	 *
	 * @return string|null
	 */
	public static function getModuleDefault(string $optionName): ?string 
	{
		static $defs;
		if ($defs === null)
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
	public static function getDefaultLanguages(): array
	{
		return ['ru', 'en', 'de'];
	}

	/**
	 * Returns true id server is in utf-8 mode. False - otherwise.
	 *
	 * @return boolean
	 */
	public static function isUtfMode(): bool 
	{
		static $flag;
		if ($flag === null)
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
	public static function getAllowedEncodings(): array 
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
	public static function getEncodingName(string $encoding): string 
	{
		static $mess;
		if ($mess === null)
		{
			$mess = Loc::loadLanguageFile(__FILE__);
			if (empty($mess))
			{
				$mess = Loc::loadLanguageFile(__FILE__, 'en');
			}
		}
		$encTitle = Loc::getMessage('TRANSLATE_ENCODING_'.mb_strtoupper(str_replace('-', '_', $encoding)));
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
	public static function getAliasEncoding(string $encoding): ?string 
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
	public static function getCultureEncoding(string $languageId): ?string
	{
		static $cultureEncoding;
		if ($cultureEncoding === null)
		{
			$cultureEncoding = [];
			$iterator = Main\Localization\CultureTable::getList([
				'select' => ['ID', 'CODE', 'CHARSET'],
				'cache' => ['ttl' => self::CACHE_TTL],
			]);
			while ($row = $iterator->fetch())
			{
				$cultureEncoding[mb_strtolower($row['CODE'])] = mb_strtolower($row['CHARSET']);
			}
		}

		return $cultureEncoding[$languageId] ?: null;
	}

	/**
	 * Returns list of all languages in the site settings.
	 *
	 * @return string[]
	 */
	public static function getLanguages(): array 
	{
		static $languages;
		if ($languages === null)
		{
			$languages = [];
			$iterator = Main\Localization\LanguageTable::getList([
				'select' => ['ID', 'SORT'],
				'order' => ['SORT' => 'ASC'],
				'cache' => ['ttl' => self::CACHE_TTL],
			]);
			while ($row = $iterator->fetch())
			{
				$languages[] = $row['ID'];
			}
		}

		return $languages;
	}

	/**
	 * Returns list of enabled languages in the site settings.
	 *
	 * @return string[]
	 */
	public static function getEnabledLanguages(): array 
	{
		static $languages;
		if ($languages === null)
		{
			$languages = [];
			$iterator = Main\Localization\LanguageTable::getList([
				'select' => ['ID', 'SORT'],
				'filter' => ['=ACTIVE' => 'Y'],
				'order' => ['SORT' => 'ASC'],
				'cache' => ['ttl' => self::CACHE_TTL],
			]);
			while ($row = $iterator->fetch())
			{
				$languages[] = $row['ID'];
			}
		}

		return $languages;
	}

	/**
	 * Returns list of language names from the site settings.
	 *
	 * @param string[] $languageIds Languages list to get name.
	 *
	 * @return array
	 */
	public static function getLanguagesTitle($languageIds): array 
	{
		static $cache = [];
		
		$cacheId = implode('-', $languageIds);
		if (!isset($cache[$cacheId]))
		{
			$cache[$cacheId] = [];

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
	public static function getAvailableLanguages(): array 
	{
		static $languages;
		if ($languages === null)
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
	public static function getAvailableDefaultLanguages(): array 
	{
		static $languages;
		if ($languages === null)
		{
			$languages = [];
			$langDirList = new Main\IO\Directory(Main\Application::getDocumentRoot(). '/bitrix/modules/main/lang/');
			foreach ($langDirList->getChildren() as $langDir)
			{
				$langId = $langDir->getName();
				if (in_array($langId, Translate\IGNORE_FS_NAMES, true) || !$langDir->isDirectory())
				{
					continue;
				}
				$languages[] = $langId;
			}
		}

		return $languages;
	}

	/**
	 * Returns list of available in system translation languages.
	 *
	 * @return string[]
	 */
	public static function getTranslationRepositoryLanguages(): array
	{
		static $languages;
		if ($languages === null)
		{
			$languages = [];
			if (Main\Localization\Translation::useTranslationRepository())
			{
				$langDirList = new Main\IO\Directory(Main\Localization\Translation::getTranslationRepositoryPath());
				foreach ($langDirList->getChildren() as $langDir)
				{
					$langId = $langDir->getName();
					if (in_array($langId, Translate\IGNORE_FS_NAMES, true) || !$langDir->isDirectory())
					{
						continue;
					}
					$languages[] = $langId;
				}
			}
		}

		return $languages;
	}

	/**
	 * Folders list allowed to translation edit.
	 *
	 * @return string[]
	 */
	public static function getInitPath(): array
	{
		/** @var string[] */
		static $initFolders;
		if ($initFolders === null)
		{
			$initFolders = [];
			$folders = (string)Main\Config\Option::get(
				'translate',
				self::OPTION_INIT_FOLDERS,
				Translate\Config::getModuleDefault(Translate\Config::OPTION_INIT_FOLDERS)
			);
			$folders = explode(',', trim($folders));
			foreach ($folders as $oneFolder)
			{
				if (!empty($oneFolder))
				{
					$oneFolder = Translate\IO\Path::normalize($oneFolder);
					$initFolders[] = '/'.ltrim($oneFolder, '/');
				}
			}
		}

		return $initFolders;
	}

	/**
	 * Returns the default translation folder path.
	 *
	 * @return string
	 */
	public static function getDefaultPath(): string
	{
		static $defaultPath;
		if ($defaultPath === null)
		{
			$folders = explode(',', self::getModuleDefault(self::OPTION_INIT_FOLDERS));
			$defaultPath = $folders[0];
		}

		return $defaultPath;
	}


	/**
	 * Returns setting of necessity to backup language files.
	 *
	 * @return bool
	 */
	public static function needToBackUpFiles(): bool
	{
		static $needToBackUpFiles;
		if ($needToBackUpFiles === null)
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
	public static function getBackupFolder(): string
	{
		static $backupFolder;
		if ($backupFolder === null)
		{
			$confOption = Main\Config\Option::get('translate', self::OPTION_BACKUP_FOLDER, '');
			if (!empty($confOption))
			{
				if (mb_strpos($confOption, '/') === 0)
				{
					$backupFolder = $confOption;
				}
				elseif (strncasecmp(PHP_OS, 'WIN', 3) === 0 && preg_match("#^[a-z]{1}:/#i", $confOption))
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
	public static function needToSortPhrases(): bool
	{
		static $needToSortPhrases;
		if ($needToSortPhrases === null)
		{
			$def = self::getModuleDefault(self::OPTION_SORT_PHRASES);
			$needToSortPhrases = (bool)(Main\Config\Option::get('translate', self::OPTION_SORT_PHRASES, $def) === 'Y');
		}

		return $needToSortPhrases;
	}

	/**
	 * Returns list of languages in that is unnecessary to sort phrases.
	 *
	 * @return string[]
	 */
	public static function getNonSortPhraseLanguages(): array
	{
		static $nonSortPhraseLanguages;
		if ($nonSortPhraseLanguages === null)
		{
			$nonSortPhraseLanguages = [];
			$def = self::getModuleDefault(self::OPTION_DONT_SORT_LANGUAGES);
			$nonSortPhraseLanguages = Main\Config\Option::get('translate', self::OPTION_DONT_SORT_LANGUAGES, $def);
			if (!is_array($nonSortPhraseLanguages))
			{
				$nonSortPhraseLanguages = explode(',', $nonSortPhraseLanguages);
			}
		}

		return $nonSortPhraseLanguages;
	}

	/**
	 * Returns disposition of the asset or export folder.
	 *
	 * @return string|null
	 */
	public static function getExportFolder(): ?string
	{
		static $exportFolder = -1;
		if ($exportFolder === -1)
		{
			$exportFolder = null;// '/bitrix/updates/_langs/';
			$confOption = Main\Config\Option::get('translate', self::OPTION_EXPORT_FOLDER, '');
			if (!empty($confOption))
			{
				if (mb_strpos($confOption, Main\Application::getDocumentRoot()) === 0)
				{
					$exportFolder = $confOption;
				}
				else
				{
					$exportFolder = Main\Application::getDocumentRoot(). '/'. $confOption;
				}
			}
		}

		return $exportFolder;
	}
}
