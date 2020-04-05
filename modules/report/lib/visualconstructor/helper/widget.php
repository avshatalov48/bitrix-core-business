<?php
namespace Bitrix\Report\VisualConstructor\Helper;

use Bitrix\Main\ArgumentException;
use Bitrix\Report\VisualConstructor\BaseReportHandler;
use Bitrix\Report\VisualConstructor\Entity\Dashboard as DashboardEntity;
use Bitrix\Report\VisualConstructor\Entity\Widget as WidgetEntity;
use Bitrix\Report\VisualConstructor\Handler\BaseReport;
use Bitrix\Report\VisualConstructor\ReportDispatcher;
use Bitrix\Report\VisualConstructor\RuntimeProvider\ViewProvider;
use Bitrix\Report\VisualConstructor\View;


/**
 * Class Widget
 * @package Bitrix\Report\VisualConstructor\Helper
 */
class Widget
{
	const LAZY_LOAD_MODE = true;

	/**
	 * Build Widget contetn pas to preparing in view controller.
	 *
	 * @param WidgetEntity $widget Widget Entity.
	 * @param bool $withCalculatedData Marker define calculate or not data in reports.
	 * @return array
	 */
	public static function prepareWidgetContent(WidgetEntity $widget, $withCalculatedData = false)
	{
		$view = ViewProvider::getViewByViewKey($widget->getViewKey());

		$resultWidget = $view->prepareWidgetContent($widget, $withCalculatedData);


		return $resultWidget;
	}




	/**
	 * @param View $view View Controller.
	 * @param WidgetEntity $widget Widget Entity.
	 * @return null
	 */
	public static function getCalculatedPerformedData($view, $widget)
	{
		$result = null;
		$widget->loadAttribute('reports');
		$reports = $widget->getReports();
		$reportsCount = count($widget->getReports());
		if ($reportsCount <= $view::MAX_RENDER_REPORT_COUNT)
		{
			$handledReportData = array();
			foreach ($reports as $reportId => $report)
			{
				$reportDispatcher = new ReportDispatcher();
				$reportDispatcher->setReport($report);
				$reportDispatcher->setView($view);
				$data = $reportDispatcher->getReportCompatibleData();
				if (!$reportDispatcher->getErrors())
				{
					if ($view::MAX_RENDER_REPORT_COUNT == 1)
					{
						$handledReportData = $data;
					}
					elseif ($view::MAX_RENDER_REPORT_COUNT > 1)
					{
						$handledReportData[] = $data;
					}

				}
				else
				{
					foreach ($reportDispatcher->getErrors() as $error)
					{
						$result['errors'][] = $error->getMessage();
					}
				}

			}
			$result = $handledReportData;
		}
		else
		{
			$result['errors'][] = 'View with key:' . $view->getKey() . 'can\'t render this count(' . $reportsCount . ') of reports';
		}

		return $result;
	}


	/**
	 * Load all dashboard by board key.
	 * Prepare it for render.
	 *
	 * @param string $boardKey Board key.
	 * @return array
	 */
	public static function prepareBoardWithEntitiesByBoardId($boardKey)
	{

		$dashboard = self::getDashboard($boardKey);

		if ($dashboard)
		{
			$rows = $dashboard->getRows();

			$resultRows = array();
			$i = 0;
			if ($rows)
			{
				foreach ($rows as $row)
				{
					$resultRow = array(
						'id' => $row->getGId(),
						'layoutMap' => $row->getLayoutMap(),
						'weight' => $row->getWeight(),
					);
					/** @var WidgetEntity $widget */
					foreach ($row->getWidgets() as $widget)
					{
						$resultRow['widgets'][] = self::prepareWidgetContent($widget, !self::LAZY_LOAD_MODE);
					}
					$i++;
					$resultRows[] = $resultRow;
				}
			}


			return array(
				'boardId' => $dashboard->getBoardKey(),
				'boardKey' => $dashboard->getBoardKey(),
				'userId' => $dashboard->getUserId(),
				'rows' => $resultRows
			);
		}
		else
		{
			return array();
		}



	}

	/**
	 * Load dashboard for user.
	 * Try load dashbaord for user. if not exist return default dashboard.
	 * @param string $boardKey Board key.
	 * @return DashboardEntity|null
	 */
	private static function getDashboard($boardKey)
	{
		/** @var \CUser $USER */
		global $USER;
		$dashboard = null;
		$dashboardForUser = DashboardEntity::loadByBoardKeyAndUserId($boardKey, $USER->getid());
		if ($dashboardForUser)
		{
			$dashboard = DashboardEntity::getBoardWithRowsAndWidgetsByBoardKeyUserId($boardKey, $USER->getId());
		}
		else
		{
			$dashboard = DashboardEntity::getBoardWithRowsAndWidgetsByBoardKeyUserId($boardKey, 0);
		}


		return $dashboard;
	}


