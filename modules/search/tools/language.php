<?
class CSearchLanguage
{
	var $_abc = array();
	var $_lang_id;
	var $_lang_bigramm_cache;
	var $_trigrams = array();
	var $_has_bigramm_info = null;
	var $_bigrams = null;

	function __construct($lang_id)
	{
		$this->_lang_id = $lang_id;
	}

	//Function loads language class
	static function GetLanguage($sLang)
	{
		static $arLanguages = array();

		if(!isset($arLanguages[$sLang]))
		{
			$obLanguage = null;
			$class_name = strtolower("CSearchLanguage".$sLang);
			if(!class_exists($class_name))
			{
				//First try to load customized class
				$strDirName = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/".$sLang."/search";
				$strFileName = $strDirName."/language.php";
				if(file_exists($strFileName))
					$obLanguage = @include($strFileName);

				if(!is_object($obLanguage))
				{
					if(!class_exists($class_name))
					{
						//Then module class
						$strDirName = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/search/tools/".$sLang;
						$strFileName = $strDirName."/language.php";
						if(file_exists($strFileName))
							@include($strFileName);
						if(!class_exists($class_name))
						{
							$class_name = "CSearchLanguage";
						}
					}
				}
			}

			if(!is_object($obLanguage))
				$obLanguage =  new $class_name($sLang);
			$obLanguage->LoadTrigrams($strDirName);
			$arStemInfo = stemming_init($sLang);
			if(is_array($arStemInfo))
				$obLanguage->_abc = array_flip($obLanguage->StrToArray($arStemInfo["abc"]));
			$obLanguage->_has_bigramm_info = is_callable(array($obLanguage, "getbigrammletterfreq"));

			$arLanguages[$sLang] = $obLanguage;
		}

		return $arLanguages[$sLang];
	}

	//Reads file with trigrams (combinations not allowed in the words)
	function LoadTrigrams($dir_name)
	{
		if(empty($this->_trigrams))
		{
			$file_name = $dir_name."/trigram";
			if(file_exists($file_name) && is_file($file_name))
			{
				$cache_id = filemtime($file_name).",v1,".$file_name;
				$obCache = new CPHPCache;
				if($obCache->StartDataCache(360000, $cache_id, "search"))
				{
					$text = file_get_contents($file_name);
					$keyboard = $this->GetKeyboardLayout();
					if (defined("BX_UTF") && isset($keyboard["trigram_charset"]))
					{
						$text = $GLOBALS["APPLICATION"]->ConvertCharset($text, $keyboard["trigram_charset"], "utf8");
					}
					$ar = explode("\n", $text);
					foreach($ar as $trigramm)
					{
						if(strlen($trigramm) == 3)
						{
							$strScanCodesTmp = $this->ConvertToScancode($trigramm, false, true);
							if(strlen($strScanCodesTmp) == 3)
							{
								$this->_trigrams[$strScanCodesTmp] = true;
							}
						}
					}

					$obCache->EndDataCache($this->_trigrams);
				}
				else
				{
					$this->_trigrams = $obCache->GetVars();
				}
			}
		}
	}

	function HasTrigrams()
	{
		return !empty($this->_trigrams);
	}

	//Check phrase against trigrams
	function CheckTrigrams($arScanCodes)
	{
		$result = 0;
		$check = "";
		$len = 0;
		foreach($arScanCodes as $i => $code)
		{
			if($code === false) //new word starts here
			{
				$check = "";
				$len = 0;
			}
			else
			{
				//running window of 3 bytes
				if($len < 3)
				{
					$check .= chr($code+1);
					$len++;
				}
				else
				{
					$check = $check[1].$check[2].chr($code+1);
					$len = 3;
				}
			}

			if($len >= 3)
			{
				if(isset($this->_trigrams[$check]))
					$result++;
			}
		}

		return $result;
	}

	//This function returns positions of the letters
	//on the keyboard. This one is default English layout
	function GetKeyboardLayout()
	{
		return array(
			"lo" => "`          - ".
				"qwertyuiop[]".
				"asdfghjkl;'".
				"zxcvbnm,. ",
			"hi" => "~            ".
				"QWERTYUIOP{}".
				"ASDFGHJKL:\"".
				"ZXCVBNM<> "
		);
	}

	function ConvertFromScancode($arScancode)
	{
		$result = "";
		$keyboard = $this->GetKeyboardLayout();
		foreach($arScancode as $code)
			$result .= substr($keyboard["lo"], $code, 1);
		return $result;
	}

	function StrToArray($str)
	{
		if(defined("BX_UTF"))
		{
			$result = array();
			$len = strlen($str);
			for($i = 0;$i < $len; $i++)
				$result[] = substr($str, $i, 1);
			return $result;
		}
		else
		{
			return str_split($str);
		}
	}

