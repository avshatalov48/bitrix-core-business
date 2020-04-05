<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage iblock
 * @copyright  2001-2018 Bitrix
 */

namespace Bitrix\Iblock;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\FloatField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Query\Join;

/**
 * @package    bitrix
 * @subpackage iblock
 */
class ElementPropertyTable extends DataManager
{
	public static function getMap()
	{
		return [
			(new IntegerField('ID'))
				->configurePrimary(true)
				->configureAutocomplete(true),

			new IntegerField('IBLOCK_PROPERTY_ID'),

			new IntegerField('IBLOCK_ELEMENT_ID'),

			new Reference(
				'ELEMENT', ElementTable::class,
				Join::on('this.IBLOCK_ELEMENT_ID', 'ref.ID')
			),

			new TextField('VALUE'),

			new StringField('VALUE_TYPE'),

			new IntegerField('VALUE_ENUM'),

			new FloatField('VALUE_NUM'),

			new StringField('DESCRIPTION'),

			new Reference(
				'ENUM',
				PropertyEnumerationTable::class,
				Join::on('this.VALUE_ENUM', 'ref.ID')
			),
		];
	}
}
