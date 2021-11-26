<?php

namespace Bitrix\Bizproc\Storage\Entity;

use Bitrix\Main\ORM;

class ActivityStorageTable extends ORM\Data\DataManager
{
	public static function getTableName()
	{
		return 'b_bp_storage_activity';
	}

	public static function getMap()
	{
		return [
			'ID' => [
				'data_type' => 'integer',
				'primary' => true,
			],
			'WORKFLOW_TEMPLATE_ID' => [
				'data_type' => 'integer'
			],
			'ACTIVITY_NAME' => [
				'data_type' => 'string'
			],
			'KEY_ID' => [
				'data_type' => 'string'
			],
			'KEY_VALUE' => [
				'data_type' => 'string'
			]
		];
	}
}
