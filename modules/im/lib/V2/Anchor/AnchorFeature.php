<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Anchor;

use Bitrix\Main\Config\Option;

class AnchorFeature
{
	public static function isOn(): bool
	{
		return Option::get('im', 'anchor_enabled', 'Y') === 'Y';
	}
}