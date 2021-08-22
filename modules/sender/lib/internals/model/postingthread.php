<?php

namespace Bitrix\Sender\Internals\Model;

use Bitrix\Main\Entity;

/**
 * Class PostingThreadTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_PostingThread_Query query()
 * @method static EO_PostingThread_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_PostingThread_Result getById($id)
 * @method static EO_PostingThread_Result getList(array $parameters = array())
 * @method static EO_PostingThread_Entity getEntity()
 * @method static \Bitrix\Sender\Internals\Model\EO_PostingThread createObject($setDefaultValues = true)
 * @method static \Bitrix\Sender\Internals\Model\EO_PostingThread_Collection createCollection()
 * @method static \Bitrix\Sender\Internals\Model\EO_PostingThread wakeUpObject($row)
 * @method static \Bitrix\Sender\Internals\Model\EO_PostingThread_Collection wakeUpCollection($rows)
 */
class PostingThreadTable extends Entity\DataManager
{
	const STATUS_NEW         = 'N';
	const STATUS_IN_PROGRESS = 'P';
	const STATUS_DONE        = 'D';

	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_posting_thread';
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		return [
			'THREAD_ID'   => [
				'data_type'    => 'integer',
				'primary'      => true,
				'autocomplete' => true,
			],
			'POSTING_ID'  => [
				'data_type' => 'integer',
				'primary'   => true,
				'required'  => true,
			],
			'STATUS'      => [
				'data_type' => 'string',
				'primary'   => false,
				'required'  => true,
			],
			'THREAD_TYPE' => [
				'data_type' => 'string',
				'required'  => true,
			],
			'EXPIRE_AT' => [
				'data_type' => 'datetime',
				'required'  => true,
			],
		];
	}
}