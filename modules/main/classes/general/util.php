<?php

use Bitrix\Main;
use Bitrix\Main\Config;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Localization\Loc;

class CUtil
{
	public static function addslashes($s)
	{
		static $aSearch = ["\\", "\"", "'"];
		static $aReplace = ["\\\\", '\\"', "\\'"];
		return str_replace($aSearch, $aReplace, $s);
	}

	public static function closetags($html)
	{
		preg_match_all("#<([a-z0-9]+)([^>]*)(?<!/)>#iu", $html, $result);
		$openedtags = $result[1];

		preg_match_all("#</([a-z0-9]+)>#iu", $html, $result);
		$closedtags = $result[1];
		$len_opened = count($openedtags);

		if (count($closedtags) == $len_opened)
		{
			return $html;
		}

		$openedtags = array_reverse($openedtags);

		for ($i = 0; $i < $len_opened; $i++)
		{
			if (!in_array($openedtags[$i], $closedtags))
			{
				$html .= '</' . $openedtags[$i] . '>';
			}
			else
			{
				unset($closedtags[array_search($openedtags[$i], $closedtags)]);
			}
		}

		return $html;
	}

	public static function JSEscape($s)
	{
		static $aSearch = ["\xe2\x80\xa9", "\\", "'", "\"", "\r\n", "\r", "\n", "\xe2\x80\xa8", "*/", "</"];
		static $aReplace = [" ", "\\\\", "\\'", '\\"', "\n", "\n", "\\n", "\\n", "*\\/", "<\\/"];
		$val = str_replace($aSearch, $aReplace, $s);
		return $val;
	}

	public static function JSUrlEscape($s)
	{
		static $aSearch = ["%27", "%5C", "%0A", "%0D", "%", "&#039;", "&#39;", "&#x27;", "&apos;"];
		static $aReplace = ["\\'", "\\\\", "\\n", "\\r", "%25", "\\'", "\\'", "\\'", "\\'"];
		return str_replace($aSearch, $aReplace, $s);
	}

	/**
	 * @deprecated Use \Bitrix\Main\Web\Json::encode().
	 */
	public static function PhpToJSObject($arData, $bWS = false, $bSkipTilda = false, $bExtType = false)
	{
		static $use_bx_encode = null;
		if (!isset($use_bx_encode))
		{
			$use_bx_encode = function_exists('bx_js_encode');
		}
		if ($use_bx_encode)
		{
			return bx_js_encode($arData, $bWS, $bSkipTilda, $bExtType);
		}

		switch (gettype($arData))
		{
			case "string":
				if (preg_match("#['\"\\n\\r<\\\\\x80]#", $arData))
				{
					return "'" . CUtil::JSEscape($arData) . "'";
				}
				return "'" . $arData . "'";

			case "array":
				$i = -1;
				$j = -1;
				foreach ($arData as $j => $temp)
				{
					$i++;
					if ($j !== $i)
					{
						break;
					}
				}

				if ($j === $i)
				{
					$res = '[';
					$first = true;
					foreach ($arData as $value)
					{
						if ($first)
						{
							$first = false;
						}
						else
						{
							$res .= ',';
						}

						switch (gettype($value))
						{
							case "string":
								if (preg_match("#['\"\\n\\r<\\\\\x80]#", $value))
								{
									$res .= "'" . CUtil::JSEscape($value) . "'";
								}
								else
								{
									$res .= "'" . $value . "'";
								}
								break;
							case "array":
								$res .= CUtil::PhpToJSObject($value, $bWS, $bSkipTilda, $bExtType);
								break;
							case "boolean":
								if ($value === true)
								{
									$res .= 'true';
								}
								else
								{
									$res .= 'false';
								}
								break;
							case "integer":
								if ($bExtType)
								{
									$res .= $value;
								}
								else
								{
									$res .= "'" . $value . "'";
								}
								break;
							case "double":
								if ($bExtType)
								{
									$res .= is_finite($value) ? $value : "Infinity";
								}
								else
								{
									$res .= "'" . $value . "'";
								}
								break;
							default:
								if (preg_match("#['\"\\n\\r<\\\\\x80]#", (string)$value))
								{
									$res .= "'" . CUtil::JSEscape($value) . "'";
								}
								else
								{
									$res .= "'" . $value . "'";
								}
								break;
						}
					}
					$res .= ']';
					return $res;
				}

				$sWS = ',' . ($bWS ? "\n" : '');
				$res = ($bWS ? "\n" : '') . '{';
				$first = true;
				foreach ($arData as $key => $value)
				{
					if ($bSkipTilda && str_starts_with($key, '~'))
					{
						continue;
					}

					if ($first)
					{
						$first = false;
					}
					else
					{
						$res .= $sWS;
					}

					if (preg_match("#['\"\\n\\r<\\\\\x80]#", $key))
					{
						$res .= "'" . CUtil::JSEscape($key) . "':";
					}
					else
					{
						$res .= "'" . $key . "':";
					}

					switch (gettype($value))
					{
						case "string":
							if (preg_match("#['\"\\n\\r<\\\\\x80]#", $value))
							{
								$res .= "'" . CUtil::JSEscape($value) . "'";
							}
							else
							{
								$res .= "'" . $value . "'";
							}
							break;
						case "array":
							$res .= CUtil::PhpToJSObject($value, $bWS, $bSkipTilda, $bExtType);
							break;
						case "boolean":
							if ($value === true)
							{
								$res .= 'true';
							}
							else
							{
								$res .= 'false';
							}
							break;
						case "integer":
							if ($bExtType)
							{
								$res .= $value;
							}
							else
							{
								$res .= "'" . $value . "'";
							}
							break;
						case "double":
							if ($bExtType)
							{
								$res .= is_finite($value) ? $value : "Infinity";
							}
							else
							{
								$res .= "'" . $value . "'";
							}
							break;
						default:
							if (preg_match("#['\"\\n\\r<\\\\\x80]#", (string)$value))
							{
								$res .= "'" . CUtil::JSEscape($value) . "'";
							}
							else
							{
								$res .= "'" . $value . "'";
							}
							break;
					}
				}
				$res .= ($bWS ? "\n" : '') . '}';
				return $res;

			case "boolean":
				if ($arData === true)
				{
					return 'true';
				}
				return 'false';

			case "integer":
				if ($bExtType)
				{
					return $arData;
				}
				return "'" . $arData . "'";

			case "double":
				if ($bExtType)
				{
					return is_finite($arData) ? $arData : "Infinity";
				}
				return "'" . $arData . "'";

			default:
				if (preg_match("#['\"\\n\\r<\\\\\x80]#", (string)$arData))
				{
					return "'" . CUtil::JSEscape($arData) . "'";
				}
				return "'" . $arData . "'";
		}
	}

