<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Command;

use Bitrix\Socialnetwork\Control\Mapper\Attribute\Field\Field;
use Bitrix\Socialnetwork\Control\Mapper\Attribute\MapOne;

/**
 * @method self setId(int $id)
 * @method self setName(string $name)
 * @method self setInitiatorId(int $initiatorId)
 */
class UpdateCommand extends AbstractCommand
{
	public int $id;

	#[MapOne(new Field('NAME'))]
	public ?string $name = null;

	public ?int $initiatorId = null;
}