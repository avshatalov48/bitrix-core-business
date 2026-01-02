<?php

declare(strict_types=1);

namespace Bitrix\Main\Provider\Params;

interface SortInterface
{
	/**
	 * Returns a list of fields which can use for sort
	 *
	 * @return string[]
	 */
	public function prepareSort(): array;
}
