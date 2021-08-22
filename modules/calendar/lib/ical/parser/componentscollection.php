<?php


namespace Bitrix\Calendar\ICal\Parser;


use ArrayIterator;
use IteratorAggregate;
use LogicException;

class ComponentsCollection implements IteratorAggregate
{
	/**
	 * @var ParserComponent[]
	 */
	private $collection;

	/**
	 * @param ParserComponent[]|null $collection
	 * @return ComponentsCollection
	 */
	public static function createInstance(array $collection = null): ComponentsCollection
	{
		return new self($collection);
	}

	/**
	 * ComponentsCollection constructor.
	 * @param ParserComponent[]|null $collection
	 */
	public function __construct(array $collection = null)
	{
		if (!$this->checkCollection($collection))
		{
			throw new LogicException('The collection contains elements of the wrong class. You need to pass a collection of objects Bitrix\\Calendar\\ICal\\Builder\\Attendee');
		}

		$this->collection = $collection ?? [];
	}

	/**
	 * @return ArrayIterator
	 */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->collection);
	}

	/**
	 * @param ParserComponent|null $component
	 * @return $this
	 */
	public function add(?ParserComponent $component): ComponentsCollection
	{
		if ($component === null)
		{
			return $this;
		}

		$this->collection[] = $component;

		return $this;
	}

	/**
	 * @return int
	 */
	public function count(): int
	{
		return count($this->collection);
	}

	/**
	 * @return bool
	 */
	public function hasOneComponent(): bool
	{
		return $this->count() === 1;
	}

	/**
	 * @return bool
	 */
	public function hasOneCalendarComponent(): bool
	{
		return $this->hasOneComponent() && ($this->collection[0] instanceof Calendar);
	}

	/**
	 * @param array|null collection
	 * @return bool
	 */
	private function checkCollection(?array $collection): bool
	{
		if ($collection === null)
		{
			return true;
		}

		$attendee = array_filter($collection, function ($attendee) {
			return !($attendee instanceof ParserComponent);
		});

		return empty($attendee);
	}
}