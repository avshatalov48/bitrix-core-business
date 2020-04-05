<?php

namespace Bitrix\Mail\Internals;

use Bitrix\Main;
use Bitrix\Main\ORM;
use Bitrix\Mail;

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
