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
use Bitrix\Main\ORM\Fields\Relations\OneToMany;
use Bitrix\Main\ORM\Fields\StringField;

/**
 * Class description
 * @package    bitrix
 * @subpackage main
 */
class StoreTable extends DataManager
{
	public static function getTableName()
	{
		return '(
			(SELECT 33 AS ID, "Store 33" AS ADDRESS)
			UNION
			(SELECT 34 AS ID, "Store 34" AS ADDRESS)
		)';
	}

	public static function getMap()
	{
		return [
			(new IntegerField('ID'))
				->configurePrimary(true)
				->configureAutocomplete(true),

			(new StringField('ADDRESS')),

			(new OneToMany('BOOK_ITEMS', StoreBookTable::class, 'STORE'))
		];
	}
}
