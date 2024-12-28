<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Handler\Add;

use Bitrix\Socialnetwork\Collab\Control\Handler\Trait\SetOptionsTrait;
use Bitrix\Socialnetwork\Control\Command\AddCommand;
use Bitrix\Socialnetwork\Control\Handler\Add\AddHandlerInterface;
use Bitrix\Socialnetwork\Control\Handler\HandlerResult;
use Bitrix\Socialnetwork\Item\Workgroup;

class AddOptionsHandler implements AddHandlerInterface
{
	use SetOptionsTrait;

	public function add(AddCommand $command, Workgroup $entity): HandlerResult
	{
		return $this->setOptions($command, $entity);
	}
}
