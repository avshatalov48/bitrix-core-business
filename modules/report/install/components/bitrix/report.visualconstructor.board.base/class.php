<?php

use Bitrix\Report\VisualConstructor\Helper\Dashboard;
use Bitrix\Report\VisualConstructor\Helper\Filter;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
\Bitrix\Main\Loader::includeModule('report');

/**
 * Class ReportVisualConstructorBoardBase
 */
class ReportVisualConstructorBoardBase extends CBitrixComponent
{
	private $isDefaultModeIsDemo;
	/**
	 * Function calls __includeComponent in order to execute the component.
	 *
	 * @return void
	 *
	 */
	public function executeComponent()
	{
		Dashboard::renewDefaultDashboard($this->arParams['BOARD_ID']);
		$this->arResult['BOARD_ID'] = $this->arParams['BOARD_ID']; //TODO@ add check for required params such us BOARD_ID
		$this->arResult['FILTER'] = $this->getFilter();
		$this->arResult['REPORTS_CATEGORIES'] = $this->arParams['REPORTS_CATEGORIES'];
		if (isset($this->arParams['IS_DEFAULT_MODE_DEMO']))
		{
			Dashboard::updateBoardCustomDefaultMode($this->arParams['BOARD_ID'], $this->arParams['IS_DEFAULT_MODE_DEMO']);
		}

		$this->arResult['IS_BOARD_DEMO'] = Dashboard::getBoardModeIsDemo($this->arParams['BOARD_ID']);

		$preparedDashboard = \Bitrix\Report\VisualConstructor\Helper\Widget::prepareBoardWithEntitiesByBoardId($this->arParams['BOARD_ID']);

		$this->arResult['ROWS'] = isset($preparedDashboard['rows']) ? $preparedDashboard['rows'] : array();
		$this->includeComponentTemplate();
	}


	/**
	 * @return Filter
	 */
	private function getFilter()
	{
		if (isset($this->arParams['FILTER']) && $this->arParams['FILTER'] instanceof Filter)
		{
			return $this->arParams['FILTER'];
		}
		else
		{
			return new Filter($this->arResult['BOARD_ID']);
		}
	}
}