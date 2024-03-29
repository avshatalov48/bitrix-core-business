<?php

namespace Bitrix\Mail\Internals;

use Bitrix\Main\Entity;
use Bitrix\Main\ORM;

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
	const DIR = 'DIR';
	const MAILBOX = 'MAILBOX';
	const MESSAGE = 'MESSAGE';

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
				'values' => array(self::DIR, self::MAILBOX, self::MESSAGE),
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