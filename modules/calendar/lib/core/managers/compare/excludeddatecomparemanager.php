<?php

namespace Bitrix\Calendar\Core\Managers\Compare;

use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Base\Property;
use Bitrix\Calendar\Core\Event\Properties\ExcludedDatesCollection;

class ExcludedDateCompareManager implements CompareManager
{
	/**
	 * @var ExcludedDatesCollection
	 */
	protected $originalCollection;
	/**
	 * @var ExcludedDatesCollection
	 */
	protected $currentCollection;
	/**
	 * @var bool
	 */
	protected $isEqual = true;
	/**
	 * @var array|Property[]
	 */
	protected $diff = [];

	/**
	 * @param ExcludedDatesCollection|null $originalCollection
	 * @param ExcludedDatesCollection|null $currentCollection
	 * @return ExcludedDateCompareManager
	 */
	public static function createInstance(?ExcludedDatesCollection $originalCollection, ?ExcludedDatesCollection $currentCollection): ExcludedDateCompareManager
	{
		return new self($originalCollection, $currentCollection);
	}

	/**
	 * @param ExcludedDatesCollection|null $originalCollection
	 * @param ExcludedDatesCollection|null $currentCollection
	 */
	public function __construct(?ExcludedDatesCollection $originalCollection, ?ExcludedDatesCollection $currentCollection)
	{
		$this->originalCollection = $originalCollection ?? new ExcludedDatesCollection([]);
		$this->currentCollection = $currentCollection ?? new ExcludedDatesCollection([]);

		$this->compare();
	}

	/**
	 * @return void
	 */
	protected function compare(): void
	{
		$this->diff = array_udiff(
			$this->currentCollection->getCollection(),
			$this->originalCollection->getCollection(),
			[$this, 'compareHandler']
		);

		if (
			$this->diff
			|| $this->originalCollection->count() !== $this->currentCollection->count()
		)
		{
			$this->isEqual = false;
		}
	}

	/**
	 * @return ExcludedDatesCollection
	 */
	public function getDiffCollection(): ExcludedDatesCollection
	{
		return new ExcludedDatesCollection($this->diff);
	}

	/**
	 * @return bool
	 */
	public function isEqual(): bool
	{
		return $this->isEqual;
	}

	/**
	 * @return bool
	 */
	public function hasDiff(): bool
	{
		return (bool)$this->diff;
	}

	/**
	 * @param Date $current
	 * @param Date $original
	 * @return int
	 */
	private function compareHandler(Date $current, Date $original): int
	{
		return $current->getTimestamp() <=> $original->getTimestamp();
	}

	/**
	 * @return array|Property[]
	 */
	public function getDiff(): array
	{
		return $this->diff;
	}
}
