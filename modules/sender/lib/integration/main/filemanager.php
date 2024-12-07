<?php

namespace Bitrix\Sender\Integration\Main;

use Bitrix\Sender\Internals\Model\FileInfoTable;

final class FileManager
{
	private const MODULE_ID = 'sender';

	/**
	 * @param array{ID: int|string|null, MODULE_ID: ?string, FILE_NAME: ?string} $fileData
	 * @return void
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function OnAfterFileSave(array $fileData): void
	{
		$moduleId = (string)($fileData['MODULE_ID'] ?? '');
		$fileName = (string)($fileData['FILE_NAME'] ?? '');
		$fileId = (int)($fileData['ID'] ?? null);

		if ($moduleId !== self::MODULE_ID || !$fileId || empty($fileName) )
		{
			return;
		}

		$senderFileInfo = FileInfoTable::getById($fileId)->fetch();

		if (!$senderFileInfo)
		{
			FileInfoTable::add([
				'ID' => $fileId,
				'FILE_NAME' => $fileName,
			]);

			return;
		}

		if ($senderFileInfo['FILE_NAME'] !== $fileName)
		{
			FileInfoTable::update($fileId, ['FILE_NAME' => $fileName]);
		}
	}
}
