<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control;

use Bitrix\Socialnetwork\Control\Command\AddCommand;
use Bitrix\Socialnetwork\Control\Command\AddCommandHandler;
use Bitrix\Socialnetwork\Control\Command\DeleteCommand;
use Bitrix\Socialnetwork\Control\Command\DeleteCommandHandler;
use Bitrix\Socialnetwork\Control\Command\UpdateCommand;
use Bitrix\Socialnetwork\Control\Command\UpdateCommandHandler;

class GroupService
{
	public function add(AddCommand $command): GroupResult
	{
		return (new AddCommandHandler($command))();
	}

	public function update(UpdateCommand $command): GroupResult
	{
		return (new UpdateCommandHandler($command))();
	}

	public function delete(DeleteCommand $command): GroupResult
	{
		return (new DeleteCommandHandler($command))();
	}
}