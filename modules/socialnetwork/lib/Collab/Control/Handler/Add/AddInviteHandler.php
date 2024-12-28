<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Handler\Add;

use Bitrix\Socialnetwork\Collab\Activity\LastActivityTrigger;
use Bitrix\Socialnetwork\Collab\Integration\IM\ActionMessageBuffer;
use Bitrix\Socialnetwork\Provider\EmployeeProvider;
use Bitrix\Socialnetwork\Control\Member\Trait\AddMemberTrait;
use Bitrix\Socialnetwork\Collab\Integration\IM\ActionType;
use Bitrix\Socialnetwork\Control\Command\AddCommand;
use Bitrix\Socialnetwork\Control\Handler\Add\AddHandlerInterface;
use Bitrix\Socialnetwork\Control\Handler\HandlerResult;
use Bitrix\Socialnetwork\Integration\HumanResources\AccessCodeConverter;
use Bitrix\Socialnetwork\Item\Workgroup;
use Bitrix\Socialnetwork\UserToGroupTable;

class AddInviteHandler implements AddHandlerInterface
{
	use AddMemberTrait;

	public function add(AddCommand $command, Workgroup $entity): HandlerResult
	{
		$handlerResult = new HandlerResult();

		$invitedMembers = $command->getInvitedMembers();
		if (empty($invitedMembers))
		{
			return $handlerResult;
		}

		$membersByCommand = (new AccessCodeConverter(...$invitedMembers))
			->getUsers()
			->getUserIds();

		[$employeeIds, $guestIds] = EmployeeProvider::getInstance()->splitIntoEmployeesAndGuests($membersByCommand);

		$guestResult = $this->addMembers(
			$entity->getId(),
			$command->getInitiatorId(),
			UserToGroupTable::ROLE_REQUEST,
			...$guestIds,
		);

		$handlerResult->merge($guestResult);

		$employeeResult = $this->inviteMembers(
			$entity->getId(),
			$command->getInitiatorId(),
			...$employeeIds,
		);

		$handlerResult->merge($employeeResult);

		if (!$handlerResult->isSuccess())
		{
			return $handlerResult;
		}

		foreach ($membersByCommand as $userId)
		{
			LastActivityTrigger::execute($userId, $entity->getId());
		}

		$parameters = [
			'skipChat' => !$command->getInitiator()->isIntranet(),
		];

		ActionMessageBuffer::getInstance()
			->put(ActionType::InviteGuest, $entity->getId(), $command->getInitiatorId(), $guestIds)
			->put(ActionType::InviteUser, $entity->getId(), $command->getInitiatorId(), $employeeIds, $parameters);

		return $handlerResult;
	}
}