	//This function converts text between layouts
	static function ConvertKeyboardLayout($text, $from, $to)
	{
		static $keyboards = array();
		$combo = $from."|".$to;

		if(!isset($keyboards[$combo]))
		{
			//Fill local cache
			if(!array_key_exists($from, $keyboards))
			{
				$ob = CSearchLanguage::GetLanguage($from);
				$keyboard = $ob->GetKeyboardLayout();
				if(is_array($keyboard))
					$keyboards[$from] = array_merge($ob->StrToArray($keyboard["lo"]), $ob->StrToArray($keyboard["hi"]));
				else
					$keyboards[$from] = null;
			}

			if(!array_key_exists($to, $keyboards))
			{
				$ob = CSearchLanguage::GetLanguage($to);
				$keyboard = $ob->GetKeyboardLayout();
				if(is_array($keyboard))
					$keyboards[$to] = array_merge($ob->StrToArray($keyboard["lo"]), $ob->StrToArray($keyboard["hi"]));
				else
					$keyboards[$to] = null;
			}

			//when both layouts defined
			if(isset($keyboards[$from]) && isset($keyboards[$to]))
			{
				$keyboards[$combo] = array();
				foreach($keyboards[$from] as $i => $ch)
					if($ch != false)
						$keyboards[$combo][$ch] = $keyboards[$to][$i];
			}
		}

		if(isset($keyboards[$combo]))
		{
			if (defined("BX_UTF"))
			{
				$text = static::StrToArray($text);
				foreach ($text as $pos => $char)
				{
					if (isset($keyboards[$combo][$char]))
						$text[$pos] = $keyboards[$combo][$char];
				}
				return implode('', $text);
			}
			else
			{
				return strtr($text, $keyboards[$combo]);
			}
		}
		else
		{
			return $text;
		}
	}

	//This function converts text into array of character positions
	//on the keyboard. Not defined chars turns into "false" value.
	function ConvertToScancode($text, $strict=false, $binary=false)
	{
		static $cache = array();
		if(!isset($cache[$this->_lang_id]))
		{
			$cache[$this->_lang_id] = array();
			$keyboard = $this->GetKeyboardLayout();

			foreach($this->StrToArray($keyboard["lo"]) as $pos => $ch)
				$cache[$this->_lang_id][$ch] = $pos;

			foreach($this->StrToArray($keyboard["hi"]) as $pos => $ch)
				$cache[$this->_lang_id][$ch] = $pos;
		}

		$scancodes = &$cache[$this->_lang_id];

		if($binary)
		{
			$result = "";
			foreach($this->StrToArray($text) as $ch)
			{
				if(
					isset($scancodes[$ch])
					&& !($ch === " ")
					&& !($strict && !isset($this->_abc[$ch]))
				)
					$result .= chr($scancodes[$ch]+1);
			}
		}
		else
		{
			$result = array();
			foreach($this->StrToArray($text) as $ch)
			{
				if($ch === " ")
					$result[] = false;
				elseif($strict && !isset($this->_abc[$ch]))
					$result[] = false;
				elseif(isset($scancodes[$ch]))
					$result[] = $scancodes[$ch];
				else
					$result[] = false;
			}
		}
		return $result;
	}

	function PreGuessLanguage($text, $lang=false)
	{
		//Indicates that there is no own guess
		return false;
		//In subclasses you should return array("from" => lang, "to" => lang) to translate
		//or return true when no translation nedded
		//or parent::GuessLanguage for futher processing
	}

