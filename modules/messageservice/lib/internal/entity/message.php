<?php
namespace Bitrix\MessageService\Internal\Entity;

use Bitrix\Main\Application;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\ArrayField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\Type\DateTime;

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
class MessageTable extends DataManager
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
		return [
			'ID' =>
				(new IntegerField('ID', []))
					->configurePrimary(true)
					->configureAutocomplete(true)
			,
			'TYPE' =>
				(new StringField('TYPE', [
					'validation' => [__CLASS__, 'validateType']
				]))
					->configureRequired(true)
			,
			'SENDER_ID' =>
				(new StringField('SENDER_ID', [
					'validation' => [__CLASS__, 'validateSenderId']
				]))
					->configureRequired(true)
			,
			'AUTHOR_ID' => (new IntegerField('AUTHOR_ID',
				[]
			))
				->configureDefaultValue(0),
			'MESSAGE_FROM' =>
				(new StringField('MESSAGE_FROM', [
					'validation' => [__CLASS__, 'validateMessageFrom']
				]))
			,
			'MESSAGE_TO' =>
				(new StringField('MESSAGE_TO', [
					'validation' => [__CLASS__, 'validateMessageTo']
				]))
					->configureRequired(true)
			,
			'MESSAGE_HEADERS' =>
				(new ArrayField('MESSAGE_HEADERS', []))
					->configureSerializationPhp()
			,
			'MESSAGE_BODY' =>
				(new TextField('MESSAGE_BODY', []))
					->configureRequired(true)
			,
			'DATE_INSERT' =>
				(new DatetimeField('DATE_INSERT',	[]))
			,
			'DATE_EXEC' =>
				(new DatetimeField('DATE_EXEC', []))
			,
			'NEXT_EXEC' =>
				(new DatetimeField('NEXT_EXEC', []))
			,
			'SUCCESS_EXEC' =>
				(new StringField('SUCCESS_EXEC', []))
					->configureDefaultValue('N')
			,
			'EXEC_ERROR' =>
				(new StringField('EXEC_ERROR', [
					'validation' => [__CLASS__, 'validateExecError']
				]))
			,
			'STATUS_ID' =>
				(new IntegerField('STATUS_ID', []))
					->configureDefaultValue(0)
			,
			'EXTERNAL_ID' =>
				(new StringField('EXTERNAL_ID', [
					'validation' => [__CLASS__, 'validateExternalId']
				]))
			,
			'EXTERNAL_STATUS' =>
				(new StringField('EXTERNAL_STATUS', [
					'validation' => [__CLASS__, 'validateExternalStatus']
				]))
			,
			'CLUSTER_GROUP' =>
				(new IntegerField('CLUSTER_GROUP', []))
			,
		];
	}

	public static function getByExternalId(string $senderId, string $externalId, ?string $from = null)
	{
		return MessageTable::getList([
			'filter' => [
				'=SENDER_ID' => $senderId,
				'=EXTERNAL_ID' => $externalId,
			],
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

		$connection->query("
			UPDATE
				{$tableName}
			SET
				STATUS_ID = {$newStatusId}
			WHERE
				ID = $id
				AND STATUS_ID != {$newStatusId}
		");

		return $connection->getAffectedRowsCount() === 1;
	}

	public static function updateMessageStatuses($id, $newInternalStatusId, $newExternalStatus): bool
	{
		$connection = Application::getConnection();
		$tableName = static::getTableName();

		$newExternalStatus = $connection->getSqlHelper()->forSql($newExternalStatus);

		$connection->query("
			UPDATE
				{$tableName}
			SET
				STATUS_ID = {$newInternalStatusId}, 
				EXTERNAL_STATUS = '{$newExternalStatus}'
			WHERE
				ID = {$id}
				AND STATUS_ID < {$newInternalStatusId}
		");

		return $connection->getAffectedRowsCount() === 1;
	}

	public static function getDailyCount($senderId, $fromId): int
	{
		$today = (new DateTime)->setTime(0, 0, 0);

		return self::getCount([
			'=SUCCESS_EXEC' => 'Y',
			'>=DATE_EXEC' => $today,
			'=SENDER_ID' => $senderId,
			'=MESSAGE_FROM' => $fromId,
		]);
	}

	public static function getAllDailyCount(): array
	{
		$today = (new DateTime)->setTime(0, 0, 0);

		$result = self::getList([
			'runtime' => [new ExpressionField('CNT', 'COUNT(*)')],
			'select' => [
				'SENDER_ID', 'MESSAGE_FROM', 'CNT'
			],
			'filter' => [
				'=SUCCESS_EXEC' => 'Y',
				'>=DATE_EXEC' => $today,
			],
			'group' => ['SENDER_ID', 'MESSAGE_FROM'],
		]);

		$counts = [];
		while ($row = $result->fetch())
		{
			$id = $row['SENDER_ID'] .':'. $row['MESSAGE_FROM'];
			$counts[$id] = (int)$row['CNT'];
		}

		return $counts;
	}

	public static function returnDeferredToQueue($senderId, $fromId): bool
	{
		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();

		$senderId = $helper->forSql((string)$senderId);
		$fromId = $helper->forSql((string)$fromId);

		$connection->queryExecute("
			UPDATE b_messageservice_message 
			SET NEXT_EXEC = NULL 
			WHERE 
				SUCCESS_EXEC = 'N' 
				AND NEXT_EXEC IS NOT NULL 
				AND SENDER_ID = '{$senderId}'
				AND MESSAGE_FROM = '{$fromId}'
		");

		return true;
	}

	/**
	 * Returns validators for TYPE field.
	 *
	 * @return array
	 */
	public static function validateType(): array
	{
		return [
			new LengthValidator(null, 30),
		];
	}

	/**
	 * Returns validators for SENDER_ID field.
	 *
	 * @return array
	 */
	public static function validateSenderId(): array
	{
		return [
			new LengthValidator(null, 50),
		];
	}

	/**
	 * Returns validators for MESSAGE_FROM field.
	 *
	 * @return array
	 */
	public static function validateMessageFrom(): array
	{
		return [
			new LengthValidator(null, 260),
		];
	}

	/**
	 * Returns validators for MESSAGE_TO field.
	 *
	 * @return array
	 */
	public static function validateMessageTo(): array
	{
		return [
			new LengthValidator(null, 50),
		];
	}

	/**
	 * Returns validators for EXEC_ERROR field.
	 *
	 * @return array
	 */
	public static function validateExecError(): array
	{
		return [
			new LengthValidator(null, 255),
		];
	}

	/**
	 * Returns validators for EXTERNAL_ID field.
	 *
	 * @return array
	 */
	public static function validateExternalId(): array
	{
		return [
			new LengthValidator(null, 128),
		];
	}

	/**
	 * Returns validators for EXTERNAL_STATUS field.
	 *
	 * @return array
	 */
	public static function validateExternalStatus(): array
	{
		return [
			new LengthValidator(null, 128),
		];
	}
}