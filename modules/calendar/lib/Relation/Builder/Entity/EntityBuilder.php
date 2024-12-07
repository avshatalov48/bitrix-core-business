<?php

namespace Bitrix\Calendar\Relation\Builder\Entity;

use Bitrix\Calendar\Relation\Item\Entity;

abstract class EntityBuilder
{
	abstract protected function getEntityId(): int;
	abstract protected function getEntityType(): string;
	abstract protected function getLink(): string;

	public function build(): Entity
	{
		$entity = new Entity($this->getEntityId(), $this->getEntityType());
		$entity->setLink($this->getLink());

		return $entity;
	}
}