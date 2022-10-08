<?php

namespace Bitrix\Report\VisualConstructor\Controller;

use Bitrix\Main;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\Query;
use Bitrix\Report\VisualConstructor\Internal\Engine\Response\Component;
use Bitrix\Main\Localization\Loc;
use Bitrix\Report\VisualConstructor\Entity\ConfigurableModel;
use Bitrix\Report\VisualConstructor\Entity\DashboardRow;
use Bitrix\Report\VisualConstructor\Fields\Valuable\BaseValuable;
use Bitrix\Report\VisualConstructor\Handler\Base;
use Bitrix\Report\VisualConstructor\Handler\BaseReport;
use Bitrix\Report\VisualConstructor\Helper\Dashboard as DashboardHelper;
use Bitrix\Report\VisualConstructor\Helper\Dashboard;
use Bitrix\Report\VisualConstructor\Helper\Report;
use Bitrix\Report\VisualConstructor\Helper\Util;
use Bitrix\Report\VisualConstructor\Internal\Error\Error;
use Bitrix\Report\VisualConstructor\RuntimeProvider\ViewProvider;

/**
 * Class Widget
 * @package Bitrix\Report\VisualConstructor\Controller
 */
class Widget extends \Bitrix\Report\VisualConstructor\Controller\Base
{
	public function configureActions()
	{
		return [
			'load' => [
				'+prefilters' => [
					new Main\Engine\ActionFilter\CloseSession(),
				]
			],
			'loadByBoardId' => [
				'+prefilters' => [
					new Main\Engine\ActionFilter\CloseSession(),
				]
			],
		];
	}

	/**
	 * Action return configuration form by widget gid, and board gid.
	 *
	 * @param string $widgetId Widget gId.
	 * @param string $boardId Board key.
	 * @return Component|bool
	 */
	public function showConfigurationFormAction($widgetId, $boardId)
	{
		$componentName = 'bitrix:report.visualconstructor.widget.form';
		$widget = \Bitrix\Report\VisualConstructor\Entity\Widget::getCurrentUserWidgetByGId($widgetId);
		if ($widget)
		{
			$templateName = '';
			$params = array(
				'MODE' => 'update',
				'ORIGINAL_WIDGET_GID' => $widgetId,
				'WIDGET' => $widget,
				'BOARD_ID' => $boardId,
				'PAGE_TITLE' => Loc::getMessage('REPORT_WIDGET_SETTINGS_CONTENT_TITLE'),
				'SAVE_BUTTON_TITLE' => Loc::getMessage('REPORT_WIDGET_SETTINGS_SAVE_BUTTON_TITLE'),
			);
			return new Component($componentName, $templateName, $params);
		}
		else
		{
			$this->addError(new Error('No widget with this id'));
			return false;
		}
	}

