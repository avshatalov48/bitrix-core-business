<?php

namespace Bitrix\Im\Update;

use Bitrix\Disk\File;
use Bitrix\Disk\Internals\FileTable;
use Bitrix\Disk\SystemUser;
use Bitrix\Im\Model\LinkFileTable;
use Bitrix\Im\Model\MessageParamTable;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Update\Stepper;

final class DeletedMessageFiles extends Stepper
{
	protected static $moduleId = 'im';
	public const LIMIT = 50;
	public const DISK_MODULE_ID = 'disk';

	function execute(array &$option)
	{
		if (!Loader::includeModule(self::$moduleId) || !Loader::includeModule(self::DISK_MODULE_ID))
		{
			return self::FINISH_EXECUTION;
		}

		$fileIds = $this->getFileIdsFromDeletedMessage($option);

		if (empty($fileIds))
		{
			return self::FINISH_EXECUTION;
		}

		$diskFiles = File::getModelList([
			'filter' => Query::filter()->whereIn('ID', $fileIds)->where('TYPE', FileTable::TYPE)
		]);

		foreach ($diskFiles as $diskFile)
		{
			$diskFile->delete(SystemUser::SYSTEM_USER_ID);
		}

		return self::CONTINUE_EXECUTION;
	}

	private function getFileIdsFromDeletedMessage(array &$option): array
	{
		$result = MessageParamTable::query()
			->setSelect(['LINK_MESSAGE_ID' => 'LINK_FILE.MESSAGE_ID'])
			->where('MESSAGE_ID', '>', $option['lastId'] ?? 0)
			->setOrder(['MESSAGE_ID'])
			->where('PARAM_NAME', 'IS_DELETED')
			->registerRuntimeField(
				'LINK_FILE',
				new Reference(
					'LINK_FILE',
					LinkFileTable::class,
					Join::on('this.MESSAGE_ID', 'ref.MESSAGE_ID'),
					['join_type' => Join::TYPE_INNER]
				)
			)
			->setLimit(self::LIMIT)
			->fetchAll()
		;

		if (empty($result))
		{
			return [];
		}

		$messageIds = array_unique(array_map('intval', array_column($result, 'LINK_MESSAGE_ID')));
		$option['lastId'] = max($messageIds);

		return $this->getFileIdsByMessageIds($messageIds);
	}

	private function getFileIdsByMessageIds(array $messageIds): array
	{
		if (empty($messageIds))
		{
			return [];
		}

		return LinkFileTable::query()
			->setSelect(['DISK_FILE_ID'])
			->whereIn('MESSAGE_ID', $messageIds)
			->fetchCollection()
			->getDiskFileIdList()
		;
	}
}