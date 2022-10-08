<?php

namespace Bitrix\Calendar\Core\Managers\Compare;

use Bitrix\Calendar\Core\Base\Property;
use Bitrix\Calendar\Core\Event\Properties\Remind;
use Bitrix\Calendar\Core\Event\Properties\RemindCollection;
use Bitrix\Calendar\Core\Event\Tools\PropertyException;

class RemindCompareManager implements CompareManager
{
	/**
	 * @var RemindCollection
	 */
	protected $currentCollection;
	/**
	 * @var RemindCollection
	 */
	protected $originalCollection;
	/**
	 * @var Property[]
	 */
	protected $diff;
	/**
	 * @var false
	 */
	protected $isEqual = true;

	/**
	 * @param RemindCollection $currentCollection
	 * @param RemindCollection $originalCollection
	 */
	public function __construct(RemindCollection $currentCollection, RemindCollection $originalCollection)
	{
		$this->currentCollection = $currentCollection;
		$this->originalCollection = $originalCollection;

		$this->compare();
	}

	/**
	 * @param RemindCollection $currentCollection
	 * @param RemindCollection $originalCollection
	 * @return RemindCompareManager
	 */
	public static function createInstance(
		RemindCollection $currentCollection,
		RemindCollection $originalCollection
	): RemindCompareManager
	{
		return new self($currentCollection, $originalCollection);
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
	 * @return Property[]
	 */
	public function getDiff(): array
	{
		return $this->diff;
	}

	/**
	 * @return bool
	 */
	public function hasDiff(): bool
	{
		return (bool)$this->diff;
	}

	/**
	 * @return RemindCollection
	 */
	public function getDiffCollection(): RemindCollection
	{
		return new RemindCollection($this->diff);
	}

	/**
	 * @return bool
	 */
	public function isEqual(): bool
	{
		return $this->isEqual;
	}

	/**
	 * @param Remind $current
	 * @param Remind $original
	 * @return int
	 * @throws PropertyException
	 */
	private function compareHandler(Remind $current, Remind $original): int
	{
		return $current->getSpecificTime()->getTimestamp() <=> $original->getSpecificTime()->getTimestamp();
	}
}
