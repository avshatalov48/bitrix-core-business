<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Operation;

use Bitrix\Main\Error;
use Bitrix\Socialnetwork\Control\Command\AddCommand;
use Bitrix\Socialnetwork\Control\Exception\GroupNotAddedException;
use Bitrix\Socialnetwork\Control\GroupResult;
use Bitrix\Socialnetwork\Item\Workgroup;
use CSocNetGroup;

class AddOperation extends AbstractOperation
{
	protected AddCommand $command;
	protected Workgroup $entity;

	public function __construct(AddCommand $command)
	{
		$this->command = $command;
	}

	public function run(): GroupResult
	{
		$result = new GroupResult();

		$fields = $this->getMapper()->toArray($this->command);

		$id = (int)CSocNetGroup::createGroup($this->command->getOwnerId(), $fields);

		if ($id === 0)
		{
			$result->addApplicationError();

			return $result;
		}

		$group = $this->getRegistry()->get($id);
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