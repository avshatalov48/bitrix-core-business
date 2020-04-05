<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Report\VisualConstructor\Helper\Dashboard;
use Bitrix\Report\VisualConstructor\Helper\Filter;

\Bitrix\Main\Loader::includeModule('report');

/**
 * Class ReportVisualConstructorBoardBase
 */
class ReportVisualConstructorBoardBase extends CBitrixComponent
{
	private $isDefaultModeIsDemo;

	private $buttons = [];
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
		$this->arResult['FILTER'] = $this->arParams['FILTER'] instanceof Filter ? $this->arParams['FILTER'] : null;
		$this->arResult['FILTER_ID'] = $this->arParams['FILTER'] instanceof Filter ? $this->arParams['FILTER']->getFilterParameters()['FILTER_ID'] : "";
		$this->arResult['REPORTS_CATEGORIES'] = $this->arParams['REPORTS_CATEGORIES'];
		$this->arResult['IS_ENABLED_STEPPER'] = isset($this->arParams['IS_ENABLED_STEPPER']) ? $this->arParams['IS_ENABLED_STEPPER'] : false;
		$this->arResult['STEPPER_IDS'] = isset($this->arParams['STEPPER_IDS']) ? $this->arParams['STEPPER_IDS'] : [];
		$this->arResult['HEADER_TEMPLATE_NAME'] = !empty($this->arParams['VIEW_MODE']) && $this->arParams['VIEW_MODE'] === 'slider' ? 'slider' : '';
		$this->arResult['IS_BOARD_DEFAULT'] = isset($this->arParams['IS_BOARD_DEFAULT']) ? $this->arParams['IS_BOARD_DEFAULT'] : false;
		$this->arResult['BOARD_BUTTONS'] = $this->getBoardButtons();

		if (isset($this->arParams['IS_DEFAULT_MODE_DEMO']))
		{
			Dashboard::updateBoardCustomDefaultMode($this->arParams['BOARD_ID'], $this->arParams['IS_DEFAULT_MODE_DEMO']);
		}

		$this->arResult['IS_BOARD_DEMO'] = Dashboard::getBoardModeIsDemo($this->arParams['BOARD_ID']);

		$preparedDashboard = \Bitrix\Report\VisualConstructor\Helper\Widget::prepareBoardWithEntitiesByBoardId($this->arParams['BOARD_ID']);

		$this->arResult['ROWS'] = isset($preparedDashboard['rows']) ? $preparedDashboard['rows'] : array();
		$this->includeComponentTemplate();
	}

	private function getBoardButtons()
	{
		$buttons = $this->getBoardDefaultButtons();
		if (isset($this->arParams['BOARD_BUTTONS']))
		{
			foreach ($this->arParams['BOARD_BUTTONS'] as $button)
			{
				$buttons[] = $button;
			}
		}

		return $buttons;
	}

	private function getBoardDefaultButtons()
	{
		$defaultButtons = [];
		if ($this->arParams['WITH_ADD_BUTTON'])
		{
			$componentName = 'bitrix:report.visualconstructor.board.controls';
			$componentParams = [
				'BOARD_ID' => $this->arParams['BOARD_ID'],
				'REPORTS_CATEGORIES' => $this->arParams['REPORTS_CATEGORIES']
			];

			$defaultButtons[] = new \Bitrix\Report\VisualConstructor\BoardComponentButton($componentName, '', $componentParams);
		}
		return $defaultButtons;
	}

	/**
	 * @deprecated
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