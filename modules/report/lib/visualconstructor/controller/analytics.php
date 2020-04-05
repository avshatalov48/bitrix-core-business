<?php

namespace Bitrix\Report\VisualConstructor\Controller;

use Bitrix\ImOpenLines\Integrations\Report\Filter;
use Bitrix\Main\Error;
use Bitrix\Main\UI\Filter\Options;
use Bitrix\Report\VisualConstructor\AnalyticBoard;
use Bitrix\Report\VisualConstructor\Entity\Dashboard;
use Bitrix\Report\VisualConstructor\Internal\Engine\Response\Component;
use Bitrix\Report\VisualConstructor\RuntimeProvider\AnalyticBoardProvider;

/**
 * Controller class for Analytics actions
 * @package Bitrix\Report\VisualConstructor\Controller
 */
class Analytics extends Base
{
	public function openDefaultAnalyticsPageAction()
	{
		//$componentName = 'bitrix:report.analytics.base';
		$componentName = 'bitrix:ui.sidepanel.wrapper';
		$params = [
			'POPUP_COMPONENT_NAME' => 'bitrix:report.analytics.base',
			'POPUP_COMPONENT_TEMPLATE_NAME' => '',
		];
		return new Component($componentName, '', $params);
	}

	public function getBoardComponentByKeyAction($boardKey='')
	{
		$analyticBoard = $this->getAnalyticBoardByKey($boardKey);
		if (!$analyticBoard)
		{
			$this->addError(new Error('Analytic board with this key not exist'));
			return false;
		}

		$additionalParams = [
			'pageTitle' => $analyticBoard->getTitle(),
			'pageControlsParams' => $analyticBoard->getButtonsContent()

		];
		if ($analyticBoard->isDisabled())
		{
			$componentName = 'bitrix:report.analytics.empty';
			$params = [];
		}
		else
		{
			$componentName = 'bitrix:report.visualconstructor.board.base';
			$params = [
				'BOARD_ID' => $boardKey,
				'IS_DEFAULT_MODE_DEMO' => false,
				'IS_BOARD_DEFAULT' => true,
				'FILTER' => $analyticBoard->getFilter(),
				'IS_ENABLED_STEPPER' => $analyticBoard->isStepperEnabled(),
				'STEPPER_IDS' => $analyticBoard->getSteperIds()
			];
		}

		return new Component($componentName, '', $params, $additionalParams);
	}

	/**
	 * @param $boardKey
	 * @return AnalyticBoard
	 */
	private function getAnalyticBoardByKey($boardKey)
	{
		$provider = new AnalyticBoardProvider();
		$provider->addFilter('boardKey', $boardKey);
		$board = $provider->execute()->getFirstResult();
		return $board;
	}

	/**
	 * @param $boardKey
	 *
	 * @return Component|bool
	 */
	public function toggleToDefaultByBoardKeyAction($boardKey)
	{
		$analyticBoardProvider = new AnalyticBoardProvider();
		$analyticBoardProvider->addFilter('boardKey', $boardKey);
		$analyticBoard = $analyticBoardProvider->execute()->getFirstResult();
		if (!$analyticBoard)
		{
			$this->addError(new Error('Analytic board with this key not exist'));
			return false;
		}


		global $USER;
		$userId = $USER->getId();
		$dashboardForUser = Dashboard::loadByBoardKeyAndUserId($boardKey, $userId);
		if ($dashboardForUser)
		{
			$dashboardForUser->delete();
		}



		if (!empty($analyticBoard))
		{
			$filter = $analyticBoard->getFilter();
			$filterId = $filter->getFilterParameters()['FILTER_ID'];

			$options = new Options($filterId, $filter::getPresetsList());
			$options->restore($filter::getPresetsList());
			$options->save();

		}

		$additionalParams = [
			'pageTitle' => $analyticBoard->getTitle(),
			'pageControlsParams' => $analyticBoard->getButtonsContent()

		];
		if ($analyticBoard->isDisabled())
		{
			$componentName = 'bitrix:report.analytics.empty';
			$params = [];
		}
		else
		{
			$componentName = 'bitrix:report.visualconstructor.board.base';
			$params = [
				'BOARD_ID' => $boardKey,
				'IS_DEFAULT_MODE_DEMO' => false,
				'IS_BOARD_DEFAULT' => true,
				'FILTER' => $analyticBoard->getFilter()
			];
		}

		return new Component($componentName, '', $params, $additionalParams);
	}
}