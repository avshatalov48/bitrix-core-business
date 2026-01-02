<?php

namespace Bitrix\Im\V2\Notification\Group\Condition;

class ModulesConditionMap
{
	/**
	 * @var array<string, ModuleConditions>
	 */
	private array $map = [];

	public function add(ModuleConditions $moduleConditions): static
	{
		$this->map[$moduleConditions->module] = $moduleConditions;

		return $this;
	}

	public function addByEventCondition(ModuleEventCondition $eventCondition): static
	{
		if (!isset($this->map[$eventCondition->module]))
		{
			$this->map[$eventCondition->module] = new ModuleConditions($eventCondition->module);
		}

		$moduleConditions = $this->map[$eventCondition->module];
		if ($eventCondition->event)
		{
			$moduleConditions->events[$eventCondition->event] = $eventCondition->event;
		}
		else
		{
			$moduleConditions->isWhole = true;
		}

		return $this;
	}

	/**
	 * @return array<string, ModuleConditions>
	 */
	public function getAll(): array
	{
		return $this->map;
	}
}