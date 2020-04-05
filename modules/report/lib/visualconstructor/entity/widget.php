<?php

namespace Bitrix\Report\VisualConstructor\Entity;

use Bitrix\Main\Entity\Query;
use Bitrix\Report\VisualConstructor\Category;
use Bitrix\Report\VisualConstructor\Config\Common;
use Bitrix\Report\VisualConstructor\Handler\BaseReport;
use Bitrix\Report\VisualConstructor\Handler\BaseWidget;
use Bitrix\Report\VisualConstructor\Internal\WidgetConfigurationTable;
use Bitrix\Report\VisualConstructor\Internal\WidgetTable;
use Bitrix\Report\VisualConstructor\RuntimeProvider\WidgetProvider;

/**
 * Class Widget
 * @method addReports(Report | Report[] $report) add report/reports to this widget.
 * @method deleteReports(Report | Report[] $report) delete report connection adn if it is ONE-TO-MANY delete Report entity.
 * @method deleteRow(DashboardRow $row) delete report connection with row.
 * @package Bitrix\Report\VisualConstructor\Entity
 */
class Widget extends ConfigurableModel
{
	protected $gId;
	protected $weight;
	protected $boardId;
	protected $rowId;
	protected $widgetClass;
	/**
	 * @var BaseWidget $widgetHandler.
	 */
	protected $widgetHandler;
	protected $viewKey;
	protected $ownerId = 0;
	protected $categoryKey = '';
	protected $isPattern = false;
	protected $parentWidgetId = 0;

	/** @var Report[] $reports */
	protected $reports = array();

	/** @var DashboardRow $row */
	protected $row;

	/** @var Widget $parentWidget */
	protected $parentWidget;

	/** @var Widget[] $childWidgets */
	protected $childWidgets = array();

	/**
	 * Widget constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->weight = 0;
	}

	/**
	 * @return string
	 */
	public static function getTableClassName()
	{
		return WidgetTable::getClassName();
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
		$attributes['BOARD_ID'] = 'boardId';
		$attributes['DASHBOARD_ROW_ID'] = 'rowId';
		$attributes['PARENT_WIDGET_ID'] = 'parentWidgetId';
		$attributes['WIDGET_CLASS'] = 'widgetClass';
		$attributes['CATEGORY_KEY'] = 'categoryKey';
		$attributes['VIEW_KEY'] = 'viewKey';
		$attributes['WEIGHT'] = 'weight';
		$attributes['OWNER_ID'] = 'ownerId';
		$attributes['IS_PATTERN'] = 'isPattern';
		return $attributes;
	}

	/**
	 * @return array
	 */
	public static function getMapReferenceAttributes()
	{
		return array(
			'row' => array(
				'type' => Common::MANY_TO_ONE,
				'targetEntity' => DashboardRow::getClassName(),
				'inversedBy' => 'widgets',
				'join' => array(
					'field' => array('rowId', 'id')
				)
			),
			'configurations' => array(
				'type' => Common::MANY_TO_MANY,
				'targetEntity' => Configuration::getClassName(),
				'join' => array(
					'tableClassName' => WidgetConfigurationTable::getClassName(),
					'column' => array('WIDGET' => array('id', 'WIDGET_ID')),
					'inverseColumn' => array('CONFIGURATION_SETTING' => array('id', 'CONFIGURATION_ID'))
				),
			),
			'reports' => array(
				'type' => Common::ONE_TO_MANY,
				'targetEntity' => Report::getClassName(),
				'mappedBy' => 'widget'
			),
			'parentWidget' => array(
				'type' => Common::MANY_TO_ONE,
				'targetEntity' => Widget::getClassName(),
				'inversedBy' => 'childWidgets',
				'join' => array(
					'field' => array('parentWidgetId', 'id')
				),
				'options' => array(
					'deleteSkip' => true
				)
			),
			'childWidgets' => array(
				'type' => Common::ONE_TO_MANY,
				'targetEntity' => Widget::getClassName(),
				'mappedBy' => 'parentWidget',
				'options' => array(
					'deleteSkip' => true
				)
			),
		);
	}


	/**
	 * Delete widget if it is not pattern.
	 *
	 * @return bool|null
	 */
	public function delete()
	{
		if ($this->isPattern())
		{
			$this->rowId = 0;
			$this->save();
			return true;
		}
		else
		{
			return parent::delete();
		}
	}

