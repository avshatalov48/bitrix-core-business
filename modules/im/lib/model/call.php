<?php

namespace Bitrix\Im\Model;

use Bitrix\Disk\Internals\Entity\Query;
use Bitrix\Im\Call\Call;
use Bitrix\Main\Application;
use Bitrix\Main\Entity;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;

class CallTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_im_call';
	}

	public static function getMap()
	{
		return array(
			new Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true
			)),
			new Entity\IntegerField('TYPE'),
			new Entity\IntegerField('INITIATOR_ID'),
			new Entity\StringField('IS_PUBLIC', array(
				'default_value' => 'N'
			)),
			new Entity\StringField('PUBLIC_ID'),
			new Entity\StringField('PROVIDER'),
			new Entity\StringField('ENTITY_TYPE'),
			new Entity\StringField('ENTITY_ID'),
			new Entity\IntegerField('PARENT_ID'),
			new Entity\StringField('STATE'),
			new Entity\DatetimeField('START_DATE', array(
				'default_value' => function()
				{
					return new DateTime();
				}
			)),
			new Entity\DatetimeField('END_DATE'),
			new Entity\IntegerField('CHAT_ID'),
			new Entity\StringField('LOG_URL'),
		);
	}

	/**
	 * Updates call state in the database. Returns true if state was changed by the update and false otherwise.
	 * @param int $id Id of the call.
	 * @param string $newState New call state
	 * @return bool
	 */
	public static function updateState(int $id, string $newState) : bool
	{
		$connection = Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();
		$tableName = static::getTableName();
		$newState = $sqlHelper->forSql($newState);

		$update = "STATE = '$newState'";
		if($newState === Call::STATE_FINISHED)
		{
			$update .= ", END_DATE = CURRENT_TIMESTAMP";
		}

		$query = "
			UPDATE
				$tableName
			SET
				$update
			WHERE
				ID = $id
				AND STATE != '$newState'
		";

		$connection->query($query);
		return $connection->getAffectedRowsCount() === 1;
	}

}