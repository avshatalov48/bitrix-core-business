<?php

namespace Bitrix\Im\V2\Link\File;

use Bitrix\Disk\File;
use Bitrix\Disk\Internals\AttachedObjectTable;
use Bitrix\Disk\SystemUser;
use Bitrix\Im\Model\FileTemporaryTable;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Type\DateTime;

class TemporaryFileService
{
	public const TASK_SOURCE = 'TASK';

	protected const SOURCE_MUST_BE_ATTACHED = [self::TASK_SOURCE];
	protected const LIMIT_SELECT_UNATTACHED_FILES = 200;
	protected const EXPIRY_INTERVAL = '-12 hours';

	protected DateTime $dateExpired;

	public function __construct()
	{
		$this->dateExpired = (new DateTime())->add(self::EXPIRY_INTERVAL);
	}

	public static function cleanAgent(): string
	{
		(new self())->clean();

		return __METHOD__. '();';
	}

	public function clean(): void
	{
		if (!Loader::includeModule('disk'))
		{
			return;
		}

		$this->deleteUnattachedFiles();
		$this->cleanExpired();
	}

	protected function deleteUnattachedFiles(): void
	{
		$subQuery = AttachedObjectTable::query()
			->setSelect(['ID'])
			->where('OBJECT_ID', new \Bitrix\Main\DB\SqlExpression('%s'))
		;

		$unattachedFiles = FileTemporaryTable::query()
			->setSelect(['DISK_FILE_ID'])
			->where('DATE_CREATE', '<', $this->dateExpired)
			->whereIn('SOURCE', self::SOURCE_MUST_BE_ATTACHED)
			->whereExpr("NOT EXISTS ({$subQuery->getQuery()})", ['DISK_FILE_ID'])
			->setLimit(self::LIMIT_SELECT_UNATTACHED_FILES)
			->fetchAll()
		;

		$diskFilesIds = array_column($unattachedFiles, 'DISK_FILE_ID');

		if (empty($diskFilesIds))
		{
			return;
		}

		$diskFiles = File::getModelList(['filter' => Query::filter()->whereIn('ID', $diskFilesIds)]);

		foreach ($diskFiles as $diskFile)
		{
			$diskFile->delete(SystemUser::SYSTEM_USER_ID);
		}
	}

	protected function cleanExpired(): void
	{
		FileTemporaryTable::deleteByFilter(['<DATE_CREATE' => $this->dateExpired]);
	}
}