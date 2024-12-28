<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Command;

use Bitrix\Main\Validation\Rule\PositiveNumber;
use Bitrix\Socialnetwork\Permission\GroupAccessController;
use Bitrix\Socialnetwork\Control\Command\Attribute\AccessController;

/**
 * @method self setId(int $id)
 * @method int getId()
 */

#[AccessController(GroupAccessController::class)]
class DeleteCommand extends InitiatedCommand
{
	#[PositiveNumber]
	protected int $id;
}