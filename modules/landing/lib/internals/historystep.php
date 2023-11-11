<?php

namespace Bitrix\Landing\Internals;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Entity;

Loc::loadMessages(__FILE__);

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