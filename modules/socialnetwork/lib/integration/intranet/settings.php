<?php

namespace Bitrix\Socialnetwork\Integration\Intranet;

use Bitrix\Intranet\Settings\Tools\ToolsManager;
use Bitrix\Main\Loader;

final class Settings
{
	public const LIMIT_CODES = [
		'workgroups' => 'limit_groups_off',
		'projects' => 'limit_projects_off',
		'scrum' => 'limit_tasks_scrum_off',
	];

	public const TASKS_TOOLS = [
		'base_tasks' => 'base_tasks',
		'projects' => 'projects',
		'scrum' => 'scrum',
		'departments' => 'departments',
		'effective' => 'effective',
		'employee_plan' => 'employee_plan',
		'report' => 'report',
	];

	public const SONET_TOOLS = [
		'news' => 'news',
		'workgroups' => 'workgroups',
	];

	public const CALENDAR_TOOLS = [
		'calendar' => 'calendar',
	];

	private function isAvailable(): bool
	{
		return Loader::includeModule('intranet') && class_exists(ToolsManager::class);
	}

	public function isGroupAvailableByType(bool $isProject, bool $isScrum): bool
	{
		return $this->isToolAvailable($this->getToolIdByType($isProject, $isScrum));
	}

	private function getToolIdByType(bool $isProject, bool $isScrum): string
	{
		if ($isScrum)
		{
			$toolId = self::TASKS_TOOLS['scrum'];
		}
		elseif ($isProject)
		{
			$toolId = self::TASKS_TOOLS['projects'];
		}
		else
		{
			$toolId = self::SONET_TOOLS['workgroups'];
		}

		return $toolId;
	}

	public function getGroupLimitCodeByType(bool $isProject, bool $isScrum): ?string
	{
		return self::LIMIT_CODES[$this->getToolIdByType($isProject, $isScrum)] ?? null;
	}

	public function isToolAvailable(string $tool): bool
	{
		$tools = array_merge(self::TASKS_TOOLS, self::SONET_TOOLS, self::CALENDAR_TOOLS);
		if (!$this->isAvailable() || !array_key_exists($tool, $tools))
		{
			return true;
		}

		return ToolsManager::getInstance()->checkAvailabilityByToolId($tool);
	}
}