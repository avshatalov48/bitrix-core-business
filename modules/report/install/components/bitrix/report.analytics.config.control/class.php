<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

class ReportAnalyticsConfigControl extends CBitrixComponent
{
	public function executeComponent()
	{
		$this->arResult['BOARD_ID'] = !empty($this->arParams['BOARD_ID']) ? $this->arParams['BOARD_ID'] : '';
		$this->arResult['BOARD_OPTIONS'] =
			!empty($this->arParams['BOARD_OPTIONS']) && is_array($this->arParams['BOARD_OPTIONS'])
				? $this->arParams['BOARD_OPTIONS']
				: []
		;
		$this->includeComponentTemplate();
	}
}