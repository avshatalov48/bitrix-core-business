<?php
namespace Bitrix\Landing\Internals;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Entity;

Loc::loadMessages(__FILE__);

/**
 * Class UrlCheckerWhitelistTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_UrlCheckerWhitelist_Query query()
 * @method static EO_UrlCheckerWhitelist_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_UrlCheckerWhitelist_Result getById($id)
 * @method static EO_UrlCheckerWhitelist_Result getList(array $parameters = [])
 * @method static EO_UrlCheckerWhitelist_Entity getEntity()
 * @method static \Bitrix\Landing\Internals\EO_UrlCheckerWhitelist createObject($setDefaultValues = true)
 * @method static \Bitrix\Landing\Internals\EO_UrlCheckerWhitelist_Collection createCollection()
 * @method static \Bitrix\Landing\Internals\EO_UrlCheckerWhitelist wakeUpObject($row)
 * @method static \Bitrix\Landing\Internals\EO_UrlCheckerWhitelist_Collection wakeUpCollection($rows)
 */
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