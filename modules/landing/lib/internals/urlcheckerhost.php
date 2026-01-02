<?php
namespace Bitrix\Landing\Internals;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Entity;

Loc::loadMessages(__FILE__);

/**
 * Class UrlCheckerHostTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_UrlCheckerHost_Query query()
 * @method static EO_UrlCheckerHost_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_UrlCheckerHost_Result getById($id)
 * @method static EO_UrlCheckerHost_Result getList(array $parameters = [])
 * @method static EO_UrlCheckerHost_Entity getEntity()
 * @method static \Bitrix\Landing\Internals\EO_UrlCheckerHost createObject($setDefaultValues = true)
 * @method static \Bitrix\Landing\Internals\EO_UrlCheckerHost_Collection createCollection()
 * @method static \Bitrix\Landing\Internals\EO_UrlCheckerHost wakeUpObject($row)
 * @method static \Bitrix\Landing\Internals\EO_UrlCheckerHost_Collection wakeUpCollection($rows)
 */
class UrlCheckerHostTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_landing_urlchecker_host';
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
			'STATUS_ID' => new Entity\IntegerField('STATUS_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_UCH_FIELD_STATUS_ID'),
				'required' => true
			)),
			'HOST' => new Entity\StringField('HOST', array(
				'title' => Loc::getMessage('LANDING_TABLE_UCH_FIELD_HOST'),
				'required' => true
			))
		);
	}
}
