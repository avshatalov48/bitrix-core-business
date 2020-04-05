<?php
namespace Bitrix\Iblock;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class SectionElementTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_iblock_section_element';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'IBLOCK_SECTION_ID' => new Main\Entity\IntegerField('IBLOCK_SECTION_ID', array(
				'primary' => true,
				'title' => Loc::getMessage('IBLOCK_SECTION_ELEMENT_ENTITY_IBLOCK_SECTION_ID_FIELD'),
			)),
			'IBLOCK_ELEMENT_ID' => new Main\Entity\IntegerField('IBLOCK_ELEMENT_ID', array(
				'primary' => true,
				'title' => Loc::getMessage('IBLOCK_SECTION_ELEMENT_ENTITY_IBLOCK_ELEMENT_ID_FIELD'),
			)),
			'ADDITIONAL_PROPERTY_ID' => new Main\Entity\IntegerField('ADDITIONAL_PROPERTY_ID', array(
				'title' => Loc::getMessage('IBLOCK_SECTION_ELEMENT_ENTITY_ADDITIONAL_PROPERTY_ID_FIELD'),
			)),
			'IBLOCK_SECTION' => new Main\Entity\ReferenceField(
				'IBLOCK_SECTION',
				'Bitrix\Iblock\Section',
				array('=this.IBLOCK_SECTION_ID' => 'ref.ID')
			),
			'IBLOCK_ELEMENT' => new Main\Entity\ReferenceField(
				'IBLOCK_ELEMENT',
				'Bitrix\Iblock\Element',
				array('=this.IBLOCK_ELEMENT_ID' => 'ref.ID'),
				array(
					'title' => Loc::getMessage('IBLOCK_SECTION_ELEMENT_ENTITY_IBLOCK_ELEMENT_FIELD'),
				)
			)
		);
	}
}
