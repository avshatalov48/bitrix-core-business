<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Handler\Delete;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Error;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Socialnetwork\Collab\Collab;
use Bitrix\Socialnetwork\Collab\Control\Command\CollabDeleteCommand;
use Bitrix\Socialnetwork\Collab\Control\Option\Command\DeleteOptionsCommand;
use Bitrix\Socialnetwork\Control\Command\DeleteCommand;
use Bitrix\Socialnetwork\Control\Handler\Delete\DeleteHandlerInterface;
use Bitrix\Socialnetwork\Control\Handler\HandlerResult;
use Bitrix\Socialnetwork\Item\Workgroup;
use Psr\Container\NotFoundExceptionInterface;

class DeleteOptionsHandler implements DeleteHandlerInterface
{
	/**
	 * @throws NotFoundExceptionInterface
	 * @throws ObjectNotFoundException
	 */
	public function delete(DeleteCommand $command, Workgroup $entityBefore): HandlerResult
	{
		$result = new HandlerResult();
		if (!$command instanceof CollabDeleteCommand || !$entityBefore instanceof Collab)
		{
			$result->addError(new Error('Unexpected command type'));

			return $result;
		}

		$optionCommand = (new DeleteOptionsCommand())
			->setCollabId($command->getId());

		$service = ServiceLocator::getInstance()->get('socialnetwork.collab.option.service');

		$optionResult = $service->delete($optionCommand);

		return $result->merge($optionResult);
	}
}
