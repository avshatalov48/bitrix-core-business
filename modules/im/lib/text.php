<?php
namespace Bitrix\Im;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Text
{
	private static $replacements = Array();

	public static function parse($text, $params = Array())
	{
		if (!isset($params['SAFE']) || $params['SAFE'] == 'Y')
		{
			$text = htmlspecialcharsbx($text);
		}

		$allowTags = array(
			"HTML" => "N",
			"USER" => "N",
			"ANCHOR" => $params['LINK'] == 'N'? 'N': 'Y',
			"BIU" => "Y",
			"IMG" => "N",
			"QUOTE" => "N",
			"CODE" => "N",
			"FONT" => "N",
			"LIST" => "N",
			"SMILES" => $params['SMILES'] == 'N'? 'N': 'Y',
			"NL2BR" => "Y",
			"VIDEO" => "N",
			"TABLE" => "N",
			"CUT_ANCHOR" => "N",
			"SHORT_ANCHOR" => "N",
			"ALIGN" => "N"
		);

		$parser = new \CTextParser();
		$parser->serverName = Common::getPublicDomain();
		$parser->maxAnchorLength = intval($params['LINK_LIMIT'])? $params['LINK_LIMIT']: 55;
		$parser->maxStringLen = intval($params['TEXT_LIMIT']);
		$parser->allow = $allowTags;

		$text = preg_replace_callback("/\[PUT(?:=(.+?))?\](.+?)?\[\/PUT\]/i", Array('\Bitrix\Im\Text', 'setReplacement'), $text);
		$text = preg_replace_callback("/\[SEND(?:=(.+?))?\](.+?)?\[\/SEND\]/i", Array('\Bitrix\Im\Text', 'setReplacement'), $text);

		if (isset($params['CUT_STRIKE']) && $params['CUT_STRIKE'] == 'Y')
		{
			$text = preg_replace("/\[s\].*?\[\/s\]/i", "", $text);
		}

		$text = $parser->convertText($text);

		$text = str_replace(array('#BR#', '[br]', '[BR]'), '<br/>', $text);

		$text = self::recoverReplacements($text);

		return $text;
	}

	/**
	 * @param $text
	 * @return \Bitrix\Main\Text\DateConverterResult[]
	 */
	public static function getDateConverterParams($text)
	{
		if (strlen($text) <= 0)
			return Array();

		$text = preg_replace_callback("/\[PUT(?:=(.+?))?\](.+?)?\[\/PUT\]/i", Array('\Bitrix\Im\Text', 'setReplacement'), $text);
		$text = preg_replace_callback("/\[SEND(?:=(.+?))?\](.+?)?\[\/SEND\]/i", Array('\Bitrix\Im\Text', 'setReplacement'), $text);
		$text = preg_replace_callback('/\[URL\=([^\]]*)\]([^\]]*)\[\/URL\]/i', Array('\Bitrix\Im\Text', 'setReplacement'), $text);
		$text = preg_replace_callback('/(https?):\/\/(([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+(:([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+)?@)?(?#)((([a-z0-9]\.|[a-z0-9][a-z0-9-]*[a-z0-9]\.)*[a-z][a-z0-9-]*[a-z0-9]|((\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])\.){3}(\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5]))(:\d+)?)(((\/+([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)*(\?([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)?)?)?(#([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)?/im', Array('\Bitrix\Im\Text', 'setReplacement'), $text);
		$text = preg_replace_callback('#\-{54}(.+?)\-{54}#s', Array('\Bitrix\Im\Text', 'setReplacement'), $text);

		return \Bitrix\Main\Text\DateConverter::decode($text, 1000);
	}

	public static function setReplacement($match)
	{
		$code = '####REPLACEMENT_MARK_'.count(self::$replacements).'####';

		self::$replacements[$code] = $match[0];

		return $code;
	}

	public static function recoverReplacements($text)
	{
		foreach(self::$replacements as $code => $value)
		{
			$text = str_replace($code, $value, $text);
		}
		self::$replacements = Array();

		return $text;
	}

