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
 */
class PublisherTable extends DataManager
{
	public static function getTableName()
	{
		return '(
			(SELECT 253 AS ID, "Publisher Title 253" AS TITLE)
			UNION
			(SELECT 254 AS ID, "Publisher Title 254" AS TITLE)
		)';
	}

	public static function getMap()
	{
		return [
			(new IntegerField('ID'))
				->configurePrimary(true)
				->configureAutocomplete(true),

			(new StringField('TITLE')),

			(new OneToMany('BOOKS', BookTable::class, 'PUBLISHER'))
				->configureJoinType('left')
		];
	}

}
