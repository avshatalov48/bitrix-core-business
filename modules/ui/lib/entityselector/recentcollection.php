<?

namespace Bitrix\UI\EntitySelector;

class RecentCollection implements \IteratorAggregate, \JsonSerializable
{
	private $items = [];
	private $itemsByEntity = [];

	public function __construct()
	{
	}

	public function add(RecentItem $recentItem)
	{
		if ($this->has($recentItem))
		{
			return;
		}

		if (!isset($this->itemsByEntity[$recentItem->getEntityId()]))
		{
			$this->itemsByEntity[$recentItem->getEntityId()] = [];
		}

		$this->itemsByEntity[$recentItem->getEntityId()][$recentItem->getId()] = $recentItem;
		$this->items[] = $recentItem;
	}

	public function get(string $entityId, $itemId): ?RecentItem
	{
		return $this->itemsByEntity[$entityId][$itemId] ?? null;
	}

	public function has(RecentItem $recentItem): bool
	{
		return isset($this->itemsByEntity[$recentItem->getEntityId()][$recentItem->getId()]);
	}

	public function getByItem(Item $item): ?RecentItem
	{
		return $this->itemsByEntity[$item->getEntityId()][$item->getId()] ?? null;
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

	public function jsonSerialize()
	{
		return array_values(array_filter($this->items, function(RecentItem $recentItem) {
			return $recentItem->isLoaded() && $recentItem->isAvailable();
		}));
	}

	public function getIterator(): \ArrayIterator
	{
		return new \ArrayIterator($this->items);
	}
}