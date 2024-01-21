<?php

namespace Bitrix\Socialnetwork\Space\List\Invitation;

use Bitrix\Main\Type\Contract\Arrayable;

final class InvitationCollection implements Arrayable
{
	/** @var array<Invitation> $items */
	private array $items = [];

	public function add(Invitation $invitation): void
	{
		$this->items[] = $invitation;
	}

	public function toArray(): array
	{
		return $this->items;
	}
}