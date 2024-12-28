<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Activity\Command;

use Bitrix\Main\Validation\Rule\AtLeastOnePropertyNotEmpty;
use Bitrix\Main\Validation\Rule\ElementsType;
use Bitrix\Main\Validation\Rule\Enum\Type;
use Bitrix\Main\Validation\Rule\PositiveNumber;
use Bitrix\Socialnetwork\Control\Command\AbstractCommand;

/**
 * @method self setCollabId(int $collabId)
 * @method null|int getCollabId()
 * @method self setUserIds(array $userIds)
 * @method null|array getUserIds()
 */

#[AtLeastOnePropertyNotEmpty(['collabId', 'userIds'])]
class DeleteLastActivityCommand extends AbstractCommand
{
	#[PositiveNumber]
	protected ?int $collabId;

	#[ElementsType(Type::Integer)]
	protected ?array $userIds;
}