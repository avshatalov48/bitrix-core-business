<?php

namespace Bitrix\Im\V2\Notification\Group\Condition;

class ModuleConditions
{
	/**
	 * @param string $module
	 * @param bool $isWhole
	 * @param array<string, string> $events
	 */
	public function __construct(
		public readonly string $module,
		public bool $isWhole = false,
		public array $events = [],
	) {}
}