<?php
namespace Bitrix\Catalog;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class MeasureTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> CODE int mandatory
 * <li> MEASURE_TITLE string(500) optional
 * <li> SYMBOL_RUS string(20) optional
 * <li> SYMBOL_INTL string(20) optional
 * <li> SYMBOL_LETTER_INTL string(20) optional
 * <li> IS_DEFAULT bool optional default 'N'
 * </ul>
 *
 * @package Bitrix\Catalog
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Measure_Query query()
 * @method static EO_Measure_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Measure_Result getById($id)
 * @method static EO_Measure_Result getList(array $parameters = [])
 * @method static EO_Measure_Entity getEntity()
 * @method static \Bitrix\Catalog\EO_Measure createObject($setDefaultValues = true)
 * @method static \Bitrix\Catalog\EO_Measure_Collection createCollection()
 * @method static \Bitrix\Catalog\EO_Measure wakeUpObject($row)
 * @method static \Bitrix\Catalog\EO_Measure_Collection wakeUpCollection($rows)
 */

class MeasureTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_catalog_measure';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => new Main\Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('MEASURE_ENTITY_ID_FIELD')
			)),
			'CODE' => new Main\Entity\IntegerField('CODE', array(
				'required' => true,
				'title' => Loc::getMessage('MEASURE_ENTITY_CODE_FIELD')
			)),
			'MEASURE_TITLE' => new Main\Entity\StringField('MEASURE_TITLE', array(
				'validation' => array(__CLASS__, 'validateMeasureTitle'),
				'title' => Loc::getMessage('MEASURE_ENTITY_MEASURE_TITLE_FIELD')
			)),
			'SYMBOL' => new Main\Entity\StringField('SYMBOL', array(
				'column_name' => 'SYMBOL_RUS',
				'validation' => array(__CLASS__, 'validateSymbol'),
				'title' => Loc::getMessage('MEASURE_ENTITY_SYMBOL_FIELD')
			)),
			'SYMBOL_INTL' => new Main\Entity\StringField('SYMBOL_INTL', array(
				'validation' => array(__CLASS__, 'validateSymbolIntl'),
				'title' => Loc::getMessage('MEASURE_ENTITY_SYMBOL_INTL_FIELD')
			)),
			'SYMBOL_LETTER_INTL' => new Main\Entity\StringField('SYMBOL_LETTER_INTL', array(
				'validation' => array(__CLASS__, 'validateSymbolLetterIntl'),
				'title' => Loc::getMessage('MEASURE_ENTITY_SYMBOL_LETTER_INTL_FIELD')
			)),
			'IS_DEFAULT' => new Main\Entity\BooleanField('IS_DEFAULT', array(
				'values' => array('N', 'Y'),
				'default_value' => 'N',
				'title' => Loc::getMessage('MEASURE_ENTITY_IS_DEFAULT_FIELD')
			))
		);
	}
	/**
	 * Returns validators for MEASURE_TITLE field.
	 *
	 * @return array
	 */
	public static function validateMeasureTitle()
	{
		return array(
			new Main\Entity\Validator\Length(null, 500),
		);
	}
	/**
	 * Returns validators for SYMBOL field.
	 *
	 * @return array
	 */
	public static function validateSymbol()
	{
		return array(
			new Main\Entity\Validator\Length(null, 20),
		);
	}
	/**
	 * Returns validators for SYMBOL_INTL field.
	 *
	 * @return array
	 */
	public static function validateSymbolIntl()
	{
		return array(
			new Main\Entity\Validator\Length(null, 20),
		);
	}
	/**
	 * Returns validators for SYMBOL_LETTER_INTL field.
	 *
	 * @return array
	 */
	public static function validateSymbolLetterIntl()
	{
		return array(
			new Main\Entity\Validator\Length(null, 20),
		);
	}
}