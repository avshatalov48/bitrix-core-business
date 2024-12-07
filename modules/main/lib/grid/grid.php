<?php

namespace Bitrix\Main\Grid;

use Bitrix\Main\Context;
use Bitrix\Main\Filter\Filter;
use Bitrix\Main\Grid\Action\Action;
use Bitrix\Main\Grid\Action\PaginationAction;
use Bitrix\Main\Grid\Column\Column;
use Bitrix\Main\Grid\Column\Columns;
use Bitrix\Main\Grid\Pagination\PageNavigationStorage;
use Bitrix\Main\Grid\Pagination\Storage\StorageSupporter;
use Bitrix\Main\Grid\Panel\Panel;
use Bitrix\Main\Grid\Row\Assembler\EmptyRowAssembler;
use Bitrix\Main\Grid\Row\Rows;
use Bitrix\Main\Grid\UI\GridResponse;
use Bitrix\Main\Grid\UI\Request\GridRequestFactory;
use Bitrix\Main\Grid\UI\Response\GridResponseFactory;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\UI\PageNavigation;

/**
 * Grid object.
 *
 * Contains all the necessary information to work with the grid: columns, settings, actions.
 * There is no work with the filter here, there is a separate `Filter` class for this.
 *
 * @see \Bitrix\Main\Filter\Filter
 *
 * Usage example:
 * ```php
//
// create instances for grid and filter
//
$settings = new \Bitrix\Main\Grid\Settings([
    'ID' => 'test-grid',
]);
$grid = new Grid($settings);

//
// processing grid actions from current request
//
$grid->processRequest();

//
// set all records count for pagination (if using)
//
$grid->getPagination()->setRecordCount(
	Entity::getCount(
		$grid->getOrmFilter() ?? []
	)
);

//
// fill grid rows with raw data
// It is important to do this AFTER processing the request, because the handlers could change the data.
//
$grid->setRawRows(
	Entity::getList($grid->getOrmParams())
);

//
// render component
//
$APPLICATION->IncludeComponent(
	'bitrix:main.ui.grid',
	'',
	\Bitrix\Main\Grid\Component\ComponentParams::get($grid, [
        // additional params
    ])
);
 * ```
 */
abstract class Grid
{
	use StorageSupporter;
	use DeprecatedMethods;

	private array $rawRows;
	private Options $options;
	private Settings $settings;
	private Columns $columns;
	private Rows $rows;

	// optional
	private ?Panel $panel = null;
	private ?Filter $filter = null;
	private ?PageNavigation $pagination = null;

	// internal
	private array $actionsMap;
	protected GridRequestFactory $gridRequestFactory;
	protected GridResponseFactory $gridResponseFactory;

	/**
	 * @param Settings $settings required for all childs!
	 */
	public function __construct(Settings $settings)
	{
		$this->settings = $settings;
		$this->gridRequestFactory = new GridRequestFactory;
		$this->gridResponseFactory = new GridResponseFactory;
	}

	#region public api

	/**
	 * Grid id.
	 *
	 * @return string
	 */
	final public function getId(): string
	{
		return $this->getSettings()->getId();
	}

	/**
	 * Configuration settings.
	 *
	 * @return Settings
	 */
	final public function getSettings(): Settings
	{
		return $this->settings;
	}

	/**
	 * User options.
	 *
	 * @return Options
	 */
	final public function getOptions(): Options
	{
		$this->options ??= new Options($this->getId());

		return $this->options;
	}

	/**
	 * Grid columns.
	 *
	 * @return Columns
	 */
	final public function getColumns(): Columns
	{
		$this->columns ??= $this->createColumns();

		return $this->columns;
	}

	/**
	 * Grid rows.
	 *
	 * @return Rows
	 */
	final public function getRows(): Rows
	{
		$this->rows ??= $this->createRows();

		return $this->rows;
	}

	/**
	 * Grid footer actions panel.
	 *
	 * @return Panel|null
	 */
	final public function getPanel(): ?Panel
	{
		$this->panel ??= $this->createPanel();

		return $this->panel;
	}

	/**
	 * Grid filter.
	 *
	 * @return Filter|null
	 */
	final public function getFilter(): ?Filter
	{
		$this->filter ??= $this->createFilter();

		return $this->filter;
	}

