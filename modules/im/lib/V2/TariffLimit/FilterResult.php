<?php

namespace Bitrix\Im\V2\TariffLimit;

use Bitrix\Im\V2\Result;

/**
 * @template T
 * @extends Result<?T>
 */
class FilterResult extends Result
{
	private bool $wasFiltered = false;

	public function setFiltered(bool $flag): static
	{
		$this->wasFiltered = $flag;

		return $this;
	}

	public function wasFiltered(): bool
	{
		return $this->wasFiltered;
	}
}