	/**
	 * Configuration form save handler.
	 *
	 * @param array $formParams Parameters form form to save widget configurations.
	 * @return array|bool
	 */
	public function saveConfigurationFormAction($formParams)
	{
		if (!empty($formParams['boardId']))
		{
			$boardKey = $formParams['boardId'];
		}
		else
		{
			$this->addError(new Error('Argument boardId not exist'));
			return false;
		}


		$dashboardForUser = DashboardHelper::getDashboardByKeyForCurrentUser($boardKey);

		if (!$dashboardForUser)
		{
			$this->addError(new Error('Can\'t save configuration because current user has not dashboard to edit'));
			return false;
		}

		$isPattern = false;
		if (!empty($formParams['isPattern']))
		{
			$isPattern = $formParams['isPattern'] === 'on' ? true : false;
		}

		$categoryKey = !empty($formParams['categoryKey']) ? $formParams['categoryKey']: '';

		$widgetGId = $formParams['widgetId'];
		$originalWidgetGId = $formParams['originalWidgetGId'];
		$widgetConfigurations = $formParams['widget'][$widgetGId]['configurations'];
		$reportsConfigurationsFromForm = $formParams['widget'][$widgetGId]['reports'];
		$deletedReportIds = !empty($formParams['deletedReports']) ? $formParams['deletedReports'] : array();


		if ($widgetGId !== $originalWidgetGId)
		{
			$widget = \Bitrix\Report\VisualConstructor\Entity\Widget::getCurrentUserWidgetByGId($originalWidgetGId);
		}
		else
		{
			$widget = \Bitrix\Report\VisualConstructor\Entity\Widget::getCurrentUserWidgetByGId($widgetGId);
		}

		if (!$widget)
		{
			$this->addError(new Error('Can\'t save configuration because widget with this id not exist'));
			return false;
		}
		else
		{
			if ($widgetGId !== $originalWidgetGId)
			{
				$newWidget = $this->createWidgetByParams($formParams);
				$newWidget->setWeight($widget->getWeight());
				$newWidget->setGId($widget->getGId());
				$newWidget->setRowId((int)$widget->getRowId());
				$newWidget->setCategoryKey($widget->getCategoryKey());
				$newWidget->save();

				$widget->delete();
				return array('widgetId' => $newWidget->getGId());
			}
			else
			{
				$widget->loadAttribute('reports');
				$widgetHandler = $widget->getWidgetHandler();
				$this->setConfigurableEntityConfiguration($widget, $widgetHandler, $widgetConfigurations);
				$widget->setViewKey($widget->getWidgetHandler()->getConfiguration('view_type')->getValue());
				$widget->setBoardId($boardKey);
				$widget->setCategoryKey($categoryKey);
				$widget->save();
			}
		}


		$widgetReports = $widget->getReportsGidKeyed();


		//delete reports, which mark as deleted in form
		foreach ($deletedReportIds as $deletedReportId)
		{
			if (!empty($widgetReports[$deletedReportId]))
			{
				$widgetReports[$deletedReportId]->delete();
			}
			unset($reportsConfigurationsFromForm[$deletedReportId]);
		}

		//save report configurations
		foreach ($reportsConfigurationsFromForm as $reportId => $configurationFromForm)
		{
			$configuration = $configurationFromForm['configurations'];

			if ($this->isReportPseudo($reportId))
			{
				$this->addReportToWidget($widget, $configuration);
			}
			else
			{
				$report = $widgetReports[$reportId];
				$reportHandler = $report->getReportHandler();
				$this->setConfigurableEntityConfiguration($report, $reportHandler, $configuration);
			}
		}

		$widget->save();

		if ($isPattern)
		{
			\Bitrix\Report\VisualConstructor\Helper\Widget::saveWidgetAsCurrentUserPattern($widget);
		}

		return array('widgetId' => $widget->getGId());
	}

	/**
	 * Create widget from form params action and save it;
	 *
	 * @param array $formParams Parameters from form.
	 * @return array [widgetId => 'Gid of new created widget'].
	 */
	public function addWidgetFromConfigurationFormAction($formParams)
	{
		//@TODO optimize
		$widget = $this->createWidgetByParams($formParams);

		return array('widgetId' => $widget->getGId());
	}


