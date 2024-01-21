<?php

namespace Bitrix\Socialnetwork\Space\List\Invitation;

use Bitrix\Main\Type\Contract\Arrayable;

final class Sender implements Arrayable
{
	public function __construct(private int $id, private string $name)
	{}

	public function getName(): string
	{
		return $this->name;
	}

	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'name' => $this->name,
		];
	}
}