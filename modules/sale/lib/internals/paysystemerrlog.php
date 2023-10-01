<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sale\Internals;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class PaySystemErrLogTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_PaySystemErrLog_Query query()
 * @method static EO_PaySystemErrLog_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_PaySystemErrLog_Result getById($id)
 * @method static EO_PaySystemErrLog_Result getList(array $parameters = [])
 * @method static EO_PaySystemErrLog_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_PaySystemErrLog createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_PaySystemErrLog_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_PaySystemErrLog wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_PaySystemErrLog_Collection wakeUpCollection($rows)
 */
class PaySystemErrLogTable extends Main\Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_sale_pay_system_err_log';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('PAY_SYSTEM_ENTITY_ID_FIELD'),
			),
			'DATE_INSERT' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('PAY_SYSTEM_ENTITY_DATE_ADD_FIELD'),
			),
			'MESSAGE' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('PAY_SYSTEM_ENTITY_LID_FIELD'),
				'required' => true
			)
		);
	}
}