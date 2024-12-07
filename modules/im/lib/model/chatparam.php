<?php
namespace Bitrix\Im\Model;

use Bitrix\Main\Entity;
use Bitrix\Main;

/**
 * Class ChatParamTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_ChatParam_Query query()
 * @method static EO_ChatParam_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_ChatParam_Result getById($id)
 * @method static EO_ChatParam_Result getList(array $parameters = [])
 * @method static EO_ChatParam_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_ChatParam createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_ChatParam_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_ChatParam wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_ChatParam_Collection wakeUpCollection($rows)
 */
class ChatParamTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_im_chat_param';
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
			'CHAT_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'PARAM_NAME' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateParamName'),
			),
			'PARAM_VALUE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateParamValue'),
				'save_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getSaveModificator'),
				'fetch_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getFetchModificator'),
			),
			'PARAM_JSON' => array(
				'data_type' => 'text',
				'save_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getSaveModificator'),
				'fetch_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getFetchModificator'),
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