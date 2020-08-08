<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Component\BaseUfComponent;
use Bitrix\Iblock\UserField\Types\ElementType;
use Bitrix\Main\Loader;

/**
 * Class ElementUfComponent
 */
class ElementUfComponent extends BaseUfComponent
{
	protected static
		$iblockIncluded = null;

	public function __construct($component = null)
	{
		if(self::$iblockIncluded === null)
		{
			self::$iblockIncluded = Loader::includeModule('iblock');
		}
		parent::__construct($component);
	}

	/**
	 * @return bool
	 */
	public function isIblockIncluded():bool
	{
		return (static::$iblockIncluded !== null);
	}

	protected static function getUserTypeId(): string
	{
		return ElementType::USER_TYPE_ID;
	}
}