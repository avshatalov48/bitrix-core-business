<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Mapper\Field;

use Bitrix\Socialnetwork\Control\Enum\ViewMode;

class ViewModeMapper implements ValueMapperInterface
{
	public function getValue(mixed $value): string
	{
		if (is_string($value))
		{
			$value = ViewMode::tryFrom($value);
		}

		if ($value === ViewMode::SECRET)
		{
			return 'N';
		}

		return 'Y';
	}
}