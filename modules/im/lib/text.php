<?php
namespace Bitrix\Im;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Text
{
	private static $replacements = Array();
	private static $parsers = Array();

	private static $emojiList = [
		"file" => ':f09f938e:',
		"image" => ':f09f96bc:',
		"audio" => ':f09f9488:',
		"video" => ':f09f93ba:',
		"code" => ':f09f9384:',
		"call" => ':f09f939e:',
		"attach" => ':f09fa7a9:',
		"quote" => ':f09f92ac:',
	];

	public static function parse($text, $params = Array())
	{
		$linkParam = $params['LINK'] ?? null;
		$smilesParam = $params['SMILES'] ?? null;
		$linkLimitParam = $params['LINK_LIMIT'] ?? null;
		$textLimitParam = $params['TEXT_LIMIT'] ?? null;
		$cutStrikeParam = $params['CUT_STRIKE'] ?? null;

		$parseId = md5($linkParam.$smilesParam.$linkLimitParam.$textLimitParam);
		if (isset(self::$parsers[$parseId]))
		{
			$parser = self::$parsers[$parseId];
		}
		else
		{
			$parser = new \CTextParser();
			$parser->serverName = Common::getPublicDomain();
			$parser->maxStringLen = intval($textLimitParam);

			$parser->anchorType = 'bbcode';
			$parser->maxAnchorLength = intval($linkLimitParam)? $linkLimitParam: 55;

			foreach ($parser->allow as $tag => $value)
			{
				$parser->allow[$tag] = 'N';
			}
			$parser->allow['EMOJI'] = 'Y';
			$parser->allow['HTML'] = 'Y';
			$parser->allow['ANCHOR'] = 'Y';
			$parser->allow['TEXT_ANCHOR'] = 'Y';

			self::$parsers[$parseId] = $parser;
		}

		$text = preg_replace_callback("/\[CODE\](.*?)\[\/CODE\]/si", Array('\Bitrix\Im\Text', 'setReplacement'), $text);
		$text = preg_replace_callback("/\[PUT(?:=(.+?))?\](.+?)?\[\/PUT\]/i", Array('\Bitrix\Im\Text', 'setReplacement'), $text);
		$text = preg_replace_callback("/\[SEND(?:=(.+?))?\](.+?)?\[\/SEND\]/i", Array('\Bitrix\Im\Text', 'setReplacement'), $text);
		$text = preg_replace_callback("/\[USER=([0-9]{1,})\]\[\/USER\]/i", Array('\Bitrix\Im\Text', 'modifyShortUserTag'), $text);

		if ($cutStrikeParam === 'Y')
		{
			$text = preg_replace("/\[s\].*?\[\/s\]/i", "", $text);
		}

		$text = $parser->convertText($text);

		$text = str_replace(['<br />', '#BR#', '[br]'], "\n", $text);
		$text = str_replace(["&#169;", "&#153;", "&#174;"], ["(c)", "(tm)", "(r)"], $text);
		$text = str_replace("&nbsp;", " ", $text);
		$text = str_replace("&quot;", "\"", $text);
		$text = str_replace("&#092;", "\\", $text);
		$text = str_replace("&#036;", "\$", $text);
		$text = str_replace("&#33;", "!", $text);
		$text = str_replace("&#91;", "[", $text);
		$text = str_replace("&#93;", "]", $text);
		$text = str_replace("&#39;", "'", $text);
		$text = str_replace("&lt;", "<", $text);
		$text = str_replace("&gt;", ">", $text);
		$text = str_replace("&#124;", '|', $text);
		$text = str_replace("&amp;", "&", $text);

		$text = self::recoverReplacements($text);

		return $text;
	}

