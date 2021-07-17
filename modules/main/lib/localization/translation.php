<?php
namespace Bitrix\Main\Localization;

use Bitrix\Main;
use Bitrix\Main\IO\Path;
use Bitrix\Main\Context;
use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Text\Encoding;

class Translation
{
	/** @var boolean */
	private static $allowConvertEncoding = null;
	/** @var boolean */
	private static $useTranslationRepository = null;
	/** @var string */
	private static $translationRepositoryPath = null;
	/** @var string */
	private static $currentEncoding = null;
	/** @var bool[] */
	private static $needConvertEncoding = array();
	/** @var array */
	private static $map = array();

	const CACHE_ID = 'TranslationLoadMapCache';
	const CACHE_TTL = 3600;

	/**
	 * Returns true if language translation is one of the default translation.
	 *
	 * @param string $lang Language code to check.
	 * @return bool
	 */
	public static function isDefaultTranslationLang($lang)
	{
		return
			($lang === 'ru') ||
			($lang === 'en') ||
			($lang === 'de') ||
			($lang === 'ua');
	}

	/**
	 * Returns encoding of source lang file in the translation repository.
	 *
	 * @param string $lang Language code to retrieve encoding.
	 * @return string
	 */
	public static function getDefaultTranslationEncoding($lang)
	{
		static $sourceEncoding = array(
			'ru' => 'windows-1251',
			'en' => 'iso-8859-1',
			'de' => 'iso-8859-15',
			'ua' => 'windows-1251',
		);
		if(isset($sourceEncoding[$lang]))
		{
			return $sourceEncoding[$lang];
		}

		return 'utf-8';
	}


	/**
	 * Returns encoding of source lang file in the translation repository.
	 *
	 * @param string $lang Language code to retrieve encoding.
	 * @return string
	 */
	public static function getSourceEncoding($lang)
	{
		$encoding = '';
		if (self::useTranslationRepository() || self::allowConvertEncoding())
		{
			if (self::isDefaultTranslationLang($lang))
			{
				$encoding = self::getDefaultTranslationEncoding($lang);
			}
			else
			{
				$encoding = 'utf-8';
			}
		}
		elseif (Main\Application::isUtfMode() || defined('BX_UTF'))
		{
			$encoding = 'utf-8';
		}

		if (empty($encoding))
		{
			$culture = Main\Localization\CultureTable::getRow(array('filter' => array('=CODE' => $lang)));
			if ($culture)
			{
				$encoding = $culture['CHARSET'];
			}
		}

		if (empty($encoding))
		{
			$encoding = self::getCurrentEncoding();
		}

		return $encoding;
	}


	/**
	 * Sets current encoding.
	 *
	 * @param  string $encoding Charset encoding.
	 * @return void
	 */
	public static function setCurrentEncoding($encoding)
	{
		self::$currentEncoding = $encoding;
	}

	/**
	 * Returns current encoding.
	 *
	 * @return string
	 */
	public static function getCurrentEncoding()
	{
		if (self::$currentEncoding === null)
		{
			$encoding = null;
			// site settings
			if (Main\Application::isUtfMode() || defined('BX_UTF'))
			{
				$encoding = 'utf-8';
			}
			elseif (defined('SITE_CHARSET') && (SITE_CHARSET <> ''))
			{
				$encoding = SITE_CHARSET;
			}
			elseif (defined('LANG_CHARSET') && (LANG_CHARSET <> ''))
			{
				$encoding = LANG_CHARSET;
			}
			else
			{
				$context = Context::getCurrent();
				if ($context instanceof Main\Context)
				{
					$culture = $context->getCulture();
					if ($culture instanceof Context\Culture)
					{
						$encoding = $culture->getCharset();
					}
				}
			}
			// default settings
			if ($encoding === null)
			{
				if (Configuration::getValue('default_charset') !== null)
				{
					$encoding = Configuration::getValue('default_charset');
				}
				elseif (defined('BX_DEFAULT_CHARSET') && (BX_DEFAULT_CHARSET <> ''))
				{
					$encoding = BX_DEFAULT_CHARSET;
				}
				else
				{
					$encoding = 'windows-1251';
				}
			}

			self::$currentEncoding = mb_strtolower($encoding);
		}

		return self::$currentEncoding;
	}

