<?php

declare(strict_types=1);

namespace Bitrix\Main\Provider\Params;

use Bitrix\Main\ORM\Query\Query;

interface PrepareQueryInterface
{
	public function prepareQuery(Query $query): void;
}
