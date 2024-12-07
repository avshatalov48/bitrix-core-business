<?php

namespace Bitrix\Sale\Label;

class Label
{
	public function __construct(private string $name, private string $value)
	{
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getValue(): string
	{
		return $this->value;
	}
}
