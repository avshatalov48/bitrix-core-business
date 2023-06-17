<?php

namespace Bitrix\Im\Update;

use Bitrix\Im\Common;
use Bitrix\Im\Model\EO_MessageParam_Collection;
use Bitrix\Im\Model\LinkFileTable;
use Bitrix\Im\Model\MessageParamTable;
use Bitrix\Im\Model\MessageTable;
use Bitrix\Im\V2\Link\File\FileCollection;
use Bitrix\Im\V2\Link\File\FileItem;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Update\Stepper;
use Bitrix\Pull\Event;

final class LinkFileMigration extends Stepper
{
	protected static $moduleId = 'im';
	public const OPTION_NAME = 'im_link_file_migration';
	public const OPTION_NAME_LIMIT = 'im_link_file_migration_limit';
	public const OPTION_NAME_ITERATION_COUNT = 'im_link_file_migration_iteration';
	public const LIMIT_DEFAULT = 500;
	public const ITERATION_COUNT_DEFAULT = 4;

	function execute(array &$option)
	{
		if (!Loader::includeModule(self::$moduleId))
		{
			return self::CONTINUE_EXECUTION;
		}

		$numOfIterations = (int)Option::get(self::$moduleId, self::OPTION_NAME_ITERATION_COUNT, self::ITERATION_COUNT_DEFAULT);

		$result = self::CONTINUE_EXECUTION;
		for ($i = 0; $i < $numOfIterations; ++$i)
		{
			$result = $this->makeMigrationIteration($option);

			if ($result === self::FINISH_EXECUTION)
			{
				return $result;
			}
		}

		return $result;
	}

	private function makeMigrationIteration(array &$option): bool
	{
		$isFinished = Option::get(self::$moduleId, self::OPTION_NAME, '');

		if ($isFinished === '')
		{
			Option::set(self::$moduleId, self::OPTION_NAME, 'N');
		}

		if ($isFinished === 'Y')
		{
			return self::FINISH_EXECUTION;
		}

		$lastId = $option['lastId'] ?? 0;
		$params = $this->getParams($lastId);

		if ($params->count() === 0)
		{
			Option::set(self::$moduleId, self::OPTION_NAME, 'Y');
			if (\Bitrix\Main\Loader::includeModule('pull'))
			{
				Event::add(
					Event::SHARED_CHANNEL,
					[
						'module_id' => 'im',
						'command' => 'linkFileMigrationFinished',
						'extra' => Common::getPullExtra(),
					],
					\CPullChannel::TYPE_SHARED
				);
			}

			return self::FINISH_EXECUTION;
		}

		$ids = $params->getParamValueList();
		$lastId = max($params->getIdList());
		$this->changeMigrationFlag(true);
		$fileCollection = new FileCollection();
		$fileEntities = \Bitrix\Im\V2\Entity\File\FileCollection::initByDiskFilesIds($ids);
		foreach ($params as $param)
		{
			$fileEntity = $fileEntities->getById((int)$param->getParamValue());
			if ($fileEntity === null)
			{
				continue;
			}
			$message = $param->getMessage();
			if ($message === null)
			{
				continue;
			}
			$file = new FileItem();
			$file
				->setChatId($message->getChatId())
				->setAuthorId($message->getAuthorId())
				->setMessageId($param->getMessageId())
				->setEntity($fileEntity)
				->setDateCreate($message->getDateCreate())
			;
			$fileCollection->add($file);
		}
		$fileCollection->save(true);
		$this->changeMigrationFlag(false);
		$option['lastId'] = $lastId;
		$steps = LinkFileTable::getCount();
		$count = MessageParamTable::getCount(Query::filter()->where('PARAM_NAME', 'FILE_ID'));
		$option['steps'] = $steps;
		$option['count'] = $count;

		return self::CONTINUE_EXECUTION;
	}

	private function getParams(int $lastId): EO_MessageParam_Collection
	{
		$params = MessageParamTable::query()
			->setSelect(['ID'])
			->where('PARAM_NAME', 'FILE_ID')
			->where('ID', '>', $lastId)
			->setOrder(['ID' => 'ASC'])
			->setLimit((int)Option::get(self::$moduleId, self::OPTION_NAME_LIMIT, self::LIMIT_DEFAULT))
			->fetchCollection()
		;

		if ($params->count() === 0)
		{
			return $params;
		}

		$params->fill(['MESSAGE_ID', 'PARAM_VALUE']);

		$messageIds = $params->getMessageIdList();

		if (empty($messageIds))
		{
			return $params;
		}

		$messages = MessageTable::query()
			->setSelect(['ID', 'AUTHOR_ID', 'DATE_CREATE', 'CHAT_ID'])
			->whereIn('ID', $messageIds)
			->fetchCollection()
		;

		foreach ($params as $param)
		{
			$message = $messages->getByPrimary($param->getMessageId());
			if ($message !== null)
			{
				$param->setMessage($message);
			}
		}

		return $params;
	}

	private function changeMigrationFlag(bool $flag): void
	{
		$this->changeMigrationFlagForClass(FileCollection::class, $flag);
		$this->changeMigrationFlagForClass(FileItem::class, $flag);
	}

	private function changeMigrationFlagForClass(string $className, bool $flag): void
	{
		$migrationFlagName = 'isMigrationFinished';
		$migrationFlag = new \ReflectionProperty($className, $migrationFlagName);
		$migrationFlag->setAccessible(true);
		$migrationFlag->setValue($flag);
	}
}