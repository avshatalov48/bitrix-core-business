<?php

namespace Bitrix\Catalog\Integration\Report\Dashboard;

use Bitrix\Catalog\Integration\Report\Handler\BaseHandler;
use Bitrix\Catalog\Integration\Report\Dashboard\Group\Group;
use Bitrix\Catalog\Integration\Report\View\ViewRenderable;
use Bitrix\Report\VisualConstructor;
use Bitrix\Report\VisualConstructor\Entity\Report;
use Bitrix\Report\VisualConstructor\Entity\Widget;
use Bitrix\Report\VisualConstructor\Entity\Dashboard;
use Bitrix\Report\VisualConstructor\Entity\DashboardRow;
use Bitrix\Report\VisualConstructor\Handler\BaseWidget;
use Bitrix\Report\VisualConstructor\AnalyticBoard;
use Bitrix\Report\VisualConstructor\AnalyticBoardBatch;
use Bitrix\Report\VisualConstructor\Views\Component\Base;
use Bitrix\Report\VisualConstructor\View;

/**
 * Instances of this class must be provided from DashboardManager, that make access validation before this
 */
abstract class CatalogDashboard
{
	public const BUTCH_GROUP = 'catalog_general';
	public const BUTCH_GROUP_SORT = 160;

	protected array $dashboardViewList = [];

	protected BaseHandler $handler;
	protected Group $group;

	public function __construct()
	{
		foreach (static::getDefaultViewList() as $weight => $view)
		{
			$this->addView($view, $weight);
		}

		$this->bindGroup(static::getDefaultGroup());
	}

	public function bindGroup(Group $group): void
	{
		$this->group = $group;
	}

	public function getGroup(): Group
	{
		return $this->group;
	}

	abstract protected static function getDefaultGroup(): Group;

	/**
	 * Returns array of <b>Bitrix\Report\VisualConstructor\View</b> instances
	 */
	abstract protected static function getDefaultViewList(): array;

	/**
	 * Returns identified <b>board key</b> for dashboard instance
	 * @return string
	 */
	abstract public function getBoardKey(): string;

	/**
	 * Returns identified <b>access board id</b> for catalog access checking
	 * @return string
	 */
	abstract public function getAccessBoardId(): int;

	/**
	 * Returns <b>board version</b> of dashboard instance
	 * @return string
	 */
	abstract public function getBoardVersion(): string;

	abstract public function getBoardTitle(): ?string;

	public function getAnalyticBoard(): AnalyticBoard
	{
		$analyticBoard = new AnalyticBoard($this->getBoardKey());
		$analyticBoard->setBatchKey($this->group->getGroupKey());
		$analyticBoard->setGroup(static::BUTCH_GROUP);
		$analyticBoard->setTitle($this->getBoardTitle());

		return $analyticBoard;
	}

	public function getAnalyticBoardBatch(): AnalyticBoardBatch
	{
		$analyticBoardBatch = new AnalyticBoardBatch();
		$analyticBoardBatch->setKey($this->group->getGroupKey());
		$analyticBoardBatch->setGroup(static::BUTCH_GROUP);
		$analyticBoardBatch->setTitle($this->group->getGroupTitle());
		$analyticBoardBatch->setOrder(static::BUTCH_GROUP_SORT);

		return $analyticBoardBatch;
	}

	public function getDashboard(): Dashboard
	{
		$board = new Dashboard();
		$board->setVersion($this->getBoardVersion());
		$board->setBoardKey($this->getBoardKey());
		$board->setGId(VisualConstructor\Helper\Util::generateUserUniqueId());
		$board->setUserId(0);

		$board->addRows($this->getRows());

		return $board;
	}

	public function getRows(): array
	{
		ksort($this->dashboardViewList);

		$rows = [];
		foreach ($this->dashboardViewList as $weight => $view)
		{
			$row = DashboardRow::factoryWithHorizontalCells(1);
			$row->setWeight($weight);
			$rowWidget = $this->buildWidgetFromView($view);
			$rowWidget->setWeight($row->getLayoutMap()['elements'][0]['id']);
			$row->addWidgets($rowWidget);
			$rows[] = $row;
		}

		return $rows;
	}

	protected function buildWidgetFromView(ViewRenderable $view): Widget
	{
		$widget = new Widget();

		$widget->setGId(VisualConstructor\Helper\Util::generateUserUniqueId());
		$widget->setWidgetClass(BaseWidget::getClassName());
		$widget->setViewKey($view::VIEW_KEY);
		$widget->setCategoryKey('catalog');
		$widget->setBoardId($this->getBoardKey());
		$widget->getWidgetHandler(true)
			->updateFormElementValue('label', $view->getLabel());
		$widget->addConfigurations($widget->getWidgetHandler(true)->getConfigurations());

		$report = new Report();
		$report->setGId(VisualConstructor\Helper\Util::generateUserUniqueId());
		$report->setReportClassName(get_class($view->getViewHandler()));

		$report->setWidget($widget);
		$report->addConfigurations($report->getReportHandler(true)->getConfigurations());
		$widget->addReports($report);

		return $widget;
	}

	/**
	 * Add view to dashboard that will show in <b>$weight</b> order to page
	 * @param View $view
	 * @param int $weight
	 * @return void
	 */
	public function addView(View $view, int $weight): void
	{
		$this->dashboardViewList[$weight] = $view;
	}

	public function setHandler(BaseHandler $handler): void
	{
		$this->handler = $handler;
	}

	public function getHandler(): BaseHandler
	{
		return $this->handler;
	}

	public function getActiveViewList(): array
	{
		return $this->dashboardViewList;
	}
}
