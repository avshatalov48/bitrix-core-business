<?php

namespace Bitrix\Calendar\Core\Builders\EventCategory;

use Bitrix\Calendar\Core\Builders\Builder;
use Bitrix\Calendar\Core\EventCategory\EventCategory;

abstract class EventCategoryBuilder implements Builder
{
	public function build(): EventCategory
	{
		return (new EventCategory())
			->setId($this->getId())
			->setName($this->getName())
			->setCreatorId($this->getCreatorId())
			->setClosed($this->getClosed())
			->setDescription($this->getDescription())
			->setAccessCodes($this->getAccessCodes())
			->setDeleted($this->getDeleted())
			->setChannelId($this->getChannelId())
			->setEventsCount($this->getEventsCount())
			;
	}

	abstract protected function getId(): ?int;
	abstract protected function getName(): ?string;
	abstract protected function getCreatorId(): ?int;
	abstract protected function getClosed(): ?bool;
	abstract protected function getDescription(): ?string;
	abstract protected function getAccessCodes(): ?array;
	abstract protected function getDeleted(): ?bool;
	abstract protected function getChannelId(): ?int;
	abstract protected function getEventsCount(): ?int;
}
