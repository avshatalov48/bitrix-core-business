<?php
namespace Bitrix\Sale\Cashbox\Internals;

use	Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Localization\Loc;
use	Bitrix\Main\Type\DateTime;

Loc::loadMessages(__FILE__);

/**
 * Class CashboxTable
 * @package Bitrix\Sale\Cashbox\Internals
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Cashbox_Query query()
 * @method static EO_Cashbox_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Cashbox_Result getById($id)
 * @method static EO_Cashbox_Result getList(array $parameters = array())
 * @method static EO_Cashbox_Entity getEntity()
 * @method static \Bitrix\Sale\Cashbox\Internals\EO_Cashbox createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Cashbox\Internals\EO_Cashbox_Collection createCollection()
 * @method static \Bitrix\Sale\Cashbox\Internals\EO_Cashbox wakeUpObject($row)
 * @method static \Bitrix\Sale\Cashbox\Internals\EO_Cashbox_Collection wakeUpCollection($rows)
 */
class CashboxTable extends DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sale_cashbox';
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'primary' => true,
				'data_type' => 'integer',
				'title' => Loc::getMessage('SALE_CASHBOX_ENTITY_ID_FIELD'),
			),
			'NAME' => array(
				'data_type' => 'string',
				'required' => true,
				'title' => Loc::getMessage('SALE_CASHBOX_ENTITY_NAME_FIELD'),
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('SALE_CASHBOX_ENTITY_ACTIVE_FIELD'),
			),
			'HANDLER' => array(
				'data_type' => 'string',
				'required' => true,
				'title' => Loc::getMessage('SALE_CASHBOX_ENTITY_HANDLER_FIELD'),
			),
			'EMAIL' => array(
				'data_type' => 'string',
				'required' => true,
				'title' => Loc::getMessage('SALE_CASHBOX_ENTITY_EMAIL_FIELD'),
			),
			'SORT' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('SALE_CASHBOX_ENTITY_SORT_FIELD'),
			),
			'DATE_CREATE' => array(
				'data_type' => 'datetime',
				'default_value' => new DateTime(),
				'title' => Loc::getMessage('SALE_CASHBOX_ENTITY_DATE_CREATE_FIELD'),
			),
			'DATE_LAST_CHECK' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('SALE_CASHBOX_ENTITY_DATE_LAST_CHECK_FIELD'),
			),
			'KKM_ID' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('SALE_CASHBOX_ENTITY_KKM_ID_FIELD'),
			),
			'OFD' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('SALE_CASHBOX_ENTITY_OFD_FIELD'),
			),
			'NUMBER_KKM' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('SALE_CASHBOX_ENTITY_NUMBER_KKM_FIELD'),
			),
			'SETTINGS' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('SALE_CASHBOX_ENTITY_SETTINGS_FIELD'),
				'serialized' => true
			),
			'OFD_SETTINGS' => array(
				'data_type' => 'string',
				'serialized' => true
			),
			'USE_OFFLINE' => array(
				'data_type' => 'boolean',
				'title' => Loc::getMessage('SALE_CASHBOX_ENTITY_OFD_TEST_MODE_FIELD'),
				'values' => array('N', 'Y')
			),
			'ENABLED' => array(
				'data_type' => 'boolean',
				'title' => Loc::getMessage('SALE_CASHBOX_ENTITY_ENABLED_FIELD'),
				'values' => array('N', 'Y')
			),
		);
	}

}
