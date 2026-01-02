<?php

namespace Bitrix\Mail\Internals;

use Bitrix\Main\Entity;
use Bitrix\Main\ORM;
use Bitrix\Main\Type\DateTime;

/**
 * Class MailEntityOptionsTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_MailEntityOptions_Query query()
 * @method static EO_MailEntityOptions_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_MailEntityOptions_Result getById($id)
 * @method static EO_MailEntityOptions_Result getList(array $parameters = [])
 * @method static EO_MailEntityOptions_Entity getEntity()
 * @method static \Bitrix\Mail\Internals\EO_MailEntityOptions createObject($setDefaultValues = true)
 * @method static \Bitrix\Mail\Internals\EO_MailEntityOptions_Collection createCollection()
 * @method static \Bitrix\Mail\Internals\EO_MailEntityOptions wakeUpObject($row)
 * @method static \Bitrix\Mail\Internals\EO_MailEntityOptions_Collection wakeUpCollection($rows)
 */
class MailEntityOptionsTable extends Entity\DataManager
{
	const DIR_TYPE_NAME = 'DIR';
	const MAILBOX_TYPE_NAME = 'MAILBOX';
	const MESSAGE_TYPE_NAME = 'MESSAGE';

	const CONNECT_ERROR_ATTEMPT_COUNT_PROPERTY_NAME = 'CONNECT_ERROR_ATTEMPT_COUNT';
	const SYNC_STATUS_PROPERTY_NAME = 'SYNC_STATUS';

	public static function add($fields)
	{
		try {
			return parent::add($fields);
		} catch (\Exception $exception)
		{
			//key conflict
		}
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

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_mail_entity_options';
	}

	public static function insertIgnore(
		int $mailboxId,
		int $entityId,
		string $entityType,
		string $propertyName,
		string $value,
		DateTime $dataInsert = new DateTime()
	): void
	{
		$connection = self::getEntity()->getConnection();
		$sqlHelper = $connection->getSqlHelper();

		[$columns, $insert] = $sqlHelper->prepareInsert(self::getTableName(),
			[
				'MAILBOX_ID' => $mailboxId,
				'ENTITY_TYPE' => $entityType,
				'ENTITY_ID' => $entityId,
				'PROPERTY_NAME' => $propertyName,
				'DATE_INSERT' => $dataInsert,
				'VALUE' => $value,
			]
		);

		$connection->queryExecute(
			$sqlHelper->getInsertIgnore(
				MailEntityOptionsTable::getTableName(),
				"($columns)",
				"VALUES($insert)"
			)
		);
	}

	public static function getMap()
	{
		return array(
			'MAILBOX_ID' => array(
				'data_type' => 'integer',
				'required'  => true,
				'primary' => true,
			),
			'ENTITY_TYPE' => array(
				'data_type' => 'enum',
				'values' => array(self::DIR_TYPE_NAME, self::MAILBOX_TYPE_NAME, self::MESSAGE_TYPE_NAME),
				'required'  => true,
				'primary' => true,
			),
			'ENTITY_ID' => array(
				'data_type' => 'string',
				'required'  => true,
				'primary' => true,
			),
			'PROPERTY_NAME' => array(
				'data_type' => 'string',
				'required'  => true,
				'primary' => true,
			),
			'VALUE' => array(
				'data_type' => 'string',
			),
			'DATE_INSERT' => array(
				'data_type' => 'datetime',
			),
		);
	}
}