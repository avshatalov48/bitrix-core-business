<?php
namespace Bitrix\Sale\Rest\Entity;

use Bitrix\Main\Localization\Loc;

class RelationType
{
	const UNDEFINED = 0;
	const PAYSYSTEM = 1;
	const DELIVERY = 2;

	const PAYSYSTEM_NAME = 'P';
	const DELIVERY_NAME = 'D';

	static private $descriptions = [];

	public static function isDefined($typeID)
	{
		if(!is_numeric($typeID))
		{
			return false;
		}

		$typeID = intval($typeID);
		return $typeID >= self::PAYSYSTEM && $typeID <= self::DELIVERY;
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
			case self::PAYSYSTEM:
				return self::PAYSYSTEM_NAME;
			case self::DELIVERY:
				return self::DELIVERY_NAME;
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
			case self::PAYSYSTEM_NAME:
				return self::PAYSYSTEM;
			case self::DELIVERY_NAME:
				return self::DELIVERY;
			default:
				return self::UNDEFINED;
		}
	}

	public static function getAllDescriptions()
	{
		if(!self::$descriptions[LANGUAGE_ID])
		{
			Loc::loadMessages($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale/admin/order_props_edit.php');
			self::$descriptions[LANGUAGE_ID] = [
				self::PAYSYSTEM => GetMessage('SALE_PROPERTY_PAYSYSTEM'),
				self::DELIVERY => GetMessage('SALE_PROPERTY_DELIVERY'),
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