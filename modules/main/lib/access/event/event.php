<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\Access\Event;

class Event extends \Bitrix\Main\Event
{
	public function isAccess(): ?bool
	{
		$isAccess = null;
		foreach ($this->getResults() as $eventResult)
		{
			/* @var EventResult $eventResult */
			if (!is_a($eventResult, EventResult::class))
			{
				continue;
			}
			if ($eventResult->getType() !== EventResult::SUCCESS)
			{
				continue;
			}
			if ($eventResult->isAccess() !== null)
			{
				$isAccess = ($isAccess === null)
					? $eventResult->isAccess()
					: ($isAccess && $eventResult->isAccess());
			}
		}
		return $isAccess;
	}
}