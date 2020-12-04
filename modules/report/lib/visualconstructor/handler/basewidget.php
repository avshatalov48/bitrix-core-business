<?php

namespace Bitrix\Report\VisualConstructor\Handler;

use Bitrix\Report\VisualConstructor\Entity\Widget;
use Bitrix\Report\VisualConstructor\Fields;
use Bitrix\Report\VisualConstructor\Fields\Container;
use Bitrix\Report\VisualConstructor\Fields\Valuable\BaseValuable;
use Bitrix\Report\VisualConstructor\RuntimeProvider\ViewProvider;

/**
 * Class BaseWidget class for extending to create preset widget classes
 * @package Bitrix\Report\VisualConstructor\Handler
 */
class BaseWidget extends Base
{
	private $widget;
	private $reportHandlerList = array();

	/**
	 * @return string
	 */
	public static function getClassName()
	{
		return get_called_class();
	}

	/**
	 * BaseWidgetHandler constructor.
	 */
	public function __construct()
	{
		$widget = new Widget();
		$widget->setWidgetClass(static::getClassName());
		$this->setWidget($widget);
	}

	/**
	 * @return Fields\Base[]
	 */
	public function getCollectedFormElements()
	{
		parent::getCollectedFormElements();
		$this->getView()->collectWidgetHandlerFormElements($this);
		return $this->getFormElements();
	}

	/**
	 * Collecting form elements for configuration form.
	 *
	 * @return void
	 */
	protected function collectFormElements()
	{


	}

	/**
	 * @return Widget
	 */
	public function getWidget()
	{
		return $this->widget;
	}

	/**
	 * @param Widget $widget Widget entity.
	 * @return void
	 */
	public function setWidget($widget)
	{
		$this->widget = $widget;
	}

	/**
	 * @return Fields\Base[]
	 */
	public function getFormElements()
	{
		$result = array();
		foreach ($this->formElementsList as $key => $element)
		{
			$viewModesWhereFieldAvailable = $element->getCompatibleViewTypes();
			if ($viewModesWhereFieldAvailable != null)
			{
				$viewKey = $this->getWidget()->getViewKey();;
				$viewProvider = new ViewProvider();
				$viewProvider->addFilter('primary', $viewKey);
				$viewProvider->addFilter('dataType', $viewModesWhereFieldAvailable);
				$views = $viewProvider->execute()->getResults();
				if (!empty($views))
				{
					$result[$key] = $element;
				}
			}
			else
			{
				$result[$key] = $element;
			}
			if (($element instanceof BaseValuable) || ($element instanceof Container))
			{
				$element->setName($this->getNameForFormElement($element));
			}
		}
		return $result;
	}

	/**
	 * Construct and return form element name.
	 *
	 * @param BaseValuable $element Form element.
	 * @return string
	 */
	protected function getNameForFormElement(BaseValuable $element)
	{
		$name = '';
		if ($this->getWidget())
		{
			$name = 'widget[' . $this->getWidget()->getGId() . ']';
		}
		$name .= parent::getNameForFormElement($element);
		return $name;
	}

	/**
	 * @return BaseReport[]
	 */
	public function getReportHandlers()
	{
		return $this->reportHandlerList;
	}

	/**
	 * Attach report handler to widget handler.
	 *
	 * @param BaseReport $reportHandler Report handler.
	 * @return $this
	 */
	public function addReportHandler(BaseReport $reportHandler)
	{
		$reportHandler->setWidgetHandler($this);
		$this->getWidget()->addReportHandler($reportHandler);
		$this->reportHandlerList[] = $reportHandler;
		return $this;
	}

	/**
	 * Fill Widget handler entity with parameters from Widget entity.
	 *
	 * @param Widget $widget Widget handler.
	 * @return void
	 */
	public function fillWidget(Widget $widget)
	{
		$viewHandler = ViewProvider::getViewByViewKey($widget->getViewKey());
		if ($viewHandler)
		{
			$this->setView($viewHandler);
		}
		$this->setWidget($widget);
		$this->setConfigurations($widget->getConfigurations());
		$this->getCollectedFormElements();
		$this->fillFormElementValues();
		if ($widget->getReports())
		{
			foreach ($widget->getReports() as $report)
			{
				$this->reportHandlerList[] = $report->getReportHandler();
			}
		}
	}

	private function fillFormElementValues()
	{
		$formElements = $this->getFormElements();
		$configurations = $this->getConfigurations();
		if (!empty($configurations))
		{
			foreach ($configurations as $configuration)
			{
				if (isset($formElements[$configuration->getKey()]) && ($formElements[$configuration->getKey()] instanceof BaseValuable))
				{
					/** @var BaseValuable[] $formElements */
					$formElements[$configuration->getKey()]->setValue($configuration->getValue());
				}
			}
		}
	}
}