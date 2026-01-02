<?php

namespace Bitrix\Main\ORM\Data\AddStrategy;

final class AddedData
{
	public function __construct(
		public readonly int $id = 0,
		/**
		 * Used only for perf optimization. IS NOT RELIABLE, can be different on different DBMS.
		 */
		public readonly bool $isDBChanged = true,
	)
	{
	}
}
