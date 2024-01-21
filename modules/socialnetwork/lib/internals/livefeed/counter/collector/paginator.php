<?php

namespace Bitrix\Socialnetwork\Internals\LiveFeed\Counter\Collector;

use Traversable;

class Paginator implements \IteratorAggregate
{
	private int $limit;
	private int $pages;
	private array $offsets = [];

	public function __construct(int $total, int $limit)
	{
		$this->limit = $limit;
		$this->pages = ceil($total / $limit);
		$this->paginate();
	}

	/**
	 * @return Traversable <int, object>
	 */
	public function getIterator(): Traversable
	{
		return new \ArrayIterator($this->offsets);
	}

	private function paginate(): void
	{
		for ($page = 1; $page <= $this->pages; $page++)
		{
			$this->offsets[] = ($page - 1) * $this->limit;
		}
	}
}