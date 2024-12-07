<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Decorator;

use Bitrix\Main\Error;
use Bitrix\Socialnetwork\Control\Command\AddCommand;
use Bitrix\Socialnetwork\Control\Command\DeleteCommand;
use Bitrix\Socialnetwork\Control\Command\UpdateCommand;
use Bitrix\Socialnetwork\Control\GroupResult;
use Bitrix\Socialnetwork\Helper\Workgroup\Access;

class AccessDecorator extends AbstractGroupServiceDecorator
{
	public function add(AddCommand $command): GroupResult
	{
		$result = new GroupResult();
		if (!Access::canCreate())
		{
			$result->addError(new Error('Access denied'));

			return $result;
		}

		return $this->source->add($command);
	}

	public function update(UpdateCommand $command): GroupResult
	{
		$result = new GroupResult();
		if (!Access::canUpdate(['groupId' => $command->id, 'userId' => $command->initiatorId]))
		{
			$result->addError(new Error('Access denied'));

			return $result;
		}

		return $this->source->update($command);
	}

	public function delete(DeleteCommand $command): GroupResult
	{
		$result = new GroupResult();

		if (!Access::canDelete(['groupId' => $command->id, 'userId' => $command->initiatorId]))
		{
			$result->addError(new Error('Access denied'));

			return $result;
		}

		return $this->source->delete($command);
	}
}