	/**
	 * Tells if need to convert encoding.
	 *
	 * @param string $language Language code.
	 * @param string $targetEncoding Target encoding.
	 *
	 * @return bool
	 */
	public static function needConvertEncoding($language, $targetEncoding = null)
	{
		if (!isset(self::$needConvertEncoding[$language]) || self::$needConvertEncoding[$language] === null)
		{
			self::$needConvertEncoding[$language] = false;

			if (self::allowConvertEncoding())
			{
				if ($targetEncoding === null)
				{
					$targetEncoding = self::getCurrentEncoding();
				}
				$sourceEncoding = self::getSourceEncoding($language);
				self::$needConvertEncoding[$language] = ($targetEncoding != $sourceEncoding);
			}
		}

		return self::$needConvertEncoding[$language];
	}


	/**
	 * Tells if need to convert encoding for certain file.
	 *
	 * @param string $langFile Language file path.
	 *
	 * @return bool
	 */
	public static function checkPathRestrictionConvertEncoding($langFile)
	{
		$needConvert = false;
		if (self::allowConvertEncoding())
		{
			if (self::getDeveloperRepositoryPath() !== null)
			{
				$needConvert = (stripos($langFile, self::getDeveloperRepositoryPath()) === 0);
			}
			if (!$needConvert && self::useTranslationRepository())
			{
				$needConvert = (stripos($langFile, self::getTranslationRepositoryPath()) === 0);
			}
		}

		return $needConvert;
	}

	/**
	 * Tells if need to use translation repository.
	 *
	 * @return bool
	 */
	public static function useTranslationRepository()
	{
		if (self::$useTranslationRepository === null)
		{
			self::$useTranslationRepository = false;

			if(self::getTranslationRepositoryPath() !== null)
			{
				self::$useTranslationRepository = true;
			}
		}

		return self::$useTranslationRepository;
	}

	/**
	 * Returns path to translation repository.
	 *
	 * @return string
	 */
	public static function getTranslationRepositoryPath()
	{
		if(self::$translationRepositoryPath === null)
		{
			$config = Configuration::getValue('translation');

			if ($config !== null && !empty($config['translation_repository']))
			{
				$translationRepositoryPath = realpath($config['translation_repository']);
				if (file_exists($translationRepositoryPath))
				{
					self::$translationRepositoryPath = Path::normalize($translationRepositoryPath);
				}
			}
		}

		return self::$translationRepositoryPath;
	}

	/**
	 * Tells true if configuration allows to convert encodings.
	 *
	 * @return bool
	 */
	public static function allowConvertEncoding()
	{
		if(self::$allowConvertEncoding === null)
		{
			self::$allowConvertEncoding = false;

			$config = Configuration::getValue('translation');

			if ($config !== null && !empty($config['convert_encoding']))
			{
				self::$allowConvertEncoding = ($config['convert_encoding'] === true);
			}
		}

		return self::$allowConvertEncoding;
	}

	/**
	 * Returns path to developer repository.
	 *
	 * @return string
	 */
	public static function getDeveloperRepositoryPath()
	{
		static $developerRepositoryPath, $wasChecked;
		if($wasChecked === null)
		{
			$wasChecked = true;
			$config = Configuration::getValue('translation');

			if ($config !== null && !empty($config['developer_repository']))
			{
				$developerRepositoryPath = realpath($config['developer_repository']);
				if (file_exists($developerRepositoryPath))
				{
					$developerRepositoryPath = Path::normalize($developerRepositoryPath);
				}
			}
		}

		return $developerRepositoryPath;
	}

