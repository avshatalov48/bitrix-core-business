<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Mapper\Field;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Socialnetwork\Integration\HumanResources\AccessCodeConverter;

class MemberMapper implements ValueMapperInterface
{
	/**
	 * @throws LoaderException
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function getValue(mixed $value): ?array
	{
		if (!is_array($value))
		{
			return null;
		}

		return (new AccessCodeConverter(...$value))->getUsers()->getUserIds();
	}
}
