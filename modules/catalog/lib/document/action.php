<?php

namespace Bitrix\Catalog\Document;

use Bitrix\Main\Result;

/**
 * Action with document.
 */
interface Action
{
	/**
	 * Checking the possibility of executing an action.
	 *
	 * @return Result
	 */
	public function canExecute(): Result;

	/**
	 * Executing action.
	 *
	 * @return Result
	 */
	public function execute(): Result;
}