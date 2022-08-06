<?php

namespace Bitrix\Bizproc\Storage\Entity;

use Bitrix\Main\ORM;

/**
 * Class ActivityStorageTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_ActivityStorage_Query query()
 * @method static EO_ActivityStorage_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_ActivityStorage_Result getById($id)
 * @method static EO_ActivityStorage_Result getList(array $parameters = [])
 * @method static EO_ActivityStorage_Entity getEntity()
 * @method static \Bitrix\Bizproc\Storage\Entity\EO_ActivityStorage createObject($setDefaultValues = true)
 * @method static \Bitrix\Bizproc\Storage\Entity\EO_ActivityStorage_Collection createCollection()
 * @method static \Bitrix\Bizproc\Storage\Entity\EO_ActivityStorage wakeUpObject($row)
 * @method static \Bitrix\Bizproc\Storage\Entity\EO_ActivityStorage_Collection wakeUpCollection($rows)
 */
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
