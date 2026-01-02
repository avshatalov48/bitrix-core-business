<?php

namespace Bitrix\Landing\Internals;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Entity;

Loc::loadMessages(__FILE__);

/**
 * Class HistoryStepTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_HistoryStep_Query query()
 * @method static EO_HistoryStep_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_HistoryStep_Result getById($id)
 * @method static EO_HistoryStep_Result getList(array $parameters = [])
 * @method static EO_HistoryStep_Entity getEntity()
 * @method static \Bitrix\Landing\Internals\EO_HistoryStep createObject($setDefaultValues = true)
 * @method static \Bitrix\Landing\Internals\EO_HistoryStep_Collection createCollection()
 * @method static \Bitrix\Landing\Internals\EO_HistoryStep wakeUpObject($row)
 * @method static \Bitrix\Landing\Internals\EO_HistoryStep_Collection wakeUpCollection($rows)
 */
class HistoryStepTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'b_landing_history_step';
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
				'title' => Loc::getMessage('LANDING_TABLE_HISTORYSTEP_FIELD_ENTITY_TYPE'),
				'required' => true,
			]),
			'ENTITY_ID' => new Entity\IntegerField('ENTITY_ID', [
				'title' => Loc::getMessage('LANDING_TABLE_HISTORYSTEP_FIELD_ENTITY_ID'),
				'required' => true,
			]),
			'STEP' => new Entity\IntegerField('STEP', [
				'title' => Loc::getMessage('LANDING_TABLE_HISTORYSTEP_FIELD_STEP'),
				'required' => true,
			]),
		];
	}
}