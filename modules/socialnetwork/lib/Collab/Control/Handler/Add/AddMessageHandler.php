<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Handler\Add;

use Bitrix\Socialnetwork\Collab\Integration\IM\ActionType;
use Bitrix\Socialnetwork\Collab\Integration\IM\ActionMessageFactory;
use Bitrix\Socialnetwork\Control\Command\AddCommand;
use Bitrix\Socialnetwork\Control\Handler\Add\AddHandlerInterface;
use Bitrix\Socialnetwork\Control\Handler\HandlerResult;
use Bitrix\Socialnetwork\Item\Workgroup;

class AddMessageHandler implements AddHandlerInterface
{
	public function add(AddCommand $command, Workgroup $entity): HandlerResult
	{
		ActionMessageFactory::getInstance()
			->getActionMessage(ActionType::CreateCollab, $entity->getId(), $command->getInitiatorId())
			->runAction();

		return new HandlerResult();
	}
}