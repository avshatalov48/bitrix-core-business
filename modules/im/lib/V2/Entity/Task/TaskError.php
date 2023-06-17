<?php

namespace Bitrix\Im\V2\Entity\Task;

use Bitrix\Im\V2\Error;
use Bitrix\Main\Localization\Loc;

class TaskError extends Error
{
	public const TASKS_NOT_INSTALLED = 'TASKS_NOT_INSTALLED';
	public const WRONG_SIGNED_FILES = 'WRONG_SIGNED_FILES';
	public const ADD_TASK_MESSAGE_FAILED = 'ADD_TASK_MESSAGE_FAILED';
	public const NOT_FOUND = 'TASK_NOT_FOUND';
	public const ACCESS_ERROR = 'TASK_ACCESS_ERROR';
	public const DELETE_ERROR = 'TASK_DELETE_ERROR';

	protected function loadErrorMessage($code, $replacements): string
	{
		return Loc::getMessage("ERROR_TASK_{$code}", $replacements) ?: '';
	}

	protected function loadErrorDescription($code, $replacements): string
	{
		return Loc::getMessage("ERROR_TASK_{$code}_DESC", $replacements) ?: '';
	}
}