	/**
	 * Delete pattern widget.
	 *
	 * @return bool|null
	 */
	public function deletePatternWidget()
	{
		return parent::delete();
	}

	/**
	 * Attach report handler to widget handler.
	 *
	 * @param BaseReport $reportHandler Report handler.
	 * @return void
	 */
	public function addReportHandler(BaseReport $reportHandler)
	{
		$report = $reportHandler->getReport();
		$this->addReports($report);
		if ($this->getId())
		{
			$report->setWidget($this);
			$reportHandler->fillReport($report);
		}

	}

	/**
	 * @param bool $isRuntime
	 *
	 * @return BaseWidget
	 */
	public function getWidgetHandler($isRuntime = false)
	{
		if (!$this->widgetHandler)
		{
			$widgetProvider = new WidgetProvider();
			$widgetProvider->addFilter('widgetClassName', $this->widgetClass);
			$widgetHandlerFromEvent = $widgetProvider->execute()->getFirstResult();
			if ($widgetHandlerFromEvent)
			{
				$this->widgetHandler = new $widgetHandlerFromEvent;
				if (!$isRuntime)
				{
					$this->loadAttribute('configurations');
				}
				$this->widgetHandler->fillWidget($this);
			}
		}

		return $this->widgetHandler;
	}



	/**
	 * Setter for widget handler.
	 * Set class name and set widget handler.
	 *
	 * @param BaseWidget $widgetHandler Widget handler.
	 * @return void
	 */
	public function setWidgetHandler(BaseWidget $widgetHandler)
	{
		$this->setWidgetClass($widgetHandler::getClassName());
		$this->widgetHandler = $widgetHandler;
	}

	/**
	 * @return Report[]
	 */
	public function getReports()
	{
		return $this->reports;
	}

	/**
	 * Return report list, key in list get from gId.
	 *
	 * @return Report[]
	 */
	public function getReportsGidKeyed()
	{
		$reports = $this->getReports();
		$result = array();
		foreach ($reports as $report)
		{
			$result[$report->getGId()] = $report;
		}
		return $result;
	}

	/**
	 * @param string $reportGId
	 * @return Report|null
	 */
	public function getReportByGId($reportGId)
	{
		foreach ($this->getReports() as $report)
		{
			echo $report->getGId() . PHP_EOL;
			if($report->getGId() === $reportGId)
			{
				return $report;
			}
		}
		return null;
	}

	/**
	 * @return string
	 */
	public function getBoardId()
	{
		return $this->boardId;
	}

	/**
	 * Setter for board id.
	 *
	 * @param string $boardId Board id.
	 * @return void
	 */
	public function setBoardId($boardId)
	{
		$this->boardId = $boardId;
	}

	/**
	 * @return string
	 */
	public function getViewKey()
	{
		return $this->viewKey;
	}

	/**
	 * Setter for view key.
	 *
	 * @param string $viewKey View key.
	 * @return void
	 */
	public function setViewKey($viewKey)
	{
		$this->viewKey = $viewKey;
	}

	/**
	 * construct and return filter name base on widget id.
	 *
	 * @return string
	 */
	public function getFilterId()
	{
		return 'report_board_' . $this->getBoardId() . '_filter';
	}

	/**
	 * Perform copy of widget with copies of nested relations.
	 *
	 * @return Widget
	 */
	public function getCopyForCurrentUser()
	{
		global $USER;
		//TODO create normal copy function for models
		$coreWidget = clone $this;
		$copyWidget = new Widget();
		$copyWidget->setBoardId($coreWidget->getBoardId());
		$copyWidget->setWidgetClass($coreWidget->getWidgetClass());
		$copyWidget->setViewKey($coreWidget->getViewKey());
		$copyWidget->setGId($coreWidget->getGId());
		$copyWidget->setWeight($coreWidget->getWeight());
		$copyWidget->setOwnerId($USER->getID());
		if ($coreWidget->getId())
		{
			$copyWidget->setParentWidgetId($coreWidget->getId());
		}
		else
		{
			$copyWidget->setParentWidgetId($coreWidget->getId());
		}

		$reports = $coreWidget->getReports();
		if (is_array($reports))
		{
			foreach ($reports as $report)
			{
				//HACK: must rewrite
				$report->loadAttribute('configurations');
				$reportCopy = $report->getCopy();
				$reportCopy->setWidget($copyWidget);
				$reportCopy->setWidgetId(null);
				$copyWidget->addReports($reportCopy);
			}
		}


		$configurations = $coreWidget->getConfigurations();
		if ($configurations)
		{
			foreach ($configurations as $configuration)
			{
				$configuration->setId(null);
				$copyWidget->addConfigurations($configuration);
			}
		}


		return $copyWidget;
	}