	public static function JsObjectToPhp($data, $bSkipNative = false)
	{
		$arResult = [];
		$parse = $bSkipNative;

		if (!$parse)
		{
			// json_decode recognize only UTF strings
			// the name and value must be enclosed in double quotes
			// single quotes are not valid
			$arResult = json_decode($data, true);

			if ($arResult === null)
			{
				$parse = true;
			}
		}

		if ($parse)
		{
			if (!$bSkipNative)
			{
				// prevents warning recursion
				trigger_error("CUtil::JsObjectToPhp() is deprecated. Probably, data is enclosed in single-quotes. Change it to double-quotes.", E_USER_WARNING);
			}

			$data = preg_replace('/[\s]*([{}\[\]\"])[\s]*/', '\1', $data);
			$data = trim($data);

			if (str_starts_with($data, '{')) // object
			{
				$arResult = [];

				$depth = 0;
				$end_pos = 0;
				$arCommaPos = [];
				$bStringStarted = false;
				$prev_symbol = "";

				$string_delimiter = '';
				for ($i = 1, $len = mb_strlen($data); $i < $len; $i++)
				{
					$cur_symbol = mb_substr($data, $i, 1);
					if ($cur_symbol == '"' || $cur_symbol == "'")
					{
						if (
							$prev_symbol != '\\' && (
								!$string_delimiter || $string_delimiter == $cur_symbol
							)
						)
						{
							if ($bStringStarted = !$bStringStarted)
							{
								$string_delimiter = $cur_symbol;
							}
							else
							{
								$string_delimiter = '';
							}
						}
					}

					elseif ($cur_symbol == '{' || $cur_symbol == '[')
					{
						$depth++;
					}
					elseif ($cur_symbol == ']')
					{
						$depth--;
					}
					elseif ($cur_symbol == '}')
					{
						if ($depth == 0)
						{
							$end_pos = $i;
							break;
						}
						else
						{
							$depth--;
						}
					}
					elseif ($cur_symbol == ',' && $depth == 0 && !$bStringStarted)
					{
						$arCommaPos[] = $i;
					}
					$prev_symbol = $cur_symbol;
				}

				if ($end_pos == 0)
				{
					return false;
				}

				$token = mb_substr($data, 1, $end_pos - 1);

				$arTokens = [];
				if (!empty($arCommaPos))
				{
					$prev_index = 0;
					foreach ($arCommaPos as $pos)
					{
						$arTokens[] = mb_substr($token, $prev_index, $pos - $prev_index - 1);
						$prev_index = $pos;
					}
					$arTokens[] = mb_substr($token, $prev_index);
				}
				else
				{
					$arTokens[] = $token;
				}

				foreach ($arTokens as $token)
				{
					$arTokenData = explode(":", $token, 2);

					$q = mb_substr($arTokenData[0], 0, 1);
					if ($q == '"')
					{
						$arTokenData[0] = mb_substr($arTokenData[0], 1, -1);
					}
					$arResult[CUtil::JsObjectToPhp($arTokenData[0], true)] = CUtil::JsObjectToPhp($arTokenData[1] ?? null, true);
				}
			}
			elseif (str_starts_with($data, '[')) // array
			{
				$arResult = [];

				$depth = 0;
				$end_pos = 0;
				$arCommaPos = [];
				$bStringStarted = false;
				$prev_symbol = "";
				$string_delimiter = "";

				for ($i = 1, $len = mb_strlen($data); $i < $len; $i++)
				{
					$cur_symbol = mb_substr($data, $i, 1);
					if ($cur_symbol == '"' || $cur_symbol == "'")
					{
						if (
							$prev_symbol != '\\' && (
								!$string_delimiter || $string_delimiter == $cur_symbol
							)
						)
						{
							if ($bStringStarted = !$bStringStarted)
							{
								$string_delimiter = $cur_symbol;
							}
							else
							{
								$string_delimiter = '';
							}
						}
					}
					elseif ($cur_symbol == '{' || $cur_symbol == '[')
					{
						$depth++;
					}
					elseif ($cur_symbol == '}')
					{
						$depth--;
					}
					elseif ($cur_symbol == ']')
					{
						if ($depth == 0)
						{
							$end_pos = $i;
							break;
						}
						else
						{
							$depth--;
						}
					}
					elseif ($cur_symbol == ',' && $depth == 0 && !$bStringStarted)
					{
						$arCommaPos[] = $i;
					}
					$prev_symbol = $cur_symbol;
				}

				if ($end_pos == 0)
				{
					return false;
				}

				$token = mb_substr($data, 1, $end_pos - 1);

				if (!empty($arCommaPos))
				{
					$prev_index = 0;
					foreach ($arCommaPos as $pos)
					{
						$arResult[] = CUtil::JsObjectToPhp(mb_substr($token, $prev_index, $pos - $prev_index - 1), true);
						$prev_index = $pos;
					}
					$r = CUtil::JsObjectToPhp(mb_substr($token, $prev_index), true);
					if (isset($r))
					{
						$arResult[] = $r;
					}
				}
				else
				{
					$r = CUtil::JsObjectToPhp($token, true);
					if (isset($r))
					{
						$arResult[] = $r;
					}
				}
			}
			elseif ($data === "")
			{
				return null;
			}
			else // scalar
			{
				$q = mb_substr($data, 0, 1);
				if ($q == '"' || $q == "'")
				{
					$data = mb_substr($data, 1, -1);
				}

				//\u0412\u0430\u0434\u0438\u043c
				if (str_contains($data, '\u'))
				{
					$data = preg_replace_callback("/\\\u([0-9A-F]{2})([0-9A-F]{2})/i", ['CUtil', 'DecodeUtf16'], $data);
				}

				$arResult = $data;
			}
		}

		return $arResult;
	}

