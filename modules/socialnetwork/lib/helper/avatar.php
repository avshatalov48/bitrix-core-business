<?php

namespace Bitrix\Socialnetwork\Helper;

use Bitrix\Main\Type\Contract\Arrayable;

final class Avatar implements Arrayable
{
	public function __construct(private string $type, private string $id = '')
	{}

	public function getType(): string
	{
		return $this->type;
	}

	public function setType(string $type): self
	{
		$this->type = $type;

		return $this;
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function setId(string $id): self
	{
		$this->id = $id;

		return $this;
	}

	public function toArray(): array
	{
		return [
			'type' => $this->type,
			'id' => $this->id,
		];
	}
}