	/**
	 * Grid pagination.
	 *
	 * @return PageNavigation|null
	 */
	final public function getPagination(): ?PageNavigation
	{
		$this->pagination ??= $this->createPagination();

		return $this->pagination;
	}

	/**
	 * Set raw rows (only data).
	 *
	 * @param iterable $rawValue
	 *
	 * @return void
	 */
	public function setRawRows(iterable $rawValue): void
	{
		$this->rawRows = [];

		foreach ($rawValue as $item)
		{
			$this->rawRows[] = $item;
		}
	}

	/**
	 * Get raw rows.
	 *
	 * @return array
	 */
	final protected function getRawRows(): array
	{
		return $this->rawRows;
	}

	/**
	 * Gets rows prepared for output.
	 *
	 * @return array[]
	 */
	public function prepareRows(): array
	{
		return $this->getRows()->prepareRows($this->getRawRows());
	}

	/**
	 * Gets columns prepared for output.
	 *
	 * @return Column[]
	 */
	public function prepareColumns(): array
	{
		$result = [];

		foreach ($this->getColumns() as $column)
		{
			$result[] = $column;
		}

		return $result;
	}

	/**
	 * Processing all actions of the grid.
	 *
	 * Handles actions of the grid, panel, and rows.
	 *
	 * @param HttpRequest|null $request
	 *
	 * @return void
	 */
	public function processRequest(?HttpRequest $request = null): void
	{
		$request ??= Context::getCurrent()->getRequest();
		$gridRequest = $this->gridRequestFactory->createFromRequest($request);

		$response = $this->processGridActionsRequest($gridRequest);
		if ($response instanceof GridResponse)
		{
			if ($response->isSendable())
			{
				$response->send();
			}

			return;
		}

		$panel = $this->getPanel();
		if (isset($panel))
		{
			$response = $panel->processRequest($gridRequest, $this->getFilter());
			if ($response instanceof GridResponse)
			{
				if ($response->isSendable())
				{
					$response->send();
				}

				return;
			}
		}

		$response = $this->getRows()->processRequest($gridRequest);
		if ($response instanceof GridResponse)
		{
			if ($response->isSendable())
			{
				$response->send();
			}

			return;
		}
	}

	#region orm

	/**
	 * Params for ORM and `getList` method.
	 *
	 * @see \Bitrix\Main\ORM\Data\DataManager method `getList`
	 *
	 * @return array
	 */
	public function getOrmParams(): array
	{
		$params = [
			'select' => $this->getOrmSelect(),
			'order' => $this->getOrmOrder(),
		];

		$filter = $this->getOrmFilter();
		if (isset($filter))
		{
			$params['filter'] = $filter;
		}

		$pagination = $this->getPagination();
		if (isset($pagination))
		{
			$params['limit'] = $pagination->getLimit();
			$params['offset'] = $pagination->getOffset();
		}

		return $params;
	}

	/**
	 * Select for ORM and `getList` method.
	 *
	 * @see \Bitrix\Main\ORM\Data\DataManager method `getList`
	 *
	 * @return array
	 */
	public function getOrmSelect(): array
	{
		return $this->getColumns()->getSelect(
			$this->getVisibleColumnsIds()
		);
	}

	/**
	 * Filter for ORM and `getList` method.
	 *
	 * @see \Bitrix\Main\ORM\Data\DataManager method `getList`
	 *
	 * @return array|null is filter not setted, returns `null`
	 */
	public function getOrmFilter(): ?array
	{
		$filter = $this->getFilter();
		if (isset($filter))
		{
			return $filter->getValue();
		}

		return null;
	}

	/**
	 * Order for ORM and `getList` method.
	 *
	 * @see \Bitrix\Main\ORM\Data\DataManager method `getList`
	 *
	 * @return array
	 */
	public function getOrmOrder(): array
	{
		$sorting = $this->getOptions()->getSorting(
			$this->getDefaultSorting()
		);

		return $sorting['sort'];
	}

	#endregion orm
	#endregion public api

	/**
	 * Grid actions.
	 *
	 * These are the actions of the grid itself, and not its component parts (rows, panel, ...).
	 *
	 * @return Action[]
	 */
	protected function getActions(): array
	{
		$actions = [];

		$pagination = $this->getPagination();
		if (isset($pagination))
		{
			$actions[] = new PaginationAction($pagination, $this->getPaginationStorage());
		}

		return $actions;
	}