	public static function DecodeUtf16($ch)
	{
		$res = chr(hexdec($ch[2])) . chr(hexdec($ch[1]));
		return Encoding::convertEncoding($res, 'UTF-16', 'UTF-8');
	}

	/**
	 * @deprecated Does nothing.
	 */
	public static function JSPostUnescape()
	{
	}

	/**
	 * @deprecated Does nothing.
	 */
	public static function decodeURIComponent()
	{
	}

	/**
	 * @deprecated Use \Bitrix\Main\Text\Encoding::detectUtf8().
	 */
	public static function DetectUTF8($string)
	{
		return Encoding::detectUtf8($string);
	}

	/**
	 * @deprecated Use \Bitrix\Main\Text\Encoding::convertToUtf().
	 */
	public static function ConvertToLangCharset($string)
	{
		return Encoding::convertToUtf($string);
	}

	public static function GetAdditionalFileURL($file, $bSkipCheck = false)
	{
		$filePath = $_SERVER['DOCUMENT_ROOT'] . $file;
		if ($bSkipCheck || file_exists($filePath))
		{
			return $file . '?' . filemtime($filePath) . filesize($filePath);
		}

		return $file;
	}

	/**
	 * @deprecated Use \CJSCore::Init().
	 */
	public static function InitJSCore($arExt = [], $bReturn = false)
	{
		/*patchvalidationapp2*/
		return CJSCore::Init($arExt, $bReturn);
	}