	/**
	 * Get Widget by board id.
	 * Load all nested relation.
	 *
	 * @param string $boardId Board id.
	 * @return static[]
	 */
	public static function getWidgetsByBoard($boardId)
	{
		$widgets = static::getModelList(array(
			'select' => array('*'),
			'filter' => array('=BOARD_ID' => $boardId),
			'with' => array('reports.configurations', 'reports.widget', 'configurations'),
			'order' => array('WEIGHT'),
		));
		return $widgets;
	}

	/**
	 * @return Widget[]
	 */
	public static function getCurrentUserPatternedWidgets()
	{
		global $USER;
		$userId = $USER->getId();
		$filter = Query::filter()
			->where('IS_PATTERN', '1')
			->where(Query::filter()
				->where(Query::filter()
					->where('OWNER_ID', $userId)
				)
				->logic('or')
				->where(Query::filter()
					->where('OWNER_ID', 0)
					->whereNot('CATEGORY_KEY', '')
				)
			);
		$widgets = static::getModelList(array(
			'select' => array('*'),
			'filter' => $filter,
			'order' => array('CREATED_DATE' => 'DESC'),
		));
		return $widgets ? $widgets : array();
	}

	/**
	 * Load widget and get configurations of loaded widget.
	 *
	 * @param string $widgetId Widget.
	 * @return Configuration[]
	 */
	public static function getWidgetConfigurations($widgetId)
	{
		$widget = static::getWidgetById($widgetId);
		$configurations = $widget->getConfigurations();
		return $configurations;
	}

	/**
	 * Load and return widget by widget id.
	 *
	 * @param string $widgetId Widget id.
	 * @return static
	 */
	public static function getWidgetById($widgetId)
	{
		static $widgets;
		if (!isset($widgets[$widgetId]))
		{
			$widgets[$widgetId] = static::load(array('ID' => $widgetId), array('row', 'reports.configurations', 'configurations'));
		}
		return $widgets[$widgetId];
	}

	/**
	 * Get current user widget by widget id with nested relations,
	 *
	 * @param string $widgetGId Widget gId.
	 * @return Widget
	 */
	public static function getCurrentUserWidgetByGId($widgetGId)
	{
		global $USER;
		if ($USER)
		{
			//TODO
			$filter = Query::filter();
			$filter->where('GID', $widgetGId);
			$filter->logic('and');
			$filter->where('ROW.DASHBOARD.USER_ID', $USER->getId());

			$widget = static::load(
				$filter,
				array('row', 'reports.configurations', 'configurations')
			);
			if (!$widget)
			{
				$filter = Query::filter();
				$filter->where('GID', $widgetGId);
				$filter->logic('and');
				$filter->where('ROW.DASHBOARD.USER_ID', 0);
				$widget = static::load(
					$filter,
					array('row', 'reports.configurations', 'configurations')
				);
			}

			return $widget;
		}
		return null;
	}

	/**
	 * If parameter start with pseudo_ then create new widget.
	 * Else try to load widget by gid, and change view key.
	 *
	 * @param array $params Parameters to build pseudo widget. [viewKey => 'number', widgetGId => 'pseudo_widget_gid'].
	 * @return Widget
	 */
	public static function buildPseudoWidget($params)
	{
		if (strpos($params['widgetGId'], 'pseudo_') === 0)
		{
			$widget = new self();
			$widget->setViewKey($params['viewKey']);
			$widget->setGId($params['widgetGId']);
			$widget->setWidgetClass(BaseWidget::getClassName());
			return $widget;
		}

		$widget = self::getCurrentUserWidgetByGId($params['widgetGId']);
		if ($widget)
		{
			$widget->setViewKey($params['viewKey']);
		}
		return $widget;
	}

	/**
	 * Load widget with nested relations by widget gId.
	 *
	 * @param string $widgetGId Widget gId.
	 * @return Widget
	 */
	public static function getWidgetByGId($widgetGId)
	{
		return static::load(
			array(
				'GID' => $widgetGId
			),
			array('row', 'reports.configurations', 'configurations')
		);
	}

