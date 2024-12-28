<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Handler\Add;

use Bitrix\Socialnetwork\Collab\Integration\IM\ActionMessageBuffer;
use Bitrix\Socialnetwork\Control\Member\Trait\AddMemberTrait;
use Bitrix\Socialnetwork\Collab\Integration\IM\ActionType;
use Bitrix\Socialnetwork\Collab\Integration\IM\ActionMessageFactory;
use Bitrix\Socialnetwork\Collab\Integration\IM\Messenger;
use Bitrix\Socialnetwork\Control\Command\AddCommand;
use Bitrix\Socialnetwork\Control\Handler\Add\AddHandlerInterface;
use Bitrix\Socialnetwork\Control\Handler\HandlerResult;
use Bitrix\Socialnetwork\Control\Member\Trait\GetMembersTrait;
use Bitrix\Socialnetwork\Integration\HumanResources\AccessCodeConverter;
use Bitrix\Socialnetwork\Item\Workgroup;
use Bitrix\Socialnetwork\UserToGroupTable;

class AddModeratorsHandler implements AddHandlerInterface
{
	use GetMembersTrait;
	use AddMemberTrait;

	public function add(AddCommand $command, Workgroup $entity): HandlerResult
	{
		$handlerResult = new HandlerResult();

		$moderators = $command->getModeratorMembers();
		if (empty($moderators))
		{
			return $handlerResult;
		}

		$membersByCommand = (new AccessCodeConverter(...$moderators))
			->getUsers()
			->getUserIds();

		$add = array_diff($membersByCommand, $this->getMemberIds($entity->getId()));

		$handlerResult = $this->addMembers(
			$entity->getId(),
			$command->getInitiatorId(),
			UserToGroupTable::ROLE_MODERATOR,
			...$add,
		);

		if (!$handlerResult->isSuccess())
		{
			return $handlerResult;
		}

		ActionMessageBuffer::getInstance()
			->put(ActionType::AddUser, $entity->getId(), $command->getInitiatorId(), $add)
			->flush();

		Messenger::setManagers($entity->getId(), $add);

		return $handlerResult;
	}
}