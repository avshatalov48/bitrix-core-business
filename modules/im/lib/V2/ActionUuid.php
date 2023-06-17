<?php

namespace Bitrix\Im\V2;

use Bitrix\Im\Message\Uuid;

class ActionUuid
{
	private static ?self $instance = null;

	private ?string $value = null;

	private function __construct()
	{
	}

	public static function getInstance(): self
	{
		self::$instance ??= new static();

		return self::$instance;
	}

	public function getValue(): ?string
	{
		return $this->value;
	}

	public function setValue(string $value): self
	{
		if (Uuid::validate($value))
		{
			$this->value = $value;
		}

		return $this;
	}
}