<?php
namespace Bitrix\Im\Model;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class LastSearchTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> USER_ID int mandatory
 * <li> DIALOG_ID string(50) mandatory
 * </ul>
 *
 * @package Bitrix\Im
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_LastSearch_Query query()
 * @method static EO_LastSearch_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_LastSearch_Result getById($id)
 * @method static EO_LastSearch_Result getList(array $parameters = array())
 * @method static EO_LastSearch_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_LastSearch createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_LastSearch_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_LastSearch wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_LastSearch_Collection wakeUpCollection($rows)
 */

class LastSearchTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_im_last_search';
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
			'USER_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'DIALOG_ID' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateDialogId'),
			),
			'ITEM_RID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'ITEM_CID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'RELATION' => array(
				'data_type' => 'Bitrix\Im\Model\RelationTable',
				'reference' => array('=this.ITEM_RID' => 'ref.ID'),
				'join_type' => 'INNER',
			),
			'CHAT' => array(
				'data_type' => 'Bitrix\Im\Model\ChatTable',
				'reference' => array('=this.ITEM_CID' => 'ref.ID'),
				'join_type' => 'INNER',
			),
		);
	}
	/**
	 * Returns validators for DIALOG_ID field.
	 *
	 * @return array
	 */
	public static function validateDialogId()
	{
		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}
}