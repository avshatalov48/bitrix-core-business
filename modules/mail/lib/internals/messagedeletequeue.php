<?php

namespace Bitrix\Mail\Internals;

use Bitrix\Main;
use Bitrix\Main\ORM;
use Bitrix\Mail;

/**
 * Class MessageDeleteQueueTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_MessageDeleteQueue_Query query()
 * @method static EO_MessageDeleteQueue_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_MessageDeleteQueue_Result getById($id)
 * @method static EO_MessageDeleteQueue_Result getList(array $parameters = array())
 * @method static EO_MessageDeleteQueue_Entity getEntity()
 * @method static \Bitrix\Mail\Internals\EO_MessageDeleteQueue createObject($setDefaultValues = true)
 * @method static \Bitrix\Mail\Internals\EO_MessageDeleteQueue_Collection createCollection()
 * @method static \Bitrix\Mail\Internals\EO_MessageDeleteQueue wakeUpObject($row)
 * @method static \Bitrix\Mail\Internals\EO_MessageDeleteQueue_Collection wakeUpCollection($rows)
 */
class MessageDeleteQueueTable extends ORM\Data\DataManager
{

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_mail_message_delete_queue';
	}

	public static function deleteList(array $filter)
	{
		$entity = static::getEntity();
		$connection = $entity->getConnection();

		return $connection->query(sprintf(
			'DELETE FROM %s WHERE %s',
			$connection->getSqlHelper()->quote($entity->getDbTableName()),
			ORM\Query\Query::buildFilterSql($entity, $filter)
		));
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary'   => true,
				'autocomplete' => true,
			),
			'MAILBOX_ID' => array(
				'data_type' => 'integer',
				'required'  => true,
			),
			'MESSAGE_ID' => array(
				'data_type' => 'integer',
				'required'  => true,
			),
		);
	}

}
