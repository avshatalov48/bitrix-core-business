<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Socialnetwork;

use Bitrix\Main\Entity;
use Bitrix\Main\NotImplementedException;

/**
 * Class UserWelltoryDisclaimerTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_UserWelltoryDisclaimer_Query query()
 * @method static EO_UserWelltoryDisclaimer_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_UserWelltoryDisclaimer_Result getById($id)
 * @method static EO_UserWelltoryDisclaimer_Result getList(array $parameters = [])
 * @method static EO_UserWelltoryDisclaimer_Entity getEntity()
 * @method static \Bitrix\Socialnetwork\EO_UserWelltoryDisclaimer createObject($setDefaultValues = true)
 * @method static \Bitrix\Socialnetwork\EO_UserWelltoryDisclaimer_Collection createCollection()
 * @method static \Bitrix\Socialnetwork\EO_UserWelltoryDisclaimer wakeUpObject($row)
 * @method static \Bitrix\Socialnetwork\EO_UserWelltoryDisclaimer_Collection wakeUpCollection($rows)
 */
class UserWelltoryDisclaimerTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sonet_user_welltory_disclaimer';
	}

	/**
	 * Returns entity map definition
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'USER_ID' => array(
				'data_type' => 'integer',
			),
			'USER' => array(
				'data_type' => 'Bitrix\Main\UserTable',
				'reference' => array('=this.USER_ID' => 'ref.ID'),
			),
			'DATE_SIGNED' => array(
				'data_type' => 'datetime'
			),
		);
	}
}
