<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\UI\PageNavigation;

trait ListAction
{
	abstract protected function getServiceListName(): string;

	abstract protected function getList(
		array $select,
		array $filter,
		array $order,
		PageNavigation $pageNavigation = null
	): array;

	abstract protected function count($filter);

	/**
	 * @param PageNavigation $pageNavigation
	 * @param array $select
	 * @param array $filter
	 * @param array $order
	 * @param bool $__calculateTotalCount
	 * @return Page
	 */
	public function listAction(
		PageNavigation $pageNavigation,
		array $select = [],
		array $filter = [],
		array $order = [],
		bool $__calculateTotalCount = true
	): Page
	{
		$params = $this->modifyListActionParameters([
			'select' => $select,
			'filter' => $filter,
			'order' => $order,
		]);

		return new Page(
			$this->getServiceListName(),
			$this->getList($params['select'], $params['filter'], $params['order'], $pageNavigation),
			$__calculateTotalCount ? $this->count($filter) : 0
		);
	}

	/**
	 * Modify select, filter or order for entity.
	 *
	 * @param array $params
	 * All keys are case-sensitive:
	 * <ul>
	 * <li>array select - constains select fields. Required.
	 * <li>array filter - constains filter. Required
	 * <li>array order - contains order for list. Required.
	 * </ul>
	 * @return array - contains incoming parameters after change.
	 */
	protected function modifyListActionParameters(array $params): array
	{
		return $params;
	}
}
