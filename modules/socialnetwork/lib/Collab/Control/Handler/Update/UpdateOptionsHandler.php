<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Handler\Update;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\SocialNetwork\Collab\Analytics\CollabAnalytics;
use Bitrix\Socialnetwork\Collab\Collab;
use Bitrix\Socialnetwork\Collab\Control\Command\CollabUpdateCommand;
use Bitrix\Socialnetwork\Collab\Control\Handler\Trait\SetOptionsTrait;
use Bitrix\Socialnetwork\Collab\Control\Option\AbstractOption;
use Bitrix\Socialnetwork\Collab\Log\CollabLogEntryCollection;
use Bitrix\Socialnetwork\Collab\Log\Entry\UpdateCollabLogEntry;
use Bitrix\Socialnetwork\Collab\Property\Option;
use Bitrix\Socialnetwork\Control\Handler\HandlerResult;
use Bitrix\Socialnetwork\Control\Handler\Update\UpdateHandlerInterface;
use Bitrix\Socialnetwork\Control\Command\UpdateCommand;
use Bitrix\Socialnetwork\Item\Workgroup;

class UpdateOptionsHandler implements UpdateHandlerInterface
{
	use SetOptionsTrait;

	public function update(UpdateCommand $command, Workgroup $entityBefore, Workgroup $entityAfter): HandlerResult
	{
		$handlerResult = $this->setOptions($command, $entityBefore);

		if (!$handlerResult->isSuccess())
		{
			return $handlerResult;
		}

		$writeToLogResult = $this->writeCollabUpdateToLog($command, $entityBefore);

		return $handlerResult->merge($writeToLogResult);
	}

	private function writeCollabUpdateToLog(UpdateCommand $command, Workgroup $entityBefore): HandlerResult
	{
		$handlerResult = new HandlerResult();

		if (!($command instanceof CollabUpdateCommand) || !($entityBefore instanceof Collab))
		{
			return $handlerResult;
		}

		$logEntryCollection = new CollabLogEntryCollection();

		$newOptions = $command->getOptions()?->getValue();

		if ($newOptions === null)
		{
			return $handlerResult;
		}

		$analytics = CollabAnalytics::getInstance();

		$oldOptions = $entityBefore->getOptions();

		foreach ($newOptions as $newOption)
		{
			/** @var AbstractOption $newOption */
			$oldOption = $this->getOptionByName($oldOptions, $newOption->getName());
			if ($oldOption === null)
			{
				continue;
			}

			if ($oldOption->value === $newOption->getValue())
			{
				continue;
			}

			$logEntry = new UpdateCollabLogEntry(
				userId: $command->getInitiatorId(),
				collabId: $command->getId(),
			);

			$logEntry
				->setFieldName($newOption->getName())
				->setPreviousValue($oldOption->value)
				->setCurrentValue($newOption->getValue())
			;

			$logEntryCollection->add($logEntry);

			$analytics->onSettingsChanged($command->getInitiatorId(), $command->getId(), strtolower($newOption->getName()));
		}

		if ($logEntryCollection->isEmpty())
		{
			return $handlerResult;
		}

		$service = ServiceLocator::getInstance()->get('socialnetwork.collab.log.service');
		$service->saveCollection($logEntryCollection);

		return $handlerResult;
	}

	private function getOptionByName(array $options, string $name): ?Option
	{
		foreach ($options as $option)
		{
			/** @var Option $option */
			if ($option->name === $name)
			{
				return $option;
			}
		}

		return null;
	}
}
