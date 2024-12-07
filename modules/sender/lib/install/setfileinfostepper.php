<?php

namespace Bitrix\Sender\Install;

use Bitrix\Main;
use Bitrix\Main\Update\Stepper;
use Bitrix\Main\Type as MainType;
use Bitrix\Sender;
use Bitrix\Sender\Internals\Model\FileInfoTable;
use Bitrix\Sender\Internals\Model\MessageFieldTable;
use Bitrix\Sender\Internals\SqlBatch;

final class SetFileInfoStepper extends Stepper
{
	private const LIMIT = 100;
	private const FILE_INFO_SYNC_STAGE = 1;
	private const ATTACHMENT_SYNC_STAGE = 2;
	protected static $moduleId = 'sender';

	public function execute(array &$option): bool
	{
		$option['lastId'] = (int)($option['lastId'] ?? 0);
		$option['stage'] = (int)($option['stage'] ?? self::FILE_INFO_SYNC_STAGE);
		$firstId = $option['lastId'];

		if ($option['stage'] === self::FILE_INFO_SYNC_STAGE)
		{
			$selectedFiles = Main\FileTable::query()
				->setSelect(['ID', 'FILE_NAME'])
				->where('ID', '>', $option['lastId'])
				->where('MODULE_ID', self::$moduleId)
				->setOrder(['ID' => 'ASC'])
				->setLimit(self::LIMIT)
				->fetchAll()
			;

			foreach ($selectedFiles as $file)
			{
				$option['lastId'] = (int)$file['ID'];
				try
				{
					FileInfoTable::add([
						'ID' => $file['ID'],
						'FILE_NAME' => $file['FILE_NAME'],
					]);
				}
				catch (\Exception $exception)
				{
					/*
					 * which occurs if the file has already been added to the table earlier
					 * no need to react to duplicate key error
					 */
				}
			}

			if ($option['lastId'] === $firstId)
			{
				$option['stage'] = self::ATTACHMENT_SYNC_STAGE;
				$option['lastId'] = 0;
			}

			return self::CONTINUE_EXECUTION;
		}

		$attachmentFiles = MessageFieldTable::query()
			->setSelect(['MESSAGE_ID', 'VALUE'])
			->where('MESSAGE_ID', '>', $option['lastId'])
			->where('CODE', 'ATTACHMENT')
			->where('TYPE', 'file')
			->setOrder(['MESSAGE_ID' => 'ASC'])
			->setLimit(self::LIMIT)
			->fetchAll()
		;

		$batchData = [];
		foreach ($attachmentFiles as $file)
		{
			$option['lastId'] = (int)$file['MESSAGE_ID'];
			if (empty($file['VALUE']))
			{
				continue;
			}

			$attachmentIds = explode(',', $file['VALUE']);
			foreach ($attachmentIds as $attachmentId)
			{
				if (!is_numeric($attachmentId))
				{
					continue;
				}

				$fileInfo = Sender\FileTable::query()
					->setSelect(['ID'])
					->where('ENTITY_TYPE', Sender\FileTable::TYPES['LETTER'])
					->where('ENTITY_ID', $file['MESSAGE_ID'])
					->where('FILE_ID', $attachmentId)
					->setLimit(1)
					->fetch()
				;

				if ($fileInfo)
				{
					continue;
				}

				$batchData[] = [
					'ENTITY_TYPE' => Sender\FileTable::TYPES['LETTER'],
					'ENTITY_ID' => $file['MESSAGE_ID'],
					'FILE_ID' => $attachmentId,
					'DATE_INSERT' => new MainType\DateTime()
				];
			}
		}

		if (!empty($batchData))
		{
			SqlBatch::insert(Sender\FileTable::getTableName(), $batchData);
		}

		if ($option['lastId'] === $firstId)
		{
			\COption::SetOptionString('sender', 'sender_file_info_load_completed', 'Y');

			return self::FINISH_EXECUTION;
		}

		return self::CONTINUE_EXECUTION;
	}
}