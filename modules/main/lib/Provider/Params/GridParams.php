<?php

declare(strict_types=1);

namespace Bitrix\Main\Provider\Params;

use Bitrix\Main\ORM\Query\Filter\ConditionTree;

class GridParams
{
	public function __construct(
		public PagerInterface $pager,
		public ?FilterInterface $filter = null,
		public ?SortInterface $sort = null,
		public ?SelectInterface $select = null,
	)
	{
	}

	public function getLimit(): int
	{
		return $this->pager->getLimit();
	}

	public function getOffset(): int
	{
		return $this->pager->getOffset();
	}

	public function getFilter(): ?ConditionTree
	{
		return $this->filter?->prepareFilter();
	}

	/**
	 * Returns a list of fields which can use for sort
	 *
	 * @return string[]|null
	 */
	public function getSort(): ?array
	{
		return $this->sort?->prepareSort();
	}

	/**
	 * Returns a list of selectable fields names
	 *
	 * @return string[]|null
	 */
	public function getSelect(): ?array
	{
		return $this->select?->prepareSelect();
	}
}
