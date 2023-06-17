<?php
namespace Bitrix\Location\Entity\Address\Normalizer;

use Bitrix\Main\IO\File;
use Bitrix\Main\Localization;

/**
 * Normalize location name due to language specialties
 *
 * Class LanguageNormalizer
 * @package Bitrix\Location\Entity\Address\Normalizer
 * @internal
 */
class LanguageNormalizer implements INormalizer
{
	/** @var array Letters to replace */
	protected $letters = [];
	/** @var array Names to replace wholly */
	protected $aliases = [];
	/** @var array Locations types titles */
	protected $titles = [];

	/**
	 * LanguageNormalizer constructor.
	 * @param string $lang Language id.
	 * @param array $langData Language specific data.
	 */
	public function __construct($lang = LANGUAGE_ID, array $langData = [])
	{
		if(empty($langData))
		{
			$langData = $this->loadLangData($lang);
		}

		$this->setLangData($langData);
	}

	/** @inheritdoc */
	public function normalize(string $string): string
	{
		$result = $string;

		if(is_array($this->letters) && !empty($this->letters))
		{
			$result = $this->replaceLetters($result, $this->letters);
		}

		if(is_array($this->aliases) && !empty($this->aliases))
		{
			$result = $this->replaceAliases($result, $this->aliases);
		}

		if(is_array($this->titles) && !empty($this->titles))
		{
			$result = $this->replaceTitles($result, $this->titles);
		}

		return $result;
	}

	/**
	 * @param string $string Location name.
	 * @param array $letters Replacements
	 * @return string
	 */
	protected function replaceLetters(string $string, array $letters): string
	{
		$result = $string;

		foreach($letters as $search => $replace)
		{
			$result = str_replace($search, $replace, $result);
		}

		return $result;
	}

	/**
	 * @param string $string Location name.
	 * @param array $titles Replacements
	 * @return string
	 */
	protected function replaceTitles(string $string, array $titles): string
	{
		$result = $string;
		$implodedTitles = implode('|', $titles);
		$regexp = '/^('.$implodedTitles.')+\s+(.*?)$/i'.BX_UTF_PCRE_MODIFIER;
		$result = preg_replace($regexp, '$2', $result);

		if($result !== null)
		{
			$regexp = '/^(.*?)\s+(' . $implodedTitles . ')+$/i' . BX_UTF_PCRE_MODIFIER;
			$result = preg_replace($regexp, '$1', $result);
		}

		return $result !== null ? $result : '';
	}

	/**
	 * @param string $string Location name
	 * @param array $aliases Replacements
	 * @return string
	 */
	protected function replaceAliases(string $string, array $aliases): string
	{
		return $aliases[$string] ?? $string;
	}

	/**
	 * @param string $lang Language id
	 * @return array Language data.
	 */
	protected function loadLangData(string $lang): array
	{
		$result = [];

		if(empty($result))
		{
			if ($langDataPath = $this->getLangDataFilePath($lang))
			{
				if (File::isFileExists($langDataPath))
				{
					if (Localization\Translation::allowConvertEncoding())
					{
						if (class_exists('\Bitrix\Main\Localization\StreamConverter'))
						{
							$result = Localization\StreamConverter::include($langDataPath, $lang);
						}
						elseif (class_exists('\Bitrix\Main\Localization\SteamConverter'))
						{
							$result = Localization\SteamConverter::include($langDataPath, $lang);
						}
					}
					else
					{
						$result = require $langDataPath;
					}
				}
			}
		}

		return $result;
	}

	/**
	 * @param string $lang Language id.
	 * @return string Path to language data file.
	 */
	protected function getLangDataFilePath(string $lang): string
	{
		return $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/location/lang/'.$lang.'/lib/entity/address/normalizer/langnormdata.php';
	}

	/**
	 * @param array $langData Language data.
	 */
	public function setLangData(array $langData): void
	{
		if(isset($langData['LETTERS']) && is_array($langData['LETTERS']))
		{
			$this->letters = $langData['LETTERS'];
		}

		if(isset($langData['ALIASES']) && is_array($langData['ALIASES']))
		{
			$this->aliases = $langData['ALIASES'];
		}

		if(isset($langData['TITLES']) && is_array($langData['TITLES']))
		{
			$this->titles = $langData['TITLES'];
		}
	}
}
