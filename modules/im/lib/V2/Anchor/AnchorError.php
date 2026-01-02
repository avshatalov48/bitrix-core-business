<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Anchor;

use Bitrix\Im\V2\Error;

class AnchorError extends Error
{
	public const UNEXPECTED = 'UNEXPECTED';
	public const UNCOMMON_MESSAGE = 'UNCOMMON_MESSAGE';
}