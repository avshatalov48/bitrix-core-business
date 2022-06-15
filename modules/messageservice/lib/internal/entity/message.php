<?php
namespace Bitrix\MessageService\Internal\Entity;

use Bitrix\Main\Application;
use Bitrix\Main\Entity;
use Bitrix\Main\Type;

/**
 * Class MessageTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Message_Query query()
 * @method static EO_Message_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Message_Result getById($id)
 * @method static EO_Message_Result getList(array $parameters = array())
 * @method static EO_Message_Entity getEntity()
 * @method static \Bitrix\MessageService\Internal\Entity\EO_Message createObject($setDefaultValues = true)
 * @method static \Bitrix\MessageService\Internal\Entity\EO_Message_Collection createCollection()
 * @method static \Bitrix\MessageService\Internal\Entity\EO_Message wakeUpObject($row)
 * @method static \Bitrix\MessageService\Internal\Entity\EO_Message_Collection wakeUpCollection($rows)
 */
class MessageTable extends Entity\DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_messageservice_message';
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'TYPE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateVarchar30'),
			),
			'SENDER_ID' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateVarchar50'),
			),
			'AUTHOR_ID' => array(
				'data_type' => 'integer',
				'default_value' => 0,
			),
			'AUTHOR' => array(
				'data_type' => '\Bitrix\Main\UserTable',
				'reference' => array(
					'=this.AUTHOR_ID' => 'ref.ID'
				),
				'join_type' => 'LEFT',
			),
			'MESSAGE_FROM' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateVarchar260'),
			),
			'MESSAGE_TO' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateVarchar50'),
			),
			'MESSAGE_HEADERS' => array(
				'data_type' => 'string',
				'serialized' => true
			),
			'MESSAGE_BODY' => array(
				'data_type' => 'string',
				'required' => true
			),
			'DATE_INSERT' => array(
				'data_type' => 'datetime',
				'default_value' => new Type\DateTime(),
			),
			'DATE_EXEC' => array(
				'data_type' => 'datetime'
			),
			'NEXT_EXEC' => array(
				'data_type' => 'datetime'
			),
			'SUCCESS_EXEC' => array(
				'data_type' => 'string',
				'default_value' => 'N',
			),
			'EXEC_ERROR' => array(
				'data_type' => 'string'
			),
			'STATUS_ID' => array(
				'data_type' => 'integer'
			),
			'EXTERNAL_ID' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateVarchar128'),
			),
			'EXTERNAL_STATUS' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateVarchar128'),
			)
		);
	}

	public static function getByExternalId(string $senderId, string $externalId, ?string $from = null)
	{
		$filter = [
			'=SENDER_ID' => $senderId,
			'=EXTERNAL_ID' => $externalId,
		];


		return MessageTable::getList([
			'filter' => $filter,
			'limit' => 1
		]);
	}

	/**
	 * Updates message to the new status and returns result of update.
	 *
	 * @param int $id Id of the message.
	 * @param int $newStatusId New status id.
	 * @return bool True if updated successfully and false otherwise (for example, if the message already was in this status).
	 */
	public static function updateStatusId(int $id, int $newStatusId): bool
	{
		$connection = Application::getConnection();
		$tableName = static::getTableName();

		$update = "STATUS_ID = {$newStatusId}";

		$query = "
			UPDATE
				$tableName
			SET
				$update
			WHERE
				ID = $id
				AND STATUS_ID != {$newStatusId}
		";

		$connection->query($query);
		return $connection->getAffectedRowsCount() === 1;
	}

	public static function getDailyCount($senderId, $fromId)
	{
		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();
		$today = date('Y-m-d') . ' 00:00:00';

		$senderId = $helper->forSql((string)$senderId);
		$fromId = $helper->forSql((string)$fromId);

		$strSql = "SELECT COUNT(*) CNT
			FROM b_messageservice_message
			WHERE SUCCESS_EXEC = 'Y'
			AND DATE_EXEC >= '{$today}'
			AND SENDER_ID = '{$senderId}'
			AND MESSAGE_FROM = '{$fromId}'";

		$result = $connection->query($strSql)->fetch();
		return is_array($result) ? (int)$result['CNT'] : 0;
	}

	public static function getAllDailyCount()
	{
		$connection = Application::getConnection();
		$today = date('Y-m-d') . ' 00:00:00';

		$strSql = "SELECT SENDER_ID, MESSAGE_FROM, COUNT(*) CNT
			FROM b_messageservice_message
			WHERE SUCCESS_EXEC = 'Y'
			AND DATE_EXEC >= '{$today}'
			GROUP BY SENDER_ID, MESSAGE_FROM";

		$result = $connection->query($strSql);
		$counts = array();

		while ($row = $result->fetch())
		{
			$id = $row['SENDER_ID'] .':'. $row['MESSAGE_FROM'];
			$counts[$id] = (int)$row['CNT'];
		}

		return $counts;
	}

	public static function returnDeferredToQueue($senderId, $fromId)
	{
		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();

		$senderId = $helper->forSql((string)$senderId);
		$fromId = $helper->forSql((string)$fromId);

		$strSql = "UPDATE b_messageservice_message SET NEXT_EXEC = NULL 
			WHERE SUCCESS_EXEC = 'N' AND NEXT_EXEC IS NOT NULL 
			AND SENDER_ID = '{$senderId}'
			AND MESSAGE_FROM = '{$fromId}'";

		$connection->query($strSql);

		return true;
	}

	/**
	 * @return array
	 */
	public static function validateVarchar50()
	{
		return array(
			new Entity\Validator\Length(null, 50),
		);
	}

	/**
	 * @return array
	 */
	public static function validateVarchar260()
	{
		return array(
			new Entity\Validator\Length(null, 260),
		);
	}

	/**
	 * @return array
	 */
	public static function validateVarchar30()
	{
		return array(
			new Entity\Validator\Length(null, 30),
		);
	}

	/**
	 * @return array
	 */
	public static function validateVarchar128()
	{
		return array(
			new Entity\Validator\Length(null, 128),
		);
	}
}