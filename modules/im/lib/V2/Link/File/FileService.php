<?php

namespace Bitrix\Im\V2\Link\File;

use Bitrix\Disk\Driver;
use Bitrix\Disk\File;
use Bitrix\Disk\Security\DiskSecurityContext;
use Bitrix\Im\Model\MessageParamTable;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Link\Push;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Result;
use Bitrix\Main\Config\Option;
use Bitrix\Main\ORM\Query\Query;

class FileService
{
	use ContextCustomer;

	protected const ADD_FILE_EVENT = 'fileAdd';
	protected const DELETE_FILE_EVENT = 'fileDelete';

	protected bool $isMigrationFinished;

	public function __construct()
	{
		$this->isMigrationFinished = Option::get('im', 'im_link_file_migration', 'N') === 'Y';
	}

	public function save(Message $message): Result
	{
		$files = $message->getFiles();
		$links = FileCollection::linkEntityToMessage($files, $message);

		return $this->saveInternal($links);
	}

	/**
	 * @param File[] $files
	 * @param Message $message
	 * @return Result
	 */
	public function saveFilesFromMessage(array $files, Message $message): Result
	{
		$result = new Result();

		if (empty($files))
		{
			return $result;
		}

		$entities = new \Bitrix\Im\V2\Entity\File\FileCollection($files, $message->getChatId());
		/** @var FileCollection $links */
		$links = FileCollection::linkEntityToMessage($entities, $message);

		return $this->saveInternal($links);
	}

	protected function saveInternal(FileCollection $links): Result
	{
		$result = new Result();
		$saveResult = $links->save();

		if ($links->count() === 0)
		{
			return $result;
		}

		if (!$saveResult->isSuccess())
		{
			$result->addErrors($saveResult->getErrors());
		}

		if ($saveResult->isSuccess())
		{
			foreach ($links as $link)
			{
				Push::getInstance()
					->setContext($this->context)
					->sendFull($link, self::ADD_FILE_EVENT, ['CHAT_ID' => $link->getChatId()])
				;
			}
		}

		return $result;
	}

	public function deleteFilesByDiskFileId(int $diskFileId): Result
	{
		$result = new Result();

		$link = FileItem::getByDiskFileId($diskFileId);

		if ($link === null)
		{
			return $result;
		}

		$deleteResult = $link->delete();

		if (!$deleteResult->isSuccess())
		{
			return $result->addErrors($deleteResult->getErrors());
		}

		if (!$this->isMigrationFinished)
		{
			return $result;
		}

		Push::getInstance()
			->setContext($this->context)
			->sendIdOnly($link, self::DELETE_FILE_EVENT, ['CHAT_ID' => $link->getChatId()])
		;

		return $result;
	}

	public function isMigrationFinished(): bool
	{
		return $this->isMigrationFinished;
	}

	public function getFilesBeforeMigrationFinished(int $chatId, int $limit, ?int $lastId = null, ?string $filename = null): \Bitrix\Im\V2\Entity\File\FileCollection
	{
		$folderModel = \CIMDisk::getFolderModel($chatId, false);
		if ($folderModel === false)
		{
			return new \Bitrix\Im\V2\Entity\File\FileCollection();
		}
		$relation = \CIMChat::GetRelationById($chatId, $this->getContext()->getUserId(), true, false);
		$filter = Query::filter()
			->where('PARENT_ID', $folderModel->getId())
			->where('STORAGE_ID', $folderModel->getStorageId())
			->where('ID', '>', $relation['LAST_FILE_ID'])
		;
		if (isset($lastId))
		{
			$filter->where('ID', '<', $lastId);
		}
		if (isset($filename))
		{
			$clearFileName = str_replace("%", '', $filename);
			$filter->whereLike('NAME', "$clearFileName%");
		}
		$parameters = [
			'filter' => $filter,
			'with' => ['CREATE_USER'],
			'limit' => $limit,
			'order' => ['ID' => 'DESC']
		];
		$securityContext = new DiskSecurityContext($this->getContext()->getUserId());
		$parameters = Driver::getInstance()->getRightsManager()->addRightsCheck($securityContext, $parameters, ['ID', 'CREATED_BY']);
		$diskFiles = File::getModelList($parameters);

		return new \Bitrix\Im\V2\Entity\File\FileCollection($diskFiles, $chatId);
	}
}