<?php

namespace Bitrix\Landing\Internals;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Entity;

Loc::loadMessages(__FILE__);

/**
 * Class HistoryTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_History_Query query()
 * @method static EO_History_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_History_Result getById($id)
 * @method static EO_History_Result getList(array $parameters = [])
 * @method static EO_History_Entity getEntity()
 * @method static \Bitrix\Landing\Internals\EO_History createObject($setDefaultValues = true)
 * @method static \Bitrix\Landing\Internals\EO_History_Collection createCollection()
 * @method static \Bitrix\Landing\Internals\EO_History wakeUpObject($row)
 * @method static \Bitrix\Landing\Internals\EO_History_Collection wakeUpCollection($rows)
 */
class HistoryTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'b_landing_history';
	}

	/**
	 * Returns entity map definition.
	 * @return array
	 */
	public static function getMap(): array
	{
		return [
			'ID' => new Entity\IntegerField('ID', [
				'primary' => true,
				'autocomplete' => true,
				'title' => 'ID',
			]),
			'ENTITY_TYPE' => new Entity\StringField('ENTITY_TYPE', [
				'title' => Loc::getMessage('LANDING_TABLE_HISTORY_FIELD_ENTITY_TYPE'),
				'required' => true,
			]),
			'ENTITY_ID' => new Entity\IntegerField('ENTITY_ID', [
				'title' => Loc::getMessage('LANDING_TABLE_HISTORY_FIELD_ENTITY_ID'),
				'required' => true,
			]),
			'ACTION' => new Entity\TextField('ACTION', [
				'title' => Loc::getMessage('LANDING_TABLE_HISTORY_FIELD_ACTION'),
				'required' => true,
			]),
			'ACTION_PARAMS' => new Entity\TextField('ACTION_PARAMS', [
				'title' => Loc::getMessage('LANDING_TABLE_HISTORY_FIELD_ACTION_PARAMS'),
				'required' => true,
				'serialized' => true,
			]),
			'MULTIPLY_ID' => new Entity\IntegerField('MULTIPLY_ID', [
				'title' => Loc::getMessage('LANDING_TABLE_HISTORY_FIELD_MULTIPLY_ID'),
			]),
			'CREATED_BY_ID' => new Entity\IntegerField('CREATED_BY_ID', [
				'title' => Loc::getMessage('LANDING_TABLE_HISTORY_FIELD_CREATED_BY_ID'),
				'required' => true,
			]),
			'DATE_CREATE' => new Entity\DatetimeField('DATE_CREATE', [
				'title' => Loc::getMessage('LANDING_TABLE_HISTORY_FIELD_DATE_CREATE'),
				'required' => true,
			]),
		];
	}
}