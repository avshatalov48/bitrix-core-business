<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Mapper\Field;

use Bitrix\Main\LoaderException;
use Bitrix\Socialnetwork\Integration\HumanResources\AccessCodeConverter;

class DepartmentMapper implements ValueMapperInterface
{
	/**
	 * @throws LoaderException
	 */
	public function getValue(mixed $value): ?array
	{
		if (!is_array($value))
		{
			return null;
		}

		$codes = (new AccessCodeConverter(...$value))->getDepartments()->getAccessCodeIdList();
		if (empty($codes))
		{
			return null;
		}

		return $codes;
	}
}
