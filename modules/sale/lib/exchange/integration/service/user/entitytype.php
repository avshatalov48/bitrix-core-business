<?php
namespace Bitrix\Sale\Exchange\Integration\Service\User;


class EntityType
{
	const UNDEFINED = 0;
	const TYPE_I = 1;
	const TYPE_E = 2;

	const TYPE_I_NAME = 'I';
	const TYPE_E_NAME = 'E';

	public static function isDefined($typeId)
	{
		if(!is_numeric($typeId))
		{
			return false;
		}

		$typeId = intval($typeId);
		return $typeId >= self::TYPE_I && $typeId <= self::TYPE_E;
	}

	public static function resolveName($typeId)
	{
		if(!is_numeric($typeId))
		{
			return '';
		}

		$typeId = intval($typeId);
		if($typeId <= 0)
		{
			return '';
		}

		switch($typeId)
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

	public static function resolveId($name)
	{
		$name = mb_strtoupper(trim($name));
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
}