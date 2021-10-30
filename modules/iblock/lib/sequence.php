<?php
namespace Bitrix\Iblock;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class SequenceTable
 *
 * Fields:
 * <ul>
 * <li> IBLOCK_ID int mandatory
 * <li> CODE string(50) mandatory
 * <li> SEQ_VALUE int optional
 * <li> IBLOCK reference to {@link \Bitrix\Iblock\IblockTable}
 * </ul>
 *
 * @package Bitrix\Iblock
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Sequence_Query query()
 * @method static EO_Sequence_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Sequence_Result getById($id)
 * @method static EO_Sequence_Result getList(array $parameters = array())
 * @method static EO_Sequence_Entity getEntity()
 * @method static \Bitrix\Iblock\EO_Sequence createObject($setDefaultValues = true)
 * @method static \Bitrix\Iblock\EO_Sequence_Collection createCollection()
 * @method static \Bitrix\Iblock\EO_Sequence wakeUpObject($row)
 * @method static \Bitrix\Iblock\EO_Sequence_Collection wakeUpCollection($rows)
 */

class SequenceTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_iblock_sequence';
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
				'title' => Loc::getMessage('IBLOCK_SEQUENCE_ENTITY_IBLOCK_ID_FIELD'),
			),
			'CODE' => array(
				'data_type' => 'string',
				'primary' => true,
				'validation' => array(__CLASS__, 'validateCode'),
				'title' => Loc::getMessage('IBLOCK_SEQUENCE_ENTITY_CODE_FIELD'),
			),
			'SEQ_VALUE' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('IBLOCK_SEQUENCE_ENTITY_SEQ_VALUE_FIELD'),
			),
			'IBLOCK' => array(
				'data_type' => 'Bitrix\Iblock\Iblock',
				'reference' => array('=this.IBLOCK_ID' => 'ref.ID'),
			),
		);
	}

	/**
	 * Returns validators for CODE field.
	 *
	 * @return array
	 */
	public static function validateCode()
	{
		return array(
			new Entity\Validator\Length(null, 50),
		);
	}
}