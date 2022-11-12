<?php

namespace Bitrix\Main\Access\Rule;

use Bitrix\Main\Access\AccessibleController;

interface RuleFactory
{
	public function createFromAction(string $actionName, AccessibleController $controller): ?RuleInterface;
}
