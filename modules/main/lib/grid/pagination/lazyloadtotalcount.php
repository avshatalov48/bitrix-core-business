<?php

namespace Bitrix\Main\Grid\Pagination;

use Bitrix\Main\Grid\Action\CalculateTotalCountAction;
use Bitrix\Main\UI\PageNavigation;
use Closure;

trait LazyLoadTotalCount
{
	private ?Closure $totalCountCalculator = null;

	/**
	 * Get grid id.
	 *
	 * @return string
	 */
	abstract public function getId(): string;


	/**
	 * Set Closure for count the total number of rows.
	 * Use \Bitrix\Main\UI\CountlessPageNavigation for countless pagination.
	 *
	 * @param Closure $value
	 * @return void
	 */
	public function setTotalCountCalculator(Closure $value): void
	{
		$this->totalCountCalculator = $value;
	}

	private function getCalculateTotalCountAction(PageNavigation $pagination): CalculateTotalCountAction
	{
		return new CalculateTotalCountAction($this->totalCountCalculator, $pagination);
	}

	/**
	 * HTML for total rows widget.
	 *
	 * For correct work MUST BE loaded extension `main.pagination.lazyloadtotalcount` in component's template or page.
	 *
	 * @return string
	 */
	public function getTotalRowsCountHtml(): string
	{
		return '<lazy-load-total-count grid-id="' . htmlspecialcharsbx($this->getId()) . '" />';
	}

	public function setRawRowsWithLazyLoadPagination(Closure $getRawRowsCallback): void
	{
		$params = $this->getOrmParams();
		$pagination = $this->getPagination();

		if (empty($pagination) || empty($params['limit']))
		{
			$this->setRawRows(
				$getRawRowsCallback($params)
			);

			return;
		}

		$params['limit']++;
		$rows = [];
		$rowsCount = 0;

		foreach ($getRawRowsCallback($params) as $row)
		{
			$rowsCount++;
			if ($rowsCount > $pagination->getPageSize())
			{
				break;
			}

			$rows[] = $row;
		}

		$rowsCount += $params['offset'] ?? 0;
		$pagination->setRecordCount($rowsCount);

		$this->setRawRows($rows);
	}

	protected function getActions(): array
	{
		$actions = parent::getActions();
		$actions[] = $this->getCalculateTotalCountAction($this->getPagination());

		return $actions;
	}
}
