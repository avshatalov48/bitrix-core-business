<?php

namespace Bitrix\Bizproc\Script\Entity;

use Bitrix\Bizproc\Script\Queue\Status;
use Bitrix\Main;

class ScriptQueueTable extends Main\Entity\DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_bp_script_queue';
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		return [
			'ID' => [
				'data_type' => 'integer',
				'primary' => true,
			],
			'SCRIPT_ID' => [
				'data_type' => 'integer'
			],
			'STARTED_DATE' => [
				'data_type' => 'datetime'
			],
			'STARTED_BY' => [
				'data_type' => 'integer'
			],
			'STARTED_USER' => [
				'data_type' => Main\UserTable::class,
				'reference' => [
					'=this.STARTED_BY' => 'ref.ID'
				],
				'join_type' => 'LEFT',
			],
			'STATUS' => [
				'data_type' => 'integer'
			],
			'MODIFIED_DATE' => [
				'data_type' => 'datetime'
			],
			'MODIFIED_BY' => [
				'data_type' => 'integer'
			],
			new Main\ORM\Fields\ArrayField('WORKFLOW_PARAMETERS'),
		];
	}

	/**
	 * @param int $queueId
	 * @return int[]
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getDocumentCounters(int $queueId): array
	{
		$all = (int) ScriptQueueDocumentTable::getCount(['=QUEUE_ID' => $queueId]);
		$queued = (int) ScriptQueueDocumentTable::getCount(['=QUEUE_ID' => $queueId, '=STATUS' => Status::QUEUED]);

		return [
			'all' => $all,
			'queued' => $queued,
			'completed' => $all - $queued
		];
	}

	public static function getNextQueuedDocument(int $queueId): ?EO_ScriptQueueDocument
	{
		$document = ScriptQueueDocumentTable::getList([
			'filter' => [
				'=QUEUE_ID' => $queueId,
				'=STATUS' => Status::QUEUED,
			],
			'order' => ['ID' => 'ASC'],
			'limit' => 1
		])->fetchObject();

		return $document;
	}

	public static function markTerminated(int $queueId, int $userId)
	{
		static::update(
			$queueId,
			[
				'STATUS' => Status::TERMINATED,
				'MODIFIED_BY' => $userId
			]
		);

		$docResult = ScriptQueueDocumentTable::getList([
			'filter' => [
				'=QUEUE_ID' => $queueId,
				'=STATUS' => Status::QUEUED,
				],
			'select' => ['ID']
		]);

		$docIds = array_column($docResult->fetchAll(), 'ID');

		ScriptQueueDocumentTable::updateMulti($docIds, ['STATUS' => Status::TERMINATED], true);
	}

	public static function markExecuting(int $queueId)
	{
		static::update($queueId, ['STATUS' => Status::EXECUTING]);
	}

	public static function markCompleted(int $queueId)
	{
		static::update($queueId, ['STATUS' => Status::COMPLETED]);
	}

	public static function deleteByScript(int $scriptId)
	{
		$result = static::getList(['filter' => ['=SCRIPT_ID' => $scriptId], 'select' => ['ID']]);

		foreach ($result as $row)
		{
			static::delete($row['ID']);
			ScriptQueueDocumentTable::deleteByQueue($row['ID']);
		}
	}

	public static function onBeforeUpdate(Main\ORM\Event $event): Main\ORM\EventResult
	{
		$result = new Main\ORM\EventResult();

		$fields = $event->getParameter('fields');
		$modifyFields = ['MODIFIED_DATE' => new Main\Type\DateTime()];

		if(!isset($fields['MODIFIED_BY']))
		{
			$modifyFields['MODIFIED_BY'] = 0;
		}

		$result->modifyFields($modifyFields);

		return $result;
	}
}