<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Permission;

use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Main\Type\Contract\Arrayable;

interface AccessModelInterface extends AccessibleItem
{
	public static function createFromArray(array|Arrayable $data): static;
}