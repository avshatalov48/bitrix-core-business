<?php

use Bitrix\Crm\Widget\FilterPeriodType;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class ReportVisualConstructorBoardFilter extends CBitrixComponent
{
	private $boardId;

	public function executeComponent()
	{
		$this->boardId = $this->arParams['BOARD_ID'];
		$this->arResult['REPORTS_CATEGORIES'] = $this->arParams['REPORTS_CATEGORIES'];
		$this->arResult['FILTER'] = $this->arParams['FILTER'];

		$this->includeComponentTemplate();
	}

}