<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2016 Bitrix
 */
namespace Bitrix\Main\UserConsent\Internals;

use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class ConsentTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Consent_Query query()
 * @method static EO_Consent_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Consent_Result getById($id)
 * @method static EO_Consent_Result getList(array $parameters = [])
 * @method static EO_Consent_Entity getEntity()
 * @method static \Bitrix\Main\UserConsent\Internals\EO_Consent createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\UserConsent\Internals\EO_Consent_Collection createCollection()
 * @method static \Bitrix\Main\UserConsent\Internals\EO_Consent wakeUpObject($row)
 * @method static \Bitrix\Main\UserConsent\Internals\EO_Consent_Collection wakeUpCollection($rows)
 */
class ConsentTable extends Entity\DataManager
{
	/**
	 * Get table name.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_consent_user_consent';
	}

	/**
	 * Get map.
	 *
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'DATE_INSERT' => array(
				'data_type' => 'datetime',
				'required' => true,
				'default_value' => new DateTime(),
			),
			'AGREEMENT_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'USER_ID' => array(
				'data_type' => 'integer',
			),
			'IP' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => function()
				{
					return [
						function ($value)
						{
							return filter_var($value, FILTER_VALIDATE_IP) !== false;
						}
					];
				}
			),
			'URL' => array(
				'data_type' => 'string',
				'required' => false,
			),
			'ORIGIN_ID' => array(
				'data_type' => 'string',
				'required' => false,
			),
			'ORIGINATOR_ID' => array(
				'data_type' => 'string',
				'required' => false,
			),
			'USER' => array(
				'data_type' => 'Bitrix\Main\UserTable',
				'reference' => array('=this.USER_ID' => 'ref.ID'),
			),
			(new OneToMany('ITEMS', UserConsentItemTable::class, 'USER_CONSENT'))
		);
	}
}
