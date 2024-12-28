<?php

namespace Bitrix\Socialnetwork\Control\Handler\Delete;

use Bitrix\Socialnetwork\Control\Command\DeleteCommand;
use Bitrix\Socialnetwork\Control\Handler\HandlerResult;
use Bitrix\Socialnetwork\Item\Workgroup;

interface DeleteHandlerInterface
{
	public function delete(DeleteCommand $command, Workgroup $entityBefore): HandlerResult;
}