<?php
namespace Bitrix\Main\Localization;

use Bitrix\Main;
use Bitrix\Main\IO\Path;
use Bitrix\Main\Context;
use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Text\Encoding;

final class Loc
{
	private static $currentLang = null;
	private static $messages = array();
	private static $customMessages = array();
	private static $userMessages = array();
	private static $includedFiles = array();
	private static $lazyLoadFiles = array();
	private static $triedFiles = array();

	/**
	 * Returns translation by message code.
	 * Loc::loadMessages(__FILE__) should be called first once per php file
	 *
	 * @param string $code
	 * @param array $replace e.g. array("#NUM#"=>5)
	 * @param string $language
	 * @return string|null
	 */
	public static function getMessage($code, $replace = null, $language = null)
	{
		if($language === null)
		{
			//function call optimization
			if(self::$currentLang === null)
			{
				$language = self::getCurrentLang();
			}
			else
			{
				$language = self::$currentLang;
			}
		}

		if(!isset(self::$messages[$language][$code]))
		{
			self::loadLazy($code, $language);
		}

		$s = self::$messages[$language][$code] ?? null;

		if (is_array($replace) && $s !== null)
		{
			$s = strtr($s, $replace);
		}

		return $s;
	}

	/**
	 * Loads language messages for specified file in a lazy way
	 *
	 * @param string $file
	 */
	public static function loadMessages($file)
	{
		if(($realPath = realpath($file)) !== false)
		{
			$file = $realPath;
		}
		$file = Path::normalize($file);

		self::$lazyLoadFiles[$file] = $file;
	}

	/**
	 * @return string
	 */
	public static function getCurrentLang()
	{
		if(self::$currentLang === null)
		{
			$context = Context::getCurrent();
			if($context !== null)
			{
				$language = $context->getLanguage();
				if($language !== null)
				{
					self::$currentLang = $language;
				}
			}
		}
		return (self::$currentLang !== null? self::$currentLang : 'en');
	}

	public static function setCurrentLang($language)
	{
		self::$currentLang = $language;
	}

	/**
	 * Loads language messages for specified file and language.
	 *
	 * @param string $file File path to look for language translation for its.
	 * @param string $language Language code.
	 * @param string $loadedLangFile Certain loaded language file.
	 *
	 * @return array
	 */
	private static function includeLangFiles($file, $language, &$loadedLangFile)
	{
		static $langDirCache = array();

		// open_basedir restriction
		static $openBasedir = [], $openBasedirRestriction;
		if ($openBasedirRestriction === null)
		{
			$openBasedirTmp = ini_get('open_basedir');
			if (!empty($openBasedirTmp))
			{
				// multiple paths split by colon ":" - "/home/bitrix:/var/www/html"
				// under non windows by semicolon ";" - "c:/www/;c:/www/html"
				$openBasedirTmp = explode(
					(strncasecmp(PHP_OS, 'WIN', 3) == 0 ? ';' : ':'),
					$openBasedirTmp
				);
				foreach ($openBasedirTmp as $testDir)
				{
					if (!empty($testDir))
					{
						$testDir = Path::normalize($testDir);
						if (is_dir($testDir))
						{
							$openBasedir[] = $testDir;
						}
					}
				}
			}
			$openBasedirRestriction = !empty($openBasedir);
		}

		$path = Path::getDirectory($file);

		if(isset($langDirCache[$path]))
		{
			$langDir = $langDirCache[$path];
			$fileName = mb_substr($file, (mb_strlen($langDir) - 5));
		}
		else
		{
			//let's find language folder
			$langDir = $fileName = '';
			$filePath = $file;
			while (($slashPos = mb_strrpos($filePath, '/')) !== false)
			{
				$filePath = mb_substr($filePath, 0, $slashPos);
				if ($openBasedirRestriction === true)
				{
					$withinOpenBasedir = false;
					foreach ($openBasedir as $testDir)
					{
						if (stripos($filePath, $testDir) === 0)
						{
							$withinOpenBasedir = true;
							break;
						}
					}
					if (!$withinOpenBasedir)
					{
						break;
					}
				}
				$langPath = $filePath. '/lang';
				if (is_dir($langPath))
				{
					$langDir = $langPath;
					$fileName = mb_substr($file, $slashPos);
					$langDirCache[$path] = $langDir;
					break;
				}
			}
		}

		$mess = array();
		if($langDir <> '')
		{
			//load messages for default lang first
			$defaultLang = self::getDefaultLang($language);
			if($defaultLang <> $language)
			{
				$langFile = $langDir. '/'. $defaultLang. $fileName;

				$langFile = Translation::convertLangPath($langFile, $defaultLang);

				if(file_exists($langFile))
				{
					$mess = self::includeFile($langFile);
					$loadedLangFile = $langFile;
				}
			}

			//then load messages for specified lang
			$langFile = $langDir. '/'. $language. $fileName;

			$langFile = Translation::convertLangPath($langFile, $language);

			if(file_exists($langFile))
			{
				$mess = array_merge($mess, self::includeFile($langFile));
				$loadedLangFile = $langFile;
			}
		}

		return $mess;
	}

