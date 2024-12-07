<?php

namespace Bitrix\Calendar\Relation\Strategy;

use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Relation\Item\Relation;
use Bitrix\Calendar\Relation\Exception\RelationException;

abstract class RelationStrategy
{
	public function __construct(protected int $userId, protected Event $event)
	{}

	/**
	 * @throws RelationException
	 */
	abstract public function getRelation(): Relation;
}