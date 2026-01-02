<?php

namespace Bitrix\Main\ORM\Data\AddStrategy;

final class AddedMultiData
{
	public function __construct(
		/**
		 * Used only for perf optimization. IS NOT RELIABLE, can be different on different DBMS.
		 */
		public readonly bool $isDBChanged = true,
	)
	{
	}
}
