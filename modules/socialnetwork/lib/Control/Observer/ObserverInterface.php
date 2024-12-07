<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Observer;

use Bitrix\Socialnetwork\Control\Command\AbstractCommand;
use Bitrix\Socialnetwork\Item\Workgroup;

interface ObserverInterface
{
	public function update(AbstractCommand $command, Workgroup $entity): void;
}