<?php

declare(strict_types=1);

namespace Bitrix\Main\Provider\Params;

abstract class Sort implements SortInterface
{
	/**
	 * @param string[] $sort Field names that can use as a sort criteria
	 */
	public function __construct(protected array $sort = [])
	{
	}

	public function prepareSort(): array
	{
		return array_intersect_key($this->sort, array_flip($this->getAllowedFields()));
	}

	/**
	 * Returns a list of field names that can use as a sort criteria
	 *
	 * @return string[]
	 */
	abstract protected function getAllowedFields(): array;
}
