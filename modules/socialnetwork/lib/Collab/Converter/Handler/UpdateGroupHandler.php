<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Converter\Handler;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Result;
use Bitrix\Socialnetwork\Collab\Control\Command\CollabUpdateCommand;
use Bitrix\Socialnetwork\Collab\Converter\Command\AbstractConverterCommand;
use Bitrix\Socialnetwork\Control\AbstractGroupService;
use Bitrix\Socialnetwork\Control\Command\UpdateCommand;

class UpdateGroupHandler extends AbstractHandler
{
	public function __construct(private readonly UpdateCommand $updateCommand)
	{
	}

	public function execute(AbstractConverterCommand $command): Result
	{
		$result = new Result();
		$updateResult = $this->getService()->update($this->updateCommand);

		if (!$updateResult->isSuccess())
		{
			$result->addErrors($updateResult->getErrors());
		}

		return $result;
	}

	private function getService(): AbstractGroupService
	{
		$locator = ServiceLocator::getInstance();

		return match (true) {
			$this->updateCommand instanceof CollabUpdateCommand => $locator->get('socialnetwork.collab.service'),
			default => $locator->get('socialnetwork.group.service'),
		};
	}
}
