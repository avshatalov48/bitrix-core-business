<?php

namespace Bitrix\Im\V2\Controller\Filter;

use Bitrix\Im\V2\Notification\Group\UserGroup\Dto\ConditionDto;
use Bitrix\Im\V2\Notification\Group\UserGroup\Dto\UserGroupDto;
use Bitrix\Im\V2\Notification\Group\UserGroup\GroupRepository;
use Bitrix\Im\V2\Notification\NotifyError;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Engine\ActionFilter\Base;

class CheckNotifyGroupAccess extends Base
{
	private readonly GroupRepository $groupRepository;

	public function __construct()
	{
		parent::__construct();
		$this->groupRepository = new GroupRepository();
	}

	public function onBeforeAction(Event $event): ?EventResult
	{
		if ($this->isNotFound())
		{
			$this->addError(new NotifyError(NotifyError::GROUP_NOT_FOUND));

			return new EventResult(EventResult::ERROR, null, null, $this);
		}

		return null;
	}

	private function isNotFound(): bool
	{
		foreach ($this->getAction()->getArguments() as $argument)
		{
			if ($argument instanceof UserGroupDto || $argument instanceof ConditionDto)
			{
				$exists = $this->groupRepository->isExistsWithUserId($argument->groupId, $argument->userId);

				return $exists === false;
			}
		}

		return false;
	}
}