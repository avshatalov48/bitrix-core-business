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
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Store_Query query()
 * @method static EO_Store_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Store_Result getById($id)
 * @method static EO_Store_Result getList(array $parameters = [])
 * @method static EO_Store_Entity getEntity()
 * @method static \Bitrix\Main\Test\Typography\EO_Store createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\Test\Typography\EO_Store_Collection createCollection()
 * @method static \Bitrix\Main\Test\Typography\EO_Store wakeUpObject($row)
 * @method static \Bitrix\Main\Test\Typography\EO_Store_Collection wakeUpCollection($rows)
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
				->configurePrimary()
				->configureAutocomplete(),

			(new StringField('ADDRESS')),

			(new OneToMany('BOOK_ITEMS', StoreBookTable::class, 'STORE'))
		];
	}
}
