<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Handler\Add;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Socialnetwork\Collab\Integration\IM\ActionMessageBuffer;
use Bitrix\Socialnetwork\Provider\EmployeeProvider;
use Bitrix\Socialnetwork\Control\Member\Trait\AddMemberTrait;
use Bitrix\Socialnetwork\Collab\Integration\IM\ActionType;
use Bitrix\Socialnetwork\Control\Command\AddCommand;
use Bitrix\Socialnetwork\Control\Handler\Add\AddHandlerInterface;
use Bitrix\Socialnetwork\Control\Handler\HandlerResult;
use Bitrix\Socialnetwork\Control\Member\Trait\GetMembersTrait;
use Bitrix\Socialnetwork\Integration\HumanResources\AccessCodeConverter;
use Bitrix\Socialnetwork\Item\Workgroup;
use Bitrix\Socialnetwork\UserToGroupTable;

class AddMemberHandler implements AddHandlerInterface
{
	use GetMembersTrait;
	use AddMemberTrait;

	/**
	 * @throws LoaderException
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function add(AddCommand $command, Workgroup $entity): HandlerResult
	{
		$handlerResult = new HandlerResult();

		$members = $command->getMembers();
		if (empty($members))
		{
			return $handlerResult;
		}

		$membersByCommand = (new AccessCodeConverter(...$members))
			->getUsers()
			->getUserIds();

		$add = array_diff($membersByCommand, $this->getMemberIds($entity->getId()));

		$handlerResult = $this->addMembers(
			$entity->getId(),
			$command->getInitiatorId(),
			UserToGroupTable::ROLE_USER,
			...$add,
		);

		if (!$handlerResult->isSuccess())
		{
			return $handlerResult;
		}

		[$employeeIds, $guestIds] = EmployeeProvider::getInstance()->splitIntoEmployeesAndGuests($membersByCommand);

		ActionMessageBuffer::getInstance()
			->put(ActionType::AddUser, $entity->getId(), $command->getInitiatorId(), $employeeIds)
			->put(ActionType::AddGuest, $entity->getId(), $command->getInitiatorId(), $guestIds);

		return $handlerResult;
	}
}