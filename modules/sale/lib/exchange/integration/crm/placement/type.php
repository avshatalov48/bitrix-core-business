<?php
namespace Bitrix\Sale\Exchange\Integration\CRM\Placement;

class Type
{
	const UNDEFINED = 0;
	const DEFAULT_TOOLBAR = 1;
	const DEAL_DETAIL_TOOLBAR = 2;

	const DEFAULT_TOOLBAR_NAME = 'DEFAULT';
	const DEAL_DETAIL_TOOLBAR_NAME = 'CRM_DEAL_DETAIL_TOOLBAR';

	const FIRST_TYPE = 1;
	const LAST_TYPE = 2;

	public static function isDefined($typeId)
	{
		if(!is_int($typeId))
		{
			$typeId = (int)$typeId;
		}

		return $typeId >= self::FIRST_TYPE && $typeId <= self::LAST_TYPE;
	}

	public static function resolveId($name)
	{
		if($name == '')
		{
			return self::UNDEFINED;
		}

		switch($name)
		{
			case self::DEFAULT_TOOLBAR_NAME:
				return self::DEFAULT_TOOLBAR;
			case self::DEAL_DETAIL_TOOLBAR_NAME:
				return self::DEAL_DETAIL_TOOLBAR;

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
			case self::DEFAULT_TOOLBAR:
				return self::DEFAULT_TOOLBAR_NAME;
			case self::DEAL_DETAIL_TOOLBAR:
				return self::DEAL_DETAIL_TOOLBAR_NAME;

			case self::UNDEFINED:
			default:
				return '';
		}
	}
}