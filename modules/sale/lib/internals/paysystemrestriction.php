<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sale\Internals;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class PaySystemRestrictionTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_PaySystemRestriction_Query query()
 * @method static EO_PaySystemRestriction_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_PaySystemRestriction_Result getById($id)
 * @method static EO_PaySystemRestriction_Result getList(array $parameters = array())
 * @method static EO_PaySystemRestriction_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_PaySystemRestriction createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_PaySystemRestriction_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_PaySystemRestriction wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_PaySystemRestriction_Collection wakeUpCollection($rows)
 */
class PaySystemRestrictionTable extends \Bitrix\Main\Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sale_pay_system_rstr';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true
			),
			'PAY_SYSTEM_ID' => array(
				'data_type' => 'integer'
			),
			'SORT' => array(
				'data_type' => 'integer'
			),
			'CLASS_NAME' => array(
				'data_type' => 'string'
			),
			'PARAMS' => array(
				'data_type' => 'string'
			)
		);
	}
}
