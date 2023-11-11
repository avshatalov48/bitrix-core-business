<?php
namespace Bitrix\Main;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\Entity;

Loc::loadMessages(__FILE__);

/**
 * Class SessionIndexTable
 *
 * Fields:
 * <ul>
 * <li> SESSION_ID int mandatory
 * <li> SEARCH_CONTENT string optional
 * </ul>
 *
 * @package Bitrix\Main
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_UserIndex_Query query()
 * @method static EO_UserIndex_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_UserIndex_Result getById($id)
 * @method static EO_UserIndex_Result getList(array $parameters = [])
 * @method static EO_UserIndex_Entity getEntity()
 * @method static \Bitrix\Main\EO_UserIndex createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\EO_UserIndex_Collection createCollection()
 * @method static \Bitrix\Main\EO_UserIndex wakeUpObject($row)
 * @method static \Bitrix\Main\EO_UserIndex_Collection wakeUpCollection($rows)
 */

class UserIndexTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_user_index';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */

	public static function getMap()
	{
		return array(
			'USER_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'SEARCH_USER_CONTENT' => array(
				'data_type' => 'text',
			),
			'SEARCH_ADMIN_CONTENT' => array(
				'data_type' => 'text',
			),
			'SEARCH_DEPARTMENT_CONTENT' => array(
				'data_type' => 'text',
			),
			'NAME' => array(
				'data_type' => 'string'
			),
			'SECOND_NAME' => array(
				'data_type' => 'string'
			),
			'LAST_NAME' => array(
				'data_type' => 'string'
			),
			'WORK_POSITION' => array(
				'data_type' => 'string'
			),
			'UF_DEPARTMENT_NAME' => array(
				'data_type' => 'string'
			),
		);
	}

	protected static function getMergeFields()
	{
		return array('USER_ID');
	}

	public static function merge(array $data)
	{
		global $DB;

		$result = new Entity\AddResult();

		$helper = Application::getConnection()->getSqlHelper();
		$insertData = $data;
		$updateData = $data;
		$mergeFields = static::getMergeFields();

		foreach ($mergeFields as $field)
		{
			unset($updateData[$field]);
		}

		if (isset($updateData['SEARCH_USER_CONTENT']))
		{
			$value = $DB->forSql($updateData['SEARCH_USER_CONTENT']);
			$encryptedValue = sha1($updateData['SEARCH_USER_CONTENT']);
			$updateData['SEARCH_USER_CONTENT'] = new \Bitrix\Main\DB\SqlExpression("case when " . $helper->getSha1Function(static::getTableName().".SEARCH_USER_CONTENT") . " = '{$encryptedValue}' then ".static::getTableName().".SEARCH_USER_CONTENT else '{$value}' end");
		}

		if (isset($updateData['SEARCH_DEPARTMENT_CONTENT']))
		{
			$value = $DB->forSql($updateData['SEARCH_DEPARTMENT_CONTENT']);
			$encryptedValue = sha1($updateData['SEARCH_DEPARTMENT_CONTENT']);
			$updateData['SEARCH_DEPARTMENT_CONTENT'] = new \Bitrix\Main\DB\SqlExpression("case when " . $helper->getSha1Function(static::getTableName().".SEARCH_DEPARTMENT_CONTENT") ." = '{$encryptedValue}' then ".static::getTableName().".SEARCH_DEPARTMENT_CONTENT else '{$value}' end");
		}

		if (isset($updateData['SEARCH_ADMIN_CONTENT']))
		{
			$value = $DB->forSql($updateData['SEARCH_ADMIN_CONTENT']);
			$encryptedValue = sha1($updateData['SEARCH_ADMIN_CONTENT']);
			$updateData['SEARCH_ADMIN_CONTENT'] = new \Bitrix\Main\DB\SqlExpression("case when " . $helper->getSha1Function(static::getTableName().".SEARCH_ADMIN_CONTENT") . " = '{$encryptedValue}' then ".static::getTableName().".SEARCH_ADMIN_CONTENT else '{$value}' end");
		}

		$merge = $helper->prepareMerge(
			static::getTableName(),
			static::getMergeFields(),
			$insertData,
			$updateData
		);

		if ($merge[0] != "")
		{
			Application::getConnection()->query($merge[0]);
			$id = Application::getConnection()->getInsertedId();
			$result->setId($id);
			$result->setData($data);
		}
		else
		{
			$result->addError(new Error('Error constructing query'));
		}

		return $result;
	}
}