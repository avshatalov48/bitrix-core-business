<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2014 Bitrix
 */
namespace Bitrix\Main\Analytics;

use Bitrix\Main\Security\Random;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields;

/**
 * Class CounterDataTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_CounterData_Query query()
 * @method static EO_CounterData_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_CounterData_Result getById($id)
 * @method static EO_CounterData_Result getList(array $parameters = [])
 * @method static EO_CounterData_Entity getEntity()
 * @method static \Bitrix\Main\Analytics\EO_CounterData createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\Analytics\EO_CounterData_Collection createCollection()
 * @method static \Bitrix\Main\Analytics\EO_CounterData wakeUpObject($row)
 * @method static \Bitrix\Main\Analytics\EO_CounterData_Collection wakeUpCollection($rows)
 */
class CounterDataTable extends DataManager
{
	public static function getTableName()
	{
		return 'b_counter_data';
	}

	public static function getMap()
	{
		return array(
			new Fields\StringField('ID', array(
				'primary' => true,
				'default_value' => array(__CLASS__ , 'getUniqueEventId')
			)),
			new Fields\StringField('TYPE', array(
				'required' => true
			)),
			new Fields\TextField('DATA', array(
				'serialized' => true
			))
		);
	}

	public static function getUniqueEventId()
	{
		[$usec, $sec] = explode(" ", microtime());

		$uniqid = mb_substr(base_convert($sec.mb_substr($usec, 2), 10, 36), 0, 16);

		if (mb_strlen($uniqid) < 16)
		{
			$uniqid .= Random::getString(16 - mb_strlen($uniqid));
		}

		return $uniqid;
	}

	public static function submitData($limit = 50)
	{
		return '';
	}
}
