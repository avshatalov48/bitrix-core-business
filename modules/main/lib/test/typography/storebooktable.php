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
				->configurePrimary(true),

			(new Reference('STORE', StoreTable::class,
				Join::on('this.STORE_ID', 'ref.ID')))
				->configureJoinType('inner'),

			(new IntegerField('BOOK_ID'))
				->configurePrimary(true),

			(new Reference('BOOK', BookTable::class,
				Join::on('this.BOOK_ID', 'ref.ID')))
				->configureJoinType('inner'),

			(new IntegerField('QUANTITY'))
				->configureDefaultValue(0)
		];
	}
}
