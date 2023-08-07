<?php

namespace Bitrix\MessageService\Restriction;

use Bitrix\MessageService\Internal\Entity\RestrictionTable;

trait Parameterizable
{
	abstract protected function getCurrentAdditionalParam(): string;
	protected function insertCounter(): void
	{
		RestrictionTable::insertCounterWithParam($this->getEntityId(), $this->getCurrentAdditionalParam());
	}

	protected function updateCounter(): bool
	{
		if (in_array($this->getCurrentAdditionalParam(), $this->additionalParams))
		{
			return true;
		}

		return RestrictionTable::updateCounterWithParam($this->getEntityId(), $this->limit, $this->getCurrentAdditionalParam());
	}
}