	/**
	 * @param $params
	 * @return \Bitrix\Report\VisualConstructor\Entity\Widget|bool
	 */
	private function createWidgetByParams($params)
	{
		$widgetGid = $params['widgetId'];

		$viewController = ViewProvider::getViewByViewKey($params['widget'][$widgetGid]['configurations']['new']['view_type']);
		if (!$viewController)
		{
			$this->addError(new Error('No such view controller.'));
			return false;
		}

		global $USER;
		$userId = $USER->getId();
		if (!$userId)
		{
			$this->addError(new Error('Can\'t create widget because current user has not id'));
			return false;
		}

		$dashboardForUser = DashboardHelper::getDashboardByKeyForCurrentUser($params['boardId']);
		if (!$dashboardForUser)
		{
			$this->addError(new Error('Can\'t create widget because current user has not board to edit'));
			return false;
		}


		$isPattern = false;
		if (!empty($params['isPattern']))
		{
			$isPattern = $params['isPattern'] === 'on' ? true : false;
		}

		$categoryKey = !empty($params['categoryKey']) ? $params['categoryKey']: '';
		$widgetHandler = $viewController->buildWidgetHandlerForBoard($params['boardId']);


		$widgetPositions = array('cell_' . rand(999, 99999));

		try
		{
			$row = \Bitrix\Report\VisualConstructor\Helper\Row::getRowDefaultEntity(array(
				'cellIds' => $widgetPositions
			));
		}
		catch (ArgumentException $e)
		{
			$this->addError(new Error($e->getMessage()));
			return false;
		}

		$dashboardForUser->addRows($row);


		/** @var \Bitrix\Report\VisualConstructor\Entity\Widget $widget */
		$widget = $widgetHandler->getWidget();
		$widget->setCategoryKey($categoryKey);
		$widget->setWeight($widgetPositions[0]);
		$widget->setOwnerId($userId);
		$widget->setGId(Util::generateUserUniqueId());
		$row->addWidgets($widget);
		$dashboardForUser->save();


		$widgetGId = $params['widgetId'];
		$widgetConfigurations = $params['widget'][$widgetGId]['configurations'];
		$reportsConfigurationsFromForm = $params['widget'][$widgetGId]['reports'];

		$widgetHandler = $widget->getWidgetHandler();
		$this->setConfigurableEntityConfiguration($widget, $widgetHandler, $widgetConfigurations);
		//save report configurations
		foreach ($reportsConfigurationsFromForm as $reportId => $configurationFromForm)
		{
			$configuration = $configurationFromForm['configurations'];

			if (is_array($params['deletedReports']) && in_array($reportId, $params['deletedReports']))
			{
				continue;
			}

			if ($this->isReportPseudo($reportId))
			{
				$this->addReportToWidget($widget, $configuration);
			}
		}

		if ($isPattern)
		{
			\Bitrix\Report\VisualConstructor\Helper\Widget::saveWidgetAsCurrentUserPattern($widget, $widget->getCategoryKey());
		}

		return $widget;
	}

	/**
	 * Build create or configuration form.
	 *
	 * @param array $params Parameters to create form.
	 * @return Component|bool
	 */
	public function buildFormAction($params)
	{
		$componentName = 'bitrix:report.visualconstructor.widget.form';

		$boardId = $params['boardId'];
		$mode = $params['mode'];
		try
		{
			$widget = \Bitrix\Report\VisualConstructor\Helper\Widget::constructNewPseudoWidgetByParams($params);
		}
		catch (ArgumentException $e)
		{
			$this->addError(new Error($e->getMessage()));
			return false;
		}

		$widget->setGId('pseudo_widget_for_add');
		if ($widget)
		{
			$templateName = '';
			$params = array(
				'MODE' => $mode,
				'ORIGINAL_WIDGET_GID' => $params['widgetId'],
				'WIDGET' => $widget,
				'BOARD_ID' => $boardId,
				'PAGE_TITLE' => $mode === 'create' ? Loc::getMessage('REPORT_CREATE_WIDGET_SETTINGS_CONTENT_TITLE') : Loc::getMessage('REPORT_WIDGET_SETTINGS_CONTENT_TITLE'),
				'SAVE_BUTTON_TITLE' => $mode === 'create' ? Loc::getMessage('REPORT_CREATE_WIDGET_SETTINGS_SAVE_BUTTON_TITLE') : Loc::getMessage('REPORT_WIDGET_SETTINGS_SAVE_BUTTON_TITLE'),
			);
			return new Component($componentName, $templateName, $params);
		}
		else
		{
			$this->addError(new Error('No widget with this id'));
			return false;
		}
	}