	/**
	 * Loads language messages for specified file
	 *
	 * @param string $file
	 * @param string $language
	 * @param bool $normalize
	 * @return array
	 */
	public static function loadLanguageFile($file, $language = null, $normalize = true)
	{
		if($language === null)
		{
			$language = self::getCurrentLang();
		}

		if($normalize)
		{
			$file = Path::normalize($file);
		}

		if(!isset(self::$messages[$language]))
		{
			self::$messages[$language] = array();
		}

		//first time call only for lang
		if(!isset(self::$userMessages[$language]))
		{
			self::$userMessages[$language] = self::loadUserMessages($language);
		}

		//let's find language folder and include lang files
		$mess = self::includeLangFiles($file, $language, $langFile);

		if (!empty($mess))
		{
			[$convertEncoding, $targetEncoding, $sourceEncoding] = Translation::getEncodings($language, $langFile);

			foreach ($mess as $key => $val)
			{
				if (isset(self::$customMessages[$language][$key]))
				{
					self::$messages[$language][$key] = $mess[$key] = self::$customMessages[$language][$key];
				}
				else
				{
					if ($convertEncoding)
					{
						$val = Encoding::convertEncoding($val, $sourceEncoding, $targetEncoding);
						$mess[$key] = $val;
					}

					self::$messages[$language][$key] = $val;
				}
			}
		}

		return $mess;
	}

	/**
	 * Loads custom messages from the file to overwrite messages by their IDs.
	 *
	 * @param string $file
	 * @param string|null $language
	 */
	public static function loadCustomMessages($file, $language = null)
	{
		if($language === null)
		{
			$language = self::getCurrentLang();
		}

		if(!isset(self::$customMessages[$language]))
		{
			self::$customMessages[$language] = array();
		}

		//let's find language folder and include lang files
		$mess = self::includeLangFiles(Path::normalize($file), $language, $langFile);

		if (!empty($mess))
		{
			[$convertEncoding, $targetEncoding, $sourceEncoding] = Translation::getEncodings($language, $langFile);

			foreach ($mess as $key => $val)
			{
				if ($convertEncoding)
				{
					$val = $mess[$key] = Encoding::convertEncoding($val, $sourceEncoding, $targetEncoding);
				}

				self::$customMessages[$language][$key] = $val;
			}
		}
	}

	private static function loadLazy($code, $language)
	{
		if($code == '')
		{
			return;
		}

		//control of duplicates
		if(!isset(self::$triedFiles[$language]))
		{
			self::$triedFiles[$language] = [];
		}

		$trace = Main\Diag\Helper::getBackTrace(4, DEBUG_BACKTRACE_IGNORE_ARGS);

		$currentFile = null;
		for($i = 3; $i >= 1; $i--)
		{
			if (isset($trace[$i]) && stripos($trace[$i]["function"], "GetMessage") === 0)
			{
				$currentFile = Path::normalize($trace[$i]["file"]);

				//we suppose there is a language file even if it wasn't registered via loadMessages()
				self::$lazyLoadFiles[$currentFile] = $currentFile;
				break;
			}
		}

		if($currentFile !== null && isset(self::$lazyLoadFiles[$currentFile]))
		{
			//in most cases we know the file containing the "code" - load it directly
			if(!isset(self::$triedFiles[$language][$currentFile]))
			{
				self::loadLanguageFile($currentFile, $language, false);
				self::$triedFiles[$language][$currentFile] = true;
			}
			unset(self::$lazyLoadFiles[$currentFile]);
		}

		if(!isset(self::$messages[$language][$code]))
		{
			//we still don't know which file contains the "code" - go through the files in the reverse order
			$unset = array();
			if(($file = end(self::$lazyLoadFiles)) !== false)
			{
				do
				{
					if(!isset(self::$triedFiles[$language][$file]))
					{
						self::loadLanguageFile($file, $language, false);
						self::$triedFiles[$language][$file] = true;
					}

					$unset[] = $file;
					if(isset(self::$messages[$language][$code]))
					{
						if(defined("BX_MESS_LOG") && $currentFile !== null)
						{
							file_put_contents(BX_MESS_LOG, 'CTranslateUtils::CopyMessage("'.$code.'", "'.$file.'", "'.$currentFile.'");'."\n", FILE_APPEND);
						}
						break;
					}
				}
				while(($file = prev(self::$lazyLoadFiles)) !== false);
			}
			foreach($unset as $file)
			{
				unset(self::$lazyLoadFiles[$file]);
			}
		}

		if(!isset(self::$messages[$language][$code]) && defined("BX_MESS_LOG"))
		{
			file_put_contents(BX_MESS_LOG, $code.": not found for ".$currentFile."\n", FILE_APPEND);
		}
	}

	/**
	 * Reads messages from user defined lang file
	 *
	 * @param string $lang
	 * @return array
	 */
	private static function loadUserMessages($lang)
	{
		$userMess = array();
		$documentRoot = Main\Application::getDocumentRoot();
		if(($fname = Main\Loader::getLocal("php_interface/user_lang/".$lang."/lang.php", $documentRoot)) !== false)
		{
			$mess = self::includeFile($fname);

			// typical call is Loc::loadMessages(__FILE__)
			// __FILE__ can differ from path used in the user file
			foreach($mess as $key => $val)
				$userMess[str_replace("\\", "/", realpath($documentRoot.$key))] = $val;
		}
		return $userMess;
	}

