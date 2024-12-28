<?php

namespace Bitrix\Bizproc\Api\Enum\WorkflowFacesService;

use Bitrix\Main\Localization\Loc;

enum WorkflowFacesStep: string
{
	case Author = 'author';
	case Running = 'running';
	case Completed = 'completed';
	case Done = 'done';
	case TimeInWork = 'time_in_work';
	case TimeFinal = 'time_final';

	public function getTitle(): string
	{
		return match ($this)
		{
			self::Author => Loc::getMessage('BIZPROC_API_ENUM_WORKFLOW_FACES_SERVICE_STEP_TITLE_AUTHOR') ?? '',
			self::Running => Loc::getMessage('BIZPROC_API_ENUM_WORKFLOW_FACES_SERVICE_STEP_TITLE_RUNNING') ?? '',
			self::Completed => Loc::getMessage('BIZPROC_API_ENUM_WORKFLOW_FACES_SERVICE_STEP_TITLE_COMPLETED') ?? '',
			self::Done => Loc::getMessage('BIZPROC_API_ENUM_WORKFLOW_FACES_SERVICE_STEP_TITLE_DONE') ?? '',
			self::TimeInWork => Loc::getMessage('BIZPROC_API_ENUM_WORKFLOW_FACES_SERVICE_STEP_TITLE_TIME_IN_WORK') ?? '',
			self::TimeFinal => Loc::getMessage('BIZPROC_API_ENUM_WORKFLOW_FACES_SERVICE_STEP_TITLE_TIME_FINAL') ?? '',
		};
	}

	public function getStatus(bool $success = null): ?WorkflowFacesStepStatus
	{
		return match ($this)
		{
			self::Author => null,
			self::Running, self::TimeInWork => WorkflowFacesStepStatus::Wait,
			self::Completed, self::Done => (
				$success ? WorkflowFacesStepStatus::Success : WorkflowFacesStepStatus::NotSuccess
			),
			self::TimeFinal => WorkflowFacesStepStatus::Success,
		};
	}
}
