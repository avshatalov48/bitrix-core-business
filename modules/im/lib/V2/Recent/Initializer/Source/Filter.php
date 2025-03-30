<?php

namespace Bitrix\Im\V2\Recent\Initializer\Source;

use Bitrix\Main\ORM\Query\Query;

interface Filter
{
	public function apply(Query $query, string $userIdFieldName): ?Query;
}