<?php

namespace Bitrix\Socialnetwork\Space\List\Query\Filter\Mode;

use Bitrix\Main\ORM\Query\Filter\ConditionTree;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Socialnetwork\UserToGroupTable;

final class AllModeFilter extends AbstractModeFilter
{
	public function apply(Query $query): void
	{
		$query->where($this->getAllVisibleCondition());
	}
}