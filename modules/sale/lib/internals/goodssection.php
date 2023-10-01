<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2012 Bitrix
 *
 * @ignore
 * @see \Bitrix\Iblock\SectionElementTable
 */

namespace Bitrix\Sale\Internals;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class GoodsSectionTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_GoodsSection_Query query()
 * @method static EO_GoodsSection_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_GoodsSection_Result getById($id)
 * @method static EO_GoodsSection_Result getList(array $parameters = [])
 * @method static EO_GoodsSection_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_GoodsSection createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_GoodsSection_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_GoodsSection wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_GoodsSection_Collection wakeUpCollection($rows)
 */
class GoodsSectionTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_iblock_section_element';
	}

	public static function getMap()
	{
		return array(
			'IBLOCK_ELEMENT_ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'PRODUCT' => array(
				'data_type' => 'Product',
				'reference' => array(
					'=this.IBLOCK_ELEMENT_ID' => 'ref.ID'
				)
			),
			'IBLOCK_SECTION_ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'SECT' => array(
				'data_type' => 'Section',
				'reference' => array(
					'=this.IBLOCK_SECTION_ID' => 'ref.ID'
				)
			)
		);
	}
}