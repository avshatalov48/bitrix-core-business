<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Handler\Add;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Error;
use Bitrix\Socialnetwork\Collab\Collab;
use Bitrix\Socialnetwork\Collab\Control\Command\CollabAddCommand;
use Bitrix\Socialnetwork\Collab\Log\Entry\CreateCollabLogEntry;
use Bitrix\Socialnetwork\Control\Command\AddCommand;
use Bitrix\Socialnetwork\Control\Handler\Add\AddHandlerInterface;
use Bitrix\Socialnetwork\Control\Handler\HandlerResult;
use Bitrix\Socialnetwork\Item\Workgroup;

class AddLogEntryHandler implements AddHandlerInterface
{
	public function add(AddCommand $command, Workgroup $entity): HandlerResult
	{
		$handlerResult = new HandlerResult();

		if (!($command instanceof CollabAddCommand) || !($entity instanceof Collab))
		{
			$handlerResult->addError(new Error('Unexpected command type'));

			return $handlerResult;
		}

		$logEntry = new CreateCollabLogEntry(
			userId: $command->getInitiatorId(),
			collabId: $entity->getId(),
		);

		$logEntry->setDescription($command->getDescription() ?? '');

		try
		{
			$service = ServiceLocator::getInstance()->get('socialnetwork.collab.log.service');
			$service->save($logEntry);
		}
		catch (\Exception $exception)
		{
			$handlerResult->addError(Error::createFromThrowable($exception));
		}

		return $handlerResult;
	}
}
