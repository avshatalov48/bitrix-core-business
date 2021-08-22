<?php
namespace Bitrix\Im\Model;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main;

Loc::loadMessages(__FILE__);

/**
 * Class MessageParamTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> MESSAGE_ID int mandatory
 * <li> PARAM_NAME string(100) mandatory
 * <li> PARAM_VALUE string(100) mandatory
 * </ul>
 *
 * @package Bitrix\Im
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_MessageParam_Query query()
 * @method static EO_MessageParam_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_MessageParam_Result getById($id)
 * @method static EO_MessageParam_Result getList(array $parameters = array())
 * @method static EO_MessageParam_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_MessageParam createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_MessageParam_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_MessageParam wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_MessageParam_Collection wakeUpCollection($rows)
 */

class MessageParamTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_im_message_param';
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
				'title' => Loc::getMessage('MESSAGE_PARAM_ENTITY_ID_FIELD'),
			),
			'MESSAGE_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('MESSAGE_PARAM_ENTITY_MESSAGE_ID_FIELD'),
			),
			'PARAM_NAME' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateParamName'),
				'title' => Loc::getMessage('MESSAGE_PARAM_ENTITY_PARAM_NAME_FIELD'),
			),
			'PARAM_VALUE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateParamValue'),
				'title' => Loc::getMessage('MESSAGE_PARAM_ENTITY_PARAM_VALUE_FIELD'),
				'save_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getSaveModificator'),
				'fetch_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getFetchModificator'),
			),
			'PARAM_JSON' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('MESSAGE_PARAM_ENTITY_PARAM_JSON_FIELD'),
				'save_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getSaveModificator'),
				'fetch_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getFetchModificator'),
			),
			'MESSAGE' => array(
				'data_type' => 'Bitrix\Im\Model\MessageTable',
				'reference' => array('=this.MESSAGE_ID' => 'ref.ID'),
				'join_type' => 'INNER',
			),
		);
	}
	/**
	 * Returns validators for PARAM_NAME field.
	 *
	 * @return array
	 */
	public static function validateParamName()
	{
		return array(
			new Entity\Validator\Length(null, 100),
		);
	}
	/**
	 * Returns validators for PARAM_VALUE field.
	 *
	 * @return array
	 */
	public static function validateParamValue()
	{
		return array(
			new Entity\Validator\Length(null, 100),
		);
	}

	/**
	 * Deletes rows by filter.
	 * @param array $filter Filter does not look like filter in getList. It depends by current implementation.
	 * @return void
	 */
	public static function deleteBatch(array $filter)
	{
		$whereSql = \Bitrix\Main\Entity\Query::buildFilterSql(static::getEntity(), $filter);

		if ($whereSql <> '')
		{
			$tableName = static::getTableName();
			$connection = Main\Application::getConnection();
			$connection->queryExecute("DELETE FROM {$tableName} WHERE {$whereSql}");
		}
	}
}