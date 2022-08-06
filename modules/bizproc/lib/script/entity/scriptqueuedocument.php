<?php

namespace Bitrix\Bizproc\Script\Entity;

use Bitrix\Main;

/**
 * Class ScriptQueueDocumentTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_ScriptQueueDocument_Query query()
 * @method static EO_ScriptQueueDocument_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_ScriptQueueDocument_Result getById($id)
 * @method static EO_ScriptQueueDocument_Result getList(array $parameters = [])
 * @method static EO_ScriptQueueDocument_Entity getEntity()
 * @method static \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument createObject($setDefaultValues = true)
 * @method static \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument_Collection createCollection()
 * @method static \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument wakeUpObject($row)
 * @method static \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument_Collection wakeUpCollection($rows)
 */
class ScriptQueueDocumentTable extends Main\Entity\DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_bp_script_queue_document';
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
			'QUEUE_ID' => [
				'data_type' => 'integer'
			],
			'DOCUMENT_ID' => [
				'data_type' => 'string'
			],
			'WORKFLOW_ID' => [
				'data_type' => 'string'
			],
			'STATUS' => [
				'data_type' => 'integer'
			],
			'STATUS_MESSAGE' => [
				'data_type' => 'string'
			],
			'QUEUE' => [
				'data_type' => ScriptQueueTable::class,
				'reference' => array(
					'=this.QUEUE_ID' => 'ref.ID'
				),
			]
		];
	}

	public static function deleteByQueue(int $queueId)
	{
		$result = static::getList(['filter' => ['=QUEUE_ID' => $queueId], 'select' => ['ID']]);

		foreach ($result as $row)
		{
			static::delete($row['ID']);
		}
	}
}