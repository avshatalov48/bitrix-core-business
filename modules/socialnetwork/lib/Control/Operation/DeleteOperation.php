<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Operation;

use Bitrix\Main\Error;
use Bitrix\Socialnetwork\Control\Command\DeleteCommand;
use Bitrix\Socialnetwork\Control\Exception\GroupNotDeletedException;
use Bitrix\Socialnetwork\Control\GroupResult;
use Bitrix\Socialnetwork\Item\Workgroup;
use CSocNetGroup;

class DeleteOperation extends AbstractOperation
{
	protected DeleteCommand $command;

	public function __construct(DeleteCommand $command)
	{
		$this->command = $command;
	}

	public function run(): GroupResult
	{
		$result = new GroupResult();

		$deleteResult = CSocNetGroup::Delete($this->command->getId());

		if ($deleteResult === false)
		{
			$result->addApplicationError();

			return $result;
		}

		$result->setGroup(Workgroup::createFromId($this->command->getId()));

		return $result;
	}
}