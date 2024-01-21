<?php

namespace Bitrix\Socialnetwork\Space\List\Query\Filter\Mode;

use Bitrix\Main\ORM\Query\Filter\ConditionTree;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Socialnetwork\UserToGroupTable;

final class OtherModeFilter extends AbstractModeFilter
{
	public function apply(Query $query): void
	{
		$query->where(Query::filter()
			->logic(ConditionTree::LOGIC_OR)
			->where(Query::filter()
				->whereNull('MEMBER.USER_ID')
				->where('VISIBLE', 'Y')
			)
			->where('MEMBER.ROLE', UserToGroupTable::ROLE_REQUEST)
		);
	}
}