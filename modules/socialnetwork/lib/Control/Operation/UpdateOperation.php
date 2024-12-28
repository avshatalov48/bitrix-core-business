<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Operation;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Socialnetwork\Control\Command\UpdateCommand;
use Bitrix\Socialnetwork\Control\Exception\GroupNotUpdatedException;
use Bitrix\Socialnetwork\Control\GroupResult;
use Bitrix\Socialnetwork\Item\Workgroup;
use CSocNetGroup;

class UpdateOperation extends AbstractOperation
{
	protected UpdateCommand $command;
	protected Workgroup $entity;

	public function __construct(UpdateCommand $command)
	{
		$this->command = $command;
	}

	public function run(): GroupResult
	{
		$result = new GroupResult();

		$fields = $this->getMapper()->toArray($this->command);
		if ($fields !== [])
		{
			$updateResult = CSocNetGroup::Update($this->command->getId(), $fields);

			if ($updateResult === false)
			{
				$result->addApplicationError();

				return $result;
			}
		}

		$group = $this->getRegistry()->get($this->command->getId());
		if ($group === null)
		{
			$result->addError(new Error('Group not found'));

			return $result;
		}

		$this->entity = $group;

		$result->setGroup($this->entity);

		return $result;
	}
}