	public static function GuessLanguage($text, $lang=false)
	{
		if(strlen($text) <= 0)
			return false;

		static $cache = array();
		if(empty($cache))
		{
			$cache[] = "en";//English is always in mind and on the first place
			$rsLanguages = CLanguage::GetList(($b=""), ($o=""));
			while($arLanguage = $rsLanguages->Fetch())
				if($arLanguage["LID"] != "en")
					$cache[] = $arLanguage["LID"];
		}

		if(is_array($lang))
			$arLanguages = $lang;
		else
			$arLanguages = $cache;

		if(count($arLanguages) < 2)
			return false;

		//Give customized languages a chance to guess
		foreach($arLanguages as $lang)
		{
			$ob = CSearchLanguage::GetLanguage($lang);
			$res = $ob->PreGuessLanguage($text, $lang);
			if(is_array($res))
				return $res;
			elseif($res === true)
				return false;
		}

		//First try to detect language which
		//was used to type the phrase
		$max_len = 0;
		$languages_from = array();
		foreach($arLanguages as $lang)
		{
			$ob = CSearchLanguage::GetLanguage($lang);

			$arScanCodesTmp1 = $ob->ConvertToScancode($text, true);
			$_cnt = count(array_filter($arScanCodesTmp1));
			if ($_cnt > $max_len)
				$max_len = $_cnt;
			$languages_from[$lang] = $arScanCodesTmp1;
		}

		if (empty($languages_from))
			return false;

		if ($max_len < 2)
			return false;

		$languages_from = array_filter($languages_from,
			function($a) use($max_len)
			{
				return count(array_filter($a)) >= $max_len;
			}
		);

		uasort($languages_from,
			function($a, $b)
			{
				return count(array_filter($b)) - count(array_filter($a));
			}
		);

		//If more than one language is detected as input
		//try to get one with best trigram info
		$arDetectionFrom = array();
		$i = 0;
		foreach($languages_from as $lang => $arScanCodes)
		{
			$ob = CSearchLanguage::GetLanguage($lang);
			//Calculate how far sequence of scan codes
			//is from language model
			$deviation = $ob->GetDeviation($arScanCodes);

			$arDetectionFrom[$lang] = array(
				$ob->HasTrigrams(),
				$ob->CheckTrigrams($arScanCodes),
				$deviation[1],
				intval($deviation[0]*100),
				$i,
			);

			$i++;
		}
		uasort($arDetectionFrom, array("CSearchLanguage", "cmp"));

		//Now try the best to detect the language
		$arDetection = array();
		$i = 0;
		foreach($arDetectionFrom as $lang_from => $arTemp)
		{
			foreach($arLanguages as $lang)
			{
				$lang_from_to = $lang_from."=>".$lang;

				$arDetection[$lang_from_to] = array();

				$ob = CSearchLanguage::GetLanguage($lang);

				$alt_text = CSearchLanguage::ConvertKeyboardLayout($text, $lang_from, $lang);
				$arScanCodes = $ob->ConvertToScancode($alt_text, true);

				$arDetection[$lang_from_to][] = $ob->HasBigrammInfo()? 0: 1;
				$arDetection[$lang_from_to][] = $ob->CheckTrigrams($arScanCodes);
				$arDetection[$lang_from_to][] = -count(array_filter($arScanCodes));

				//Calculate how far sequence of scan codes
				//is from language model
				$deviation = $ob->GetDeviation($arScanCodes);
				$arDetection[$lang_from_to][] = $deviation[1];
				$arDetection[$lang_from_to][] = $deviation[0];

				$arDetection[$lang_from_to][] = $i;
				$arDetection[$lang_from_to][] = $lang_from_to;
				$i++;
			}
		}

		uasort($arDetection, array("CSearchLanguage", "cmp"));
		$language_from_to = key($arDetection);

		list($language_from, $language_to) = explode("=>", $language_from_to);

		$alt_text = CSearchLanguage::ConvertKeyboardLayout($text, $language_from, $language_to);
		if($alt_text === $text)
			return false;

		return array("from" => $language_from, "to" => $language_to);
	}

	//Compare to results of text analysis
	static function cmp($a, $b)
	{
		$c = count($a);
		for($i = 0; $i < $c; $i++)
		{
			if($a[$i] < $b[$i])
				return -1;
			elseif($a[$i] > $b[$i])
				return 1;
		}
		return 0;//never happens
	}

	//Function returns distance of the text (sequence of scan codes)
	//from language model
	function GetDeviation($arScanCodes)
	{
		//This is language model
		$lang_bigrams = $this->GetBigrammScancodeFreq();
		$lang_count = $lang_bigrams["count"];
		unset($lang_bigrams["count"]);

		//This is text model
		$text_bigrams = $this->ConvertToBigramms($arScanCodes);
		$count = $text_bigrams["count"];
		unset($text_bigrams["count"]);

		$deviation = 0;
		$zeroes = 0;
		foreach($text_bigrams as $key => $value)
		{
			for ($i = 0;$i < $value; $i++)
			{
				if(!isset($lang_bigrams[$key]))
				{
					$zeroes++;
					$deviation += 1/$count;
				}
				else
				{
					$deviation += abs(1/$count - $lang_bigrams[$key]/$lang_count);
				}
			}
		}

		return array($deviation, $zeroes);
	}

	//Function returns bigramms of the text (array of scancodes)
	//For example "FAT RAT" will be
	//array("FA", "AT", "RA", "AT")
	//This is model of the text
	function ConvertToBigramms($arScancodes)
	{
		$result = array();

		$len = count($arScancodes)-1;
		for($i = 0; $i < $len; $i++)
		{
			$code1 = $arScancodes[$i];
			$code2 = $arScancodes[$i+1];
			if($code1 !== false && $code2 !== false)
			{
				$result["count"]++;
				$result[$code1." ".$code2]++;
			}
		}
		return $result;
	}

	function HasBigrammInfo()
	{
		return $this->_has_bigramm_info;
	}

	//Function returns model of the language
	function GetBigrammScancodeFreq()
	{
		if(!$this->HasBigrammInfo())
			return array("count"=>1);

		if(!isset($this->_lang_bigramm_cache))
		{
			$bigramms = $this->GetBigrammLetterFreq();
			$keyboard = $this->GetKeyboardLayout();
			$keyboard_lo = $keyboard["lo"];
			$keyboard_hi = $keyboard["hi"];

			$result = array();
			foreach($bigramms as $letter1 => $row)
			{
				$p1 = strpos($keyboard_lo, $letter1);
				if($p1 === false)
					$p1 = strpos($keyboard_hi, $letter1);

				$i = 0;
				foreach($bigramms as $letter2 => $tmp)
				{
					$p2 = strpos($keyboard_lo, $letter2);
					if($p2 === false)
						$p2 = strpos($keyboard_hi, $letter2);

					$weight = $row[$i];
					$result["count"] += $weight;
					$result[$p1." ".$p2] = $weight;
					$i++;
				}
			}
			$this->_lang_bigramm_cache = $result;
		}
		return $this->_lang_bigramm_cache;
	}
}
?>
