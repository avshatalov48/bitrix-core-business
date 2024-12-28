<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Provider\User;

use ArrayIterator;
use Bitrix\Main\Type\Contract\Arrayable;
use IteratorAggregate;

class UserCollection implements IteratorAggregate, Arrayable
{
	protected array $users = [];

	public function __construct(User ...$users)
	{
		$this->users = $users;
	}

	public function add(User $user): static
	{
		$this->users[] = $user;

		return $this;
	}

	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->users);
	}

	public function toArray(): array
	{
		$users = [];
		foreach ($this->users as $user)
		{
			$users[$user->id] = $user->toArray();
		}

		return $users;
	}
}