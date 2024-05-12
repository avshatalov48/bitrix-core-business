<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

class CalendarICalMailComponent extends CBitrixComponent
{
	public function executeComponent()
	{
		$this->arResult = $this->arParams['PARAMS'];

		$this->includeComponentTemplate('template-new');
	}
}
