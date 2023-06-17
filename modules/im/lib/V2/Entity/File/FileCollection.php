<?php

namespace Bitrix\Im\V2\Entity\File;

use Bitrix\Disk\File;
use Bitrix\Disk\Driver;
use Bitrix\Disk\Internals\FileTable;
use Bitrix\Disk\Storage;
use Bitrix\Im\Model\EO_FileTemporary;
use Bitrix\Im\Model\EO_FileTemporary_Collection;
use Bitrix\Im\V2\Entity\EntityCollection;
use Bitrix\Im\V2\Entity\User\UserPopupItem;
use Bitrix\Im\V2\Rest\PopupData;
use Bitrix\Im\V2\Result;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Query\Query;

/**
 * @method FileItem next()
 * @method FileItem current()
 * @method FileItem offsetGet($offset)
 */
class FileCollection extends EntityCollection
{
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

		$diskFiles = File::getModelList([
			'filter' => Query::filter()->whereIn('ID', $diskFilesIds)->where('TYPE', FileTable::TYPE)
		]);

		return new static($diskFiles, $chatId);
	}

	public function getCopies(?Storage $storage = null): self
	{
		$userId = $this->getContext()->getUserId();
		$storage = $storage ?? Driver::getInstance()->getStorageByUserId($userId);
		$copies = new static();

		foreach ($this as $fileEntity)
		{
			$copy = $fileEntity->getCopy($storage);
			if ($copy !== null)
			{
				$copies[] = $copy;
			}
		}

		return $copies;
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

	public function getPopupData(array $excludedList = []): PopupData
	{
		$data = new PopupData([new UserPopupItem()], $excludedList);

		return parent::getPopupData($excludedList)->merge($data);
	}
}