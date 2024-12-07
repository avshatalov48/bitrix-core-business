<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Mapper\Attribute\Field;

class YesNoField implements FieldInterface
{
	public function __construct(
		private readonly string $toField,
		private readonly mixed $noValue,
	)
	{

	}

	public function getValue(mixed $value): ?string
	{
		if ($value === $this->noValue)
		{
			return 'N';
		}

		return 'Y';
	}

	public function getFieldName(): string
	{
		return $this->toField;
	}
}