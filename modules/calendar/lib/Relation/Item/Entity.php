<?php

namespace Bitrix\Calendar\Relation\Item;

use Bitrix\Main\Type\Contract\Arrayable;

class Entity implements Arrayable
{
	private ?string $link = null;

	public function __construct(private int $id, private string $type)
	{}

	public function getId(): int
	{
		return $this->id;
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function getLink(): ?string
	{
		return $this->link;
	}

	public function setLink (string $link): self
	{
		$this->link = $link;

		return $this;
	}

	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'type' => $this->type,
			'link' => $this->link,
		];
	}
}