	/**
	 * Reads messages from lang file.
	 *
	 * @param string $path
	 * @return array
	 */
	private static function includeFile($path)
	{
		self::$includedFiles[$path] = $path;

		//the name $MESS is predefined in language files
		$MESS = array();
		include($path);

		//redefine messages from user lang file
		if(!empty(self::$userMessages))
		{
			$path = str_replace("\\", "/", realpath($path));

			//cycle through languages
			foreach(self::$userMessages as $messages)
			{
				if(isset($messages[$path]) && is_array($messages[$path]))
				{
					foreach($messages[$path] as $key => $val)
					{
						$MESS[$key] = $val;
					}
				}
			}
		}

		return $MESS;
	}

	/**
	 * Returns default language for specified language. Default language is used when translation is not found.
	 *
	 * @param string $lang
	 * @return string
	 */
	public static function getDefaultLang($lang)
	{
		static $subst = ['ua' => 'en', 'kz' => 'ru', 'by' => 'ru', 'ru' => 'ru', 'en' => 'en', 'de' => 'en'];
		if(isset($subst[$lang]))
		{
			return $subst[$lang];
		}

		$options = Configuration::getValue("default_language");
		if(isset($options[$lang]))
		{
			return $options[$lang];
		}

		return 'en';
	}

	/**
	 * Returns previously included lang files.
	 * @return array
	 */
	public static function getIncludedFiles()
	{
		return self::$includedFiles;
	}

	/**
	 * Gets plural message by id and number
	 * @param {string} messageId
	 * @param {number} value
	 * @param {object} [replacements]
	 * @return {?string}
	 */

	/**
	 * Returns plural message by message code and number.
	 * Loc::loadMessages(__FILE__) should be called first once per php file
	 *
	 * @param string $code
	 * @param int $value
	 * @param array|null $replace e.g. array("#NUM#"=>5)
	 * @param string|null $language
	 * @return string|null
	 */
	public static function getMessagePlural(string $code, int $value, array $replace = null, string $language = null): ?string
	{
		$language = (string)$language;
		if ($language === '')
		{
			$language = LANGUAGE_ID;
		}

		$result = self::getMessage($code . '_PLURAL_' . self::getPluralForm($value, $language), $replace);
		if ($result === null)
		{
			$result = self::getMessage($code . '_PLURAL_1', $replace);
		}

		return $result;
	}

	/**
	 * Return language plural form id by number
	 * see http://docs.translatehouse.org/projects/localization-guide/en/latest/l10n/pluralforms.html
	 * @param {number} value
	 * @param {string} languageId
	 * @return integer
	 */
	public static function getPluralForm($value, $language = ''): int
	{
		$value = (int)$value;
		$language = (string)$language;
		if ($language === '')
		{
			$language = LANGUAGE_ID;
		}

		if ($value < 0)
		{
			$value = (-1) * $value;
		}

		switch ($language)
		{
			case 'ar':
				$pluralForm = (($value !== 1) ? 1 : 0);
/*
				if ($value === 0)
				{
					$pluralForm = 0;
				}
				else if ($value === 1)
				{
					$pluralForm = 1;
				}
				else if ($value === 2)
				{
					$pluralForm = 2;
				}
				else if (
					$value % 100 >= 3
					&& $value % 100 <= 10
				)
				{
					$pluralForm = 3;
				}
				else if ($value % 100 >= 11)
				{
					$pluralForm = 4;
				}
				else
				{
					$pluralForm = 5;
				}
*/
				break;

			case 'br':
			case 'fr':
			case 'tr':
				$pluralForm = (($value > 1) ? 1 : 0);
				break;

			case 'de':
			case 'en':
			case 'hi':
			case 'it':
			case 'la':
				$pluralForm = (($value !== 1) ? 1 : 0);
				break;

			case 'ru':
			case 'ua':
				if (
					($value % 10 === 1)
					&& ($value % 100 !== 11)
				)
				{
					$pluralForm = 0;
				}
				else if (
					($value % 10 >= 2)
					&& ($value % 10 <= 4)
					&& (
						($value % 100 < 10)
						|| ($value % 100 >= 20)
					)
				)
				{
					$pluralForm = 1;
				}
				else
				{
					$pluralForm = 2;
				}
				break;

			case 'pl':
				if ($value === 1)
				{
					$pluralForm = 0;
				}
				else if (
					$value % 10 >= 2
					&& $value % 10 <= 4
					&& (
						$value % 100 < 10
						|| $value % 100 >= 20
					)
				)
				{
					$pluralForm = 1;
				}
				else
				{
					$pluralForm = 2;
				}
				break;

			case 'id':
			case 'ja':
			case 'ms':
			case 'sc':
			case 'tc':
			case 'th':
			case 'vn':
				$pluralForm = 0;
				break;

			default:
				$pluralForm = 1;
				break;
		}

		return $pluralForm;

	}

}
