<?
namespace Bitrix\Main\Rating;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\NotImplementedException;

Loc::loadMessages(__FILE__);

/**
 * Class ResultsTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> RATING_ID int mandatory
 * <li> ENTITY_TYPE_ID string(50) mandatory
 * <li> ENTITY_ID int mandatory
 * <li> CURRENT_VALUE double optional
 * <li> PREVIOUS_VALUE double optional
 * <li> CURRENT_POSITION int optional
 * <li> PREVIOUS_POSITION int optional
 * <li> RATING reference to {@link \Bitrix\Main\Rating\RatingTable}
 * </ul>
 *
 * @package Bitrix\Main\Rating
 **/

class ResultsTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_rating_results';
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
				'title' => Loc::getMessage('RESULTS_ENTITY_ID_FIELD'),
			),
			'RATING_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'ENTITY_TYPE_ID' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateEntityTypeId'),
			),
			'ENTITY_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'CURRENT_VALUE' => array(
				'data_type' => 'float',
			),
			'PREVIOUS_VALUE' => array(
				'data_type' => 'float',
			),
			'CURRENT_POSITION' => array(
				'data_type' => 'integer',
			),
			'PREVIOUS_POSITION' => array(
				'data_type' => 'integer',
			),
			'RATING' => array(
				'data_type' => 'Bitrix\Rating\Rating',
				'reference' => array('=this.RATING_ID' => 'ref.ID'),
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