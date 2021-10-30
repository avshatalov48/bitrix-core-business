<?php
namespace Bitrix\Landing\Internals;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Entity;

Loc::loadMessages(__FILE__);

class UrlCheckerStatusTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_landing_urlchecker_status';
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
			'URL' => new Entity\StringField('URL', array(
				'title' => Loc::getMessage('LANDING_TABLE_UCS_FIELD_URL'),
				'required' => true
			)),
			'HASH' => new Entity\StringField('HASH', array(
				'title' => Loc::getMessage('LANDING_TABLE_UCS_FIELD_HASH'),
				'required' => true
			)),
			'STATUS' => new Entity\StringField('STATUS', array(
				'title' => Loc::getMessage('LANDING_TABLE_UCS_FIELD_STATUS')
			))
		);
	}
}