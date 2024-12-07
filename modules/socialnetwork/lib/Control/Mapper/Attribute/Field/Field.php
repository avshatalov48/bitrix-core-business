<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Mapper\Attribute\Field;

class Field implements FieldInterface
{
	public function __construct(
		private readonly string $toField,
	)
	{

	}

	public function getValue(mixed $value): mixed
	{
		return $value;
	}

	public function getFieldName(): string
	{
		return $this->toField;
	}
}