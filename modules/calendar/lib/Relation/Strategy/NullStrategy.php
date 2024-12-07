<?php

namespace Bitrix\Calendar\Relation\Strategy;

use Bitrix\Calendar\Relation\Item\Relation;
use Bitrix\Calendar\Relation\Exception\RelationException;

class NullStrategy extends RelationStrategy
{

	/**
	 * @inheritdoc
	 */
	public function getRelation(): Relation
	{
		throw new RelationException('Unknown type of event relation');
	}
}