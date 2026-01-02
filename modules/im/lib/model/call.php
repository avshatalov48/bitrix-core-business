<?php

namespace Bitrix\Im\Model;

use Bitrix\Im\Call\Call;
use Bitrix\Main\Application;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\EnumField;
use Bitrix\Main\ORM\Fields\Relations\Reference;

/**
 * Class CallTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Call_Query query()
 * @method static EO_Call_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Call_Result getById($id)
 * @method static EO_Call_Result getList(array $parameters = [])
 * @method static EO_Call_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_Call createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_Call_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_Call wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_Call_Collection wakeUpCollection($rows)
 */
class CallTable extends DataManager
{
	public static function getTableName(): string
	{
		return 'b_im_call';
	}

	public static function getMap(): array
	{
		return [
			(new IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete(),

			new IntegerField('TYPE'),

			(new EnumField('SCHEME'))
				->configureValues([Call::SCHEME_CLASSIC, Call::SCHEME_JWT])
				->configureDefaultValue(Call::SCHEME_CLASSIC)
				->configureNullable(),

			new IntegerField('INITIATOR_ID'),

			(new StringField('IS_PUBLIC'))
				->configureDefaultValue('N'),

			new StringField('PUBLIC_ID'),

			new StringField('PROVIDER'),

			new StringField('ENTITY_TYPE'),

			new StringField('ENTITY_ID'),

			new IntegerField('PARENT_ID'),

			(new StringField('PARENT_UUID'))
				->configureSize(36),

			new StringField('STATE'),

			(new DatetimeField('START_DATE'))
				->configureDefaultValue(fn() => new DateTime()),

			new DatetimeField('END_DATE'),

			new IntegerField('CHAT_ID'),

			new StringField('LOG_URL'),

			(new StringField('UUID'))
				->configureSize(36),

			(new StringField('SECRET_KEY'))
				->configureSize(10),

			new StringField('ENDPOINT'),

			(new BooleanField('RECORD_AUDIO'))
				->configureValues('N', 'Y')
				->configureDefaultValue('N'),

			(new BooleanField('AI_ANALYZE'))
				->configureValues('N', 'Y')
				->configureDefaultValue('N'),

			(new Reference('CALL_USER',CallUserTable::class,
				Join::on('this.ID', 'ref.CALL_ID')))
				->configureJoinType(Join::TYPE_INNER),
		];
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