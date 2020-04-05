<?php
use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Iblock;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

if (!Loader::includeModule('iblock'))
{
	ShowError(Loc::getMessage('IBLOCK_SELECTOR_ELEMENT_ERR_IBLOCK_MODULE_NOT_INSTALLED'));
	return;
}

class IblockElement extends Iblock\Component\Selector\Element
{
	public function __construct($component = null)
	{
		parent::__construct($component);
	}
}