	/**
	 * Construct widget by params.
	 *
	 * @param array $params Parameters to construct widget.
	 * @return WidgetEntity
	 * @throws ArgumentException
	 */
	public static function constructPseudoWidgetByParams($params)
	{
		if (!isset($params['viewType']))
		{
			throw new ArgumentException('viewType argument not exist');
		}

		if (!isset($params['widgetId']))
		{
			throw new ArgumentException('widgetId argument not exist');
		}

		if (!isset($params['boardId']))
		{
			throw new ArgumentException('boardId argument not exist');
		}

		if (!isset($params['categoryKey']) || $params['categoryKey'] === 'myWidgets')
		{
			$categoryKey = '';
		}
		else
		{
			$categoryKey = $params['categoryKey'];
		}

		$viewKey = $params['viewType'];


		$widgetGId = $params['widgetId'];
		$boardId = $params['boardId'];
		$widgetConfigurations = !empty($params['widget'][$widgetGId]['configurations']) ? $params['widget'][$widgetGId]['configurations'] : array();
		$reportsConfigurationsFromForm = !empty($params['widget'][$widgetGId]['reports']) ? $params['widget'][$widgetGId]['reports'] : array();

		$viewController = ViewProvider::getViewByViewKey($viewKey);
		$widgetHandler = $viewController->buildWidgetHandlerForBoard($boardId);

		$widget = $widgetHandler->getWidget();
		$widget->setCategoryKey($categoryKey);

		if (!empty($widgetConfigurations['old']))
		{
			foreach ($widgetConfigurations['old'] as $configurationGid => $configuration)
			{
				foreach ($configuration as $key => $value)
				{
					$widgetHandler->getFormElement($key)->setValue($value);
				}
			}
		}

		if (!empty($widgetConfigurations['new']))
		{
			foreach ($widgetConfigurations['new'] as $key => $value)
			{
				$widgetHandler->getFormElement($key)->setValue($value);
			}
		}

		foreach ($reportsConfigurationsFromForm as $reportId=> $report)
		{
			if (!empty($params['deletedReports']) && in_array($reportId, $params['deletedReports']))
			{
				continue;
			}

			if (!empty($report['configurations']['new']['reportHandler']))
			{
				/** @var BaseReport $reportHandler */
				$reportHandler = $viewController->getReportHandler($report['configurations']['new']['reportHandler'], $widgetHandler);
			}
			elseif (!empty($report['configurations']['old']))
			{
				foreach ($report['configurations']['old'] as $configuration)
				{
					foreach ($configuration as $key => $value)
					{
						if ($key === 'reportHandler')
						{
							/** @var BaseReport $reportHandler */
							$reportHandler = $viewController->getReportHandler($value, $widgetHandler);
							break 2;
						}
					}
				}
			}
			else
			{
				continue;
			}


			if (isset($reportHandler) && $reportHandler instanceof BaseReport)
			{
				if (!empty($report['configurations']['old']))
				{
					foreach ($report['configurations']['old'] as $configuration)
					{
						foreach ($configuration as $key => $value)
						{
							$reportHandler->getFormElement($key)->setValue($value);
						}
					}
				}

				if (!empty($report['configurations']['new']))
				{
					foreach ($report['configurations']['new'] as $key => $value)
					{
						$reportHandler->getFormElement($key)->setValue($value);
					}
				}

				$widgetHandler->addReportHandler($reportHandler);
				$reportHandler->getReport()->setConfigurations($reportHandler->getConfigurations());
				$reportHandler->getReport()->setWidget($widget);
			}

		}



		$widget->setConfigurations($widgetHandler->getConfigurations());
		$widget->setGId('pseudo_' . randString(4));


		return $widget;
	}


	/**
	 * Construct Pseudo widget by form params, to render preview in previewBlock.
	 *
	 * @param array $params Parameters to construct new widget.
	 * @return WidgetEntity
	 * @throws ArgumentException
	 */
	public static function constructNewPseudoWidgetByParams($params)
	{
		if (!isset($params['viewType']))
		{
			throw new ArgumentException('viewType argument not exist');
		}

		if (!isset($params['widgetId']))
		{
			throw new ArgumentException('widgetId argument not exist');
		}

		if (!isset($params['boardId']))
		{
			throw new ArgumentException('boardId argument not exist');
		}

		if (!isset($params['categoryKey']) || $params['categoryKey'] === 'myWidgets')
		{
			$categoryKey = '';
		}
		else
		{
			$categoryKey = $params['categoryKey'];
		}

		$viewKey = $params['viewType'];


		$boardId = $params['boardId'];


		$viewController = ViewProvider::getViewByViewKey($viewKey);
		$widgetHandler = $viewController->buildWidgetHandlerForBoard($boardId);
		$widgetHandler = $viewController->addDefaultReportHandlersToWidgetHandler($widgetHandler);
		$widget = $widgetHandler->getWidget();
		$widget->setCategoryKey($categoryKey);


		$widget->setConfigurations($widgetHandler->getConfigurations());
		$widget->setGId('pseudo_' . randString(4));


		return $widget;
	}


	/**
	 * Get copy from core widget. set net gId. and save.
	 *
	 * @param WidgetEntity $widget Entity of core widget.
	 * @param string $categoryKey kay of Category to attach pattern widget.
	 *
	 * @return WidgetEntity
	 */
	public static function saveWidgetAsCurrentUserPattern(WidgetEntity $widget, $categoryKey = '')
	{
		$patternWidget = $widget->getCopyForCurrentUser();
		$patternWidget->setIsPattern(true);
		$patternWidget->setGId(Util::generateUserUniqueId());
		$patternWidget->setRowId(0);
		$patternWidget->setCategoryKey($categoryKey);
		$patternWidget->save();
		return $patternWidget;
	}

}