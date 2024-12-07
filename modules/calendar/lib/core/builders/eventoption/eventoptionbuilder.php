<?php

namespace Bitrix\Calendar\Core\Builders\EventOption;

use Bitrix\Calendar\Core\Builders\Builder;
use Bitrix\Calendar\Core\EventOption\EventOption;

abstract class EventOptionBuilder implements Builder
{
	public function build(): EventOption
	{
		return (new EventOption())
			->setId($this->getId())
			->setEventId($this->getEventId())
			->setCategoryId($this->getCategoryId())
			->setCategory($this->getCategory())
			->setThreadId($this->getThreadId())
			->setOptions($this->getOptions())
			->setAttendeesCount($this->getAttendeesCount())
			;
	}

	abstract protected function getId();
	abstract protected function getEventId();
	abstract protected function getCategoryId();
	abstract protected function getCategory();
	abstract protected function getThreadId();
	abstract protected function getOptions();
	abstract protected function getAttendeesCount();
}