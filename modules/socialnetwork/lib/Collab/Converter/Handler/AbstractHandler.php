<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Converter\Handler;

use Bitrix\Main\Result;
use Bitrix\Socialnetwork\Collab\Converter\Command\AbstractConverterCommand;

abstract class AbstractHandler
{
	abstract public function execute(AbstractConverterCommand $command): Result;
}
