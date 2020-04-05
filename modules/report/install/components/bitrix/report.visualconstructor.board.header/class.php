<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class ReportVisualConstructorBoardHeader extends CBitrixComponent
{
	public function executeComponent()
	{
		$this->arResult['BOARD_ID'] = $this->arParams['BOARD_ID'];
		$this->arResult['REPORTS_CATEGORIES'] = $this->arParams['REPORTS_CATEGORIES'];
		$this->arResult['FILTER'] = $this->arParams['FILTER'];

		$this->includeComponentTemplate();
	}


}