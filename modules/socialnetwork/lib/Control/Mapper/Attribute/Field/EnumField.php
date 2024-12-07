<?php

namespace Bitrix\Socialnetwork\Control\Mapper\Attribute\Field;

use BackedEnum;

class EnumField implements FieldInterface
{
	public function __construct(
		private readonly string $toField,
	)
	{

	}

	public function getValue(mixed $value): mixed
	{
		if ($value instanceof BackedEnum)
		{
			return $value->value;
		}

		return null;
	}

	public function getFieldName(): string
	{
		return $this->toField;
	}
}
