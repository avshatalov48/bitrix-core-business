<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class ReportVisualConstructorBoardHeader extends CBitrixComponent
{
	public function executeComponent()
	{
		$this->arResult['BOARD_ID'] = $this->arParams['BOARD_ID'];
		$this->arResult['REPORTS_CATEGORIES'] = $this->arParams['REPORTS_CATEGORIES'];
		$this->arResult['FILTER'] = $this->arParams['FILTER'];
		$this->arResult['WITH_ADD_BUTTON'] = !$this->arParams['DEFAULT_BOARD'];
		$this->arResult['IS_FRAME_MODE'] = $this->isFrameMode();
		$this->arResult['BOARD_BUTTONS'] = $this->arParams['BOARD_BUTTONS'];
		$this->includeComponentTemplate();

	}

	private function isFrameMode()
	{
		$isFrame = \Bitrix\Main\Application::getInstance()->getContext()->getRequest()->get('IFRAME');
		return $isFrame === "Y";
	}

}