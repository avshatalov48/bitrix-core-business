<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CUiInfoHelperComponent extends \CBitrixComponent
{
	public function executeComponent()
	{
		\Bitrix\Main\UI\Extension::load('ui.info-helper');
	}
}