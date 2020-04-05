<?php

class ReportAnalyticsConfigControl extends CBitrixComponent
{
	public function executeComponent()
	{
		$this->arResult['BOARD_ID'] = !empty($this->arParams['BOARD_ID']) ? $this->arParams['BOARD_ID'] : '';
		$this->includeComponentTemplate();
	}
}