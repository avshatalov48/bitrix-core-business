<?php
namespace Bitrix\Im\Model;

use Bitrix\Im\V2\Common\DeleteTrait;
use Bitrix\Im\V2\Common\MultiplyInsertTrait;
use Bitrix\Main\ORM\Data\DataManager;

class AiTranscribeQueueTable extends DataManager
{
	use MultiplyInsertTrait;
	use DeleteTrait;
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_im_ai_transcribe_queue';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			'ID' => [
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			],
			'FILE_ID' => [
				'data_type' => 'integer',
				'required' => true,
			],
			'CHAT_ID' => [
				'data_type' => 'integer',
				'required' => true,
			],
		];
	}
}
