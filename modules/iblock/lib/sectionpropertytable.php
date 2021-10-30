<?php
namespace Bitrix\Iblock;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class SectionPropertyTable
 *
 * Fields:
 * <ul>
 * <li> IBLOCK_ID int mandatory
 * <li> SECTION_ID int mandatory
 * <li> PROPERTY_ID int mandatory
 * <li> SMART_FILTER bool optional default 'N'
 * <li> DISPLAY_TYPE enum ('A', 'B', 'F', 'G', 'H', 'K', 'P', 'R') optional
 * <li> DISPLAY_EXPANDED bool optional default 'N'
 * <li> FILTER_HINT string(255) optional
 * <li> IBLOCK reference to {@link \Bitrix\Iblock\IblockTable}
 * <li> PROPERTY reference to {@link \Bitrix\Iblock\PropertyTable}
 * <li> SECTION reference to {@link \Bitrix\Iblock\SectionTable}
 * </ul>
 *
 * @package Bitrix\Iblock
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_SectionProperty_Query query()
 * @method static EO_SectionProperty_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_SectionProperty_Result getById($id)
 * @method static EO_SectionProperty_Result getList(array $parameters = array())
 * @method static EO_SectionProperty_Entity getEntity()
 * @method static \Bitrix\Iblock\EO_SectionProperty createObject($setDefaultValues = true)
 * @method static \Bitrix\Iblock\EO_SectionProperty_Collection createCollection()
 * @method static \Bitrix\Iblock\EO_SectionProperty wakeUpObject($row)
 * @method static \Bitrix\Iblock\EO_SectionProperty_Collection wakeUpCollection($rows)
 */

class SectionPropertyTable extends Entity\DataManager
{
	//ABCDE - for numbers
	const NUMBERS_WITH_SLIDER = 'A';
	const NUMBERS = 'B';
	//FGHIJ - for checkboxes
	const CHECKBOXES = 'F';
	const CHECKBOXES_WITH_PICTURES = 'G';
	const CHECKBOXES_WITH_PICTURES_AND_LABELS = 'H';
	//KLMNO - for radio buttons
	const RADIO_BUTTONS = 'K';
	//PQRST - for drop down
	const DROPDOWN = 'P';
	const DROPDOWN_WITH_PICTURES_AND_LABELS = 'R';
	//UWXYZ - reserved
	const CALENDAR = 'U';

	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_iblock_section_property';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'IBLOCK_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'title' => Loc::getMessage('IBLOCK_SECTION_PROPERTY_ENTITY_IBLOCK_ID_FIELD'),
			),
			'SECTION_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'title' => Loc::getMessage('IBLOCK_SECTION_PROPERTY_ENTITY_SECTION_ID_FIELD'),
			),
			'PROPERTY_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'title' => Loc::getMessage('IBLOCK_SECTION_PROPERTY_ENTITY_PROPERTY_ID_FIELD'),
			),
			'SMART_FILTER' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('IBLOCK_SECTION_PROPERTY_ENTITY_SMART_FILTER_FIELD'),
			),
			'DISPLAY_TYPE' => array(
				'data_type' => 'enum',
				'values' => array(
					self::NUMBERS_WITH_SLIDER,
					self::NUMBERS,
					self::CHECKBOXES,
					self::CHECKBOXES_WITH_PICTURES,
					self::CHECKBOXES_WITH_PICTURES_AND_LABELS,
					self::RADIO_BUTTONS,
					self::DROPDOWN,
					self::DROPDOWN_WITH_PICTURES_AND_LABELS,
					self::CALENDAR
				),
				'title' => Loc::getMessage('IBLOCK_SECTION_PROPERTY_ENTITY_DISPLAY_TYPE_FIELD'),
			),
			'DISPLAY_EXPANDED' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('IBLOCK_SECTION_PROPERTY_ENTITY_DISPLAY_EXPANDED_FIELD'),
			),
			'FILTER_HINT' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('IBLOCK_SECTION_PROPERTY_ENTITY_FILTER_HINT_FIELD'),
				'validation' => array(__CLASS__, 'validateFilterHint'),
			),
			'IBLOCK' => array(
				'data_type' => 'Bitrix\Iblock\Iblock',
				'reference' => array('=this.IBLOCK_ID' => 'ref.ID'),
			),
			'PROPERTY' => array(
				'data_type' => 'Bitrix\Iblock\Property',
				'reference' => array('=this.PROPERTY_ID' => 'ref.ID'),
			),
			'SECTION' => array(
				'data_type' => 'Bitrix\Iblock\Section',
				'reference' => array('=this.SECTION_ID' => 'ref.ID'),
			),
		);
	}

	/**
	 * Returns validators for FILTER_HINT field.
	 *
	 * @return array
	 */
	public static function validateFilterHint()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}
}