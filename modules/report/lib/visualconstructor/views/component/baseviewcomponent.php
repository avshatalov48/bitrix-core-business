<?php

namespace Bitrix\Report\VisualConstructor\Views\Component;

use Bitrix\Report\VisualConstructor\IReportSingleData;

/**
 * Class BaseComponent created for prevent duplicating code for all component widget views
 * @package Bitrix\Report\VisualConstructor\Views\Component
 */
abstract class BaseViewComponent extends \CBitrixComponent
{
	/**
	 * Common execute method for all views of type component.
	 * @return void
	 */
	public function executeComponent()
	{
		/**
		 * @see IReportSingleData::getSingleData()
		 */
		$this->arResult['CALCULATION_RESULT'] = $this->arParams['RESULT'] ?? null;
		$this->arResult['WIDGET'] = $this->arParams['WIDGET'] ?? null;
		$this->arResult['HEIGHT'] = $this->arParams['HEIGHT'] ?? null;
		$this->arResult['WIDGET_COLOR'] = $this->arParams['WIDGET_COLOR'] ?? null;
		$this->includeComponentTemplate();
	}
}