<?php


namespace Bitrix\Calendar\ICal\Builder;


use Bitrix\Calendar\SerializeObject;
use ArrayIterator;
use IteratorAggregate;
use LogicException;
use Serializable;

class AttachCollection implements IteratorAggregate, Serializable
{
	use SerializeObject;
	/**
	 * @var array
	 */
	private $collection;

	/**
	 * @param Attach[] $collection
	 * @return AttachCollection
	 */
	public static function createInstance(array $collection = []): AttachCollection
	{
		return new self($collection);
	}

	/**
	 * AttachCollection constructor.
	 * @param Attach[] $collection
	 */
	public function __construct(array $collection = [])
	{
		if (!$this->checkCollection($collection))
		{
			throw new LogicException('The collection contains elements of the wrong class. You need to pass a collection of objects Bitrix\\Calendar\\ICal\\Parser\\Attendee');
		}
		$this->collection = $collection;
	}

	/**
	 * @param Attach $attach
	 * @return $this
	 */
	public function add(Attach $attach): AttachCollection
	{
		$this->collection[] = $attach;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getCollection(): array
	{
		return $this->collection;
	}

	/**
	 * @return ArrayIterator
	 */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->collection);
	}

	public function getCount(): int
	{
		return count($this->collection);
	}
	/**
	 * @param array $collection
	 * @return bool
	 */
	private function checkCollection(array $collection): bool
	{
		if (is_null($collection))
		{
			return true;
		}

		$attach = array_filter($collection, function ($attach) {
			return !($attach instanceof Attach);
		});

		return empty($attach);
	}
}