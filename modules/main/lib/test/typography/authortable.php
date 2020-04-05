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
use Bitrix\Main\ORM\Fields\Relations\ManyToMany;
use Bitrix\Main\ORM\Fields\StringField;

/**
 * @package    bitrix
 * @subpackage main
 */
class AuthorTable extends DataManager
{
	public static function getTableName()
	{
		return '(
			(SELECT 17 AS ID, "Name 17" AS NAME, "Last name 17" as LAST_NAME)
			UNION
			(SELECT 18 AS ID, "Name 18" AS NAME, "Last name 18" as LAST_NAME)
		)';
	}

	public static function getMap()
	{
		return [
			(new IntegerField('ID'))
				->configurePrimary(true)
				->configureAutocomplete(true),

			(new StringField('NAME')),

			(new StringField('LAST_NAME')),

			(new ManyToMany('BOOKS', BookTable::class))
				->configureMediatorTableName('(
					(SELECT 1 AS BOOK_ID, 18 AS AUTHOR_ID)
					UNION
					(SELECT 2 AS BOOK_ID, 17 AS AUTHOR_ID)
					UNION
					(SELECT 2 AS BOOK_ID, 18 AS AUTHOR_ID)
				)'),
		];
	}
}
