<?
namespace Bitrix\Sale\Location\Normalizer;

use Bitrix\Main\IO\File;

/**
 * Normalize due to language specialties
 * Class LanguageNormalizer
 * @package Bitrix\Sale\Location\Normalizer
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
	public function normalize($string)
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
	protected function replaceLetters($string, array $letters)
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
	protected function replaceTitles($string, $titles)
	{
		$result = $string;
		$implodedTitles = implode('|', $titles);
		$regexp = '/^('.$implodedTitles.')+\s+(.*?)$/i'.BX_UTF_PCRE_MODIFIER;
		$result = preg_replace($regexp, '$2', $result);
		$regexp = '/^(.*?)\s+('.$implodedTitles.')+$/i'.BX_UTF_PCRE_MODIFIER;
		$result = preg_replace($regexp, '$1', $result);
		return $result;
	}

	/**
	 * @param string $string Location name
	 * @param array $aliases Replacements
	 * @return string
	 */
	protected function replaceAliases($string, $aliases)
	{
		$result = $string;

		if(isset($aliases[$string]))
		{
			$result = $aliases[$string];
		}

		return $result;
	}

	/**
	 * @param string $lang Language id
	 * @return array Language data.
	 */
	protected function loadLangData($lang)
	{
		$result = [];

		if(empty($result))
		{
			if ($langDataPath = $this->getLangDataFilePath($lang))
			{
				if (File::isFileExists($langDataPath))
				{
					$result = require $langDataPath;
				}
			}
		}

		return $result;
	}

	/**
	 * @param string $lang Language id.
	 * @return string Path to language data file.
	 */
	protected function getLangDataFilePath($lang)
	{
		return __DIR__.'/lang/'.$lang.'/langnormdata.php';
	}

	/**
	 * @param array $langData Language data.
	 */
	public function setLangData($langData)
	{
		if (isset($langData['LETTERS']) && is_array($langData['LETTERS']))
		{
			$this->letters = $langData['LETTERS'];
		}

		if (isset($langData['ALIASES']) && is_array($langData['ALIASES']))
		{
			$this->aliases = $langData['ALIASES'];
		}

		if (isset($langData['TITLES']) && is_array($langData['TITLES']))
		{
			$this->titles = $langData['TITLES'];
		}
	}
}