	/**
	 * Load widget with nested relations by widget Id.
	 *
	 * @param string $widgetId Widget id.
	 * @return Widget
	 */
	public static function getWidgetByIdWithReports($widgetId)
	{
		$widget = static::load(
			array('ID' => $widgetId),
			array('row', 'reports', 'reports.configurations', 'configurations')
		);
		return $widget;
	}

	/**
	 * Remove current user widget by gId.
	 *
	 * @param string $widgetGId Widget gId.
	 * @return boolean
	 */
	public static function removeCurrentUserWidgetByGId($widgetGId)
	{
		global $USER;
		if ($USER)
		{
			$widget = static::load(
				array(
					'GID' => $widgetGId,
					'ROW.DASHBOARD.USER_ID' => $USER->getId()
				)
			);
			$widget->delete();
			return $widgetGId;
		}
		return null;

	}

	/**
	 * @return int
	 */
	public function getWeight()
	{
		return $this->weight;
	}

	/**
	 * Setter for widget position.
	 *
	 * @param string $weight Position of widget in row.
	 * @return void
	 */
	public function setWeight($weight)
	{
		$this->weight = $weight;
	}

	/**
	 * @return string
	 */
	public function getWidgetClass()
	{
		return $this->widgetClass;
	}

	/**
	 * Widget handler class name.
	 *
	 * @see BaseWidget::getClassName().
	 * @param string $widgetClass Widget handler class name.
	 * @return void
	 */
	public function setWidgetClass($widgetClass)
	{
		$this->widgetClass = $widgetClass;
	}

	/**
	 * @return DashboardRow
	 */
	public function getRow()
	{
		return $this->row;
	}

	/**
	 * Setter for row.
	 *
	 * @param DashboardRow $row Row Entity where place widget.
	 * @return void
	 */
	public function setRow(DashboardRow $row)
	{
		$this->row = $row;
	}

	/**
	 * @return bool
	 */
	public function isPattern()
	{
		return $this->isPattern;
	}

	/**
	 * Setter for pattern marker.
	 *
	 * @param bool $isPattern Marker to set is pattern or not.
	 * @return void
	 */
	public function setIsPattern($isPattern)
	{
		$this->isPattern = $isPattern;
	}

	/**
	 * @return Widget
	 */
	public function getParentWidget()
	{
		return $this->parentWidget;
	}

	/**
	 * parent Widget entity.
	 *
	 * @param Widget $parentWidget Widget entity.
	 * @return void
	 */
	public function setParentWidget($parentWidget)
	{
		$this->parentWidget = $parentWidget;
	}

	/**
	 * @return Widget[]
	 */
	public function getChildWidgets()
	{
		return $this->childWidgets;
	}

	/**
	 * @return mixed
	 */
	public function getParentWidgetId()
	{
		return $this->parentWidgetId;
	}

	/**
	 * Setter for parent widget id.
	 *
	 * @param mixed $parentWidgetId Parent widget id.
	 * @return void
	 */
	public function setParentWidgetId($parentWidgetId)
	{
		$this->parentWidgetId = $parentWidgetId;
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
	 * @param mixed $gId Value of gId.
	 * @return void
	 */
	public function setGId($gId)
	{
		$this->gId = $gId;
	}

	/**
	 * @return string
	 */
	public function getCategoryKey()
	{
		return $this->categoryKey;
	}

	/**
	 * Attach widget to category.
	 *
	 * @see Category.
	 * @param string $categoryKey Category key.
	 * @return void
	 */
	public function setCategoryKey($categoryKey)
	{
		$this->categoryKey = $categoryKey;
	}

	/**
	 * @return mixed
	 */
	public function getRowId()
	{
		return $this->rowId;
	}

	/**
	 * Setter for row id.
	 *
	 * @param mixed $rowId Value of row id.
	 * @return void
	 */
	public function setRowId($rowId)
	{
		$this->rowId = $rowId;
	}

	/**
	 * @return int
	 */
	public function getOwnerId()
	{
		return $this->ownerId;
	}

	/**
	 * Attach widget to some user.
	 *
	 * @param int $ownerId User id.
	 * @return void
	 */
	public function setOwnerId($ownerId)
	{
		$this->ownerId = $ownerId;
	}


}