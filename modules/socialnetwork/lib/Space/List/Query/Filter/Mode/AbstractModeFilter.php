<?php

namespace Bitrix\Socialnetwork\Space\List\Query\Filter\Mode;

use Bitrix\Main\ORM\Query\Filter\ConditionTree;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Socialnetwork\Space\List\Query\Filter\FilterInterface;
use Bitrix\Socialnetwork\UserToGroupTable;

abstract class AbstractModeFilter implements FilterInterface
{
	public function __construct(protected int $userId)
	{}
	abstract public function apply(Query $query): void;
	protected function getAllPinnedCondition(): ConditionTree
	{
		return
			Query::filter()
				->whereNotNull('PIN.ID')
				->where($this->getAllVisibleCondition())
		;
	}

	protected function getAllVisibleCondition(): ConditionTree
	{
		return
			Query::filter()
				->logic(ConditionTree::LOGIC_OR)
				->where($this->getParticipantCondition())
				->where($this->getRequestCondition())
				->where('VISIBLE', 'Y')
		;
	}

	protected function getParticipantCondition(): ConditionTree
	{
		return
			Query::filter()
				->where('MEMBER.USER_ID', $this->userId)
				->where('MEMBER.ROLE', '<=' , UserToGroupTable::ROLE_USER)
		;
	}

	protected function getRequestCondition(): ConditionTree
	{
		return
			Query::filter()
				->where('MEMBER.USER_ID', $this->userId)
				->where('MEMBER.ROLE', UserToGroupTable::ROLE_REQUEST)
		;
	}
}