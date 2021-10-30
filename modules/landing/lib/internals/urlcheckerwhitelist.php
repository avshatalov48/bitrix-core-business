<?php
namespace Bitrix\Landing\Internals;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Entity;

Loc::loadMessages(__FILE__);

class UrlCheckerWhitelistTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_landing_urlchecker_whitelist';
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
			'DOMAIN' => new Entity\StringField('DOMAIN', array(
				'title' => Loc::getMessage('LANDING_TABLE_UCWL_FIELD_DOMAIN'),
				'required' => true
			))
		);
	}
}