<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Iblock\Component\UserField\Catalog\CheckAccessTrait;
use Bitrix\Main\Component\BaseUfComponent;
use Bitrix\Iblock\UserField\Types\SectionType;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class SectionUfComponent
 *
 * @todo Now autoloader not load ElementUfComponent
 */
class SectionUfComponent extends BaseUfComponent
{
	use CheckAccessTrait;

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
		return SectionType::USER_TYPE_ID;
	}

	/**
	 * @inheritDoc
	 */
	protected function prepareResult(): void
	{
		parent::prepareResult();

		$this->arResult['hasAccessToCatalog'] = $this->hasAccessToCatalog();
	}
}
