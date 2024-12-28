<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Handler\Update;

use Bitrix\Main\Error;
use Bitrix\Socialnetwork\Collab\Control\Command\CollabUpdateCommand;
use Bitrix\Socialnetwork\Collab\Integration\IM\ActionMessageBuffer;
use Bitrix\Socialnetwork\Provider\EmployeeProvider;
use Bitrix\Socialnetwork\Collab\Control\Handler\Trait\AddMemberLogTrait;
use Bitrix\Socialnetwork\Collab\Control\Handler\Trait\DeleteMemberLogTrait;
use Bitrix\Socialnetwork\Control\Member\Trait\AddMemberTrait;
use Bitrix\Socialnetwork\Control\Member\Trait\GetMembersTrait;
use Bitrix\Socialnetwork\Collab\Integration\IM\ActionType;
use Bitrix\Socialnetwork\Collab\Integration\IM\ActionMessageFactory;
use Bitrix\Socialnetwork\Control\Command\UpdateCommand;
use Bitrix\Socialnetwork\Control\Handler\HandlerResult;
use Bitrix\Socialnetwork\Control\Handler\Update\UpdateHandlerInterface;
use Bitrix\Socialnetwork\Integration\HumanResources\AccessCodeConverter;
use Bitrix\Socialnetwork\Item\Workgroup;
use Bitrix\Socialnetwork\UserToGroupTable;

class UpdateMemberHandler implements UpdateHandlerInterface
{
	use AddMemberTrait;
	use GetMembersTrait;
	use AddMemberLogTrait;
	use DeleteMemberLogTrait;

	public function update(UpdateCommand $command, Workgroup $entityBefore, Workgroup $entityAfter): HandlerResult
	{
		$handlerResult = new HandlerResult();

		if (!$command instanceof CollabUpdateCommand)
		{
			$handlerResult->addError(new Error('Unexpected command type'));

			return $handlerResult;
		}

		$addResult = $this->addMembersByCommand($command);
		$deleteResult = $this->deleteMembersByCommand($command, $entityAfter);

		return $handlerResult->merge($addResult)->merge($deleteResult);
	}

	protected function addMembersByCommand(CollabUpdateCommand $command): HandlerResult
	{
		$handlerResult = new HandlerResult();

		$addMembers = $command->getAddMembers();
		if (empty($addMembers))
		{
			return $handlerResult;
		}

		$addMembersByCommand = (new AccessCodeConverter(...$addMembers))
			->getUsers()
			->getUserIds()
		;

		$add = array_diff($addMembersByCommand, $this->getMemberIds($command->getId()));

		$handlerResult = $this->addMembers(
				$command->getId(),
				$command->getInitiatorId(),
				UserToGroupTable::ROLE_USER,
			...$add,
		);

		if (!$handlerResult->isSuccess())
		{
			return $handlerResult;
		}

		[$employeeIds, $guestIds] = EmployeeProvider::getInstance()->splitIntoEmployeesAndGuests($add);

		ActionMessageBuffer::getInstance()
			->put(ActionType::AddUser, $command->getId(), $command->getInitiatorId(), $employeeIds)
			->put(ActionType::AddGuest, $command->getId(), $command->getInitiatorId(), $guestIds);

		$writeToLogResult = $this->writeAddMemberLog(
			$add,
			$command->getId(),
			$command->getInitiatorId(),
			UserToGroupTable::ROLE_USER
		);

		return $handlerResult->merge($writeToLogResult);
	}

	protected function deleteMembersByCommand(CollabUpdateCommand $command, Workgroup $entityAfter): HandlerResult
	{
		$handlerResult = new HandlerResult();

		$deleteMembers = $command->getDeleteMembers();
		if (empty($deleteMembers))
		{
			return $handlerResult;
		}

		$delete = (new AccessCodeConverter(...$deleteMembers))
			->getUsers()
			->getUserIds()
		;

		$handlerResult = $this->deleteMembers($command->getId(), ...$delete);

		if (!$handlerResult->isSuccess())
		{
			return $handlerResult;
		}

		if (in_array($command->getInitiatorId(), $delete, true))
		{
			ActionMessageFactory::getInstance()
				->getActionMessage(ActionType::LeaveUser, $command->getId(), $command->getInitiatorId())
				->runAction()
			;

			$delete = array_filter(
				$delete,
				static fn(int $userId): bool => $userId !== $command->getInitiatorId(),
			);
		}

		ActionMessageFactory::getInstance()
			->getActionMessage(ActionType::ExcludeUser, $command->getId(), $command->getInitiatorId())
			->runAction($delete)
		;

		$writeToLogResult = $this->writeDeleteMemberLog($delete, $entityAfter, $command->getInitiatorId());

		return $handlerResult->merge($writeToLogResult);
	}
}
