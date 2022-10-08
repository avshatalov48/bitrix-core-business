<?php

namespace Bitrix\Calendar\Core\Event\Properties;

use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Base\PropertyCollection;


class ExcludedDatesCollection extends PropertyCollection
{
	public const EXCLUDED_DATE_FORMAT = 'd.m.Y';

	/**
	 * @param string $separator
	 * @return string
	 */
	public function toString(string $separator = ';'): string
	{
		return implode(
			$separator,
			array_unique(
				array_map(
					fn (Date $date) => $date->format(\CCalendar::DFormat(false)),
					$this->collection)
			));
	}

	/**
	 * @param string $interval
	 * @return $this
	 */
	public function getDateCollectionNewerThanInterval(string $interval = '1 month'): ExcludedDatesCollection
	{
		$timestamp = (new Date())->sub($interval)->getTimestamp();

		$exdateCollection = new static();

		/** @var Date $item */
		foreach ($this->collection as $item)
		{
			if ($item->getTimestamp() > $timestamp)
			{
				$exdateCollection->add($item);
			}
		}

		return $exdateCollection;
	}

	/**
	 * @param Date $date
	 * @return void
	 */
	public function removeDateFromCollection(Date $date): void
	{
		/**
		 	* @var $key
			* @var Date $item
		 */
		foreach ($this->collection as $key => $item)
		{
			if ($item->format('d.m.Y') === $date->format('d.m.Y'))
			{
				unset($this->collection[$key]);
			}
		}
	}
}
