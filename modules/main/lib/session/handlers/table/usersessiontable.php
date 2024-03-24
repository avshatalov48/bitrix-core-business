<?php
namespace Bitrix\Main\Session\Handlers\Table;


use Bitrix\Main\Application;
use Bitrix\Main\DB\MysqlCommonConnection;
use Bitrix\Main\DB\PgsqlConnection;
use Bitrix\Main\Entity;
use Bitrix\Main\Type;

/**
 * Class UserSessionTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_UserSession_Query query()
 * @method static EO_UserSession_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_UserSession_Result getById($id)
 * @method static EO_UserSession_Result getList(array $parameters = [])
 * @method static EO_UserSession_Entity getEntity()
 * @method static \Bitrix\Main\Session\Handlers\Table\EO_UserSession createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\Session\Handlers\Table\EO_UserSession_Collection createCollection()
 * @method static \Bitrix\Main\Session\Handlers\Table\EO_UserSession wakeUpObject($row)
 * @method static \Bitrix\Main\Session\Handlers\Table\EO_UserSession_Collection wakeUpCollection($rows)
 */
class UserSessionTable extends Entity\DataManager
{
	/** @var string Connection name used for SQL queries */
	public const CONNECTION_NAME = 'user_session';

	/**
	 * {@inheritdoc}
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_user_session';
	}

	/**
	 * Returns connection name for entity
	 * Have side affect, keep it in mind!
	 * Clone default database connection if connection {@link SessionTable::CONNECTION_NAME} doesn't exists
	 *
	 * @return string
	 */
	public static function getConnectionName()
	{
		$pool = Application::getInstance()->getConnectionPool();
		$isConnectionExists = $pool->getConnection(static::CONNECTION_NAME) !== null;
		if (!$isConnectionExists)
		{
			$pool->cloneConnection(
				$pool::DEFAULT_CONNECTION_NAME,
				static::CONNECTION_NAME
			);
		}

		return static::CONNECTION_NAME;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			new Entity\StringField('SESSION_ID', [
				'primary' => true,
				'format' => '#^[0-9a-z\-,]{6,250}$#iD'
			]),
			new Entity\DatetimeField('TIMESTAMP_X', [
				'default_value' => new Type\DateTime
			]),
			new Entity\TextField('SESSION_DATA', [
				'default_value' => '',
				'save_data_modification' => function() {
					return [
						function($data) {
							return base64_encode($data);
						}
					];
				},
				'fetch_data_modification' => function() {
					return [
						function($data) {
							return base64_decode($data);
						}
					];
				},
			])
		];
	}

	/**
	 * Locks specified session id
	 *
	 * @param string $id Session id.
	 * @param int $timeout Lock timeout.
	 * @return bool Returns true if lock occurred.
	 */
	public static function lock($id, $timeout = 60)
	{
		$result = true;

		$pool = Application::getInstance()->getConnectionPool();
		$pool->useMasterOnly(true);

		$connection = static::getEntity()->getConnection();
		if (
			$connection instanceof MysqlCommonConnection
			|| $connection instanceof PgsqlConnection
		)
		{
			$result = $connection->lock($id, (int)$timeout);
		}
		else
		{
			trigger_error(sprintf('SessionTable::lock not supported for connection of type "%s"', get_class($connection)), E_USER_WARNING);
		}

		$pool->useMasterOnly(false);

		return $result;
	}

	/**
	 * Unlock specified session id
	 *
	 * @param string $id Session id.
	 * @return bool Returns true if lock released.
	 */
	public static function unlock($id)
	{
		$pool = Application::getInstance()->getConnectionPool();
		$pool->useMasterOnly(true);

		$connection = static::getEntity()->getConnection();
		if (
			$connection instanceof MysqlCommonConnection
			|| $connection instanceof PgsqlConnection
		)
		{
			$connection->unlock($id);
		}
		else
		{
			trigger_error(sprintf('SessionTable::unlock not supported for connection of type "%s"', get_class($connection)), E_USER_WARNING);
		}

		$pool->useMasterOnly(false);

		return true;
	}

	/**
	 * Deletes old sessions
	 *
	 * @param int $sec Seconds.
	 * @return void
	 */
	public static function deleteOlderThan($sec)
	{
		$pool = Application::getInstance()->getConnectionPool();
		$pool->useMasterOnly(true);

		$tableName = static::getTableName();
		$connection = static::getEntity()->getConnection();
		$connection->queryExecute(
			sprintf("delete from {$tableName} where TIMESTAMP_X < %s",
				$connection->getSqlHelper()->addSecondsToDateTime('-'.$sec))
		);
		$pool->useMasterOnly(false);
	}
}