	/**
	 * Grid action.
	 *
	 * @param string $id
	 *
	 * @return Action|null
	 */
	final protected function getActionById(string $id): ?Action
	{
		if (empty($id))
		{
			return null;
		}

		if (!isset($this->actionsMap))
		{
			$this->actionsMap = [];

			foreach ($this->getActions() as $action)
			{
				$this->actionsMap[$action::getId()] = $action;
			}
		}

		return $this->actionsMap[$id] ?? null;
	}

	/**
	 * Processing only grid actions.
	 *
	 * @see `::processRequest` for processing all actions (grid, rows and panel).
	 *
	 * @param GridRequest $request
	 *
	 * @return GridResponse|null
	 */
	protected function processGridActionsRequest(GridRequest $request): ?GridResponse
	{
		$result = null;

		if (!check_bitrix_sessid())
		{
			return null;
		}

		$requestGridId = $request->getGridId();
		if ($requestGridId !== $this->getId())
		{
			return null;
		}

		$action = $this->getActionById(
			$request->getGridActionId() ?? ''
		);
		if ($action)
		{
			$result = $action->processRequest($request->getHttpRequest());
		}
		else
		{
			return null;
		}

		return
			isset($result)
				? $this->gridResponseFactory->createFromResult($result)
				: null
		;
	}

	/**
	 * Ids of visible columns.
	 *
	 * If the user's display options are filled in, they are used.
	 * Otherwise, used default columns.
	 *
	 * @return string[] columns ids
	 */
	public function getVisibleColumnsIds(): array
	{
		$visibleColumns = $this->getOptions()->GetVisibleColumns();
		if (empty($visibleColumns))
		{
			$visibleColumns = [];

			foreach ($this->getColumns() as $column)
			{
				if ($column->isDefault())
				{
					$visibleColumns[] = $column->getId();
				}
			}
		}

		return $visibleColumns;
	}

	/**
	 * Default sorting.
	 *
	 * @return array
	 */
	protected function getDefaultSorting(): array
	{
		return [
			'ID' => 'ASC',
		];
	}

	/**
	 * Create columns collection.
	 *
	 * @return Columns
	 */
	abstract protected function createColumns(): Columns;

	/**
	 * Create rows collection.
	 *
	 * @return Rows
	 */
	protected function createRows(): Rows
	{
		$emptyRowAssembler = new EmptyRowAssembler(
			$this->getVisibleColumnsIds()
		);

		return new Rows(
			$emptyRowAssembler
		);
	}

	/**
	 * Create panel.
	 *
	 * @return Panel|null
	 */
	protected function createPanel(): ?Panel
	{
		return null;
	}

	/**
	 * Create filter.
	 *
	 * @return Filter|null
	 */
	protected function createFilter(): ?Filter
	{
		return null;
	}

	/**
	 * Create pagination.
	 *
	 * In most cases, you can use `PaginationFactory` for create pagination.
	 *
	 * @see \Bitrix\Main\Grid\Pagination\PaginationFactory
	 *
	 * @return PageNavigation|null
	 */
	protected function createPagination(): ?PageNavigation
	{
		return null;
	}
}

trait DeprecatedMethods
{
	/**
	 * @deprecated use `createPagination` method.
	 *
	 * Init pagination.
	 *
	 * If you use pagination, you need to call this method before getting the ORM parameters (`getOrmParams` method).
	 *
	 * @param int $totalRowsCount
	 * @param string|null $navId
	 *
	 * @return void
	 */
	public function initPagination(int $totalRowsCount, ?string $navId = null): void
	{
		$navParams = $this->getOptions()->GetNavParams();
		if (empty($navId))
		{
			$navId = $this->getId() . '_nav';
		}

		$this->pagination = new PageNavigation($navId);
		$this->pagination->allowAllRecords(false);
		$this->pagination->setPageSize($navParams['nPageSize']);
		$this->pagination->setPageSizes($this->getPageSizes());
		$this->pagination->setRecordCount($totalRowsCount);
		$this->pagination->setCurrentPage(1);

		$storage = $this->getPaginationStorage();
		if ($storage instanceof PageNavigationStorage)
		{
			$storage->fill($this->pagination);
		}
	}

	/**
	 * @deprecated use `createPagination` method.
	 *
	 * Available page sizes.
	 *
	 * @return int[]
	 */
	protected function getPageSizes(): array
	{
		return [
			5,
			10,
			20,
			50,
			100,
		];
	}
}