	/**
	 * Converts lang file to translation repository path.
	 *
	 * @param string $langFile Language file path.
	 * @param string $language Language code to retrieve translation.
	 * @return string
	 */
	public static function convertLangPath($langFile, $language)
	{
		if (empty($language) || !(self::useTranslationRepository() || self::getDeveloperRepositoryPath() !== null))
		{
			return $langFile;
		}

		static $documentRoot;
		if ($documentRoot === null)
		{
			$documentRoot = Path::normalize(Main\Application::getDocumentRoot());
		}

		if (self::useTranslationRepository() && !self::isDefaultTranslationLang($language))
		{
			$modulePath = self::getTranslationRepositoryPath().'/'.$language.'/';
		}
		elseif (self::getDeveloperRepositoryPath() !== null)
		{
			$modulePath = self::getDeveloperRepositoryPath(). '/';
		}
		elseif (self::isDefaultTranslationLang($language))
		{
			$modulePath = $documentRoot. '/bitrix/modules/';
		}
		else
		{
			return $langFile;
		}

		if (mb_strpos($langFile, '\\') !== false)
		{
			$langFile = str_replace('\\', '/', $langFile);
		}
		if (mb_strpos($langFile, '//') !== false)
		{
			$langFile = str_replace('//', '/', $langFile);
		}

		// linked
		if (self::getDeveloperRepositoryPath() !== null)
		{
			if (mb_strpos($langFile, self::getDeveloperRepositoryPath()) === 0)
			{
				$langFile = str_replace(
					self::getDeveloperRepositoryPath(). '/',
					$modulePath,
					$langFile
				);

				return $langFile;
			}
		}

		// module lang
		if (strpos($langFile, $documentRoot. '/bitrix/modules/') === 0)
		{
			$langFile = str_replace(
				$documentRoot.'/bitrix/modules/',
				$modulePath,
				$langFile
			);

			return $langFile;
		}

		self::loadMap();

		$langPathParts = preg_split('#[/]+#', trim(str_replace($documentRoot, '', $langFile), '/'), 6);
		if (empty($langPathParts) || $langPathParts[0] !== 'bitrix')
		{
			return $langFile;
		}

		$testEntry = $langPathParts[1];
		switch ($testEntry)
		{
			// bitrix/mobileapp/[moduleName] -> [moduleName]/install/mobileapp/[moduleName]
			case 'mobileapp':
				$moduleName = $langPathParts[2];
				if (isset(self::$map[$moduleName][$testEntry], self::$map[$moduleName][$testEntry][$moduleName]))
				{
					$testEntry = 'mobileapp/'. $moduleName;
					$langFile = str_replace(
						$documentRoot.'/bitrix/mobileapp/'. $moduleName. '/',
						$modulePath.''.$moduleName.'/install/mobileapp/'. $moduleName. '/',
						$langFile
					);
				}
				break;

			// bitrix/templates/[templateName] -> [moduleName]/install/templates/[templateName]
			case 'templates':
				$templateName = $langPathParts[2];
				foreach (self::$map as $moduleName => $moduleEntries)
				{
					if (isset(self::$map[$moduleName][$testEntry], self::$map[$moduleName][$testEntry][$templateName]))
					{
						$langFile = str_replace(
							$documentRoot.'/bitrix/templates/'.$templateName.'/',
							$modulePath.''.$moduleName.'/install/templates/'. $templateName .'/',
							$langFile
						);
						break;
					}
				}
				break;

			// bitrix/components/bitrix/[componentName] -> [moduleName]/install/components/bitrix/[componentName]
			// bitrix/activities/bitrix/[activityName] -> [moduleName]/install/activities/bitrix/[activityName]
			// bitrix/wizards/bitrix/[wizardsName] -> [moduleName]/install/wizards/bitrix/[wizardsName]
			// bitrix/gadgets/bitrix/[gadgetName] -> [moduleName]/install/gadgets/bitrix/[gadgetName]
			case 'components':
			case 'activities':
			case 'wizards':
			case 'gadgets':
			case 'blocks':
				if ($langPathParts[2] !== 'bitrix')
				{
					break;
				}
				$searchEntryName = $langPathParts[3];
				foreach (self::$map as $moduleName => $moduleEntries)
				{
					if (isset(self::$map[$moduleName][$testEntry], self::$map[$moduleName][$testEntry][$searchEntryName]))
					{
						$langFile = str_replace(
							$documentRoot.'/bitrix/'.$testEntry.'/bitrix/'.$searchEntryName.'/',
							$modulePath.''.$moduleName.'/install/'.$testEntry.'/bitrix/'. $searchEntryName. '/',
							$langFile
						);
						break;
					}
				}
				break;

			// bitrix/js/[moduleName]/[smth] -> [moduleName]/install/js/[moduleName]/[smth]
			// bitrix/js/[moduleName]/[smth] -> [moduleName]/install/public/js/[moduleName]/[smth]
			case 'js':
				$libraryNamespace = $langPathParts[2]. '/'. $langPathParts[3];

				foreach (self::$map as $moduleName => $moduleEntries)
				{
					if (isset(self::$map[$moduleName][$testEntry], self::$map[$moduleName][$testEntry][$libraryNamespace]))
					{
						$langFile = str_replace(
							$documentRoot.'/bitrix/'.$testEntry.'/'.$libraryNamespace.'/',
							$modulePath.''.$moduleName.'/install/'.$testEntry.'/'.$libraryNamespace.'/',
							$langFile
						);
						break;
					}
					if (isset(self::$map[$moduleName]["public/{$testEntry}"], self::$map[$moduleName]["public/{$testEntry}"][$libraryNamespace]))
					{
						$langFile = str_replace(
							$documentRoot.'/bitrix/'.$testEntry.'/'.$libraryNamespace.'/',
							$modulePath.''.$moduleName.'/install/public/'.$testEntry.'/'.$libraryNamespace.'/',
							$langFile
						);
						break;
					}
				}
				break;

			// bitrix/[moduleName]/payment/[paymentHandler] -> [moduleName]/payment/[paymentHandler]
			case 'payment':
				$searchEntryName = $langPathParts[3];
				foreach (self::$map as $moduleName => $moduleEntries)
				{
					if (isset(self::$map[$moduleName][$testEntry], self::$map[$moduleName][$testEntry][$searchEntryName]))
					{
						$langFile = str_replace(
							$documentRoot.'/bitrix/modules/'.$moduleName.'/'.$testEntry.'/',
							$modulePath.''.$moduleName.'/'.$testEntry.'/',
							$langFile
						);
						break;
					}
				}
				break;
		}

		return $langFile;
	}


