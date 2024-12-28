<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Mapper\Field;

class ValueMapper implements ValueMapperInterface
{
	public function getValue(mixed $value): mixed
	{
		return $value;
	}
}