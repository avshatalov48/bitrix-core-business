<?php
namespace Bitrix\Highloadblock;

use Bitrix\Main\Entity;

/**
 * Class HighloadBlockRightsTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_HighloadBlockRights_Query query()
 * @method static EO_HighloadBlockRights_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_HighloadBlockRights_Result getById($id)
 * @method static EO_HighloadBlockRights_Result getList(array $parameters = [])
 * @method static EO_HighloadBlockRights_Entity getEntity()
 * @method static \Bitrix\Highloadblock\EO_HighloadBlockRights createObject($setDefaultValues = true)
 * @method static \Bitrix\Highloadblock\EO_HighloadBlockRights_Collection createCollection()
 * @method static \Bitrix\Highloadblock\EO_HighloadBlockRights wakeUpObject($row)
 * @method static \Bitrix\Highloadblock\EO_HighloadBlockRights_Collection wakeUpCollection($rows)
 */
class HighloadBlockRightsTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_hlblock_entity_rights';
	}

	/**
	 * Returns entity map definition.
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => new Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
			)),
			'HL_ID' => new Entity\IntegerField('HL_ID', array(
				'required' => true,
			)),
			'TASK_ID' => new Entity\IntegerField('TASK_ID', array(
				'required' => true,
			)),
			'ACCESS_CODE' => new Entity\StringField('ACCESS_CODE', array(
				'required' => true,
				'validation' => array(__CLASS__, 'validateAccessCode'),
			)),
			'USER_ACCESS' => new Entity\ReferenceField(
				'USER_ACCESS',
				'\Bitrix\Main\UserAccessTable',
				array('=this.ACCESS_CODE' => 'ref.ACCESS_CODE')
			),
			'TASK_OPERATION' => new Entity\ReferenceField(
				'TASK_OPERATION',
				'\Bitrix\Main\TaskOperationTable',
				array('=this.TASK_ID' => 'ref.TASK_ID')
			),
		);
	}

	/**
	 * Returns validators for ACCESS_CODE field.
	 *
	 * @return array
	 */
	public static function validateAccessCode()
	{
		return array(
			new Entity\Validator\Length(null, 50),
		);
	}

	/**
	 * Get all available operations for hl block (for current user).
	 * @param int|array $hlId Hl block (id or array of id).
	 * @return array
	 */
	public static function getOperationsName($hlId)
	{
		$operations = array();
		$uid = $GLOBALS['USER']->getId();//@fixme
		$acc = new \CAccess;
		$acc->UpdateCodes();

		$res = \Bitrix\HighloadBlock\HighloadBlockRightsTable::getList(array(
				'select' => array(
					'HL_ID',
					'OPNAME' => 'TASK_OPERATION.OPERATION.NAME'
				),
				'filter' => array(
					'HL_ID' => $hlId,
					'USER_ACCESS.USER_ID' => $uid,
					'!TASK_OPERATION.OPERATION.NAME' => false
				)
			));
		while ($row = $res->fetch())
		{
			if (!isset($operations[$row['HL_ID']]))
			{
				$operations[$row['HL_ID']] = array();
			}
			$operations[$row['HL_ID']][] = $row['OPNAME'];
		}

		return is_array($hlId) ? $operations : $operations[$hlId];
	}
}