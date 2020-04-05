<?php
namespace Bitrix\Im\Integration\Imopenlines;

class Localize
{
	static $MESS = Array();

	const FILE_LIB_CHAT = 'FILE_LIB_CHAT';

	private static function resolveType($type)
	{
		$types = Array(
			'FILE_LIB_CHAT' => '/bitrix/modules/imopenlines/lib/chat.php',
		);

		return isset($types[$type])? $types[$type]: false;
	}

	public static function get($type, $phraseCode = null, $lang = null)
	{
		$path = self::resolveType($type);
		if (!$path)
		{
			return is_string($phraseCode)? '': Array();
		}

		if (!is_string($lang))
		{
			$lang = null;
		}

		if (!isset(self::$MESS[$type][$lang]))
		{
			self::$MESS[$type][$lang] = \Bitrix\Main\Localization\Loc::loadLanguageFile($_SERVER['DOCUMENT_ROOT'].$path, $lang);
		}

		if (is_string($phraseCode))
		{
			return isset(self::$MESS[$type][$lang][$phraseCode])? self::$MESS[$type][$lang][$phraseCode]: '';
		}
		else
		{
			return isset(self::$MESS[$type][$lang])? self::$MESS[$type][$lang]: Array();
		}
	}
}