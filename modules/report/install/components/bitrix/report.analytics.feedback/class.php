<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

class ReportAnalyticsFeedback extends CBitrixComponent
{
	public function executeComponent()
	{
		$this->arResult['BOARD_KEY'] = $this->arParams['BOARD_KEY'];
		$this->includeComponentTemplate();
	}
}