<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage iblock
 * @copyright  2001-2018 Bitrix
 */

namespace Bitrix\Iblock\ORM;

use Bitrix\Iblock\Iblock;
use Bitrix\Iblock\Property;
use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\FileTable;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\ORM\Fields\FloatField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Query\Join;

/**
 * Generates entity fields for base types
 *
 * @package    bitrix
 * @subpackage iblock
 */
class PropertyToField
{
	/**
	 * @param Property $property
	 * @param Entity $propertyValueEntity
	 *
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function attachField($property, $propertyValueEntity)
	{
		switch ($property->getPropertyType())
		{
			case PropertyTable::TYPE_STRING:

				$propertyValueEntity->addField(new StringField('VALUE'));
				break;

			case PropertyTable::TYPE_NUMBER:

				$propertyValueEntity->addField(new FloatField('VALUE'));
				break;

			case PropertyTable::TYPE_FILE:

				$propertyValueEntity->addField(new IntegerField('VALUE'));

				// add reference to file
				$propertyValueEntity->addField(new Reference(
					'FILE', FileTable::class,
					Join::on("this.VALUE", 'ref.ID')
				));
				break;

			case PropertyTable::TYPE_ELEMENT:

				$propertyValueEntity->addField(new IntegerField('VALUE'));

				// add reference to element
				$refIblock = Iblock::wakeUp($property->getLinkIblockId());
				$refIblock->fill('API_CODE');

				if($refIblock->getApiCode() <> '')
				{
					$refEntityName = $refIblock->getEntityDataClass();

					$propertyValueEntity->addField(
						new Reference('ELEMENT', $refEntityName, Join::on("this.VALUE", 'ref.ID'))
					);
				}
				break;

			case PropertyTable::TYPE_SECTION:

				$propertyValueEntity->addField(new IntegerField('VALUE'));

				// add reference to section
				$propertyValueEntity->addField(new Reference(
					'SECTION', SectionTable::class,
					Join::on("this.VALUE", 'ref.ID')
				));
				break;

			case PropertyTable::TYPE_LIST:

				$propertyValueEntity->addField(new IntegerField('VALUE'));

				// add reference to list item
				$propertyValueEntity->addField(new Reference(
					'ITEM',
					PropertyEnumerationTable::class,
					Join::on('this.VALUE', 'ref.ID')
				));
				break;
		}
	}
}