	public static function GetPopupSize($resize_id, $arDefaults = [])
	{
		if ($resize_id)
		{
			return CUserOptions::GetOption(
				'BX.WindowManager.9.5',
				'size_' . $resize_id,
				[
					'width' => $arDefaults['width'] ?? null,
					'height' => $arDefaults['height'] ?? null,
				]
			);
		}

		return false;
	}

	public static function GetPopupOptions($wnd_id)
	{
		if ($wnd_id)
		{
			return CUserOptions::GetOption(
				'BX.WindowManager.9.5',
				'options_' . $wnd_id
			);
		}

		return false;
	}

	public static function SetPopupOptions($wnd_id, $arOptions)
	{
		if ($wnd_id)
		{
			CUserOptions::SetOption(
				'BX.WindowManager.9.5',
				'options_' . $wnd_id,
				$arOptions
			);
		}
	}

	public static function translit($str, $lang, $params = [])
	{
		static $search = [];

		if (!isset($search[$lang]))
		{
			$mess = Loc::loadLanguageFile($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/js_core_translit.php", $lang, false);
			$transFrom = explode(",", $mess["TRANS_FROM"]);
			$transto = explode(",", $mess["TRANS_TO"]);
			$search[$lang] = array_combine($transFrom, $transto);
		}

		$defaultParams = [
			"max_len" => 100,
			"change_case" => 'L', // 'L' - toLower, 'U' - toUpper, false - do not change
			"replace_space" => '_',
			"replace_other" => '_',
			"delete_repeat_replace" => true,
			"safe_chars" => '',
		];
		foreach ($defaultParams as $key => $value)
		{
			if (!isset($params[$key]))
			{
				$params[$key] = $value;
			}
		}

		$chars = mb_str_split($str);
		$len = count($chars);
		$strNew = '';
		$lastChrNew = '';

		for ($i = 0; $i < $len; $i++)
		{
			$chr = $chars[$i];

			if (preg_match("/[a-zA-Z0-9]/", $chr) || ($params["safe_chars"] != '' && mb_strpos($params["safe_chars"], $chr) !== false))
			{
				$chrNew = $chr;
			}
			elseif (preg_match("/\\s/u", $chr))
			{
				if (
					!$params["delete_repeat_replace"]
					||
					($i > 0 && $lastChrNew != $params["replace_space"])
				)
				{
					$chrNew = $params["replace_space"];
				}
				else
				{
					$chrNew = '';
				}
			}
			else
			{
				if (isset($search[$lang][$chr]))
				{
					$chrNew = $search[$lang][$chr];
				}
				else
				{
					if (
						!$params["delete_repeat_replace"]
						||
						($i > 0 && $i != $len - 1 && $lastChrNew != $params["replace_other"])
					)
					{
						$chrNew = $params["replace_other"];
					}
					else
					{
						$chrNew = '';
					}
				}
			}

			if ($chrNew != '')
			{
				$strNew .= $chrNew;
				$lastChrNew = $chrNew;
			}

			if (mb_strlen($strNew) >= $params["max_len"])
			{
				break;
			}
		}

		if ($params["change_case"] == "L" || $params["change_case"] == "l")
		{
			$strNew = mb_strtolower($strNew);
		}
		elseif ($params["change_case"] == "U" || $params["change_case"] == "u")
		{
			$strNew = mb_strtoupper($strNew);
		}

		return $strNew;
	}

	/**
	 * Convert shorthand notation to integer equivalent
	 * @param string $str
	 * @return int
	 *
	 * @deprecated Use \Bitrix\Main\Config\Ini::unformatInt().
	 */
	public static function Unformat($str)
	{
		return Config\Ini::unformatInt((string)$str);
	}

	public static function getSitesByWizard($wizard)
	{
		static $list = [];

		if (!isset($list[$wizard]))
		{
			$list[$wizard] = [];

			if ('portal' == $wizard && Main\ModuleManager::isModuleInstalled('bitrix24'))
			{
				$list[$wizard] = Main\SiteTable::getByPrimary('s1', ['cache' => ['ttl' => 86400]])->fetchAll();
			}
			else
			{
				if ($wizard <> '')
				{
					$res = Main\SiteTable::getList(['order' => ['DEF' => 'DESC', 'SORT' => 'ASC']]);
					foreach ($res as $item)
					{
						if (Main\Config\Option::get('main', '~wizard_id', '', $item['LID']) === $wizard)
						{
							$list[$wizard][] = $item;
						}
					}
				}
			}
		}

		return $list[$wizard];
	}
}
