<?php

namespace Bitrix\Calendar\Relation\Item;

use Bitrix\Main\Type\Contract\Arrayable;

class Owner implements Arrayable
{
	private ?string $avatar = null;
	private ?string $name = null;
	private ?string $link = null;

	public function __construct(private int $id)
	{}

	public function getId(): int
	{
		return $this->id;
	}

	public function setAvatar(?string $avatar): self
	{
		$this->avatar = $avatar;

		return $this;
	}

	public function setName(?string $name): self
	{
		$this->name = $name;

		return $this;
	}

	public function setLink(?string $link): self
	{
		$this->link = $link;

		return $this;
	}

	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'avatar' => $this->avatar,
			'name' => $this->name,
			'link' => $this->link,
		];
	}
}