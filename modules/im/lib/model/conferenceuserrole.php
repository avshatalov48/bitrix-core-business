<?php
namespace Bitrix\Im\Model;

use Bitrix\Main,
	Bitrix\Main\Entity;

/**
 * Class ConferenceUserRoleTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_ConferenceUserRole_Query query()
 * @method static EO_ConferenceUserRole_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_ConferenceUserRole_Result getById($id)
 * @method static EO_ConferenceUserRole_Result getList(array $parameters = array())
 * @method static EO_ConferenceUserRole_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_ConferenceUserRole createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_ConferenceUserRole_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_ConferenceUserRole wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_ConferenceUserRole_Collection wakeUpCollection($rows)
 */
class ConferenceUserRoleTable extends Main\Entity\DataManager
{
	public static function getTableName(): string
	{
		return 'b_im_conference_user_role';
	}

	public static function getMap(): array
	{
		return array(
			new Entity\IntegerField('CONFERENCE_ID', [
				'primary' => true
			]),
			new Entity\IntegerField('USER_ID', [
				'primary' => true
			]),
			new Entity\StringField('ROLE'),
		);
	}
}