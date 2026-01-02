<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Integration\Calendar\ClosestWorkDate\Trait;

use Bitrix\Main\Application;
use Bitrix\Main\Type\DateTime;
use Bitrix\Socialnetwork\Integration\Calendar\Exception\LoopException;

trait ClosestWorkDateTrait
{
	protected function getClosestWorkDate(DateTime $start): DateTime
	{
		$currentDate = clone $start;

		for ($counter = 0; $counter < 365; $counter++)
		{
			if ($this->schedule->isWeekend($currentDate))
			{
				$currentDate->add('1 day');

				continue;
			}

			$workDayStart = $this->schedule->getShiftStart($currentDate);
			$workDayEnd = $this->schedule->getShiftEnd($currentDate);

			if ($currentDate->getTimestamp() > $workDayEnd->getTimestamp())
			{
				$currentDate->setTime(0, 0);
				$currentDate->add('1 day');

				continue;
			}

			if ($currentDate->getTimeStamp() > $workDayStart->getTimeStamp())
			{
				return $currentDate;
			}

			return $workDayStart;
		}

		$exception = new LoopException('Probably infinite loop with date ' . $start->toString());
		Application::getInstance()->getExceptionHandler()->writeToLog($exception);

		return $currentDate;
	}
}
