<?php


namespace Bitrix\Sale\Rest\Entity;


use Bitrix\Main\Localization\Loc;

class BusinessValuePersonDomainType
{
	const UNDEFINED = 0;
	const TYPE_I = 1;
	const TYPE_E = 2;

	const TYPE_I_NAME = 'I';
	const TYPE_E_NAME = 'E';

	static private $descriptions = [];

	public static function isDefined($typeID)
	{
		if(!is_numeric($typeID))
		{
			return false;
		}

		$typeID = intval($typeID);
		return $typeID >= self::TYPE_I && $typeID <= self::TYPE_E;
	}

	public static function resolveName($typeID)
	{
		if(!is_numeric($typeID))
		{
			return '';
		}

		$typeID = intval($typeID);
		if($typeID <= 0)
		{
			return '';
		}

		switch($typeID)
		{
			case self::TYPE_I:
				return self::TYPE_I_NAME;
			case self::TYPE_E:
				return self::TYPE_E_NAME;
			case self::UNDEFINED:
			default:
				return '';
		}
	}

	public static function resolveID($name)
	{
		$name = strtoupper(trim($name));
		if($name == '')
		{
			return self::UNDEFINED;
		}

		switch($name)
		{
			case self::TYPE_I_NAME:
				return self::TYPE_I;
			case self::TYPE_E_NAME:
				return self::TYPE_E;
			default:
				return self::UNDEFINED;
		}
	}

	public static function getAllDescriptions()
	{
		if(!self::$descriptions[LANGUAGE_ID])
		{
			Loc::loadMessages($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale/admin/person_type_edit.php');
			self::$descriptions[LANGUAGE_ID] = [
				self::TYPE_I => GetMessage('SPTEN_DOMAIN_P_TYPE_I'),
				self::TYPE_E => GetMessage('SPTEN_DOMAIN_P_TYPE_E'),
			];
		}

		return self::$descriptions[LANGUAGE_ID];
	}

	public static function getDescription($typeId)
	{
		$typeId = intval($typeId);
		$all = self::getAllDescriptions();
		return isset($all[$typeId]) ? $all[$typeId] : '';
	}
}