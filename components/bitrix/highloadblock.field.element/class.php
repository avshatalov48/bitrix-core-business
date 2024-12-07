<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Component\BaseUfComponent;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

/**
 * Class ElementUfComponent
 */
class HighloadblockElementUfComponent extends BaseUfComponent
{
	protected static bool $highloadblockIncluded;

	public function __construct($component = null)
	{
		if (!isset(self::$highloadblockIncluded))
		{
			self::$highloadblockIncluded = Loader::includeModule('highloadblock');
		}
		parent::__construct($component);
	}

	/**
	 * @return bool
	 */
	public function isHighloadblockIncluded():bool
	{
		return static::$highloadblockIncluded ?? false;
	}

	protected static function getUserTypeId(): string
	{
		return \CUserTypeHlblock::USER_TYPE_ID;
	}

	/**
	 * @inheritDoc
	 */
	protected function prepareResult(): void
	{
		parent::prepareResult();

		$this->arResult['defaultSettings'] = \CUserTypeHlblock::getDefaultSettings($this->isMultiple());
	}
}
