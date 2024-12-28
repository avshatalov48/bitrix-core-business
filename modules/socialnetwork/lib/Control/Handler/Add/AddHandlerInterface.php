<?php

namespace Bitrix\Socialnetwork\Control\Handler\Add;

use Bitrix\Socialnetwork\Control\Command\AddCommand;
use Bitrix\Socialnetwork\Control\Handler\HandlerResult;
use Bitrix\Socialnetwork\Item\Workgroup;

interface AddHandlerInterface
{
	public function add(AddCommand $command, Workgroup $entity): HandlerResult;
}