	public static function parseLegacyFormat($text, $params = Array())
	{
		if (!$text)
		{
			return '';
		}

		$safeParam = $params['SAFE'] ?? null;
		$linkParam = $params['LINK'] ?? null;
		$fontParam = $params['FONT'] ?? null;
		$smilesParam = $params['SMILES'] ?? null;
		$textAnchorParam = $params['TEXT_ANCHOR'] ?? null;
		$linkLimitParam = $params['LINK_LIMIT'] ?? null;
		$textLimitParam = $params['TEXT_LIMIT'] ?? null;
		$linkTargetSelfParam = $params['LINK_TARGET_SELF'] ?? null;
		$cutStrikeParam = $params['CUT_STRIKE'] ?? null;

		if (!$safeParam || $safeParam === 'Y')
		{
			$text = htmlspecialcharsbx($text);
		}

		$allowTags = [
			'HTML' => 'N',
			'USER' => 'N',
			'ANCHOR' => $linkParam === 'N' ? 'N' : 'Y',
			'BIU' => 'Y',
			'IMG' => 'N',
			'QUOTE' => 'N',
			'CODE' => 'N',
			'FONT' => $fontParam === 'Y' ? 'Y' : 'N',
			'LIST' => 'N',
			'SPOILER' => 'N',
			'SMILES' => $smilesParam === 'N' ? 'N' : 'Y',
			'EMOJI' => 'Y',
			'NL2BR' => 'Y',
			'VIDEO' => 'N',
			'TABLE' => 'N',
			'CUT_ANCHOR' => 'N',
			'SHORT_ANCHOR' => 'N',
			'ALIGN' => 'N',
			'TEXT_ANCHOR' => $textAnchorParam === 'N' ? 'N' : 'Y',
		];

		$parseId = md5('legacy'.$linkParam.$smilesParam.$linkLimitParam.$textLimitParam.$linkTargetSelfParam);
		if (isset(self::$parsers[$parseId]))
		{
			$parser = self::$parsers[$parseId];
		}
		else
		{
			$parser = new \CTextParser();
			$parser->serverName = Common::getPublicDomain();
			$parser->maxAnchorLength = intval($linkLimitParam)? $linkLimitParam: 55;
			$parser->maxStringLen = intval($textLimitParam);
			$parser->allow = $allowTags;
			if ($linkTargetSelfParam === 'Y')
			{
				$parser->link_target = "_self";
			}

			self::$parsers[$parseId] = $parser;
		}

		$text = preg_replace_callback("/\[PUT(?:=(.+?))?\](.+?)?\[\/PUT\]/i", Array('\Bitrix\Im\Text', 'setReplacement'), $text);
		$text = preg_replace_callback("/\[SEND(?:=(.+?))?\](.+?)?\[\/SEND\]/i", Array('\Bitrix\Im\Text', 'setReplacement'), $text);
		$text = preg_replace_callback("/\[CODE\](.*?)\[\/CODE\]/si", Array('\Bitrix\Im\Text', 'setReplacement'), $text);
		$text = preg_replace_callback("/\[USER=([0-9]{1,})\]\[\/USER\]/i", Array('\Bitrix\Im\Text', 'modifyShortUserTag'), $text);

		if ($cutStrikeParam === 'Y')
		{
			$text = preg_replace("/\[s\].*?\[\/s\]/i", "", $text);
		}

		$text = $parser->convertText($text);

		$text = str_replace(array('#BR#', '[br]', '[BR]'), '<br/>', $text);

		$text = self::recoverReplacements($text);

		return $text;
	}

	public static function getReplaceMap($text)
	{
		$replaces = [];

		$dates = self::getDateConverterParams($text);
		foreach ($dates as $result)
		{
			$replaces[] = [
				'TYPE' => 'DATE',
				'TEXT' => $result->getText(),
				'VALUE' => $result->getDate(),
				'START' => $result->getTextPosition(),
				'END' => $result->getTextPosition()+$result->getTextLength(),
			];
		}

		return self::resolveIntersect($replaces);
	}

	private static function resolveIntersect(array $segments): array
	{
		usort($segments, fn(array $segmentA, array $segmentB) => $segmentA['START'] <=> $segmentB['START']);

		$result = [];
		$maxEnd = -1;

		foreach ($segments as $segment)
		{
			if ($segment['START'] > $maxEnd)
			{
				$result[] = $segment;
				$maxEnd = $segment['END'];
			}
		}

		return array_reverse($result);
	}

	/**
	 * @param $text
	 * @return \Bitrix\Main\Text\DateConverterResult[]
	 */
	public static function getDateConverterParams($text)
	{
		if ($text == '')
			return Array();

		$text = preg_replace_callback("/\[PUT(?:=(.+?))?\](.+?)?\[\/PUT\]/i", Array('\Bitrix\Im\Text', 'setReplacement'), $text);
		$text = preg_replace_callback("/\[SEND(?:=(.+?))?\](.+?)?\[\/SEND\]/i", Array('\Bitrix\Im\Text', 'setReplacement'), $text);
		$text = preg_replace_callback('/\[URL\=([^\]]*)\]([^\]]*)\[\/URL\]/i', Array('\Bitrix\Im\Text', 'setReplacement'), $text);
		$text = preg_replace_callback('/(https?):\/\/(([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+(:([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+)?@)?(?#)((([a-z0-9]\.|[a-z0-9][a-z0-9-]*[a-z0-9]\.)*[a-z][a-z0-9-]*[a-z0-9]|((\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])\.){3}(\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5]))(:\d+)?)(((\/+([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)*(\?([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)?)?)?(#([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)?/im', Array('\Bitrix\Im\Text', 'setReplacement'), $text);
		$text = preg_replace_callback('#\-{54}(.+?)\-{54}#s', Array('\Bitrix\Im\Text', 'setReplacement'), $text);

		return \Bitrix\Main\Text\DateConverter::decode($text, 1000);
	}

