<?php

namespace Bitrix\Report\VisualConstructor\Views\Component;

use Bitrix\Report\VisualConstructor\Config\Common;
use Bitrix\Report\VisualConstructor\Entity\Widget;
use Bitrix\Report\VisualConstructor\Helper\Widget as WidgetHelper;
use Bitrix\Report\VisualConstructor\View;

/**
 * Base class for component content type for widgets in report dashboard.
 *
 * @package Bitrix\Report\VisualConstructor\Views\Component
 */
abstract class Base extends View
{
	private $componentName;
	private $componentTemplateName = '';
	private $componentParameters;


	/**
	 * Base component type view constructor.
	 */
	public function __construct()
	{
		$this->setJsClassName('BX.Report.Dashboard.Content.Html');
	}

	/**
	 * @return mixed
	 */
	public function getComponentName()
	{
		return $this->componentName;
	}

	/**
	 * Setter for component name.
	 *
	 * @param string $componentName Component name.
	 * @return void
	 */
	public function setComponentName($componentName)
	{
		$this->componentName = $componentName;
	}


	/**
	 * @return string
	 */
	public function getComponentParameters()
	{
		return $this->componentParameters;
	}

	/**
	 * Component parameters setter.
	 *
	 * @param array $componentParameters Parameters which pass to component.
	 * @return void
	 */
	public function setComponentParameters($componentParameters)
	{
		$this->componentParameters = $componentParameters;
	}

	/**
	 * @param $key
	 * @param $value
	 */
	public function addComponentParameters($key, $value)
	{
		$this->componentParameters[$key] = $value;
	}


	/**
	 * Handle all data prepared for this view.
	 *
	 * @param array $calculatedPerformedData Performed data from report handler.
	 * @return array
	 */
	public function handlerFinallyBeforePassToView($calculatedPerformedData)
	{
		$result['data'] = $calculatedPerformedData;
		return $result;
	}


	/**
	 * Method to modify Content which pass to widget view, in absolute end.
	 *
	 * @param Widget $widget Widget entity.
	 * @param bool $withCalculatedData Marker for calculate or no data in widget.
	 * @return array
	 */
	public function prepareWidgetContent(Widget $widget, $withCalculatedData = false)
	{

		$resultWidget = parent::prepareWidgetContent($widget, $withCalculatedData);

		if (!$withCalculatedData)
		{
			return $resultWidget;
		}

		if ($withCalculatedData)
		{
			$resultWidget['content']['params']['color'] = $widget->getWidgetHandler()->getFormElement('color')->getValue();
		}

		$result = $this->getCalculatedPerformedData($widget, $withCalculatedData);

		if (!empty($result['data']) && static::MAX_RENDER_REPORT_COUNT > 1)
		{
			foreach ($result['data'] as $num => &$reportResult)
			{
				if (!isset($reportResult['config']['color']))
				{
					$reportResult['config']['color'] = $widget->getWidgetHandler()->getReportHandlers()[$num]->getFormElement('color')->getValue();
				}

				if (!isset($reportResult['config']['title']))
				{
					$reportResult['title'] = $widget->getWidgetHandler()->getReportHandlers()[$num]->getFormElement('label')->getValue();
				}
				else
				{
					$reportResult['title'] = $reportResult['config']['title'];
				}
			}
		}
		elseif (!empty($result['data']))
		{
			$reportResult['config']['color'] = $widget->getWidgetHandler()->getReportHandlers()[0]->getFormElement('color')->getValue();
			$reportResult['title'] = $widget->getWidgetHandler()->getReportHandlers()[0]->getFormElement('label')->getValue();
		}


		$this->addComponentParameters('WIDGET', $widget);
		$this->addComponentParameters('RESULT', $result);

		$componentResult = $this->includeComponent();

		$resultWidget['content']['params']['html'] = $componentResult['html'];
		$resultWidget['content']['params']['css'] = $componentResult['css'];
		$resultWidget['content']['params']['js'] = $componentResult['js'];
		return $resultWidget;
	}

	/**
	 * Get calculated and format data.
	 *
	 * @param Widget $widget Widget Entity.
	 * @param bool $withCalculatedData Marker for calculate or no data in widget.
	 * @return array|null
	 */
	protected function getCalculatedPerformedData(Widget $widget, $withCalculatedData)
	{
		static $data;
		if (!$data)
		{
			$data = $withCalculatedData ? WidgetHelper::getCalculatedPerformedData($this, $widget) : array();
			$data = $this->handlerFinallyBeforePassToView($data);
		}
		return $data;
	}

	/**
	 * @param $componentName
	 * @param array $params
	 * @return mixed
	 */
	private function includeComponent()
	{
		global $APPLICATION;
		ob_start();
		$APPLICATION->IncludeComponent(
			$this->getComponentName(),
			$this->getComponentTemplateName(),
			$this->getComponentParameters()
		);
		$componentContent = ob_get_clean();
		$result['html'] = $componentContent;
		$result['js'] = $APPLICATION->arHeadScripts;
		$result['css'] = $APPLICATION->sPath2css;
		return $result;

	}

	/**
	 * @return string
	 */
	public function getComponentTemplateName()
	{
		return $this->componentTemplateName;
	}

	/**
	 * @param string $componentTemplateName
	 */
	public function setComponentTemplateName($componentTemplateName)
	{
		$this->componentTemplateName = $componentTemplateName;
	}

}