<?

namespace Bitrix\UI\EntitySelector;

class ItemCollection implements \IteratorAggregate, \JsonSerializable
{
	private $items = [];
	private $itemsByEntity = [];

	public function __construct()
	{
	}

	public function add(Item $item): bool
	{
		if ($this->has($item))
		{
			return false;
		}

		if (!isset($this->itemsByEntity[$item->getEntityId()]))
		{
			$this->itemsByEntity[$item->getEntityId()] = [];
		}

		$this->itemsByEntity[$item->getEntityId()][$item->getId()] = $item;
		$this->items[] = $item;

		return true;
	}

	public function get(string $entityId, $itemId): ?Item
	{
		return $this->itemsByEntity[$entityId][$itemId] ?? null;
	}

	public function has(Item $item): bool
	{
		return isset($this->itemsByEntity[$item->getEntityId()][$item->getId()]);
	}

	public function getAll(): array
	{
		return $this->items;
	}

	public function count(): int
	{
		return count($this->items);
	}

	public function getEntityItems(string $entityId): array
	{
		return $this->itemsByEntity[$entityId] ?? [];
	}

	public function toJsObject(): string
	{
		$items = $this->toArray();

		return \CUtil::phpToJSObject($items, false, false, true);
	}

	public function toArray(): array
	{
		return array_map(function(Item $item) {
			return $item->toArray();
		}, $this->getAll());
	}

	public function jsonSerialize()
	{
		return $this->items;
	}

	public function getIterator(): \ArrayIterator
	{
		return new \ArrayIterator($this->items);
	}
}