	/**
	 * This action call when try to change to other view type of existing widget.
	 * If new view key do not compatible with core view type return false, else true.
	 *
	 * @param array $params Parameters like [newViewKey => 'linearGraph', oldViewKey => 'column'].
	 * @return array|bool
	 */
	public function checkIsCompatibleWithSelectedViewAction($params)
	{
		if (!isset($params['newViewKey']))
		{
			$this->addError(new Error('new view key not exist'));
			return false;
		}

		if (!isset($params['oldViewKey']))
		{
			$this->addError(new Error('old view key not exist'));
			return false;
		}
		$newView = ViewProvider::getViewByViewKey($params['newViewKey']);
		$oldView = ViewProvider::getViewByViewKey($params['oldViewKey']);

		if (!$newView)
		{
			$this->addError(new Error('view not found with key: ' . $params['newViewKey']));
			return false;
		}

		if (!$oldView)
		{
			$this->addError(new Error('view not found with key: ' . $params['oldViewKey']));
			return false;
		}


		$result = $oldView->isCompatibleWithView($newView);
		return array('isCompatible' => $result);
	}

	/**
	 * Construct widget for show preview.
	 *
	 * @param array $params Form params will be apply in preview widget.
	 * @return array|bool
	 */
	public function constructPseudoWidgetAction($params)
	{
		try
		{
			$widget = \Bitrix\Report\VisualConstructor\Helper\Widget::constructPseudoWidgetByParams($params);
		}
		catch (ArgumentException $e)
		{
			$this->addError(new Error($e->getMessage()));
			return false;
		}

		$pseudoWidgetPreparedData = \Bitrix\Report\VisualConstructor\Helper\Widget::prepareWidgetContent($widget, true);
		$widgetConfigurationFields = $widget->getWidgetHandler()->getFormElements();
		$reports = $widget->getReports();
		$reportsResult = array();
		if ($reports)
		{
			foreach ($reports as $report)
			{
				$configurationFields = $report->getReportHandler()->getFormElements();
				$reportsResult[] = array(
					'configurationFields' => $configurationFields
				);
			}
		}

		return array(
			'widget' => array(
				'pseudoWidget' => $pseudoWidgetPreparedData,
				'configurationFields' => $widgetConfigurationFields,
			),
			'reports' => $reportsResult
		);
	}

	/**
	 * @param ConfigurableModel $model
	 * @param Base $handler
	 * @param $formConfigurations
	 */
	private function setConfigurableEntityConfiguration(ConfigurableModel $model, Base $handler, $formConfigurations)
	{
		if (!empty($formConfigurations['old']))
		{
			$keys = array_keys($formConfigurations['old']);
			$configurations = $handler->getConfigurationsGidKeyed();
			foreach ($configurations as $id => $configuration)
			{
				if (in_array($id, $keys))
				{
					$field = $handler->getFormElement($configuration->getKey());
					if ($field instanceof BaseValuable)
					{
						$newValue = $formConfigurations['old'][$id][$configuration->getKey()];
						$field->setValue($newValue);
						$configuration->setValue($field->getValue());
					}
				}
			}
		}


		if (!empty($formConfigurations['new']))
		{
			foreach ($formConfigurations['new'] as $key => $newConfiguration)
			{
				$field = $handler->getFormElement($key);
				if ($field instanceof BaseValuable)
				{
					$field->setDefaultValue($newConfiguration);
					$model->addConfigurationField($field);
				}
			}
		}
	}


	/**
	 * Check is report id is pseudo.
	 *
	 * @param string $reportId Report id.
	 * @return bool
	 */
	private function isReportPseudo($reportId)
	{
		return (mb_strpos($reportId, '_pseudo') === 0);
	}


	/**
	 * @param $widget
	 * @param array $configuration
	 */
	private function addReportToWidget(\Bitrix\Report\VisualConstructor\Entity\Widget $widget, $configuration)
	{
		if (!empty($configuration['new']['reportHandler']))
		{
			$reportHandler = Report::buildReportHandlerForWidget($configuration['new']['reportHandler'], $widget);
			if ($reportHandler instanceof BaseReport)
			{
				foreach ($configuration['new'] as $key => $configurationValue)
				{
					$formElement = $reportHandler->getFormElementFromCollected($key);
					if ($formElement instanceof BaseValuable)
					{
						$formElement->setValue($configurationValue);
						$reportConfiguration = $reportHandler->getConfiguration($key);
						if ($reportConfiguration)
						{
							$reportConfiguration->setValue($formElement->getValue());
						}
					}
				}
				$reportHandler->getReport()->setConfigurations($reportHandler->getConfigurations());
				$widget->addReportHandler($reportHandler);
				$widget->save();
			}


		}
	}

