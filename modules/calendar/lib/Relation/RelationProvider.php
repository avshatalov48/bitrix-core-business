<?php

namespace Bitrix\Calendar\Relation;

use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Mappers;
use Bitrix\Calendar\Relation\Exception\RelationException;
use Bitrix\Calendar\Relation\Result\RelationResult;
use Bitrix\Calendar\Relation\Strategy;
use Bitrix\Main\Error;

class RelationProvider
{
	public function getEventRelation(int $userId, int $eventId): RelationResult
	{
		$result = new RelationResult();

		/** @var Event $event */
		$event = (new Mappers\Event())->getById($eventId);
		if (!$event)
		{
			$result->addError(new Error('Event not found'));

			return $result;
		}

		$strategy = Strategy\Factory::getInstance()->getStrategy($userId, $event);

		try
		{
			$result->setRelation($strategy->getRelation());
		}
		catch (RelationException $exception)
		{
			$result->addError(Error::createFromThrowable($exception));
		}

		return $result;
	}
}