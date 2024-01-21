<?php


namespace Bitrix\Socialnetwork\Internals\LiveFeed\Counter\Queue;

use Bitrix\Main\Entity\DataManager;

/**
 * Class QueueTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Queue_Query query()
 * @method static EO_Queue_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Queue_Result getById($id)
 * @method static EO_Queue_Result getList(array $parameters = [])
 * @method static EO_Queue_Entity getEntity()
 * @method static \Bitrix\Socialnetwork\Internals\LiveFeed\Counter\Queue\EO_Queue createObject($setDefaultValues = true)
 * @method static \Bitrix\Socialnetwork\Internals\LiveFeed\Counter\Queue\EO_Queue_Collection createCollection()
 * @method static \Bitrix\Socialnetwork\Internals\LiveFeed\Counter\Queue\EO_Queue wakeUpObject($row)
 * @method static \Bitrix\Socialnetwork\Internals\LiveFeed\Counter\Queue\EO_Queue_Collection wakeUpCollection($rows)
 */
class QueueTable extends DataManager
{

	public static function getTableName(): string
	{
		return 'b_sonet_scorer_queue';
	}

	public static function getClass(): string
	{
		return static::class;
	}

	public static function getMap(): array
	{
		return [
			'ID' => [
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			],
			'USER_ID' => [
				'data_type' => 'integer',
				'required' => true,
			],
			'TYPE' => [
				'data_type' => 'string',
				'required' => true,
			],
			'SONET_LOG_ID' => [
				'data_type' => 'integer',
				'required' => true,
			],
			'DATETIME' => [
				'data_type' => 'datetime'
			]
		];
	}
}