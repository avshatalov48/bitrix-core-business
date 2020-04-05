<?php
namespace Bitrix\Landing\Internals;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Entity;

Loc::loadMessages(__FILE__);

class FilterBlockTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_landing_filter_block';
	}

	/**
	 * Returns entity map definition.
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => new Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
				'title' => 'ID'
			)),
			'FILTER_ID' => new Entity\IntegerField('FILTER_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_FILTER_ID'),
				'required' => true
			)),
			'BLOCK_ID' => new Entity\IntegerField('BLOCK_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_BLOCK_ID'),
				'required' => true
			))
		);
	}
}