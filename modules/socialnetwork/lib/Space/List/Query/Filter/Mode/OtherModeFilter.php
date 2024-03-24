<?php

namespace Bitrix\Socialnetwork\Space\List\Query\Filter\Mode;

use Bitrix\Main\ORM\Query\Filter\ConditionTree;
use Bitrix\Main\ORM\Query\Query;

final class OtherModeFilter extends AbstractModeFilter
{
	public function apply(Query $query): void
	{
		$availableFilter = Query::filter()
			->whereNull('MEMBER.USER_ID')
		;

		if (!$this->isSuperAdmin)
		{
			$availableFilter->where('VISIBLE', 'Y');
		}

		$query->where(Query::filter()
			->logic(ConditionTree::LOGIC_OR)
			->where($availableFilter)
			->where($this->getRequestCondition())
		);
	}
}