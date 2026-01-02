<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Converter;

use Bitrix\Main\Application;
use Bitrix\Main\DB\Connection;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Socialnetwork\Collab\Converter\Command\AbstractConverterCommand;
use Bitrix\Socialnetwork\Collab\Converter\Event\ConverterEvent;
use Bitrix\Socialnetwork\Collab\Converter\Handler\AbstractHandler;
use Bitrix\Socialnetwork\Collab\Converter\Result\ConverterResult;
use Bitrix\Socialnetwork\Internals\EventService;
use Bitrix\Socialnetwork\Internals\Registry\GroupRegistry;

class ConverterService
{
	private Connection $connection;

	public function __construct()
	{
		$this->connection = Application::getConnection();
	}

	public function convert(AbstractConverterCommand $command): ConverterResult
	{
		$result = new ConverterResult();

		$validationResult = $command->validateGroup();
		if (!$validationResult->isSuccess())
		{
			return $result->addErrors($validationResult->getErrors());
		}

		$entityBefore = $command->getGroup();

		$this->connection->startTransaction();

		try
		{
			$handlersResult = $this->runHandlers($command);

			if (!$handlersResult->isSuccess())
			{
				$this->revertChanges($command->getGroup()->getId());

				return $result->addErrors($handlersResult->getErrors());
			}

			GroupRegistry::getInstance()->invalidate($command->getGroup()->getId());
			$entityAfter = GroupRegistry::getInstance()->get($entityBefore->getId());

			if (!$entityAfter)
			{
				$this->revertChanges($command->getGroup()->getId());

				return $result->addError(new Error('Group not found after conversion'));
			}

			$this->connection->commitTransaction();
		}
		catch (\Throwable $exception)
		{
			$this->revertChanges($command->getGroup()->getId());

			return $result->addError(Error::createFromThrowable($exception));
		}

		(new ConverterEvent($command, $entityBefore, $entityAfter))->send();

		EventService\Service::addEvent(EventService\EventDictionary::EVENT_WORKGROUP_CONVERT, [
			'GROUP_ID' => $command->getGroup()->getId(),
		]);

		return $result->setEntityAfter($entityAfter);
	}

	private function runHandlers(AbstractConverterCommand $command): Result
	{
		$result = new Result();
		foreach ($command->getHandlers() as $handler)
		{
			if (!$handler instanceof AbstractHandler)
			{
				continue;
			}

			$handlerResult = $handler->execute($command);

			if (!$handlerResult->isSuccess())
			{
				$result->addErrors($handlerResult->getErrors());
				break;
			}
		}

		return $result;
	}

	private function revertChanges(int $id): void
	{
		global $CACHE_MANAGER;
		$CACHE_MANAGER->ClearByTag('sonet_group_' . $id);
		$this->connection->rollbackTransaction();
	}
}
