<?php
namespace Bitrix\Landing\Internals;

use \Bitrix\Main\Entity;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class BlockLastUsedTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_BlockLastUsed_Query query()
 * @method static EO_BlockLastUsed_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_BlockLastUsed_Result getById($id)
 * @method static EO_BlockLastUsed_Result getList(array $parameters = [])
 * @method static EO_BlockLastUsed_Entity getEntity()
 * @method static \Bitrix\Landing\Internals\EO_BlockLastUsed createObject($setDefaultValues = true)
 * @method static \Bitrix\Landing\Internals\EO_BlockLastUsed_Collection createCollection()
 * @method static \Bitrix\Landing\Internals\EO_BlockLastUsed wakeUpObject($row)
 * @method static \Bitrix\Landing\Internals\EO_BlockLastUsed_Collection wakeUpCollection($rows)
 */
class BlockLastUsedTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'b_landing_block_last_used';
	}

	/**
	 * Returns entity map definition.
	 * @return array
	 */
	public static function getMap(): array
	{
		return array(
			'ID' => new Entity\IntegerField('ID', array(
				'title' => 'ID',
				'primary' => true,
				'autocomplete' => true,
			)),
			'USER_ID' => new Entity\IntegerField('USER_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_LU_USER_ID'),
				'required' => true
			)),
			'CODE' => new Entity\StringField('CODE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_LU_CODE'),
				'required' => true
			)),
			'DATE_CREATE' => new Entity\DatetimeField('DATE_CREATE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_LU_DATE_CREATE')
			))
		);
	}
}
