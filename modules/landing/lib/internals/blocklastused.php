<?php
namespace Bitrix\Landing\Internals;

use \Bitrix\Main\Entity;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

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
				'primary' => true
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
