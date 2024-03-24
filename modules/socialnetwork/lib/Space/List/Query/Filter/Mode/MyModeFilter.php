<?php

namespace Bitrix\Socialnetwork\Space\List\Query\Filter\Mode;

use Bitrix\Main\ORM\Query\Filter\ConditionTree;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Socialnetwork\UserToGroupTable;

final class MyModeFilter extends AbstractModeFilter
{
	public function apply(Query $query): void
	{
		$query->where($this->getParticipantCondition());
		$query->setOrder(['PIN.ID' => 'DESC', 'RECENT_ACTIVITY_DATE' => 'DESC']);
	}
}