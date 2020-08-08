<?php

namespace Bitrix\Sender\Internals\Model;

use Bitrix\Main\Entity;

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