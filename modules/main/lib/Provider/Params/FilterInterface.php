<?php

declare(strict_types=1);

namespace Bitrix\Main\Provider\Params;

use Bitrix\Main\ORM\Query\Filter\ConditionTree;

interface FilterInterface
{
	public function prepareFilter(): ConditionTree;
}
