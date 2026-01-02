<?php

namespace Bitrix\Socialnetwork\Integration\Intranet;

use Bitrix\Intranet\Settings\Tools\ToolsManager;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Item\Workgroup\Type;

final class Settings
{
	public const LIMIT_CODES = [
		'workgroups' => 'limit_groups_off',
		'projects' => 'limit_projects_off',
		'scrum' => 'limit_tasks_scrum_off',
		'collab' => 'limit_v2_socialnetwork_collab_off',
	];

	public const LIMIT_FEATURES = [
		'collab' => 'socialnetwork_collab_off',
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
		'collab' => 'collab',
	];

	public const CALENDAR_TOOLS = [
		'calendar' => 'calendar',
	];

	private function isAvailable(): bool
	{
		return Loader::includeModule('intranet') && class_exists(ToolsManager::class);
	}

	public function isGroupAvailableByType(Type $type): bool
	{
		return $this->isToolAvailable($this->getToolIdByGroupType($type));
	}

	private function getToolIdByGroupType(Type $type): string
	{
		return match ($type)
		{
			Type::Scrum => self::TASKS_TOOLS['scrum'],
			Type::Project => self::TASKS_TOOLS['projects'],
			Type::Collab => self::SONET_TOOLS['collab'],
			default => self::SONET_TOOLS['workgroups'],
		};
	}

	public function getGroupLimitCodeByType(Type $type): ?string
	{
		return self::LIMIT_CODES[$this->getToolIdByGroupType($type)] ?? null;
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