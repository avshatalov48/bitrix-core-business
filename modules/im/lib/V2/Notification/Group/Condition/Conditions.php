<?php

namespace Bitrix\Im\V2\Notification\Group\Condition;

class Conditions
{
	private bool $onlyConfirms = false;

	private ModulesConditionMap $modulesConditionMap;

	public function __construct()
	{
		$this->modulesConditionMap = new ModulesConditionMap();
	}

	public function setOnlyConfirms(bool $value): static
	{
		$this->onlyConfirms = $value;

		return $this;
	}

	public function getOnlyConfirms(): bool
	{
		return $this->onlyConfirms;
	}

	public function toFilterFormat(bool $withConfirm = true): array
	{
		$filter = [];

		if ($withConfirm && $this->onlyConfirms)
		{
			$filter['=NOTIFY_TYPE'] = IM_NOTIFY_CONFIRM;
		}

		$modulesFilter = $this->getModulesFilter();
		if ($modulesFilter)
		{
			$filter[] = $modulesFilter;
		}

		return $filter;
	}

	public function appendModuleEvent(ModuleEventCondition $moduleEventCondition): static
	{
		$this->modulesConditionMap->addByEventCondition($moduleEventCondition);

		return $this;
	}

	private function getModulesFilter(): array
	{
		if (empty($this->modulesConditionMap->getAll()))
		{
			return [];
		}

		$modulesFilter = [
			'LOGIC' => 'OR',
		];
		foreach ($this->modulesConditionMap->getAll() as $moduleConditions)
		{
			$modulesFilter[] = $this->getModuleFilter($moduleConditions);
		}

		return $modulesFilter;
	}

	private function getModuleFilter(ModuleConditions $conditions): array
	{
		$filter = [
			'LOGIC' => 'AND',
		];

		if ($conditions->module)
		{
			$filter['=NOTIFY_MODULE'] = $conditions->module;
		}

		if (!$conditions->isWhole)
		{
			$filter['@NOTIFY_EVENT'] = $conditions->events;
		}

		return $filter;
	}

	public function toRestFormat(): array
	{
		$view = [];
		if ($this->getOnlyConfirms())
		{
			$view['notifyType'] = IM_NOTIFY_CONFIRM;
		}

		foreach ($this->modulesConditionMap->getAll() as $module => $moduleConditions)
		{
			$key = $module ?: 'all';
			$view['moduleConditions'][$key] = [
				'isWhole' => $moduleConditions->isWhole,
				'events' => array_values($moduleConditions->events),
			];
		}

		return $view;
	}
}