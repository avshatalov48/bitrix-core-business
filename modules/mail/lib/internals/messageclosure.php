<?php

namespace Bitrix\Mail\Internals;

use Bitrix\Main\Entity;

/**
 * Class MessageClosureTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_MessageClosure_Query query()
 * @method static EO_MessageClosure_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_MessageClosure_Result getById($id)
 * @method static EO_MessageClosure_Result getList(array $parameters = array())
 * @method static EO_MessageClosure_Entity getEntity()
 * @method static \Bitrix\Mail\Internals\EO_MessageClosure createObject($setDefaultValues = true)
 * @method static \Bitrix\Mail\Internals\EO_MessageClosure_Collection createCollection()
 * @method static \Bitrix\Mail\Internals\EO_MessageClosure wakeUpObject($row)
 * @method static \Bitrix\Mail\Internals\EO_MessageClosure_Collection wakeUpCollection($rows)
 */
class MessageClosureTable extends Entity\DataManager
{

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_mail_message_closure';
	}

	public static function getMap()
	{
		return array(
			'MESSAGE_ID' => array(
				'data_type' => 'integer',
				'primary'   => true,
			),
			'PARENT_ID' => array(
				'data_type' => 'integer',
				'primary'   => true,
			),
		);
	}

	/**
	 * Insert ignore with raw sql
	 *
	 * @param string $fromSelect Raw Sql
	 *
	 * @return int Affected rows
	 */
	public static function insertIgnoreFromSelect(string $fromSelect): int
	{
		return self::insertIgnoreFromSql("($fromSelect)");
	}

	/**
	 * Insert ignore from select
	 *
	 * @param string $sql Sql for insert (values(), or select)
	 *
	 * @return int Affected rows
	 */
	public static function insertIgnoreFromSql(string $sql): int
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$sql = $connection->getSqlHelper()
			->getInsertIgnore(self::getTableName(), ' (MESSAGE_ID, PARENT_ID) ', $sql);
		$connection->query($sql);

		return $connection->getAffectedRowsCount();
	}

}