	/**
	 * Load widget params for rendering.
	 *
	 * @param string $widgetId Widget gId.
	 * @return array|false
	 */
	public function loadAction($widgetId)
	{
		$widget = \Bitrix\Report\VisualConstructor\Entity\Widget::getCurrentUserWidgetByGId($widgetId);
		if (!$widget->getId())
		{
			$this->addError(new Error('Widget no exist'));
			return false;
		}
		$preparedWidget = \Bitrix\Report\VisualConstructor\Helper\Widget::prepareWidgetContent($widget, true);
		$preparedWidget['row'] = array(
			'id' => $widget->getRow()->getGId(),
			'layoutMap' => $widget->getRow()->getLayoutMap(),
		);
		return $preparedWidget;
	}

	/**
	 * Load widget by board Id.
	 *
	 * @param string $boardId Board id.
	 * @return array
	 */
	public function loadByBoardIdAction($boardId)
	{
		$preparedObjectForDashboard = \Bitrix\Report\VisualConstructor\Helper\Widget::prepareBoardWithEntitiesByBoardId($boardId);
		return $preparedObjectForDashboard;
	}

	/**
	 * Widget update action.
	 * Call whan save configuration form.
	 *
	 * @param string $boardKey Board key.
	 * @param string $widgetId Widget gId.
	 * @param array $params Form parameters to apply in update of widget.
	 * @return string Saved widget gId.
	 */
	public function updateAction($boardKey, $widgetId, $params)
	{
		$dashboardForUser = DashboardHelper::getDashboardByKeyForCurrentUser($boardKey);

		if (!$dashboardForUser)
		{
			$this->addError(new Error('Can\'t update widget because current user has not dashboard to edit'));
			return false;
		}

		$widget = \Bitrix\Report\VisualConstructor\Entity\Widget::getCurrentUserWidgetByGId($widgetId);
		if ($widget)
		{

			if (!empty($params['rowId']) && $params['rowId'] != $widget->getRow()->getGId())
			{
				$row = DashboardRow::getCurrentUserRowByGId($params['rowId']);

				if ($row)
				{
					$widget->setRow($row);
				}
				else
				{
					$this->addError(new Error("No row with id: " . $params['rowId']));
				}
			}

			$widget->getRow()->setLayoutMap($params['rowLayoutMap']);


			if (!empty($params['cellId']))
			{
				$widget->setWeight($params['cellId']);
			}

			$widget->save();
			return $widget->getGId();
		}
		else
		{
			$this->addError(new Error("No widget with id: " . $widgetId));
			return null;
		}

	}

	/**
	 * Remove widget from board action.
	 *
	 * @param array $params Parameters like [boardId => 'some_board_id', widgetId => 'some_widget_gId'].
	 * @return bool
	 */
	public function removeAction($params)
	{
		$boardKey = $params['boardId'];
		$widgetId = $params['widgetId'];
		$dashboardForUser = DashboardHelper::getDashboardByKeyForCurrentUser($boardKey);

		if ($dashboardForUser)
		{
			$deleteWidgetId = \Bitrix\Report\VisualConstructor\Entity\Widget::removeCurrentUserWidgetByGId($widgetId);
			return $deleteWidgetId;
		}
		else
		{
			$this->addError(new Error('Cant delete row because current user has not own dashboard'));
			return false;
		}

	}

	/**
	 * Remove pattern widget.
	 * Delete only current user pattern widget.
	 *
	 * @param string $widgetId Widget id.
	 * @return void
	 */
	public function removePatternAction($widgetId)
	{
		global $USER;
		$filter = Query::filter();
		$filter->where('GID', $widgetId);
		$filter->where('OWNER_ID', $USER->getId());
		$filter->where('IS_PATTERN', true);
		$widget = \Bitrix\Report\VisualConstructor\Entity\Widget::load($filter);

		if ($widget->getOwnerId() === $USER->getId())
		{
			$widget->deletePatternWidget();
		}
	}


}