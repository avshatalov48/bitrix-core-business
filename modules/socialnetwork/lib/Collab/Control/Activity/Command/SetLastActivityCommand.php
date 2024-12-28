<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Activity\Command;

use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Validation\Rule\PositiveNumber;
use Bitrix\Socialnetwork\Control\Command\AbstractCommand;

/**
 * @method self setUserId(int $userId)
 * @method int getUserId()
 * @method self setCollabId(int $collabId)
 * @method int getCollabId()
 * @method self setDate(DateTime $date)
 * @method null|DateTime getDate()
 */
class SetLastActivityCommand extends AbstractCommand
{
	#[PositiveNumber]
	protected int $userId;

	#[PositiveNumber]
	protected int $collabId;

	protected ?DateTime $date = null;
}