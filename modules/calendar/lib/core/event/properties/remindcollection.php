<?php

namespace Bitrix\Calendar\Core\Event\Properties;


use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Base\PropertyCollection;
use Bitrix\Calendar\Core\Event\Tools\PropertyException;

class RemindCollection extends PropertyCollection
{
	/**
	 * @var ?Date
	 */
	private ?Date $start = null;

	/**
	 * @var bool
	 */
	private bool $single = false;

	/**
	 * @param Date $start
	 * @return $this
	 */
	public function setEventStart(Date $start): RemindCollection
	{
		$this->start = $start;

		return $this;
	}

	/**
	 * @param string|null $dateFormat
	 * @return array
	 * @throws PropertyException
	 */
	public function getSpecificTimeCollection(string $dateFormat = null): array
	{
		if (!$this->start)
		{
			throw new PropertyException('You should set start event time. Use setEventStart.');
		}

		return array_map(function ($remind) use ($dateFormat) {
			/** @var Remind $remind */
			try
			{
				return $remind
					->setEventStart($this->start)
					->getSpecificTime()
					->format($dateFormat)
				;
			}
			catch (PropertyException $exception)
			{
				return '';
			}
		}, $this->collection);
	}

	/**
	 * @return array
	 */
	public function getFilterRemindBeforeEventStart(): array
	{
		return array_filter($this->collection, function ($remind) {
			try
			{
				/** @var Remind $remind */
				return $remind
					->setEventStart($this->start)
					->isBeforeEventStart()
				;
			}
			catch (PropertyException $exception)
			{
				return false;
			}
		});
	}

	/**
	 * @return $this
	 *
	 * @throws PropertyException
	 */
	public function deDuplicate(): self
	{
		if (!$this->start)
		{
			throw new PropertyException('You should set start event time. Use setEventStart.');
		}
		/** @var Remind[] $remindList */
		$remindList = [];

		/** @var Remind $remind */
		foreach ($this->collection as $remind)
		{
			$key = $remind
				->setEventStart($this->start)
				->getSpecificTime()
				->format('U');

			if (empty($remindList[$key]))
			{
				$remindList[$key] = $remind;
			}
			else
			{
				$remindList[$key] = $this->chooseRemindByRank($remind, $remindList[$key]);
			}
		}

		$this->collection = array_values($remindList);

		return $this;
	}

	/**
	 * @return $this
	 */
	public function sortFromStartEvent(): RemindCollection
	{
		usort($this->collection, function (Remind $remind1, Remind $remind2) {
			if (
				($remind1->getEventStart() === null)
				|| ($remind2->getEventStart() === null)
			)
			{
				$remind1->setEventStart($this->start);
				$remind2->setEventStart($this->start);
			}

			return $remind1->getTimeBeforeStartInMinutes() <=> $remind2->getTimeBeforeStartInMinutes();
		});

		return $this;
	}

	/**
	 * @param Remind $remind1
	 * @param Remind $remind2
	 *
	 * @return Remind
	 */
	private function chooseRemindByRank(Remind $remind1, Remind $remind2): Remind
	{
		return ($remind1->getRank() > $remind2->getRank())
			? $remind1
			: $remind2
		;
	}

	/**
	 * @param Remind[] $collection
	 *
	 * @return $this
	 */
	public function setCollection(array $collection): self
	{
		$this->collection = $collection;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isSingle(): bool
	{
		return $this->single;
	}

	/**
	 * @param bool $single
	 *
	 * @return RemindCollection
	 */
	public function setSingle(bool $single): self
	{
		$this->single = $single;

		return $this;
	}

	/**
	 * @return Date|null
	 */
	public function getEventStart(): ?Date
	{
		return $this->start;
	}
}
