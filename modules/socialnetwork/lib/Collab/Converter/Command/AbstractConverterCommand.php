<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Converter\Command;

use Bitrix\Main\Result;
use Bitrix\Socialnetwork\Collab\Converter\Handler\AbstractHandler;
use Bitrix\Socialnetwork\Control\Command\InitiatedCommand;
use Bitrix\Socialnetwork\Item\Workgroup;

/**
 * @method Workgroup getGroup()
 * @method self setGroup(Workgroup $group)
 */
abstract class AbstractConverterCommand extends InitiatedCommand
{
	protected Workgroup $group;
	/** @return array<AbstractHandler> */
	abstract public function getHandlers(): array;

	public function validateGroup(): Result
	{
		return new Result();
	}
}
