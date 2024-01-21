<?php

namespace Bitrix\Bizproc\FileUploader;

use Bitrix\Main\Engine\CurrentUser;
use Bitrix\UI\FileUploader\FileOwnershipCollection;
use Bitrix\UI\FileUploader\Configuration;
use Bitrix\UI\FileUploader\UploaderController;

class TaskUploaderController extends UploaderController
{
	public function __construct(array $options)
	{
		$options['taskId'] = (int)($options['taskId'] ?? 0);
		$options['fieldId'] = (string)($options['fieldName'] ?? 0);

		parent::__construct($options);
	}

	public function isAvailable(): bool
	{
		return true;
	}

	public function getConfiguration(): Configuration
	{
		return new Configuration();
	}

	public function canUpload(): bool
	{
		[
			'taskId' => $taskId,
			'fieldId' => $fieldId,
		] = $this->getOptions();

		if (!$taskId)
		{
			return false;
		}

		$userId = (int)(CurrentUser::get()?->getId() ?? 0);
		$taskUserIds = \CBPTaskService::getTaskUserIds($taskId);

		if (!in_array($userId, $taskUserIds, true))
		{
			return false;
		}

		$task = \CBPTaskService::getList(
			arFilter: ['ID' => $taskId],
			arSelectFields: ['ACTIVITY', 'PARAMETERS', 'STATUS']
		)->fetch();

		if ($task && (int)$task['STATUS'] === \CBPTaskStatus::Running)
		{
			$taskFields = \CBPDocument::getTaskControls($task)['FIELDS'] ?? [];

			$editableFields = array_filter(
				$taskFields,
				static fn($field) => $field['Id'] === $fieldId && in_array($field['Type'], ['file', 'S:DiskFile']),
			);

			return count($editableFields) > 0;
		}

		return false;
	}

	public function verifyFileOwner(FileOwnershipCollection $files): void
	{
		$stop = true;

		// $entityFiles = $this->fetchToDoActivityFiles();
		// foreach ($files as $file)
		// {
		// 	if (in_array($file->getId(), $entityFiles, true))
		// 	{
		// 		$file->markAsOwn();
		// 	}
		// }
	}

	public function canView(): bool
	{
		return true;// todo: check?
	}

	public function canRemove(): bool
	{
		return false;
	}
}
