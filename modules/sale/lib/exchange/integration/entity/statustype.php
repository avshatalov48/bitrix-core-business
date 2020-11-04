<?php


namespace Bitrix\Sale\Exchange\Integration\Entity;


use Bitrix\Main\Localization\Loc;

class StatusType
{
	const UNDEFINED = 0;
	const SUCCESS = 1;
	const PROCESS = 2;
	const FAULTY = 3;

	const SUCCESS_NAME = 'S';
	const PROCESS_NAME = 'P';
	const FAULTY_NAME = 'F';

	private static $ALL_DESCRIPTIONS = array();

	public static function isDefined($typeId)
	{
		if(!is_int($typeId))
		{
			$typeId = (int)$typeId;
		}

		return ($typeId == static::SUCCESS
			|| $typeId == static::FAULTY
			|| $typeId == static::PROCESS);
	}

	public static function resolveId($name)
	{
		if($name == '')
		{
			return self::UNDEFINED;
		}

		switch($name)
		{
			case self::SUCCESS_NAME:
				return self::SUCCESS;
			case self::FAULTY_NAME:
				return self::FAULTY;
			case self::PROCESS_NAME:
				return self::PROCESS;

			default:
				return self::UNDEFINED;
		}
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
			case self::SUCCESS:
				return self::SUCCESS_NAME;
			case self::FAULTY:
				return self::FAULTY_NAME;
			case self::PROCESS:
				return self::PROCESS_NAME;

			case self::UNDEFINED:
			default:
				return '';
		}
	}

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
				self::SUCCESS => Loc::getMessage('SALE_INTEGRATION_STATUS_SUCCESS'),
				self::FAULTY => Loc::getMessage('SALE_INTEGRATION_STATUS_FAULTY'),
				self::PROCESS => Loc::getMessage('SALE_INTEGRATION_STATUS_PROCESS'),
			);
		}

		return self::$ALL_DESCRIPTIONS[LANGUAGE_ID];
	}
}