	/**
	 * Restore project map structure from file structure.
	 *
	 * @return array
	 */
	public static function loadMap()
	{
		if (empty(self::$map))
		{
			$cacheManager = Main\Application::getInstance()->getManagedCache();
			if ($cacheManager->read(static::CACHE_TTL, static::CACHE_ID))
			{
				self::$map = $cacheManager->get(static::CACHE_ID);
			}
		}

		if (empty(self::$map))
		{
			$testForExistence = array(
				'templates',
				'components',
				'activities',
				'wizards',
				'gadgets',
				'js',
				'public/js',
				'blocks',
				'payment',
				'mobileapp',
			);
			$bxRoot = Main\Application::getDocumentRoot(). '/bitrix/modules/';
			$modulesList = new Main\IO\Directory($bxRoot);
			foreach ($modulesList->getChildren() as $moduleDirectory)
			{
				if ($moduleDirectory->isDirectory())
				{
					$moduleName = $moduleDirectory->getName();
					if (strpos($moduleName, '.') === false || strpos($moduleName, 'bitrix.') === 0)
					{
						self::$map[$moduleName] = array();
						foreach ($testForExistence as $testEntry)
						{
							$testPath = $bxRoot. '/'. $moduleName. '/install/'. $testEntry;
							if ($testEntry === 'templates' || $testEntry === 'mobileapp')
							{
								$testPath .= '/';
							}
							elseif ($testEntry === 'js' || $testEntry === 'public/js')
							{
								$testPath .= '/'. $moduleName. '/';
							}
							elseif ($testEntry === 'payment')
							{
								$testPath = $bxRoot. '/'. $moduleName. '/'. $testEntry;
							}
							else
							{
								$testPath .= '/bitrix/';
							}

							$testDirectory = new Main\IO\Directory($testPath);
							if ($testDirectory->isExists())
							{
								self::$map[$moduleName][$testEntry] = array();
								foreach ($testDirectory->getChildren() as $testDirectoryEntry)
								{
									if ($testDirectoryEntry->isDirectory())
									{
										if ($testEntry === 'js' || $testEntry === 'public/js')
										{
											self::$map[$moduleName][$testEntry][$moduleName.'/'.$testDirectoryEntry->getName()] = 1;
										}
										else
										{
											self::$map[$moduleName][$testEntry][$testDirectoryEntry->getName()] = 1;
										}
									}
								}
							}
						}
					}
				}
			}

			$cacheManager->set(static::CACHE_ID, static::$map);
		}

		return self::$map;
	}

	/**
	 * @param $language
	 * @param $langFile
	 * @return array
	 */
	public static function getEncodings($language, $langFile)
	{
		static $encodingCache = array();

		if(isset($encodingCache[$language]))
		{
			list($convertEncoding, $targetEncoding, $sourceEncoding) = $encodingCache[$language];
		}
		else
		{
			$convertEncoding = self::needConvertEncoding($language);
			$targetEncoding = $sourceEncoding = '';
			if($convertEncoding)
			{
				$targetEncoding = self::getCurrentEncoding();
				$sourceEncoding = self::getSourceEncoding($language);
			}

			$encodingCache[$language] = array($convertEncoding, $targetEncoding, $sourceEncoding);
		}

		if($convertEncoding)
		{
			$convertEncoding = self::checkPathRestrictionConvertEncoding($langFile);
		}

		return array($convertEncoding, $targetEncoding, $sourceEncoding);
	}
}
