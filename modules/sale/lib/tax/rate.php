<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2014 Bitrix
 */
namespace Bitrix\Sale\Tax;

use Bitrix\Sale;
use Bitrix\Main;
use Bitrix\Main\Entity;

/**
 * Class RateTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Rate_Query query()
 * @method static EO_Rate_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Rate_Result getById($id)
 * @method static EO_Rate_Result getList(array $parameters = [])
 * @method static EO_Rate_Entity getEntity()
 * @method static \Bitrix\Sale\Tax\EO_Rate createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Tax\EO_Rate_Collection createCollection()
 * @method static \Bitrix\Sale\Tax\EO_Rate wakeUpObject($row)
 * @method static \Bitrix\Sale\Tax\EO_Rate_Collection wakeUpCollection($rows)
 */
class RateTable extends Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_sale_tax_rate';
	}

	public static function getMap()
	{
		return array(

			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			)
		);
	}
}
