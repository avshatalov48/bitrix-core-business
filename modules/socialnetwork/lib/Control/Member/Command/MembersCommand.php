<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Member\Command;

use Bitrix\Main\Validation\Rule\InArray;
use Bitrix\Main\Validation\Rule\NotEmpty;
use Bitrix\Main\Validation\Rule\PositiveNumber;
use Bitrix\Socialnetwork\Control\Command\Attribute\AccessCode;
use Bitrix\Socialnetwork\Control\Command\InitiatedCommand;
use Bitrix\Socialnetwork\UserToGroupTable;

/**
 * @method self setGroupId(int $groupId)
 * @method int getGroupId()
 * @method self setMembers(?array $members)
 * @method null|array getMembers()
 * @method self setInitiatedByType(string $type)
 * @method string InitiatedByType()
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

	#[InArray([null, UserToGroupTable::INITIATED_BY_GROUP, UserToGroupTable::INITIATED_BY_USER, UserToGroupTable::INITIATED_BY_STRUCTURE])]
	protected ?string $initiatedByType = null;
}
