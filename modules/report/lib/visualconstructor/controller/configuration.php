<?php
namespace Bitrix\Report\VisualConstructor\Controller;

use Bitrix\Report\VisualConstructor\Internal\Engine\Response\Component;
use Bitrix\Report\VisualConstructor\BaseReportHandler;
use Bitrix\Report\VisualConstructor\Handler\EmptyReport;
use Bitrix\Report\VisualConstructor\Helper\Report;
use Bitrix\Report\VisualConstructor\Internal\Error\Error;
use Bitrix\Report\VisualConstructor\RuntimeProvider\ReportProvider;
use Bitrix\Report\VisualConstructor\RuntimeProvider\ViewProvider;


/**
 * Class Configuration
 * @package Bitrix\Report\VisualConstructor\Controller
 */
class Configuration extends Base
{

	/**
	 * Build pseudo report configuration block, and render it in component.
	 *
	 * @see report/install/components/bitrix/report.visualconstructor.widget.pseudoconfig/templates/.default/template.php.
	 * @param array $params Parameters from form.
	 * @return Component|bool
	 */
	public function buildPseudoReportConfigurationAction($params)
	{
		if (!isset($params['widgetId']))
		{
			$this->addError(new Error('widgetId not exist'));
			return false;
		}

		if (!isset($params['viewKey']))
		{
			$this->addError(new Error('view key not exist'));
			return false;
		}

		$existReportCount = !empty($params['existReportCount']) ? $params['existReportCount'] : 0;

		$componentName = 'bitrix:report.visualconstructor.widget.pseudoconfig';
		$templateName = '';
		$widgetParams = array(
			'widgetGId' => $params['widgetId'],
			'viewKey' => $params['viewKey'],
		);

		$reportHandlerClassName = !empty($params['reportHandlerClassName']) ? $params['reportHandlerClassName'] : '__';

		$widget = \Bitrix\Report\VisualConstructor\Entity\Widget::buildPseudoWidget($widgetParams);
		if ($widget)
		{
			$componentParams = array(
				'WIDGET_ID' => $widget->getGId()
			);

			if ($reportHandlerClassName == '__')
			{
				$reportHandlerClassName = EmptyReport::getClassName();
			}

			$reportHandler = Report::buildReportHandlerForWidget($reportHandlerClassName, $widget, true);
			$colorFieldValue = !empty($params['colorFieldValue']) ? $params['colorFieldValue'] : $reportHandler->getView()->getReportDefaultColor($existReportCount + 1);

			$reportHandler->getFormElement('color')->setValue($colorFieldValue);
			$reportHandler->getFormElement('head_container_start')->addInlineStyle('background-color', $colorFieldValue);
			$reportHandler->getFormElement('main_container_start')->addInlineStyle('background-color', $colorFieldValue . '5f');
			$componentParams['REPORT_HANDLER'] = $reportHandler;
			return new Component($componentName, $templateName, $componentParams);
		}
		$this->addError(new Error('No widget with this id'));
		return false;
	}

	/**
	 * Return array of available what will calculate options.
	 *
	 * @param array $params Parameters from form.
	 * @return array|bool
	 */
	public function loadWhatWillCalculateByGroupAction($params)
	{
		if (!isset($params['widgetId']))
		{
			$this->addError(new Error('widgetId not exist'));
			return false;
		}

		if (!isset($params['viewKey']))
		{
			$this->addError(new Error('view key not exist'));
			return false;
		}

		if (!isset($params['reportHandlerClassName']))
		{
			$this->addError(new Error('report handler class name not exist'));
			return false;
		}

		if (!isset($params['groupBy']))
		{
			$this->addError(new Error('groupBy field not exist'));
			return false;
		}

		$widgetParams = array(
			'widgetGId' => $params['widgetId'],
			'viewKey' => $params['viewKey'],
		);
		$groupBy = $params['groupBy'];
		$reportHandlerClassName = $params['reportHandlerClassName'];
		$widget = \Bitrix\Report\VisualConstructor\Entity\Widget::buildPseudoWidget($widgetParams);
		if ($widget)
		{
			$reportHandler = ReportProvider::getReportHandlerByClassName($reportHandlerClassName);

			$viewHandler = ViewProvider::getViewByViewKey($widget->getViewKey());
			$result = array();
			if ($reportHandler && $viewHandler)
			{
				$reportHandler->setView($viewHandler);
				$whatWillCalculate = $reportHandler->getWhatWillCalculateOptions($groupBy);
				foreach ($whatWillCalculate as $value => $text)
				{
					$result[$value] = $text;
				}
			}
			return $result;
		}
		$this->addError(new Error('No widget with this id'));
		return false;

	}

}