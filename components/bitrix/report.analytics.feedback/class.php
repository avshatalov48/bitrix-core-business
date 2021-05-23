<?php

class ReportAnalyticsFeedback extends CBitrixComponent
{
	public function executeComponent()
	{
		$this->arResult['BOARD_KEY'] = $this->arParams['BOARD_KEY'];
		$this->includeComponentTemplate();
	}
}