<?php

namespace Bitrix\UI\AccessRights\V2\Options\UserGroup\Member;

use Bitrix\Main\Type\Contract\Arrayable;
use JsonSerializable;

class Access implements JsonSerializable, Arrayable
{
	public function __construct(
		protected string|int $id,
		protected string|int $value,
	)
	{
	}

	public function getId(): int|string
	{
		return $this->id;
	}

	public function setId(int|string $id): static
	{
		$this->id = $id;

		return $this;
	}

	public function getValue(): int|string
	{
		return $this->value;
	}

	public function setValue(int|string $value): static
	{
		$this->value = $value;

		return $this;
	}

	public function toArray(): array
	{
		return [
			'id' => $this->getId(),
			'value' => $this->getValue(),
		];
	}

	public function jsonSerialize(): array
	{
		return $this->toArray();
	}
}
