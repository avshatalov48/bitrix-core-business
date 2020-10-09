<?

namespace Bitrix\UI\EntitySelector;

class ItemCollection implements \IteratorAggregate, \JsonSerializable
{
	private $items = [];
	private $itemsByEntity = [];

	public function __construct()
	{
	}

	public function add(Item $item)
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

	public function getAll()
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

	public function toJsObject()
	{
		$items = array_map(function(Item $item) {
			return $item->jsonSerialize();
		}, $this->getAll());

		return \CUtil::phpToJSObject($items);
	}

	public function jsonSerialize()
	{
		return $this->items;
	}

	public function getIterator()
	{
		return new \ArrayIterator($this->items);
	}
}