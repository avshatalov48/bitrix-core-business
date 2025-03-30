<?php

namespace Bitrix\Im\V2\Entity\File;

use Bitrix\Disk\File;
use Bitrix\Disk\Driver;
use Bitrix\Disk\Folder;
use Bitrix\Disk\Internals\FileTable;
use Bitrix\Im\Model\EO_FileTemporary;
use Bitrix\Im\Model\EO_FileTemporary_Collection;
use Bitrix\Im\V2\Entity\EntityCollection;
use Bitrix\Im\V2\Entity\User\UserPopupItem;
use Bitrix\Im\V2\Registry;
use Bitrix\Im\V2\Rest\PopupData;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\TariffLimit\DateFilterable;
use Bitrix\Im\V2\TariffLimit\FilterResult;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Type\DateTime;

/**
 * @extends Registry<FileItem>
 * @method FileItem offsetGet($key)
 */
class FileCollection extends EntityCollection implements DateFilterable
{
	protected static array $preloadDiskFiles = [];

	/**
	 * @param int[]|File[]|null $diskFiles
	 * @param int|null $chatId
	 */
	public function __construct(?array $diskFiles = null, ?int $chatId = null)
	{
		parent::__construct();

		if ($diskFiles !== null)
		{
			foreach ($diskFiles as $diskFile)
			{
				$this[] = new FileItem($diskFile, $chatId);
			}
		}
	}

	public static function getRestEntityName(): string
	{
		return 'files';
	}

	/**
	 * @param int[] $diskFilesIds
	 * @param int|null $chatId
	 * @return static
	 */
	public static function initByDiskFilesIds(array $diskFilesIds, ?int $chatId = null): self
	{
		if (empty($diskFilesIds) || !Loader::includeModule('disk'))
		{
			return new static();
		}

		[$preloadDiskFiles, $filesToLoad] = static::getPreloadDiskFile($diskFilesIds);
		$diskFiles = [];

		if (!empty($filesToLoad))
		{
			$diskFiles = File::getModelList([
				'filter' => Query::filter()->whereIn('ID', $filesToLoad)->where('TYPE', FileTable::TYPE),
				'with' => ['PREVIEW'],
			]);
		}

		return new static(array_merge($diskFiles, $preloadDiskFiles), $chatId);
	}

	public function getDiskFiles(): array
	{
		$diskFiles = [];

		foreach ($this as $file)
		{
			$diskFile = $file->getDiskFile();
			if ($diskFile)
			{
				$diskFiles[$diskFile->getId()] = $diskFile;
			}
		}

		return $diskFiles;
	}

	public function getMessageOut(): array
	{
		$result = [];

		foreach ($this as $file)
		{
			$messageOut = $file->getMessageOut();
			if ($messageOut)
			{
				$result[] = $messageOut;
			}
		}

		return $result;
	}

	/**
	 * @param Folder $folder
	 * @return Result<FileCollection>
	 */
	public function copyTo(Folder $folder): Result
	{
		$result = new Result();
		$copies = new static();

		foreach ($this as $fileEntity)
		{
			$copy = $fileEntity->copyTo($folder)->getResult();
			if (isset($copy))
			{
				$copies[] = $copy;
			}
		}

		if ($copies->count() > 0)
		{
			$result->setResult($copies);
		}
		else
		{
			$result->addError(new FileError(FileError::COPY_ERROR));
		}

		return $result;
	}

	/**
	 * @return Result<FileCollection>
	 */
	public function copyToOwnSavedFiles(): Result
	{
		$result = $this->getOwnStorageFolderByType(FolderType::SavedFiles);
		if (!$result->hasResult())
		{
			return $result;
		}

		return $this->copyTo($result->getResult());
	}

	/**
	 * @return Result<FileCollection>
	 */
	public function copyToOwnUploadedFiles(): Result
	{
		$result = $this->getOwnStorageFolderByType(FolderType::UploadedFiles);
		if (!$result->hasResult())
		{
			return $result;
		}

		return $this->copyTo($result->getResult());
	}

	/**
	 * @param FolderType $folderType
	 * @return Result<Folder>
	 */
	private function getOwnStorageFolderByType(FolderType $folderType): Result
	{
		$result = new Result();

		$userId = $this->getContext()->getUserId();
		$storage = Driver::getInstance()->getStorageByUserId($userId);
		if (!isset($storage))
		{
			return $result->addError(new FileError(FileError::STORAGE_NOT_FOUND));
		}

		$folder = $storage->getSpecificFolderByCode($folderType->value);
		if (!isset($folder))
		{
			return $result->addError(new FileError(FileError::FOLDER_NOT_FOUND));
		}

		return $result->setResult($folder);
	}

	public function addToTmp(string $source): Result
	{
		$tmpCollection = new EO_FileTemporary_Collection();

		foreach ($this as $file)
		{
			$tmpEntity = new EO_FileTemporary(['DISK_FILE_ID' => $file->getId(), 'SOURCE' => $source]);
			$tmpCollection->add($tmpEntity);
		}

		$addResult = $tmpCollection->save(true);

		if (!$addResult->isSuccess())
		{
			return (new Result())->addErrors($addResult->getErrors());
		}

		return new Result();
	}

	public function getFileDiskAttributes(int $chatId, array $options = []): array
	{
		$resultData = [];
		foreach ($this as $file)
		{
			$resultData[$file->getDiskFileId()] = \CIMDisk::GetFileParams($chatId, $file->getDiskFile(), $options = []);
		}

		return $resultData;
	}

	/**
	 * @param File[] $diskFiles
	 * @return void
	 */
	public static function addDiskFilesToPreload(array $diskFiles): void
	{
		foreach ($diskFiles as $diskFile)
		{
			if ($diskFile instanceof File)
			{
				static::$preloadDiskFiles[$diskFile->getId()] = $diskFile;
			}
		}
	}

	protected static function getPreloadDiskFile(array $diskFileIds): array
	{
		$preloadDiskFiles = [];
		$filesToLoad = [];

		foreach ($diskFileIds as $diskFileId)
		{
			if (isset(self::$preloadDiskFiles[$diskFileId]))
			{
				$preloadDiskFiles[] = self::$preloadDiskFiles[$diskFileId];
			}
			else
			{
				$filesToLoad[] = $diskFileId;
			}
		}

		return [$preloadDiskFiles, $filesToLoad];
	}

	public function getPopupData(array $excludedList = []): PopupData
	{
		$data = new PopupData([new UserPopupItem()], $excludedList);

		return parent::getPopupData($excludedList)->merge($data);
	}

	public function filterByDate(DateTime $date): FilterResult
	{
		$filtered = $this->filter(
			static fn (FileItem $file) => $file->getDiskFile()?->getCreateTime()?->getTimestamp() > $date->getTimestamp()
		);

		return (new FilterResult())->setResult($filtered)->setFiltered($this->count() !== $filtered->count());
	}

	public function getRelatedChatId(): ?int
	{
		return $this->getAny()?->getChatId();
	}
}