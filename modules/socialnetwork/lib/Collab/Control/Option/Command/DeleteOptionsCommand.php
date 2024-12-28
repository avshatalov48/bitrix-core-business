<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Option\Command;

use Bitrix\Main\Validation\Rule\PositiveNumber;
use Bitrix\Socialnetwork\Control\Command\AbstractCommand;

/**
 * @method self setCollabId(int $collabId)
 * @method int getCollabId()
 */
class DeleteOptionsCommand extends AbstractCommand
{
	#[PositiveNumber]
	protected int $collabId;
}