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
	private static $userMessages = null;
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
	 * @return string
	 */
	public static function getMessage($code, $replace = null, $language = null)
	{
		if($language === null)
		{
			//function call optimization
			if(static::$currentLang === null)
			{
				self::getCurrentLang();
			}

			$language = static::$currentLang;
		}

		if(!isset(self::$messages[$language][$code]))
		{
			self::loadLazy($code, $language);
		}

		$s = self::$messages[$language][$code];

		if($replace !== null && is_array($replace))
		{
			foreach($replace as $search => $repl)
			{
				$s = str_replace($search, $repl, $s);
			}
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
				self::$currentLang = $context->getLanguage();
			}
			else
			{
				self::$currentLang = 'en';
			}
		}
		return self::$currentLang;
	}

	public static function setCurrentLang($language)
	{
		static::$currentLang = $language;
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

		$path = Path::getDirectory($file);

		if(isset($langDirCache[$path]))
		{
			$langDir = $langDirCache[$path];
			$fileName = substr($file, (strlen($langDir)-5));
		}
		else
		{
			//let's find language folder
			$langDir = $fileName = '';
			$filePath = $file;
			while(($slashPos = strrpos($filePath, '/')) !== false)
			{
				$filePath = substr($filePath, 0, $slashPos);
				$langPath = $filePath.'/lang';
				if(is_dir($langPath))
				{
					$langDir = $langPath;
					$fileName = substr($file, $slashPos);
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
		if(self::$userMessages === null)
		{
			self::$userMessages = self::loadUserMessages($language);
		}

		//let's find language folder and include lang files
		$mess = self::includeLangFiles($file, $language, $langFile);

		if (!empty($mess))
		{
			static $encodingCache = array();
			if (isset($encodingCache[$language]))
			{
				list($convertEncoding, $targetEncoding, $sourceEncoding) = $encodingCache[$language];
			}
			else
			{
				$convertEncoding = Translation::needConvertEncoding($language);
				$targetEncoding = $sourceEncoding = '';
				if ($convertEncoding)
				{
					$targetEncoding = Translation::getCurrentEncoding();
					$sourceEncoding = Translation::getSourceEncoding($language);
				}

				$encodingCache[$language] = array($convertEncoding, $targetEncoding, $sourceEncoding);
			}


			if ($convertEncoding)
			{
				$convertEncoding = \Bitrix\Main\Localization\Translation::checkPathRestrictionConvertEncoding($langFile);
			}


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
	 * @deprecated Do not use, will be removed soon. loadLanguageFile() is enough
	 */
	public static function loadFile($langFile, $language = null)
	{
		if (empty($language))
		{
			// extract language from path
			$arr = explode('/', $langFile);
			$langKey = array_search('lang', $arr);
			if ($langKey !== false && isset($arr[$langKey + 1]))
			{
				$language = $arr[$langKey + 1];
			}
		}

		if (empty($language))
		{
			$language = self::getCurrentLang();
		}

		$langFile = Translation::convertLangPath($langFile, $language);

		$mess = array();
		if (file_exists($langFile))
		{
			$mess = self::includeFile($langFile);

			if (!empty($mess))
			{
				static $encodingCache = array();
				if (isset($encodingCache[$language]))
				{
					list($convertEncoding, $targetEncoding, $sourceEncoding) = $encodingCache[$language];
				}
				else
				{
					$convertEncoding = Translation::needConvertEncoding($language);
					$targetEncoding = $sourceEncoding = '';
					if ($convertEncoding)
					{
						$targetEncoding = Translation::getCurrentEncoding();
						$sourceEncoding = Translation::getSourceEncoding($language);
					}

					$encodingCache[$language] = array($convertEncoding, $targetEncoding, $sourceEncoding);
				}

				if ($convertEncoding)
				{
					$convertEncoding = \Bitrix\Main\Localization\Translation::checkPathRestrictionConvertEncoding($langFile);
				}

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
		}

		return $mess;
	}

	/**
	 * Loads custom messages from the file to overwrite messages by their IDs.
	 *
	 * @param $file
	 * @param null $language
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

		foreach($mess as $key => $val)
		{
			self::$customMessages[$language][$key] = $val;
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
			if(stripos($trace[$i]["function"], "GetMessage") === 0)
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
			if(is_array(self::$userMessages[$path]))
				foreach(self::$userMessages[$path] as $key => $val)
					$MESS[$key] = $val;
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
		static $subst = array('ua'=>'ru', 'kz'=>'ru', 'by'=>'ru', 'ru'=>'ru', 'en'=>'en', 'de'=>'en');
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
}
