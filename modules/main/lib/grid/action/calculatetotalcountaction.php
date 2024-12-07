<?php

namespace Bitrix\Main\Grid\Action;

use Bitrix\Main\HttpRequest;
use Bitrix\Main\Result;
use Bitrix\Main\UI\PageNavigation;
use Closure;

final class CalculateTotalCountAction implements Action
{
	public function __construct(
		private readonly Closure $calculator,
		private readonly PageNavigation $pagination,
	)
	{}

	/**
	 * @inheritDoc
	 */
	public static function getId(): string
	{
		return 'get_total_rows_count';
	}

	/**
	 * @inheritDoc
	 */
	public function processRequest(HttpRequest $request): ?Result
	{
		$totalCount = $this->calculator->__invoke();

		$this->pagination->setRecordCount($totalCount);

		$result = new Result();
		$result->setData([
			'totalCount' => $totalCount,
		]);

		return $result;
	}
}
