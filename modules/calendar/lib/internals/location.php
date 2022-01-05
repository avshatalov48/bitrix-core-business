<?php
namespace Bitrix\Calendar\Internals;

use Bitrix\Main;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class LocationTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> SECTION_ID int mandatory
 * <li> NECESSITY bool ('N', 'Y') optional default 'N'
 * <li> CAPACITY int optional default 0
 * </ul>
 *
 * @package Bitrix\Calendar
 **/
class LocationTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_calendar_location';
	}
	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			'ID' => [
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('LOCATION_ID'),
			],
			'SECTION_ID' => [
				'data_type' => 'integer',
				'required' => true,
				'validation' => array(__CLASS__, 'validateSectionId'),
				'title' => Loc::getMessage('LOCATION_SECTION_ID')
			],
			'NECESSITY' => [
				'data_type' => 'boolean',
				'values' => ['N', 'Y'],
				'title' => Loc::getMessage('LOCATION_NECESSITY'),
			],
			'CAPACITY' => [
				'data_type' => 'integer',
				'validation' => array(__CLASS__, 'validateCapacity'),
				'title' => Loc::getMessage('LOCATION_CAPACITY')
			],
		];
	}

	/**
	 * Returns validators for SECTION_ID field.
	 * @return array
	 */
	public static function validateSectionId()
	{
		return array(
			new Main\Entity\Validator\Length(null, 100),
		);
	}

	/**
	 * Returns validators for CAPACITY field.
	 * @return array
	 */
	public static function validateCapacity()
	{
		return array(
			new Main\Entity\Validator\Length(null, 10),
		);
	}
}