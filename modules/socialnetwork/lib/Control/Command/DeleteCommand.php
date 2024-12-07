<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Command;

/**
 * @method self setId(int $id)
 * @method self setInitiatorId(int $initiatorId)
 */
class DeleteCommand extends AbstractCommand
{
	public int $id;

	public ?int $initiatorId = null;
}