<?php


namespace Bitrix\Calendar\ICal\Builder;


use Bitrix\Calendar\SerializeObject;
use ArrayIterator;
use IteratorAggregate;
use Serializable;

/**
 * Class AttendeesCollection
 * @package Bitrix\Calendar\ICal\Builder
 */
class AttendeesCollection implements IteratorAggregate, Serializable
{
	use SerializeObject;
	/**
	 * @var array
	 */
	private array $collection;

	/**
	 * @param Attendee[]|null $collection
	 * @return AttendeesCollection
	 */
	public static function createInstance(array $collection = []): AttendeesCollection
	{
		return new self($collection);
	}


	/**
	 * AttendeesCollection constructor.
	 * @param array|null $collection
	 */
	public function __construct(array $collection = [])
	{
		if (!$this->checkCollection($collection))
		{
			throw new \InvalidArgumentException('The collection contains elements of the wrong class. You need to pass a collection of objects Bitrix\\Calendar\\ICal\\Builder\\Attendee');
		}

		$this->collection = $collection;
	}

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		if (empty($this->collection))
		{
			return '';
		}

		$result = [];
		foreach ($this->collection as $attendee)
		{
			if (!empty($attendee->getFullName()))
			{
				$result[] = $attendee->getFullName();
			}
		}

		return implode(', ', $result);
	}

	/**
	 * @param Attendee $attendee
	 * @return $this
	 */
	public function add(Attendee $attendee): AttendeesCollection
	{
		$this->collection[] = $attendee;

		return $this;
	}

	/**
	 * @return ArrayIterator
	 */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->collection);
	}

	/**
	 * @return int
	 */
	public function getCount(): int
	{
		return count($this->collection);
	}

	/**
	 * @param array|null $collection collection
	 * @return bool
	 */
	private function checkCollection(?array $collection): bool
	{
		if (is_null($collection))
		{
			return true;
		}

		$attendee = array_filter($collection, static function ($attendee) {
			return !($attendee instanceof Attendee);
		});

		return empty($attendee);
	}
}