<?php

namespace Bitrix\Socialnetwork\Space\Toolbar\Composition;

use ArrayIterator;
use Bitrix\Main\Type\Contract\Arrayable;
use IteratorAggregate;

class CompositionItemCollection implements IteratorAggregate, Arrayable
{
	/** @var AbstractCompositionItem[]  */
	private array $items = [];

	public static function createFromModuleIds(array $moduleIds): static
	{
		$collection = new static();
		array_map(static function (string $moduleId) use ($collection): void {
			$item = AbstractCompositionItem::createFromModuleId($moduleId);
			!is_null($item) && $collection->addItem($item);
		}, $moduleIds);

		return $collection;
	}

	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->items);
	}

	public function addItem(AbstractCompositionItem $item): static
	{
		if (!$this->has($item))
		{
			$this->items[] = $item;
		}

		return $this;
	}

	public function remove(string $moduleId): static
	{
		foreach ($this->items as $key => $item)
		{
			if ($item->getModuleId() === $moduleId)
			{
				unset($this->items[$key]);
			}
		}

		return $this;
	}

	public function has(AbstractCompositionItem $item): bool
	{
		return in_array(
			$item->getModuleId(),
			array_map(fn (AbstractCompositionItem $compositionItem): string => $compositionItem->getModuleId(),
				$this->items),
			true
		);
	}

	public function fillBoundItems(): static
	{
		foreach ($this->items as $item)
		{
			$item->hasBoundItem() && $this->addItem($item->getBoundItem());
		}

		return $this;
	}

	public function hideItems(): static
	{
		foreach ($this->items as $item)
		{
			$item->isHidden() && $this->remove($item->getModuleId());
		}

		return $this;
	}

	public function toArray(): array
	{
		$moduleIds = [];
		foreach ($this->items as $item)
		{
			$moduleIds[] = $item->getModuleId();
		}

		return $moduleIds;
	}
}