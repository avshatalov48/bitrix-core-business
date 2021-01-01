<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Loader;

class CUiInfoHelperComponent extends \CBitrixComponent
{
	public function executeComponent()
	{
		if (!Loader::includeModule("ui"))
		{
			return;
		}

		$this->arResult["CONFIG"] = \Bitrix\UI\InfoHelper::getInitParams();

		$this->includeComponentTemplate();
	}
}
?>