	public static function isOnlyEmoji($text)
	{
		$total = 0;
		$count = 0;

		$pattern = '%(?:
				\xF0[\x90-\xBF][\x80-\xBF]{2} # planes 1-3
				| [\xF1-\xF3][\x80-\xBF]{3} # planes 4-15
				| \xF4[\x80-\x8F][\x80-\xBF]{2} # plane 16
			)%xs';
		$text = preg_replace_callback($pattern, function () {return "";}, $text, 4-$total, $count);
		$total += $count;

		if ($total > 3)
		{
			return false;
		}

		if ($total <= 0)
		{
			return false;
		}

		$text = trim($text);

		return !$text;
	}

	public static function setReplacement($match)
	{
		$code = '####REPLACEMENT_MARK_'.count(self::$replacements).'####';

		self::$replacements[$code] = $match[0];

		return $code;
	}

	public static function recoverReplacements($text)
	{
		if (empty(self::$replacements))
		{
			return $text;
		}

		foreach(self::$replacements as $code => $value)
		{
			$text = str_replace($code, $value, $text);
		}

		if (mb_strpos($text, '####REPLACEMENT_MARK_') !== false)
		{
			$text = self::recoverReplacements($text);
		}

		self::$replacements = Array();

		return $text;
	}

	public static function modifyShortUserTag($matches)
	{
		$userId = $matches[1];
		$userName = \Bitrix\Im\User::getInstance($userId)->getFullName(false);
		return '[USER='.$userId.' REPLACE]'.$userName.'[/USER]';
	}

	public static function removeBbCodes($text, $withFile = false, $attachValue = false)
	{
		if ($attachValue)
		{
			if ($attachValue === true || preg_match('/^(\d+)$/', $attachValue))
			{
				$text .= " [".Loc::getMessage('IM_MESSAGE_ATTACH')."]";
			}
			else
			{
				$text .= ' '. $attachValue;
			}
		}

		$text = preg_replace("/\[s\](.*?)\[\/s\]/i", "", $text);
		$text = preg_replace("/\[[buis]\](.*?)\[\/[buis]\]/i", "$1", $text);
		$text = preg_replace("/\[url\](.*?)\[\/url\]/iu", "$1", $text);
		$text = preg_replace("/\[url\\s*=\\s*((?:[^\\[\\]]++|\\[ (?: (?>[^\\[\\]]+) | (?:\\1) )* \\])+)\\s*\\](.*?)\\[\\/url\\]/ixsu", "$2", $text);
		$text = preg_replace("/\[RATING=([1-5]{1})\]/i", " [".Loc::getMessage('IM_MESSAGE_RATING')."] ", $text);
		$text = preg_replace("/\[ATTACH=([0-9]{1,})\]/i", " [".Loc::getMessage('IM_MESSAGE_ATTACH')."] ", $text);
		$text = preg_replace_callback("/\[USER=([0-9]{1,})\]\[\/USER\]/i", Array('\Bitrix\Im\Text', 'modifyShortUserTag'), $text);
		$text = preg_replace("/\[USER=([0-9]+)( REPLACE)?](.*?)\[\/USER]/i", "$3", $text);
		$text = preg_replace("/\[dialog=(chat\d+|\d+:\d)(?: message=(\d+))?](.*?)\[\/dialog]/i", "$3", $text);
		$text = preg_replace("/\[context=(chat\d+|\d+:\d+)\/(\d+)](.*?)\[\/context]/i", "$3", $text);
		$text = preg_replace("/\[CHAT=([0-9]{1,})\](.*?)\[\/CHAT\]/i", "$2", $text);
		$text = preg_replace("/\[SEND(?:=(.+?))?\](.+?)?\[\/SEND\]/i", "$2", $text);
		$text = preg_replace("/\[PUT(?:=(.+?))?\](.+?)?\[\/PUT\]/i", "$2", $text);
		$text = preg_replace("/\[CALL(?:=(.+?))?\](.+?)?\[\/CALL\]/i", "$2", $text);
		$text = preg_replace("/\[PCH=([0-9]{1,})\](.*?)\[\/PCH\]/i", "$2", $text);
		$text = preg_replace("/\[size=(\d+)](.*?)\[\/size]/i", "$2", $text);
		$text = preg_replace("/\[color=#([0-9a-f]{3}|[0-9a-f]{6})](.*?)\[\/color]/i", "$2", $text);
		$text = preg_replace_callback("/\[ICON\=([^\]]*)\]/i", Array('\Bitrix\Im\Text', 'modifyIcon'), $text);
		$text = preg_replace('#\-{54}.+?\-{54}#s', " [".Loc::getMessage('IM_QUOTE')."] ", str_replace(array("#BR#"), Array(" "), $text));
		$text = trim($text);

		if ($withFile)
		{
			$text .= " [".Loc::getMessage('IM_MESSAGE_FILE')."]";
		}

		$text = trim($text);

		if ($text == '')
		{
			$text = Loc::getMessage('IM_MESSAGE_DELETE');
		}

		return $text;
	}

	public static function populateUserBbCode(string $text): string
	{
		return preg_replace_callback("/\[USER=([0-9]{1,})\]\[\/USER\]/i", static function($matches){
			$userId = $matches[1];
			$userName = \Bitrix\Im\User::getInstance($userId)->getFullName(false);
			return '[USER='.$userId.' REPLACE]'.$userName.'[/USER]';
		}, $text);
	}

	public static function encodeEmoji($text)
	{
		return \Bitrix\Main\Text\Emoji::encode($text);
	}

	public static function decodeEmoji($text)
	{
		return \Bitrix\Main\Text\Emoji::decode($text);
	}

	public static function getEmoji($code, $fallbackText = '')
	{
		if (!isset(self::$emojiList[$code]))
		{
			return $fallbackText;
		}

		return self::decodeEmoji(self::$emojiList[$code]);
	}

	public static function getEmojiList(): ?array
	{
		return array_map(fn ($element) => self::decodeEmoji($element), self::$emojiList);
	}

	public static function convertHtmlToBbCode($html)
	{
		if (!is_string($html))
		{
			return $html;
		}

		$html = str_replace('&nbsp;', ' ', $html);
		$html = str_replace('<hr>', '------[BR]', $html);
		$html = str_replace('#BR#', '[BR]', $html);

		$replaced = 0;
		do
		{
			$html = preg_replace(
				"/<([busi])[^>a-z]*>(.+?)<\\/(\\1)[^>a-z]*>/isu",
				"[\\1]\\2[/\\1]",
				$html, -1, $replaced
			);
		}
		while($replaced > 0);

		$html = preg_replace("/\\<br\s*\\/*\\>/isu","[br]", $html);
		$html = preg_replace(
			[
				"#<a[^>]+href\\s*=\\s*('|\")(.+?)(?:\\1)[^>]*>(.*?)</a[^>]*>#isu",
				"#<a[^>]+href(\\s*=\\s*)([^'\">]+)>(.*?)</a[^>]*>#isu"
			],
			"[url=\\2]\\3[/url]", $html
		);
		$html = preg_replace(
			["/<font[^>]+color\s*=[\s'\"]*#([0-9a-f]{3}|[0-9a-f]{6})[\s'\"]*>(.+?)<\/font[^>]*>/iu"],
			["[color=#\\1]\\2[/color]"],
			$html
		);
		$html = preg_replace(
			["/<span[^>]+color\s*=[\s'\"]*#([0-9a-f]{3}|[0-9a-f]{6})[\s'\"]*>(.+?)<\/span[^>]*>/iu"],
			["[color=#\\1]\\2[/color]"],
			$html
		);
		$html = preg_replace(
			["/<font[^>]+size\s*=[\s'\"]*(\d+)[\s'\"]*>(.+?)<\/font[^>]*>/iu"],
			["[size=\\1]\\2[/size]"],
			$html
		);

		$replaced = 0;
		do
		{
			$html = preg_replace(
				"/<div(?:.*?)>(.*?)<\/div>/iu",
				"\\1",
				$html, -1, $replaced
			);
		}
		while($replaced > 0);

		$replaced = 0;
		do
		{
			$html = preg_replace(
				"/<span(?:.*?)>(.*?)<\/span>/iu",
				"\\1",
				$html, -1, $replaced
			);
		}
		while($replaced > 0);

		$html = preg_replace(
			"/<font(?:.*?)>(.*?)<\/font>/iu",
			"\\1",
			$html
		);

		return $html;
	}

	public static function modifyIcon($params)
	{
		$text = $params[1];

		$title = Loc::getMessage('IM_MESSAGE_ICON');

		preg_match('/title\=(.*[^\s\]])/i', $text, $match);
		if ($match)
		{
			$title = $match[1];
			if (mb_strpos($title, 'width=') !== false)
			{
				$title = mb_substr($title, 0, mb_strpos($title, 'width='));
			}
			if (mb_strpos($title, 'height=') !== false)
			{
				$title = mb_substr($title, 0, mb_strpos($title, 'height='));
			}
			if (mb_strpos($title, 'size=') !== false)
			{
				$title = mb_substr($title, 0, mb_strpos($title, 'size='));
			}
			$title = trim($title);
		}

		return '('.$title.')';
	}

	public static function modifySendPut($params)
	{
		$code = mb_strpos(mb_strtoupper($params[0]), '[SEND') === 0? 'SEND': 'PUT';
		return preg_replace("/\[$code(?:=(.+))?\](.+?)?\[\/$code\]/i", "$2", $params[0]);
	}
}