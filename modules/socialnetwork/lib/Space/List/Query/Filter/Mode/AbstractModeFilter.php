<?php

namespace Bitrix\Socialnetwork\Space\List\Query\Filter\Mode;

use Bitrix\Main\ORM\Query\Filter\ConditionTree;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Socialnetwork\Helper\User;
use Bitrix\Socialnetwork\Space\List\Query\Filter\FilterInterface;
use Bitrix\Socialnetwork\UserToGroupTable;

abstract class AbstractModeFilter implements FilterInterface
{
	protected bool $isSuperAdmin;

	public function __construct(protected int $userId)
	{
		$currentUserId = User::getCurrentUserId();

		$this->isSuperAdmin =
			$currentUserId === $this->userId
			&& \CSocNetUser::IsCurrentUserModuleAdmin()
		;
	}

	abstract public function apply(Query $query): void;

	protected function getAllVisibleCondition(): ConditionTree
	{
		$condition = Query::filter();

		if (!$this->isSuperAdmin)
		{
			$condition
				->logic(ConditionTree::LOGIC_OR)
				->where($this->getParticipantCondition())
				->where($this->getRequestCondition())
				->where('VISIBLE', 'Y')
			;
		}

		return $condition;
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