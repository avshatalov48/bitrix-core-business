<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Invite\Command;

use Bitrix\Main\Validation\Rule\PositiveNumber;
use Bitrix\Socialnetwork\Control\Command\InitiatedCommand;

/**
 * @method int getCollabId()
 * @method self setCollabId(int $collabId)
 * @method int getRecipientId()
 * @method self setRecipientId(int $recipientId)
 * @method int getRelationId()
 * @method self setRelationId(int $relationId)
 * @method bool hasRelationId()
 * @method int getInitiatorId()
 * @method self setInitiatorId(int $initiatorId)
 * @method bool hasInitiatorId()
 */
class InvitationCommand extends InitiatedCommand
{
	#[PositiveNumber]
	protected int $collabId;

	#[PositiveNumber]
	protected int $recipientId;

	#[PositiveNumber]
	protected ?int $relationId;
}