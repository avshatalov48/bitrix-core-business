<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2018 Bitrix
 */

namespace Bitrix\Main\Test\Typography;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\ArrayField;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\Relations\ManyToMany;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\StringField;

/**
 * Test entity.
 * @package    bitrix
 * @subpackage main
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Book_Query query()
 * @method static EO_Book_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Book_Result getById($id)
 * @method static EO_Book_Result getList(array $parameters = [])
 * @method static EO_Book_Entity getEntity()
 * @method static \Bitrix\Main\Test\Typography\Book createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\Test\Typography\Books createCollection()
 * @method static \Bitrix\Main\Test\Typography\Book wakeUpObject($row)
 * @method static \Bitrix\Main\Test\Typography\Books wakeUpCollection($rows)
 */
class BookTable extends DataManager
{
	public static function getObjectClass()
	{
		return Book::class;
	}

	public static function getCollectionClass()
	{
		return Books::class;
	}

	public static function getUfId()
	{
		return 'BOOK';
	}

	public static function getTableName()
	{
		return '(
			(SELECT 1 AS ID, "Title 1" AS TITLE, 253 AS PUBLISHER_ID, "978-3-16-148410-0" AS ISBN, "Y" AS IS_ARCHIVED,
				"[\\"quote1\\",\\"quote2\\"]" AS QUOTES
			)
			UNION
			(SELECT 2 AS ID, "Title 2" AS TITLE, 253 AS PUBLISHER_ID, "456-1-05-586920-1" AS ISBN, "N" AS IS_ARCHIVED,
				"[\\"quote3\\",\\"quote4\\"]" AS QUOTES
			)
		)';
	}

	public static function getMap()
	{
		return [
			(new IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete(),

			(new StringField('TITLE')),

			(new IntegerField('PUBLISHER_ID')),

			(new Reference(
					'PUBLISHER',
					PublisherTable::class,
					Join::on('this.PUBLISHER_ID', 'ref.ID')
				))
				->configureJoinType('inner'),

			(new StringField('ISBN'))
				->configureUnique(),

			(new BooleanField('IS_ARCHIVED'))
				->configureValues('N', 'Y'),

			(new ArrayField('QUOTES')),

			(new ManyToMany('AUTHORS', AuthorTable::class))
				->configureMediatorTableName('(
					(SELECT 1 AS BOOK_ID, 18 AS AUTHOR_ID)
					UNION
					(SELECT 2 AS BOOK_ID, 17 AS AUTHOR_ID)
					UNION
					(SELECT 2 AS BOOK_ID, 18 AS AUTHOR_ID)
				)'),

			(new OneToMany('STORE_ITEMS', StoreBookTable::class, 'BOOK'))
		];
	}
}
