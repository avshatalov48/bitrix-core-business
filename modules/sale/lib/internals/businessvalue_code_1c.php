<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Main;

/**
 * Class BusinessValueCode1CTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_BusinessValueCode1C_Query query()
 * @method static EO_BusinessValueCode1C_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_BusinessValueCode1C_Result getById($id)
 * @method static EO_BusinessValueCode1C_Result getList(array $parameters = array())
 * @method static EO_BusinessValueCode1C_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_BusinessValueCode1C createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_BusinessValueCode1C_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_BusinessValueCode1C wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_BusinessValueCode1C_Collection wakeUpCollection($rows)
 */
class BusinessValueCode1CTable extends Main\Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_sale_bizval_code_1c';
	}

	public static function getMap()
	{
		return array(
			new Main\Entity\IntegerField('PERSON_TYPE_ID', array('primary' => true)),
			new Main\Entity\IntegerField('CODE_INDEX'    , array('primary' => true)),
			new Main\Entity\StringField ('NAME'          , array('required' => true, 'size' => 255)),
		);
	}
}
