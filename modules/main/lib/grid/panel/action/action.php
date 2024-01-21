<?php

namespace Bitrix\Main\Grid\Panel\Action;

use Bitrix\Main\Filter\Filter;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Result;

/**
 * Object of a single panel action.
 *
 * @see \Bitrix\Main\Grid\Panel\Action\EditAction
 * @see \Bitrix\Main\Grid\Panel\Action\RemoveAction
 * @see \Bitrix\Main\Grid\Panel\Action\ForAllCheckboxAction
 * @see \Bitrix\Main\Grid\Panel\Action\GroupAction
 */
interface Action
{
	/**
	 * Action's id.
	 *
	 * @return string
	 */
	public static function getId(): string;

	/**
	 * Request processing.
	 *
	 * @param HttpRequest $request
	 * @param bool $isSelectedAllRows
	 * @param Filter|null $filter if is selected all rows, MUST BE to use a filter to get a list of items.
	 *
	 * @return Result|null `null` is returned if the action does not have a handler, or the action cannot return the result object.
	 */
	public function processRequest(HttpRequest $request, bool $isSelectedAllRows, ?Filter $filter): ?Result;

	/**
	 * Panel control.
	 *
	 * @see \Bitrix\Main\Grid\Panel\Snippet for details and examples.
	 *
	 * @return array|null
	 */
	public function getControl(): ?array;
}
