<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\Authentication\Internal;

use Bitrix\Main;
use Bitrix\Main\ORM\Data;
use Bitrix\Main\ORM\Fields;

/**
 * Class UserDeviceTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_UserDevice_Query query()
 * @method static EO_UserDevice_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_UserDevice_Result getById($id)
 * @method static EO_UserDevice_Result getList(array $parameters = [])
 * @method static EO_UserDevice_Entity getEntity()
 * @method static \Bitrix\Main\Authentication\Internal\EO_UserDevice createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\Authentication\Internal\EO_UserDevice_Collection createCollection()
 * @method static \Bitrix\Main\Authentication\Internal\EO_UserDevice wakeUpObject($row)
 * @method static \Bitrix\Main\Authentication\Internal\EO_UserDevice_Collection wakeUpCollection($rows)
 */
class UserDeviceTable extends Data\DataManager
{
	use Data\Internal\DeleteByFilterTrait;

	public static function getTableName()
	{
		return 'b_user_device';
	}

	public static function getMap()
	{
		return [
			(new Fields\IntegerField('ID'))
				->configurePrimary(true)
				->configureAutocomplete(true),

			(new Fields\IntegerField('USER_ID'))
				->addValidator(new Fields\Validators\ForeignValidator(Main\UserTable::getEntity()->getField('ID'))),

			(new Fields\StringField('DEVICE_UID')),

			(new Fields\IntegerField('DEVICE_TYPE')),

			(new Fields\StringField('BROWSER')),

			(new Fields\StringField('PLATFORM')),

			(new Fields\TextField('USER_AGENT')),

			(new Fields\BooleanField('COOKABLE'))
				->configureValues('N', 'Y')
				->configureDefaultValue('N'),
		];
	}

	public static function onDelete(\Bitrix\Main\ORM\Event $event)
	{
		$id = $event->getParameter('id');

		UserDeviceLoginTable::deleteByFilter(['=DEVICE_ID' => $id]);
	}

	protected static function onBeforeDeleteByFilter(string $where)
	{
		UserDeviceLoginTable::deleteByDeviceFilter($where);
	}
}
