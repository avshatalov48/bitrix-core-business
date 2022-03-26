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
use Bitrix\Main\ORM\Fields\StringField;

use Bitrix\Main\ORM\Fields\Relations\OneToMany;

/**
 * @package    bitrix
 * @subpackage main
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Publisher_Query query()
 * @method static EO_Publisher_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Publisher_Result getById($id)
 * @method static EO_Publisher_Result getList(array $parameters = [])
 * @method static EO_Publisher_Entity getEntity()
 * @method static \Bitrix\Main\Test\Typography\EO_Publisher createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\Test\Typography\EO_Publisher_Collection createCollection()
 * @method static \Bitrix\Main\Test\Typography\EO_Publisher wakeUpObject($row)
 * @method static \Bitrix\Main\Test\Typography\EO_Publisher_Collection wakeUpCollection($rows)
 */
class PublisherTable extends DataManager
{
	public static function getTableName()
	{
		return '(
			(SELECT 253 AS ID, "Publisher Title 253" AS TITLE, 2 AS BOOKS_COUNT)
			UNION
			(SELECT 254 AS ID, "Publisher Title 254" AS TITLE, 0 AS BOOKS_COUNT)
		)';
	}

	public static function getMap()
	{
		return [
			(new IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete(),

			(new StringField('TITLE')),

			(new IntegerField('BOOKS_COUNT')),

			(new OneToMany('BOOKS', BookTable::class, 'PUBLISHER'))
				->configureJoinType('left')
		];
	}

}
