<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Handler\Trait;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Error;
use Bitrix\Socialnetwork\Collab\Control\Command\CollabAddCommand;
use Bitrix\Socialnetwork\Collab\Control\Command\CollabUpdateCommand;
use Bitrix\Socialnetwork\Collab\Control\Option\Command\SetOptionsCommand;
use Bitrix\Socialnetwork\Control\Command\AbstractCommand;
use Bitrix\Socialnetwork\Control\Handler\HandlerResult;
use Bitrix\Socialnetwork\Item\Workgroup;

trait SetOptionsTrait
{
	public function setOptions(AbstractCommand $command, Workgroup $entity): HandlerResult
	{
		$handlerResult = new HandlerResult();
		if (!$this->isParamsCorrect($handlerResult, $command))
		{
			return $handlerResult;
		}

		$options = $command->getOptions();
		if ($options === null)
		{
			return $handlerResult;
		}

		$optionCommand = (new SetOptionsCommand())
			->setCollabId($entity->getId())
			->setOptions($options);

		$service = ServiceLocator::getInstance()->get('socialnetwork.collab.option.service');

		$optionResult = $service->set($optionCommand);

		if (!empty($options->getValue()))
		{
			$handlerResult->setGroupChanged();
		}

		return $handlerResult->merge($optionResult);
	}

	private function isParamsCorrect(HandlerResult $handlerResult, AbstractCommand $command): bool
	{
		if (!($command instanceof CollabAddCommand || $command instanceof CollabUpdateCommand))
		{
			$handlerResult->addError(new Error('Unexpected command type'));

			return false;
		}

		return true;
	}
}