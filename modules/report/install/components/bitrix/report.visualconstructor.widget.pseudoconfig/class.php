<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/**
 * Class ReportVisualConstructorWidgetConfig
 */
class ReportVisualConstructorWidgetPseudoConfig extends CBitrixComponent
{
	public function executeComponent()
	{
		$this->arResult['REPORT_HANDLER'] = $this->arParams['REPORT_HANDLER'];
		$this->arResult['WIDGET_ID'] = $this->arParams['WIDGET_ID'];
		$this->arResult['WIDGET'] = \Bitrix\Report\VisualConstructor\Entity\Widget::getCurrentUserWidgetByGId($this->arParams['WIDGET_ID']);
		$this->includeComponentTemplate();
	}

}