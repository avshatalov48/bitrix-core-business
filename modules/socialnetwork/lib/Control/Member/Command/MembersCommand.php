<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Member\Command;

use Bitrix\Main\Validation\Rule\NotEmpty;
use Bitrix\Main\Validation\Rule\PositiveNumber;
use Bitrix\Socialnetwork\Control\Command\Attribute\AccessCode;
use Bitrix\Socialnetwork\Control\Command\InitiatedCommand;

/**
 * @method self setGroupId(int $groupId)
 * @method int getGroupId()
 * @method self setMembers(?array $members)
 * @method null|array getMembers()
 *
 * @method int getInitiatorId()
 * @method self setInitiatorId(int $initiatorId)
 * @method bool hasInitiatorId()
 */

class MembersCommand extends InitiatedCommand
{
	#[PositiveNumber]
	protected int $groupId;

	#[AccessCode]
	#[NotEmpty]
	protected ?array $members = null;
}