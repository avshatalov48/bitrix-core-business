<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2016 Bitrix
 */
namespace Bitrix\Sale\Internals;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;

/**
 * Class OrderArchivePackedTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_OrderArchivePacked_Query query()
 * @method static EO_OrderArchivePacked_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_OrderArchivePacked_Result getById($id)
 * @method static EO_OrderArchivePacked_Result getList(array $parameters = array())
 * @method static EO_OrderArchivePacked_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_OrderArchivePacked createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_OrderArchivePacked_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_OrderArchivePacked wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_OrderArchivePacked_Collection wakeUpCollection($rows)
 */
class OrderArchivePackedTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sale_order_archive_packed';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			new Main\Entity\IntegerField(
				'ORDER_ARCHIVE_ID',
				array(
					'primary' => true,
				)
			),

			new Main\Entity\StringField('ORDER_DATA')
		);
	}
}