<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Command;

use Bitrix\Main\Error;
use Bitrix\Socialnetwork\Control\Exception\GroupNotUpdatedException;
use Bitrix\Socialnetwork\Control\GroupResult;
use Bitrix\Socialnetwork\Item\Workgroup;
use CSocNetGroup;

class UpdateCommandHandler extends AbstractCommandHandler
{
	/** @var UpdateCommand  */
	protected AbstractCommand $command;

	protected array $fields = [];

	public function __invoke(): GroupResult
	{
		$result = new GroupResult();

		try
		{
			$this->update();

			$this->notify();
		}
		catch (GroupNotUpdatedException $e)
		{
			$result->addError(Error::createFromThrowable($e));

			return $result;
		}

		$result->setGroup($this->entity);

		return $result;
	}

	/**
	 * @throws GroupNotUpdatedException
	 */
	protected function update(): void
	{
		$fields = $this->mapper->toArray($this->command);
		if ($fields !== [])
		{
			CSocNetGroup::Update($this->command->id, $this->mapper->toArray($this->command));

			global $APPLICATION;
			if ($e = $APPLICATION->GetException())
			{
				throw new GroupNotUpdatedException($e->msg);
			}
		}

		$group = Workgroup::getById($this->command->id);
		if (false === $group)
		{
			throw new GroupNotUpdatedException('No such group');
		}

		$this->entity = $group;
	}
}