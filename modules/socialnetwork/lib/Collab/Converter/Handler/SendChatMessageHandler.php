<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Converter\Handler;

use Bitrix\Main\Result;
use Bitrix\Socialnetwork\Collab\Converter\Command\AbstractConverterCommand;
use Bitrix\Socialnetwork\Collab\Integration\IM\ActionMessageFactory;
use Bitrix\Socialnetwork\Collab\Integration\IM\ActionType;

class SendChatMessageHandler extends AbstractHandler
{
	public function __construct(private readonly ActionType $actionType)
	{
	}

	public function execute(AbstractConverterCommand $command): Result
	{
		$group = $command->getGroup();

		ActionMessageFactory::getInstance()
			->getActionMessage($this->actionType, $group->getId(), $command->getInitiatorId())
			->send()
		;

		return new Result();
	}
}
