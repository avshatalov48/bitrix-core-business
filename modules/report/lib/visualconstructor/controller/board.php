<?php

namespace Bitrix\Report\VisualConstructor\Controller;

use Bitrix\Main\ArgumentException;
use Bitrix\Report\VisualConstructor\Internal\Engine\Response\Component;
use Bitrix\Report\VisualConstructor\Entity\Dashboard;
use Bitrix\Report\VisualConstructor\Helper\Dashboard as DashboardHelper;
use Bitrix\Report\VisualConstructor\Helper\Row;
use Bitrix\Report\VisualConstructor\Helper\Util;
use Bitrix\Report\VisualConstructor\Internal\Error\Error;

/**
 * Class Board
 * @package Bitrix\Report\VisualConstructor\Controller
 */
class Board extends Base
{

	/**
	 * Set user default board to his own or to default of system.
	 *
	 * @param string $boardKey Key for finding dashboard.
	 * @return bool
	 */
	public function toggleToDefaultAction($boardKey)
	{
		global $USER;
		$userId = $USER->getId();
		$dashboardForUser = Dashboard::loadByBoardKeyAndUserId($boardKey, $userId);
		if ($dashboardForUser)
		{
			$dashboardForUser->delete();
		}
		return true;
	}

	/**
	 * @param string $boardKey Board Key.
	 * @return array
	 */
	public function toggleModeAction($boardKey)
	{
		$oldMode = DashboardHelper::getBoardModeIsDemo($boardKey);
		DashboardHelper::setBoardModeIsDemo($boardKey, !$oldMode);
		return array('demoMode' => !$oldMode);
	}

	/**
	 * Return rendered add form component.
	 *
	 * @param array $categories Categories ids.
	 * @param string $boardId Board Id for pass to add form component.
	 * @return Component
	 */
	public function showAddFormAction($categories, $boardId)
	{
		$componentName = 'bitrix:report.visualconstructor.board.controls';
		$templateName = 'addform';
		$params = array(
			'BOARD_ID' => $boardId,
			'REPORTS_CATEGORIES' => array(),// @TODO
		);
		return new Component($componentName, $templateName, $params);
	}

	/**
	 * Handler for submit dashboard add form.
	 *
	 * @param array $formParams Parameters send from form.
	 * @return array|bool
	 */
	public function submitAddFormAction($formParams = array())
	{
		if ($formParams['boardId'])
		{
			if ($formParams['patternWidgetId'])
			{
				$widgetGId = $this->createWidgetFromExisting($formParams);
			}
			else
			{
				$this->addError(new Error('Should select view type or select from widget pattern'));
				return false;
			}


			return array('widgetId' => $widgetGId);
		}
		else
		{
			$this->addError(new Error('Board id might be not blank'));
			return false;
		}

	}

	/**
	 * @param array $formParams Parameters send from form.
	 * @return mixed
	 */
	private function createWidgetFromExisting($formParams)
	{
		$dashboardForUser = DashboardHelper::getDashboardByKeyForCurrentUser($formParams['boardId']);
		if (!$dashboardForUser)
		{
			$this->addError(new Error('Can\'t create widget because current user has not board to edit'));
			return false;
		}


		$widget = \Bitrix\Report\VisualConstructor\Entity\Widget::getWidgetByGId($formParams['patternWidgetId']);

		$copy = $widget->getCopyForCurrentUser();
		$cellId = 'cell_' . randString(4);
		try
		{
			$row = Row::getRowDefaultEntity(array(
				'cellIds' => array($cellId)
			));
		}
		catch (ArgumentException $e)
		{
			$this->errorCollection[] = new Error($e->getMessage());
			return false;
		}

		$row->setBoardId($formParams['boardId']);
		$copy->setWeight($cellId);
		$copy->setGId(Util::generateUserUniqueId());
		$copy->setIsPattern(false);
		$copy->setBoardId($formParams['boardId']);
		$row->addWidgets($copy);
		$row->setWeight(0);
		$dashboardForUser->addRows($row);
		$dashboardForUser->save();
		return $copy->getGId();
	}

}