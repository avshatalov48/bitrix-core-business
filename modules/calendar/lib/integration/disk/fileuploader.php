<?php

namespace Bitrix\Calendar\Integration\Disk;

use Bitrix\Disk\Driver;
use Bitrix\Disk\Ui\Text;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Result;

final class FileUploader
{
	private const NEW_FILE_PREFIX = 'n';

	private readonly int $userId;

	public function __construct(int $userId)
	{
		$this->userId = $userId;
	}

	/**
	 * @param int $fileId
	 *
	 * @return Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function addFile(int $fileId): Result
	{
		$result = new Result();

		if (!$this->isAvailable())
		{
			return $result->addError(new Error('Module disk not available'));
		}

		$storage = Driver::getInstance()->getStorageByUserId($this->userId);
		if (!$storage)
		{
			return $result->addError(new Error('Storage not found'));
		}

		$folder = $storage->getFolderForUploadedFiles();
		if (!$folder)
		{
			return $result->addError(new Error('Upload folder not found'));
		}

		if (!$folder->canAdd($storage->getSecurityContext($this->userId)))
		{
			return $result->addError(new Error('Access denied'));
		}

		$fileArray = \CFile::GetFileArray($fileId);

		$addedFile = $folder->addFile(
			[
				'NAME' => Text::correctFilename($fileArray['ORIGINAL_NAME']),
				'FILE_ID' => $fileId,
				'CONTENT_PROVIDER' => null,
				'SIZE' => $fileArray['FILE_SIZE'],
				'CREATED_BY' => $this->userId,
				'UPDATE_TIME' => null,
			],
			[],
			true,
		);

		if (!$addedFile)
		{
			return $result->addError(new Error('Error while uploading file'));
		}

		return $result->setData([
			'FILE' => $addedFile,
			'ATTACHMENT_ID' => self::NEW_FILE_PREFIX . $addedFile->getId(),
		]);
	}

	/**
	 * @return bool
	 * @throws \Bitrix\Main\LoaderException
	 */
	private function isAvailable(): bool
	{
		return Loader::includeModule('disk');
	}
}
