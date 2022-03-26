<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2018 Bitrix
 */

namespace Bitrix\Main\Test\Typography;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;

/**
 * @package    bitrix
 * @subpackage main
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_StoreBook_Query query()
 * @method static EO_StoreBook_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_StoreBook_Result getById($id)
 * @method static EO_StoreBook_Result getList(array $parameters = [])
 * @method static EO_StoreBook_Entity getEntity()
 * @method static \Bitrix\Main\Test\Typography\EO_StoreBook createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\Test\Typography\EO_StoreBook_Collection createCollection()
 * @method static \Bitrix\Main\Test\Typography\EO_StoreBook wakeUpObject($row)
 * @method static \Bitrix\Main\Test\Typography\EO_StoreBook_Collection wakeUpCollection($rows)
 */
class StoreBookTable extends DataManager
{
	public static function getTableName()
	{
		return '(
			(SELECT 33 AS STORE_ID, 1 AS BOOK_ID, 4 AS QUANTITY)
			UNION
			(SELECT 33 AS STORE_ID, 2 AS BOOK_ID, 0 AS QUANTITY)
			UNION
			(SELECT 34 AS STORE_ID, 2 AS BOOK_ID, 9 AS QUANTITY)
		)';
	}

	public static function getMap()
	{
		return [
			(new IntegerField('STORE_ID'))
				->configurePrimary(),

			(new Reference('STORE', StoreTable::class,
				Join::on('this.STORE_ID', 'ref.ID')))
				->configureJoinType('inner'),

			(new IntegerField('BOOK_ID'))
				->configurePrimary(),

			(new Reference('BOOK', BookTable::class,
				Join::on('this.BOOK_ID', 'ref.ID')))
				->configureJoinType('inner'),

			(new IntegerField('QUANTITY'))
				->configureDefaultValue(0)
		];
	}
}
