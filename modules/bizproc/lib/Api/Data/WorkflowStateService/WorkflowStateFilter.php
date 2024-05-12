<?php

namespace Bitrix\Bizproc\Api\Data\WorkflowStateService;

use Bitrix\Main\Localization\Loc;

class WorkflowStateFilter
{
	public const PRESET_IN_WORK = 'in_work';
	public const PRESET_STARTED = 'started';
	public const PRESET_HAS_TASK = 'has_task';
	public const PRESET_ALL_COMPLETED = 'all_completed';
	public const PRESET_DEFAULT = self::PRESET_IN_WORK;

	public static function getPresetList(): array
	{
		return [
			[
				'id' => static::PRESET_IN_WORK,
				'name' => Loc::getMessage('BIZPROC_API_DATA_WORKFLOW_STATE_FILTER_PRESET_IN_WORK'),
				'default' => true,
			],
			[
				'id' => static::PRESET_STARTED,
				'name' => Loc::getMessage('BIZPROC_API_DATA_WORKFLOW_STATE_FILTER_PRESET_STARTED'),
			],
			[
				'id' => static::PRESET_HAS_TASK,
				'name' => Loc::getMessage('BIZPROC_API_DATA_WORKFLOW_STATE_FILTER_PRESET_HAS_TASK_MSGVER_1'),
			],
			[
				'id' => static::PRESET_ALL_COMPLETED,
				'name' => Loc::getMessage('BIZPROC_API_DATA_WORKFLOW_STATE_FILTER_PRESET_ALL_COMPLETED'),
			],
		];
	}

	public static function isDefined(string $presetId): bool
	{
		return in_array($presetId, array_column(static::getPresetList(), 'id'), true);
	}
}
