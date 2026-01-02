<?php

declare(strict_types=1);

namespace Bitrix\Main\Provider\Params;

interface SelectInterface
{
	/**
	 * Returns a list of selectable fields names
	 *
	 * @return string[]
	 */
	public function prepareSelect(): array;
}
