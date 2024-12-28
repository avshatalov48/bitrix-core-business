<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Handler\Delete;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Error;
use Bitrix\Socialnetwork\Collab\Collab;
use Bitrix\Socialnetwork\Collab\Control\Activity\Command\DeleteLastActivityCommand;
use Bitrix\Socialnetwork\Collab\Control\Command\CollabDeleteCommand;
use Bitrix\Socialnetwork\Control\Command\DeleteCommand;
use Bitrix\Socialnetwork\Control\Handler\Delete\DeleteHandlerInterface;
use Bitrix\Socialnetwork\Control\Handler\HandlerResult;
use Bitrix\Socialnetwork\Item\Workgroup;

class DeleteLastActivityHandler implements DeleteHandlerInterface
{
	public function delete(DeleteCommand $command, Workgroup $entityBefore): HandlerResult
	{
		$result = new HandlerResult();
		if (!$command instanceof CollabDeleteCommand || !$entityBefore instanceof Collab)
		{
			$result->addError(new Error('Unexpected command type'));

			return $result;
		}

		$service = ServiceLocator::getInstance()->get('socialnetwork.collab.activity.service');

		$activityCommand = (new DeleteLastActivityCommand())
			->setCollabId($command->getId());

		$activityResult = $service->delete($activityCommand);

		return $result->merge($activityResult);
	}
}