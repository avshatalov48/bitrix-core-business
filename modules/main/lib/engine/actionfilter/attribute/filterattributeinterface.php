<?php

declare(strict_types=1);

namespace Bitrix\Main\Engine\ActionFilter\Attribute;

use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Engine\ActionFilter\FilterType;

interface FilterAttributeInterface
{
	/**
	 * @return (Base|string)[]
	 */
	public function getFilters(): array;

	public function getType(): FilterType;
}