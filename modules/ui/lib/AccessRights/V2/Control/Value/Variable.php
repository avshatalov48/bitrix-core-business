<?php

namespace Bitrix\UI\AccessRights\V2\Control\Value;

class Variable
{
	public function __construct(
		protected readonly string $title,
		protected readonly string $value,
	)
	{
	}

	public function title(): string
	{
		return $this->title;
	}

	public function value(): string
	{
		return $this->value;
	}
}
