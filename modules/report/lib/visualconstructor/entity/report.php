<?php

namespace Bitrix\Report\VisualConstructor\Entity;

use Bitrix\Report\VisualConstructor\Config\Common;
use Bitrix\Report\VisualConstructor\Handler\BaseReport;
use Bitrix\Report\VisualConstructor\Internal\Error\Error;
use Bitrix\Report\VisualConstructor\Internal\ReportConfigurationTable;
use Bitrix\Report\VisualConstructor\Internal\ReportTable;
use Bitrix\Report\VisualConstructor\RuntimeProvider\ReportProvider;

/**
 * Class Report
 * @package Bitrix\Report\VisualConstructor\Entity
 */
class Report extends ConfigurableModel
{
	protected $gId;
	protected $widgetId;
	protected $reportClassName;
	/** @var BaseReport $reportHandler */
	protected $reportHandler;
	protected $weight = 0;

	/**
	 * @var Widget $widget
	 */
	protected $widget;

	/**
	 * @return string
	 */
	public static function getTableClassName()
	{
		return ReportTable::getClassName();
	}

	/**
	 * Returns the list of pair for mapping data and object properties.
	 * Key is field in DataManager, value is object property.
	 *
	 * @return array
	 */
	public static function getMapAttributes()
	{
		$attributes = parent::getMapAttributes();
		$attributes['GID'] = 'gId';
		$attributes['REPORT_CLASS'] = 'reportClassName';
		$attributes['WIDGET_ID'] = 'widgetId';
		$attributes['WEIGHT'] = 'weight';

		return $attributes;
	}

	/**
	 * @return array
	 */
	public static function getMapReferenceAttributes()
	{
		return array(
			'configurations' => array(
				'type' => Common::MANY_TO_MANY,
				'targetEntity' => Configuration::getClassName(),
				'join' => array(
					'tableClassName' => ReportConfigurationTable::getClassName(),
					'column' => array('REPORT' => array('id', 'REPORT_ID')),
					'inverseColumn' => array('CONFIGURATION_SETTING' => array('id', 'CONFIGURATION_ID'))
				),
			),
			'widget' => array(
				'type' => Common::MANY_TO_ONE,
				'targetEntity' => Widget::getClassName(),
				'inversedBy' => 'reports',
				'join' => array(
					'field' => array('widgetId', 'id')
				)
			),
		);
	}

	/**
	 * Get copy of report entity with nested relations,
	 *
	 * @return Report
	 */
	public function getCopy()
	{
		$coreReport = clone $this;
		$copyReport = new Report();
		$copyReport->setId(null);
		$copyReport->setGId($coreReport->getGId());
		$copyReport->setWeight($coreReport->getWeight());
		$copyReport->setWidgetId($coreReport->getWidgetId());
		$copyReport->setReportClassName($coreReport->getReportClassName());

		$configurations = $coreReport->getConfigurations();
		if (is_array($configurations))
		{
			foreach ($configurations as $configuration)
			{
				$configuration->setId(null);
				$copyReport->addConfigurations($configuration);
			}
		}


		return $copyReport;

	}

	/**
	 * @return mixed
	 */
	public function getWidgetId()
	{
		return $this->widgetId;
	}

	/**
	 * Connection report to widget.
	 *
	 * @param mixed $widgetId Widget id.
	 * @return void
	 */
	public function setWidgetId($widgetId)
	{
		$this->widgetId = $widgetId;
	}

	/**
	 * @return string
	 */
	public function getReportClassName()
	{
		return $this->reportClassName;
	}

	/**
	 * Setter for report class name.
	 *
	 * @see BaseReport::getClassName()
	 * @param string $reportClassName Report class name.
	 * @return void
	 */
	public function setReportClassName($reportClassName)
	{
		$this->reportClassName = $reportClassName;
	}

	/**
	 * Find report handler by report class name,in report provider.
	 * Return it if exist or return null.
	 *
	 * @return BaseReport|null
	 */
	public function getReportHandler()
	{
		if (!$this->reportHandler)
		{
			$reportHandlerFromEvent = ReportProvider::getReportHandlerByClassName($this->reportClassName);
			if ($reportHandlerFromEvent)
			{
				$this->reportHandler = new $reportHandlerFromEvent;
				$this->reportHandler->setView($this->getWidget()->getWidgetHandler()->getView());
				$this->loadAttribute('configurations');
				$this->reportHandler->fillReport($this);
			}
			else
			{
				$this->errors[] = new Error('No such report handler with this class');
				return null;
			}

		}


		return $this->reportHandler;
	}

	/**
	 * Setter for report handler.
	 *
	 * @param BaseReport $handler Report handler.
	 * @return void
	 */
	public function setReportHandler(BaseReport $handler)
	{
		$this->setReportClassName($handler::getClassName());
		$this->reportHandler = $handler;
	}

	/**
	 * @return mixed
	 */
	public function getWeight()
	{
		return $this->weight;
	}

	/**
	 * Setter of Weight in report list of widget. (for sorting).
	 *
	 * @param mixed $weight Weight of report.
	 * @return void
	 */
	public function setWeight($weight)
	{
		$this->weight = $weight;
	}

	/**
	 * Get Widget, if not exist try to load it from db.
	 *
	 * @return Widget
	 */
	public function getWidget()
	{
		/**
		 * @HACK
		 */
		if (!$this->widget && $this->widgetId)
		{
			$this->widget = Widget::loadById($this->widgetId);
		}
		return $this->widget;
	}

	/**
	 * Attach report to widget.
	 * @param Widget $widget Widget entity.
	 * @return void
	 */
	public function setWidget($widget)
	{
		$this->widget = $widget;
	}

	/**
	 * @return mixed
	 */
	public function getGId()
	{
		return $this->gId;
	}

	/**
	 * Setter for gId.
	 *
	 * @param mixed $gId Value og gId.
	 * @return void
	 */
	public function setGId($gId)
	{
		$this->gId = $gId;
	}

}