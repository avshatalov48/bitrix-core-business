<?php

namespace Bitrix\Calendar\Core\Managers\Compare;

interface CompareManager
{
	/**
	 * @return array
	 */
	public function getDiff(): array;
}
