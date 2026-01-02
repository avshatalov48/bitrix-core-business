<?php

namespace Bitrix\Im\V2\Controller\Disk;

use Bitrix\Im\V2\Application\Features;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Controller\BaseController;
use Bitrix\Im\V2\Controller\Filter\CheckDiskFileAccess;
use Bitrix\Im\V2\Entity\File\FileCollection;
use Bitrix\Im\V2\Entity\File\FileError;
use Bitrix\Im\V2\Entity\File\FileItem;
use Bitrix\Im\V2\Entity\User\UserError;
use Bitrix\Im\V2\Integration\AI\CopilotError;
use Bitrix\Im\V2\Integration\AI\Transcription\TranscribeManager;
use Bitrix\Main\Engine\AutoWire\ExactParameter;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Loader;

class File extends BaseController
{
	public function configureActions(): array
	{
		return [
			'save' => [
				'+prefilters' => [
					new CheckDiskFileAccess(),
				]
			],
			'transcribe' => [
				'+prefilters' => [
					new CheckDiskFileAccess(),
				]
			],
		];
	}

	public function getPrimaryAutoWiredParameter()
	{
		return new ExactParameter(
			FileCollection::class,
			'files',
			function ($className, array $ids) {
				return $this->getFilesByIds($ids);
			}
		);
	}

	public function getAutoWiredParameters()
	{
		return array_merge(parent::getAutoWiredParameters(), [
			new ExactParameter(
				FileItem::class,
				'file',
				function ($className, int $fileId) {
					return FileItem::initByDiskFileId($fileId);
				}
			),
		]);
	}

	/**
	 * @restMethod im.v2.Disk.File.save
	 */
	public function saveAction(FileCollection $files, CurrentUser $currentUser): ?array
	{
		$userId = $currentUser->getId();
		if (!isset($userId))
		{
			$this->addError(new UserError(UserError::NOT_FOUND));
			return null;
		}

		if (!Loader::includeModule('disk'))
		{
			$this->addError(new FileError(FileError::DISK_NOT_INSTALLED));
			return null;
		}

		$result = $files->copyToOwnSavedFiles();
		if (!$result->hasResult())
		{
			$this->addErrors($result->getErrors());
			return null;
		}

		return ['result' => true];
	}

	/**
	 * @restMethod im.v2.Disk.File.transcribe
	 */
	public function transcribeAction(Chat $chat, FileItem $file): ?array
	{
		if ((int)$file->getDiskFile()?->getSize() > TranscribeManager::MAX_TRANSCRIBABLE_FILE_SIZE)
		{
			$this->addError(new FileError(FileError::FILE_SIZE_EXCEEDED));
			return null;
		}

		if (!Features::isAiFileTranscriptionAvailable())
		{
			$this->addError(new CopilotError(CopilotError::TRANSCRIPTION_NOT_ACTIVE));
			return null;
		}

		if (!$file->isTranscribable())
		{
			$this->addError(new CopilotError(CopilotError::FILE_NOT_TRANSCRIBABLE));
			return null;
		}

		$transcribeManager = new TranscribeManager($file->getOriginalFileId(), $file->getId(), (int)$chat->getId());

		return $transcribeManager->transcribeFile()->getFileItem()->toRestFormat();
	}

	protected function getFilesByIds(array $ids): FileCollection
	{
		$result = [];

		foreach ($ids as $id)
		{
			if (is_numeric($id) && (int)$id > 0)
			{
				$id = (int)$id;
				$result[$id] = $id;
			}
		}

		return FileCollection::initByDiskFilesIds(array_values($result));
	}
}
