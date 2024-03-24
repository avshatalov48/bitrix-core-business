<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2012 Bitrix
 *
 * @ignore
 * @see \Bitrix\Catalog\SectionTable
 */
namespace Bitrix\Sale\Internals;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class SectionTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Section_Query query()
 * @method static EO_Section_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Section_Result getById($id)
 * @method static EO_Section_Result getList(array $parameters = [])
 * @method static EO_Section_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_Section createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_Section_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_Section wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_Section_Collection wakeUpCollection($rows)
 */
class SectionTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_iblock_section';
	}

	public static function getMap()
	{
		return [
			'ID' => [
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			],
			'NAME' => [
				'data_type' => 'string',
			]
		];
	}
}
