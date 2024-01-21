<?php

namespace Bitrix\Socialnetwork\Space\Toolbar\Switcher;

use ArrayIterator;
use IteratorAggregate;

class SwitcherCollection implements IteratorAggregate
{
	private array $items = [];

	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->items);
	}

	public function has(AbstractSwitcher $switcher): bool
	{
		return !empty(array_filter(
			$this->items,
			fn (AbstractSwitcher $item): bool =>
				$this->isEquals($item, $switcher)
		));
	}

	public function add(AbstractSwitcher $switcher): static
	{
		if ($this->has($switcher))
		{
			return $this;
		}

		$this->items[] = $switcher;
		return $this;
	}

	public function remove(AbstractSwitcher $switcher): static
	{
		if (!$this->has($switcher))
		{
			return $this;
		}

		foreach ($this->items as $key => $item)
		{
			if ($this->isEquals($item, $switcher))
			{
				unset($this->items[$key]);
			}
		}

		return $this;
	}

	private function isEquals(AbstractSwitcher $a, AbstractSwitcher $b): bool
	{
		return $a::class === $b::class
			&& $a->getUserId() === $b->getUserId()
			&& $a->getSpaceId() === $b->getSpaceId()
			&& $a->getCode() === $b->getCode();
	}
}