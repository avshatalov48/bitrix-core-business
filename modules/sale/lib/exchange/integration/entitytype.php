<?php


namespace Bitrix\Sale\Exchange\Integration;


use Bitrix\Main\Localization\Loc;

class EntityType
{
	const UNDEFINED = 0;
	const ORDER = 1;
	const USER = 2;

	private static $ALL_DESCRIPTIONS = array();

	public static function getDescription($typeId)
	{
		$typeId = intval($typeId);
		$all = self::getAllDescriptions();
		return isset($all[$typeId]) ? $all[$typeId] : '';
	}

	public static function getAllDescriptions()
	{
		if(!self::$ALL_DESCRIPTIONS[LANGUAGE_ID])
		{
			Loc::loadLanguageFile(__FILE__);

			self::$ALL_DESCRIPTIONS[LANGUAGE_ID] = array(
				self::ORDER => Loc::getMessage('SALE_INTEGRATION_ORDER_TYPE'),
				self::USER => Loc::getMessage('SALE_INTEGRATION_USER_TYPE'),
			);
		}

		return self::$ALL_DESCRIPTIONS[LANGUAGE_ID];
	}
}