<?
namespace Bitrix\Main\Rating;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\NotImplementedException;

Loc::loadMessages(__FILE__);

/**
 * Class RatingTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> ACTIVE string(1) mandatory
 * <li> NAME string(512) mandatory
 * <li> ENTITY_ID string(50) mandatory
 * <li> CALCULATION_METHOD string(3) mandatory default 'SUM'
 * <li> CREATED datetime optional
 * <li> LAST_MODIFIED datetime optional
 * <li> LAST_CALCULATED datetime optional
 * <li> POSITION bool optional default 'N'
 * <li> AUTHORITY bool optional default 'N'
 * <li> CALCULATED bool optional default 'N'
 * <li> CONFIGS string optional
 * </ul>
 *
 * @package Bitrix\Rating
 **/

class RatingTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_rating';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'ACTIVE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateActive'),
			),
			'NAME' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateName'),
			),
			'ENTITY_ID' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateEntityId'),
			),
			'CALCULATION_METHOD' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateCalculationMethod'),
			),
			'CREATED' => array(
				'data_type' => 'datetime',
			),
			'LAST_MODIFIED' => array(
				'data_type' => 'datetime',
			),
			'LAST_CALCULATED' => array(
				'data_type' => 'datetime',
			),
			'POSITION' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
			),
			'AUTHORITY' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
			),
			'CALCULATED' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
			),
			'CONFIGS' => array(
				'data_type' => 'text',
			),
		);
	}

	public static function add(array $data)
	{
		throw new NotImplementedException("Use CRatings class.");
	}

	public static function update($primary, array $data)
	{
		throw new NotImplementedException("Use CRatings class.");
	}

	public static function delete($primary)
	{
		throw new NotImplementedException("Use CRatings class.");
	}
}