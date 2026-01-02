<?php

namespace Bitrix\Im\V2\Notification\Group\Condition;

class ModuleEventCondition
{
	public function __construct(
		public readonly string $module,
		public readonly string $event = '',
	) {}
}