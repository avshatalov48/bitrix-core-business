<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Option\Command;

use Bitrix\Main\Validation\Rule\PositiveNumber;
use Bitrix\Main\Validation\Rule\Recursive\Validatable;
use Bitrix\Socialnetwork\Collab\Control\Command\ValueObject\CollabOptions;
use Bitrix\Socialnetwork\Control\Command\AbstractCommand;

/**
 * @method self setCollabId(int $collabId)
 * @method int getCollabId()
 * @method self setOptions(CollabOptions $options)
 * @method CollabOptions getOptions()
 */
class SetOptionsCommand extends AbstractCommand
{
	#[PositiveNumber]
	protected int $collabId;

	#[Validatable]
	protected CollabOptions $options;
}