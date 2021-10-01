<?php

namespace Bitrix\Catalog\v2\Integration\JS\ProductForm;

use Bitrix\Catalog\v2\IoC\ServiceContainer;
use Bitrix\Catalog\v2\Product\BaseProduct;
use Bitrix\Catalog\v2\Sku\BaseSku;

class BasketBuilder implements \IteratorAggregate, \Countable
{
	/** @var BasketItem[] */
	private $items = [];

	public function __construct()
	{
	}

	public function add(BasketItem ...$items): self
	{
		foreach ($items as $item)
		{
			$this->setItem($item);
		}

		return $this;
	}

	public function createItem(): BasketItem
	{
		return new BasketItem();
	}

	public function setItem(BasketItem $item): self
	{
		$this->items[$item->getId()] = $item;

		return $this;
	}

	public function loadItemsBySkuIds(array $ids): self
	{
		foreach ($ids as $id)
		{
			$id = (int)$id;
			if ($id > 0)
			{
				$item = $this->loadItemBySkuId($id);
				if ($item)
				{
					$this->setItem($item);
					$item->setSort($this->count() * 100);
				}
			}
		}

		return $this;
	}

	public function loadItemBySkuId(int $id): ?BasketItem
	{
		$repositoryFacade = ServiceContainer::getRepositoryFacade();
		if ($repositoryFacade)
		{
			$variation = $repositoryFacade->loadVariation($id);
		}

		if ($variation === null)
		{
			return null;
		}

		$item = $this->createItem();

		$item->setSku($variation);

		return $item;
	}

	public function getItemById(string $uniqId): ?BasketItem
	{
		return $this->items[$uniqId] ?? null;
	}

	public function getItemBySkuId(int $id): ?BasketItem
	{
		foreach ($this->items as $item)
		{
			if ($item->getSkuId() === $id)
			{
				return $item;
			}
		}

		return null;
	}

	public function getIterator(): \ArrayIterator
	{
		return new \ArrayIterator(array_values($this->items));
	}

	public function clear(): self
	{
		$this->items = [];

		return $this;
	}

	public function count(): int
	{
		return count($this->getIterator());
	}

	public function getJsObject(): string
	{
		return \CUtil::PhpToJSObject($this->getFormattedItems());
	}

	public function getFormattedItems(): array
	{
		$result = [];
		foreach ($this->getIterator() as $item)
		{
			$result[] = $item->getResult();
		}

		return $result;
	}
}