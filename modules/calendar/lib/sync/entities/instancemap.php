<?php

namespace Bitrix\Calendar\Sync\Entities;

use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Base\Map;

class InstanceMap extends Map
{
	public const DATE_FORMAT_FOR_KEY = 'Ymd';

	/**
	 * @param Date $originalDate
	 * @return string
	 */
	public static function getKeyByDate(Date $originalDate): string
	{
		return $originalDate->format(self::DATE_FORMAT_FOR_KEY);
	}

	/**
	 * @param $item
	 * @param $key
	 * @return $this
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function add($item, $key = null): self
	{
		/** @var SyncEvent $item */
		if ($key === null)
		{
			if ($date = $item->getEvent()->getOriginalDateFrom())
			{
				$key = self::getKeyByDate($date);
			}
			else
			{
				$key = self::getKeyByDate($item->getEvent()->getStart());
			}
		}

		parent::add($item, $key);

		return $this;
	}

	/**
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function addItems(array $items): self
	{
		foreach ($items as $item)
		{
			$this->add($item);
		}

		return $this;
	}
}
