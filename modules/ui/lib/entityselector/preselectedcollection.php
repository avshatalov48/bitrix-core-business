<?

namespace Bitrix\UI\EntitySelector;

class PreselectedCollection implements \IteratorAggregate, \JsonSerializable
{
	private $items = [];
	private $itemsByEntity = [];

	public function __construct()
	{
	}

	public function add(PreselectedItem $preselectedItem)
	{
		if ($this->has($preselectedItem))
		{
			return;
		}

		if (!isset($this->itemsByEntity[$preselectedItem->getEntityId()]))
		{
			$this->itemsByEntity[$preselectedItem->getEntityId()] = [];
		}

		$this->itemsByEntity[$preselectedItem->getEntityId()][$preselectedItem->getId()] = $preselectedItem;
		$this->items[] = $preselectedItem;
	}

	public function load(array $ids)
	{
		foreach ($ids as $itemId)
		{
			if (!is_array($itemId) || count($itemId) !== 2)
			{
				continue;
			}

			[$entityId, $id] = $itemId;
			if (is_string($entityId) && (is_string($id) || is_int($id)))
			{
				if ($this->get($entityId, $id) === null)
				{
					$this->add(new PreselectedItem(['entityId' => $entityId, 'id' => $id]));
				}
			}
		}
	}

	public function get(string $entityId, $itemId): ?PreselectedItem
	{
		return $this->itemsByEntity[$entityId][$itemId] ?? null;
	}

	public function has(PreselectedItem $preselectedItem): bool
	{
		return isset($this->itemsByEntity[$preselectedItem->getEntityId()][$preselectedItem->getId()]);
	}

	public function getByItem(Item $item): ?PreselectedItem
	{
		return $this->itemsByEntity[$item->getEntityId()][$item->getId()] ?? null;
	}

	public function getAll()
	{
		return $this->items;
	}

	public function getItems()
	{
		return $this->itemsByEntity;
	}

	public function count(): int
	{
		return count($this->items);
	}

	/**
	 * @param string $entityId
	 *
	 * @return PreselectedItem[]
	 *
	 */
	public function getEntityItems(string $entityId): array
	{
		return $this->itemsByEntity[$entityId] ?? [];
	}

	/**
	 * @return string[]
	 */
	public function getEntities()
	{
		return array_keys($this->itemsByEntity);
	}

	public function jsonSerialize()
	{
		return $this->getAll();
	}

	public function getIterator()
	{
		return new \ArrayIterator($this->items);
	}
}