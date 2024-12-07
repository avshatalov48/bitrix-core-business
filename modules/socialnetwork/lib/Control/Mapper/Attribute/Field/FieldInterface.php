<?php

namespace Bitrix\Socialnetwork\Control\Mapper\Attribute\Field;

interface FieldInterface
{
	public function getValue(mixed $value): mixed;

	public function getFieldName(): string;
}