<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Mapper\Field;

use Bitrix\Socialnetwork\Collab\Control\Option\Type\WhoCanInviteOption;
use Bitrix\Socialnetwork\Control\Mapper\Field\ValueMapperInterface;

class InitiatePermissionMapper implements ValueMapperInterface
{
	public function getValue(mixed $value): mixed
	{
		if ($value instanceof WhoCanInviteOption)
		{
			return $value->getValue();
		}

		if (!is_array($value))
		{
			return WhoCanInviteOption::DEFAULT_VALUE;
		}

		foreach ($value as $option)
		{
			if ($option instanceof WhoCanInviteOption)
			{
				return $option->getValue();
			}
		}

		return WhoCanInviteOption::DEFAULT_VALUE;
	}
}