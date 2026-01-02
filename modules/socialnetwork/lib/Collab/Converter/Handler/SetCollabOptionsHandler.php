<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Converter\Handler;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Result;
use Bitrix\Socialnetwork\Collab\Control\Command\ValueObject\CollabOptions;
use Bitrix\Socialnetwork\Collab\Control\Option\Command\SetOptionsCommand;
use Bitrix\Socialnetwork\Collab\Converter\Command\AbstractConverterCommand;

class SetCollabOptionsHandler extends AbstractHandler
{
	public function __construct(private readonly CollabOptions $options)
	{
	}

	public function execute(AbstractConverterCommand $command): Result
	{
		$result = new Result();
		$group = $command->getGroup();
		$optionCommand = (new SetOptionsCommand())
			->setCollabId($group->getId())
			->setOptions($this->options)
		;

		$service = ServiceLocator::getInstance()->get('socialnetwork.collab.option.service');

		$optionResult = $service->set($optionCommand);

		if (!$optionResult->isSuccess())
		{
			$result->addErrors($optionResult->getErrors());
		}

		return $result;
	}
}
