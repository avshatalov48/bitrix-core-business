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
 * Class BasketArchivePackedTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_BasketArchivePacked_Query query()
 * @method static EO_BasketArchivePacked_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_BasketArchivePacked_Result getById($id)
 * @method static EO_BasketArchivePacked_Result getList(array $parameters = array())
 * @method static EO_BasketArchivePacked_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_BasketArchivePacked createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_BasketArchivePacked_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_BasketArchivePacked wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_BasketArchivePacked_Collection wakeUpCollection($rows)
 */
class BasketArchivePackedTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sale_basket_archive_packed';
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
				'BASKET_ARCHIVE_ID',
				array(
					'primary' => true,
				)
			),

			new Main\Entity\StringField('BASKET_DATA')
		);
	}
}