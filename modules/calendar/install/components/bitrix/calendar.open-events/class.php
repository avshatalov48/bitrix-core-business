<?php

use Bitrix\Calendar\OpenEvents\Filter\Filter;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CalendarOpenEventsComponent extends CBitrixComponent
{
	public function executeComponent(): void
	{
		$this->arResult['FILTER_ID'] = Filter::getId();

		$this->includeComponentTemplate();
	}
}
