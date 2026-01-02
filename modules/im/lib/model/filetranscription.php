<?php
namespace Bitrix\Im\Model;

use Bitrix\Im\V2\Common\MultiplyInsertTrait;
use Bitrix\Main\ORM\Data\DataManager;

class FileTranscriptionTable extends DataManager
{
	use MultiplyInsertTrait;
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_im_file_transcription';
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
			'TEXT' => [
				'data_type' => 'text',
			],
		];
	}
}
