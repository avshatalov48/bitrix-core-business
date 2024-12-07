<?php
namespace Bitrix\Main;

/**
 * Class UserFieldConfirmTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> USER_ID int mandatory
 * <li> DATE_CHANGE datetime mandatory default 'CURRENT_TIMESTAMP'
 * <li> FIELD string(255) mandatory
 * <li> FIELD_VALUE string(255) mandatory
 * <li> CONFIRM_CODE string(32) mandatory
 * <li> ATTEMPTS int
 * </ul>
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_UserFieldConfirm_Query query()
 * @method static EO_UserFieldConfirm_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_UserFieldConfirm_Result getById($id)
 * @method static EO_UserFieldConfirm_Result getList(array $parameters = [])
 * @method static EO_UserFieldConfirm_Entity getEntity()
 * @method static \Bitrix\Main\EO_UserFieldConfirm createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\EO_UserFieldConfirm_Collection createCollection()
 * @method static \Bitrix\Main\EO_UserFieldConfirm wakeUpObject($row)
 * @method static \Bitrix\Main\EO_UserFieldConfirm_Collection wakeUpCollection($rows)
 */

class UserFieldConfirmTable extends Entity\DataManager
{
	const MAX_ATTEMPTS_COUNT = 3;
	public static function getTableName()
	{
		return 'b_user_field_confirm';
	}

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
				'required' => true,
			),
			'DATE_CHANGE' => array(
				'data_type' => 'datetime',
			),
			'FIELD' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'FIELD_VALUE' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'CONFIRM_CODE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateConfirmCode'),
			),
			"ATTEMPTS" => array(
				'data_type' => 'integer',
				"default_value" => 0,
			),
		);
	}

	public static function validateConfirmCode()
	{
		return array(
			new Entity\Validator\Length(null, 32),
		);
	}
}
