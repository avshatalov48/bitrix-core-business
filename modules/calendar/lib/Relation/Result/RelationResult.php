<?php

namespace Bitrix\Calendar\Relation\Result;

use Bitrix\Calendar\Relation\Item\Relation;
use Bitrix\Main\Result;

class RelationResult extends Result
{
	public function setRelation(Relation $relation): self
	{
		$this->setData(['relation' => $relation]);

		return $this;
	}

	public function getRelation(): ?Relation
	{
		return $this->getData()['relation'] ?? null;
	}
}