	public static function removeBbCodes($text, $withFile = false, $withAttach = false)
	{
		$text = preg_replace("/\[[buis]\](.*?)\[\/[buis]\]/i", "$1", $text);
		$text = preg_replace("/\[url\](.*?)\[\/url\]/i".BX_UTF_PCRE_MODIFIER, "$1", $text);
		$text = preg_replace("/\[url\\s*=\\s*((?:[^\\[\\]]++|\\[ (?: (?>[^\\[\\]]+) | (?:\\1) )* \\])+)\\s*\\](.*?)\\[\\/url\\]/ixs".BX_UTF_PCRE_MODIFIER, "$2", $text);
		$text = preg_replace("/\[RATING=([1-5]{1})\]/i", " [".Loc::getMessage('IM_MESSAGE_RATING')."] ", $text);
		$text = preg_replace("/\[ATTACH=([0-9]{1,})\]/i", " [".Loc::getMessage('IM_MESSAGE_ATTACH')."] ", $text);
		$text = preg_replace("/\[USER=([0-9]{1,})\](.*?)\[\/USER\]/i", "$2", $text);
		$text = preg_replace("/\[CHAT=([0-9]{1,})\](.*?)\[\/CHAT\]/i", "$2", $text);
		$text = preg_replace("/\[SEND(?:=(.+?))?\](.+?)?\[\/SEND\]/i", "$2", $text);
		$text = preg_replace("/\[PUT(?:=(.+?))?\](.+?)?\[\/PUT\]/i", "$2", $text);
		$text = preg_replace("/\[CALL(?:=(.+?))?\](.+?)?\[\/CALL\]/i", "$2", $text);
		$text = preg_replace("/\[PCH=([0-9]{1,})\](.*?)\[\/PCH\]/i", "$2", $text);
		$text = preg_replace_callback("/\[ICON\=([^\]]*)\]/i", Array("CIMMessenger", "PrepareMessageForPushIconCallBack"), $text);
		$text = preg_replace('#\-{54}.+?\-{54}#s', " [".Loc::getMessage('IM_QUOTE')."] ", str_replace(array("#BR#"), Array(" "), $text));
		$text = trim($text);

		if ($withFile)
		{
			$text .= " [".Loc::getMessage('IM_MESSAGE_FILE')."]";
		}
		if ($withAttach)
		{
			$text .= " [".Loc::getMessage('IM_MESSAGE_ATTACH')."]";
		}

		if ($withFile || $withAttach)
		{
			$text = trim($text);
		}

		if (strlen($text) <= 0)
		{
			$text = Loc::getMessage('IM_MESSAGE_DELETE');
		}

		return $text;
	}

	public static function prepareBeforeSave($text)
	{
		$text = self::replaceEmoji($text);

		return $text;
	}

	public static function replaceEmoji($text)
	{
		if (!\Bitrix\Main\Application::isUtfMode())
		{
			return $text;
		}

		$text = preg_replace('/[\x{1F300}-\x{1F5FF}]/u', '('.Loc::getMessage('IM_MESSAGE_EMOJI').')', $text);
		$text = preg_replace('/[\x{1F600}-\x{1F64F}]/u', '('.Loc::getMessage('IM_MESSAGE_EMOJI').')', $text);
		$text = preg_replace('/[\x{1F680}-\x{1F6FF}]/u', '('.Loc::getMessage('IM_MESSAGE_EMOJI').')', $text);
		$text = preg_replace('/[\x{1F1E6}-\x{1F1FF}]/u', '('.Loc::getMessage('IM_MESSAGE_EMOJI').')', $text);
		$text = preg_replace('/[\x{2600}-\x{26FF}]/u', '('.Loc::getMessage('IM_MESSAGE_EMOJI').')', $text);
		$text = preg_replace('/[\x{2700}-\x{27BF}]/u', '('.Loc::getMessage('IM_MESSAGE_EMOJI').')', $text);
		$text = preg_replace('/[\x{FE00}-\x{FE00}]/u', '('.Loc::getMessage('IM_MESSAGE_EMOJI').')', $text);

		return $text;
	}
}