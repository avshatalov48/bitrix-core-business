<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Handler\Update;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Error;
use Bitrix\Socialnetwork\Collab\Collab;
use Bitrix\Socialnetwork\Collab\Control\Command\CollabUpdateCommand;
use Bitrix\Socialnetwork\Collab\Log\CollabLogEntryCollection;
use Bitrix\Socialnetwork\Collab\Log\Entry\UpdateCollabLogEntry;
use Bitrix\Socialnetwork\Control\Command\UpdateCommand;
use Bitrix\Socialnetwork\Control\Handler\HandlerResult;
use Bitrix\Socialnetwork\Control\Handler\Update\UpdateHandlerInterface;
use Bitrix\Socialnetwork\Item\Workgroup;

class UpdateLogEntryHandler implements UpdateHandlerInterface
{
	private const FIELDS = [
		'name',
		'description',
	];

	private const IGNORE_VALUE_FIELDS = [
		'description',
	];

	public function update(UpdateCommand $command, Workgroup $entityBefore, Workgroup $entityAfter): HandlerResult
	{
		$handlerResult = new HandlerResult();

		if (!
			($command instanceof CollabUpdateCommand)
			|| !($entityBefore instanceof Collab)
			|| !($entityAfter instanceof Collab)
		)
		{
			$handlerResult->addError(new Error('Unexpected command type'));

			return $handlerResult;
		}

		$logEntryCollection = new CollabLogEntryCollection();

		/** @var CollabUpdateCommand $command */
		/** @var Collab $entityBefore */

		foreach (self::FIELDS as $field)
		{
			$methodName = 'get' . ucfirst($field);

			if (!method_exists($entityBefore, $methodName))
			{
				continue;
			}

			if ($command->$methodName() === null || $entityBefore->$methodName() === $command->$methodName())
			{
				continue;
			}

			$logEntry = new UpdateCollabLogEntry(
				userId: $command->getInitiatorId(),
				collabId: $entityAfter->getId(),
			);

			$logEntry->setFieldName($field);

			if (!in_array($field, self::IGNORE_VALUE_FIELDS, true))
			{
				$logEntry
					->setPreviousValue($entityBefore->$methodName())
					->setCurrentValue($command->$methodName())
				;
			}

			$logEntryCollection->add($logEntry);
		}

		$service = ServiceLocator::getInstance()->get('socialnetwork.collab.log.service');
		$service->saveCollection($logEntryCollection);

		return $handlerResult;
	}
}
