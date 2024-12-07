<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Command;

use Bitrix\Main\Error;
use Bitrix\Socialnetwork\Control\Exception\GroupNotDeletedException;
use Bitrix\Socialnetwork\Control\GroupResult;
use CSocNetGroup;

class DeleteCommandHandler extends AbstractCommandHandler
{
	/** @var DeleteCommand  */
	protected AbstractCommand $command;

	public function __invoke(): GroupResult
	{
		$result = new GroupResult();

		try
		{
			$this->delete();

			$this->notify();
		}
		catch (GroupNotDeletedException $e)
		{
			$result->addError(Error::createFromThrowable($e));
		}

		$result->setGroupId($this->command->id);

		return $result;
	}

	/**
	 * @throws GroupNotDeletedException
	 */
	protected function delete(): void
	{
		$result = CSocNetGroup::Delete($this->command->id);

		if ($result === false)
		{
			global $APPLICATION;
			if ($e = $APPLICATION->GetException())
			{
				throw new GroupNotDeletedException($e->msg);
			}
		}
	}
}